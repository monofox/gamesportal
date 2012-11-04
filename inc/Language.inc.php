<?php

class Language {
    private $code;
    private $text;
    private static $languages = array();

    public function __construct($code = null, $text = null) {
        $this->code = $code;
        $this->text = $text;

        if ($code != null && $text == null) {
            $this->load();
        }
    }                      

    public function getCode() {
        return $this->code;
    }

    public function getText() {
        return $this->text;
    }

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

    public static function getLanguages() {
        if (count(self::$languages) <= 0) {
            self::cache();
        }

        return self::$languages;
    }

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
