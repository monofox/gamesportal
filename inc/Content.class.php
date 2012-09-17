<?php

/**
 * Content,
 * manages the whole content, whether php or from databse: its all here ;-)
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Content.class.php
 */
use \FLS\Lib\Configuration\Configuration as Configuration;

// maybe includes in future.

/**
 * Content,
 * manages the whole content, whether php or from databse: its all here ;-)
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Content.class.php
 */
class Content implements Listener {
    
    const RID_VIEW          = '53c964f6';
    const RID_VIEW_DRAFT    = '91b3ab91';
    const RID_CREATE        = '6b9cbada';
    const RID_EDIT          = 'a4d4fbde';
    const RID_DELETE        = '329a4351';
    const RID_NEW_NOTIFY    = 'a41c735e';
    const RID_CHANGE_NOTIFY = 'c818ea59';
    const RID_PUBLISH       = 'fe2b20ab';
    const RID_INCLUDE       = '855232a5';
    const RID_ARTICLE       = '5b9b3b02';
    const RID_LINKLESS      = '0ef0b5a1';
    const RID_DECLINED      = '9a6c72e8';
    const RID_RIGHTS        = '76376f84';

    const RELEASE_DRAFT     = 0;
    const RELEASE_PUBLISH   = 1;
    const RELEASE_DECLINED  = 2;
    const RELEASE_RESERVED  = 3;

    /**
     * stellt fest, ob es sich hierbei um einen eingefügten Inhalt von der HDD handelt, oder um einen DB Eintrag
     * @var boolean true if there are include.
     */
    private $included;

    /**
     * ist der Datensatz editierbar, vorzugsweise Datenbankeinträge
     * @var boolean true if content ist editable
     */
    private $editable;

    /**
     * Is set, if the content is actually edited..
     * @var boolean true if you're actually editing the content
     */
    private $editing;

    /**
     * InhaltID des konstruierten Inhaltes
     * @var string content id
     */
    private $id;

    /**
     * Stellt für die Suchfunktion fest, ob der Inhalt gesucht werden darf
     * @var boolean true if content is searchable.
     */
    private $search;

    /**
     * soll die GalerieBox angezeigt werden?
     * @var boolean true if you want to display the gallery
     */
    private $show_gallery;

    /**
     * soll die FileBox angezeigt werden?
     * @var boolean true if you want to display the files.
     */
    private $show_filebox;

    /**
     * should the sidebar be displayed?
     * @var boolean true if you want to display the sidebars
     */
    private $show_sidebar;

    /**
     * is there any error?
     * @var boolean true on error
     */
    private $error;

    /**
     * @var Database connection to mysql
     */
    private $db;

    /**
     * @var Configuration connection to the configuration file.
     */
    private $conf;

    /**
     * @var array hash => path to css file
     */
    private $css;

    /**
     * @var array hash => path to js file
     */
    private $js;

    /**
     * @var array hash => path to smarty file
     */
    private $tpl;

    /**
     * @var Content Instance of this class
     */
    private static $instance = null;

    /**
     * constructor, initialize all vars about
     */
    public function Content() {
        //include datei
        $this->included = false;
        //editierbar wenn keine include
        $this->editable = false;
        $this->id = false;
        $this->search = false;
        $this->show_gallery = false;
        $this->show_filebox = false;
        $this->show_sidebar = true;

        $this->css = array();
        $this->js = array();
        $this->tpl = array();

        $this->db = Database2::getInstance();
        $this->conf = Configuration::getInstance();

        // We want to listen
        Session::getInstance()->addListener($this);

        self::setInstance($this);
    }

    /**
     * Set the main instance
     *
     * @param Content $instance an object
     *
     * @return void
     */
    public static function setInstance(Content $instance = null) {
        if (self::getInstance() == null || $instance == null) {
            self::$instance = $instance;
        }
    }

    /**
     * Get the main instance of the class
     *
     * @return Content
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * Returns the Wizard Information.
     *
     * @return StatusHandler
     */
    public function getWizardInfo() {
        $sh = new StatusHandler;

        return $sh;
    }

    /**
     * adds a new css file
     *
     * @param string $file Relative to res/styles/
     *
     * @return void
     */
    public function addCSS($file) {
        // Schon vorhanden?
        $hash = hash('crc32b', $file);

        if (!isset($this->css[$hash])) {
            $this->css[$hash] = $file;
        }
    }

    /**
     * Add some css files that would be usefull in the default context
     *
     * @return void
     */
    public function addDefaultCss() {
        $this->addCSS('global/style.css');
        $this->addCSS('global/sidebars.css');
        $this->addCSS('global/font_formats.css');
        $this->addCSS('global/font_styles.css');
        $this->addCSS('global/colors.css');
    }

    /**
     * adds a new javascript file
     *
     * @param string $file relative to res/js/
     *
     * @return void
     */
    public function addJavaScript($file) {
        // already exist?
        $hash = hash('crc32b', $file);

        if (!isset($this->js[$hash])) {
            $this->js[$hash] = $file;
        }
    }

