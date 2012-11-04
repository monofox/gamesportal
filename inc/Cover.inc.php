<?php

/**
 * Cover,
 * manages the cover with all its functions.
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   1.1 Class documentated
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class Cover {
    /**
     * @var integer user id
     */
    private $id;

    /**
     * @var binary the image in its binary form
     */
    private $image;

    /**
     * @var string the name of the file
     */
    private $name;

    /**
     * @var string the mime type of file
     */
    private $type;

    /**
     * Initializes the cover
     *
     * @param integer $id    the cover id
     * @param boolean $image load the image with binary info
     */
    public function __construct($id = null, $image = false) {
        if ($id != null) {
            $this->id = $id;
            $this->load($image);
        }
    }                      

    /**
     * Get the cover id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get cover name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get the cover
     *
     * @return binary
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Get mime type of cover
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Get size of image
     *
     * @return long
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * Loads an image with or without the image itself
     *
     * @param boolean $image enable if you want to load the binary thing
     *
     * @return void
     */
    public function load($image = false) {
        $db = Database2::getInstance();
        $q = $db->q(
            'SELECT coverID, '. ($image ? 'coverImage,':'').' coverName, coverType, coverSize 
            FROM %pcovers WHERE coverID = %i', 
            $this->id
        );
        if ($q->hasData()) {
            if ($image) {
                $this->image = $q->getFirst()->coverImage;
            }
            $this->name = $q->getFirst()->coverName;
            $this->type = $q->getFirst()->coverType;
            $this->size = $q->getFirst()->coverSize;
        }
    }
}

?>
