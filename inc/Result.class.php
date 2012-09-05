<?php
/**
 * Wrapper of the resultId from DB Requests
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Database.class.php
 */

// maybe includes in future.

/**
 * Wrapper of the resultId from DB Requests
 *
 * PHP Version 5.3
 *
 * @package    FLS
 * @author     Website-Team <website-team@fls-wiesbaden.de>
 * @copyright  2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license    GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link       https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Database.class.php
 * @deprecated in future use ResultRow and ResultRowSet.
 */
class Result {

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
     * @param integer $resultId  the id of the statement
     * @param string  $statement the statement
     */
    public function Result($resultId, $statement) {
        $this->resultId = $resultId;
        $this->statement = $statement;

    }

    /**
     * Get the number of affected or resulted rows.
     * This is universal - it doesn't matter if it is for insert, update or select,...
     *
     * @return false|integer It returns false on no result and no update/insert; else: number of rows
     */
    public function getNumRows() {
        if ($query_id === true && Database2::getInstance()->isOpen()) {
            return mysql_affected_rows(Database2::getInstance()->getConnectionId());
        } else if ($query_id) {
            return mysql_num_rows($query_id);
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
        return mysql_num_fields($this->resultId);
    }

    /**
     * Get the name of a field (by a specific index)
     *
     * @param integer $offset index number of col
     *
     * @return string the name
     */
    public function getFieldName($offset) {
        return mysql_field_name($this->resultId, $offset);
    }

    /**
     * Get the type of the field
     *
     * @param integer $offset index number of col
     *
     * @return string the type
     */
    public function getFieldType($offset) {
        return mysql_field_type($this->resultId, $offset);
    }

    /**
     * Get one (!) row of an query
     *
     * @return array the values
     */
    public function fetchRow() {
        return mysql_fetch_array($this->resultId, MYSQL_ASSOC);
    }

    /**
     * Get an complete result list of an query
     *
     * @return array the values
     */
    public function fetchRowSet() {
        $result = array();
        while ($temp = mysql_fetch_array($this->resultId, MYSQL_ASSOC)) {
            $result[] = $temp;
        }
        return $result;
    }

    /**
     * Jump to a specific row
     *
     * @param integer $rownum Row number
     *
     * @return boolean true or false
     */
    public function rowSeek($rownum) {
        return mysql_data_seek($this->resultId, $rownum);
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
     * Whether there is data or not
     *
     * @return boolean true if there is data.
     */
    public function isData() {
        return $this->db->numrows($this->resultId) > 0;
    }

    /**
     * Release the memory which was needed for this.
     */
    function __destruct() {
        mysql_free_result($this->resultId);
    }
}

?>
