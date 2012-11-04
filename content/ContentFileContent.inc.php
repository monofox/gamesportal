<?php

/**
 * ContentFileContent
 * has the force over the other content files ;-)
 * The Name of this class is a little bit confusing, but it is needed that content is at the end of the name,
 * because auto_load don't work otherwise.
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012 
 * @version   1.0 Created class.
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
interface ContentFileContent {

    /**
     * executes pre conditions.
     *
     * @param User  $user User object
     * @param array $url  URL.
     *
     * @return boolean
     */
    public function preExecute($user, $url); // for checking rights, etc.

    /**
     * executes the main process for display the module.
     *
     * @param Smarty_FLS $tpl     Smarty template object
     * @param Content    $content Content object
     * @param User       $user    User object
     * @param array      $url     URL.
     *
     * @return void
     */
    public function execute($tpl, $content, $user, $url);

    /**
     * executes post conditions.
     *
     * @param Content $content Content object
     *
     * @return void
     */
    public function postExecute($content); // for display errors, etc.
}

?>
