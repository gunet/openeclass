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
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'functions.php';

$sessionTitle = title_session($course_id,$_GET['session']);
$pageName = $langEditSession;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $_GET['session'] , 'name' => $sessionTitle);

load_js('tools.js');
load_js('select2');
load_js('bootstrap-datetimepicker');

if(isset($_POST['modify'])){
  if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

  $v = new Valitron\Validator($_POST);
  $v->rule('required', array('creators'));
  $v->rule('required', array('title'));
  $v->rule('required', array('start_session'));
  $v->rule('required', array('end_session'));
  if(isset($_POST['session_type']) and $_POST['session_type']=='one'){
    $v->rule('required', array('one_participant'));
  }elseif(isset($_POST['session_type']) and $_POST['session_type']=='group'){
    $v->rule('required', array('many_participants'));
  }
  $v->labels(array(
      'title' => "$langTheField $langTitle",
      'creators' => "$langTheField $langCreator",
      'one_participant' => "$langTheField $langSelectUser",
      'many_participants' => "$langTheField $langSelectUser",
      'start_session' => "$langTheField $langDate",
      'end_session' => "$langTheField $langDate"
  ));

  if($v->validate()) {
    $start_session = !empty($_POST['start_session']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['start_session'])->format('Y-m-d H:i:s') : null;
    $end_session = !empty($_POST['end_session']) ? DateTime::createFromFormat('d-m-Y H:i', $_POST['end_session'])->format('Y-m-d H:i:s') : null;
    if(!is_null($start_session) && !is_null($end_session) && $end_session < $start_session){
      Session::flash('message',$langAddInCorrectDateVal);
      Session::flash('alert-class', 'alert-danger');
      redirect_to_home_page("modules/session/new.php?course=".$course_code);
    }
    $creator = isset($_POST['creators']) ? $_POST['creators'] : 0;
    $title = isset($_POST['title']) ? q($_POST['title']) : '';
    $comments = isset($_POST['comments']) ? purify($_POST['comments']) : null;
    $type_session = $_POST['session_type'];
    $visible_session = (isset($_POST['session_visible']) and $_POST['session_visible']=='on') ? 1 : 0;

    $insert = Database::get()->query("UPDATE mod_session SET
                                        creator = ?d,
                                        title = ?s,
                                        comments = ?s,
                                        type = ?s,
                                        start = ?t,
                                        finish = ?t,
                                        visible = ?d
                                        WHERE course_id = ?d
                                        AND id = ?d",$creator, $title, $comments, $type_session, $start_session, $end_session, $visible_session, $course_id, $_GET['session']);

    if(isset($_POST['session_type']) and $_POST['session_type']=='one'){
      Database::get()->query("DELETE FROM mod_session_users WHERE session_id = ?d",$_GET['session']);
      $insert_users = Database::get()->query("INSERT INTO mod_session_users SET 
                                                participants = ?d,
                                                session_id = ?d", $_POST['one_participant'], $_GET['session']);
    }elseif(isset($_POST['session_type']) and $_POST['session_type']=='group'){
      Database::get()->query("DELETE FROM mod_session_users WHERE session_id = ?d",$_GET['session']);
      foreach($_POST['many_participants'] as $m){
        $insert_users = Database::get()->query("INSERT INTO mod_session_users SET 
                                                  session_id = ?d,
                                                  participants = ?d", $_GET['session'], $m);
      }
    }

    if($insert_users){
      Session::flash('message',$langAddSessionCompleted);
      Session::flash('alert-class', 'alert-success');
      redirect_to_home_page("modules/session/index.php?course=".$course_code);
    }else{
      Session::flash('message',$langAddSessionNotCompleted);
      Session::flash('alert-class', 'alert-danger');
      redirect_to_home_page("modules/session/edit.php?course=".$course_code."&session=".$_GET['session']);
    }
    
  }else{
    Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
    redirect_to_home_page("modules/session/edit.php?course=".$course_code."&session=".$_GET['session']);
  }

}

$data['session_id'] = $_GET['session'];
$data['is_tutor_course'] = $is_tutor_course = is_tutor_course($course_id,$uid);
$data['session_info'] = $session_info = Database::get()->querySingle("SELECT * FROM mod_session WHERE id = ?d",$_GET['session']);
$data['title'] = $session_info->title;
$data['creator'] = $session_info->creator;
$data['comments'] = rich_text_editor('comments', 5, 40, $session_info->comments);
$data['session_type'] = $session_info->type;
$startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $session_info->start);
$data['start'] = q($startDate_obj->format('d-m-Y H:i'));
$endDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $session_info->finish);
$data['finish'] = q($endDate_obj->format('d-m-Y H:i'));
$data['visible'] = $session_info->visible;
$users_participants = Database::get()->queryArray("SELECT participants FROM mod_session_users
                                                            WHERE session_id = ?d",$_GET['session']);
$participants_arr = [];
if(count($users_participants) > 0){
    foreach($users_participants as $u){
        $participants_arr[] = $u->participants;
    }
}
$data['participants_arr'] = $participants_arr;               

if($is_tutor_course){// is the tutor course
  $data['creators'] = Database::get()->queryArray("SELECT course_user.user_id,user.givenname,user.surname FROM course_user
                                                    LEFT JOIN user ON course_user.user_id=user.id
                                                    WHERE course_user.editor = ?d
                                                    AND course_user.course_id = ?d", 1, $course_id);
}else{// is the consultant
  $data['creators'] = Database::get()->queryArray("SELECT id,givenname,surname FROM user WHERE id = ?d",$uid);
}
 

$data['simple_users'] = Database::get()->queryArray("SELECT course_user.user_id,user.givenname,user.surname FROM course_user
                                                      LEFT JOIN user ON course_user.user_id=user.id
                                                      WHERE course_user.status = ?d
                                                      AND course_user.tutor = ?d
                                                      AND course_user.editor = ?d
                                                      AND course_reviewer = ?d
                                                      AND course_user.reviewer = ?d
                                                      AND course_user.course_id = ?d", USER_STUDENT, 0, 0, 0, 0, $course_id);

$data['action_bar'] = action_bar([
    [ 'title' => $langBack,
      'url' => 'index.php?course=' . $course_code,
      'icon' => 'fa-reply',
      'button-class' => 'btn-success',
      'level' => 'primary-label' ]
], false);


view('modules.session.edit', $data);
