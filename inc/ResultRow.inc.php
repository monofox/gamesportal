<?php

/**
 * Objective result row 
 *
 * PHP Version 5.3
 *
 * @package   FLS::Database 
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/ResultRow.class.php
 */
class ResultRow {

    /**
     * set attributes - generates it dynamical.
     *
     * @param string $name  Name of the attribute
     * @param mixed  $value Value of the attribute
     *
     * @return void
     */
    public function setAttribute($name, $value) {
        $this->{$name} = $value;
    }

    /**
     * Removes an attribute.
     *
     * @param string $name Name of the attribute
     *
     * @return void
     */
    public function removeAttribute($name) {
        unset($this->{$name});
    }
}

?>
