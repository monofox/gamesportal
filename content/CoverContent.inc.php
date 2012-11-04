<?php
/**
 * CoverContent
 * controls the delivery of game covers.
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012 
 * @version   1.0 class created.
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class CoverContent implements ContentFileContent {

    /**
     * executes pre conditions.
     *
     * @param User  $user User object
     * @param array $url  URL.
     *
     * @return boolean
     */
    public function preExecute($user, $url) {
        return true;
    }

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
    public function execute($tpl, $content, $user, $url) {
        // Keine template ausgabe
        $tpl->setDisplay(false);
        $urlsplit = explode('-', $url[1]);
        $cover = new Cover($urlsplit[0], true);
        header('Content-Type: ' . $cover->getType());
        header('Content-Length: ' . $cover->getSize());
        echo $cover->getImage();
    }

    /**
     * executes post conditions.
     *
     * @param Content $content Content object
     *
     * @return void
     */
    public function postExecute($content) {
        ;
    }

}

?>