    /**
     * Add some scripts that would be usefull in the default context
     *
     * @return void
     */
    public function addDefaultJs() {
        $this->addJavaScript('functions.js');
        $this->addJavaScript('Effect.js');
        $this->addJavaScript('Login.js');
    }

    /**
     * adds a new smarty file to include
     *
     * @param string $file relative to tpl/
     *
     * @return void
     */
    public function addSmartyFile($file) {
        // already exist?
        $hash = hash('crc32b', $file);

        if (!isset($this->tpl[$hash])) {
            $this->tpl[$hash] = $file;
        }
    }

    /**
     * finds the right content and includes it.
     *
     * @param string $tplPath the path from where the templates should be loaded.
     *
     * @return void
     *
     * @global boolean $edit  zentrale Systemvariable, die feststellt, ob bearbeitet (wird|werden soll)
     */
    public function buildContent($tplPath) {
        global $edit, $url;
        $sh = new StatusHandler(true);
        $tpl = Smarty_FLS::getInstance();
        $user = User::getInstance();

        $cClass     = ucfirst($url[0]) . 'Content';

        try {
            $controller = new $cClass();
            if (!$controller->preExecute($user, $url)) {
                $this->showError(500);
            } else {
                $controller->execute($tpl, $this, $user, $url);
            }
        } catch(Exception $e) {
            $this->showError(404);
        }
        $tpl->writeToTpl();

        if (!$sh->getStatus()) {
            StatusHandler::messagesMerge($sh);
            Main::getInstance()->back();
        }
    }


    /**
     * send the added css files to content
     * we don't check whether the files exist or not anymore.
     *
     * @param string $cssPrefix the prefix for the css files. It serve as a distinction between mobile and others.
     *
     * @return void
     */
    public function sendCSS($cssPrefix) {
        $tpl = Smarty_FLS::getInstance();
        $files = array();
        $compress = (!$this->conf->admin->DEBUG->value) ? true : false;

        foreach ($this->css as $c) {
            $cA = explode('/', $c);
            $cA[count($cA) - 1] = $cssPrefix . $cA[count($cA) - 1];
            $cPrefixed = implode('/', $cA);
            if (is_file('./res/styles/' . ($compress ? '_compressed/' : '') . $cPrefixed)) {
                $files[] = ($compress ? '_compressed/' : '') . $cPrefixed;
            } else {
                $files[] = ($compress ? '_compressed/' : '') . $c;
            }
        }
        $tpl->assign('contentCSSFiles', $files);
    }

    /**
     * send the added js files to content
     * we don't check whether the files exist or not anymore.
     *
     * @return void
     */
    public function sendJs() {
        $tpl = Smarty_FLS::getInstance();
        $files = array();
        $compress = (!$this->conf->admin->DEBUG->value) ? true : false;

        foreach ($this->js as $c) {
            $files[] = ($compress ? '_compressed/' : '') . $c;
        }

        $tpl->assign('contentJSFiles', $files);
    }

    /**
     * send the added smarty include files to content
     *
     * @return void
     */
    public function sendSmartyFiles() {
        $tpl = Smarty_FLS::getInstance();

        $tpl->assign('smartyIncludes', $this->tpl);
    }

    /**
     * If content/file was not found or if user hasn't enough rights, this method
     * will display an error message
     *
     * @param integer $errorC Error Code (at this time: 403, 404)
     * @param string  $module List of modulerights which are needed to display the content/file
     *
     * @return void
     */
    public function showError($errorC, $module = '') {
        $sh = new StatusHandler();

        switch ($errorC) {
            case 404:
                $sh->addError('Die angeforderte Seite kann nicht gefunden werden.');
                break;
            case 403:
                if (empty($module)) {
                    $sh->addError('Sie haben keine Rechte diese Seite aufzurufen.');
                } else {
                    $sh->addError('Ihnen fehlen Rechte f&uuml;r das Modul "' . $module . '"');
                }
                break;
             case 500:
                $sh->addError('Die angeforderte Seite kann nicht verarbeitet werden.');
                break; 
            default:
                $sh->addError('Ein nicht bekannter Fehlercode ist aufgetreten: ' . $errorC);
                break;
        }

        StatusHandler::messagesMerge($sh);
        Main::getInstance()->back();
    }

    /**
     * is performed when there occurs an action
     *
     * @param string $action the action key
     * @param mixed  $data   can be all things...
     *
     * @return void
     */
    public function actionPerformed($action, $data = false) {
        switch ($action) {
            case 'sessionDestroy':
                continue;
                break;
        }
    }

    /**
     * Removes javascript from the string
     *
     * @param string $filter string to clean
     *
     * @return string cleaned string
     */
    public static function strip_javascript($filter) {
        //var_dump(preg_match_all('/<script type="text\/javascript"(.*)>(.*)<\/script>/i', $filter, &$matches));
        $filter = preg_replace('/<script (.*)type="text\/javascript"(.*)>([^\/]*)<\/script>/i', '', $filter);

        return $filter;
    }
}

?>
