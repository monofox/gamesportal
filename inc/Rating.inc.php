<?php

/**
 * Rating,
 * manages rating of games
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   2.0 Class documentated
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class Rating {

    /**
     * @var integer the game ID
     */
    private $gameId;

    /**
     * @var integer the user ID
     */
    private $userId;

    /**
     * @var integer the rating from 1 to 5
     */
    private $rating;

    /**
     * @var string the comment
     */
    private $comment;

    /**
     * @var User the user
     */
    private $user;

    /**
     * @var string the date and time of comment
     */
    private $datetime;

    /**
     * Initializes the rating
     *
     * @param integer $gameID Game id
     * @param integer $userID the user ID
     */
    public function __construct($gameId, $userId) {
        $this->gameId = $gameId;
        $this->userId = $userId;
        $this->load();
    }                      

    /**
     * Get the user
     *
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Get the rating
     *
     * @return integer
     */
    public function getRating() {
        return $this->rating;
    }

    /**
     * Get the comment
     *
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Get the game ID
     *
     * @return integer
     */
    public function getGameId() {
        return $this->gameId;
    }

    /**
     * Get the user ID
     *
     * @return integer
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * Get the date and time in specific format
     *
     * @return string
     */
    public function getDateTime() {
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $this->datetime);
        return $dt->format('d.m.Y H:i:s');
    }

    /**
     * Adds a comment and rating to game
     *
     * @param integer $rating  Rating from 1 to 5
     * @param string  $comment The comment of the user
     *
     * @return StatusHandler
     */
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

    /**
     * Loads the data
     *
     * @return void
     */
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

    /**
     * Get all comments of game
     *
     * @param integer $gameID the game ID
     *
     * @return StatusHandler
     */
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

    /**
     * Get the rating for a game
     *
     * @param integer $gameID the game ID
     *
     * @return StatusHandler
     */
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
