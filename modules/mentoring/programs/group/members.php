<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

if(isset($_GET['group_id']) and intval(getDirectReference($_GET['group_id'])) != 0){
    put_session_group_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['group_id']));
    $_SESSION['mentoring_group_id'] = getDirectReference($_GET['group_id']);
}

//delete mentee from current group
if(isset($_POST['delete_mentee_from_group'])){

    //delete mentee-posts and bookings
    $group_id = getDirectReference($_GET['group_id']);

    $del_booking_user = Database::get()->query("DELETE FROM mentoring_booking WHERE mentoring_program_id = ?d AND group_id = ?d AND
                                                id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id = ?d)",$mentoring_program_id,$group_id,$_POST['del_user_id']);

    $forumid = Database::get()->querySingle("SELECT forum_id FROM mentoring_group WHERE id = ?d",$group_id)->forum_id;
    $topicIds = Database::get()->queryArray("SELECT id FROM mentoring_forum_topic WHERE forum_id = ?d",$forumid);

    $topicIdsCache = Database::get()->queryArray("SELECT id FROM mentoring_forum_topic WHERE poster_id = ?d
                                                  AND forum_id = ?d",$_POST['del_user_id'],$forumid);

    foreach($topicIds as $topic_id){
        $file_path = Database::get()->querySingle("SELECT topic_filepath FROM mentoring_forum_post 
                     WHERE topic_id = ?d AND poster_id = ?d",$topic_id->id,$_POST['del_user_id'])->topic_filepath;
        if($file_path){
            $target_dir = "$webDir/mentoring_programs/$mentoring_program_code/forum/";
            unlink($target_dir.$file_path);
        }
        Database::get()->query("DELETE FROM mentoring_forum_post WHERE
                                topic_id IN (?d) AND poster_id = ?d",$topic_id->id,$_POST['del_user_id']);
        Database::get()->query("DELETE FROM mentoring_forum_topic WHERE
                                id = ?d AND poster_id = ?d",$topic_id->id,$_POST['del_user_id']); 
    }
    foreach($topicIdsCache as $c){
        Database::get()->query("DELETE FROM mentoring_forum_post WHERE
                                topic_id = ?d",$c->id);
    }

    $del = Database::get()->query("DELETE FROM mentoring_group_members 
                                WHERE group_id = ?d AND user_id = ?d
                                AND is_tutor = ?d AND status_request = ?d",$group_id,$_POST['del_user_id'],0,1);
    
    if($del){
        $group_title = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE mentoring_program_id = ?d AND id = ?d",$mentoring_program_id,$group_id)->name;
        Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_DELETE, array('uid' => display_user($_POST['del_user_id'], false, false),'group_title' => $group_title, 'type' => 'delete_mentee_from_group'));
        Session::flash('message',$langDeleteMenteeFromGroupSuccess); 
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langDeleteMenteeFromGroupNoSuccess); 
        Session::flash('alert-class', 'alert-danger');
    }

    redirect_to_home_page("modules/mentoring/programs/group/members.php?group_id=".getInDirectReference($group_id));
}

//space of group id
if(isset($_GET['group_id'])){

    if(intval(getDirectReference($_GET['group_id'])) != 0){
        $data['group_id'] = $group_id = getDirectReference($_GET['group_id']);

        $check_group = Database::get()->queryArray("SELECT *FROM mentoring_group WHERE id = ?d",$group_id);
        if(count($check_group) == 0){
            redirect_to_home_page("modules/mentoring/programs/group/select_group.php");
        }
    }else{
        after_reconnect_go_to_mentoring_homepage();
    }

    $toolName = $langMembers.' ('.get_name_for_current_group($group_id).')';

    $data['is_mentee'] = $is_mentee = check_if_uid_is_mentee_for_current_group($uid,$group_id);
    $data['is_editor_current_group'] = $is_editor_current_group = get_editor_for_current_group($uid,$group_id);
    $data['is_tutor_of_mentoring_program'] = $is_tutor_of_mentoring_program = show_mentoring_program_editor($mentoring_program_id,$uid);

    $data['mentees_for_current_group'] = get_all_mentees_of_current_group($data['group_id']);

    $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                        WHERE id = ?d 
                                                        AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }

    //only mentee,tutor,editor of group can view group
    if($is_mentee or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin){

        $data['action_bar'] = action_bar([
        [ 'title' => trans('langBackPage'),
            'url' => $urlAppend.'modules/mentoring/programs/group/group_space.php?space_group_id='.getInDirectReference($group_id),
            'icon' => 'fa-chevron-left',
            'level' => 'primary-label',
            'button-class' => 'backButtonMentoring' ]
        ], false);

        view('modules.mentoring.programs.group.members', $data);
    }else{
        redirect_to_home_page("modules/mentoring/programs/show_programs.php");
    }
}

