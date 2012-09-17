<?php

/**
 * Handles the connection to MySQL
 *
 * PHP Version 5.3
 *
 * @package    FLS
 * @subpackage System
 * @author     Website-Team <website-team@fls-wiesbaden.de>
 * @copyright  2012-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license    GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link       https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Database2.class.php
 */
// maybe includes in future.

/**
 * Handles the connection to MySQL
 *
 * @package    FLS
 * @subpackage System
 * @author     Website-Team <website-team@fls-wiesbaden.de>
 * @copyright  2012-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license    GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link       https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Database2.class.php
 */
class Database2 {

    private $connectionId;
    private $in_transaction = false;
    private $beginTransaction = false;
    private $endTransaction = false;
    private $num_queries = 0;
    private $lastResult = null;
    private $prefix = '';
    private $externalPrefix = null;
    private $defaultPrefix = '';
    private static $_instance = null;

    const IGNORE_VALUE = "3j9t~SrYb|PW(`b\Â§P(=Jw~H/)u8$:*0$6MJ:|K8<<d-8eZYUTx@)jjLeE/(";

    /**
     * Database Class Constructor
     * Initialize the connection
     *
     * @param string  $sqlserver   host name of the mysql server
     * @param string  $sqluser     user name for the connection
     * @param string  $sqlpassword password for $sqluser on $sqlserver
     * @param string  $database    name of database of $sqlserver
     * @param boolean $persistency Set the connectino persistency for all users.
     */
    public function Database2($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true) {

        if ($persistency) {
            $sqlserver = 'p:' . $sqlserver;
        }

        $this->connectionId = mysqli_connect($sqlserver, $sqluser, $sqlpassword, $database) or
                die('No connection is possible.');

        self::$_instance = $this;

        if ($this->isOpen()) {
            $this->beginTransaction = true;
            $this->endTransaction = true;
        } else {
            trigger_error('Connection error to the sql server.', E_USER_ERROR);
        }
    }

    /**
     * You get an instance...
     *
     * @return sql_db Databaseconnection
     */
    public static function getInstance() {
        return self::$_instance;
    }

    /**
     * Set the table prefix
     *
     * @param string $prefix Table prefix
     *
     * @return void
     */
    public function setPrefix($prefix) {
        $this->defaultPrefix = $prefix;
        $this->prefix = $prefix;
    }

    /**
     * Set the external table prefix
     *
     * @param string $prefix Table prefix
     *
     * @return void
     */
    public function setExternalPrefix($prefix) {
        $this->externalPrefix = $prefix;
    }

    /**
     * Sets the external prefix active for the next query, if one is set
     *
     * @return void
     */
    public function activateExternalPrefix() {
        if ($this->externalPrefix != null) {
            $this->prefix = $this->externalPrefix;
        }
    }

    /**
     * Set the prefix to the default prefix
     *
     * @return void
     */
    public function resetPrefix() {
        $this->prefix = $this->defaultPrefix;
    }

    /**
     * Get the id of the connection if the connection is open
     *
     * @return int
     */
    public function getConnectionId() {
        return $this->connectionId;
    }

    /**
     * Get the number of queries
     *
     * @return int the number
     */
    public function getQueryNumber() {
        return $this->num_queries;
    }

    /**
     * Get the last inserted id
     *
     * @return integer|boolean boolean on false, else the number
     */
    public function getlastInsertId() {
        return ( is_object($this->connectionId) ) ? mysqli_insert_id($this->connectionId) : false;
    }

    /**
     * Get all sql errors
     *
     * @return array all SQL errors
     */
    public function getSqlErrors() {
        $error = array();
        if (!$this->lastResult->getStatus()) {
            $error[] = $this->lastResult->getError(true);
        }

        return $error;
    }

