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

        if ($q->hasData()) {
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

        if ($q->hasData()) {
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

        if ($q->hasData()) {
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

    /**
     * @TODO: platforms, etc. pp. !?
     */
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

    public static function searchGames($searchTerm) {
        $db = Database2::getInstance();
        $sh = new StatusHandler();
        $list = explode(' ', $searchTerm);
        foreach ($list as $k => $v ) {
            $vTemp = trim($v);
            if (empty($v)) {
                unset($list[$k]);
            }
        }

        $args = array();
        $sql = 'SELECT g.gameID FROM %pgame g JOIN %pgame_platform p ON g.gameID = p.gameID 
            JOIN %pplatforms gp ON p.platID = gp.platID WHERE (';
        $i = 0;
        foreach ($list as $v) {
            if ($i > 0) {
                $sql .= ' AND';
            }
            $sql .= ' g.gameTitle LIKE %l';
            $args[] = $v;
            $i++;
        }
        $sql .= ') OR (';
        $i = 0;
        foreach ($list as $v) {
            if ($i > 0) {
                $sql .= ' AND';
            }
            $sql .= ' g.gameDescription LIKE %l';
            $args[] = $v;
            $i++;
        }
        $sql .= ') OR (';
        $i = 0;
        foreach ($list as $v) {
            if ($i > 0) {
                $sql .= ' AND';
            }
            $sql .= ' g.gameFeatures LIKE %l';
            $args[] = $v;
            $i++;
        }
        $sql .= ') OR (';
        $i = 0;
        foreach ($list as $v) {
            if ($i > 0) {
                $sql .= ' AND';
            }
            $sql .= ' g.gameUSK LIKE %l';
            $args[] = $v;
            $i++;
        }
        $sql .= ') OR (';
        $i = 0;
        foreach ($list as $v) {
            if ($i > 0) {
                $sql .= ' AND';
            }
            $sql .= ' p.gamePrice LIKE %l';
            $args[] = $v;
            $i++;
        }
        $sql .= ') OR (';
        $i = 0;
        foreach ($list as $v) {
            if ($i > 0) {
                $sql .= ' AND';
            }
            $sql .= ' gp.platName LIKE %l';
            $args[] = $v;
            $i++;
        }
        $sql .= ') GROUP BY g.gameID';

        $q = $db->q($sql, $args);
        if ($q->hasData()) {
            $sh->setStatus(true);
            foreach ($q->getData() as $v) {
                $sh->addData(new Game($v->gameID));
            }
        } else {
            $sh->setStatus(false);
            $sh->addInfo('Es konnte kein Spiel mit den Begriffen gefunden werden.');
        }

        return $sh;
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
