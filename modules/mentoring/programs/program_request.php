<?php

$require_login = TRUE;


require_once '../../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

//after_reconnect_go_to_mentoring_homepage();
if(!isset($mentoring_program_id) or !$mentoring_program_id){
    redirect_to_home_page("modules/mentoring/mentoring_platform_home.php");
}

//request sent from mentee
if(isset($_POST['request_submit'])){
    $mentoring_program_id = $_POST['mentoring_program_id'];



    $result = Database::get()->query("INSERT INTO mentoring_programs_requests SET
                                       mentoring_program_id = ?d,
                                       guided_id = ?d,
                                       status_request = ?d",$_POST['mentoring_program_id'], $_POST['guided_id'], $_POST['request_val']);
    if($result){

        //send email to tutor of program.
            
            $titleProgram = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE id = ?d",$mentoring_program_id)->title;

            $userGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$_POST['guided_id'])->givenname;
            $userSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$_POST['guided_id'])->surname;

            $emailUser = Database::get()->queryArray("SELECT email FROM user
                                                        WHERE id IN (SELECT user_id FROM mentoring_programs_user
                                                                        WHERE mentoring_program_id = ?d
                                                                        AND status = ?d
                                                                        AND tutor = ?d)",$mentoring_program_id,USER_TEACHER,1);
            if(count($emailUser) > 0){
                foreach($emailUser as $e){

                    $emailTutorUser = $e->email;

                    $emailHeader = "
                    <!-- Header Section -->
                            <div id='mail-header'>
                                <br>
                                <div>
                                    <div id='header-title'>$langSendMenteeRequestToProgram $titleProgram</div>
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

                    $emailsubject = 'Mentoring:'.$langSendMenteeRequestToProgram.' '.$titleProgram;

                    $emailbody = $emailHeader.$emailMain;

                    $emailPlainBody = html2text($emailbody);
                    
                    send_mail_multipart('', '', '', $emailTutorUser, $emailsubject, $emailPlainBody, $emailbody);
                }
            }

        Session::flash('message',$langRequestSendSuccess);
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langNoRequestSendSuccess);
        Session::flash('alert-class', 'alert-danger');
    }
}

