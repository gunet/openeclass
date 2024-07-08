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
 * @file resource_space.php
 * @brief Resource space of a session
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'modules/document/doc_init.php';
require_once 'functions.php';

check_activation_of_collaboration();

load_js('tools.js');
load_js('screenfull/screenfull.min.js');
ModalBoxHelper::loadModalBox(true);

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}elseif(isset($_GET['id'])){
    $data['sessionID'] = $sessionID = $_GET['id'];
}

if(isset($_GET['resource_id'])){
    $data['resource_id'] = $resource_id = $_GET['resource_id'];
}

if(isset($_GET['file_id'])){
    $data['file_id'] = $file_id = $_GET['file_id'];
}

doc_init();

session_exists($sessionID);

$sessionTitle = title_session($course_id,$sessionID);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);

$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));

if(!$is_consultant){
    $pageName = $langDownloadFile;
}else{
    $pageName = $langDocSender;
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
        $basedir = $webDir . '/courses/' . $course_code . '/session/session_' . $sessionID . '/' . $_GET['userID'];
        $dload_filename = $basedir . $downloadDir;
        $delete = false;
    }

    send_file_to_client($dload_filename, $real_filename, null, true, $delete);
    exit;
}

// ---------------------------
// Delete deliverable
// ---------------------------

if(isset($_POST['delete_resource'])){
    $r = Database::get()->querySingle("SELECT doc_id,from_user,is_completed FROM session_resources 
                                                    WHERE res_id = ?d AND session_id = ?d",$_POST['delete_resource'],$sessionID);
    if($r){
        $resource_id = $r->doc_id;
        if(!$is_consultant && $r->is_completed){
            Session::flash('message',$langForbidden);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sessionID); 
        }
        $badge = Database::get()->querySingle("SELECT id FROM badge WHERE session_id = ?d AND course_id = ?d",$sessionID,$course_id);
        if($badge){
            $badge_id = $badge->id;
            $badge_criterion = Database::get()->querySingle("SELECT id FROM badge_criterion
                                                              WHERE badge = ?d
                                                              AND resource = ?d
                                                              AND activity_type = ?s",$badge_id,$resource_id,'document-submit');
            if($badge_criterion){
                $badge_criterion_id = $badge_criterion->id;
                Database::get()->query("DELETE FROM user_badge_criterion 
                                            WHERE user = ?d AND badge_criterion = ?d",$r->from_user,$badge_criterion_id);
            }
        }
        
    }

    $file = Database::get()->queryArray("SELECT filename,path,lock_user_id FROM document WHERE id = ?d",$_POST['delete_resource']);
    if(count($file) > 0){
        foreach($file as $f){
            $user_doc = $f->lock_user_id;
            $target_dir = "$webDir/courses/$course_code/session/session_$sessionID/$user_doc/";
            unlink($target_dir.$f->path);
        }
    }
    Database::get()->query("DELETE FROM document WHERE id = ?d AND subsystem = ?d",$_POST['delete_resource'],MYSESSIONS);
    Database::get()->query("DELETE FROM session_resources WHERE session_id = ?d AND res_id = ?d",$sessionID,$_POST['delete_resource']);
    Session::flash('message',$langSessionResourseDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/resource_space.php?course=".$course_code."&session=".$sessionID."&resource_id=".$_GET['resource_id']."&file_id=".$_GET['file_id']); 
}


// ---------------------------
// Upload a deliverable for a user from consultant
// ---------------------------

$upload_for_user = false;
if(isset($_GET['upload_for_user'])){
    $upload_for_user = true;
}
$data['upload_doc_for_user'] = $upload_for_user;

// ---------------------------
// uploaded doc completion by consultant
// ---------------------------
if(isset($_POST['userBadgeCriterionId'])){
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    $exists_criterio = Database::get()->querySingle("SELECT id FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d",$_POST['userSender'],$_POST['userBadgeCriterionId']);
    if(!$exists_criterio){
        Database::get()->query("INSERT INTO user_badge_criterion SET 
                            user = ?d,
                            created = " . DBHelper::timeAfter() . ",
                            badge_criterion = ?d",$_POST['userSender'],$_POST['userBadgeCriterionId']);

        Database::get()->query("UPDATE session_resources SET
                                is_completed = ?d
                                WHERE session_id = ?d AND res_id = ?d AND type = ?s AND from_user = ?d",1,$sessionID,$_POST['document_id'],'doc',$_POST['userSender']);

        Session::flash('message',$langDocCompletionSuccess);
        Session::flash('alert-class', 'alert-success');
    }else{
        Database::get()->query("DELETE FROM user_badge_criterion WHERE user = ?d AND badge_criterion = ?d",$_POST['userSender'],$_POST['userBadgeCriterionId']);

        Database::get()->query("UPDATE session_resources SET
                                    is_completed = ?d
                                    WHERE session_id = ?d AND res_id = ?d AND type = ?s AND from_user = ?d",0,$sessionID,$_POST['document_id'],'doc',$_POST['userSender']);

        Session::flash('message',$langDocCompletionNoSuccess);
        Session::flash('alert-class', 'alert-warning');
    }
     
    redirect_to_home_page("modules/session/resource_space.php?course=".$course_code."&session=".$sessionID."&resource_id=".$_GET['resource_id']."&file_id=".$_GET['file_id']); 
} 

// ---------------------------
// add comment to deliverable by consultant
// ---------------------------
if(isset($_POST['add_comment'])){
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    Database::get()->query("UPDATE session_resources SET
                                    deliverable_comments = ?s
                                    WHERE session_id = ?d 
                                    AND doc_id = ?d 
                                    AND from_user = ?d",q($_POST['add_comment']), $sessionID, $_POST['for_resource_id'], $_POST['for_user_id']);

    
    // Send email to user
    $sessionName =  title_session($course_id,$sessionID);
    $nameUser = participant_name($_POST['for_user_id']);
    $emailHeader = "
      <!-- Header Section -->
              <div id='mail-header'>
                  <br>
                  <div>
                      <div id='header-title'>$sessionName</div>
                  </div>
              </div>";

      $emailMain = "
      <!-- Body Section -->
          <div id='mail-body'>
              <br>
              <div><strong>$langSomeComments</strong></div>
              <div id='mail-body-inner'>
                  <p>" . purify($_POST['add_comment']) . "</p>
              </div>
              <div>
                  <br>
                  <p>$langProblem</p><br>" . get_config('admin_name') . "
                  <ul id='forum-category'>
                      <li>$langManager: $siteName</li>
                      <li>$langTel: -</li>
                      <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                  </ul>
              </div>
          </div>";

    $emailsubject = $siteName.':'.$langSomeComments;

    $emailbody = $emailHeader.$emailMain;

    $emailPlainBody = html2text($emailbody);
    $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$_POST['for_user_id'])->email;
    send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);

    Session::flash('message',$langAddCommentsSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/resource_space.php?course=".$course_code."&session=".$sessionID."&resource_id=".$_GET['resource_id']."&file_id=".$_GET['file_id']); 
}


// ---------------------------
// Get information about resource and creation downloadable link
// ---------------------------

$link = '';
$download_hidden_link = '';
$resource_info = session_resource_info($resource_id,$sessionID);
$file = Database::get()->querySingle("SELECT * FROM document WHERE course_id = ?d AND id = ?d", $course_id, $file_id);
if($file){
    if($file->subsystem != MYSESSIONS){// These files are regarded with course documents
        $image = choose_image('.' . $file->format);
        $download_url = "{$urlServer}modules/document/index.php?course=$course_code&amp;download=" . getInDirectReference($file->path);
        $download_hidden_link = ($can_upload || visible_module(MODULE_ID_DOCS))?
            "<input type='hidden' value='$download_url'>" : '';
        $file_obj = MediaResourceFactory::initFromDocument($file);
        $file_obj->setAccessURL(file_url($file->path, $file->filename));
        $file_obj->setPlayURL(file_playurl($file->path, $file->filename));
        $link = MultimediaHelper::chooseMediaAhref($file_obj);
    }else{// These files are regarded with session documents
        $image = choose_image('.' . $file->format);
        $download_url = "{$urlServer}modules/session/session_space.php?course=$course_code&amp;session=$sessionID&amp;download=" . getInDirectReference($file->path);
        $download_hidden_link = ($can_upload || visible_module(MODULE_ID_DOCS))?
            "<input type='hidden' value='$download_url'>" : '';
        $file_obj = MediaResourceFactory::initFromDocument($file);
        $file_obj->setAccessURL(session_file_url($file->path, $file->filename));
        $file_obj->setPlayURL(session_file_playurl($file->path, $file->filename));
        $link = MultimediaHelper::chooseMediaAhref($file_obj);
    }
}else{
    redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sessionID);
}
$data['resource_info'] = $resource_info;
$data['file'] = $file;
$data['link'] = $link;
$data['download_hidden_link'] = $download_hidden_link;


// ---------------------------
// Get resources which are selected as criteria for session completion
// ---------------------------

$resources = array();
$badge = Database::get()->querySingle("SELECT id FROM badge WHERE session_id = ?d AND course_id = ?d",$sessionID,$course_id);
if($badge){
    $badge_id = $badge->id;
    $resources = Database::get()->queryArray("SELECT * FROM session_resources
                                                WHERE res_id IN (SELECT resource FROM badge_criterion WHERE badge = ?d AND activity_type = ?s)
                                                AND doc_id = ?d
                                                AND session_id = ?d",$badge_id,'document-submit',0,$sessionID);
}
$data['resources'] = $resources;



// An consultant can create a session
$total_deliverables = 0;
if($is_coordinator or $is_consultant){
    $sql = "AND id IN (SELECT res_id FROM session_resources WHERE doc_id = $file_id AND from_user > 0)";
    $total_info = Database::get()->querySingle("SELECT COUNT(*) as total FROM session_resources
                                                WHERE session_id = ?d AND doc_id = ?d AND from_user > 0",$sessionID,$file_id);
    if($total_info){
        $total_deliverables = $total_info->total;
    }
}else{// is simple user
    $sql = "AND lock_user_id = $uid AND id IN (SELECT res_id FROM session_resources WHERE doc_id = $file_id)";
}
$data['total_deliverables'] = $total_deliverables;

$docs = Database::get()->queryArray("SELECT * FROM document
                                            WHERE course_id = ?d
                                            AND subsystem = ?d
                                            AND subsystem_id = ?d
                                            $sql", $course_id, MYSESSIONS, $sessionID);

if(count($docs) > 0){
    $badge_id = 0;
    if($is_consultant || $is_course_reviewer){
        $badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d",$course_id,$sessionID);
        if($badge){
            $badge_id = $badge->id;
        }
    }
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
        $file->fileTitle = $file->title ?? $file->filename;
        
        $refers_temp = Database::get()->querySingle("SELECT doc_id,from_user,is_completed,deliverable_comments FROM session_resources WHERE res_id = ?d AND session_id = ?d",$file->id,$sessionID);
        $refers = Database::get()->querySingle("SELECT title FROM session_resources WHERE res_id = ?d AND session_id = ?d",$refers_temp->doc_id,$sessionID);
        $file->refers_to = $refers->title;
        $file->deliverable_comment = $refers_temp->deliverable_comments;
        if(($is_consultant || $is_course_reviewer) && $badge_id > 0){
            $user_badge_criterion = Database::get()->querySingle("SELECT id FROM badge_criterion 
                                                                    WHERE resource = ?d 
                                                                    AND activity_type = ?s 
                                                                    AND badge = ?d",$refers_temp->doc_id,'document-submit',$badge_id);

            if($user_badge_criterion){
                $file->user_badge_criterion_id = $user_badge_criterion->id; 
            }
            $file->user_sender = $refers_temp->from_user;
            $file->completed = $refers_temp->is_completed;
            $file->can_delete_file = 1;                            
        }
        if(!$is_consultant){
            if(!$refers_temp->is_completed){
                $can_delete = 1;
            }else{
                $can_delete = 0;
            }
            $file->can_delete_file = $can_delete;
        }
    }
}
$data['docs'] = $docs;

$data['users_participants'] = session_participants_ids($sessionID);

$data['is_criterion_completion'] = false;
if (resource_belongs_to_session_completion($sessionID, $file_id)) {
    $data['is_criterion_completion'] = true;
}

view('modules.session.resource_space', $data);
