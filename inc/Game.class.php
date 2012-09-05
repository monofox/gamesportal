<?php
use \FLS\Lib\Configuration\Configuration as Configuration;

class Game {
    private $title;
    private $desc;
    private $features;
    private $cover;
    private $usk;
    private $id;
    private $languages = array();
    private $platforms = array();
    private $compats = array();

    public function __construct($id = null, $detail = true) {
        if ($id != null) {
            $this->id = $id;
            $this->loadGame($detail);
        }
    }

    public function loadGame($detail = true) {
        $db = Database2::getInstance();
        $q = $db->q(
            'SELECT * FROM %pgame WHERE gameID = %i', $this->id
        );

        if ($q->hasData() > 0) {
            $this->title = $q->getFirst()->gameTitle;
            $this->desc = $q->getFirst()->gameDescription;
            $this->features = $q->getFirst()->gameFeatures;
            $this->usk = $q->getFirst()->gameUSK;

            if ($q->getFirst()->gameCover != null && $detail) {
                $this->cover = new Cover($q->getFirst()->gameCover);
            }

            if ($detail) {
                // Load language
                $this->loadLanguages();

                // Load platforms
                $this->loadPlatforms();
            }
        }
    }

    private function loadLanguages() {
        $db = Database2::getInstance();
        $q = $db->q(
            'SELECT * FROM %pgame_lang WHERE gameID = %i', $this->id
        );

        if ($q->hasData() > 0) {
            foreach ($q->getData() as $v) {
                $lang = Language::getLangByCode($v->langCode);
                if ($lang !== false) {
                    $this->languages[] = $lang;        
                }
            }
        }
    }

    private function loadPlatforms() {
        $db = Database2::getInstance();
        $q = $db->q(
            'SELECT platID, gameID FROM %pgame_platform
            WHERE gameID = %i', $this->id
        );

        if ($q->hasData() > 0) {
            foreach ($q->getData() as $v) {
                $platform = new GamePlatform($v->gameID, $v->platID);
                if ($platform->getType() == GamePlatform::TYPE_PLATFORM) {
                    $this->platforms[] = $platform;
                } else {
                    $this->compats[] = $platform;
                }
            }
        }
    }

    public function getTitle() {
        return $this->title;
    }

    public function getId() {
        return $this->id;
    }

    public function getShortName() {
        return str_replace(array('@', ':', ';'), '', str_replace(' ', '-', strtolower($this->title)));
    }

    public function getFeatures() {
        return $this->features;
    }

    public function getLanguages() {
        return $this->languages;
    }

    public function getPlatforms() {
        return $this->platforms;
    }

    public function getCompats() {
        return $this->compats;
    }

    public function getUSK() {
        return $this->usk;
    }

    public function getDescription() {
        return $this->desc;
    }

    public function getCover() {
        return $this->cover;
    }

    public function setCover(Cover $cover) {
        $this->cover = $cover;
    }

    public function createGame($desc, $features, Cover $cover, $usk) {
    
    }

    public function saveGame() {
        $db = Database2::getInstance();
        $q = $db->q(
            'UPDATE %pgame SET 
            gameDescription = %s, 
            gameUSK = %i,
            gameTitle = %s,
            gameCover = %i,
            gameFeatures = %s
            WHERE gameID = %i',
            $this->desc, $this->usk, $this->title, $this->cover, $this->features, $this->id
        );
        if ($q->getStatus()) {
            $q->addSuccess('Spiel erfolgreich gespeichert.');
        } else {
            $q->addError('Spiel konnte nicht gespeichert werden.');
        }

        return $q;
    }

    public static function getList() {
        $sh = new StatusHandler(true);
        $db = Database2::getInstance();
        // You can easily add new conditions to this.
        $q = $db->q('SELECT gameID FROM %pgame');
        if ($q->hasData()) {
            foreach ($q->getData() as $k => $v) {
                $sh->addData(new Game($v->gameID));
            }
        } else {
            $sh->setStatus(false);
            $sh->addInfo('Keine Spiele vorhanden.');
        }

        return $sh;
    }
}
?>
