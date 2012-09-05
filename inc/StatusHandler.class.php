<?php
/**
 * StatusHandler,
 * manages the status and helps by returning objects with specific
 * informations such as info, success, error messages and data.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/StatusHandler.class.php
 */

// maybe includes in future.

/**
 * StatusHandler,
 * manages the status and helps by returning objects with specific
 * informations such as info, success, error messages and data.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/StatusHandler.class.php
 */
class StatusHandler {

    /**
     * If action was successful or not successful.
     * @var boolean
     */
    private $status = false;
    /**
     * Error messages
     * @var array iterable array with error messages
     */
    private $error = array();
    /**
     * Information messages
     * @var array iterable array with information messages
     */
    private $info = array();
    /**
     *
     * @var array iterable array with successful messages
     */
    private $success = array();
    /**
     * Data which was requested.
     * It can be all....
     * @var string|array|null it can be anything!
     */
    private $data = null;
    /**
     * Object of Instance...
     * @var StatusHandler static instance of StatusHandler
     */
    private static $instance = null;

    /**
     * This constructor does nothing but is there now for further editing.
     * On Default the Status will be false
     *
     * @param boolean $status The initial status..
     */
    public function StatusHandler($status = false) {
        $this->status = $status;
    }

    /**
     * Creates an global instance of StatusHandler
     *
     * @return void
     */
    public static function createGlobal() {
        self::setInstance(new StatusHandler(true));
    }

    /**
     * Set a global StatusHandler
     *
     * @param StatusHandler $sh new global StatusHandler
     *
     * @return void
     */
    public static function setInstance(StatusHandler $sh = null) {
        if (self::getInstance() == null || $sh == null) {
            self::$instance = $sh;
        }
    }

    /**
     * Returns object of Class "StatusHandler"
     *
     * @return StatusHandler Object of this class
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * Sets the status of the handler.
     *
     * @param boolean $st true|false for the status
     *
     * @return returns the value that was set
     */
    public function setStatus($st) {
        if (!is_bool($st)) {
            trigger_error("Der Status sollte eine boolean Variable sein");
        }
        $this->status = $st;
        return $st;
    }

    /**
     * Gets the Status of the Action.
     * If it was successful it will return true - else false.
     *
     * @return boolean false on error
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get the status of the data
     *
     * @return boolean if the status is true and there is data
     */
    public function getDataStatus() {
        return $this->getStatus() && $this->isData();
    }

    /* All messages have to be an array! I don't accept anything else.
     * But I will accept the joining or anything else!
     * So I will only provide addXY and not setXY Methods for error and info!
     */

    /**
     * Adds an error message
     *
     * @param string|array $msg the message
     *
     * @return void
     */
    public function addError($msg) {
        if (!is_array($msg)) {
            // Hash it:
            $h = self::hashMessage($msg);
            // Exist the same message?
            if (!isset($this->error[$h])) {
                $this->error[$h] = $msg;
            }
        } else {
            foreach ($msg as $k => $v) {
                // Hash it:
                $h = self::hashMessage($v);
                // Exist the same message?
                if (!isset($this->error[$h])) {
                    $this->error[$h] = $v;
                }
            }
        }
    }

    /**
     * Hashs the message in "adler32"
     *
     * @param string $msg Message to be hashed
     *
     * @return string the hashed string.
     */
    public static function hashMessage($msg) {
        return hash('adler32', $msg);
    }

    /**
     * clear errors
     *
     * @return void
     */
    public function resetErrors() {
        $this->error = array();
    }

    /**
     * Adds an information message
     *
     * @param string $msg the message
     *
     * @return void
     */
    public function addInfo($msg) {
        if (!is_array($msg)) {
            // Hash it:
            $h = self::hashMessage($msg);
            // Exist the same message?
            if (!isset($this->info[$h])) {
                $this->info[$h] = $msg;
            }
        } else {
            foreach ($msg as $k => $v) {
                // Hash it:
                $h = self::hashMessage($v);
                // Exist the same message?
                if (!isset($this->info[$h])) {
                    $this->info[$h] = $v;
                }
            }
        }
    }

    /**
     * clear infos
     *
     * @return void
     */
    public function resetInfos() {
        $this->info = array();
    }

