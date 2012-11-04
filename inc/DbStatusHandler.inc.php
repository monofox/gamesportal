<?php

/**
 * DbStatusHandler
 * Wrapper of the resultId from DB Requests
 *
 * @package    FLS
 * @subpackage System
 * @author     Website-Team <website-team@fls-wiesbaden.de>
 * @copyright  2012-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license    GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link       https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/DbStatusHandler.class.php
 */
class DbStatusHandler extends StatusHandler {

    /**
     * @var Database2 $resultId the database
     */
    private $db;

    /**
     * @var integer $resultId Result Id
     */
    private $resultId;

    /**
     * @var string $statement the full statement which was executed
     */
    private $statement;

    /**
     * Constructor of the Result Class
     *
     * @param string   $statement the statement
     * @param Database $db        database object.
     */
    public function DbStatusHandler($statement, Database2 $db) {
        $this->statement = $statement;
        $this->db = $db;
        $this->resultId = false;
    }

    /**
     * Set the result
     *
     * @param integer $resultId the "result id" of mysql
     *
     * @return void
     */
    public function setResult($resultId) {
        $this->resultId = $resultId;
        if ($resultId == null || is_bool($resultId)) {
            if (!$this->setStatus($resultId)) {
                $this->addError('Interner Fehler bei der Datenbankabfrage');
                if (\FLS\Lib\Configuration\Configuration::getInstance()->admin->DEBUG->value) {
                    StatusHandler::getInstance()->messagesMerge($this);
                }
            } else {
                if (stristr($this->statement, 'DELETE') !== false) {
                    $this->addSuccess('Daten erfolgreich gel&ouml;scht');
                } else {
                    $this->addSuccess('Daten erfolgreich gespeichert');
                }
            }
        } else {
            $this->setStatus(true);
            $num = mysqli_num_rows($this->resultId);
            if ($num !== false && $num > 0) {
                $this->setData($this->setResultSet($this->fetchRowSet()));
                $this->addSuccess('Daten erfolgreich geladen');
            } else {
                $this->addError('Keine Daten gefunden');
            }
        }
    }

    /**
     * Set an result set (full)
     *
     * @param array $data 2d array with the data of mysql
     *
     * @return ResultRowSet
     */
    private function setResultSet($data) {
        return new ResultRowSet($data);
    }

    /**
     * Get the number of affected or resulted rows.
     * This is universal - it doesn't matter if it is for insert, update or select,...
     *
     * @return false|integer It returns false on no result and no update/insert; else: number of rows
     */
    public function getNumRows() {
        if ($this->resultId === true && Database2::getInstance()->isOpen()) {
            return mysqli_affected_rows(Database2::getInstance()->getConnectionId());
        } else if ($this->resultId) {
            return mysqli_num_rows($this->resultId);
        } else {
            return false;
        }
    }
    
    /**
     * Get the number of fields (cols)
     *
     * @return integer number of fields
     */
    public function getNumFields() {
        return $this->resultId !== false ? mysqli_num_fields($this->resultId) : false;
    }

    /**
     * Get the name of a field (by a specific index)
     *
     * @param integer $offset index number of col
     *
     * @return string the name
     */
    public function getFieldName($offset) {
        if ($this->resultId !== false) {
            $fields = mysqli_fetch_fields($this->resultId);
            return $fields[$offset]->name;
        } else {
            return false;
        }
    }

    /**
     * Get the type of the field
     *
     * @param integer $offset index number of col
     *
     * @return string the type
     */
    public function getFieldType($offset) {
        if ($this->resultId !== false) {
            $fields = mysqli_fetch_fields($this->resultId);
            return $fields[$offset]->type;
        } else {
            return false;
        }
    }

    /**
     * Get the result set
     *
     * @return ResultRowSet the data
     */
    public function getResultSet() {
        return parent::getData();
    }

    /**
     * Get the complete array of data
     *
     * @return array the data
     */
    public function getData() {
        if (parent::getData() != null) {
            if (is_a(parent::getData(), 'ResultRowSet')) {
                return parent::getData()->getAll();
            } else {
                return parent::getData();
            }            
        } else {
            return array();
        }
    }

    /**
     * Get the first row of the result. This is usefull if the statement should only return one row.
     *
     * @return boolean|ResultRow the data or false, if there is no data
     */
    public function getFirst() {
        $data = $this->getData();
        if (is_array($data) && sizeof($data) > 0) {
            return $data[0];
        } else {
            return false;
        }
    }

    /**
     * Get one (!) row of an query
     *
     * @return array the values
     */
    public function fetchRow() {
        return $this->resultId !== false ? mysqli_fetch_assoc($this->resultId) : false;
    }

    /**
     * We will fetch an object of a single result
     *
     * @return Object 
     */
    public function fetchObject() {
        return $this->resultId !== false ? mysqli_fetch_object($this->resultId) : false;
    }

    /**
     * Get an complete result list of an query
     *
     * @return array the values
     */
    private function fetchRowSet() {
        if ($this->resultId !== false) {
            $result = array();
            while ($temp = mysqli_fetch_assoc($this->resultId)) {
                $result[] = $temp;
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Jump to a specific row
     *
     * @param integer $rownum Row number
     *
     * @return boolean true or false
     */
    public function rowSeek($rownum) {
        return $this->resultId !== false ? mysqli_data_seek($this->resultId, $rownum) : false;
    }

    /**
     * Get the statement of the query
     *
     * @return string the statement
     */
    public function getStatement() {
        return $this->statement;
    }

    /**
     * If the status is true and there is fetched data
     *
     * @return boolean true, if there is data, false otherwise
     */
    public function hasData() {
        return $this->getStatus() && $this->getNumRows() > 0;
    }

    /**
     * Release the memory which was needed for this.
     */
    public function __destruct() {
        if (is_resource($this->resultId)) {
            mysqli_free_result($this->resultId);
        }
    }

}

?>
