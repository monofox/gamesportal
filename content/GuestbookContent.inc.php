<?php

/**
 * GuestbookContent
 * manages the Guestbook for creating and showing entries.
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   1.5 Class created and documentated
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class GuestbookContent implements ContentFileContent {

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
        $tpl->setHeading('Gästebuch');
        $tpl->setMenu('guestbook');
        $tpl->setTpl('content/guestbook.tpl');

        // Create some text?
        $gb = new Guestbook();
        if (isset($_POST['create']) && isset($_POST['name']) && isset($_POST['comment'])) {
            StatusHandler::messagesMerge($gb->create($_POST['name'], $_POST['comment']));
        }

        $page = 0;
        if (isset($_GET['p']) && is_numeric($_GET['p'])) {
            $page = $_GET['p'];
        }
        $entriesHandler = $gb->showEntries($page);
        $entries = array();
        if ($entriesHandler->hasData()) {
            $entries = $entriesHandler->getData();
        }
        $tpl->assign('entries', $entries);
    }

    /**
     * executes post conditions.
     *
     * @param Content $content Content object
     *
     * @return void
     */
    public function postExecute($content) {
    
    }
}

?>
