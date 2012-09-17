<?php

class Rating {
    
    private $gameId;
    private $userId;
    private $rating;
    private $comment;
    private $user;
    private $datetime;


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

    public function getDateTime() {
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $this->datetime);
        return $dt->format('d.m.Y H:i:s');
    }

    public function addComment($rating, $comment) {
        $sh = new StatusHandler();
        $db = Database2::getInstance();

        if (empty($comment)) {
            $comment = null;
        }

        if ($rating == '0') {
            $rating = null;
        }

        if ($comment === null && $rating === null ) {
            $sh->addError('Sie müssen entweder kommentieren, bewerten oder beides!');
        }

        if (!$sh->issetErrorMsg()) {
            $q = $db->q(
                'INSERT INTO %prating (gameID, userID, rating, comment, created) VALUES (%i, %i, %i, %s, %s)
                ON DUPLICATE KEY UPDATE rating = %i, comment = %s, created = %s ',
                $this->gameId, $this->userId, $rating, $comment, date('Y-m-d H:i:s'),
                $rating, $comment, date('Y-m-d H:i:s')
            );

            if ($q->getStatus()) {
                $sh->addSuccess('Bewertung und/oder Bemerkung gespeichert.');
                $sh->setStatus(true);
            } else {
                $sh->addError('Speichern war nicht möglich.');
            }
        }
    

        return $sh;
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
            $this->datetime = $q->getFirst()->created;
        }
    }

    public static function getComments($gameID) {
        $sh = new StatusHandler();
        $db = Database2::getInstance();

        $q = $db->q(
            'SELECT gameId, userId FROM %prating WHERE gameId = %i AND comment IS NOT NULL ORDER BY created DESC', 
            $gameID
        );
        if ($q->hasData()) {
            $sh->setStatus(true);
            foreach ($q->getData() as $v) {
                $sh->addData(new Rating($v->gameId, $v->userId));
            }
        }

        return $sh;
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
