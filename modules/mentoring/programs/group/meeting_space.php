<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
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
    $data['is_editor_current_group'] = $is_editor_current_group = get_editor_for_current_group($uid,$group_id);
    $data['is_tutor_of_mentoring_program'] = $is_tutor_of_mentoring_program = show_mentoring_program_editor($mentoring_program_id,$uid);

    //only mentee,tutor,editor of group can view group
    if($is_mentee or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin){

        if($is_tutor_of_mentoring_program or $is_admin){
            $toolName = 'Meetings'.' ('.get_name_for_current_group($group_id).')';
        }else{
            $toolName = $langMyMeetings;
        }
        

        $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

        if($checkIsCommon == 1){
            $data['isCommonGroup'] = 1;
        }else{
            $data['isCommonGroup'] = 0;
        }

        if(isset($_POST['withDrawAlMentee'])){
            Database::get()->query("DELETE FROM mentoring_rentezvous_user
                                    WHERE mentoring_rentezvous_id = ?d 
                                    AND mentee_id = ?d",$_POST['rentezvous_id'],$_POST['mentee_id']);
            Session::flash('message',$withDrawAlMenteeSuccessMsg);
            Session::flash('alert-class', 'alert-success');
        }

        if(isset($_GET['del_meeting_id'])){
            $meeting_id = getDirectReference($_GET['del_meeting_id']);
            Database::get()->query("DELETE FROM mentoring_rentezvous WHERE id = ?d",$meeting_id);
            Session::flash('message',$langDeleteRentezvousSuccess);
            Session::flash('alert-class', 'alert-success');
        }

        if(isset($_GET['show_history'])){

            $toolName = $langHistoryRentezvous;

            $data['action_bar'] = action_bar([
                [ 'title' => trans('langBackPage'),
                    'url' => $urlServer.'modules/mentoring/programs/group/meeting_space.php?group_id='.getIndirectReference($group_id),
                    'icon' => 'fa-chevron-left',
                    'level' => 'primary-label',
                    'button-class' => 'backButtonMentoring' ]
                ], false);

            if($is_mentee){
                $data['history_rentezvous'] = Database::get()->queryArray("SELECT *FROM mentoring_rentezvous
                                                                        WHERE id IN (SELECT mentoring_rentezvous_id FROM mentoring_rentezvous_user
                                                                                        WHERE mentee_id = ?d)
                                                                        AND group_id = ?d
                                                                        AND mentoring_program_id = ?d",$uid,$group_id,$mentoring_program_id);
            }else{
                if($is_admin or $is_tutor_of_mentoring_program){
                    $data['history_rentezvous'] = Database::get()->queryArray("SELECT *FROM mentoring_rentezvous
                                                    WHERE group_id = ?d
                                                    AND mentoring_program_id = ?d",$group_id,$mentoring_program_id);
                }else{
                    $data['history_rentezvous'] = Database::get()->queryArray("SELECT *FROM mentoring_rentezvous
                                                                        WHERE mentor_id = ?d 
                                                                        AND group_id = ?d
                                                                        AND mentoring_program_id = ?d",$uid,$group_id,$mentoring_program_id);
                }
            }
                

            view('modules.mentoring.programs.group.meeting_history', $data);
        }else{
            $data['action_bar'] = action_bar([
            [ 'title' => trans('langBackPage'),
                'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getIndirectReference($group_id),
                'icon' => 'fa-chevron-left',
                'level' => 'primary-label',
                'button-class' => 'backButtonMentoring' ]
            ], false);

            view('modules.mentoring.programs.group.meeting_space', $data);
        }
    }else{
        redirect_to_home_page("modules/mentoring/programs/show_programs.php");
    }
    
}




