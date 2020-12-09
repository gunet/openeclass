<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

// For creating bbb urls & params
$guest_allow = true;
require_once 'bbb-api.php';
require_once 'om-api.php';
require_once 'functions.php';
require_once '../../include/init.php';

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
    $r_group = explode(",",$external_users);
    if (!empty($q->options))  {
        $options = unserialize($q->options);
    } else {
        $options = NULL;
    }
} else {
    redirect_to_home_page();
    exit;
}

$server_type = Database::get()->querySingle("SELECT `type` FROM tc_servers WHERE id = ?d", $server_id)->type;

if ($active <> '1'
    or date_diff_in_minutes($start_date,date('Y-m-d H:i:s')) > $unlock_interval
    or !in_array($_GET['username'],$r_group)) {
        $msg = "Η τηλεδιάσκεψη δεν έχει ξεκινήσει ακόμα. Παρακαλώ δοκιμάστε να συνδεθείτε αργότερα ή επικοινωνήστε με τους διαχειριστές.";
        display_message($msg);
        exit;
}

if ($server_type == 'bbb') { // bbb server
    if(bbb_session_running($meeting_id) == false) {
        create_bbb_meeting($title, $meeting_id, $mod_pw, $att_pw, $record, $options);
    }
    # Get session capacity
    $sess = Database::get()->querySingle("SELECT sessionUsers, mod_pw, running_at FROM tc_session WHERE meeting_id=?s",$meeting_id);
    $serv = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $sess->running_at);

    if($sess->sessionUsers < get_meeting_users($serv->server_key,$serv->api_url,$meeting_id,$sess->mod_pw))
    {
        $msg = "Έχει συμπληρωθεί ο μέγιστος αριθμός συμμετεχόντων στην τηλεσυνεργασία. Παρακαλώ δοκιμάστε να συνδεθείτε αργότερα ή επικοινωνήστε με τους διαχειριστές.";
        display_message($msg);
        exit;
    } else {
        header('Location: ' . bbb_join_user($meeting_id,$att_pw,$_GET['username'],""));
    }
}

if ($server_type == 'om') { // OM server
    if (om_session_running($meeting_id) == false) {
        create_om_meeting($title, $meeting_id, $record);
    }
    # Get session capacity
    $sess = Database::get()->querySingle("SELECT sessionUsers, mod_pw, running_at FROM tc_session where meeting_id=?s",$meeting_id);
    $serv = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $sess->running_at);

    if ($sess->sessionUsers < get_om_connected_users($server_id))
    {
        $msg = "Έχει συμπληρωθεί ο μέγιστος αριθμός συμμετεχόντων στην τηλεσυνεργασία. Παρακαλώ δοκιμάστε να συνδεθείτε αργότερα ή επικοινωνήστε με τους διαχειριστές.";
        display_message($msg);
        exit;
    } else {
        header('Location: ' . om_join_user($meeting_id, $_GET['username'], -1, "", $_GET['username'], "", 0));
    }
}

/**
 * @brief display message
 * @param message
 */
function display_message($message) {

    global $urlServer;

    echo "
        <!DOCTYPE HTML>
        <html>
        <head>            
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
            <title>Υπηρεσία Τηλεδιάσκεψης</title>
            <link rel='stylesheet' href='{$urlServer}template/default/CSS/bootstrap-custom.css'>
        </head>        
        <body style='background-color: white;'>
            <div class='container'>
                <div class='row'>
                    <div class='col-xs-12 text-center'>
                        <div style='padding-top: 10px; padding-bottom: 10px;'>
                            <img style = 'filter: invert(100%);' src='{$urlServer}template/default/img/logo_eclass.png' alt=''>
                        </div>                        
                        <div class='panel-body'>
                            <div class='alert alert-warning'>
                                $message
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
        </body>         
        </html>
    ";
}
