<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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
 * @file session_users.php
 * @brief About the users of session
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'functions.php';

check_activation_of_collaboration();

load_js('tools.js');

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}

session_exists($sessionID);
check_user_belongs_in_session($sessionID);

$sessionTitle = title_session($course_id,$sessionID);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);
$pageName = $langUserConsent;

if(isset($_POST['submit_user'])){
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    if(isset($_POST['addUserId'])){
        Database::get()->query("UPDATE mod_session_users SET
                                is_accepted = ?d
                                WHERE session_id = ?d AND participants = ?d",1,$sessionID,$_POST['addUserId']);
    }elseif(isset($_POST['deleteUserId'])){
        Database::get()->query("UPDATE mod_session_users SET
                                is_accepted = ?d
                                WHERE session_id = ?d AND participants = ?d",0,$sessionID,$_POST['deleteUserId']);
    }

    Session::flash('message',$langProcessCompleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/session_users.php?course=$course_code&session=$sessionID");

}

$data['all_users'] = Database::get()->queryArray("SELECT * FROM mod_session_users WHERE session_id = ?d",$sessionID);

view('modules.session.session_users', $data);
