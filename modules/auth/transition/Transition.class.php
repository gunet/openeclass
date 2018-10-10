<?php 

require_once 'modules/auth/auth.inc.php';

define('SSO_TRANSITION_EXCEPTION_PENDING', 1);
define('SSO_TRANSITION_EXCEPTION_APPROVED', 2);
define('SSO_TRANSITION_EXCEPTION_BLOCKED', 3);
define('SSO_TRANSITION_EXCEPTION_CLOSED', 4);

class Transition {

    public $userid;

    public function __construct($userid) {
        $this->userid = $userid;
    }

    /**
     * @brief check user auth method. If eclass user then needs transition.
     */
    public function user_needs_transition() {
        global $auth_ids;

        $password = Database::get()->querySingle("SELECT `password` FROM user WHERE id = ?d", $this->userid)->password;
        if (!in_array($password, $auth_ids)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief sso user authentication
     */
    public function sso_authenticate() {
        
        global $urlServer;
                
        $cas_res = cas_authenticate(7);
        if (phpCAS::checkAuthentication()) {
            $cas = get_auth_settings(7);
            $_SESSION['cas_attributes'] = phpCAS::getAttributes();
            $attrs = get_cas_attrs($_SESSION['cas_attributes'], $cas);
            $_SESSION['cas_uname'] = strtolower(phpCAS::getUser());            
            if (!empty($_SESSION['cas_uname'])) {
                $_SESSION['uname'] = $_SESSION['cas_uname'];
            }
            if (!empty($attrs['surname'])) {
                $_SESSION['surname'] = $_SESSION['cas_surname'] = $attrs['surname'];
            }
            if (!empty($attrs['givenname'])) {
                $_SESSION['givenname'] = $_SESSION['cas_givenname'] = $attrs['givenname'];
            }

            if (user_exists($_SESSION['cas_uname'])) { // check if user exists
                unset($_SESSION['cas_uname']);
                unset($_SESSION['cas_surname']);
                unset($_SESSION['cas_givenname']);
                Session::Messages("Ο χρήστης υπάρχει ήδη ή έχει πραγματοποιήσει μετάβαση!", 'alert-danger');
                redirect("{$urlServer}index.php?logout=TRUE");
            } else { // update user
                Database::get()->query("UPDATE user SET username = ?s, 
                                                    givenname = ?s, 
                                                    surname = ?s, 
                                                    password = 'cas' 
                                                WHERE id = ?d",
                    $_SESSION['uname'], $_SESSION['cas_givenname'],
                    $_SESSION['cas_surname'], $this->userid);

                $_SESSION['uid'] = $this->userid;
                header("Location: $urlServer");
            }
        }
        //print_a($_SESSION);
        //die;
    }


    /**
     * @brief get user exception status
     */
    public function get_sso_exception_status() {

        $q = Database::get()->querySingle("SELECT status FROM sso_exception WHERE uid = ?d", $this->userid);
        if ($q) {
            return $q->status;            
        } else {
            return false;
        }

    }

    /**
     * @brief get user exception comments
     */
    public function get_sso_exception_comments() {
        
        $q = Database::get()->querySingle("SELECT comments FROM sso_exception WHERE uid = ?d", $this->userid);
        if ($q) {
            return $q->comments;
        } else {
            return false;
        }
    }

    /**
     * @brief add exception comments to database
     * @param $comments
     */
    public function add_sso_exception($comments) {

        Database::get()->query("INSERT INTO sso_exception SET 
                                              uid = ?d, comments = ?s, 
                                              status = " . SSO_TRANSITION_EXCEPTION_PENDING .",
                                              timestamp = " . DBHelper::timeAfter() . "",
                                          $this->userid, $comments);
    }

}