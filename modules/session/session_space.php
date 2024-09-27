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
 * @file session_space.php
 * @brief Session space
 */

$require_login = true;
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'session_space';

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'modules/document/doc_init.php';
require_once 'modules/progress/process_functions.php';
require_once 'functions.php';

check_activation_of_collaboration();

load_js('tools.js');

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}
elseif(isset($_GET['id'])){
    $data['sessionID'] = $sessionID = $_GET['id'];
}

doc_init();

session_exists($sessionID);
check_user_belongs_in_session($sessionID);

$pageName = title_session($course_id,$sessionID);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$toolName = $langSession;

$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toReorder'])) {
        reorder_table('session_resources', null, null, $_POST['toReorder'], isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
    }
    exit;
}


// Hide or show resource
if(isset($_GET['vis_res'])){
    $infoResource = Database::get()->querySingle("SELECT res_id,type FROM session_resources WHERE id = ?d AND session_id = ?d", $_GET['res_id'], $_GET['session']);
    if($infoResource->type == 'doc'){
        Database::get()->query("UPDATE document SET visible = ?d 
                                WHERE id = ?d
                                AND course_id = ?d
                                AND subsystem_id = ?d", $_GET['vis_res'], $infoResource->res_id, $course_id, $_GET['session']);
    }
    Database::get()->query("UPDATE session_resources SET visible = ?d WHERE id = ?d AND session_id = ?d", $_GET['vis_res'], $_GET['res_id'], $_GET['session']);
    Session::flash('message',$langDocCompletionSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$_GET['session']);
}

// ---------------------------
// download directory or file
// ---------------------------
if (isset($_GET['download'])) {
    $downloadDir = getDirectReference($_GET['download']);

    if ($downloadDir == '/') {
        $format = '.dir';
        $real_filename = remove_filename_unsafe_chars($langDoc . ' ' . $public_code);
    } else {

        $subSystem = MYSESSIONS;
        if(isset($_GET['downloadReference'])){
            $subSystem = SESSION_REFERENCE;
        }
        $q = Database::get()->querySingle("SELECT filename, format, visible, extra_path, public FROM document
                        WHERE course_id = ?d AND subsystem = ?d AND subsystem_id = ?d AND
                        path = ?s", $course_id, $subSystem, $sessionID, $downloadDir);
                        
        
        if (!$q) {
            not_found($downloadDir);
        }
        $real_filename = $q->filename;
        $format = $q->format;
        $visible = $q->visible;
        $extra_path = $q->extra_path;
        $public = $q->public;
        if (!(resource_access($visible, $public) or (isset($status) and $status == USER_TEACHER))) {
            not_found($downloadDir);
        }
    }
    // Allow unlimited time for creating the archive
    set_time_limit(0);

    if ($format == '.dir') {
        if (!$uid) {
            forbidden($downloadDir);
        }
        $real_filename = $real_filename . '.zip';
        $dload_filename = $webDir . '/courses/temp/' . safe_filename('zip');
        zip_documents_directory($dload_filename, $downloadDir, $can_upload);
        $delete = true;
    } elseif ($extra_path) {
        if ($real_path = common_doc_path($extra_path, true)) {
            // Common document
            if (!$common_doc_visible) {
                forbidden($downloadDir);
            }
            $dload_filename = $real_path;
            $delete = false;
        } else {
            // External document - redirect to URL
            redirect($extra_path);
        }
    } else {
        $basedir = $webDir . '/courses/' . $course_code . '/session/session_' . $sessionID;
        $dload_filename = $basedir . $downloadDir;
        $delete = false;
    }

    send_file_to_client($dload_filename, $real_filename, null, true, $delete);
    exit;
}

if(isset($_GET['editResource'])){
    $resource_id = $_GET['editResource'];
    redirect_to_home_page("modules/session/edit_resource.php?course=".$course_code."&session=".$sessionID."&resource_id=".$resource_id);
}
if(isset($_GET['show_passage'])){
    $resource_id = $_GET['show_passage'];
    redirect_to_home_page("modules/session/edit_resource.php?course=".$course_code."&session=".$sessionID."&resource_id=".$resource_id."&passage_resource=true");
}

if(isset($_GET['del'])){

    $activity_result = 2;
    if (prereq_session_has_completion_enabled($sessionID)) {
        $activity_result = session_resource_completion($sessionID, $_GET['del']);
    }

    if($activity_result == 2){
        $q = Database::get()->querySingle("SELECT res_id,type FROM session_resources WHERE id = ?d",$_GET['del']);
        $file = Database::get()->queryArray("SELECT filename,path FROM document WHERE id = ?d",$q->res_id);
        if(count($file) > 0){
            $target_dir = "$webDir/courses/$course_code/session/session_$sessionID/";
            foreach($file as $f){
                unlink($target_dir.$f->path);
            }

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Here we will delete the user's files that related to the current deliverable.
            $users_docs = Database::get()->queryArray("SELECT id,res_id,from_user,type FROM session_resources 
                                                        WHERE doc_id = ?d AND session_id = ?d AND from_user > 0",$q->res_id,$sessionID);
            if(count($users_docs) > 0){
                foreach($users_docs as $d){
                    $currentUser = $d->from_user;
                    $target_userdir = "$webDir/courses/$course_code/session/session_$sessionID/$currentUser/";
                    $files_user = Database::get()->queryArray("SELECT filename,path FROM document 
                                                                WHERE id = ?d
                                                                AND course_id = ?d
                                                                AND subsystem = ?d
                                                                AND subsystem_id = ?d
                                                                AND lock_user_id = ?d", $d->res_id, $course_id, MYSESSIONS, $sessionID, $currentUser);
                    foreach($files_user as $f){
                        unlink($target_userdir.$f->path);
                    }
                    if($d->type == 'doc'){
                        Database::get()->query("DELETE FROM document 
                                                WHERE id = ?d 
                                                AND course_id = ?d
                                                AND subsystem = ?d 
                                                AND subsystem_id = ?d
                                                AND lock_user_id = ?d", $d->res_id, $course_id, MYSESSIONS, $sessionID, $currentUser);

                        Database::get()->query("DELETE FROM session_resources WHERE id = ?d AND session_id = ?d AND from_user = ?d", $d->id, $sessionID, $currentUser);
                    }
                }
            }
            ///////////////////////////////////////////////////////////////////////////////////////////
        }
        if($q->type == 'doc'){
            Database::get()->query("DELETE FROM document WHERE id = ?d AND subsystem = ?d",$q->res_id,MYSESSIONS);
        }
        if($q->type == 'doc_reference'){
            Database::get()->query("DELETE FROM document WHERE id = ?d AND subsystem = ?d",$q->res_id,SESSION_REFERENCE);
        }
        if($q->type == 'tc'){
            Database::get()->query("DELETE FROM tc_session WHERE id = ?d AND course_id = ?d AND id_session = ?d",$q->res_id,$course_id,$sessionID);
        }
        Database::get()->query("DELETE FROM session_resources WHERE id = ?d",$_GET['del']);
        Session::flash('message',$langSessionResourseDeleted);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sessionID);
    }else{
        Session::flash('message',$langSessionResourseParticipatesInSessionCompletion);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sessionID);
    }
    
}
$data['tool_content_sessions'] = show_session_resources($sessionID);

// An consultant can create a session
if($is_coordinator or $is_consultant){
    if($is_coordinator){
        $data['all_session'] = Database::get()->queryArray("SELECT * FROM mod_session 
                                                WHERE course_id = ?d
                                                ORDER BY start ASC",$course_id);
    }elseif($is_consultant){
        $data['all_session'] = Database::get()->queryArray("SELECT * FROM mod_session 
                                    WHERE course_id = ?d AND creator = ?d
                                    ORDER BY start ASC",$course_id,$uid);
    }

    // calculating session completion
    foreach ($data['all_session'] as $s) {
        $all_participants_ids = session_participants_ids($s->id);
        foreach($all_participants_ids as $p){
            if(!$s->type_remote){
                // This refers to session completion with completed meeting.
                check_session_completion_by_meeting_completed($s->id,$p);
            }elseif($s->type_remote){
                // This refers to session completion with completed tc.
                check_session_completion_by_tc_completed($s->id,$p);
            }
            // This refers to session completion for other activities.
            check_session_progress($s->id,$p);  // check session completion - call to Game.php
        }
    }

}else{// is simple user

    $session_info = Database::get()->querySingle("SELECT * FROM mod_session WHERE id = ?d",$sessionID);
    if((date('Y-m-d H:i:s') < $session_info->start) or !$session_info->visible){
        redirect_to_home_page("modules/session/index.php?course=".$course_code);
    }
    $data['all_session'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                    WHERE visible = ?d
                                                    AND course_id = ?d
                                                    AND id IN (SELECT session_id FROM mod_session_users
                                                                WHERE participants = ?d AND is_accepted = ?d)
                                                    ORDER BY start ASC",1,$course_id,$uid,1); 

    foreach ($data['all_session'] as $s) {
        if(!$s->type_remote){
            // This refers to session completion with completed meeting.
            check_session_completion_by_meeting_completed($s->id,$uid);
        }elseif($s->type_remote){
            // This refers to session completion with completed tc.
            check_session_completion_by_tc_completed($s->id,$uid);
        }
        // This refers to session completion for other activities.
        check_session_progress($s->id,$uid);  // check session completion - call to Game.php
    }

    $visible_sessions_id = array();
    $visible_user_sessions = findUserVisibleSessions($uid, $data['all_session']);
    foreach ($visible_user_sessions as $d) {
        $visible_sessions_id[] = $d->id;
    }
    if(!in_array($sessionID, $visible_sessions_id)){
        Session::flash('message',$langSessionNotCompleted);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/session/index.php?course=".$course_code);
    }

}

// check if current session is completed by all users
$is_session_completed = false;
$is_session_completed_message = "";
$sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $sessionID);
if ($sql_badge) {
    $per = 0;
    $badge_id = $sql_badge->id;
    $participants = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d",$sessionID,1);
    if(count($participants) > 0){
        foreach($participants as $p){
            $per = $per + get_cert_percentage_completion_by_user('badge',$badge_id,$p->participants);
        }
    }
    if( count($participants) > 0 && $per/count($participants) == 100 ){
            $is_session_completed = true;
            $is_session_completed_message .= "  <div class='d-flex justify-content-start align-items-start gap-2'>
                                                    <div>
                                                        <span class='badge Success-200-bg d-flex justify-content-center align-items-center' style='width:25px; height:25px; border-radius:50%;'>
                                                            <i class='fa-solid fa-check fa-lg pt-1'></i>
                                                        </span>
                                                    </div>
                                                    <p class='mb-0'>$langUsersHaveCompletedCriteria</p>
                                             ";
    }else{
        $is_session_completed_message .= "  <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                <p>$langUsersCompletedCriteriaInProgress</p>
                                                <div>
                                                    <div class='spinner-grow text-success spinner-grow-sm' role='status'>
                                                        <span class='visually-hidden'></span>
                                                    </div>
                                                    <div class='spinner-grow text-danger spinner-grow-sm' role='status'>
                                                        <span class='visually-hidden'></span>
                                                    </div>
                                                    <div class='spinner-grow text-warning spinner-grow-sm' role='status'>
                                                        <span class='visually-hidden'></span>
                                                    </div>
                                                    <div class='spinner-grow text-info spinner-grow-sm' role='status'>
                                                        <span class='visually-hidden'></span>
                                                    </div>
                                                </div>
                                            </div>
        ";
    }
}
$data['is_session_completed_message'] = $is_session_completed_message;

$data['participants'] = Database::get()->queryArray("SELECT participants FROM mod_session_users 
                                                     WHERE session_id = ?d AND is_accepted = ?d",$sessionID,1);

$data['comments'] = Database::get()->querySingle("SELECT comments FROM mod_session WHERE id = ?d",$sessionID);

$data['prereq_session'] = Database::get()->querySingle("SELECT title FROM mod_session 
                                                        WHERE id IN (SELECT prerequisite_session FROM session_prerequisite 
                                                                     WHERE course_id = ?d
                                                                     AND session_id = ?d)",$course_id,$sessionID);

view('modules.session.session_space', $data);
