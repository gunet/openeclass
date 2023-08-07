<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/mentoring/mentoring_log.class.php';
if(!isset($_GET['fromRegModals'])){
    require_once 'modules/mentoring/programs/wall/wall_wrapper.php';
}
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

load_js('bootstrap-datetimepicker');

if(isset($_GET['space_group_id']) and intval(getDirectReference($_GET['space_group_id'])) != 0){
    put_session_group_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['space_group_id']));
    $_SESSION['mentoring_group_id'] = getDirectReference($_GET['space_group_id']);
}

//delete current group
if(isset($_POST['delete_group'])){
    $group_id = getDirectReference($_GET['space_group_id']);
    $forum = Database::get()->querySingle("SELECT forum_id FROM mentoring_group
             WHERE mentoring_program_id = ?d AND id = ?d",$mentoring_program_id,$group_id);
    $forum_id = $forum->forum_id;

    $topic = Database::get()->queryArray("SELECT id FROM mentoring_forum_topic
                                            WHERE forum_id = ?d",$forum_id);
    $topic_ids = array();
    $old_m = array();
    foreach($topic as $t){
        $topic_ids[] = $t->id;
    }
    $values = implode(',', $topic_ids);

    Database::get()->query("DELETE FROM mentoring_forum_post WHERE topic_id IN ($values)");
    Database::get()->query("DELETE FROM mentoring_forum_topic WHERE forum_id = ?d",$forum_id);
    Database::get()->query("DELETE FROM mentoring_forum WHERE id = ?d AND mentoring_program_id = ?d",$forum_id,$mentoring_program_id);
    Database::get()->query("DELETE FROM mentoring_group_properties WHERE mentoring_program_id = ?d AND group_id = ?d",$mentoring_program_id,$group_id);
    Database::get()->query("DELETE FROM mentoring_group_members WHERE group_id = ?d",$group_id);
    Database::get()->query("DELETE FROM mentoring_booking WHERE group_id IN (?d)",$group_id);
    Database::get()->query("DELETE FROM mentoring_rentezvous WHERE group_id IN (?d)",$group_id);

    $group_title = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE mentoring_program_id = ?d AND id = ?d",$mentoring_program_id,$group_id)->name;
    $del = Database::get()->query("DELETE FROM mentoring_group WHERE mentoring_program_id = ?d AND id = ?d",$mentoring_program_id,$group_id);

    if($del){
        Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_GROUP, MENTORING_LOG_DELETE, array('uid' => '','group_title' => $group_title, 'type' => 'delete_group'));
        Session::flash('message',$langDeleteMentoringGroupSuccess); 
        Session::flash('alert-class', 'alert-success');
        isMemberOfCommonGroup($uid,$mentoring_program_id,$group_id);
    }else{
        Session::flash('message',$langDeleteMentoringGroupNoSuccess); 
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getInDirectReference($group_id));
    }
}

