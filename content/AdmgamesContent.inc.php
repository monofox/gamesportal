<?php

/**
 * AdmgamesContent
 * Controls the administration of games. 
 * This means: edit, create, delete and renaming.
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012 
 * @version   1.5 Tried to implement games creation.
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class AdmgamesContent implements ContentFileContent {

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
        $tpl->setHeading('Administration: Spiele');
        $tpl->setMenu('admgames');
        $tpl->setTpl('content/admin/games.tpl');
        $tpl->assign('languages', Language::getLanguages());
        $tpl->assign('platforms', GamePlatform::getPlatforms()->getData());

        // Create new !
        $tpl->assign('game', new Game());
        // Not working at the moment!
        /*if (isset($url[1]) && $url[1] == 'new' && isset($_POST['gameTitle'])) {
            StatusHandler::messagesMerge(
                GamePlatform::create(
                    $_POST['gameTitle'],
                    $_POST['gameDesc'],
                    $_POST['gameUSK'],
                    $_FILES['gameCover'],
                    $_POST['gameFeatures'],
                    $_POST['gamePlatforms'],
                    $_POST['gameCompatibilty']
                )
            ); 
        }*/

        // Edit ?
        $tpl->assign('edit', false);
        if (isset($url[2]) && $url[2] == 'edit' && is_numeric($url[1])) {
            $game = new Game($url[1]);
            // Are there some data?
            if (isset($_POST['gameid'])) {
                // Save changes
                $game->setTitle($_POST['gameTitle']);
                $game->setDesc($_POST['gameDesc']);
                $game->setFeatures($_POST['gameFeatures']);
                $game->setUSK($_POST['gameUSK']);
                $game->setLanguages($_POST['gameLanguages']);
                $game->setPlatforms($_POST['gamePlatforms']);
                $game->setCompats($_POST['gameCompats']);
                // FIXME: Cover
                $lsh = $game->saveGame();
                StatusHandler::messagesMerge($lsh);
                if (!$lsh->getStatus()) {
                    $tpl->assign('edit', true);
                }
            } else {
                $tpl->assign('edit', true);
            }

            $tpl->assign('game', $game);
        }

        // Delete ?
        if (isset($url[2]) && $url[2] == 'delete' && is_numeric($url[1])) {
            // Delete
            $game = new Game($url[1]);
            StatusHandler::messagesMerge($game->delete());
        }

        // Get list of games
        $tpl->assign('games', Game::getList());
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
