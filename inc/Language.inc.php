<?php

/**
 * Language,
 * manages the different game languages
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   1.0 Class documentated
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class Language {
    /**
     * @var string lang code
     */
    private $code;

    /**
     * @var string readable lang name
     */
    private $text;

    /**
     * @var array<Language> list of languages
     */
    private static $languages = array();

    /**
     * Initializes the langauges
     *
     * @param string $code the lang code
     * @param string $text the lang text
     */
    public function __construct($code = null, $text = null) {
        $this->code = $code;
        $this->text = $text;

        if ($code != null && $text == null) {
            $this->load();
        }
    }                      

    /**
     * Get the lang code
     *
     * @return string
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Get the lang text
     *
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Loads the languages
     *
     * @return void
     */
    public function load() {
        $db = Database2::getInstance();
        $q = $db->q(
            'SELECT langCode, langText 
            FROM %planguages WHERE langCode = %s', 
            $this->code
        );
        if ($q->hasData()) {
            $this->code = $q->getFirst()->langCode;
            $this->text = $q->getFirst()->langText;
        }
    }

    /**
     * Get all languages.
     *
     * @return array<Language>
     */
    public static function getLanguages() {
        if (count(self::$languages) <= 0) {
            self::cache();
        }

        return self::$languages;
    }

    /**
     * Get specific language by code
     *
     * @param string $code lang code
     *
     * @return Language False if not found
     */
    public static function getLangByCode($code) {
        if (count(self::$languages) <= 0) {
            self::cache();
        }

        if (!isset(self::$languages[$code])) {
            return false;
        } else {
            return self::$languages[$code];
        }
    }

    /**
     * Caches all languages
     *
     * @return void
     */
    public static function cache() {
        $db = Database2::getInstance();
        $q = $db->q('SELECT * FROM %planguages ORDER BY langCode');
        if ($q->hasData()) {
            foreach ($q->getData() as $v) {
                self::$languages[$v->langCode] = new Language($v->langCode, $v->langText);
            }
        }
    }
}

?>
