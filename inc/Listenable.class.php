<?php

/**
 * This file defines the class "Listenable" which is implemented in other classes.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Listenable.class.php
 */
// maybe includes in future.

/**
 * Listenable
 * this class which is implemented in other classes.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Listenable.class.php
 */
class Listenable {

    /**
     * @var array of type Listener
     */
    private $listener = array();

    /**
     * Add a listener
     *
     * @param Listener $listener the listener to add
     *
     * @return void
     */
    public function addListener(Listener $listener) {
        $this->listener[] = $listener;
    }

    /**
     * removes a listener
     *
     * @param Listener $listener the listener which will be searched.
     *
     * @return void
     */
    public function removeListener(Listener $listener) {
        $j = false;
        for ($i = 0; $i < count($this->listener); $i++) {
            if ($this->listener[$i] === $listener) {
                $j = $i;
            }
        }
        if ($j !== false) {
            array_splice($this->listener, $i, 1);
        }
    }

    /**
     * This method fires an event to the childs.
     *
     * @param string $event the event name
     * @param mixed  $data  the data
     *
     * @return void
     */
    public function fireEvent($event, $data = false) {
        foreach ($this->listener as $value) {
            $value->actionPerformed($event, $data);
        }
    }

}

?>
