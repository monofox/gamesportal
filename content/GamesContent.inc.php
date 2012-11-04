<?php

/**
 * GamesContent
 * controls the games list.
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   1.0 Class documentated.
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class GamesContent implements ContentFileContent {

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
        $gamelist = Game::getList();
        StatusHandler::messagesMerge($gamelist);

        $tpl->setHeading('Spiele');
        $tpl->setMenu('game');
        $tpl->setTpl('content/gamelist.tpl');
        $tpl->assign('games', $gamelist->getData());
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
