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
if(isset($_GET['addSessions'])){
  $require_consultant = true;
}

require_once '../../include/baseTheme.php';
require_once 'functions.php';

check_activation_of_collaboration();

$pageName = $is_consultant ? $langPercentageCompletedConsultingByUser : $langPercentageCompletedConsulting;
if(isset($_GET['addSessions'])){
  $pageName = $langCompletedConsulting;
}
$data['is_tutor_course'] = $is_tutor_course = is_tutor_course($course_id,$uid);
$data['is_consultant'] = $is_consultant = is_consultant($course_id,$uid);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
student_view_is_active();
if(!isset($_GET['showCompletedConsulting'])){
  is_admin_of_session();
}

if(isset($_POST['submit'])){
  if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

  if(isset($_POST['sessions_completed'])){
    Database::get()->query("DELETE FROM mod_session_completion WHERE course_id = ?d",$course_id);
    foreach($_POST['sessions_completed'] as $s){
      Database::get()->query("INSERT INTO mod_session_completion SET course_id = ?d,session_id = ?d",$course_id,$s);
    }
  }else{
    Database::get()->query("DELETE FROM mod_session_completion WHERE course_id = ?d",$course_id);
  }

  Session::flash('message',$langAddSessionConsultingCompleted);
  Session::flash('alert-class', 'alert-success');
  redirect_to_home_page("modules/session/completion.php?course=".$course_code."&addSessions=true");
}


$data['action_bar'] = action_bar([
    [ 'title' => $langBack,
      'url' => 'index.php?course=' . $course_code,
      'icon' => 'fa-reply',
      'button-class' => 'btn-success',
      'level' => 'primary-label' ]
  ], false);

if(isset($_GET['addSessions'])){
  if($is_tutor_course){
      $data['all_sessions'] = $all_sessions = Database::get()->queryArray("SELECT * FROM mod_session
                                                                      WHERE id IN (SELECT session_id FROM badge WHERE course_id = ?d)
                                                                      ORDER BY start ASC",$course_id);
  }elseif($is_consultant){
    $data['all_sessions'] = $all_sessions = Database::get()->queryArray("SELECT * FROM mod_session
                                                                      WHERE id IN (SELECT session_id FROM badge WHERE course_id = ?d)
                                                                      AND creator = ?d
                                                                      ORDER BY start ASC",$course_id,$uid);
  }


  $data['defined_sessions'] = $defined_sessions = Database::get()->queryArray("SELECT * FROM mod_session_completion 
                                                                    WHERE course_id = ?d",$course_id);

  $session_ids = array();
  if(count($defined_sessions)>0){
    foreach($defined_sessions as $s){
      $session_ids[] = $s->session_id;
    }
  }
  $data['session_ids'] = $session_ids;
  view('modules.session.completion', $data);
}else{
  $res = Database::get()->queryArray("SELECT session_id FROM mod_session_completion WHERE course_id = ?d",$course_id);
  $completedSessionByUsers = array();
  $visible_sessions_id = array();
  if(isset($_GET['showCompletedConsulting']) && count($res) > 0){// for user-consultant or tutor
    if(count($res) > 0){
      if(!$is_tutor_course && !$is_consultant){
        $all_user_sessions = Database::get()->queryArray("SELECT * FROM mod_session
                                                          WHERE visible = ?d
                                                          AND course_id = ?d
                                                          AND id IN (SELECT session_id FROM mod_session_users
                                                                      WHERE participants = ?d)
                                                          ORDER BY start ASC",1,$course_id,$uid); 

        $visible_user_sessions = findUserVisibleSessions($uid, $all_user_sessions);
        foreach ($visible_user_sessions as $d) {
            $visible_sessions_id[] = $d->id;
        }
      }
      foreach($res as $r){
        $hadIncompletedPrereq = 0;
        $badge = Database::get()->querySingle("SELECT id FROM badge WHERE session_id = ?d",$r->session_id);
        if($is_tutor_course or $is_consultant){
          if($is_consultant && !$is_tutor_course){
            // check if consultant is participated in current session as creator
            if(!is_session_consultant($r->session_id,$course_id)){
              continue;
            }
          }
          $userParticipant = array();
          $participants_ids = array();
          $participants_ids = session_participants_ids($r->session_id);
          if(count($participants_ids)>0){
            foreach($participants_ids as $p){
              if($badge){
                $badge_id = $badge->id;
                $per = get_cert_percentage_completion_by_user('badge',$badge_id,$p);
                $icon = "
                  <div class='progress' style='width:150px;'>
                    <div class='progress-bar' role='progressbar' style='width: $per%;' aria-valuenow='$per' aria-valuemin='0' aria-valuemax='100'>$per%</div>
                  </div>
                ";
                
                $userParticipant[$p] = [
                  'user' => participant_name($p),
                  'session_id' => $r->session_id,
                  'icon' => $icon,
                  'info' => ($per == 100) ? $langCompletedSessions : $langNotCompletedSession
                ];
              }
            }
            if($badge){
              $completedSessionByUsers[$r->session_id] = $userParticipant;
            }
          }
        }else{
          if(participation_in_session($r->session_id) && $badge){
            if(!is_session_visible($course_id,$r->session_id)){
              continue;
            }
            if(!in_array($r->session_id, $visible_sessions_id)){
              $hadIncompletedPrereq = 1;
            }
            $badge_id = $badge->id;
            $per = get_cert_percentage_completion_by_user('badge',$badge_id,$uid);
            $icon = "
                      <div class='progress' style='width:150px;'>
                        <div class='progress-bar' role='progressbar' style='width: $per%;' aria-valuenow='$per' aria-valuemin='0' aria-valuemax='100'>$per%</div>
                      </div>
                    ";

            $userParticipant[$uid] = [
              'user' => participant_name($uid),
              'session_id' => $r->session_id,
              'icon' => $icon,
              'info' => ($per == 100) ? $langCompletedSessions : $langNotCompletedSession,
              'hasIncompletePrereq' => $hadIncompletedPrereq
            ];
            $completedSessionByUsers[$r->session_id] = $userParticipant;
          }
        }
      }
    }
  }
  $data['completedSessionByUsers'] = $completedSessionByUsers;
  view('modules.session.show_results', $data);
}
