<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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
 * ========================================================================
 */

// For creating bbb urls & params
$guest_allow = true;

require_once '../../include/baseTheme.php';
require_once 'bbb-api.php';
require_once 'functions.php';
//require_once '../../include/init.php';

if (isset($_GET['meeting_id'])) {
    $meeting_id = $_GET['meeting_id'];
} else {
    redirect_to_home_page();
    exit;
}

$q = Database::get()->querySingle("SELECT * FROM tc_session WHERE meeting_id=?s", $meeting_id);
if ($q) {
    $server_id = $q->running_at;
    $mod_pw = $q->mod_pw;
    $title = $q->title;
    $att_pw = $q->att_pw;
    $record = $q->record;
    $start_date = $q->start_date;
    $end_date = $q->end_date;
    $active = $q->active;
    $unlock_interval = $q->unlock_interval;
    $external_users = $q->external_users;
    // External user details are now stored as JSON array of ['e-mail', 'Display Name']...
    $names = [];
    try {
        $external_user_details = json_decode($external_users, true, 512, JSON_THROW_ON_ERROR);
        $r_group = [];
        foreach ($external_user_details as $item) {
            $r_group[] = $item[0];
            $names[$item[0]] = $item[1];
        }
    } catch (Exception $e) {
        // If they're not, they used to be stored as comma-delimited e-mails
        $r_group = explode(',', $external_users);
    }
    if (!empty($q->options))  {
        $options = unserialize($q->options);
    } else {
        $options = NULL;
    }
} else {
    redirect_to_home_page();
    exit;
}

$now = date('Y-m-d H:i:s');
$server_type = Database::get()->querySingle("SELECT `type` FROM tc_servers WHERE id = ?d", $server_id)->type;

// meeting is disabled
if ($active <> '1') {
    display_message($langBBBDisabled);
    exit;
}
// wrong external email
if (!in_array(urldecode($_GET['username']), $r_group)) {
    display_message($langNoAccessPrivilages);
    exit;
}
// meeting not started yet
if (date_diff_in_minutes($start_date, $now) > $unlock_interval) {
    display_message($langBBBNotStarted);
    exit;
}
// meeting is expired
if (!empty($end_date) and date_diff_in_minutes($now, $end_date) > 0) {
    display_message($langBBBHasEnded);
    exit;
}

if ($server_type == 'bbb') { // bbb server
    if (!bbb_session_running($meeting_id)) {
        create_bbb_meeting($title, $meeting_id, $mod_pw, $att_pw, $record, $options);
    }
    # Get session capacity
    $sess = Database::get()->querySingle("SELECT sessionUsers, mod_pw, running_at FROM tc_session WHERE meeting_id=?s",$meeting_id);
    $serv = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $sess->running_at);

    if ($sess->sessionUsers < get_meeting_users($serv->server_key, $serv->api_url, $meeting_id, $sess->mod_pw)) {
        display_message($langBBBMaxUsersJoinError);
        exit;
    } else {
        $name = (isset($names[$_GET['username']]) && $names[$_GET['username']])? $names[$_GET['username']]: $_GET['username'];
        header('Location: ' . bbb_join_user($meeting_id, $att_pw, $name, ''));
    }
} elseif ($server_type == 'jitsi' or $server_type == 'googlemeet') { // jitsi server
    $host = Database::get()->querySingle("SELECT hostname FROM tc_servers WHERE id = ?s", $server_id)->hostname;
    header("Location: " . $host . $meeting_id);
} elseif ($server_type == 'zoom') { // zoom
    header("Location: " . $meeting_id  . '/?pwd=' . $mod_pw);
    } elseif ($server_type == 'webex') { // webex
    header("Location: " . $meeting_id);
}

/**
 * @brief display message
 * @param message
 */
function display_message($message) {

    global $urlServer, $langBBBWelcomeMsg;

    echo "
        <!DOCTYPE HTML>
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
            <title>$langBBBWelcomeMsg</title>
            <link rel='stylesheet' href='{$urlServer}template/modern/css/bootstrap.min.css'>
        </head>        
        <body style='background-color: white;'>
            <div class='container'>
                <div class='row'>
                    <div class='col-12 text-center'>
                        <div style='padding-top: 10px; padding-bottom: 10px;'>
                            <img style = 'filter: invert(100%);' src='{$urlServer}template/modern/img/logo_eclass.png' alt=''>
                        </div>                        
                        <div class='panel-body'>
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                $message</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
    ";
}