//request canceled from mentee
if(isset($_POST['cancel_request'])){
    $mentoring_program_id = $_POST['mentoring_program_id'];

    //check if request is modified from tutor. If not then continue to cancel the current request.
    $check_is_modified_request_before_canceled = Database::get()->queryArray("SELECT *FROM mentoring_programs_requests 
                                                                    WHERE mentoring_program_id = ?d
                                                                    AND guided_id = ?d
                                                                    AND status_request = ?d",$_POST['mentoring_program_id'], $_POST['guided_id'],0);
    if(count($check_is_modified_request_before_canceled) > 0){
        $result = Database::get()->query("DELETE FROM mentoring_programs_requests 
                                        WHERE mentoring_program_id = ?d
                                        AND guided_id = ?d
                                        AND status_request = ?d",$_POST['mentoring_program_id'], $_POST['guided_id'],0);

        if($result){
            Session::flash('message',$langCancelRequestSuccess);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langCancelRequestNoSuccess);
            Session::flash('alert-class', 'alert-danger');
        }
    }else{
        Session::flash('message',$langCancelRequestIsModified);
        Session::flash('alert-class', 'alert-warning');
    }
}

//accept,deny,delete request from tutor
if(isset($_POST['action_request'])){
    $mentoring_program_id = $_POST['mentoring_program_id'];
    $guided_id = $_POST['guided_id'];
    $key_id = $_POST['key_id'];

    //check if request from mentee exists yet. If exists then continue to modify the request from tutor of program
    $check_exist_request_yet = Database::get()->queryArray("SELECT *FROM mentoring_programs_requests
                                                            WHERE mentoring_program_id = ?d
                                                            AND guided_id = ?d
                                                            AND (status_request = ?d OR status_request = ?d)",$mentoring_program_id,$guided_id,0,2);
    if(count($check_exist_request_yet) > 0){
        $user_details = Database::get()->queryArray("SELECT username,givenname,surname FROM user WHERE id = ?d",$guided_id);
        foreach($user_details as $user){
            $username = $user->username;
            $name = $user->givenname.' '.$user->surname;
        }

        if($_POST['action_request'] == 'accepted'){
            $accepted = Database::get()->query("UPDATE mentoring_programs_requests SET status_request = 1
                                            WHERE id = ?d AND mentoring_program_id = ?d
                                            AND guided_id = ?d",$key_id, $mentoring_program_id, $guided_id);
            //add guided_id to the program
            if($accepted){
                $user_status = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d",$guided_id)->status;
                //if coordinator or mentor of platform has sent request to participate as mentee in program, then set status equals with USER_STUDENT
                if($user_status == USER_TEACHER){
                    $user_status = USER_STUDENT;
                }
                $register_guided_to_program = Database::get()->query("INSERT INTO mentoring_programs_user SET
                                                                    mentoring_program_id = ?d,
                                                                    user_id = ?d,
                                                                    status = ?d,
                                                                    tutor = ?d,
                                                                    reg_date = " . DBHelper::timeAfter() ." ,
                                                                    mentor = ?d,
                                                                    is_guided = ?d",$mentoring_program_id ,$guided_id ,$user_status , 0, 0 ,1);

                // add mentee in common group automatically
                $theCommonGroupId = Database::get()->querySingle("SELECT id FROM mentoring_group
                                                                    WHERE mentoring_program_id = ?d
                                                                    AND common = ?d",$mentoring_program_id,1)->id;

                $max_members_of_group = Database::get()->querySingle("SELECT max_members FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$theCommonGroupId,$mentoring_program_id)->max_members;
                $all_members_in_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                                        WHERE group_id = ?d
                                                                        AND is_tutor = ?d AND status_request = ?d",$theCommonGroupId,0,1)->ui;
                                                                        
                if($max_members_of_group == 0 or ($max_members_of_group > 0 and $all_members_in_group < $max_members_of_group)){

                    $addUserInCommonGroup = Database::get()->query("INSERT INTO mentoring_group_members SET
                                                                    group_id = ?d,
                                                                    user_id = ?d,
                                                                    is_tutor = ?d,
                                                                    status_request = ?d",$theCommonGroupId, $guided_id, 0, 1);
                }

                // Send email to user mentee
                $titleProgram = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE id = ?d",$mentoring_program_id)->title;
                $userGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$guided_id)->givenname;
                $userSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$guided_id)->surname;
                $userEmail = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$guided_id)->email;

                $emailHeader = "
                    <!-- Header Section -->
                            <div id='mail-header'>
                                <br>
                                <div>
                                    <div id='header-title'>$langAcceptRequestInProgram $titleProgram</div>
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
                                    <li><span>$langYourRequestHasAccepted</span></li>
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

                    $emailsubject = 'Mentoring:'.$langAcceptRequestInProgram.' '.$titleProgram;

                    $emailbody = $emailHeader.$emailMain;

                    $emailPlainBody = html2text($emailbody);
                    
                    send_mail_multipart('', '', '', $userEmail, $emailsubject, $emailPlainBody, $emailbody);

                                                       
                
                Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_REQUESTS ,MENTORING_LOG_INSERT, array('username' => $username,'name' => $name,'type_request' => 1));
            }
        }
        elseif($_POST['action_request'] == 'denied'){
            $denied = Database::get()->query("UPDATE mentoring_programs_requests SET status_request = 2
                                            WHERE id = ?d AND mentoring_program_id = ?d
                                            AND guided_id = ?d",$key_id, $mentoring_program_id, $guided_id);

             // Send email to user mentee
             $titleProgram = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE id = ?d",$mentoring_program_id)->title;
             $userGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$guided_id)->givenname;
             $userSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$guided_id)->surname;
             $userEmail = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$guided_id)->email;

             $emailHeader = "
                 <!-- Header Section -->
                         <div id='mail-header'>
                             <br>
                             <div>
                                 <div id='header-title'>$langDiscardRequestInProgram $titleProgram</div>
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
                                 <li><span>$langYourRequestHaDiscurded</span></li>
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

                 $emailsubject = 'Mentoring:'.$langDiscardRequestInProgram.' '.$titleProgram;

                 $emailbody = $emailHeader.$emailMain;

                 $emailPlainBody = html2text($emailbody);
                 
                 send_mail_multipart('', '', '', $userEmail, $emailsubject, $emailPlainBody, $emailbody);

            Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_REQUESTS ,MENTORING_LOG_MODIFY, array('username' => $username,'name' => $name,'type_request' => 2));
        }
        elseif($_POST['action_request'] == 'deleted'){
            $deleted = Database::get()->query("DELETE FROM mentoring_programs_requests
                                            WHERE id = ?d AND mentoring_program_id = ?d
                                            AND guided_id = ?d",$key_id, $mentoring_program_id, $guided_id);

            Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_REQUESTS, MENTORING_LOG_DELETE, array('username' => $username,'name' => $name,'type_request' => -1)); 

        }elseif($_POST['action_request'] == 'reset'){
            $reset = Database::get()->query("UPDATE mentoring_programs_requests SET status_request = 0
                                            WHERE id = ?d AND mentoring_program_id = ?d
                                            AND guided_id = ?d",$key_id, $mentoring_program_id, $guided_id);

            Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_REQUESTS, MENTORING_LOG_MODIFY, array('username' => $username,'name' => $name,'type_request' => 0));
        }

        if($register_guided_to_program or $denied or $deleted or $reset){
            Session::flash('message',$langEditNodeSuccess);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langNoEditRequestSuccess);
            Session::flash('alert-class', 'alert-success');
        }
    }else{
        Session::flash('message',$langReuestHasCanceledFromUser);
        Session::flash('alert-class', 'alert-warning');
    }
}


redirect_to_home_page('/mentoring_programs/'.$mentoring_program_code.'/index.php');


