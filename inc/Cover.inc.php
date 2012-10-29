<?php

class Cover {
    private $id;
    private $image;
    private $name;
    private $type;

    public function __construct($id = null, $image = false) {
        if ($id != null) {
            $this->id = $id;
            $this->load($image);
        }
    }                      

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getImage() {
        return $this->image;
    }

    public function getType() {
        return $this->type;
    }

    public function getSize() {
        return $this->size;
    }

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
