<?php

/**
 * This file deinfes the listener interface.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Listener.class.php
 */
// maybe includes in future.

/**
 * Listener
 * this interface defines the listener classes.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browse/flshp/trunk/inc/Listener.class.php
 *
 * @TODO: split "actionPerformed" ? Means: try to find an action named methdo and call this, otherwise call actionPerformed ?
 */

interface Listener {

    /**
     * is performed when there occurs an action
     *
     * @param string $action the action key
     * @param mixed  $data   can be all things...
     *
     * @return void
     */
    public function actionPerformed($action, $data = false);
}

?>
