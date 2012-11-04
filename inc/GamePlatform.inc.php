<?php

/**
 * GamePlatform,
 * manages the game itself
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   1.2 Class documentated
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class GamePlatform {
    const TYPE_PLATFORM = '0';
    const TYPE_COMPAT = '1';

    /**
     * @var integer Game ID
     */
    private $gameId;
    
    /**
     * @var integer Platform ID
     */
    private $platId;

    /**
     * @var string Platform name
     */
    private $name;

    /**
     * @var double Price of combination of game and platform
     */
    private $price;

    /**
     * @var string Publish date of platform <-> game
     */
    private $publishDate;

    /**
     * @var string Type of platform (0 = Platform, 1 = compatibility)
     */
    private $type;

    /**
     * Initializes the GamePlatform
     *
     * @param integer $gameId Game id (or null if you don't want to load combination of game and platform)
     * @param integer $platId Platform id (or null if you don't want to load anything)
     */
    public function __construct($gameId = null, $platId = null) {
        $this->gameId = $gameId;
        $this->platId = $platId;
        $this->type   = self::TYPE_PLATFORM;

        if ($gameId != null && $platId != null) {
            $this->load();
        } else if ($platId != null) {
            $this->loadPlatform();
        }
    }         

    /**
     * Get the platform ID
     *
     * @return integer
     */
    public function getPlatId() {
        return $this->platId;
    }    

    /**
     * Get the name of the platform
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get a short name of platform to make simple urls.
     *
     * @return string
     */
    public function getShortName() {
        return str_replace(array('@', ':', ';'), '', str_replace(' ', '-', strtolower($this->title)));
    }

    /**
     * Get the price of game on specific platform
     *
     * @return double
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * Get the publishing date in given format
     *
     * @return string
     */
    public function getPublishDate($format = 'd.m.Y') {
        $date = DateTime::createFromFormat('Y-m-d', $this->publishDate);
        return $date->format($format);
    }

    /**
     * Get the type of platform
     *
     * @return string
     *
     * @see GamePlatform::TYPE_PLATFORM
     * @see GamePlatform::TYPE_COMPAT
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Change the name of the platform
     *
     * @string $name the name of the platform
     *
     * @return StatusHandler
     */
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

    /**
     * Check whether this combination is used or not
     *
     * @return boolean
     */
    public function inUse() {
        $db = Database2::getInstance();

        $q = $db->q('SELECT * FROM %pgame_platform WHERE platID = %i', $this->platId);

        return $q->hasData();
    }

    /**
     * Deletes an combination
     *
     * @return StatusHandler
     */
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

    /**
     * Creates a new combination
     *
     * @param string $name the name of platform
     *
     * @return StatusHandler
     */
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

    /**
     * Get list of platforms
     *
     * @return DbStatusHandler
     */
    public static function getPlatforms() {
        $db = Database2::getInstance();                            
        $q = $db->q('SELECT * FROM %pplatforms ORDER BY platName');

        return $q;
    }

    /**
     * Load platform with all its informations (this manes "name" ;))
     *
     * @return void
     */
    public function loadPlatform() {
        $db = Database2::getInstance();
        $q = $db->q('SELECT * FROM %pplatforms WHERE platID = %i', $this->platId);

        if ($q->hasData()) {
            $this->name = $q->getFirst()->platName;
        }
    }

    /**
     * Loads a combination
     *
     * @return void
     */
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
