<?php

/**
 * DefaultMobileContent
 * controls the default mobile page
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   2.8 Class documentated
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class GameContent implements ContentFileContent {

    /**
     * executes pre conditions.
     *
     * @param User            $user  User object
     * @param array           $url   URL.
     *
     * @return boolean
     */
    public function preExecute($user, $url) {
        return true;
    }

    /**
     * executes the main process for display the module.
     *
     * @param Smarty_FLS      $tpl     Smarty template object
     * @param Content         $content Content object
     * @param User            $user    User object
     * @param array           $url     URL.
     *
     * @return void
     */
    public function execute($tpl, $content, $user, $url) {
        $urlsplit = explode('-', $url[1]);
        $game = new Game($urlsplit[0]);

        $tpl->setHeading($game->getTitle());
        $tpl->setMenu('game');
        $tpl->setTpl('content/game.tpl');
        $tpl->assignByRef('game', $game);

        // save comments
        if (isset($_POST['saveComment']) && User::getInstance()->isLoggedIn()) {
            $rating = new Rating($game->getId(), User::getInstance()->getId());
            StatusHandler::messagesMerge($rating->addComment($_POST['rating'], $_POST['comment']));
        } else if (isset($_POST['saveComment'])) {
            StatusHandler::getInstance()->addError('Sie müssen angemeldet sein um eine Bewertung abzugeben!');
        }

        // Ermittle Ratings fuer das Spiel
        $rating = Rating::getRatingForGame($game->getId());
        $tpl->assign('rating', $rating->getData());

        // Ermittle Kommentare für das Spiel
        $comments = Rating::getComments($game->getId());
        $tpl->assign('comments', $comments->getData());
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
