<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

put_session_values_in_db_and_get_this_after_logout($uid,$mentoring_program_code);

$toolName = $langProgramUsers;

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
  redirect_to_home_page('modules/mentoring/programs/users/index.php');
}

$data['users_program'] = get_accepted_requests($mentoring_program_id);

$data['action_bar'] = action_bar([
    [ 'title' => trans('langBackPage'),
      'url' => $urlAppend.'mentoring_programs/'.$mentoring_program_code.'/index.php',
      'icon' => 'fa-chevron-left',
      'level' => 'primary-label',
      'button-class' => 'backButtonMentoring' ]
  ], false);

view('modules.mentoring.programs.users.index', $data);