    /**
     * Adds an success message
     *
     * @param string $msg the message
     *
     * @return void
     */
    public function addSuccess($msg) {
        if (!is_array($msg)) {
            // Hash it:
            $h = self::hashMessage($msg);
            // Exist the same message?
            if (!isset($this->success[$h])) {
                $this->success[$h] = $msg;
            }
        } else {
            foreach ($msg as $k => $v) {
                // Hash it:
                $h = self::hashMessage($v);
                // Exist the same message?
                if (!isset($this->success[$h])) {
                    $this->success[$h] = $v;
                }
            }
        }
    }

    /**
     * clear success
     *
     * @return void
     */
    public function resetSuccess() {
        $this->success = array();
    }

    /**
     * You get the error messages.
     * It can be in 2 formats: as an array or as an string.
     * If you want a string you have to give me $join true or an delimiter
     * I will use ',' as delimiter if you only give me true for $join
     *
     * @param boolean|string $join false if you want the array; true|string for string
     *
     * @return string|array if you give me $join false or if you don't give me $join you get an array; otherwise you
     * get a string
     */
    public function getError($join = false) {
        if ($join !== false) {
            return implode((is_bool($join) ? ',' : $join), $this->error);
        } else {
            return $this->error;
        }
    }

    /**
     * Checks whether there is an error message or not.
     *
     * @return boolean true if there is min. 1 error message
     */
    public function issetErrorMsg() {
        if (count($this->error) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * You get the information messages.
     * It can be in 2 formats: as an array or as an string.
     * If you want a string you have to give me $join true or an delimiter
     * I will use ',' as delimiter if you only give me true for $join
     *
     * @param boolean|string $join false if you want the array; true|string for string
     *
     * @return string|array if you give me $join false or if you don't give me $join you get an array;
     * otherwise you get a string
     */
    public function getInfo($join = false) {
        if ($join !== false) {
            return implode((is_bool($join) ? ',' : $join), $this->info);
        } else {
            return $this->info;
        }
    }

    /**
     * Checks whether there is an info message or not.
     *
     * @return boolean true if there is min. 1 info message
     */
    public function issetInfoMsg() {
        if (count($this->info) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * You get the success messages.
     * It can be in 2 formats: as an array or as an string.
     * If you want a string you have to give me $join true or an delimiter
     * I will use ',' as delimiter if you only give me true for $join
     *
     * @param boolean|string $join false if you want the array; true|string for string
     *
     * @return string|array if you give me $join false or if you don't give me $join you get an array;
     * otherwise you get a string
     */
    public function getSuccess($join = false) {
        if ($join !== false) {
            return implode((is_bool($join) ? ',' : $join), $this->success);
        } else {
            return $this->success;
        }
    }

    /**
     * Checks whether there is a success message or not.
     *
     * @return boolean true if there is min. 1 success message
     */
    public function issetSuccess() {
        if (count($this->success) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether there exists success msg or even not
     *
     * @return boolean
     *
     * @see StatusHandler::issetSuccess
     */
    public function issetSuccessMsg() { // because the method above fails out of scheme ;-)
        return $this->issetSuccess();
    }

    /**
     * You get the relevant messages.
     * It can be in 2 formats: as an array or as an string.
     * If you want a string you have to give me $join true or an delimiter
     * I will use ',' as delimiter if you only give me true for $join
     * If the status is true, you get success message; otherwise you get the error messages
     * PLEASE ATTENTION: YOU WILL NOT GET INFO MSG!
     *
     * @param boolean|string $join false if you want the array; true|string for string
     *
     * @return string|array if you give me $join false or if you don't give me $join you get an array;
     * otherwise you get a string
     */
    public function getMessage($join = false) {
        if ($this->status) {
            return $this->getSuccess($join);
        } else {
            return $this->getError($join);
        }
    }

    /**
     * If you call this method it will replace all data in $this->data !
     * So be carefully.
     *
     * @param all $da Data to store.
     *
     * @return void
     */
    public function setData($da) {
        $this->data = $da;
    }

    /**
     * This is difficult.
     * If $this->data is an array it's no problem, else: it will create an array with the data in array an what you
     * give me here.
     * But also be carefully: it will replace data with existent key.
     *
     * @param mixed          $da Data to store
     * @param string|integer $k  Key for data. If it is null [default] than it let choose PHP.
     *
     * @return void
     */
    public function addData($da, $k = null) {
        if (!is_array($this->data) && $this->data !== null) {
            $this->data = array($this->data);
        } else if ($this->data == null) {
            $this->data = array();
        }

        if (is_null($k) || is_double($k)) {
            $this->data[] = $da;
        } else {
            $this->data[$k] = $da;
        }
    }

    /**
     * Whether there is data or not
     *
     * @return boolean
     */
    public function isData() {
        return $this->data != null && !empty($this->data);
    }

    /**
     * It will replace $this->data with a blank array!
     * But be carefully: it will erase all data!
     *
     * @return void
     */
    public function resetData() {
        $this->data = array();
    }

    /**
     * Data which was stored here ;-)
     *
     * @return array|string how it was saved with addData/setData
     */
    public function getData() {
        return $this->data;
    }

    /**
     * merge all attributes of the status handler
     *
     * @param StatusHandler $statusHandler Status Handler object
     * @param string        $data_key      Only for the data: which key?
     *
     * @return void
     */
    public function meltStatusHandler(StatusHandler $statusHandler, $data_key = null) {
        if ($statusHandler->getStatus()) {
            $this->addSuccess($statusHandler->getSuccess());
        } else {
            $this->addError($statusHandler->getError());
        }
        $this->addInfo($statusHandler->getInfo());

        $this->setStatus($this->getStatus() && $statusHandler->getStatus());
        if ($statusHandler->getData() != null) {
            $this->addData($statusHandler->getData(), $data_key);
        }
        return $this;
    }

    /**
     * Wrapper for meltStatusHandler
     *
     * @param StatusHandler $sh       Status Handler object
     * @param string        $data_key Only for the data: which key?
     *
     * @return void
     */
    public function merge(StatusHandler $sh, $data_key = null) {
        $this->meltStatusHandler($sh, $data_key);
    }

    /**
     * Merge all messages (only!) to the global message system if there exist some...
     *
     * @param StatusHandler $sh the statushandler which should be merged with global...
     *
     * @return boolean true, if there exist a global instance, else false
     */
    public static function messagesMerge(StatusHandler $sh) {
        // global instance:
        $gsh = self::getInstance();
        if ($gsh != null) {
            // Error
            if ($sh->issetErrorMsg()) {
                foreach ($sh->getError() as $k => $v) {
                    $gsh->addError($v);
                }
            }
            // Info
            if ($sh->issetInfoMsg()) {
                foreach ($sh->getInfo() as $k => $v) {
                    $gsh->addInfo($v);
                }
            }
            // Success
            if ($sh->issetSuccess()) {
                foreach ($sh->getSuccess() as $k => $v) {
                    $gsh->addSuccess($v);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Send all messages to template.
     * Please try to avoid setting $append to true, because
     * this implicit, that messages can be set without StatusHandler!
     *
     * @param boolean $append if it should be merged with existent smarty messages, give me true; else false
     *
     * @return void
     */
    public function sendToTemplate($append = false) {
        // Get tpl?
        $tpl = Smarty_FLS::getInstance();
        if ($this->issetErrorMsg()) {
            if ($append) {
                $tpl->append('errormsg', $this->getError(), true);
            } else {
                $tpl->assign('errormsg', $this->getError());
            }
        }
        if ($this->issetInfoMsg()) {
            if ($append) {
                $tpl->append('infomsg', $this->getInfo(), true);
            } else {
                $tpl->assign('infomsg', $this->getInfo());
            }
        }
        if ($this->issetSuccess()) {
            if ($append) {
                $tpl->append('succmsg', $this->getSuccess(), true);
            } else {
                $tpl->assign('succmsg', $this->getSuccess());
            }
        }
    }

    /**
     * Get a String with errormessages if the status is false, info and successmessages if the status is true to send
     * to the client.
     *
     * @return the string
     */
    public function getJsString() {
        $string = '<!--';
        if ($this->getStatus()) {
            foreach ($this->getInfo() as $value) {
                $string.= '#info|' . $value;
            }
            foreach ($this->getSuccess() as $value) {
                $string.= '#succ|' . $value;
            }
        } else {
            foreach ($this->getError() as $value) {
                $string.= '#error|' . $value;
            }
        }
        return $string . '-->';
    }

    /**
     * Get a json string with these Informations:
     * status, error messages (as array), success messages (as array), info messages (as array)
     * and the data, how it is stored here...
     *
     * @return json Json String with all Informations.
     */
    public function getJsonString() {
        // He get all informations:
        $data = array(
            'status' => $this->getStatus(),
            'error' => $this->getError(),
            'success' => $this->getSuccess(),
            'info' => $this->getInfo(),
            'data' => $this->getData()
        );

        return json_encode($data);
    }

}

?>
