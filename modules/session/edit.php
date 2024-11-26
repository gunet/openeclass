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
 * @file edit.php
 * @brief Edit a session
 */

$require_login = true;
$require_current_course = true;
$require_consultant = true;
$require_help = TRUE;
$helpTopic = 'edit_session';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'functions.php';

check_activation_of_collaboration();
check_user_belongs_in_session($_GET['session']);

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
      'one_participant' => "$langTheField $langParticipants",
      'many_participants' => "$langTheField $langParticipants",
      'start_session' => "$langTheField $langDate",
      'end_session' => "$langTheField $langDate"
  ));

  if($v->validate()) {

    print_r('start:'.$_POST['start_session'].'end:'.$_POST['end_session']);

    if(!empty($_POST['start_session'])){
      // $start_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['start_session']);
      // $start_session = $start_at->format("Y-m-d H:i");
      // $test_start_session = $start_at->format("Y-m-d H:i:s");
      $start_session = date('Y-m-d H:i', strtotime($_POST['start_session']));
      $test_start_session = date('Y-m-d H:i:s', strtotime($_POST['start_session']));
    }else{
      $start_session = null;
    }

    if(!empty($_POST['end_session'])){
      // $end_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['end_session']);
      // $end_session = $end_at->format("Y-m-d H:i");
      // $test_end_session = $end_at->format("Y-m-d H:i:s");
      $end_session = date('Y-m-d H:i', strtotime($_POST['end_session']));
      $test_end_session = date('Y-m-d H:i:s', strtotime($_POST['end_session']));
    }else{
      $end_session = null;
    }

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


    // if the new date is different than the current session's date and exists a session by a consultant in the same time then do not continue.
    if($is_coordinator){
      $current_session_info = Database::get()->querySingle("SELECT * FROM mod_session WHERE id = ?d AND course_id = ?d", $_GET['session'], $course_id);
      if($current_session_info->creator == $creator && $current_session_info->start != $test_start_session && $current_session_info->finish != $test_end_session){
        $sessionExists = Database::get()->querySingle("SELECT * FROM mod_session 
                                                        WHERE creator = ?d
                                                        AND start = ?t
                                                        AND finish = ?t
                                                        AND course_id = ?d", $creator, $test_start_session, $test_end_session, $course_id);
        if($sessionExists){
          Session::flash('message',$langExistsTheSameSession);
          Session::flash('alert-class', 'alert-danger');
          redirect_to_home_page("modules/session/edit.php?course=".$course_code."&session=".$_GET['session']);
        }
      }
    }


    // Update dates on video teleconference
    $tc_exists = Database::get()->querySingle("SELECT id FROM tc_session WHERE course_id = ?d AND id_session = ?d",$course_id,$_GET['session']);
    if($tc_exists){
      Database::get()->querySingle("UPDATE tc_session SET 
                                    start_date = ?t,
                                    end_date = ?t
                                    WHERE course_id = ?d
                                    AND id_session = ?d", $start_session, $end_session, $course_id, $_GET['session']);
    }


    $insert = Database::get()->query("UPDATE mod_session SET
                                        creator = ?d,
                                        title = ?s,
                                        comments = ?s,
                                        type = ?s,
                                        start = ?t,
                                        finish = ?t,
                                        visible = ?d,
                                        type_remote = ?d,
                                        consent = ?d
                                        WHERE course_id = ?d
                                        AND id = ?d",$creator, $title, $comments, $type_session, $start_session, $end_session, $visible_session, $type_remote, $consent, $course_id, $_GET['session']);


    $old_users_ids = session_edit_participants_ids($_GET['session']);
    $willSendEmail = array();
    if(isset($_POST['session_type']) and $_POST['session_type']=='one'){
      if(count($old_users_ids) == 1){
        $old_user = 0;
        foreach($old_users_ids as $old){
          $old_user = $old;
        }
        if(!in_array($_POST['one_participant'],$old_users_ids)){
          if(isset($consent) && $consent){
            $willSendEmail[] = $_POST['one_participant'];
          }
          user_badge_deletion($old_user,$_GET['session']);
          $userfiles = Database::get()->queryArray("SELECT id,filename,path FROM document 
                                                    WHERE course_id = ?d 
                                                    AND subsystem_id = ?d 
                                                    AND subsystem = ?d
                                                    AND lock_user_id = ?d",$course_id,$_GET['session'],MYSESSIONS,$old_user);

          if(count($userfiles) > 0){
              foreach($userfiles as $f){
                  $user_doc = $old_user;
                  $sessionID = $_GET['session'];
                  $target_dir = "$webDir/courses/$course_code/session/session_$sessionID/$user_doc/";
                  unlink($target_dir.$f->path);
                  Database::get()->query("DELETE FROM document WHERE id = ?d",$f->id);
              }
          }
          Database::get()->query("DELETE FROM session_resources WHERE session_id = ?d AND from_user = ?d",$_GET['session'],$old_user);
          Database::get()->query("UPDATE mod_session_users SET 
                                    session_id = ?d,
                                    participants = ?d,
                                    is_accepted = ?d
                                    WHERE session_id = ?d 
                                    AND participants = ?d", $_GET['session'], $_POST['one_participant'], $is_user_accepted, $_GET['session'], $old_user);
        }
      }elseif(count($old_users_ids) > 1){
        if(isset($consent) && $consent){
          $willSendEmail[] = $_POST['one_participant'];
        }
        $deleted_users = array_diff($old_users_ids,$willSendEmail);
        if(count($deleted_users) > 0){
          foreach($deleted_users as $del_u){
            user_badge_deletion($del_u,$_GET['session']);
            $userfiles = Database::get()->queryArray("SELECT id,filename,path FROM document 
                                                      WHERE course_id = ?d 
                                                      AND subsystem_id = ?d 
                                                      AND subsystem = ?d
                                                      AND lock_user_id = ?d",$course_id,$_GET['session'],MYSESSIONS,$del_u);

            if(count($userfiles) > 0){
              foreach($userfiles as $f){
                $user_doc = $del_u;
                $sessionID = $_GET['session'];
                $target_dir = "$webDir/courses/$course_code/session/session_$sessionID/$user_doc/";
                unlink($target_dir.$f->path);
                Database::get()->query("DELETE FROM document WHERE id = ?d",$f->id);
              }
            }
            Database::get()->query("DELETE FROM session_resources WHERE session_id = ?d AND from_user = ?d",$_GET['session'],$del_u);
            Database::get()->query("DELETE FROM mod_session_users WHERE session_id = ?d AND participants = ?d",$_GET['session'],$del_u);
          }
        }
        $new_users_ids = session_edit_participants_ids($_GET['session']);
        if(!in_array($_POST['one_participant'],$new_users_ids)){
          Database::get()->query("INSERT INTO mod_session_users SET
                                  session_id = ?d,
                                  participants = ?d,
                                  is_accepted = ?d", $_GET['session'], $_POST['one_participant'], $is_user_accepted);
        }
      }
    }elseif(isset($_POST['session_type']) and $_POST['session_type']=='group'){
      $deleted_users = array_diff($old_users_ids,$_POST['many_participants']);
      if(count($deleted_users) > 0){
        foreach($deleted_users as $del_u){
          user_badge_deletion($del_u,$_GET['session']);
          $userfiles = Database::get()->queryArray("SELECT id,filename,path FROM document 
                                                      WHERE course_id = ?d 
                                                      AND subsystem_id = ?d 
                                                      AND subsystem = ?d
                                                      AND lock_user_id = ?d",$course_id,$_GET['session'],MYSESSIONS,$del_u);

          if(count($userfiles) > 0){
            foreach($userfiles as $f){
              $user_doc = $del_u;
              $sessionID = $_GET['session'];
              $target_dir = "$webDir/courses/$course_code/session/session_$sessionID/$user_doc/";
              unlink($target_dir.$f->path);
              Database::get()->query("DELETE FROM document WHERE id = ?d",$f->id);
            }
          }
          Database::get()->query("DELETE FROM session_resources WHERE session_id = ?d AND from_user = ?d",$_GET['session'],$del_u);
          Database::get()->query("DELETE FROM mod_session_users WHERE session_id = ?d AND participants = ?d",$_GET['session'],$del_u);
        }
      }
      $new_users_ids = session_edit_participants_ids($_GET['session']);
      foreach($_POST['many_participants'] as $m){
        if(!in_array($m,$new_users_ids)){
          if(isset($consent) && $consent){
            $willSendEmail[] = $m;
          }
          Database::get()->query("INSERT INTO mod_session_users SET
                                  session_id = ?d,
                                  participants = ?d,
                                  is_accepted = ?d", $_GET['session'], $m, $is_user_accepted);
        }
      }
    }

    // Send notification - email to the user - participant
    if(isset($consent) && $consent){
      $course_title = course_id_to_title($course_id);
      $creatorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$creator)->givenname;
      $creatorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$creator)->surname;
      $dateFrom = format_locale_date(strtotime($start_session), 'short');
      $dateEnd = format_locale_date(strtotime($end_session), 'short');
      $is_remote_session = (isset($type_remote) && $type_remote) ? "$langRemote" : "$langNotRemote";
      $sid = $_GET['session'];
      $link_acceptance = $urlServer . "modules/session/session_acceptance.php?course=$course_code&session=$sid";

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
                    <a href='$link_acceptance' target='_blank' aria-label='$langOpenNewTab'>$link_acceptance</a>
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
        if(count($willSendEmail) > 0){
          foreach($willSendEmail as $m){
            if(get_user_email_notification($m)){
              $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$m)->email;
              send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);
            }
          }
        }
      }elseif(isset($_POST['session_type']) and $_POST['session_type']=='group'){
        if(count($willSendEmail) > 0){
          foreach($willSendEmail as $m){
            if(get_user_email_notification($m)){
              $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$m)->email;
              send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);
            }
          }
        }
      }
    }

    Session::flash('message',$langAddSessionCompleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/index.php?course=".$course_code);

  }else{
    Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
    redirect_to_home_page("modules/session/edit.php?course=".$course_code."&session=".$_GET['session']);
  }

}

$data['sessionID'] = $_GET['session'];
$data['session_info'] = $session_info = Database::get()->querySingle("SELECT * FROM mod_session WHERE id = ?d",$_GET['session']);
$data['title'] = $session_info->title;
$data['creator'] = $session_info->creator;
$data['comments'] = rich_text_editor('comments', 5, 40, $session_info->comments);
$data['session_type'] = $session_info->type;
$startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $session_info->start);
$data['start'] = q($startDate_obj->format('d-m-Y H:i'));
$endDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $session_info->finish);
$data['finish'] = q($endDate_obj->format('d-m-Y H:i'));
$data['finish_text'] = q($endDate_obj->format('H:i'));
$data['visible'] = $session_info->visible;
$data['type_remote'] = $session_info->type_remote;
$data['withConsent'] = $session_info->consent;
$users_participants = Database::get()->queryArray("SELECT participants FROM mod_session_users
                                                            WHERE session_id = ?d",$_GET['session']);
$participants_arr = [];
if(count($users_participants) > 0){
    foreach($users_participants as $u){
        $participants_arr[] = $u->participants;
    }
}
$data['participants_arr'] = $participants_arr;

// If exists tc link , disable the tc choice.
$data['tc_disabled'] = '';
$exists_tc = Database::get()->querySingle("SELECT id FROM tc_session WHERE course_id = ?d AND id_session = ?d",$course_id,$_GET['session']);
if($exists_tc){
  $data['tc_disabled'] = 'disabled';
}
$data['meeting_disabled'] = '';
$exists_meeting = Database::get()->querySingle("SELECT id FROM badge_criterion 
                                                WHERE badge IN (SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d)
                                                AND activity_type = ?s",$course_id,$_GET['session'],'meeting-completed');
if($exists_meeting){
  $data['meeting_disabled'] = 'disabled';
}

$data['tmp_coordinator'] = 0;
if($is_coordinator){// is the tutor course
  $data['tmp_coordinator'] = 1;
  $data['creators'] = Database::get()->queryArray("SELECT course_user.user_id,user.givenname,user.surname FROM course_user
                                                    LEFT JOIN user ON course_user.user_id=user.id
                                                    WHERE course_user.editor = ?d
                                                    AND course_user.status = ?d
                                                    AND course_user.course_id = ?d
                                                    AND course_user.tutor = ?d", 0, USER_STUDENT, $course_id, 1);

  $data['view_sessions'] = Database::get()->queryArray("SELECT mod_session.title,mod_session.creator,mod_session.start,mod_session.finish,user.givenname,user.surname FROM mod_session
                                                        LEFT JOIN user ON mod_session.creator = user.id
                                                        WHERE course_id = ?d",$course_id);

}else{// is the consultant
  $data['creators'] = Database::get()->queryArray("SELECT id,givenname,surname FROM user WHERE id = ?d",$uid);
}

$sql = "";
if($is_consultant && !$is_coordinator){
  $data['tmp_coordinator'] = 0;
  $consultant_as_tutor_group = Database::get()->queryArray("SELECT * FROM group_members 
                                                            WHERE group_id IN (SELECT id FROM `group` WHERE course_id = $course_id)
                                                            AND user_id = ?d 
                                                            AND is_tutor = ?d", $uid, 1);
  if(count($consultant_as_tutor_group) > 0){
    $arr_g = [];
    foreach ($consultant_as_tutor_group as $g) {
      $arr_g[] = $g->group_id;
    }
    $arr_as_str = implode(',',$arr_g);
    $sql = "AND course_user.user_id IN (SELECT user_id FROM group_members
                                        WHERE group_id IN ($arr_as_str))";
  }
}
$data['simple_users'] = Database::get()->queryArray("SELECT course_user.user_id,user.givenname,user.surname FROM course_user
                                                      LEFT JOIN user ON course_user.user_id=user.id
                                                      WHERE course_user.status = ?d
                                                      AND course_user.tutor = ?d
                                                      AND course_user.editor = ?d
                                                      AND course_reviewer = ?d
                                                      AND course_user.reviewer = ?d
                                                      AND course_user.course_id = ?d
                                                      $sql", USER_STUDENT, 0, 0, 0, 0, $course_id);

$data['action_bar'] = action_bar([
    [ 'title' => $langBack,
      'url' => 'index.php?course=' . $course_code,
      'icon' => 'fa-reply',
      'button-class' => 'btn-success',
      'level' => 'primary-label' ]
], false);


view('modules.session.edit', $data);
