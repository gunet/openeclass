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
$require_help = TRUE;
$helpTopic = 'course_sessions';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

check_activation_of_collaboration();

$pageName = $langCompletedConsulting;
$data['is_tutor_course'] = $is_tutor_course = is_tutor_course($course_id,$uid);
$data['is_consultant'] = $is_consultant = is_consultant($course_id,$uid);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

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
  $pageName = $langTableCompletedConsulting;
  $res = Database::get()->queryArray("SELECT session_id FROM mod_session_completion WHERE course_id = ?d",$course_id);
  $completedSessionByUsers = array();
  if(isset($_GET['showCompletedConsulting']) && count($res) > 0){// for user-consultant or tutor
    if(count($res) > 0){
      foreach($res as $r){
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
                if ($per == 100) {
                    $icon = icon('fa-check-circle fa-lg Success-200-cl', $langInstallEnd);
                } else {
                    $icon = icon('fa-hourglass-2 fa-lg Primary-600-cl', $per . "%");
                }
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
            $badge_id = $badge->id;
            $per = get_cert_percentage_completion_by_user('badge',$badge_id,$uid);
            if ($per == 100) {
                $icon = icon('fa-check-circle fa-lg Success-200-cl', $langInstallEnd);
            } else {
                $icon = icon('fa-hourglass-2 fa-lg Primary-600-cl', $per . "%");
            }
            $userParticipant[$uid] = [
              'user' => participant_name($uid),
              'session_id' => $r->session_id,
              'icon' => $icon,
              'info' => ($per == 100) ? $langCompletedSessions : $langNotCompletedSession
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
