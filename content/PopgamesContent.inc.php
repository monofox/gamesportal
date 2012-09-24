<?php

/**
 * DefaultMobileContent
 * controls the default mobile page
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browser/flshp/trunk/content/VPlanContent.php
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
