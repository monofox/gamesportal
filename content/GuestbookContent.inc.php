<?php

/**
 * GuestbookContent
 * manages the Guestbook for creating and showing entries.
 *
 * PHP Version 5.3
 *
 * @package   FLS
 * @author    Website-Team <website-team@fls-wiesbaden.de>
 * @copyright 2011-2012 Website-Team <website-team@fls-wiesbaden.de>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://trac.fls-wiesbaden.de/browser/flshp/trunk/content/ContentFileContent.php
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
