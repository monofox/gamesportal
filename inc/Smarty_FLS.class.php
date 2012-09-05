<?php
/**
 * Smarty FLS,
 * manages the template for the fls page
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Smarty_FLS.class.php
 */

// maybe includes in future.

if (!class_exists('Smarty')) {
    include_once './inc/smarty/Smarty.class.php';
}

/**
 * Smarty FLS,
 * manages the template for the fls page
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Smarty_FLS.class.php
 */
class Smarty_FLS extends Smarty {
    /*
     * @var Smarty_FLS the instance
     */
    private static $instance = null;
    private $display = true;
    public $toTpl;

    /**
     * Constructor
     * initialize Smarty
     *
     * @param string $tplDir  path to the template files
     * @param string $compDir path to the cached template files
     * @param string $cache   path to the cache folder
     * @param string $config  path to config folder
     */
    public function __construct(
        $tplDir = 'tpl/', $compDir = 'inc/smarty/tpl_c/', $cache = 'inc/smarty/cache/', $config = 'configs/'
    ) {
        parent::__construct();

        $this->template_dir = $tplDir;
        $this->compile_dir = $compDir;
        $this->cache_dir = $cache;
        $this->config_dir = $config;
        //check if newer tpl is avaible
        $this->compile_check = true;
        //compile every template new
        $this->auto_literal = false;
        $this->force_compile = false;
        $this->debugging = false;
        $this->toTpl = array('heading' => '', 'tpl' => '', 'mnu' => 'home');
        /**
         * maybe later
         * http://smarty.php.net/manual/de/caching.php
         * $this->caching = true;
         */
        $this->assign('app_name', 'Gamesportal');
        $this->configLoad('config.ini.php', 'default');
        $this->loadFilter('output', 'highlightsearch'); 

        self::setInstance($this);
    }

    /**
     * set a new template engine
     *
     * @param Smarty_FLS $tpl Object of Template Engine
     *
     * @return void
     */
    public static function setInstance(Smarty_FLS $tpl = null) {
        if (self::getInstance() == null || $tpl == null) {
            self::$instance = $tpl;
        }
    }

    /**
     * Get an object of template engine
     *
     * @return Smarty_FLS return an object of Smarty_FLS Template Engine
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * Set the heading
     *
     * @param string $heading the heading
     *
     * @return void
     */
    public function setHeading($heading='') {
        $this->toTpl['heading'] = $heading;
    }

    /**
     * Set the mnu selection
     *
     * @param string $mnu the menu key
     *
     * @return void
     */
    public function setMenu($mnu = null) {
        if ($mnu == null) {
            $mnu = 'home';
        }
        
        $this->toTpl['mnu'] = $mnu;
    }

    /**
     * Set the tpl file
     *
     * @param string $tpl the template file
     *
     * @return void
     */
    public function setTpl($tpl = null) {
        if ($tpl == null) {
            if (isset($this->toTpl['tpl'])) {
                unset($this->toTpl['tpl']);
            }
        } else {
            $this->toTpl['tpl'] = $tpl;
        }
    }

    /**
     * Writes the content to the template
     *
     * @return void
     */
    public function writeToTpl() {
        $this->assign('content', $this->toTpl);
    }

    /**
     * Get the full adress with path!
     *
     * @return string
     */
    public function getFullAddress() {
        $conf = \FLS\Lib\Configuration\Configuration::getInstance();

        return (string)$conf->default->address;
    }

    public function setDisplay($disp = true) {
        $this->display = $disp;
    }

    /**
     * Override display function of Smarty
     * Sending configuration and similar
     *
     * @param string $template   Template file
     * @param string $cache_id   Cache id 
     * @param string $compile_id Compile id
     * @param string $parent     Parent element 
     *
     * @return void
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null) {
        $config = \FLS\Lib\Configuration\Configuration::getInstance();
        $this->assign('conf', $config);
        $this->assign('fullAddr', $this->getFullAddress());
        if ($this->display) {
            parent::display($template, $cache_id, $compile_id, $parent);
        }
    }

}

?>