// register - unregister from group
if(isset($_GET['selfReg']) and $_GET['selfReg'] == 1){
    
    $group_id = getDirectReference($_GET['group_id']);
    $user_id = getDirectReference($_GET['uid']);

    //otan h rithmisi afora eggrafh se mia omada mono tote o mentee mporei na eggrafei se deuteri omada mono an ayth h omada einai h common group
    $get_common_groupID = Database::get()->querySingle("SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d",$mentoring_program_id,1)->id;
    
    $uid_in_common_GROUP = Database::get()->querySingle("SELECT COUNT(group_id) as gri FROM mentoring_group_members WHERE 
                                                         group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",$get_common_groupID,$uid,0,1)->gri;

    $uid_not_in_common_group = Database::get()->querySingle("SELECT COUNT(group_id) as gri FROM mentoring_group_members WHERE group_id NOT IN (?d)
                                                                                                        AND user_id = ?d AND is_tutor = ?d AND status_request = ?d
                                                                                                        AND group_id IN (SELECT id FROM mentoring_group WHERE
                                                                                                        mentoring_program_id = ?d)",$get_common_groupID,$uid,0,1,$mentoring_program_id)->gri;

    //check if group-registration is for many or one mentee
    $registration_check = Database::get()->querySingle("SELECT other_groups_reg FROM mentoring_programs WHERE id = ?d",$mentoring_program_id)->other_groups_reg;
    
    //because groups-registration is for one mentee group by group then check if mentee is member in other group before continue.
    $registration_mentee_checking = Database::get()->queryArray("SELECT *FROM mentoring_group_members 
                                                                WHERE group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d)
                                                                AND user_id = ?d
                                                                AND is_tutor = ?d
                                                                AND status_request = ?d",$mentoring_program_id,$user_id,0,1);
    

    $max_members_of_group = Database::get()->querySingle("SELECT max_members FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->max_members;
    $all_members_in_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                            WHERE group_id = ?d
                                                            AND is_tutor = ?d AND status_request = ?d",$group_id,0,1)->ui;
    if($max_members_of_group == 0 or ($max_members_of_group > 0 and $all_members_in_group < $max_members_of_group)){

        if(($registration_check == 0) or ($registration_check == 1 and count($registration_mentee_checking) == 0) or ($uid_in_common_GROUP == 1 and $uid_not_in_common_group == 0) or ($group_id == $get_common_groupID)){
            $register_to_group = Database::get()->query("INSERT INTO mentoring_group_members SET
                                                        group_id = ?d,
                                                        user_id = ?d,
                                                        is_tutor = ?d,
                                                        status_request = ?d",$group_id, $user_id, 0, 1);

            Session::flash('message',$langRegisterGroupMentoringSuccess); 
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langRegisterGroupMentoringNoSuccess); 
            Session::flash('alert-class', 'alert-danger');
        }
    }else{
        Session::flash('message',$langLimitGroupNotAllowed); 
        Session::flash('alert-class', 'alert-warning');
    }

    isMemberOfCommonGroup($uid,$mentoring_program_id,$group_id);
}
if(isset($_GET['selfUnReg']) and $_GET['selfUnReg'] == 1){

    $group_id = getDirectReference($_GET['group_id']);
    $user_id = getDirectReference($_GET['uid']);

    $unregister_from_group = Database::get()->query("DELETE FROM mentoring_group_members
                                                     WHERE group_id = ?d
                                                     AND user_id = ?d
                                                     AND is_tutor = ?d
                                                     AND status_request = ?d",$group_id, $user_id, 0, 1);

    if($unregister_from_group){
        Session::flash('message',$langUnRegisterGroupMentoringSuccess); 
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langUnRegisterGroupMentoringNoSuccess); 
        Session::flash('alert-class', 'alert-danger');
    }

    isMemberOfCommonGroup($uid,$mentoring_program_id,$group_id);
}

//send request for register to group
if(isset($_POST['action_send_request']) and $_POST['action_send_request'] == 'send_request'){
    $group_id = $_POST['group_id'];
    $user_id = $_POST['mentee_id'];

    $send_req = Database::get()->query("INSERT INTO mentoring_group_members SET
                                        group_id = ?d,
                                        user_id = ?d,
                                        is_tutor = ?d,
                                        status_request = ?d",$group_id, $user_id, 0, 0);

    if($send_req){

        // Send email to tutor of group
        $titleGroup = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->name;
        $userGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$user_id)->givenname;
        $userSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$user_id)->surname;

        $userEmail = Database::get()->queryArray("SELECT email FROM user WHERE id IN (SELECT user_id FROM mentoring_group_members
                                                                                        WHERE group_id = ?d
                                                                                        AND is_tutor = ?d)",$group_id,1);
        if(count($userEmail) > 0){
            foreach($userEmail as $e){

                $userEmailSender = $e->email;

                $emailHeader = "
                <!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langRequestToGroup $titleGroup</div>
                            </div>
                        </div>";

                $emailMain = "
                <!-- Body Section -->
                    <div id='mail-body'>
                        <br>
                        <div>$langDetailsUser</div>
                        <div id='mail-body-inner'>
                            <ul id='forum-category'>
                                <li><span><b>$langName: </b></span> <span>$userGivenname $userSurname</span></li>
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

                $emailsubject = 'Mentoring:'.$langRequestToGroup.' '.$titleGroup;

                $emailbody = $emailHeader.$emailMain;

                $emailPlainBody = html2text($emailbody);
                
                send_mail_multipart('', '', '', $userEmailSender, $emailsubject, $emailPlainBody, $emailbody);
            }
        }

        Session::flash('message',$langSendRequestGroupSuccess); 
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langSendRequestGroupNoSuccess); 
        Session::flash('alert-class', 'alert-danger');
    }

    isMemberOfCommonGroup($uid,$mentoring_program_id,$group_id);

  
}

//cancel request for register to group
if(isset($_POST['action_cancel_request']) and $_POST['action_cancel_request'] == 'cancel_request'){
    $group_id = $_POST['group_id'];
    $user_id = $_POST['mentee_id'];

    //check if mentor has accepted the request for mentee. If not then mentee can cancel the request
    $check_if_has_accepted_from_tutor = Database::get()->queryArray("SELECT *FROM mentoring_group_members WHERE
                                                                     group_id = ?d
                                                                     AND user_id = ?d
                                                                     AND is_tutor = ?d
                                                                     AND status_request = ?d",$group_id,$user_id,0,0);
    if(count($check_if_has_accepted_from_tutor) > 0){
        $cancel_req = Database::get()->query("DELETE FROM mentoring_group_members 
                                          WHERE group_id = ?d 
                                          AND user_id = ?d AND is_tutor = ?d 
                                          AND status_request = ?d",$group_id, $user_id, 0, 0);

        if($cancel_req){
            Session::flash('message',$langCancelRequestGroupSuccess); 
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langCancelRequestGroupNoSuccess); 
            Session::flash('alert-class', 'alert-danger');
        }
    }else{
        Session::flash('message',$langCancelRequestGroupNoContinue); 
        Session::flash('alert-class', 'alert-warning');
    }                   
    
    isMemberOfCommonGroup($uid,$mentoring_program_id,$group_id);

   
}

//settings for registration of mentees to a group
if(isset($_POST['action_settings_registration_of_mentee_to_group']) and $_POST['action_settings_registration_of_mentee_to_group'] == 'settings'){
    
    //First, check if a mentee has registered to many groups not only one to continue with settings.
    //If its ok then tutor of mentoring program can changes settings about registration of mentees to groups
    $check_if_exist_mentee_participate_to_many_groups_of_program = check_if_mentee_participate_to_many_groups_of_mentoring_program($mentoring_program_id);
    
    if($check_if_exist_mentee_participate_to_many_groups_of_program == 1){
        //When flag other_groups_reg = 0 in mentoring_programs table then mentees can register to many groups. If is 1 then only one group. 
        if(isset($_POST['reg_one'])){
            $reg = Database::get()->query("UPDATE mentoring_programs SET
                                            other_groups_reg = ?d
                                            WHERE id = ?d
                                            AND code = ?s",$_POST['reg_one'],$mentoring_program_id, $mentoring_program_code);
            if($reg){
                Session::flash('message',$langRegOneGroup); 
                Session::flash('alert-class', 'alert-success');
            }else{
                Session::flash('message',$langRegOneOrManyGroupsNoSuccess); 
                Session::flash('alert-class', 'alert-danger');
            }
            
        }
        if(isset($_POST['reg_many'])){
            $reg = Database::get()->query("UPDATE mentoring_programs SET
                                        other_groups_reg = ?d
                                        WHERE id = ?d
                                        AND code = ?s",$_POST['reg_many'],$mentoring_program_id, $mentoring_program_code);

            if($reg){
                Session::flash('message',$langRegManyGroup); 
                Session::flash('alert-class', 'alert-success');
            }else{
                Session::flash('message',$langRegOneOrManyGroupsNoSuccess); 
                Session::flash('alert-class', 'alert-danger');
            }
        }
    }else{
        Session::flash('message',$langExistsMenteesToParticipateInManyGroups); 
        Session::flash('alert-class', 'alert-danger');
    }
 
    
    redirect_to_home_page("modules/mentoring/programs/group/index.php");
}

// Mentor of group adding date and time for available contact with mentees
if(isset($_POST['add_datetime_mentor_of_group'])){
    $group_id = getDirectReference($_GET['space_group_id']);
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($_POST['enddate'] > $now and $_POST['startdate'] < $_POST['enddate']){
        Database::get()->query("INSERT INTO mentoring_mentor_availability_group SET
                            user_id = ?d, group_id = ?d , start = ?t, end = ?t",$_POST['datetime_mentor_id'],$_POST['datetime_group_id'],$_POST['startdate'],$_POST['enddate']);

        Session::flash('message',$langDatesAddedComplete); 
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langInvalidDates); 
        Session::flash('alert-class', 'alert-danger');
    }
    
    redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getInDirectReference($group_id));
}

//Delete Mentor's date
if(isset($_GET['delDateMentor'])){
    $group_id = getDirectReference($_GET['space_group_id']);
    $date_id = getDirectReference($_GET['delDateMentor']);


    //Before delete the current date, check if a mentee has booking for this date
    $dateStart = Database::get()->querySingle("SELECT start FROM mentoring_mentor_availability_group 
                                                WHERE id = ?d",$date_id)->start;
    $dateEnd = Database::get()->querySingle("SELECT end FROM mentoring_mentor_availability_group 
                                                WHERE id = ?d",$date_id)->end;
    $dateMentor = Database::get()->querySingle("SELECT user_id FROM mentoring_mentor_availability_group 
                                                    WHERE id = ?d",$date_id)->user_id;
    $dateGroup = Database::get()->querySingle("SELECT group_id FROM mentoring_mentor_availability_group 
                                                    WHERE id = ?d",$date_id)->group_id;

    $checkIfBookingExist = Database::get()->querySingle("SELECT COUNT(id) as i FROM mentoring_booking WHERE 
                                                        mentoring_program_id = ?d AND group_id = ?d AND mentor_id = ?d
                                                        AND start = ?t AND end = ?t",$mentoring_program_id,$dateGroup,$dateMentor,$dateStart,$dateEnd)->i;
    if($checkIfBookingExist == 0){
        Database::get()->query("DELETE FROM mentoring_mentor_availability_group WHERE id = ?d",$date_id);
        Session::flash('message',$langDatesADeletedComplete); 
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langDatesDeletedNotComplete); 
        Session::flash('alert-class', 'alert-danger');
    }
    
    redirect_to_home_page("modules/mentoring/programs/group/group_space.php?space_group_id=".getInDirectReference($group_id));
}


//space of group id
if(isset($_GET['space_group_id'])){

    if(intval(getDirectReference($_GET['space_group_id'])) != 0){
        $data['group_id'] = $group_id = getDirectReference($_GET['space_group_id']);

        $check_group = Database::get()->queryArray("SELECT *FROM mentoring_group WHERE id = ?d",$group_id);
        if(count($check_group) == 0){
            redirect_to_home_page("modules/mentoring/programs/group/select_group.php");
        }
    }else{
        after_reconnect_go_to_mentoring_homepage();
    }

    $data['is_mentee'] = $is_mentee = check_if_uid_is_mentee_for_current_group($uid,$group_id);
    $data['is_editor_current_group'] = $is_editor_current_group = get_editor_for_current_group($uid,$group_id);
    $data['is_tutor_of_mentoring_program'] = $is_tutor_of_mentoring_program = show_mentoring_program_editor($mentoring_program_id,$uid);

    //only mentee,tutor,editor of group can view group
    if($is_mentee or $is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin){

        $toolName = get_name_for_current_group($group_id);

        //get registration-requests if exists
        $data['exist_request'] = Database::get()->queryArray("SELECT *FROM mentoring_group_members 
                                                            WHERE group_id = ?d AND status_request = ?d",$group_id,0);

        $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                        WHERE id = ?d 
                                                        AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

        if($checkIsCommon == 1){

            $data['is_editor_wall_common_group'] = $is_mentee || $is_editor_current_group || $is_tutor_of_mentoring_program || $is_admin;

            $data['isCommonGroup'] = 1;

             $data['action_bar'] = action_bar([
                [ 'title' => trans('langBackPage'),
                    'url' => $urlServer.'modules/mentoring/programs/group/index.php?commonGroupView=1',
                    'icon' => 'fa-chevron-left',
                    'level' => 'primary-label',
                    'button-class' => 'backButtonMentoring' ]
                ], false);
        }else{

            $data['isCommonGroup'] = 0;

            $data['action_bar'] = action_bar([
                [ 'title' => trans('langBackPage'),
                    'url' => $urlServer.'modules/mentoring/programs/group/index.php',
                    'icon' => 'fa-chevron-left',
                    'level' => 'primary-label',
                    'button-class' => 'backButtonMentoring' ]
                ], false);
        }

        view('modules.mentoring.programs.group.group_space', $data);
        
    }else{
        redirect_to_home_page("modules/mentoring/programs/show_programs.php");
    }
    
}




