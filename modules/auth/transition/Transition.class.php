<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

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

            if (user_exists($_SESSION['cas_uname'])) { // check if user exists. if yes rename non sso user to 'sso_$username'
                Database::get()->query("UPDATE user SET username = CONCAT('sso_', '$_SESSION[cas_uname]')   
                                                    WHERE username = ?s", $_SESSION['cas_uname']);
            } // update user
            Database::get()->query("UPDATE user SET username = ?s, 
                                                givenname = ?s, 
                                                surname = ?s, 
                                                password = 'cas' 
                                            WHERE id = ?d",
                $_SESSION['uname'], $_SESSION['cas_givenname'],
                $_SESSION['cas_surname'], $this->userid);

            $_SESSION['uid'] = $this->userid;
            //print_a($_SESSION); die;
            header("Location: $urlServer");
        }
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


    /**
     * @brief update database with new exception status
     * @param $eid
     * @param $action
     */
    public static function change_exception_status($eid, $action) {

        switch($action) {
            case 'yes': Database::get()->query("UPDATE sso_exception 
                                                 SET status = " . SSO_TRANSITION_EXCEPTION_APPROVED . " 
                                                WHERE id = ?d", $eid);
                        break;
            case 'close': Database::get()->query("UPDATE sso_exception 
                                                 SET status = " . SSO_TRANSITION_EXCEPTION_CLOSED . " 
                                                WHERE id = ?d", $eid);
                        break;
            case 'reject': Database::get()->query("UPDATE sso_exception 
                                                 SET status = " . SSO_TRANSITION_EXCEPTION_BLOCKED . " 
                                                WHERE id = ?d", $eid);
                           Database::get()->query("UPDATE user SET expires_at = " . DBHelper::timeAfter() . " 
                                                  WHERE id = (SELECT uid FROM sso_exception WHERE id = ?d)", $eid);
                        break;
            default: break;
        }
    }

    /**
     * @brief display exception request status message
     * @param $status
     */
    public static function exception_status($status) {

        $message = '';

        switch ($status) {
            case SSO_TRANSITION_EXCEPTION_PENDING: $message = "Σε εκκρεμότητα";
                break;
            case SSO_TRANSITION_EXCEPTION_APPROVED: $message = "Έχει εξαιρεθεί";
                break;
            case SSO_TRANSITION_EXCEPTION_BLOCKED: $message = "Έχει αποκλεισθεί";
                break;
            case SSO_TRANSITION_EXCEPTION_CLOSED: $message = "Έχει απορριφθεί";
                break;
            default: break;
        }
        return $message;
    }


    /**
     * @brief display appropriate table row style
     * @param $status
     * @return string
     */
    public static function row_style($status) {

        $style = '';

        switch ($status) {
            case SSO_TRANSITION_EXCEPTION_CLOSED: $style = "warning";
                break;
            case SSO_TRANSITION_EXCEPTION_APPROVED: $style = "success";
                break;
            case SSO_TRANSITION_EXCEPTION_BLOCKED: $style = "danger";
                break;
            default: break;
        }
        return $style;
    }


    /**
     * @brief create table `sso_exception` if not exists
     */
    public static function create_table() {

        Database::get()->query("CREATE TABLE IF NOT EXISTS `sso_exception` 
                                        ( `id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
                                          `uid` int(10) unsigned NOT NULL, 
                                          `comments` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL, 
                                          `status` tinyint(4) NOT NULL, 
                                          `timestamp` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP, 
                                          PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET utf8");
    }
}
