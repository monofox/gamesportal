<?php

class GamePlatform {
    const TYPE_PLATFORM = '0';
    const TYPE_COMPAT = '1';

    private $gameId;
    private $platId;
    private $name;
    private $price;
    private $publishDate;
    private $type;


    public function __construct($gameId, $platId) {
        $this->gameId = $gameId;
        $this->platId = $platId;
        $this->type   = self::TYPE_PLATFORM;

        if ($gameId != null && $platId != null) {
            $this->load();
        } else if ($platId != null) {
            $this->loadPlatform();
        }
    }         

    public function getPlatId() {
        return $this->platId;
    }    

    public function getName() {
        return $this->name;
    }
    
    public function getShortName() {
        return str_replace(array('@', ':', ';'), '', str_replace(' ', '-', strtolower($this->title)));
    }

    public function getPrice() {
        return $this->price;
    }

    public function getPublishDate($format = 'd.m.Y') {
        $date = DateTime::createFromFormat('Y-m-d', $this->publishDate);
        return $date->format($format);
    }

    public function getType() {
        return $this->type;
    }

    public function changeName($name) {
        $sh = new StatusHandler(true);
        $db = Database2::getInstance();
        $name = trim($name);

        if (strlen($name) < 3) {
            $sh->addError('Bitte geben Sie den Namen min. mit 3 Zeichen ein!');
        }

        if (!$sh->issetErrorMsg()) {
            $q = $db->q(
                'UPDATE %pplatforms SET platName = %s WHERE platID = %i', 
                $name, $this->platId
            );
            $sh->meltStatusHandler($q);
        } else {
            $sh->setStatus(false);
        }

        return $sh;
    }

    public function inUse() {
        $db = Database2::getInstance();

        $q = $db->q('SELECT * FROM %pgame_platform WHERE platID = %i', $this->platId);

        return $q->hasData();
    }

    public function delete() {
        $sh = new StatusHandler();
        $db = Database2::getInstance();

        if ($this->inUse()) {
            $sh->addError('Plattform ist noch in Verwendung!');
        } else {
            $q = $db->q('DELETE FROM %pplatforms WHERE platID = %i', $this->platId);
            if ($q->getStatus()) {
                $sh->setStatus(true);
                $sh->addSuccess('Plattform erfolgreich entfernt.');
            } else {
                $sh->addError('Plattform konnte nicht entfernt werden.');
            }
        }

        return $sh;
    }

    public static function create($name) {
        $sh = new StatusHandler(true);
        $db = Database2::getInstance();
        $name = trim($name);

        if (strlen($name) < 3) {
            $sh->addError('Bitte geben Sie den Namen min. mit 3 Zeichen ein!');
        }

        if (!$sh->issetErrorMsg()) {
            $q = $db->q('INSERT INTO %pplatforms (platName) VALUES (%s)', $name);
            $sh->meltStatusHandler($q);
        } else {
            $sh->setStatus(false);
        }

        return $sh;
    }

    public static function getPlatforms() {
        $db = Database2::getInstance();                            
        $q = $db->q('SELECT * FROM %pplatforms ORDER BY platName');

        return $q;
    }

    public function loadPlatform() {
        $db = Database2::getInstance();
        $q = $db->q('SELECT * FROM %pplatforms WHERE platID = %i', $this->platId);

        if ($q->hasData()) {
            $this->name = $q->getFirst()->platName;
        }
    }

    public function load() {
        $db = Database2::getInstance();
        $q = $db->q(
            'SELECT g.*, p.platName FROM %pgame_platform g JOIN %pplatforms p ON g.platID = p.platID 
            WHERE g.gameID = %i AND g.platID = %i', 
            $this->gameId, $this->platId
        );
        if ($q->hasData()) {
            $this->name = $q->getFirst()->platName;
            $this->price = $q->getFirst()->gamePrice;
            $this->publishDate = $q->getFirst()->publishDate;
            $this->type = $q->getFirst()->platType;
        }
    }
}
?>
