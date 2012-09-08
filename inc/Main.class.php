<?php
use \FLS\Lib\Configuration\Configuration as Configuration;
// Following files have to be loaded manually because of a weird behaviour of php
require_once('inc/Lib/Configuration/Entry.class.php');
require_once('inc/Lib/Configuration/Section.class.php');
require_once('inc/Lib/Configuration/Configuration.class.php');

/**
 * Main
 * It manages all neccessary prestartup, startup and poststartup routines.
 *
 * PHP Version 5.3
 *
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright 2012 Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @version   0.1
 */
class Main {

    /**
     * @var Main instance
     */
    private static $instance = null;

    /**
     * @var long unix timestamp for start time
     */
    private $starttime;

    /**
     * @var array redirectFilter
     */
    private $redirectFilter = array('user/login');

    /**
     * @var int how many redirects took place
     */
    private $redirectCount = 0;

    /**
     * @var int After how many redirects an error should be thrown (prevents an endless loop)
     */
    private $redirectLimit = 2;

    /**
     * @var string to check, if this redirect was made before
     */
    private $lastRedirectUrl = null;

    /**
     * The main tpl file
     * @var string the file
     */
    private $mainTpl = 'index.tpl';

    /**
     * The path to the tpl files
     * @var string the path
     */
    private $tpl_path = 'content/';

    /**
     * Constructor
     */
    public function Main() {
        $this->starttime = microtime(true);
        $this->lastRedirectUrl = isset($_GET['url']) ? $_GET['url'] : '';
        self::setInstance($this);
    }

    /**
     * Set a new Main
     *
     * @param Main $main Object of Main
     *
     * @return void
     */
    public static function setInstance(Main $main = null) {
        if (self::getInstance() == null || $main == null) {
            self::$instance = $main;
        }
    }

    /**
     * Get an object of Main
     *
     * @return Main return an object of Main
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * Set default error handler
     *
     * @return void
     */
    public function muteSmartyErrors() {
        if (!Configuration::getInstance()->admin->DEBUG->value) {
            Smarty_FLS::muteExpectedErrors();
        }
    }

