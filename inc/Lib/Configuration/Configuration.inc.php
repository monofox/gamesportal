<?php
namespace FLS\Lib\Configuration;

/**
 * Configuration
 * Reads configuration ini files with help functionality ;-)
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2012-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Lib/Configuration/Configuration.class.php
 */
class Configuration {
    const RID_MODIFY = '22448def';

    private $_configFile;
    private static $_instance;

    /**
     * Create configuration / Load configuration
     *
     * @param string $basePath   Path to the root of the cms.
     * @param string $configFile path to config file
     */
    public function __construct($basePath, $configFile = 'configs/config.ini.php') {
        $this->_basePath = $basePath;
        $this->_configFile = $basePath . DIRECTORY_SEPARATOR . $configFile;
        $this->initDefaultEntries();
        self::setInstance($this);

        $this->loadConfiguration();
    }

    /**
     * Set the instance
     *
     * @param Configuration $inst Instance
     *
     * @return void
     */
    private static function setInstance(Configuration $inst) {
        self::$_instance = $inst;
    }

    /**
     * Get the last setted instance
     *
     * @return Configuration
     */
    public static function getInstance() {
        return self::$_instance;
    }


    /**
     * Load the configuration from file
     *
     * @return void
     */
    public function loadConfiguration() {
        $ini = parse_ini_file($this->_configFile, true);
        foreach ($ini as $key => $var) {
            foreach ($var as $k => $v) {
                $this->setUserValue($key, $k, $v);
            }
        }
    }

