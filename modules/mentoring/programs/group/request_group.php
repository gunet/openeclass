<?php

$require_login = TRUE;


require_once '../../../../include/baseTheme.php';
require_once 'main/eportfolio/eportfolio_functions.php';
require_once 'include/sendMail.inc.php';
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

        $toolName = $langRequestsRegisterGroups.' -- '.get_name_for_current_group($group_id);

        $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

        if($checkIsCommon == 1){
            $data['isCommonGroup'] = 1;
        }else{
            $data['isCommonGroup'] = 0;
        }

        if(isset($_GET['restore'])){
            $user_id_restore = getDirectReference($_GET['user']);
            $res = Database::get()->query("UPDATE mentoring_group_members SET
                                    status_request = ?d WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d",0,$group_id,$user_id_restore,0);
            if($res){
                Session::flash('message',$langReqEditMsg); 
                Session::flash('alert-class', 'alert-success');
            }

            redirect_to_home_page("modules/mentoring/programs/group/request_group.php?group_id=".getInDirectReference($group_id));
            
        }

        if(isset($_POST['accept_group_request'])){
            $user_id_request = $_POST['user_id'];

            //check if mentee has canceled request before continue
            $check = Database::get()->queryArray("SELECT *FROM mentoring_group_members
                                                  WHERE group_id = ?d
                                                  AND user_id = ?d
                                                  AND is_tutor = ?d
                                                  AND status_request = ?d",$group_id,$user_id_request,0,0);
            if(count($check) > 0){
                $session_msg = 0;
                if($_POST['accept_group_request'] == 'accept'){

                    $max_members_of_group = Database::get()->querySingle("SELECT max_members FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->max_members;
                    $all_members_in_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                                            WHERE group_id = ?d
                                                                            AND is_tutor = ?d AND status_request = ?d",$group_id,0,1)->ui;

                    //check if group-registration is for many or one mentee
                    $registration_check = Database::get()->querySingle("SELECT other_groups_reg FROM mentoring_programs WHERE id = ?d",$mentoring_program_id)->other_groups_reg;
                    if($registration_check == 0){

                        if($max_members_of_group == 0 or ($max_members_of_group > 0 and $all_members_in_group < $max_members_of_group)){
                            Database::get()->query("UPDATE mentoring_group_members SET
                                                status_request = ?d WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",1,$group_id,$user_id_request,0,0);

                            // Send email to user mentee
                            $titleGroup = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->name;
                            $userGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$user_id_request)->givenname;
                            $userSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$user_id_request)->surname;
                            $userEmail = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$user_id_request)->email;

                            $emailHeader = "
                            <!-- Header Section -->
                                    <div id='mail-header'>
                                        <br>
                                        <div>
                                            <div id='header-title'>$langRequestInGroupHasAccepted $titleGroup</div>
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

                            $emailsubject = 'Mentoring:'.$langRequestInGroupHasAccepted.' '.$titleGroup;

                            $emailbody = $emailHeader.$emailMain;

                            $emailPlainBody = html2text($emailbody);
                            
                            send_mail_multipart('', '', '', $userEmail, $emailsubject, $emailPlainBody, $emailbody);
                        }else{
                            $session_msg = 2;
                        }
                    }else{
                        //because groups-registration is for one mentee group by group then check if mentee is member in other group before continue
                        //and this other group not is the common group.
                        $registration_mentee_checking = Database::get()->queryArray("SELECT *FROM mentoring_group_members 
                                                                                     WHERE group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = ?d)
                                                                                     AND user_id = ?d
                                                                                     AND is_tutor = ?d
                                                                                     AND status_request = ?d",$mentoring_program_id,0,$user_id_request,0,1);

                        if(count($registration_mentee_checking) > 0 && $checkIsCommon == 0){
                            $session_msg = 1;
                            Session::flash('message',$langReqUserToGroupNoCompleteIsMemberInOther); 
                            Session::flash('alert-class', 'alert-warning');
                        }else{

                            if($max_members_of_group == 0 or ($max_members_of_group > 0 and $all_members_in_group < $max_members_of_group)){
                                Database::get()->query("UPDATE mentoring_group_members SET
                                            status_request = ?d WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",1,$group_id,$user_id_request,0,0);

                                // Send email to user mentee
                                $titleGroup = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->name;
                                $userGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$user_id_request)->givenname;
                                $userSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$user_id_request)->surname;
                                $userEmail = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$user_id_request)->email;

                                $emailHeader = "
                                <!-- Header Section -->
                                        <div id='mail-header'>
                                            <br>
                                            <div>
                                                <div id='header-title'>$langRequestInGroupHasAccepted $titleGroup</div>
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

                                $emailsubject = 'Mentoring:'.$langRequestInGroupHasAccepted.' '.$titleGroup;

                                $emailbody = $emailHeader.$emailMain;

                                $emailPlainBody = html2text($emailbody);
                                
                                send_mail_multipart('', '', '', $userEmail, $emailsubject, $emailPlainBody, $emailbody);
                            }else{
                                $session_msg = 2;
                            }
                        }
                    }
                    
                }elseif($_POST['accept_group_request'] == 'deny'){
                    Database::get()->query("UPDATE mentoring_group_members SET
                        status_request = ?d WHERE group_id = ?d AND user_id = ?d AND is_tutor = ?d AND status_request = ?d",2,$group_id,$user_id_request,0,0);

                        // Send email to user mentee
                        $titleGroup = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->name;
                        $userGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$user_id_request)->givenname;
                        $userSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$user_id_request)->surname;
                        $userEmail = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$user_id_request)->email;

                        $emailHeader = "
                            <!-- Header Section -->
                                    <div id='mail-header'>
                                        <br>
                                        <div>
                                            <div id='header-title'>$langRequestInGroupHasDiscurded $titleGroup</div>
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

                            $emailsubject = 'Mentoring:'.$langRequestInGroupHasDiscurded.' '.$titleGroup;

                            $emailbody = $emailHeader.$emailMain;

                            $emailPlainBody = html2text($emailbody);
                            
                            send_mail_multipart('', '', '', $userEmail, $emailsubject, $emailPlainBody, $emailbody);


                }elseif($_POST['accept_group_request'] == 'delete'){
                    Database::get()->query("DELETE FROM mentoring_group_members
                                            WHERE group_id = ?d AND user_id = ?d
                                            AND is_tutor = ?d AND status_request = ?d",$group_id,$user_id_request,0,0);
                }
                if($session_msg == 0){
                    Session::flash('message',$langReqEditMsg); 
                    Session::flash('alert-class', 'alert-success');
                }
                if($session_msg == 2){
                    Session::flash('message',$langLimitGroupNotAllowed); 
                    Session::flash('alert-class', 'alert-warning');
                }
            }else{
                Session::flash('message',$langReqNoModify); 
                Session::flash('alert-class', 'alert-warning');
            }

            redirect_to_home_page("modules/mentoring/programs/group/request_group.php?group_id=".getInDirectReference($group_id));
        }

        $data['all_requests'] = Database::get()->queryArray("SELECT id,givenname,surname FROM user
                                                             WHERE id IN (SELECT user_id FROM mentoring_group_members
                                                                          WHERE group_id = ?d AND is_tutor = ?d
                                                                          AND status_request = ?d)",$group_id,0,0);

        $data['all_denied_requests'] = Database::get()->queryArray("SELECT id,givenname,surname FROM user
                                                                WHERE id IN (SELECT user_id FROM mentoring_group_members
                                                                            WHERE group_id = ?d AND is_tutor = ?d
                                                                            AND status_request = ?d)",$group_id,0,2);

        $data['action_bar'] = action_bar([
            [ 'title' => trans('langBackPage'),
                'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getIndirectReference($group_id),
                'icon' => 'fa-chevron-left',
                'level' => 'primary-label',
                'button-class' => 'backButtonMentoring' ]
            ], false);

        view('modules.mentoring.programs.group.request_group', $data);
        
    }else{
        redirect_to_home_page("modules/mentoring/programs/show_programs.php");
    }
    
}




