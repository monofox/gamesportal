<?php

class Rating {
    
    private $gameId;
    private $userId;
    private $rating;
    private $comment;
    private $user;


    public function __construct($gameId, $userId) {
        $this->gameId = $gameId;
        $this->userId = $userId;
        $this->load();
    }                      

    public function getUser() {
        return $this->user;
    }

    public function getRating() {
        return $this->rating;
    }

    public function getComment() {
        return $this->comment;
    }

    public function getGameId() {
        return $this->gameId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function load() {
        $db = Database2::getInstance();
        $q = $db->q(
            'SELECT * FROM %prating 
            WHERE gameID = %i AND userID = %i', 
            $this->gameId, $this->userId
        );
        if ($q->hasData()) {
            $this->rating = $q->getFirst()->rating;
            $this->comment = $q->getFirst()->comment;
            $this->user = new User($this->userId);
        }
    }

    public static function getRatingForGame($gameID) {
        $sh = new StatusHandler();
        $sh->setData(0);
        $db = Database2::getInstance();

        $q = $db->q('SELECT AVG(rating) as avgRating FROM %prating WHERE gameID = %i GROUP BY gameID', $gameID);
        if ($q->hasData()) {
            $sh->setData($q->getFirst()->avgRating);
        }

        return $sh;
    }
}
?>
