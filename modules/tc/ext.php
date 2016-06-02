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

$require_current_course = FALSE;

require_once '../../include/baseTheme.php';
// For creating bbb urls & params
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

if ($server_type == 'bbb' and !is_active_bbb_server()) {
    Session::Messages($langBBBNoteEnableJoin, 'alert-warning');
    redirect_to_home_page();
    exit;
}
if ($server_type == 'om' and !is_active_om_server()) {
    Session::Messages($langBBBNoteEnableJoin, 'alert-warning');
        redirect_to_home_page();    
        exit;
}

if ($active <> '1' 
    or date_diff_in_minutes($start_date,date('Y-m-d H:i:s'))> $unlock_interval 
    or date_diff_in_minutes(date('Y-m-d H:i:s'),$start_date) > 1440     
    or !in_array($_GET['username'],$r_group)) {
        Session::Messages($langBBBNoteEnableJoin, 'alert-warning');
        redirect_to_home_page();    
        exit;
}

if ($server_type == 'bbb') { // bbb server
    if(bbb_session_running($meeting_id) == false) {
        create_meeting($title,$meeting_id,$mod_pw,$att_pw,$record);
    }
    # Get session capacity
    $c = Database::get()->querySingle("SELECT sessionUsers, mod_pw FROM tc_session WHERE meeting_id=?s",$meeting_id);    
    $serv = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $sess->running_at);

    if($c->sessionUsers < get_meeting_users($serv->server_key,$serv->api_url,$meeting_id,$c->mod_pw))
    {
        Session::Messages($langBBBMaxUsersJoinError, 'alert-warning');
        redirect_to_home_page();    
        exit;    
    } else {
        header('Location: ' . bbb_join_user($meeting_id,$att_pw,$_GET['username'],""));
    }
}

if ($server_type == 'om') { // OM server
    if(om_session_running($meeting_id) == false) {
        create_om_meeting($title, $meeting_id, $record);
    }
    # Get session capacity
    $c = Database::get()->querySingle("SELECT sessionUsers, mod_pw FROM tc_session where meeting_id=?s",$meeting_id);    
    $serv = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id=?d", $sess->running_at);

    if ($c->sessionUsers < get_om_connected_users($server_id))
    {
        Session::Messages($langBBBMaxUsersJoinError, 'alert-warning');
        redirect_to_home_page();    
        exit;    
    } else {
        header('Location: ' . om_join_user($meeting_id, $_GET['username'], -1, "", $_GET['username'], "", 0));
    }
}