    /**
     * The function is invokred before the normal (and repeatable) init function.
     * It does the following:
     * - creates the important objects, who should be alive, if there is an internal redirect
     * - Also here some options are set, which should be the same between redirects
     * - The data, who is recieved from the client, gets checked and cleaned
     * - The database connection gets established
     *
     * @return void
     */
    public function preInit() {
        setlocale(LC_ALL, 'de_DE', 'de_DE.UTF-8', 'de_DE.UTF-8@euro');
        // Set internal character encoding to UTF-8
        mb_internal_encoding("UTF-8");
        // Set timezone, specially for newer PHP Versions
        date_default_timezone_set("Europe/Berlin");

        //Auto-Load for files
        spl_autoload_register('Main::loadFiles');
 
        // New config!
        $conf = new Configuration(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../'));

        // We want debug messages ;-)
        if ($conf->admin->DEBUG->getValue()) {
            error_reporting(E_ALL);
        } 

        //Removes code from the recieved data, that could do damage
        if (get_magic_quotes_gpc()) {

            /**
             * Strips of slashes
             *
             * @param array|string $array with elements...
             *
             * @return array|string
             */
            function stripslashes_array($array) {
                return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
            }

            $_COOKIE = stripslashes_array($_COOKIE);
            $_FILES = stripslashes_array($_FILES);
            $_GET = stripslashes_array($_GET);
            $_POST = stripslashes_array($_POST);
            $_REQUEST = stripslashes_array($_REQUEST);
        }

        /**
         * Removes javascript from string
         *
         * @param array|string $array array or value
         *
         * @return array|string
         */
        function stripJavaScript_array($array) {
            return is_array($array) ? array_map('stripJavaScript_array', $array) : Content::strip_javascript($array);
        }

        $_COOKIE = stripJavaScript_array($_COOKIE);
        $_FILES = stripJavaScript_array($_FILES);
        $_GET = stripJavaScript_array($_GET);
        $_POST = stripJavaScript_array($_POST);
        $_REQUEST = stripJavaScript_array($_REQUEST);

        $db = new Database2(
                        $conf->datenbank->DBHOST->getValue(),
                        $conf->datenbank->DBUSER->getValue(),
                        $conf->datenbank->DBPASSWORD->getValue(),
                        $conf->datenbank->DBNAME->getValue(),
                        $conf->datenbank->persistent->getValue()
        );
        $db->setPrefix($conf->datenbank->DBPREFIX->getValue());
        if ($conf->default->utf8->getValue() == true) {
            $db->q('SET CHARACTER SET utf8;');
        }

        // Create new object of the session-class
        $_SESSION = new Session();
        // Change the save_handler to use the class functions
        session_set_save_handler(
                array(&$_SESSION, '_open'), array(&$_SESSION, '_close'), array(&$_SESSION, '_read'),
                array(&$_SESSION, '_write'), array(&$_SESSION, '_destroy'), array(&$_SESSION, '_gc')
        );
        session_start();

        // Global StatusHandler (should also work if the restart Method is invoked)
        StatusHandler::createGlobal();

        // Set error handler
        $this->muteSmartyErrors();
    }

    /**
     * Splits the URL
     *
     * @return void
     *
     * @global string $url
     */
    private function initUrl() {
        global $url;
        if (!isset($_GET['url'])) {
            $url = array(0 => '');
        } else {
            $_GET['url'] = rtrim($_GET['url'], '/');
            $url = explode('/', $_GET['url']);
        }
        $url[0] = ($url[0] == '' || $url[0] == 'home' || $url[0] == '/') ? 'home' : $url[0];
    }

    /**
     * This function starts the content creation. It loads all needed Data, creates the singelton objects and is the
     * main function of the website. It could be called more than one time for an internal redirect.
     *
     * @return void
     *
     * @global array $url
     */
    public function init() {
        global $url;
        $this->initUrl();
        $conf = Configuration::getInstance();
        $user = new User();

        $tpl = new Smarty_FLS(
                        $conf->smarty->templateDir->getValue(),
                        $conf->smarty->compileDir->getValue(),
                        $conf->smarty->cacheDir->getValue(),
                        $conf->smarty->configDir->getValue()
        );
        
        $content = new Content();

        //TPL init
        $tpl->assign('title', $conf->default->name->getValue());

        // We have to detect the file preamble
        $fileDir = strtolower($conf->default->filedir->value);
        $urlFlat = strtolower(implode('/', $url));

        //Here the context of the page is decided
        header("content-type: text/html; charset=utf-8");
        switch ($url[0]) {
            case 'res':
                break;
            default:
                //runtime
                $tpl->assign('runtime', (microtime(true) - $this->starttime));

                // Peak memory usage
                $tpl->assign('peakMemory', memory_get_peak_usage() / 1024 / 1024);

                //Content creation
                $content->buildContent($this->tpl_path);

                if ($this->mainTpl == 'index.tpl') {
                    $this->buildDefaultContext($tpl, $content, $conf, $user);
                }

                // Now send all messages:
                StatusHandler::getInstance()->sendToTemplate(true);

                // Now send all resources - We need this also for the external mode, otherwise it looks awful
                $content->sendSmartyFiles();

                $this->displayTemplate($tpl);

                $headers = apache_request_headers();
                $tpl->assign('donottrack', isset($headers['DNT']) && $headers['DNT'] == '1');
                break;
        }
    }

    /**
     * Build the content of the default context
     *
     * @param Smarty_FLS      $tpl     connection to templateengine
     * @param Content         $content connection to content
     * @param Configuration   $conf    connection to configuration
     * @param User            $user    connection to user
     *
     * @return void
     *
     * @global array $url the url
     */
    private function buildDefaultContext($tpl, $content, $conf, $user) {
        global $url;

        $content->addDefaultCss();
        $content->addDefaultJs();

        $this->buildDbError($conf, $tpl);
    }


    /**
     * Displays db errors and cleans the db
     *
     * @param Configuration $conf connection to configuration
     * @param Smarty_FLS    $tpl  connection to Templateengine
     *
     * @return void
     */
    private function buildDbError($conf, $tpl) {
        $db = Database2::getInstance();
        $debug = $conf->admin->DEBUG->getValue();
        if ($debug && count($db->getSqlErrors()) > 0) {
            echo "<pre>";
            print_r($db->getSqlErrors());
            echo "</pre>";
        }

        $tpl->assign('sql_queries', $db->getQueryNumber());
        $tpl->assign('debug', $debug);
    }

    /**
     * Shows the tpl file defined by the mainTpl attibute of the main class
     *
     * @param Smarty_FLS $tpl Templateengine
     *
     * @return void
     */
    private function displayTemplate($tpl) {
        try {
            $tpl->display($this->mainTpl);
        } catch (Exception $e) {
            trigger_error('Error on display page: ' . $e->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * This function does an internal rederict and recalls the init method.
     *
     * @param string $newURL the new url
     *
     * @return void
     */
    public function restart($newURL) {
        if ($this->redirectCount <= $this->redirectLimit && $newURL != $this->lastRedirectUrl) {
            // If $newURL ist not accessable, we want to go to the start page.
            $content = Content::getInstance();
            $tmpURL = explode('/', $newURL);
            if ($tmpURL[0] != '') {
                $newURL = 'home';
            }

            $this->redirectCount++;
            $this->lastRedirectUrl = $newURL;
            foreach ($GLOBALS as $key => $value) {
                if (strpos($key, '_') !== 0 && $key != 'GLOBALS') {
                    unset($GLOBALS[$key]);
                }
            }
            //Reset static instances
            Smarty_FLS::setInstance();
            Content::setInstance();
            User::setInstance();
            $i = 0;
            while ($i >= 0 && $i < count($this->redirectFilter)) {
                if ($newURL == $this->redirectFilter[$i]) {
                    $newURL = '';
                    $i = -1;
                }
                $i++;
            }
            $_GET['url'] = $newURL;
            $this->init();
            exit(0); // HRMPF @#!*$%$§)$§% ich __hasse__ es!
        } else if ($this->redirectCount <= $this->redirectLimit) {
            $this->restart('home');
        } else {
            trigger_error('Redirect limit of ' . $this->redirectLimit . ' in Main::restart() reached!', E_USER_NOTICE);
        }
    }

    /**
     * Go a page back
     *
     * @return void
     */
    public function back() {
        $url = '';
        if (isset($_SESSION['lastSite'])) {
            $url = $_SESSION['lastSite'];
        } else {
            $url = 'home';
        }
        $this->restart($url);
    }

    /**
     * Auto-Load function for the files of the website. Gets called automaticly by PHP.
     *
     * @param string $class_name the name of the class
     *
     * @return void
     */
    public static function loadFiles($class_name) {
        $load = '';
        // Check directory (because sometimes it's wrong, its known by php)
        if (getcwd() != Configuration::getInstance()->default->root->value) {
            chdir(Configuration::getInstance()->default->root->value);
        }

        // First we check if its with Namespaces:
        if (substr($class_name, 0, 4) == 'FLS\\') {
            $class_name = str_replace('\\', '/', substr($class_name, 4));
            $load = 'inc/' . $class_name . '.class.php';
        } else if (file_exists('inc/' . $class_name . '.class.php')) {
            $load = 'inc/' . $class_name . '.class.php';
        } else if (strstr($class_name, 'BoxContent')) {
            $load = 'content/box/' . $class_name . '.php';
        } else if (strstr($class_name, 'AdminContent')) {
            $load = 'content/admin/' . $class_name . '.php';
        } else if (substr($class_name, count($class_name) - 8) == 'Content' && $class_name != 'Content') {
            $load = 'content/' . $class_name . '.php';
        } else if ($class_name == 'Smarty') {
            $load = 'inc/smarty/' . $class_name . '.class.php';
        } else if (substr($class_name, 0, 7) == 'Smarty_') {
            $load = 'inc/smarty/sysplugins/' . strtolower($class_name) . '.php';
        } else {
            $load = null;
        }

        if ($load != null && file_exists($load)) {
            include_once $load;
        } else {
            // We have tried to load a class which does not exist. Trigger a warning
            trigger_error('Class ' . $class_name . ' not found.', E_USER_WARNING);
        }
    }

    /**
     * Checks whether a content file exist
     *
     * @param string $class_name the name of the class
     *
     * @return boolean
     */
    public static function checkContentFileExist($class_name) {
        $status = false;
        if (strstr($class_name, 'BoxContent')) {
            $status = is_file('content/box/' . $class_name . '.php');
        } elseif (strstr($class_name, 'AdminContent')) {
            $status = is_file('content/admin/' . $class_name . '.php');
        } elseif (substr($class_name, count($class_name) - 8) == 'Content' && $class_name != 'Content') {
            $status = is_file('content/' . $class_name . '.php');
        } else {
            $status = is_file($class_name . '.class.php');
        }
        return $status;
    }

}

?>
