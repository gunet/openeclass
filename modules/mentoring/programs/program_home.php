<?php

//$require_login = TRUE;


require_once '../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

after_reconnect_go_to_mentoring_homepage();
if(!isset($mentoring_program_id) or !$mentoring_program_id){
  redirect_to_home_page("modules/mentoring/mentoring_platform_home.php");
}

put_session_values_in_db_and_get_this_after_logout($uid,$mentoring_program_code);

$toolName = show_mentoring_program_title($mentoring_program_code);

$data['is_editor_mentoring'] = is_editor_mentoring($uid);

$data['mentoring_program_details'] = show_mentoring_program_details($mentoring_program_code, $mentoring_program_id);

if(isset($_GET['reg']) and $_GET['reg'] == 1){
  $reg = register_mentoring_program($mentoring_program_id, $uid);
  if($reg){
    Session::flash('message',$langRegisterDoneMentoringProgram);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('mentoring_programs/'.$mentoring_program_code.'/index.php');
  }else{

  }
}

//delete guided_user from program
if(isset($_POST['action_del_guided']) and $_POST['action_del_guided'] == 'delete'){
  $user_details = Database::get()->queryArray("SELECT username,givenname,surname FROM user WHERE id = ?d",$_POST['guided_id']);
  foreach($user_details as $user){
     $username = $user->username;
     $name = $user->givenname.' '.$user->surname;
  }
  $del = delete_guides_from_mentoring_program($mentoring_program_id,$_POST['guided_id']);
  if($del){
    Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_REQUESTS, MENTORING_LOG_DELETE, array('name' => $name, 'type_request' => -3));
    Session::flash('message',$langDeleteGuidedSuccess);
    Session::flash('alert-class', 'alert-success');
  }else{
    Session::flash('message',$langNoDeleteGuidedSuccess);
    Session::flash('alert-class', 'alert-danger');
  }
  redirect_to_home_page('mentoring_programs/'.$mentoring_program_code.'/index.php');
}

$data['mentoring_program_requests'] = show_mentoring_program_requests($mentoring_program_id);

//get mentor id for show calendar
if(isset($_GET['showcal'])){
  $group_id = getDirectReference($_GET['group_id']);
  $mentor_id = getDirectReference($_GET['showcal']);

  $group_id = getIndirectReference($group_id);
  $mentor_id = getIndirectReference($mentor_id);

  redirect_to_home_page('modules/mentoring/programs/mentor_rentezvous.php?showcalMentor='.$mentor_id.'&showGroup='.$group_id);
}

//get accepted requests
$data['accepted_requests'] = get_accepted_requests($mentoring_program_id);

//get denied requests
$data['denied_requests'] = get_denied_requests($mentoring_program_id);


view('modules.mentoring.programs.program_home', $data);


