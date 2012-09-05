<?php
/**
 * This file controls the start of the site.
 *
 * PHP Version 5.3
 *
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright 2012 Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @version   0.1
 */

require_once 'inc/Main.class.php';
$main = new Main();
$main->preInit();
$main->init();
?>
