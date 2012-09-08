<?php

use \FLS\Lib\Configuration\Configuration as Configuration;

/**
 * User,
 * manages all user specific things like: add new users, delete, etc.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/User.class.php
 */
class User extends Listenable {

    /**
     * @var int User ID
     */
    private $id;

    /**
     * @var string users last name
     */
    private $name;
    /**
     * @var string Users first name
     */
    private $firstname;

    /**
     * @var string Login name
     */
    private $loginName;

    /**
     * @var Database $db
     */
    private $db;

    /**
     * @var Configuration $conf
     */
    private $conf;

    /**
     * @var User instance
     */
    private static $instance = null;

    /**
     * Constructor
     * Checks if User is logged in and loads the corresponding content
     *
     * @param integer $userId User ID
     */
    public function User($userId = null) {
        $this->conf = Configuration::getInstance();
        $this->db = Database2::getInstance();

        $this->id = $userId;
        if ($this->id == null && isset($_SESSION['userid'])) {
            $this->id = $_SESSION['userid'];
        }

        if ($this->id != null) {
            $this->loadUserData();
        }

        self::setInstance($this);
    }

    /**
     * Set the user instance
     *
     * @param User $instance an object
     *
     * @return void
     */
    public static function setInstance(User $instance = null) {
        if (self::getInstance() == null || $instance == null) {
            self::$instance = $instance;
        }
    }

    /**
     * Get the user instance
     *
     * @return User
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * Get the id of the user
     *
     * @param boolean $wAnonym If you want to get the anonymous id back on no login, than set true (default)
     *
     * @return integer id
     */
    public function getID($wAnonym = true) {
        $uid = null;
        if (!is_null($this->id)) {
            $uid = $this->id;
        } else if ($wAnonym) {
            $uid = 0;
        } 

        return $uid;
    }

    /**
     * Get the name of the user
     *
     * @return string
     */
    public function getName() {
        return $this->firstname . ' ' . $this->name;
    }

    /**
     * Return the first name of the user
     *
     * @return string first name
     */
    public function getFirstName() {
        return $this->firstname;
    }

    /**
     * Return the last name of the user
     *
     * @return string last name
     */
    public function getLastName() {
        return $this->name;
    }

    /**
     * Returns wheter the User is logged in or not
     *
     * @return boolean
     */
    public function isLoggedIn() {
        return $this->getID() !== 0;
    }

    /**
     * Set User Variable (user_detail)
     *
     * @param string $name key
     * @param string $data var
     *
     * @return void
     */
    public function setVar($name, $data) {
        $_SESSION['user'][$name] = $data;
        $this->userData[$name] = $data;
    }

    /**
     * Get User Variable
     *
     * @param object $name key
     *
     * @return string/null
     */
    public function getVar($name) {
        if (isset($this->userData[$name])) {
            return $this->userData[$name];
        }
        return null;
    }

    /**
     * Get all information of a user
     *
     * @return array can return null if userData is empty.
     */
    public function getUserData() {
        return (!empty($this->userData)) ? $this->userData : null;
    }

    /**
     * Logs a user in, session creation..
     *
     * @param string $user   Email of User
     * @param string $passwd Password of User
     *
     * @return StatusHandler
     */
    public function login($user, $passwd) {
        $status = new StatusHandler(false);

        $q = $this->db->q(
            'SELECT userID, userLogin, userPass
            FROM %pusers
            WHERE userLogin = %s
            LIMIT 1',
            $user
        );

        if ($q->hasData()) {
            $data     = $q->getFirst();
            $password = $data->userPass;
            $this->id = $data->userID;

            // SaltEncryption
            $se = new SaltEncryption();
            if ($se->compare($passwd, $password)) {
                $this->loadUserData();
                $_SESSION['userid'] = $this->id;

                $status->setStatus(true);
                $status->addSuccess('Sie haben sich erfolgreich angemeldet.');
                $this->fireEvent('login');
            } else {
                $status->setStatus(false);
                $status->addError('Das Kennwort ist leider falsch.');

                session_destroy();
            }
        } else {
            $status->addError(
                'Ihre Logindaten existieren nicht.<br />
                Wenn Sie sich schon registriert haben, &uuml;berpr&uuml;fen
                Sie bitte Ihren Nutzernamen.'
            );
        } 

        return $status;
    }

    /**
     * Loads the user data
     *
     * @return void
     */
    private function loadUserData() {
        $sql = $this->db->q(
            'SELECT userID, userFirstName, userLastName, userLogin
            FROM %pusers
            WHERE userID = %i
            LIMIT 1',
            $this->id
        );
        if ($sql->hasData()) {
            $this->name = $sql->getFirst()->userLastName;
            $this->firstname = $sql->getFirst()->userFirstName;
            $this->loginName = $sql->getFirst()->userLogin;
        }
    }

    /**
     * Logs the current User out
     *
     * @return void
     */
    public function logout() {
        $this->userData = array();
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"],
                $params["domain"], $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        $this->fireEvent('logout');
    }

    /**
     * Returns wheter User with $id exists.
     *
     * @param integer $id User ID
     *
     * @return boolean
     */
    public function existUserById($id) {
        $sql = $this->db->q(
            'SELECT id FROM %pusers
            WHERE id = %s', $id
        );

        if ($sql && $this->db->numrows($sql) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param string  $email The email address
     * @param string  $s     Size in pixels, defaults to 80px [ 1 - 512 ]
     * @param string  $d     Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string  $r     Maximum rating (inclusive) [ g | pg | r | x ]
     * @param boolean $img   True to return a complete IMG tag False for just the URL
     * @param array   $atts  Optional, additional key/value attributes to include in the IMG tag
     *
     * @return string containing either just a URL or a complete image tag
     *
     * @source http://gravatar.com/site/implement/images/php/
     */
    public static function get_gravatar($email, $s = 80, $d = 'identicon', $r = 'g', $img = false, $atts = array()) {
        $url = 'https://secure.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }
        return $url;
    }
}

?>
