<?php
namespace FLS\Lib\Configuration;

/**
 * Secction
 * Its a configuration section
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2012-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Lib/Configuration/Section.class.php
 */
class Section {
    /**
     * Section name
     */
    protected $_sectionName;
    protected $_help;

    /**
     * Inits the section with the name!
     *
     * @param string $name the section name
     */
    public function __construct($name) {
        $this->_sectionName = $name;
        $this->_help = '';
    }

    /**
     * Get all entries (name => obj)
     *
     * @return array name => obj
     */
    public function getEntries() {
        $data = array();

        $entries = get_object_vars($this);
        foreach ($entries as $keyConf => $itmConf) {
            if (substr($keyConf, 0, 1) != '_') {
                $data[$keyConf] = $itmConf;
            }
        }

        return $data;
    }

    /**
     * Set the configuration help
     *
     * @param string $help Set $help as help ;-)
     *
     * @return void
     */
    public function setHelp($help) {
        $this->_help = $help;
    }

    /**
     * Get help information!
     *
     * @return string
     */
    public function getHelp() {
        return $this->_help;
    }
                                   
    /**
     * Set the section name
     *
     * @param string $name the section name
     *
     * @return void
     */
    public function setName($name) {
        $this->_sectionName = $name;
    }

    /**
     * Get the section name
     *
     * @return string
     */
    public function getName() {
        return $this->_sectionName;
    }

    /**
     * Adds an entry
     * 
     * @param Entry $configEntry Configuration entry
     *
     * @return void
     */
    public function addEntry(Entry $configEntry) {
        $this->{$configEntry->getName()} = $configEntry;
    }

    /**
     * Removes an configuration entry from the section
     *
     * @param string $name Entry name
     *
     * @return boolean
     */
    public function removeEntry($name) {
        if (property_exists($this, $name)) {
            unset($this->{$name});
            return true;
        } else {
            return false;
        }
    }
}

?>
