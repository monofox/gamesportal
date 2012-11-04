<?php

/**
 * AdmplatformContent
 * Controls the administration of platforms.
 * This means: edit, delete, rename, creation of platforms.
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012 
 * @version   1.0 Implemented the whole class.
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class AdmplatformContent implements ContentFileContent {

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
        $tpl->setHeading('Administration: Plattformen');
        $tpl->setMenu('admplatform');
        $tpl->setTpl('content/admin/conlist.tpl');

        // Create new !
        if (isset($url[1]) && $url[1] == 'new' && isset($_POST['platname'])) {
            StatusHandler::messagesMerge(GamePlatform::create($_POST['platname'])); 
        }

        // Edit ?
        $tpl->assign('edit', false);
        if (isset($url[2]) && $url[2] == 'edit' && is_numeric($url[1])) {
            // Save changes
            $platForm = new GamePlatform(null, $url[1]);
            if (isset($_POST['platname'])) {
                $lsh = $platForm->changeName($_POST['platname']); 
                StatusHandler::messagesMerge($lsh);
                if (!$lsh->getStatus()) {
                    $tpl->assign('edit', true);
                }
            } else {
                $tpl->assign('edit', true);
            }

            $tpl->assign('platid', $platForm->getPlatId());
            $tpl->assign('platname', $platForm->getName());
        }

        // Delete ?
        if (isset($url[2]) && $url[2] == 'delete' && is_numeric($url[1])) {
            // Delete
            $platForm = new GamePlatform(null, $url[1]);
            StatusHandler::messagesMerge($platForm->delete());
        }

        // Get list of platforms
        $tpl->assign('platforms', GamePlatform::getPlatforms());
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
