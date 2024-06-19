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
 * @file doc_uploaded.php
 * @brief Display uploaded docs by users
 */

$require_login = true;
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'course_sessions_uploaded_docs';

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'modules/progress/process_functions.php';
require_once 'modules/document/doc_init.php';
require_once 'functions.php';

check_activation_of_collaboration();

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}
elseif(isset($_GET['id'])){
    $data['sessionID'] = $sessionID = $_GET['id'];
}

doc_init();

session_exists($sessionID);

load_js('tools.js');
load_js('datatables');

$sessionTitle = title_session($course_id,$sessionID);
$pageName = $langDocSender;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);


$data['is_tutor_course'] = $is_tutor_course = is_tutor_course($course_id,$uid);
$data['is_consultant'] = $is_consultant = is_consultant($course_id,$uid);
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));
student_view_is_active();


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
        $basedir = $webDir . '/courses/' . $course_code . '/session/session_' . $sessionID . '/' . $_GET['userID'];
        $dload_filename = $basedir . $downloadDir;
        $delete = false;
    }

    send_file_to_client($dload_filename, $real_filename, null, true, $delete);
    exit;
}


if(isset($_GET['del'])){
    $file = Database::get()->queryArray("SELECT filename,path,lock_user_id FROM document WHERE id = ?d",$_GET['del']);
    if(count($file) > 0){
        foreach($file as $f){
            $user_doc = $f->lock_user_id;
            $target_dir = "$webDir/courses/$course_code/session/session_$sessionID/$user_doc/";
            unlink($target_dir.$f->path);
        }
    }
    Database::get()->query("DELETE FROM document WHERE id = ?d AND subsystem = ?d",$_GET['del'],MYSESSIONS);
    Database::get()->query("DELETE FROM session_resources WHERE session_id = ?d AND res_id = ?d",$sessionID,$_GET['del']);
    Session::flash('message',$langSessionResourseDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/doc_uploaded.php?course=".$course_code."&session=".$sessionID);  
}

// An consultant can create a session
if($is_tutor_course or $is_consultant){
    $sql = "AND lock_user_id != $uid";
}else{// is simple user
    $sql = "AND lock_user_id = $uid";
}

$docs = Database::get()->queryArray("SELECT * FROM document
                                            WHERE course_id = ?d
                                            AND subsystem = ?d
                                            $sql
                                            AND subsystem_id = ?d", $course_id, MYSESSIONS, $sessionID);

if(count($docs) > 0){
    foreach($docs as $file){
        $image = choose_image('.' . $file->format);
        $download_url = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;session=$sessionID&amp;download=" . getInDirectReference($file->path) . "&userID=" .$file->lock_user_id;
        $file_obj = MediaResourceFactory::initFromDocument($file);
        $file_obj->setAccessURL(session_file_uploaded_url($file->path, $file->filename, $file->lock_user_id));
        $file_obj->setPlayURL(session_file_uploaded_playurl($file->path, $file->filename, $file->lock_user_id));
        $link = MultimediaHelper::chooseMediaAhref($file_obj);
        $file->image = $image;
        $file->link = $link;
        $file->download_url = $download_url;
        
        $refers_temp = Database::get()->querySingle("SELECT doc_id FROM session_resources WHERE res_id = ?d AND session_id = ?d",$file->id,$sessionID);
        $refers = Database::get()->querySingle("SELECT title FROM session_resources WHERE res_id = ?d AND session_id = ?d",$refers_temp->doc_id,$sessionID);
        $file->refers_to = $refers->title;
    }
}
$data['docs'] = $docs;

$data['action_bar'] = action_bar([
    [
        'title' => $langTableCompletedConsulting,
        'url' => $urlAppend . "modules/session/session_space.php?course=" . $course_code . "&session=" . $sessionID,
        'icon' => 'fa-solid fa-list',
        'button-class' => 'btn-success'
    ],
], false);

view('modules.session.docs_uploaded', $data);
