<?php

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
 
    // RIDs
    const RID_CREATE = '05e178e2';
    const RID_EDIT = '0cc42901';
    const RID_DELETE = '5ce78169';
    const RID_VIEW = 'fbd9b629';
    const RID_SUGGEST = 'd374a053';
    const RID_CHSTATUS = '9f27ce4d';
    const RID_LOGIN = '529608ee';
    const RID_REGISTER_NOTIFY = 'b4bde05b';
 
    /**
     * We should set it to private when there is no classes/content anymore which access directly.
     * @var array User Details for $uid
     */
    public $userData;

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

        if ($userId != null) {
            $this->loadUserData($userId);
        } else if (isset($_SESSION['userid'])) {
            $this->loadUserData($_SESSION['userid']);
        } else {
            $this->userData = array();
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
            // FIXME: hat hier absolut überhaupt nix und nimm zu suchen!!!
            if ($instance != null && Smarty_FLS::getInstance() != null) {
                $tpl = Smarty_FLS::getInstance();
                $tpl->assign('loggedin', self::$instance->isLoggedIn());
            }
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
    public function getUid($wAnonym = true) {
        $uid = null;
        if (isset($this->userData['id'])) {
            $uid = $this->userData['id'];
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
        return isset($this->userData['name']) ? $this->userData['name'] : null;
    }

    /**
     * Returns wheter the User is logged in or not
     *
     * @return boolean
     */
    public function isLoggedIn() {
        return $this->getUid() !== 0;
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

        $sql = $this->db->q(
            "SELECT id, status
            FROM %puser
            WHERE email='%s' OR username='%s'
            LIMIT 1",
            $user, $user
        );

        if ($sql && $this->db->numrows($sql) == 1) {
            $userData = $this->db->fetchrow($sql);

            if ($userData['status'] == 'banned') {
                $status->addError('Dieser Benutzeraccount ist deaktiviert.');
            } else if ($userData['status'] == 'waitadmin') {
                $status->addError(
                    'Unsere Administratoren &uuml;berpr&uuml;fen Ihre Eingaben und schalten Sie so
                    schnell wie m&ouml;glich frei.'
                );
            } else if ($userData['status'] == 'newpassword') {
                $status->addError(
                    'Sie haben ein neues Passwort angefordert. Bitte folgen Sie dem Link aus der E-Mail.'
                );
            } else {
                $status->addError('Sie haben einen unbekannten Status');
            }
        } else {
            $status->addError(
                'Ihre Logindaten existieren nicht.<br />
                Wenn Sie sich schon registriert haben, &uuml;berpr&uuml;fen
                Sie bitte Ihre E-Mail-Adresse bzw. Ihren Benutzername.'
            );
        }

        if ($status->getStatus()) {
            $sql = $this->db->q(
                "SELECT id, password
                FROM %puser
                WHERE (email='%s' OR username='%s')
                LIMIT 1",
                $user, $user
            );
            if ($sql && $this->db->numrows($sql) == 1) {
                $user_data = $this->db->fetchrow($sql);
                $uid = $user_data['id'];
                $password = $user_data['password'];

                // so it is easier.
                $status->setStatus(false);

                $updatePw = false;
                // Check password
                // SaltEncryption
                $se = new SaltEncryption();
                if ($se->compare($passwd, $password)) {
                    $status->setStatus(true);
                } else if (md5($passwd) == $password) {
                    $status->setStatus(true);
                    // Now we update the password to Salt!
                    $updatePw = true;
                }

                if ($status->getStatus()) {
                    $this->loadUserData($uid);
                    $_SESSION['userid'] = $uid;

                    // Update last Update
                    $sql = $this->db->q(
                        "UPDATE %puser
                        SET lastlogin='%s'
                        WHERE id='%s'",
                        time(), $uid
                    );

                    //we do this here, because the data should be loaded
                    if ($updatePw) {
                        $this->updatePassword($uid, $passwd, $passwd, false);
                    }

                    $status->setStatus(true);
                    $status->addSuccess('Sie haben sich erfolgreich angemeldet.');
                    $this->fireEvent('login');
                } else {
                    $status->setStatus(false);
                    $status->addError('Das Kennwort ist leider falsch.');
                    $status->addError(
                        'Wenn Sie sich ein neues Kennwort zuschicken lassen m&ouml;chten, klicken Sie
                        bitte <a href="user/new_password">hier</a>.'
                    );

                    session_destroy();
                }
            } else {
                $status->setStatus(false);
                $status->addError('Das Kennwort konnte nicht geladen werden.');
                session_destroy();
            }
        }
        return $status;
    }

    /**
     * Loads the user data
     *
     * @param integer $userId ... yeah right.. the User ID :D
     *
     * @return void
     */
    private function loadUserData($userId) {
        $sql = $this->db->q(
            "SELECT u.id, u.firstname, u.lastname, concat(u.firstname,' ', u.lastname) AS name,
            u.geb, u.email, u.status, o.show_email, o.send_news, o.prfchgnotify, o.history,
            u.lastlogin, u.username, l.street, l.postcode, l.village, l.country, l.phone, l.handy
            FROM %puser u
            LEFT JOIN %puser_detail l ON u.id=l.user_id
            LEFT JOIN %puser_options o ON u.id=o.uid
            WHERE u.id='%s'
            LIMIT 1",
            $userId
        );
        if ($sql && $this->db->numrows($sql) == 1) {
            $this->userData = array();
            foreach ($this->db->fetchrow($sql) as $key => $val) {
                $this->userData[$key] = $val;
            }

            $this->db->freeresult($sql);
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
     * Adds a User to the Database
     *
     * @param string  $email        E-Mail Address of User
     * @param string  $passwd       Password of User
     * @param string  $firstname    First Name of User
     * @param string  $lastname     Last Name of User
     * @param long    $geb          Birthday of User (UNIX Timestamp)
     * @param integer $send_news    Send news?
     * @param string  $title        Title of the person?
     * @param boolean $activate     Activate it directly?
     * @param array   $group        Groups
     * @param integer $advent       Advent Group ID
     * @param string  $username     Username of the user.
     * @param boolean $prfchgnotify Send notification on user profile change
     * @param boolean $supressMail  True if you not want to sent a mail, else false.
     * @param boolean $checkName    True if you not want to check, if there is still a user with the name and birthday
     *
     * @return StatusHandler data => uid
     */
    public function addUser(
        $email, $passwd, $firstname, $lastname, $geb, $send_news, $title, $activate, $group, $advent, $username,
        $prfchgnotify, $supressMail = false, $checkName = false
    ) {
        $status = new StatusHandler(true);

        $goOn = true;

        if ($checkName) {
            $res = $this->db->q(
                'SELECT id FROM %puser WHERE firstname LIKE %s AND lastname LIKE %s AND geb=%i', $firstname, $lastname,
                $geb
            );
            if ($this->db->numrows($res) > 0) {
                $row = $this->db->fetchrow($res);
                $status->setData($row['id']);
                $goOn = false;
                $this->db->freeresult($res);
            }
        }
        if ($goOn) {
            // Check if theres already a User with this E-Mail
            if ($email !== null) {
                $q = $this->db->q(
                    'SELECT email
                FROM %puser
                WHERE email LIKE %s', $email
                );
                if ($this->db->numrows($q) > 0) {
                    $this->db->freeresult($q);

                    $status->setStatus(false);
                    $status->addError('Diese E-Mail Adresse wird bereits verwendet.');
                }
            }

            if ($status->getStatus()) {
                $ush = $this->existUsernameForID(-1, $username);
                if (!$ush->getStatus()) {
                    $status->meltStatusHandler($ush);
                } else {
                    $validcode = md5(uniqid(rand(), true));

                    // create password
                    $se = new SaltEncryption();
                    $passwd = $se->hash($passwd);

                    $sql = $this->db->q(
                        'INSERT INTO %puser (password,firstname,lastname,geb,email,status,validcode,title,username)
                    VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)',
                        $passwd, $firstname, $lastname, $geb, $email, "waitadmin", $validcode, $title, $username
                    );

                    if ($sql) {
                        //benutzer in anonymous gruppe stecken
                        $uid = $this->db->nextid($sql);
                        $registerMail = true;

                        $this->db->q(
                            'INSERT INTO %puser_options (uid,send_news,prfchgnotify)
                        VALUES(%i, %i, %b)',
                            $uid, $send_news, $prfchgnotify
                        );

                        if ($advent > 0) {
                            // add user to group
                            $group = new Group();
                            $group->addUser($uid, $advent);
                        }

                        if ($activate) {
                            $this->activateByUID($uid, $supressMail);
                            $registerMail = false;
                        }
                        //email verschicken
                        // commented out: 'group' => $group
                        $user = array(
                            'firstname' => $firstname, 'lastname' => $lastname, 'geb' => $geb, 'email' => $email,
                            'news' => $send_news, 'advent' => $advent
                        );
                        if (!$supressMail) {
                            $this->sendRegistrationMail(
                                array('name' => $firstname . ' ' . $lastname, 'address' => $email), $user, $validcode,
                                !$registerMail
                            );
                        }

                        $status->setStatus(true);
                        $status->addSuccess('Benutzer erfolgreich angemeldet');
                        $status->setData($uid);
                    } else {
                        $status->setStatus(false);
                        $status->addError('Benutzer konnte nicht eingetragen werden');
                    }
                }
            }
        }

        return $status;
    }

    /**
     * Updates the email adress of the user
     * 
     * @param string $newMail the new email
     * 
     * @return StatusHandler 
     */
    public function updateMail($newMail) {
        $sh = new StatusHandler();
        $db = Database2::getInstance();
        $res = $db->q('SELECT id FROM %puser WHERE email=%s', $newMail);
        if ($res->hasData()) {
            if ($res->getFirst()->id != $this->getUid()) {
                $sh->addError('Es verwendet bereits ein anderer Benutzer diese E-Mail-Adresse!');
                $sh->setStatus(false);
            }
        } else {
            $sh->meltStatusHandler($db->q('UPDATE %puser SET email=%s WHERE id=%i', $newMail, $this->getUid()));
        }
        return $sh;
    }

    /**
     * verschickt Benutzerregistierungen E-Mails ab, sowohl an den Benutzer als auch an die Admins
     *
     * @param string  $to         Zu welcher Adresse soll etwas verschickt werden
     * @param string  $user       Zu welchem Benutzernamen soll die Mail verschickt werden
     * @param string  $validcode  zum Benutzer gehoerende ID zur Aktivierung
     * @param boolean $onlyAdmins true, if there should not been send an register mail but only an email to
     * the "admins".
     *
     * @return StatusHandler
     */
    public function sendRegistrationMail($to, $user, $validcode, $onlyAdmins = false) {
        $tpl = Smarty_FLS::getInstance();

        $status = new StatusHandler();
        $email = null;

        if (!$onlyAdmins) {
            $email = new EMail();
            $email->setSubject('FLS-Website: Benutzerregistrierung');
            $tpl->assign('euser', $user);
            $mail = $tpl->fetch('email/register_user.tpl');
            $email->setHtmlmessage($mail);
            $email->build(0);
            $reg_mail = $email->send($to);

            $status->meltStatusHandler($reg_mail);
        } else {
            $tpl->assign('activated', true);
        }
        //Mail an die Admins, damit die aussortieren können
        $email = new EMail();
        $tpl->assign('euser', $user);
        $tpl->assign('validcode', $validcode);
        $mail = $tpl->fetch('email/register_admin.tpl');
        $email->setSubject('FLS-Website: Neuer Benutzer');
        $email->setHtmlmessage($mail);
        $email->build(1);

        $mail_admins = $this->getEMailListWithSpecificRight(RID_USER_REGISTER_NOTIFY);
        $mail_admins = $mail_admins->getData();

        $adm_mail = $email->sendMulti($mail_admins);
        $status->meltStatusHandler($adm_mail);

        return $status;
    }

    /**
     * Lists all User
     *
     * @param boolean $onlyID only the user ids in the array given back
     * @param boolean $wpage  With or without Page Informations?
     * @param array   $filter Filter the list
     * @param array   $order  Order by
     * @param integer $page   Page
     * @param integer $num    Number of users per site (0 = all)
     *
     * @return StatusHandler
     */
    public function userList($onlyID = false, $wpage = false, $filter = array(), $order = null, $page = 0, $num = 30) {
        $status = new StatusHandler();
        $qWData = array();
        $w = '';
        if (count($filter) > 0) {
            $w = ' WHERE ';
            if (isset($filter['search']) && !empty($filter['search'])) {
                $w .= ' u.firstname LIKE %l OR u.lastname LIKE %l OR u.email LIKE %l';
                $qWData = array_merge($qWData, array($filter['search'], $filter['search'], $filter['search']));
            }
            // Nun kommt News
            if (isset($filter['news']) && $filter['news'] != 'null') {
                if ($w != ' WHERE ') {
                    $w.= ' AND';
                }
                $w.= ' o.send_news = %b';
                $qWData[] = $filter['news'];
            }

            // Nun kommt E-Mail
            if (isset($filter['email']) && $filter['email'] != 'null') {
                if ($w != ' WHERE ') {
                    $w.= ' AND';
                }
                $w.= ' o.show_email = %b';
                $qWData[] = $filter['email'];
            }

            // Nun kommt Status
            if (isset($filter['status']) && $filter['status'] != 'null') {
                if ($w != ' WHERE ') {
                    $w.= ' AND';
                }
                $w.= ' u.status = %s';
                $qWData[] = $filter['status'];
            }

            if ($w == ' WHERE ') {
                $w = '';
            }
        }
        // Order by
        $orderby = 'u.`lastname` ASC, u.`firstname` ASC';
        $qOData = array();
        if ($order != null && is_array($order)) {
            // Jetzt muss ich noch interpretieren und translaten ;-)
            switch ($order[0]) {
                case 'Vorname':
                    $order[0] = 'u.firstname';
                    break;
                case 'Benutzername':
                    $order[0] = 'u.username';
                    break;
                case 'Nachname':
                default:
                    $order[0] = 'u.lastname';
                    break;
            }
            $orderby = '%s %s';
            $qOData[] = $order[0];
            $qOData[] = $order[1];
        }

        $max = -1;
        $statement = 'SELECT u.id, u.firstname, u.lastname, u.geb, CONCAT_WS(" ",u.firstname, u.lastname) AS `name`,
            u.email, u.status, u.username
            FROM %puser u
            LEFT JOIN %puser_options o ON u.id=o.uid ' . $w . '
            ORDER BY ' . $orderby;

        $qLData = array();
        if (!$onlyID && $num != 0) {
            $statement .= ' LIMIT %i,%i';
            $qLData[] = $page * $num;
            $qLData[] = $num;
            $st = $this->db->q('SELECT count(id) as num from %puser u' . $w, $qWData);
            $st = $this->db->fetchrow($st);
            $max = $st['num'];
        }

        $sql = $this->db->q($statement, array_merge($qWData, $qOData, $qLData));
        if ($max == -1) {
            $max = $this->db->numrows($sql);
        }
        if ($max > 0) {
            if ($onlyID) {
                $result = array();
                while ($row = $this->db->fetchrow($sql)) {
                    $result[] = $row['id'];
                }
                $this->db->freeresult($sql);
            } else {
                $result = $this->db->fetchrowset($sql);
            }

            // Page Informations
            if ($wpage) {
                $pages = array();
                $n = ceil($max / $num);
                for ($i = 0; $i < $n; $i++) {
                    if ((($page - 5) < $i || ($page + 5) > $i) || $page == 0 && ($page + 10) > $i) {
                        $pages[] = $i;
                    }
                }

                $pages = array('pages' => $pages, 'numP' => ceil($max / $num), 'entries' => $n, 'actual' => $page);
            }

            $status->setData(($wpage) ? array($result, $pages) : $result);
            $status->setStatus(true);
        } else {
            $status->setStatus(false);
        }

        return $status;
    }

    /**
     * Converts a list of user ids to a list of names
     *
     * @param array $uids the list of user ids
     * 
     * @return array the list of names
     */
    public static function convertUidToNames(array $uids) {
        $status = new StatusHandler();
        $db = Database::getInstance();

        $sql = $db->q(
            'SELECT firstname, lastname
            FROM %puser 
            WHERE id IN %t', implode(',', $uids)
        );

        if ($sql && $db->numrows() > 0) {
            while ($res = $db->fetchrow()) {
                $tmp = $res['firstname'] . ' ' . $res['lastname'];
                $status->addData($tmp);
            }

            $status->setStatus(true);
            $db->freeresult($sql);
        }

        return $status;
    }

    /**
     * Searches in the Database for $name, returns the uid
     *
     * @param string $name Namen der Benutzer
     *
     * @return StatusHandler data = uid
     */
    public static function searchUser($name) {
        $status = new StatusHandler();
        $db = Database::getInstance();

        $name = explode(' ', $name);
        if (count($name) < 2) {
            $status->setStatus(false);
            $status->addError('Kein g&uuml;ltiger Name!');
        } else {
            $sql = $db->q(
                'SELECT id FROM %puser
                WHERE `firstname` LIKE %s  AND `lastname` LIKE %s', $name[0],
                $name[1]
            );
            $c = $db->numrows($sql);
            if ($c == 1) {
                $row = $db->fetchrow($sql);
                $db->freeresult($sql);

                $status->setStatus(true);
                $status->setData($row['id']);
                $status->addSuccess('G&uuml;ltiger Name!');
            } elseif ($c > 1) {
                $status->setStatus(false);
                $status->addError('Leider nicht eindeutig');
            } else {
                $status->setStatus(false);
                $status->addError('Kein Eintrag gefunden');
            }
        }

        return $status;
    }

    /**
     * Search for a User by Database Field
     *
     * @param string  $field Fieldname
     * @param string  $value Fieldvalue (Conditions)
     * @param boolean $like  Use wildcards?
     *
     * @return StatusHandler data = Database Result
     */
    public static function searchUserByOption($field, $value, $like = false) {
        $status = new StatusHandler();

        $sql = $this->db->q(
            'SELECT * FROM %puser_options
            WHERE %s' . ($like ? 'LIKE' : '=') . '%s', $field, $value
        );
        if ($sql && $this->db->numrows($sql) > 0) {
            $res = $this->db->fetchrowset($sql);
            $status->setStatus(true);
            $status->setData($res);
        } else {
            $status->setStatus(false);
        }

        return $status;
    }

    /**
     * Search for User with $name
     *
     * @param string $name Benutzernamen
     *
     * @return StatusHandler data => array(firstname, lastname)
     */
    public function suggestUser($name) {
        $status = new StatusHandler(false);
        $sql = $this->db->q(
            'SELECT `firstname`, `lastname`, `id`, `geb`
            FROM %puser
            WHERE CONCAT_WS(" ",`firstname`, `lastname`) LIKE %l
            ORDER BY lastname ASC, firstname ASC',
            $name
        );
        if ($this->db->numrows($sql) > 0) {
            $status->setStatus(true);
            $status->setData($this->db->fetchrowset($sql));
        }

        return $status;
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
            'SELECT id FROM %puser
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
    function get_gravatar($email, $s = 80, $d = 'identicon', $r = 'g', $img = false, $atts = array()) {
        $url = 'http://www.gravatar.com/avatar/';
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

    /**
     * Changes the Status of a User
     * (active = active)
     * (waitadmin = wait for admin)
     * (newpassword = requested a new password)
     * (banned = banned)
     *
     * @param boolean $status new Status
     * @param integer $uid    User ID
     *
     * @return StatusHandler
     */
    public function setStatus($status, $uid) {
        $sh = new StatusHandler();

        $sql = $this->db->q(
            'UPDATE `%puser` SET `status` = "%s"
            WHERE `id` = %s', $status, $uid
        );

        if ($sql) {
            $sh->addSuccess('Status erfolgreich ver&auml;ndert.');
            $sh->setStatus(true);
        } else {
            $sh->addError('Status konnte nicht ver&auml;ndert werden.');
            $sh->setStatus(false);
        }

        return $status;
    }

    /**
     * Returns the state of User
     *
     * @param integer $uid User ID
     *
     * @return StatusHandler (true if active)
     */
    public function getStatus($uid) {
        $status = new StatusHandler();

        $sql = $this->db->q(
            'SELECT status FROM %puser
            WHERE `id` = %i
            LIMIT 1', $uid
        );
        $c = $this->db->numrows($sql);
        if ($c == 1) {
            $row = $this->db->fetchrow($sql);
            $this->db->freeresult($sql);

            if ($row['status'] == 'activ') {
                $status->addSuccess("Konto ist aktiviert.");
                $status->setStatus(true);
            } else {
                $status->addError("Das Benutzerkonto ist noch nicht aktiv.");
                $status->setStatus(false);
            }
        } else {
            $status->addError("Status des Benutzerkontos konnte nicht abgefragt werden.");
            $status->setStatus(false);
        }

        return $status;
    }

    /**
     * Activates a User by a validcode
     *
     * @param string $validcode Validation Code
     *
     * @return StatusHandler
     */
    public function activate($validcode) {
        $status = new StatusHandler();

        $q = $this->db->q(
            'SELECT id FROM %puser
            WHERE validcode LIKE %s
            LIMIT 1', $validcode
        );
        if ($this->db->numrows($q) > 0) {
            $id = $this->db->fetchrow($q);
            $this->db->freeresult($q);
            $id = $id['id'];

            return $this->activateByUID($id);
        }

        $status->addError('Diesen Benutzer gibt es nicht oder der Benutzer wurde bereits aktiviert.');
        $status->setStatus(false);

        return $status;
    }

    /**
     * Activates a User by ID
     *
     * @param integer $uid         UserID
     * @param boolean $supressMail true if you do not want a mail to be sent.
     *
     * @return StatusHandler
     */
    public function activateByUID($uid, $supressMail = false) {
        $status = new StatusHandler();

        if ($this->getStatus($uid)->getStatus() == false) {
            if ($this->setStatus('activ', $uid)) {
                //validcode entfernen
                $this->db->q('UPDATE %puser SET validcode = "" WHERE id LIKE %i', $uid);
                //email an benutzer verschicken
                if (!$supressMail) {
                    $this->userActivMail($uid);
                }

                $status->addSuccess("User erfolgreich aktiviert.");
                $status->setStatus(true);
            } else {
                $status->addError('Status konnte nicht auf aktiv gesetzt werden.');
                $status->setStatus(false);
            }
        } else {
            $status->addError('Status ist nicht wie erwartet.');
            $status->setStatus(false);
        }

        return $status;
    }

    /**
     * Sends an email to User after his Activation
     *
     * @param integer $uid BenutzerID
     *
     * @return StatusHandler
     */
    public function userActivMail($uid) {
        $tpl = Smarty_FLS::getInstance();

        $status = new StatusHandler();

        //adresse aus db holen
        $q = $this->db->q(
            'SELECT CONCAT_WS(" ", firstname, lastname) AS name, email AS address
            FROM %puser
            WHERE id LIKE %i
            LIMIT 1',
            $uid
        );
        if ($this->db->numrows($q) > 0) {
            $to = $this->db->fetchrow($q);
        } else {
            $status->addError("Dieser Benutzer existiert nicht.");
            $status->setStatus(false);

            return $status;
        }

        //email vorbereiten
        $email = new EMail();
        $email->setSubject('FLS-Website: Ihr Account wurde aktiviert');
        $tpl->assign('euser', $to);
        $mail = $tpl->fetch('email/activate_user.tpl');
        $email->setHtmlmessage($mail);
        $email->build(0);

        //abschicken
        $error = $email->send($to);

        $status->meltStatusHandler($error);

        return $status;
    }

    /**
     * deactivates an user by the validcode from the database.
     * Let the mail address in the database to prevent more registrations
     *
     * @param string $validcode Valid Code
     *
     * @return StatusHandler Status
     */
    public function deleteByV($validcode) {
        $sh = new StatusHandler();

        $q = $this->db->q('SELECT id FROM %puser WHERE validcode LIKE %s LIMIT 1', $validcode);
        if ($this->db->numrows($q) > 0) {
            $id = $this->db->fetchrow($q);
            $this->db->freeresult($q);
            $id = $id['id'];
            if ($this->setStatus('banned', $id)) {
                //validcode entfernen
                $this->db->q('UPDATE %puser SET validcode = "" WHERE id LIKE %i', $id);

                $sh->addSuccess('Benutzer erfolgreich gel&ouml;scht.');
                $sh->setStatus(true);
            }
            $sh->addError('Benutzer konnte nicht gel&ouml;scht werden.');
        } else {
            $sh->addError('Diesen Benutzer gibt es nicht');
        }

        return $sh;
    }

    /**
     * Ban a User
     *
     * @param integer $uid User ID
     *
     * @return StatusHandler
     */
    public function deactivateByUID($uid) {
        $status = new StatusHandler();

        if ($this->setStatus('banned', $uid)) {
            $this->userDeavtivMail($uid);

            $status->addSuccess("User erfolgreich gebannt.");
            $status->setStatus(true);
        } else {
            $status->addError("User konnte nicht gebannt werden");
            $status->setStatus(false);
        }

        return $status;
    }

    /**
     * Sends a Ban Notification to User
     *
     * @param integer $uid User Id
     *
     * @return StatusHandler
     */
    public function userDeavtivMail($uid) {
        $tpl = Smarty_FLS::getInstance();
        $status = new StatusHandler();

        //adresse aus db holen
        $q = $this->db->q(
            'SELECT CONCAT_WS(" ", firstname, lastname) AS name, email AS address
            FROM %puser
            WHERE id LIKE %i
            LIMIT 1',
            $uid
        );
        if ($this->db->numrows($q) > 0) {
            $to = $this->db->fetchrow($q);
        } else {
            $status->setStatus(false);
            $status->addError("Benutzer existiert nicht.");
            return $status;
        }

        //email vorbereiten
        $email = new EMail();
        $email->setSubject('FLS-Website Ihr Account wurde deaktiviert');
        $tpl->assign('euser', $to);
        $mail = $tpl->fetch('email/deactivate_user.tpl');
        $email->setHtmlmessage($mail);
        $email->build(0);

        //abschicken
        $error = $email->send($to);
        $status->meltStatusHandler($error);

        return $status;
    }

    /**
     * Deletes a User
     *
     * @param integer $uid User ID
     *
     * @return StatusHandler gives Statusmessage
     */
    public function deleteUser($uid) {
        $status = new StatusHandler();

        $count_rows = 0;
        $this->db->q('DELETE FROM %puser_group WHERE uid = %i', $uid);
        $count_rows += $this->db->numrows();
        $this->db->q('DELETE FROM %puser_right WHERE uid = %i', $uid);
        $count_rows += $this->db->numrows();
        $q = $this->db->q('DELETE FROM %puser_detail WHERE user_id = %i', $uid);
        $count_rows += $this->db->numrows();
        $q = $this->db->q('DELETE FROM %plkwahl_user_class WHERE uid = %i', $uid);
        $count_rows += $this->db->numrows();
        $q = $this->db->q('DELETE FROM %plkwahl_result WHERE uid = %i', $uid);
        $count_rows += $this->db->numrows();
        $q = $this->db->q('DELETE FROM %plkwahl_result_subject WHERE uid = %i', $uid);
        $count_rows += $this->db->numrows();
        $q = $this->db->q('DELETE FROM %pform_user WHERE uid = %i', $uid);
        $count_rows += $this->db->numrows();
        $q = $this->db->q('DELETE FROM %puser_options WHERE uid = %i', $uid);
        $count_rows += $this->db->numrows();
        $q = $this->db->q('DELETE FROM %puser WHERE id = %i', $uid);
        $count_rows += $this->db->numrows();

        $status->setStatus($q);

        if ($q) {
            $status->addSuccess('Account erfolgreich gel&ouml;scht.');
        } else {
            $status->addError('Account konnte nicht gel&ouml;scht werden.');
        }

        return $status;
    }

    /**
     * Lists all Admins, important for User Registration
     *
     * @param boolean $redaktion sends only to the editorial staff?
     *
     * @return StatusHandler data = array(name, address)
     *
     * @deprecated Used by: Calendar.class.php, NewsManager.class.php
     */
    public function getAdmins($redaktion = false) {
        $status = new StatusHandler();

        $admingroup = (
            ($redaktion) ? $this->conf->admin->Admin_Redaktion->value : $this->conf->admin->Admin_Group_Number->value
            );
        $adminright = 1;
        $result = array();
        $q = $this->db->q(
            'SELECT CONCAT_WS(" ",u.firstname, u.lastname) AS name,u.email AS address
            FROM %puser AS u
            LEFT JOIN %puser_group AS ug ON ug.uid = u.id
            LEFT JOIN %puser_right AS ur ON ur.uid = u.id
            WHERE (ug.gid LIKE %i OR ur.rid LIKE %i) AND u.status = "activ"',
            $admingroup, $adminright
        );
        if ($this->db->numrows($q) > 0) {
            while ($r = $this->db->fetchrow($q)) {
                if ($r['address'] != '') {
                    $result[] = $r;
                }
            }
        }

        $status->setStatus(true);
        $status->setData($result);

        return $status;
    }

    /**
     * Verifies and Updates User Detail and Basic Data
     *
     * @param integer $uid  User ID
     * @param array   $data Array of User Data | array('firstname', 'lastname', 'username'...)
     *
     * @return StatusHandler
     */
    public function verifyAndUpdateUser($uid, $data) {
        $status = new StatusHandler(true);

        if (mb_strlen($data['firstname']) < 1) {
            $status->addError('Bitte geben Sie einen Vornamen ein.');
            $status->setStatus(false);
        }

        if (mb_strlen($data['lastname']) < 1) {
            $status->addError('Bitte geben Sie einen Nachnamen ein.');
            $status->setStatus(false);
        }

        if (!preg_match("/^[a-z0-9\å\ä\ö._-]+@[a-z0-9\å\ä\ö.-]+\.[a-z]{2,6}$/i", $data['email'])) {
            $status->addError('Bitte &uuml;berpr&uuml;fen Sie Ihre E-Mail Adresse.');
            $status->setStatus(false);
        }

        if (!empty($data['phone']) && !preg_match('/^\+?(\d*)(\/| |-)?((\d*)( |\/|-)?)*(\d)$/si', $data['phone'])) {
            $status->addError('Bitte &uuml;berpr&uuml;fen Sie Ihre Telefonnummer.');
            $status->setStatus(false);
        }

        if (!empty($data['handy']) && !preg_match('/^\+?(\d*)(\/| |-)?((\d*)( |\/|-)?)*(\d)$/si', $data['handy'])) {
            $status->addError('Bitte &uuml;berpr&uuml;fen Sie Ihre Handynummer.');
            $status->setStatus(false);
        }

        if (!empty($data['postcode']) &&
            (!preg_match("/^\d+$/si", $data['postcode']) ||
            ($data['country'] == 'Deutschland' && mb_strlen($data['postcode']) != 5))
        ) {
            $status->addError('Bitte &uuml;berpr&uuml;fen Sie Ihre Postleitzahl.');
            $status->setStatus(false);
        }

        if (isset($data['username']) && !empty($data['username'])) {
            $sh = $this->existUsernameForID($uid, $data['username']);
            if (!$sh->getStatus() && ($this->userData['username'] != $data['username'])) {
                $status->addError('Dieser Benutzername ist bereits belegt.');
                $status->setStatus(false);
            }
        }

        if ($status->getStatus()) {
            // Still no Errors
            // Basic Info
            $r1 = $this->db->q(
                'UPDATE %puser SET firstname = "%s", lastname = "%s", username = "%s", email = "%s" WHERE id = "%s"',
                $data['firstname'], $data['lastname'], $data['username'], $data['email'], $uid
            );

            // Detail Info
            if ($r1) {
                $sh = $this->setUserDetail(
                    $uid, $data['street'], $data['postcode'], $data['village'], $data['country'], $data['phone'],
                    $data['handy']
                );

                if (!$sh->getStatus()) {
                    $status->setStatus(false);
                    $status->meltStatusHandler($sh);
                } else {
                    $updateOptions = $this->updateUserOptions(
                        $uid, $data['send_news'] == 'true' ? true : false, false,
                        $data['prfchgnotify'] == 'true' ? true : false, $data['history'] == 'true' ? true : false
                    );

                    if ($updateOptions->getStatus()) {
                        $status->addSuccess('Benutzerprofil erfolgreich aktualisiert.');
                        $status->setStatus(true);
                    } else {
                        $status->setStatus(false);
                        $status->meltStatusHandler($updateOptions);
                    }
                }
            } else {
                $status->addError('Benutzerprofil konnte nicht aktualisiert werden.');
                $status->setStatus(false);
            }
        }

        return $status;
    }

    /**
     * Update the User Options
     *
     * @param integer $uid          User ID
     * @param boolean $sendNews     Send news? 1 => true, 0 => false
     * @param boolean $showEmail    E-Mail is public? 1 => true, 0 => false
     * @param boolean $prfchgnotify send notification on user profile change?
     * @param boolean $history      enable/disable history of user
     *
     * @return StatusHandler
     */
    public function updateUserOptions($uid, $sendNews, $showEmail, $prfchgnotify, $history) {
        $status = new StatusHandler();

        //Insert is only needed if the user is already registered. New user always have the entry.
        $sql = $this->db->q(
            "INSERT INTO %puser_options(uid,send_news,show_email,prfchgnotify, history)
            VALUES(%i,%b,%b,%b,%b)
            ON DUPLICATE KEY UPDATE send_news=%b, show_email=%b, prfchgnotify=%b, history=%b",
            $uid, $sendNews, $showEmail, $prfchgnotify, $history, $sendNews, $showEmail, $prfchgnotify, $history
        );

        $status->setStatus($sql);
        $status->addSuccess('Benutzeroptionen wurden erfolgreich aktualisiert.');
        $status->addError('Benutzeroptionen konnten nicht aktualisiert werden.');

        return $status;
    }

    /**
     * Inserts a new Row of User Details. If there already exist one, the data is updated.
     *
     * @param integer $uid      User ID
     * @param string  $street   Street
     * @param integer $postcode Postcode
     * @param string  $village  Village
     * @param string  $country  Country
     * @param string  $phone    Phone number
     * @param string  $handy    Mobile phone number
     *
     * @return StatusHandler
     */
    public function setUserDetail($uid, $street, $postcode, $village, $country, $phone, $handy) {
        $status = new StatusHandler();

        $sql = $this->db->q(
            'INSERT INTO %puser_detail(street,postcode,village,country,phone,handy,user_id) VALUES(%s,%s,%s,%s,%s,%s,%i)
            ON DUPLICATE KEY UPDATE street=VALUES(street), postcode=VALUES(postcode), village=VALUES(village),
                                    country=VALUES(country), phone=VALUES(phone), handy=VALUES(handy)',
            $street, $postcode, $village, $country, $phone, $handy, $uid
        );

        if ($sql) {
            $status->addSuccess('Benutzerdaten wurden erfolgreich gespeichert.');
            $status->setStatus(true);
        } else {
            $status->addError('Benutzerdaten konnten nicht gespeichert werden.');
            $status->setStatus(false);
        }

        return $status;
    }

    /**
     * Sets only the phone without affecting other detail information
     * 
     * @param string $newPhone the new phone number
     * 
     * @return StatusHandler
     */
    public function setPhone($newPhone) {
        return $this->setUserDetail(
            $this->getUid(), $this->userData['street'], $this->userData['postcode'], $this->userData['village'],
            $this->userData['country'], $newPhone, $this->userData['handy']
        );
    }

    /**
     * Returns wheter User already has a Details Row
     *
     * @param integer $uid User ID
     *
     * @return StatusHandler
     */
    public function existUserDetailsForID($uid = false) {
        $status = new StatusHandler();

        if ($uid) {
            $uid = $this->getUid();
        }

        $s = $this->db->q('SELECT user_id FROM %puser_detail WHERE user_id = %i', $uid);
        if ($s && $this->db->numrows($s) > 0) {
            $status->addSuccess('Es existieren Detailangaben bei diesem Benutzer.');
            $status->setStatus(true);
        } else {
            $status->addSuccess('Es existieren keine Detailangaben bei diesem Benutzer.');
            $status->setStatus(false);
        }

        return $status;
    }

    /**
     * Checks whether there is the data to send the user a letter
     *
     * @param integer $uid the user id
     *
     * @return StatusHandler
     */
    public function existAdressForID($uid = false) {
        $status = new StatusHandler();

        if ($uid === false) {
            $uid = $this->getUid();
        }

        $s = $this->db->q('SELECT street,postcode,village,country FROM %puser_detail WHERE user_id = %i', $uid);
        $s = $this->db->fetchrow($s);
        if (
            $s !== false && !empty($s['street']) && !empty($s['postcode'])
            && !empty($s['village']) && !empty($s['country'])
        ) {
            $status->addSuccess('Es existiert die Anschrift bei diesem Benutzer.');
            $status->setStatus(true);
        } else {
            $status->addSuccess('Es existieren keine Anschrift bei diesem Benutzer.');
            $status->setStatus(false);
        }

        return $status;
    }

    /**
     * Update User by admin
     *
     * @param integer $uid  User ID
     * @param array   $data firstname, lastname, email, geburtstag, status, send_news
     *
     * @return StatusHandler
     *
     * @todo Theo: somewhat is broken here.
     */
    public function updateUserByAdmin($uid, $data) {
        $status = new StatusHandler(true);

        if (mb_strlen($data['user_firstname']) < 1) {
            $status->setStatus(false);
            $status->addError('Bitte geben Sie einen Vornamen ein');
        }
        if (mb_strlen($data['user_lastname']) < 1) {
            $status->setStatus(false);
            $status->addError('Bitte geben Sie einen Nachnamen ein');
        }
        if (!preg_match("/^[a-z0-9\å\ä\ö._-]+@[a-z0-9\å\ä\ö.-]+\.[a-z]{2,6}$/i", $data['user_email'])) {
            $status->setStatus(false);
            $status->addError('Bitte &uuml;berprüfen Sie die E-Mailadresse');
        }
        if (!isset($data['send_news'])) {
            $data['send_news'] = '0';
        }
        // Benutzername
        if (isset($data['user_username']) && !$this->existUsernameForID($uid, $data['user_username'])->getStatus()) {
            $status->setStatus(false);
            $status->addError('Der Benutzername ist bereits vergeben!');
        }
        // Geburtstag
        if (
            empty($_POST['date']) ||
            !checkdate($_POST['date']['Date_Month'], $_POST['date']['Date_Day'], $_POST['date']['Date_Year'])
        ) {
            $status->setStatus(false);
            $status->addError('Bitte &uuml;berprüfen Sie das Geburtstdatum');
        } else {
            $data['user_birthday'] = mktime(
                0, 0, 0, $_POST['date']['Date_Month'], $_POST['date']['Date_Day'], $_POST['date']['Date_Year']
            );
        }

        if ($status->getStatus()) {
            if ($data['user_status'] == '-1') { // We will let it as it is.
                $data['user_status'] = $this->getVar('status'); // Yeah its for mysql important.
            }

            $q = $this->db->q(
                'UPDATE %puser SET
                firstname = %s,
                lastname = %s,
                email = %s,
                geb = %s,
                username = %s,
                status = %s
                WHERE id = %s',
                $data['user_firstname'], $data['user_lastname'], $data['user_email'], $data['user_birthday'],
                $data['user_username'], $data['user_status'], $uid
            );

            if ($q) {
                $updateOptions = $this->updateUserOptions(
                    $uid, $data['send_news'] == '1' ? true : false, false, $data['prfchgnotify'] == '1' ? true : false,
                    $data['history'] == '1' ? true : false
                );
                if ($updateOptions->getStatus()) {
                    $status->setStatus(true);
                    $status->addSuccess(
                        'Die Daten f&uuml;r ' . $data['user_firstname'] . ' ' . $data['user_lastname'] . ' wurden
                        aktualisiert.'
                    );
                } else {
                    $status->setStatus(false);
                    $status->messagesMerge($updateOptions);
                }
            } else {
                $status->setStatus(false);
                $status->addError(
                    'Die Daten f&uuml;r ' . $data['user_firstname'] . ' ' . $data['user_lastname'] . ' wurden nicht
                    aktualisiert.'
                );
            }
        }
        return $status;
    }

    /**
     * Returns wheter Username $uname is already taken (and not by %uid himself)
     *
     * @param integer $uid   User ID
     * @param string  $uname Username
     *
     * @return StatusHandler
     */
    public function existUsernameForID($uid, $uname) {
        $status = new StatusHandler();

        // Existiert Benutzername fuer UID?
        $q = $this->db->q(
            'SELECT id FROM %puser WHERE username =\'%s\'', $uname
        );

        if ($q && $this->db->numrows() > 0) {
            $r = $this->db->fetchrow($q);
            if ($r['id'] == $uid) {
                $status->setStatus(true);
            } else {
                $status->setStatus(false);
                $status->addError('Benutzername bereits in Verwendung.');
            }
        } else if ($q) {
            $status->setStatus(true);
        } else {
            $status->setStatus(false);
            $status->addError('Benutzername konnte nicht abgefragt werden.');
        }

        return $status;
    }

    /**
     * Updates the Password for User $uid
     *
     * @param integer $uid           User ID
     * @param string  $old           Old Password
     * @param string  $new           New Password
     * @param boolean $checkForEqual should the password check for equal? (neccessary for salt update!)
     *
     * @return StatusHandler
     */
    public function updatePassword($uid, $old, $new, $checkForEqual = true) {
        $status = new StatusHandler();
        $se = new SaltEncryption();

        if ($old == $new && $checkForEqual) {
            $status->addInfo('Das neue Passwort ist genauso wie das alte.');
            $status->setStatus(true);
        } else {

            // Because we change to salt we have to check for both: salt and md5!
            $q = $this->db->q('SELECT id, password FROM %puser WHERE email=\'%s\'', $this->userData['email']);
            if ($this->db->numrows($q) > 0) {
                $passSQLresult = $this->db->fetchrow();
                $pass = $passSQLresult['password'];

                $pass_explode = explode(';', $pass);
                if (count($pass_explode) == 3) { // Okay.. its salt
                    if ($se->compare($old, $pass)) {
                        $status->setStatus(true);
                    }
                } else { // it have to be md5
                    if (md5($old) == $pass) {
                        $status->setStatus(true);
                    }
                }
            }

            if ($status->getStatus()) {
                // New is __always__ md5!
                $new = $se->hash($new);

                $q = $this->db->q('UPDATE %puser SET password = "%s" WHERE id = \'%s\'', $new, $uid);
                if ($q) {
                    $status->addSuccess('Passwort erfolgreich ge&auml;ndert.');
                    $status->setStatus(true);
                } else {
                    $status->addError('Passwort nicht erfolgreich ge&auml;ndert.');
                    $status->setStatus(false);
                }
            } else {
                $status->addError('Passwort nicht erfolgreich ge&auml;ndert.');
                $status->setStatus(false);
            }
        }

        return $status;
    }

    /**
     * first function for password reset
     * which is preparing all for a new password
     *
     * @param integer $uid User ID
     *
     * @return StatusHandler
     */
    public function newPasswordStep1($uid) {
        $tpl = Smarty_FLS::getInstance();
        $status = new StatusHandler();

        $validcode = substr(md5(uniqid('' . true)), 0, 33);

        //setzt den status um und setzt einen validcode
        $q = $this->db->q(
            'UPDATE %puser SET validcode = %s, status = "newpassword"
            WHERE id = %i', $validcode, $uid
        );
        //zuschicken des neuen Passworts
        $q = $this->db->q(
            'SELECT CONCAT_WS(" ", firstname, lastname) AS name, email AS address
            FROM %puser
            WHERE id LIKE %i
            LIMIT 1',
            $uid
        );
        if ($this->db->numrows($q) > 0) {
            $to = $this->db->fetchrow($q);
            $status->setStatus(true);
        } else {
            $status->addError('Dieser Benutzer existiert nicht.');
            $status->setStatus(false);
        }

        //email vorbereiten
        if ($status->getStatus()) {
            $email = new EMail();
            $email->setSubject('FLS-Website: Neues Kennwort');
            $tpl->assign('euser', array_merge($to, array('validcode' => $validcode)));
            $mail = $tpl->fetch('email/newpassword_verify.tpl');
            $email->setHtmlmessage($mail);
            $email->build(0);

            //abschicken
            $error = $email->send($to);
            $status->meltStatusHandler($error);

            if ($status->getStatus()) {
                $status->addSuccess('Neues Kennwort zugeschickt');
            } else {
                $status->addError('Es konnte kein Kennwort zugeschickt werden.');
            }
        }

        return $status;
    }

    /**
     * Second function for password reset
     * which checks the validcode and configures a new password
     *
     * @param string  $validcode Validcode
     * @param boolean $new       true if the new password should be send
     *
     * @return string
     */
    public function newPasswordStep2($validcode, $new = false) {
        $status = new StatusHandler();

        $q = $this->db->q(
            'SELECT id,status
            FROM %puser
            WHERE validcode LIKE \'%s\'
            LIMIT 1',
            $validcode
        );
        if ($this->db->numrows($q) > 0) {
            if ($new) {
                $row = $this->db->fetchrow($q);
                $this->db->freeresult($q);
                if ($row['status'] == 'newpassword') {
                    // Statustyp aendern
                    if ($this->newPasswordStep3($row['id'])->getStatus()) {
                        $status->setStatus(true);
                        $status->addSuccess('Neues Passwort wurde verschickt.');
                    } else {
                        $status->setStatus(false);
                        $status->addError(
                            'Das neue Passwort konnte nicht verschickt werden. Bitte wenden Sie sich an
                            einen Administrator, falls Sie sich nicht mehr anmelden k&ouml;nnen.'
                        );
                    }
                } else {
                    $status->setStatus(false);
                    $status->addError(
                        'Der Status Ihres Benutzerkontos stimmt nicht mehr &uuml;berein. Bitte wenden Sie
                        sich mit Ihrem Benutzernamen an einen Administrator.'
                    );
                }
            } else {
                $tm = $this->db->q(
                    'UPDATE %puser
                    SET validcode = "", status = "activ"
                    WHERE validcode LIKE %s',
                    $validcode
                );
                if ($tm) {
                    $status->setStatus(true);
                    $status->addSuccess('Sie k&ouml;nnen sich ab sofort wieder mit den alten Zugangsdaten anmelden.');
                } else {
                    $status->setStatus(false);
                    $status->addError(
                        'Das alte Passwort konnte nicht wiederhergestellt werden. Bitte kontaktieren Sie
                        einen Administrator, falls Sie sich nicht mehr anmelden k&ouml;nnen.'
                    );
                }
            }
        } else {
            $status->setStatus(false);
            $status->addError('Dieser Link ist nicht mehr g&uuml;ltig.');
        }

        return $status;
    }

    /**
     * sends a new password to the specific email of the given $uid and
     * write it to the database
     *
     * @param integer $uid User id
     *
     * @return boolean
     */
    public function newPasswordStep3($uid) {
        $tpl = Smarty_FLS::getInstance();

        $status = new StatusHandler(false);

        //neues Passwort generieren
        $password = substr(md5(uniqid('' . true)), 0, 10);
        // now hash it
        $se = new SaltEncryption();
        $passwd = $se->hash($password);

        //benutzer infos einholen
        $q = $this->db->q(
            'SELECT CONCAT_WS(" ", firstname, lastname) AS name, email AS address, status
            FROM %puser
            WHERE id = %i
            LIMIT 1',
            $uid
        );
        if ($this->db->numrows($q) > 0) {
            $row = $this->db->fetchrow($q);
            if ($row['status'] != 'banned') {
                //setzt den status um und setzt einen validcode
                $q = $this->db->q(
                    'UPDATE %puser SET
                    password = %s,
                    status = "activ",
                    validcode = ""
                    WHERE id = %i',
                    $passwd, $uid
                );
                //email verschicken
                if ($q) {
                    $to = array('name' => $row['name'], 'address' => $row['address']);
                    $email = new EMail();
                    $email->setSubject('FLS-Website: Ihr neues Passswort');
                    $tpl->assign('euser', $to);
                    $tpl->assign('euser_password', $password);
                    $mail = $tpl->fetch('email/newpassword_user.tpl');
                    $email->setHtmlmessage($mail);
                    $email->build(0);

                    //abschicken
                    $error = $email->send($to);
                    $status->setStatus(true);

                    $status->meltStatusHandler($error);

                    return $status;
                }
            }
        }

        return $status;
    }

    /**
     * sends a password via mail
     *
     * @param string $email E-Mail-Address
     *
     * @return StatusHandler
     */
    public function newPasswordByEmail($email) {
        $tpl = Smarty_FLS::getInstance();

        $status = new StatusHandler(true);

        $q = $this->db->q(
            'SELECT id
            FROM %puser
            WHERE email LIKE %s
            LIMIT 1', $email
        );
        if ($this->db->numrows($q) > 0) {
            $row = $this->db->fetchrow($q);
            $this->db->freeresult($q);
            $error = $this->newPasswordStep1($row['id']);

            $status->meltStatusHandler($error);

            if ($status->getStatus()) {
                $status->addInfo('Sie erhalten in K&uuml;rze eine E-Mail mit Ihren neuen Zugangsdaten.');
            }
        } else {
            $status->setStatus(false);
            $status->addError('Diese E-Mail Adresse existiert nicht.');
        }
        return $status;
    }

}

?>
