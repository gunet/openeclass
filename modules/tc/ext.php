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
require_once 'include/sendMail.inc.php';
// For creating bbb urls & params
require_once 'bbb-api.php';
require_once 'functions.php';

//$total = Database::get()->querySingle("SELECT COUNT(*) AS count FROM bbb_servers WHERE enabled='true'")->count;
//echo $total;

//print_r($_GET);

$mod_pw = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->mod_pw;
$title = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->title;
$att_pw = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->att_pw;
$record = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->record;
$start_date = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->start_date;
$end_date = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->end_date;
$active = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->active;
$unlock_interval = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->unlock_interval;
$r_group = explode(",",Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id'])->external_users);

if ($active<>'1' || date_diff_in_minutes($start_date,date('Y-m-d H:i:s'))> $unlock_interval || date_diff_in_minutes(date('Y-m-d H:i:s'),$start_date) > 1440 || !is_active_bbb_server() || !in_array($_GET['username'],$r_group))
{
    echo $langBBBNoteEnableJoin;
    exit;
}
if(bbb_session_running($_GET['meeting_id']) == false)
{
    //echo $title;
    create_meeting($title,$_GET['meeting_id'],$mod_pw,$att_pw,$record);
}
# Get session capacity
$c = Database::get()->querySingle("SELECT sessionUsers FROM bbb_session where meeting_id=?s",$_GET['meeting_id']);
$sess = Database::get()->querySingle("SELECT * FROM bbb_session WHERE meeting_id=?s",$_GET['meeting_id']);
$serv = Database::get()->querySingle("SELECT * FROM bbb_servers WHERE id=?d", $sess->running_at);

if( ($c->sessionUsers > 0) && ($c->sessionUsers < get_meeting_users($serv->server_key,$serv->api_url,$_GET['meeting_id'],$sess->mod_pw)))
{
    $tool_content .= "<div class='alert alert-warning'>$langBBBMaxUsersJoinError</div>";
}
else {
        header('Location: ' . bbb_join_user($_GET['meeting_id'],$att_pw,$_GET['username'],""));
}