    /**
     * Check whether the given configuration variable exists or not.
     *
     * @param string $section Section name
     * @param string $config  Configuration name
     *
     * @return boolean
     */
    public function issetConfig($section, $config) {
        // We use the positive feature, that the second part will only be checked,
        // when the first statement ist true!
        if (isset($this->{$section}) && isset($this->$section->{$config})) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get all sections (name => obj)
     *
     * @return array name => obj
     */
    public function getSections() {
        $data = array();

        $sections = get_object_vars($this);
        foreach ($sections as $keySec => $itmSec) {
            if (substr($keySec, 0, 1) != '_') {
                $data[$keySec] = $itmSec;
            }
        }

        return $data;
    }

    /**
     * Get all configuration items with sections as parent 
     *
     * @return array sectionName => array (name => itm)
     */
    public function getConfigurationItems() {
        $data = array();
        $sections = $this->getSections();

        foreach ($sections as $keySec => $itmSec) {
            $data[$keySec] = array();

            foreach ($itmSec->getEntries() as $confKey => $confItm) {
                $data[$keySec][$confKey] = $confItm;
            }
        }
        
        return $data;
    }

    /**
     * Save the configuration back to the file.
     *
     * @return void
     */
    public function saveConfiguration() {
        // Open file
        // we have to ignore exception, because: we can't catch it here!
        $confFile = @fopen($this->_configFile, 'w');
        if ($confFile === false) {
            trigger_error('Could not open configuration file for saving!', E_USER_ERROR);
        } else {
            $vars = get_object_vars($this);
            foreach ($vars as $keySec => $section) {
                // We ignore all privates with "_".
                if (substr($keySec, 0, 1) != '_') {
                    // Now write section
                    fwrite($confFile, PHP_EOL . '[' . $section->getName() . ']' . PHP_EOL);
                    // Now write all config values
                    $confVars = get_object_vars($section);
                    foreach ($confVars as $keyConf => $conf) {
                        if (substr($keyConf, 0, 1) != '_') {
                            $tmpValue = $conf->getValue();
                            if ($conf->getType() == Entry::TYPE_STRING) {
                                $tmpValue = '"' . $tmpValue . '"';
                            } else if ($conf->getType() != Entry::TYPE_ARRAY) {
                                $tmpValue = (string)$tmpValue;
                            }

                            if ($conf->getType() == Entry::TYPE_ARRAY) {
                                foreach ($tmpValue as $k => $v) {
                                    fwrite($confFile, $conf->getName() . '[] = "' . $v . '"' . PHP_EOL);
                                }
                            } else {
                                fwrite($confFile, $conf->getName() . ' = ' . $tmpValue . PHP_EOL);
                            }
                        }
                    }
                }
            }

            // Close file
            fclose($confFile);
        }
    }

    /**
     * Sets an user value
     *
     * @param string $section section name
     * @param string $name    entry name
     * @param mixed  $value   entry value
     *
     * @return boolean
     */
    public function setUserValue($section, $name, $value) {
        $result = true;
        if (property_exists($this, $section)) {
            if (property_exists($this->{$section}, $name)) {
                $this->{$section}->{$name}->setValue($value);
            } else {                                  
                trigger_error('Entry "' . $name . '" does not exist in "' . $section . '"', E_USER_WARNING);
                $result = false;
            }
        } else {
            trigger_error('Section "' . $section . '" does not exist!', E_USER_WARNING);
            $result = false;
        }

        return $result;
    }

    /**
     * Initializes the entries
     *
     * @return void
     */
    public function initDefaultEntries() {
        // Section: default
        $this->addSection(new Section('default'));
        $this->default->addEntry(new Entry('name', 'Ostara local'));
        $this->default->name->setHelp('Legt den Seitennamen fest (steht im Titel).');
        $this->default->addEntry(new Entry('address', 'http://localhost/ostara/'));
        $this->default->address->setHelp('Legt die Adresse der Seite fest (zur Verlinkung).');
        $this->default->addEntry(new Entry('utf8', true, Entry::TYPE_BOOL));
        $this->default->utf8->setHelp('Aktiviert/Deaktiviert UTF-8-Modus');
        $this->default->addEntry(new Entry('email', 'root@localhost.localdomain'));
        $this->default->email->setHelp('E-Mail-Adresse aller ausgehenden E-Mails.');
        $this->default->addEntry(new Entry('filedir', 'files/'));
        $this->default->filedir->setHelp('Legt den Pfad zum Dateiverzeichnis fest (relativ zum root-Verzeichnis).');
        $this->default->addEntry(new Entry('tempdir', '/tmp/'));
        $this->default->tempdir->setHelp('Legt den Pfad zum tempor&auml;ren Verzeichnis fest (relativ zum root-Verzeichnis).');
        $this->default->addEntry(new Entry('root', '/srv/www/htdocs/ostara/'));
        $this->default->root->setHelp('Legt den Pfad zum Hauptverzeichnis fest (absolut).');

        // Section: errors
        $this->addSection(new Section('errors'));
        $this->errors->addEntry(new Entry('img404', '404_notfound.png'));
        $this->errors->img404->setHelp('Legt das 404-Bildchen fest (unterhalb von res)');

        // Section: smarty
        $this->addSection(new Section('smarty'));
        $this->smarty->addEntry(new Entry('templateDir', 'tpl/'));
        $this->smarty->addEntry(new Entry('compileDir', 'inc/smarty/tpl_c/'));
        $this->smarty->addEntry(new Entry('cacheDir', 'inc/smarty/cache/'));
        $this->smarty->addEntry(new Entry('configDir', 'configs/'));

        // Section: datenbank
        $this->addSection(new Section('datenbank'));
        $this->datenbank->addEntry(new Entry('DBHOST', 'localhost'));
        $this->datenbank->addEntry(new Entry('DBNAME', 'fls'));
        $this->datenbank->addEntry(new Entry('DBUSER', 'fls'));
        $this->datenbank->addEntry(new Entry('DBPASSWORD', ''));
        $this->datenbank->addEntry(new Entry('DBPREFIX', 'fls_'));
        $this->datenbank->addEntry(new Entry('persistent', false, Entry::TYPE_BOOL));
        $this->datenbank->persistent->setHelp('Boolean: 1 = True, leer lassen f&uuml;r false!');
        $this->datenbank->addEntry(new Entry('hashPassword', ''));

        // Section: admin
        $this->addSection(new Section('admin'));
        $this->admin->addEntry(new Entry('DEBUG', true, Entry::TYPE_BOOL));
    }

    /**
     * Adds a section to the Configuration properties
     *
     * @param Section $section the section
     *
     * @return void
     */
    public function addSection(Section $section) {
        $this->{$section->getName()} = $section;
    }

    /**
     * Removes a section from the configuration
     *
     * @param string $name the name of the section
     *
     * @return boolean
     */
    public function removeSection($name) {
        if (property_exists($this, $name)) {
            unset($this->{$name});
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the configuration file
     *
     * @param string $cfile full path to configuration file
     *
     * @return void
     */
    public function setConfigFile($cfile) {
        $this->_configFile = $cfile;
    }

    /**
     * Get the path to the configuration file
     *
     * @return string
     */
    public function getConfigFile() {
        return $this->configFile;
    }

}
?>
