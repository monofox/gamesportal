<?php

/**
 * UserContent
 * Controls the different users action.
 * At this point of development: login/logout
 *
 * PHP Version 5.3
 *
 * @date      04.11.2012
 * @version   1.8 Class documentated
 * @package   Gamesportal
 * @author    Lukas Schreiner <lukas.schreiner@gmail.com>
 * @copyright Lukas Schreiner <lukas.schreiner@gmail.com>
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class UserContent implements ContentFileContent {

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
        switch ($url[1]) {
            case 'login':
                $this->login();
                Main::getInstance()->back();
                break;
            case 'logout':
                $user->logout();
                Main::getInstance()->back();
                break;
        }
    }

    /**
     * Logs in an user.
     *
     * @return void
     */
    private function login() {
        $tpl = Smarty_FLS::getInstance();
        $user = User::getInstance();
        $gsh = StatusHandler::getInstance();

        $username = $_POST['login_name'];
        $password = $_POST['login_pass'];

        $sh = $user->login($username, $password);
        $gsh->messagesMerge($sh);
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
