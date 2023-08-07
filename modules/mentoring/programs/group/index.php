<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

$toolName = $langGroupMentorsMentees;

//for uid where is tutor of mentoring_program
$data['is_tutor_of_mentoring_program'] = show_mentoring_program_editor($mentoring_program_id,$uid);

//for uid where is tutor or tutor_mentor for current mentoring program then can create group
$check_editors = check_if_uid_is_mentor_or_tutor_or_guided_of_mentoring_program($mentoring_program_id,$uid);
$is_editor_group = false;
if($check_editors[0]['tutor_or_mentor'] == 0 or $check_editors[0]['tutor_or_mentor'] == 1 or $check_editors[0]['tutor_or_mentor'] == 4 or $is_admin){
    $is_editor_group = true;
}
$_SESSION['is_editor_group'] = $data['is_editor_group'] = $is_editor_group;

// delete all groups by tutor or program
if(isset($_POST['deleteAllGroups']) and $_POST['deleteAllGroups'] == 'deleteGroups'){

    // DONT DELETE COMMON GROUP
    
    $del = Database::get()->query("DELETE FROM mentoring_forum WHERE mentoring_program_id = ?d
                                    AND id NOT IN (SELECT forum_id FROM mentoring_group
                                                    WHERE mentoring_program_id = ?d AND common = ?d)",$mentoring_program_id,$mentoring_program_id,1);


    //$del = Database::get()->query("DELETE FROM mentoring_forum_category WHERE mentoring_program_id = ?d",$mentoring_program_id);

    //$del = Database::get()->query("DELETE FROM mentoring_forum_notify WHERE mentoring_program_id = ?d",$mentoring_program_id);
    
    $del = Database::get()->query("DELETE FROM mentoring_forum_user_stats WHERE mentoring_program_id = ?d",$mentoring_program_id);

    $del = Database::get()->query("DELETE FROM mentoring_group_category WHERE mentoring_program_id = ?d",$mentoring_program_id);

    $del = Database::get()->query("DELETE FROM mentoring_group_members
                                     WHERE group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d)",$mentoring_program_id,0);

    $del = Database::get()->query("DELETE FROM mentoring_group_properties 
                                    WHERE mentoring_program_id = ?d
                                    AND group_id NOT IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d)",$mentoring_program_id,$mentoring_program_id,1);

    $del = Database::get()->query("DELETE FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d",$mentoring_program_id,0);

    if($del){
        Session::flash('message',$langDeleteAllMentoringGroupsSuccess);
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langDeleteAllMentoringGroupsNoSuccess);
        Session::flash('alert-class', 'alert-danger');
    }
}

//get groups details from current mentoring_program
$data['isCommonGroup'] = 0;
if(isset($_GET['commonGroupView'])){

    $data['groups'] = $groups = get_common_group_details_for_mentoring_program($mentoring_program_id);
    $data['isCommonGroup'] = 1;
    $toolName = $langCommonGroup;

    $data['action_bar'] = action_bar([
        [ 'title' => trans('langBackPage'),
            'url' => $urlServer.'/modules/mentoring/programs/group/select_group.php',
            'icon' => 'fa-chevron-left',
            'level' => 'primary-label',
            'button-class' => 'backButtonMentoring' ]
        ], false);

}else{
    $data['groups'] = $groups = get_groups_details_for_mentoring_program($mentoring_program_id);

    $commonGroupId = Database::get()->querySingle("SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d",$mentoring_program_id,1)->id;
    $data['action_bar'] = action_bar([
        [ 'title' => trans('langBackPage'),
            'url' => $urlServer.'/modules/mentoring/programs/group/select_group.php',
            'icon' => 'fa-chevron-left',
            'level' => 'primary-label',
            'button-class' => 'backButtonMentoring' ]
        ], false);
}


//get settings for registration of mentees to a group (registration to one or many groups)
$data['setting_reg'] = $setting_reg = get_settings_registration_of_mentees_for_mentoring_program($mentoring_program_id,$mentoring_program_code);


view('modules.mentoring.programs.group.index', $data);


