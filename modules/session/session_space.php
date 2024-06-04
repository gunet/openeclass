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
 * @file index.php
 * @brief Sessions display module
 */

$require_login = true;
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'course_sessions';

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
// require_once 'insert_doc.php';
// require_once 'insert_work.php';
// require_once 'insert_tc.php';
require_once 'functions.php';

load_js('tools.js');

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}
elseif(isset($_GET['id'])){
    $data['sessionID'] = $sessionID = $_GET['id'];
}

session_exists($sessionID);

$pageName = title_session($course_id,$sessionID);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

$data['is_tutor_course'] = $is_tutor_course = is_tutor_course($course_id,$uid);
$data['is_consultant'] = $is_consultant = is_consultant($course_id,$uid);
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toReorder'])) {
        reorder_table('session_resources', null, null, $_POST['toReorder'], isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
    }
    exit;
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
        $q = Database::get()->querySingle("SELECT filename, format, visible, extra_path, public FROM document
                        WHERE course_id = ?d AND subsystem = ?d AND subsystem_id = ?d AND
                        path = ?s", $course_id, MYSESSIONS, $sessionID, $downloadDir);
                        
        
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
    $resourse_id = $_GET['editResource'];
    redirect_to_home_page("modules/session/edit_resource.php?course=".$course_code."&session=".$sessionID."&resource_id=".$resourse_id);
}

if(isset($_GET['del'])){
    $q = Database::get()->querySingle("SELECT res_id,type FROM session_resources WHERE id = ?d",$_GET['del']);
    $file = Database::get()->queryArray("SELECT filename,path FROM document WHERE id = ?d",$q->res_id);
    if(count($file) > 0){
        $target_dir = "$webDir/courses/$course_code/session/session_$sessionID/";
        foreach($file as $f){
            unlink($target_dir.$f->path);
        }
    }
    if($q->type == 'doc'){
        Database::get()->query("DELETE FROM document WHERE id = ?d AND subsystem = ?d",$q->res_id,MYSESSIONS);
    }
    Database::get()->query("DELETE FROM session_resources WHERE id = ?d",$_GET['del']);
    Session::flash('message',$langSessionResourseDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sessionID);
}
$data['tool_content_sessions'] = show_session_resources($sessionID);

// An consultant can create a session
if($is_editor){
    if($is_consultant){
        $data['all_session'] = Database::get()->queryArray("SELECT * FROM mod_session 
                                    WHERE course_id = ?d AND creator = ?d
                                    ORDER BY start ASC",$course_id,$uid);
    }elseif($is_tutor_course){
        $data['all_session'] = Database::get()->queryArray("SELECT * FROM mod_session 
                                                WHERE course_id = ?d
                                                ORDER BY start ASC",$course_id);
    }
}else{// is simple user
    $data['all_session'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                    WHERE visible = ?d
                                                    AND course_id = ?d
                                                    AND ( finish > NOW() OR start > NOW() )
                                                    AND id IN (SELECT session_id FROM mod_session_users
                                                                WHERE participants = ?d)
                                                    ORDER BY start ASC",1,$course_id,$uid); 

    $data['action_bar'] = action_bar([
        [ 'title' => $langBack,
          'url' => $urlAppend . 'modules/session/index.php?course=' . $course_code,
          'icon' => 'fa-reply',
          'button-class' => 'btn-success',
          'level' => 'primary-label' ]
    ], false);
}

$data['participants'] = Database::get()->queryArray("SELECT participants FROM mod_session_users 
                                                     WHERE session_id = ?d",$sessionID);

view('modules.session.session_space', $data);
