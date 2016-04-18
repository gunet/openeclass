<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/**
  /*
 * Mass change user's mail verification status
 * @author Kapetanakis Giannis <bilias@edu.physics.uoc.gr>
 * @abstract This component massively changes user's verification status.
 *
 */
$require_admin = TRUE;
require_once '../../include/baseTheme.php';

register_posted_variables(array(
    'submit' => true,
    'submit0' => true,
    'submit1' => true,
    'submit2' => true,
    'old_mail_ver' => true,
    'new_mail_ver' => true
));

$data['mail_ver_data'][0] = $mail_ver_data[0] = $langMailVerificationPendingU;
$data['mail_ver_data'][1] = $mail_ver_data[1] = $langMailVerificationYesU;
$data['mail_ver_data'][2] = $mail_ver_data[2] = $langMailVerificationNoU;

if (!empty($submit) && (isset($old_mail_ver) && isset($new_mail_ver))) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    if ($old_mail_ver != $new_mail_ver) {
        $old_mail_ver = intval($old_mail_ver);
        $new_mail_ver = intval($new_mail_ver);
        $count = Database::get()->query("UPDATE `user` set verified_mail=?s WHERE verified_mail=?s AND id != 1", $new_mail_ver, $old_mail_ver)->affectedRows;
        if ($count > 0) {
            $user = ($count == 1) ? $langOfUser : $langUsersS;
            Session::Messages($langMailVerificationChanged . " " . $m['from'] . " «" . $mail_ver_data[$old_mail_ver] . "» " . $m['in'] . " «". $mail_ver_data[$new_mail_ver] . "» $m[in] $count $user", 'alert-success');
            redirect_to_home_page('modules/admin/mail_ver_settings.php');            
        }
        // user is admin or no user selected
        else {
            Session::messages($langMailVerificationChangedNoAdmin, 'alert-danger');
            redirect_to_home_page('modules/admin/mail_ver_settings.php');   
        }
    }
    // no change selected
    else {
        Session::Messages($langMailVerificationChangedNo, 'alert-info');
        redirect_to_home_page('modules/admin/mail_ver_settings.php');
    }
}

$toolName = $langMailVerification;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$data['action_bar'] = action_bar([
                        [
                            'title' => $langBack,
                            'url' => "index.php",
                            'icon' => 'fa-reply',
                            'level' => 'primary-label'
                        ]
                    ]);

// admin hasn't clicked on edit
if (empty($submit0) && empty($submit1) && empty($submit2)) {
    $data['mr'] = get_config('email_required') ? $m['yes'] : $m['no'];
    $data['mv'] = get_config('email_verification_required') ? $m['yes'] : $m['no'];
    $data['mm'] = get_config('dont_mail_unverified_mails') ? $m['yes'] : $m['no'];    
    
    $data['verified_email_cnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFIED . ";")->cnt;
    $data['unverified_email_cnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_UNVERIFIED . ";")->cnt;
    $data['verification_required_email_cnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFICATION_REQUIRED . ";")->cnt;
    $data['empty_email_user_cnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE email = '';")->cnt;
    $data['user_cnt'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user;")->cnt;
    
    $view = 'admin.users.mail_ver_settings.index';
}
// admin wants to change user's mail verification value. 3 possible
else { 
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (!empty($submit0)) {
        $data['sub'] = 0;
        $msg = $langMailVerificationPending;
    } elseif (!empty($submit1)) {
        $data['sub'] = 1;
        $msg = $langMailVerificationYes;
    } elseif (!empty($submit2)) {
        $data['sub'] = 2;
        $msg = $langMailVerificationNo;
    } else {
        $data['sub'] = NULL;
    }
    $view = 'admin.users.mail_ver_settings.change';
}

$data['menuTypeID'] = 3;
view($view, $data);
