<?php

/**
 * Objective result row
 *
 * PHP Version 5.3
 *
 * @package   FLS::Database
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/ResultRowSet.class.php
 */
// maybe includes in future.

/**
 * Objective result row
 *
 * PHP Version 5.3
 *
 * @package   FLS::Database
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/ResultRowSet.class.php
 */
class ResultRowSet {
    /**
     * @var array rows of a result
     */
    private $rows;

    /**
     * @var mixed pointer or null
     */
    private $pointer;

    /**
     * Constructor of resultset class
     * it sets the data and creates the objects.
     *
     * @param array $data resultset (2D)
     */
    public function __construct($data) {
        $this->rows = array();
        $this->pointer = null;

        $this->setData($data);
        // Reset pointer
        $this->resetRow();
    }

    /**
     * Sets the data -> creates the resultrow objects.
     *
     * @param array $data resultset (2D)
     *
     * @return void
     */
    private function setData($data) {
        foreach ($data as $rk => $row) {
            $resultObj = new ResultRow();
            foreach ($row as $k => $v) {
                $resultObj->setAttribute($k, $v);
            }
            $this->setRow($resultObj, $rk);
        }
    }

    /**
     * Reset the pointer
     *
     * @return void
     */
    public function resetRow() {
        reset($this->rows);
        $this->pointer = null;
    }

    /**
     * receive the next element
     *
     * @return boolean true on success, false on error or EOF
     */
    public function nextRow() {
        $elm = false;
        if ($this->pointer === null) {
            reset($this->rows);
            $this->pointer = current($this->rows);
            each($this->rows);
            $elm = true;
        } else {
            $value = current($this->rows);
            $tmp = each($this->rows); // er steigt hier nicht aus. Aber WARUM !?
            if ($tmp !== false) {
                $this->pointer = $value;
                $elm = true;
            }
        }

        return $elm;
    }

    /**
     * Get the data of the next row. The internal pointer is moved to the next row.
     *
     * @return mixed the data or false, if it EOF
     */
    public function getNextRow() {
        if ($this->nextRow()) {
            return $this->get();
        } else {
            return false;
        }
    }

    /**
     * Get only the content of the first row
     *
     * @return mixed most it will be an array
     */
    public function getFirstRow() {
        reset($this->rows);
        return current($this->rows);
    }

    /**
     * Get the thing which is pointed.
     *
     * @return mixed most it will be an array
     */
    public function get() {
        return $this->pointer;
    }

    /**
     * Get the complete result
     *
     * @return array
     */
    public function getAll() {
        return $this->rows;
    }

    /**
     * Set the pointer one back.
     *
     * @return boolean true on success, false on EOF or error
     */
    public function prevRow() {
        $status = false;
        $prev($this->rows);
        if (each($this->rows) === false) {
            $this->pointer = null;
        } else {
            $this->pointer = current($this->rows);
            $status = true;
        }

        return $status;
    }

    /**
     * set an result row (have to be an object of ResultRow!)
     *
     * @param ResultRow $row   result row
     * @param integer   $index index, or null to append
     *
     * @return void
     */
    public function setRow(ResultRow $row, $index = null) {
        if ($index != null) {
            $this->rows[$index] = $row;
        } else {
            $this->rows[] = $row;
        }
    }

    /**
     * remove an row
     *
     * @param integer $index index to be removed.
     *
     * @return void
     */
    public function removeRow($index) {
        unset($this->rows[$index]);
    }

}

?>
