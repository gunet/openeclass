<?php

$require_login = TRUE;


require_once '../../../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

if(isset($_GET['group_id']) and intval(getDirectReference($_GET['group_id'])) != 0){
    put_session_group_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['group_id']));
    $_SESSION['mentoring_group_id'] = getDirectReference($_GET['group_id']);
}

//get mentor id for show calendar
if(isset($_GET['showcal'])){

    if(intval(getDirectReference($_GET['group_id'])) != 0){
        $data['group_id'] = $group_id = getDirectReference($_GET['group_id']);
    }else{
        after_reconnect_go_to_mentoring_homepage();
    }

    if(intval(getDirectReference($_GET['showcal'])) == 0){
        redirect_to_home_page("modules/mentoring/programs/group/select_group.php");//go to common group
    }

    $data['mentor_uid'] = $mentor_uid = getDirectReference($_GET['showcal']);

    $MentorGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$mentor_uid)->givenname;
    $MentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$mentor_uid)->surname;

    $toolName = $langMyAvailableDates.' '.$MentorGivenname.' '.$MentorSurname;

    $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }

    $data['action_bar'] = action_bar([
        [ 'title' => trans('langBackPage'),
            'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getInDirectReference($group_id),
            'icon' => 'fa-chevron-left',
            'level' => 'primary-label',
            'button-class' => 'backButtonMentoring' ]
        ], false);

    view('modules.mentoring.programs.group.datesMentor.add_date_by_mentor', $data);
}







