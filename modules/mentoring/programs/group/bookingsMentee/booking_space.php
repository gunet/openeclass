<?php

$require_login = TRUE;


require_once '../../../../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

if(isset($_GET['group_id']) and intval(getDirectReference($_GET['group_id'])) != 0){
    put_session_group_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['group_id']));
    $_SESSION['mentoring_group_id'] = getDirectReference($_GET['group_id']);
}

//space of group id
if(isset($_GET['group_id'])){

    if(intval(getDirectReference($_GET['group_id'])) != 0){
        $data['group_id'] = $group_id = getDirectReference($_GET['group_id']);
    }else{
        after_reconnect_go_to_mentoring_homepage();
    }

    $data['is_mentee'] = $is_mentee = check_if_uid_is_mentee_for_current_group($uid,$group_id);

    //only mentee of group can view available days of mentor for booking
    if($is_mentee){

        if(intval(getDirectReference($_GET['mentor_id'])) == 0){
            redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getInDirectReference($group_id));
        }

        $data['mentor_id_for_booking'] = getDirectReference($_GET['mentor_id']);

        $mentor_details = Database::get()->queryArray("SELECT givenname,surname FROM user WHERE id = ?d",$data['mentor_id_for_booking']);
        foreach($mentor_details as $d){
            $toolName = $langBookings.' '.$d->givenname.' '.$d->surname;
        }
        
        $data['action_bar'] = action_bar([
        [ 'title' => trans('langBackPage'),
            'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getIndirectReference($group_id),
            'icon' => 'fa-chevron-left',
            'level' => 'primary-label',
            'button-class' => 'backButtonMentoring' ]
        ], false);

        $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

        if($checkIsCommon == 1){
            $data['isCommonGroup'] = 1;
        }else{
            $data['isCommonGroup'] = 0;
        }

        view('modules.mentoring.programs.group.bookingsMentee.booking_space', $data);
        
    }else{
        redirect_to_home_page("modules/mentoring/programs/show_programs.php");
    }
    
}




