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
                $sql .= ') OR (';
            }
            $i = 0;
            foreach (
                array(
                    'g.gameTitle', 'g.gameDescription', 'g.gameFeatures', 'g.gameUSK', 'p.gamePrice', 'gp.platName'
                ) as $k
            ) {
                if ($i > 0) {
                    $sql .= ' OR';
                }
                $sql .= ' '. $k .' LIKE %l';
                $args[] = $v;
                $i++;
            }
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

    public static function getPopularList() {
        $sh = new StatusHandler(true);
        $db = Database2::getInstance();
        // You can easily add new conditions to this.
        $q = $db->q(
            'SELECT g.gameID, AVG(r.rating) as rating 
            FROM %pgame g    
            LEFT JOIN %prating r ON g.gameID = r.gameId
            GROUP BY g.gameID 
            ORDER BY AVG(rating) DESC');
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

    public static function getPopularListByConsole($console) {
        $sh = new StatusHandler(true);
        $db = Database2::getInstance();
        // You can easily add new conditions to this.
        $q = $db->q(
            'SELECT g.gameID, p.platName, AVG(r.rating) as rating 
            FROM %pgame g    
            LEFT JOIN %prating r ON g.gameID = r.gameId
            JOIN %pgame_platform gp ON gp.gameID = g.gameID
            JOIN %pplatforms p ON gp.platID = p.platID
            WHERE p.platID = %i
            GROUP BY g.gameID, p.platName
            ORDER BY AVG(rating) DESC', $console);
        if ($q->hasData()) {
            $sh->addData($q->getFirst()->platName, 'platName');
            $sh->addData(array(), 'games');
            foreach ($q->getData() as $k => $v) {
                $sh->addData(new Game($v->gameID), 'games');
            }
        } else {
            $sh->setStatus(false);
            $sh->addInfo('Keine Spiele vorhanden.');
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
