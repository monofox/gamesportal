<?php

/**
 * SearchContent
 * controls the "search engine"
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   2.4 Class documentated
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class SearchContent implements ContentFileContent {

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
        $tpl->setHeading('Suche');
        $tpl->setMenu('search');

        // Suchbegriff ?
        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $tpl->setHeading('Suche "' . str_replace('+', ' ', $_GET['q']) . '"');
            $list = Game::searchGames($_GET['q']);
            StatusHandler::messagesMerge($list);
            $tpl->setTpl('content/gamelist.tpl');
            $tpl->assign('games', $list->getData());
        } else {
            StatusHandler::getInstance()->addInfo('Keinen Suchbegriff eingegeben! Bitte probieren Sie es erneut.');
        }
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
