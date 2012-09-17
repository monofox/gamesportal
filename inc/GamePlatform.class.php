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

        $this->load();
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

    public static function loadGameList() {
        
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
