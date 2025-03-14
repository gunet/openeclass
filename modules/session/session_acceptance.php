<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */


/**
 * @file session_acceptance.php
 * @brief Accept participation in current session
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'functions.php';

check_activation_of_collaboration();

$pageName = $langAddSession;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}else{
    $data['sessionID'] = $sessionID = 0;
}

session_exists($sessionID);
//check_user_belongs_in_session($sessionID);

if(isset($_POST['submit'])){
  if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

  $message = "";
  if($_POST['submit'] == 'acceptance'){
    Database::get()->query("UPDATE mod_session_users SET 
                            is_accepted = ?d
                            WHERE session_id = ?d 
                            AND participants = ?d", 1, $sessionID, $_POST['userId']);

    $message = "$langUserHasAcceptedSession" . "&nbsp;" . participant_name($_POST['userId']);
  }else{
    $message = "$langUserHasNotAcceptedSession" . "&nbsp;" . participant_name($_POST['userId']);
  }

  $session_title = title_session($course_id,$sessionID);
  $course_title = course_id_to_title($course_id);
  $consultant_id = get_session_consultant($sessionID,$course_id);
  $emailConsultant = uid_to_email($consultant_id);
  $emailHeader = "
      <!-- Header Section -->
              <div id='mail-header'>
                  <br>
                  <div>
                      <div id='header-title'>$session_title&nbsp;&nbsp;<span>($course_title)</span></div>
                  </div>
              </div>";

      $emailMain = "
      <!-- Body Section -->
          <div id='mail-body'>
              <br>
              <div id='mail-body-inner'>
                 <p>$message</p>
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

      $emailsubject = $siteName;

      $emailbody = $emailHeader.$emailMain;

      $emailPlainBody = html2text($emailbody);

      if(get_user_email_notification($consultant_id)){
        send_mail_multipart('', '', '', $emailConsultant, $emailsubject, $emailPlainBody, $emailbody);
      }

      Session::flash('message',$langProcessCompleted);
      Session::flash('alert-class', 'alert-success');
      redirect_to_home_page("modules/session/index.php?course=".$course_code);

}


$data['session_info'] = Database::get()->queryArray("SELECT * FROM mod_session WHERE id = ?d",$_GET['session']);
$data['is_participant'] = participation_in_session($sessionID);

view('modules.session.session_acceptance', $data);
