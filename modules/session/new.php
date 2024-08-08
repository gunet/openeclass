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
 * @file new.php
 * @brief Session creation
 */

$require_login = true;
$require_current_course = true;
$require_consultant = true;
$require_help = TRUE;
$helpTopic = 'add_session';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'functions.php';

check_activation_of_collaboration();

$pageName = $langAddSession;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

load_js('tools.js');
load_js('select2');
load_js('bootstrap-datetimepicker');

if(isset($_POST['submit'])){
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
  if(isset($_POST['session_type']) and $_POST['session_type']=='one'){
    $v->rule('required', array('one_participant'));
  }elseif(isset($_POST['session_type']) and $_POST['session_type']=='group'){
    $v->rule('required', array('many_participants'));
  }
  $v->labels(array(
      'title' => "$langTheField $langTitle",
      'creators' => "$langTheField $langCreator",
      'one_participant' => "$langTheField $langParticipants",
      'many_participants' => "$langTheField $langParticipants",
      'one_participant' => "$langTheField $langParticipants",
      'many_participants' => "$langTheField $langParticipants",
      'start_session' => "$langTheField $langDate",
      'end_session' => "$langTheField $langDate"
  ));

  if($v->validate()) {
    $start_session = !empty($_POST['start_session']) ? $_POST['start_session'] : null;
    $end_session = !empty($_POST['end_session']) ? $_POST['end_session'] : null;
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
    $type_remote = $_POST['type_remote'];
    $consent = (isset($_POST['with_consent']) and $_POST['with_consent']=='on') ? 1 : 0;
    $is_user_accepted = (isset($_POST['with_consent']) and $_POST['with_consent']=='on') ? 0 : 1;

    $insert = Database::get()->query("INSERT INTO mod_session SET
                                        creator = ?d,
                                        title = ?s,
                                        comments = ?s,
                                        type = ?s,
                                        start = ?t,
                                        finish = ?t,
                                        visible = ?d,
                                        course_id = ?d,
                                        type_remote = ?d,
                                        consent  = ?d",$creator, $title, $comments, $type_session, $start_session, $end_session, $visible_session, $course_id, $type_remote, $consent);

    if(isset($_POST['session_type']) and $_POST['session_type']=='one'){
      $insert_users = Database::get()->query("INSERT INTO mod_session_users SET 
                                                session_id = ?d,
                                                participants = ?d,
                                                is_accepted = ?d", $insert->lastInsertID, $_POST['one_participant'], $is_user_accepted);
    }elseif(isset($_POST['session_type']) and $_POST['session_type']=='group'){
      foreach($_POST['many_participants'] as $m){
        $insert_users = Database::get()->query("INSERT INTO mod_session_users SET 
                                                  session_id = ?d,
                                                  participants = ?d,
                                                  is_accepted = ?d", $insert->lastInsertID, $m, $is_user_accepted);
      }
    }

    if($insert_users){

      // Send notification - email to the user - participant if this session has consent option
      if(isset($consent) && $consent){
        $course_title = course_id_to_title($course_id);
        $creatorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$creator)->givenname;
        $creatorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$creator)->surname;
        $dateFrom = format_locale_date(strtotime($start_session), 'short');
        $dateEnd = format_locale_date(strtotime($end_session), 'short');
        $is_remote_session = (isset($type_remote) && $type_remote) ? "$langRemote" : "$langNotRemote";
        $session_id = $insert->lastInsertID;
        $link_acceptance = $urlServer . "modules/session/session_acceptance.php?course=$course_code&session=$session_id";

        $emailHeader = "
        <!-- Header Section -->
                <div id='mail-header'>
                    <br>
                    <div>
                        <div id='header-title'>$langAvailableSession&nbsp;&nbsp;<span>($course_title)</span></div>
                    </div>
                </div>";

        $emailMain = "
        <!-- Body Section -->
            <div id='mail-body'>
                <br>
                <div>$langDetailsSession</div>
                <div id='mail-body-inner'>
                    <div class='mb-4'>
                      <p>$langSessionAcceptance</p>
                      <a href='$link_acceptance' target='_blank' aria-label='(opens in a new tab)'>$link_acceptance</a>
                    </div>
                    <ul id='forum-category'>
                        <li>
                          <span><b>$langTitle: </b></span> 
                          <span>$title</span>
                        </li>
                        <li>
                          <span><b>$langConsultant: </b></span> 
                          <span>$creatorName $creatorSurname</span>
                        </li>
                        <li>
                          <span><b>$langStartDate: </b></span>
                          <span>$dateFrom</span>
                        </li>
                        <li>
                          <span><b>$langEndDate: </b></span>
                          <span>$dateEnd</span>
                        </li>
                        <li>
                          <span><b>$langTypeRemote: </b></span>
                          <span>$is_remote_session</span>
                        </li>
                    </ul>
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

        $emailsubject = $siteName.':'.$langAvailableSession;

        $emailbody = $emailHeader.$emailMain;

        $emailPlainBody = html2text($emailbody);

        if(isset($_POST['session_type']) and $_POST['session_type']=='one'){
          $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$_POST['one_participant'])->email;
          send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);
        }elseif(isset($_POST['session_type']) and $_POST['session_type']=='group'){
          foreach($_POST['many_participants'] as $m){
            $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$m)->email;
            send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);
          }
        }
      }

      Session::flash('message',$langAddSessionCompleted);
      Session::flash('alert-class', 'alert-success');
      redirect_to_home_page("modules/session/index.php?course=".$course_code);

    }else{

      Session::flash('message',$langAddSessionNotCompleted);
      Session::flash('alert-class', 'alert-danger');
      redirect_to_home_page("modules/session/new.php?course=".$course_code);

    }
    
  }else{
    Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
    redirect_to_home_page("modules/session/new.php?course=".$course_code);
  }

}


if($is_coordinator){// is the tutor course
  $data['creators'] = Database::get()->queryArray("SELECT course_user.user_id,user.givenname,user.surname FROM course_user
                                                    LEFT JOIN user ON course_user.user_id=user.id
                                                    WHERE course_user.status = ?d
                                                    AND course_user.tutor = ?d
                                                    AND course_user.editor = ?d
                                                    AND course_user.course_id = ?d", USER_STUDENT, 1, 0, $course_id);
}else{// is the consultant
  $data['creators'] = Database::get()->queryArray("SELECT id,givenname,surname FROM user WHERE id = ?d",$uid);
}

$data['comments'] = rich_text_editor('comments', 5, 40, '' );
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


view('modules.session.new', $data);
