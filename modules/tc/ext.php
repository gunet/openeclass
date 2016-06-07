<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

// For creating bbb urls & params
require_once '../../config/config.php';
require_once '../../modules/db/database.php';
require_once 'bbb-api.php';
require_once 'om-api.php';
require_once 'functions.php';

if (isset($_GET['meeting_id'])) {
	$meeting_id = $_GET['meeting_id'];
} else {
    redirect_to_home_page();    
    exit;
}

$q = Database::get()->querySingle("SELECT * FROM tc_session WHERE meeting_id=?s", $meeting_id);

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

$server_type = Database::get()->querySingle("SELECT `type` FROM tc_servers WHERE id = ?d", $server_id)->type;

if ($active <> '1' 
    or date_diff_in_minutes($start_date,date('Y-m-d H:i:s')) > $unlock_interval 
    or !in_array($_GET['username'],$r_group)) {
	    echo "Ο σύνδεσμος είναι ενεργός μόνο για όσες τηλεσυνεργασίες είναι σε εξέλιξη";
        exit;
}

if ($server_type == 'bbb') { // bbb server
    if(bbb_session_running($meeting_id) == false) {
        create_meeting($title,$meeting_id,$mod_pw,$att_pw,$record);
    }
    # Get session capacity
    $sess = Database::get()->querySingle("SELECT sessionUsers, mod_pw, running_at FROM tc_session WHERE meeting_id=?s",$meeting_id);
    $serv = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $sess->running_at);

    if($sess->sessionUsers < get_meeting_users($serv->server_key,$serv->api_url,$meeting_id,$sess->mod_pw))
    {
        echo "Έχει συμπληρωθεί ο μέγιστος αριθμός συμμετεχόντων στην τηλεσυνεργασία. Παρακαλώ δοκιμάστε να συνδεθείτε αργότερα ή επικοινωνήστε με τους διαχειριστές.";
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
        echo "Έχει συμπληρωθεί ο μέγιστος αριθμός συμμετεχόντων στην τηλεσυνεργασία. Παρακαλώ δοκιμάστε να συνδεθείτε αργότερα ή επικοινωνήστε με τους διαχειριστές.";
        exit;
    } else {
        header('Location: ' . om_join_user($meeting_id, $_GET['username'], -1, "", $_GET['username'], "", 0));
    }
}