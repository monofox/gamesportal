<?php

/**
 * PopgamesMobileContent
 * displays the popular games
 *
 * PHP Version 5.3
 *
 * @date      2.0 Implemented popular list by console
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class PopgamesContent implements ContentFileContent {

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
        Language::cache();
        $console = null;
        if (isset($url[1]) && is_numeric($url[1])) {
            $console = $url[1];
            $gamelist = Game::getPopularListByConsole($console);
        } else {
            $gamelist = Game::getPopularList();
        }

        StatusHandler::messagesMerge($gamelist);

        if ($console != null && $gamelist->getStatus()) {
            $tpl->setHeading('Beliebteste Spiele für Plattform "' . $gamelist->getData()['platName'] . '"');
            $tpl->assign('games', $gamelist->getData()['games']);
        } else {
            $tpl->setHeading('Beliebteste Spiele');
            $tpl->assign('games', $gamelist->getData());
        }
        $tpl->setMenu('popgame');
        $tpl->setTpl('content/gamelist.tpl');
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
