<?php
namespace FLS\Lib\Configuration;

/**
 * Entry
 * Configuration entry.
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2012-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Lib/Configuration/Entry.class.php
 */
class Entry {
    protected $origin;
    protected $name;
    protected $help;
    protected $type;
    public $value;

    const ORIGIN_FILE = 'file';
    const ORIGIN_DB = 'db';

    const TYPE_INTEGER = 'int';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'float';
    const TYPE_BOOL = 'bool';
    const TYPE_BOOLEAN = 'bool';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';

    /**
     * Inits the section with the name!
     *
     * @param string $name  the name
     * @param mixed  $value the value
     * @param string $type  the type of entry
     */
    public function __construct($name, $value = null, $type = 'string') {
        $this->origin = self::ORIGIN_FILE;
        $this->name = $name;
        $this->type = $type;
        if ($value == null) {
            $this->value = $value;
        } else {
            $this->setValue($value);
        }
        $this->help = '';
    }

    /**
     * Set the configuration help
     *
     * @param string $help Set $help as help ;-)
     *
     * @return void
     */
    public function setHelp($help) {
        $this->help = $help;
    }

    /**
     * Get help information!
     *
     * @return string
     */
    public function getHelp() {
        return $this->help;
    }
                                   
    /**
     * Set the entry name
     *
     * @param string $name the section name
     *
     * @return void
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get the entry name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get type of config entry
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }
                                    
    /**
     * Set the entry value
     *
     * @param mixed $value the value
     *
     * @return void
     */
    public function setValue($value) {
        // We will check against the type
        switch ($this->type) {
            case Entry::TYPE_INT:
                if (is_numeric($value)) {
                    $this->value = intval($value);
                }
                break;
            case Entry::TYPE_BOOL:
                if (is_bool($value)) {
                    $this->value = $value;
                } else if ($value == '1' || $value == 'true' || $value == 'True' || $value == 'TRUE') {
                    $this->value = true;
                } else {
                    $this->value = false;
                }
                break;
            case Entry::TYPE_FLOAT:
                if (is_numeric($value)) {
                    $this->value = floatval($value);
                }
                break;
            case Entry::TYPE_ARRAY:
                if (!is_array($value)) {
                    $value = trim($value);
                    $value = str_replace(' ', '', $value);
                    $value = explode(',', $value);
                } 
            default:
                $this->value = $value;
                break;
        }
    }

    /**
     * Get the entry value
     *
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }


    /**
     * Get the value by tostring ;-)
     *
     * @return string
     */
    public function __toString() {
        if ($this->type == Entry::TYPE_ARRAY) {
            return implode(',', $this->value);
        } else {
            return $this->value;
        }
    }
}
?>
