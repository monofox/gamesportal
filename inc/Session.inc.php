<?php

/**
 * Session,
 * manages the sessions for the users.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Session.class.php
 */
class Session extends Listenable {
    /* Define the mysql table you wish to use with
    this class, this table MUST exist. */

    /**
     * @var Database Datenbankanbindung
     */
    private $db;

    /**
     * @var Session the session instance
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function Session() {
        $this->db = Database2::getInstance();
        self::setInstance($this);
    }

    /**
     * Set the session instance
     *
     * @param Session $instance an object
     *
     * @return void
     */
    public static function setInstance(Session $instance = null) {
        if (self::getInstance() == null || $instance == null) {
            self::$instance = $instance;
        }
    }

    /**
     * Get the session instance
     *
     * @return Session
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * öffnet eigene Sessiondatenbankverbindung, falls diese noch nicht korrekt erstellt wurde
     *
     * @return boolean
     */
    public function _open() {
        $conf = \FLS\Lib\Configuration\Configuration::getInstance();

        if ($this->db->getConnectionId() === false || Database2::getInstance() == null) {
            new Database2(
                $conf->datenbank->DBHOST->getValue(),
                $conf->datenbank->DBUSER->getValue(),
                $conf->datenbank->DBPASSWORD->getValue(),
                $conf->datenbank->DBNAME->getValue(),
                $conf->datenbank->persistent->getValue()
            );
        }
        return true;
    }

    /**
     *  Close session
     *
     *  @return boolean
     */
    public function _close() {
        /* This is used for a manual call of the
        session gc function */
        $this->_gc(0);

        return true;
    }

    /**
     * Get the uid of a specific session
     *
     * @param string $ses_id Session id
     *
     * @return integer null if nothing was found.
     */
    public static function getSessionUid($ses_id) {
        $db = Database2::getInstance();
        $q = $db->q('SELECT ses_uid FROM %psessions WHERE ses_id = %s', $ses_id);
        if ($q->hasData()) {
            return $q->getFirst()->ses_uid;
        } else {
            return null;
        }
    }

    /**
     * Read session data from database
     *
     * @param string $ses_id SessionID dessen Daten gelesen werden sollen
     *
     * @return string leer, falls nichts gefunden wurden
     */
    public function _read($ses_id) {
        $ses_data = '';
        $q = $this->db->q("SELECT * FROM %psessions WHERE ses_id = %s", $ses_id);

        if ($q->hasData()) {
            $ses_data = base64_decode($q->getFirst()->ses_value);
        }
        
        return $ses_data;
    }

    /**
     * Checks whether there exist some session information or not.
     *
     * @param string $ses_id String of session ID
     *
     * @return boolean
     */
    private function issetSession($ses_id) {
        $q = $this->db->q('SELECT ses_id FROM %psessions WHERE ses_id = %s', $ses_id);

        return $q->hasData();
    }

    /**
     *  Speichert Daten in die Datenbank
     *
     *  @param string $ses_id String der SessionID
     *  @param string $data   Data
     *
     *  @return boolean
     */
    public function _write($ses_id, $data) {
        $uid = User::getInstance()->getID();

        if ($this->issetSession($ses_id)) {
            $q = $this->db->q(
                'UPDATE %psessions SET ses_time = %i, ses_uid = %i, ses_value = %s
                WHERE ses_id = %s', 
                time(), $uid, base64_encode($data), $ses_id
            );
        } else {
            $q = $this->db->q(
                'INSERT INTO %psessions (ses_id, ses_time, ses_start, ses_uid, ses_value)
                VALUES (%s, %i, %i, %i, %s)',
                $ses_id, time(), time(), $uid, base64_encode($data)
            );
        }

        return $q->getStatus();
    }

    /**
     * Destroy session record in database
     *
     * @param string  $ses_id  SessionID
     * @param integer $ses_uid user id
     *
     * @return boolean
     */
    public function _destroy($ses_id, $ses_uid = null) {
        if ($ses_uid === null) {
            $ses_uid = self::getSessionUid($ses_id);
        }
        $session_res = $this->db->q("DELETE FROM %psessions WHERE ses_id = %s", $ses_id);

        // Delete user session related cache data.
        $tempDir = \FLS\Lib\Configuration\Configuration::getInstance()->default->tempdir->value;
        $dir = glob($tempDir . session_id() . '.*{/*,/}', GLOB_BRACE);
        if ($dir !== false) {
            // Search all files and delete?
            foreach ($dir as $path) {
                if (is_dir($path)) {
                    rmdir($path);
                } else {
                    unlink($path);
                }
            }
        }

        $this->fireEvent('sessionDestroy', array('id' => $ses_id, 'uid' => $ses_uid));
        if (!$session_res) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Garbage collection, deletes old sessions
     *
     * @param integer $life Lebenszeit eines Zyklus
     *
     * @return boolean
     */
    public function _gc($life) {
        $result = true;
        $ses_life = $life;
        $ses_life = time() - get_cfg_var("session.gc_maxlifetime");

        // Get all old sessions
        $q = $this->db->q('SELECT ses_id, ses_uid FROM %psessions WHERE ses_time < %i', $ses_life);
        if ($q->hasData()) {
            foreach ($q->getData() as $res) {
                $result = $result && $this->_destroy($res->ses_id, $res->ses_uid);
            }
        } else { 
            $result = false;
        }
    }

}

?>