    /**
     * Whether the connection is open
     *
     * @return boolean
     */
    public function isOpen() {
        if (mysqli_ping($this->connectionId)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the last executed statement.
     *
     * @return string a SQL Statement
     */
    public function getLastStatement() {
        return $this->lastResult->getStatement();
    }

    /**
     * Get the last executed statements.
     *
     * @return array the SQL statements
     */
    public function getLastStatements() {
        $val = array();
        $val[] = $this->getLastStatement();

        return $val;
    }

    /**
     * Get the last result
     *
     * @return StatusHandler (->getData(): Result)
     * @see StatusHandler
     * @see Result
     */
    public function getLastResult() {
        return $this->lastResult;
    }

    /**
     * Get the last results
     *
     * @return array of StatusHandler (->getData(): Result)
     * @see StatusHandler
     * @see Result
     */
    public function getLastResults() {
        return array($this->lastResult);
    }

    /**
     * Execute a query.
     *
     * @param string  $query       the statement which should be executed.
     * @param boolean $transaction set the transaction. Dont know for what.
     *
     * @return DbStatusHandler
     */
    private function query($query = '', $transaction = false) {
        $sh = new DbStatusHandler($query, $this);
        if ($query != "") {
            $this->num_queries++;
            if ($transaction == $this->beginTransaction && !$this->in_transaction) {
                if ($sh->setStatus(mysqli_query($this->connectionId, 'BEGIN'))) {
                    $this->in_transaction = true;
                }
            }
            $sh->setResult(mysqli_query($this->connectionId, $query));
        } else {
            if ($transaction == $this->endTransaction && $this->in_transaction) {
                $sh->setStatus(mysqli_query($this->connectionId, "COMMIT"));
            }
        }

        if ($sh->getStatus()) {
            if ($transaction == $this->endTransaction && $this->in_transaction) {
                $this->in_transaction = false;

                if (!mysqli_query($this->connectionId, 'COMMIT')) {
                    mysqli_query($this->connectionId, 'ROLLBACK');
                    $sh->setStatus(false);
                    $sh->resetData();
                }
            }
        } else {
            trigger_error(
                    mysqli_errno($this->getConnectionId()) . ': ' . mysqli_error($this->getConnectionId()) .
                    '(' . $query . ')', E_USER_ERROR
            );
            if ($this->in_transaction) {
                mysqli_query($this->connectionId, 'ROLLBACK');
                $this->in_transaction = false;
            }
        }

        $this->lastResult = $sh;
        return $sh;
    }

    /**
     * This is a improved version of the mysql_query method. There are some tags, that will be replaced automaticly by
     * the method. Here a list of all tags:
     *
     * %p - prefix (no argument needs)
     * %n - for NULL values  (no argument needs)
     * %l - LIKE Percents left AND right!
     * %y - LIKE Percents left side.
     * %z - LIKE Percents right side.
     * %x - LIKE only for secure things!!! (NO USER INPUT!)
     * %r - raw string (without quotation or similiar)
     * %s - string (if there are no quotation marks, they will be inserted) Please do not user quotation marks in future
     * %i - integer
     * %f - float
     * %d - equivalent to %f
     * %t - comma separated tulpel list. In SQL you do not set "(" and ")": 'WHERE test IN %t';
     *      Argument can be ","-separated string or an array.
     * %b - boolean. True = 1, False = 0
     *
     * All tags need, unless it is otherwise described, a value in the arguments. The arguments could be in one array,
     * diffrent arguments or a combination of this. If an argument is null the tag will be replaced by the null tag.
     *
     * If there are too much oder less arguments or the arguments don't have the right data type,
     * the method trigger an error.
     *
     * @return DbStatusHandler
     */
    public function q() {
        $num = func_num_args();
        if ($num < 1) {
            trigger_error('Missing argument 1 with the sql statement!', E_USER_ERROR);
            return false;
        }

        // Get Statement
        $statement = func_get_arg(0);

        //get all arguments
        $args = array();
        for ($index = 1; $index < $num; $index++) {
            if (is_array(func_get_arg($index))) {
                foreach (func_get_arg($index) as $value) {
                    if ($value !== Database2::IGNORE_VALUE) {
                        $args[] = $value;
                    }
                }
            } else {
                if (func_get_arg($index) !== Database2::IGNORE_VALUE) {
                    $args[] = func_get_arg($index);
                }
            }
        }

        $run = true;
        $percentSignsThatAreNotTags = array();
        $lessArguments = false;
        $tagCount = 0;
        $nextTag = 0;
        while ($run) {
            $like = -1;
            //Get the position of the next procent sign
            $nextTag = strpos($statement, '%', $nextTag);
            //Are there procent signs left?
            if ($nextTag) {
                //Which tag is it?
                $tag = substr($statement, $nextTag + 1, 1);
                switch ($tag) {
                    case 'p':
                        $statement = substr_replace($statement, $this->prefix, $nextTag, 2);
                        break;
                    case 'x':
                        $statement = substr_replace($statement, '%', $nextTag, 2);
                        $nextTag++;
                        break;
                    case 'l':
                        $like = 2; // 0 => left, 1 => right, 2 => both
                    case 'y':
                        $like = ($like > -1) ? $like : 0;
                    case 'z':
                        $like = ($like > -1) ? $like : 1;
                    case 'r':
                    case 's':
                        if (!array_key_exists($tagCount, $args)) {
                            $lessArguments = true;
                            $run = false;
                            break;
                        } else if ($args[$tagCount] === null) {
                            $statement = substr_replace($statement, '%n', $nextTag, 2);
                            $tagCount++;
                            break;
                        }
                        $args[$tagCount] = $this->prepare($args[$tagCount], ($like > -1 ? true : false));
                        $beforeTag = substr($statement, $nextTag - 1, 1);
                        //Are there still quotation marks?
                        if (isset($like) && $like == 2) {
                            $args[$tagCount] = '%' . $args[$tagCount] . '%';
                        } else if (isset($like) && $like == 0) {
                            $args[$tagCount] = '%' . $args[$tagCount];
                        } else if (isset($like) && $like == 1) {
                            $args[$tagCount] = $args[$tagCount] . '%';
                        }

                        if (!(
                            ($beforeTag == "'" || $beforeTag == '"') &&
                            (
                                substr($statement, $nextTag + 2, 1) == $beforeTag ||
                                (
                                    substr($statement, $nextTag + 2, 1) == "\\" &&
                                    substr($statement, $nextTag + 3, 1) == $beforeTag
                                )
                            )
                        )
                        && $tag != 'r'
                        ) {
                            $args[$tagCount] = '"' . $args[$tagCount] . '"';
                        }
                        $statement = substr_replace($statement, $args[$tagCount++], $nextTag, 2);
                        // For security purpose and to avoid security holes, we have to go after the insertion.
                        $nextTag += strlen($args[$tagCount - 1]);
                        $like = -1;
                        break;
                    case 'n':
                        // When this is the case: we have to replace the operator to "IS"
                        // or to "IS NOT".
                        // Operators can be "=", ">", "<", "!=", "<=", ">="
                        //                   I   IN   IN    IN    I     I
                        $operator = substr($statement, $nextTag - 3, 3);
                        $operator = trim($operator);
                        switch ($operator) {
                            case '=':
                            case '<=':
                            case '>=':
                                $operator = ' IS ';
                                break;
                            case '!=':
                            case '<':
                            case '>':
                                $operator = ' IS NOT ';
                                break;
                            default:
                                $operator = null;
                        }
                        $statement = substr_replace($statement, 'NULL', $nextTag, 2);
                        if ($operator !== null) {
                            $statement = substr_replace($statement, $operator, $nextTag - 3, 3);
                        }
                        break;
                    case 'i':
                        if (!array_key_exists($tagCount, $args)) {
                            $lessArguments = true;
                            $run = false;
                            break;
                        } else if ($args[$tagCount] === null) {
                            $statement = substr_replace($statement, '%n', $nextTag, 2);
                            $tagCount++;
                            break;
                        }
                        if (!is_numeric($args[$tagCount]) || strstr($args[$tagCount], '.')) {
                            trigger_error(
                                    'The ' . ($tagCount + 1) . ' tag is defined as int, but the argument "' .
                                    $args[$tagCount] . '" is no integer.', E_USER_ERROR
                            );
                            return false;
                        }
                        $statement = substr_replace($statement, $this->prepare($args[$tagCount++]), $nextTag, 2);
                        break;
                    case 'b':
                        if (!array_key_exists($tagCount, $args)) {
                            $lessArguments = true;
                            $run = false;
                            break;
                        } else if ($args[$tagCount] === null) {
                            $statement = substr_replace($statement, '%n', $nextTag, 2);
                            $tagCount++;
                            break;
                        }
                        if (!is_bool($args[$tagCount])) {
                            trigger_error(
                                    'The ' . ($tagCount + 1) . ' tag is defined as boolean, but the argument "' .
                                    $args[$tagCount] . '" is no boolean.', E_USER_ERROR
                            );
                            return false;
                        }
                        $statement = substr_replace($statement, ($args[$tagCount++] == true ? '1' : '0'), $nextTag, 2);
                        break;
                    case 'f':
                    case 'd':
                        if (!array_key_exists($tagCount, $args)) {
                            $lessArguments = true;
                            $run = false;
                            break;
                        } else if ($args[$tagCount] === null) {
                            $statement = substr_replace($statement, '%n', $nextTag, 2);
                            $tagCount++;
                            break;
                        }
                        if (!is_numeric($args[$tagCount])) {
                            trigger_error(
                                    'The ' . ($tagCount + 1) . ' tag is defined as float/double, but the argument
                            "' . $args[$tagCount] . '" is no float/double.', E_USER_ERROR
                            );
                            return false;
                        }
                        $statement = substr_replace($statement, $this->prepare($args[$tagCount++]), $nextTag, 2);
                        break;
                    case 't':
                        if (!array_key_exists($tagCount, $args)) {
                            $lessArguments = true;
                            $run = false;
                            break;
                        } else if ($args[$tagCount] === null) {
                            $statement = substr_replace($statement, '%n', $nextTag, 2);
                            $tagCount++;
                            break;
                        }

                        // now check $args[$tagCount].
                        // i will only accept numeric/float and string
                        if (is_array($args[$tagCount])) {
                            $tlist = $args[$tagCount];
                        } else {
                            $tlist = explode(',', $args[$tagCount]);
                        }
                        $tlist_run = true;
                        $tlist_i = 0;
                        while ($tlist_run == true && $tlist_i < count($tlist)) {
                            if (is_float($tlist[$tlist_i]) || is_numeric($tlist[$tlist_i])) {
                                $tlist[$tlist_i] = $this->prepare($tlist[$tlist_i]);
                            } else if (is_string($tlist[$tlist_i])) {
                                $tlist[$tlist_i] = '"' . $this->prepare($tlist[$tlist_i]) . '"';
                            } else {
                                $tlist_run = false;
                            }

                            $tlist_i++;
                        }

                        if ($tlist_run == false) {
                            trigger_error(
                                    'The ' . ($tagCount + 1) . ' tag is defined as tulpel, but the argument
                            "' . $args[$tagCount] . '" is no tulpel or invalid.', E_USER_ERROR
                            );
                            return false;
                        } else {
                            $args[$tagCount] = '(' . implode(',', $tlist) . ')';
                        }

                        $statement = substr_replace($statement, $args[$tagCount++], $nextTag, 2);
                        break;
                    default:
                        //The '%' is used without a tag and because of the loop, that never will stop if the percent
                        //sign is in the statement, we remove it, but remember the place where it was, to read it later.
                        $percentSignsThatAreNotTags[] = $nextTag;
                        $statement = substr_replace($statement, '', $nextTag, 1);
                        break;
                }
            } else {
                $run = false;
            }
        }

        //Now the removed percent signs will be readded
        foreach ($percentSignsThatAreNotTags as $value) {
            $statement = substr_replace($statement, '%', $value, 0);
        }

        if ($lessArguments) {
            trigger_error(
                    'Missing argument(s).' .
                    'Data for the ' . ($tagCount + 1) . ' tag (don\'t count the tags without arguments) is missing' . "\n" .
                    'The Statement: ' . $statement, E_USER_ERROR
            );
            return false;
        }
        if ($tagCount < count($args)) {
            trigger_error(
                    'Too many arguments.' .
                    'The Statement: ' . $statement, E_USER_ERROR
            );
            return false;
        }
        
        return $this->query($statement);
    }

    /**
     * prepares a variable, to avoid hacking
     *
     * @param string  $value   The value which should be prepared
     * @param boolean $do_like If it is for like operators, this should be true!
     *
     * @return string
     */
    private function prepare($value, $do_like = false) {
        $value = stripslashes($value);

        if ($do_like) {
            $value = str_replace(array('%', '_'), array('\%', '\_'), $value);
        }

        if (function_exists('mysqli_real_escape_string')) {
            return mysqli_real_escape_string($this->connectionId, $value);
        } else {
            return mysql_escape_string($value);
        }
    }

    /**
     * Close the connection
     *
     * @return boolean if it was closed: true, else: false
     */
    public function close() {
        if ($this->isOpen()) {
            // Commit any remaining transactions
            if ($this->in_transaction) {
                mysqli_query($this->connectionId, 'COMMIT');
            }

            return mysqli_close($this->connectionId);
        } else {
            return false;
        }
    }

    /**
     * Get the next workday after the given date
     *
     * @param integer $after Unix timestamp
     *
     * @return integer
     */
    public static function getNextWorkDayAfter($after = null) {
        if ($after == null) {
            $after = time();
        }

        $chkDate = $after;
        do {
            $chkDate = strtotime('+1 day', $chkDate);
        } while (date('N', $chkDate) == 6 || date('N', $chkDate) == 7);

        return $chkDate;
    }

}

?>
