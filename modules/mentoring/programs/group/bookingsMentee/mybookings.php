<?php

$require_login = TRUE;


require_once '../../../../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

if(isset($_GET['space_group_id']) and intval(getDirectReference($_GET['space_group_id'])) != 0){
    put_session_group_id_in_db_and_get_this_after_logout($uid,$mentoring_program_code,getDirectReference($_GET['space_group_id']));
    $_SESSION['mentoring_group_id'] = getDirectReference($_GET['space_group_id']);
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

        if($is_tutor_of_mentoring_program or $is_admin){
            $toolName = 'MyBookings'.' '.get_name_for_current_group($group_id);
        }else{
            $toolName = $langMyBookings;
        }
        

        $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;

        if($checkIsCommon == 1){
            $data['isCommonGroup'] = 1;
        }else{
            $data['isCommonGroup'] = 0;
        }

        $now = date('Y-m-d H:i:s', strtotime('now'));

        if(isset($_POST['delete_history_booking_id'])){
            if($_POST['delete_history_booking_id'] == 'delAllBooking'){
                $bookings_ids = array();
                foreach($_POST['all_booking_ids'] as $b){
                    $bookings_ids[] = $b;
                }
                $values = implode(',', $bookings_ids);
                Database::get()->query("DELETE FROM mentoring_booking WHERE id IN ($values)");
                Session::flash('message',$langBookingHasCalceledAll);
            }else{
                Database::get()->query("DELETE FROM mentoring_booking WHERE id = ?d",$_POST['booking_id']);
                Session::flash('message',$langBookingHasCalceledOne);
            }
            
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/mentoring/programs/group/bookingsMentee/mybookings.php?space_group_id=".getIndiRectReference($group_id));
        }

        if(isset($_POST['accept_booking'])){
            $accept_booking = Database::get()->query("UPDATE mentoring_booking SET accepted = ?d WHERE id = ?d",1,$_POST['accept_booking_id']);
            if($accept_booking){
                $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT mentee_id FROM mentoring_booking_user WHERE mentoring_booking_id = ?d)",$_POST['accept_booking_id'])->email;
                $details_booking = Database::get()->queryArray("SELECT *FROM mentoring_booking WHERE id = ?d",$_POST['accept_booking_id']);
                
                if(count($details_booking) > 0){
                    foreach($details_booking as $d){
                        $userName = $d->title;
                        $mentorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$d->mentor_id)->givenname;
                        $mentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$d->mentor_id)->surname;
                        $dateFrom = $d->start;
                        $dateEnd = $d->end;
                    }

                    Session::flash('message',$langBookingHasAccepted.$userName);
                    Session::flash('alert-class', 'alert-success');


                    $emailHeader = "
                        <!-- Header Section -->
                                <div id='mail-header'>
                                    <br>
                                    <div>
                                        <div id='header-title'>$langYourBookingHasAccepted</div>
                                    </div>
                                </div>";

                    $emailMain = "
                    <!-- Body Section -->
                        <div id='mail-body'>
                            <br>
                            <div>$langDetailsBooking</div>
                            <div id='mail-body-inner'>
                                <ul id='forum-category'>
                                    <li><span><b>$langName: </b></span> <span>$userName</span></li>
                                    <li><span><b>$langMentoringMentors: </b></span> <span>$mentorName $mentorSurname</span></li>
                                    <li><span><b>$langDate: </b></span>$dateFrom - $dateEnd<span></span></li>
                                    <li><strong>$langUpdateSoon</strong></li>
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

                        $emailsubject = 'Mentoring:'.$langYourBookingHasAccepted;

                        $emailbody = $emailHeader.$emailMain;

                        $emailPlainBody = html2text($emailbody);
                        
                        send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);
                        
                        redirect_to_home_page("modules/mentoring/programs/group/bookingsMentee/mybookings.php?space_group_id=".getIndiRectReference($group_id));

                }

               
            }
        }


        if(isset($_POST['delete_booking'])){

            $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT mentee_id FROM mentoring_booking_user WHERE mentoring_booking_id = ?d)",$_POST['booking_id'])->email;
            $details_booking = Database::get()->queryArray("SELECT *FROM mentoring_booking WHERE id = ?d",$_POST['booking_id']);
            
            if(count($details_booking) > 0){
                foreach($details_booking as $d){
                    $userName = $d->title;
                    $mentorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$d->mentor_id)->givenname;
                    $mentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$d->mentor_id)->surname;
                    $dateFrom = $d->start;
                    $dateEnd = $d->end;
                }
            }

            Database::get()->query("DELETE FROM mentoring_booking WHERE id = ?d",$_POST['booking_id']);
            Session::flash('message',$langBookingHasCalceled);
            Session::flash('alert-class', 'alert-success');


            $emailHeader = "
            <!-- Header Section -->
                    <div id='mail-header'>
                        <br>
                        <div>
                            <div id='header-title'>$langYourBookingHasCanceled</div>
                        </div>
                    </div>";

            $emailMain = "
            <!-- Body Section -->
                <div id='mail-body'>
                    <br>
                    <div>$langDetailsBooking</div>
                    <div id='mail-body-inner'>
                        <ul id='forum-category'>
                            <li><span><b>$langName: </b></span> <span>$userName</span></li>
                            <li><span><b>$langMentoringMentors: </b></span> <span>$mentorName $mentorSurname</span></li>
                            <li><span><b>$langDate: </b></span>$dateFrom - $dateEnd<span></span></li>
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

            $emailsubject = 'Mentoring:'.$langYourBookingHasCanceled;

            $emailbody = $emailHeader.$emailMain;

            $emailPlainBody = html2text($emailbody);
            
            send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);
            
            redirect_to_home_page("modules/mentoring/programs/group/bookingsMentee/mybookings.php?space_group_id=".getIndiRectReference($group_id));
        }

        if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin){
            if($is_editor_current_group){
                $data['mybookings'] = Database::get()->queryArray("SELECT *FROM mentoring_booking 
                                                                LEFT JOIN mentoring_booking_user 
                                                                ON mentoring_booking_user.mentoring_booking_id = mentoring_booking.id 
                                                                WHERE mentoring_booking.mentoring_program_id = ?d 
                                                                AND mentoring_booking.group_id = ?d
                                                                AND mentoring_booking.mentor_id = ?d
                                                                AND (mentoring_booking.start <= ?t AND mentoring_booking.end >= ?t OR mentoring_booking.start > ?t)
                                                                ORDER BY start ASC",$mentoring_program_id,$group_id,$uid,$now,$now,$now);
                // for common group set booking_history null                                      
                $data['booking_history'] = array();
            }else{
                $data['mybookings'] = Database::get()->queryArray("SELECT *FROM mentoring_booking 
                                        LEFT JOIN mentoring_booking_user 
                                        ON mentoring_booking_user.mentoring_booking_id = mentoring_booking.id 
                                        WHERE mentoring_booking.mentoring_program_id = ?d 
                                        AND mentoring_booking.group_id = ?d
                                        AND (mentoring_booking.start <= ?t AND mentoring_booking.end >= ?t OR mentoring_booking.start > ?t)
                                        ORDER BY start ASC",$mentoring_program_id,$group_id,$now,$now,$now); 

                $data['booking_history'] = Database::get()->queryArray("SELECT *FROM mentoring_booking 
                                            LEFT JOIN mentoring_booking_user 
                                            ON mentoring_booking_user.mentoring_booking_id = mentoring_booking.id 
                                            WHERE mentoring_booking.mentoring_program_id = ?d 
                                            AND mentoring_booking.group_id = ?d
                                            AND mentoring_booking.end < ?t
                                            ORDER BY start ASC",$mentoring_program_id,$group_id,$now);
            }
        }else{
            $data['mybookings'] = Database::get()->queryArray("SELECT *FROM mentoring_booking WHERE
                                                            id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id = ?d)
                                                            AND mentoring_program_id = ?d
                                                            AND group_id = ?d
                                                            AND (start <= ?t AND end >= ?t OR start > ?t)
                                                            ORDER BY start ASC",$uid,$mentoring_program_id,$group_id,$now,$now,$now); 
        }
        


        $data['action_bar'] = action_bar([
            [ 'title' => trans('langBackPage'),
                'url' => $urlServer.'modules/mentoring/programs/group/group_space.php?space_group_id='.getInDirectReference($group_id),
                'icon' => 'fa-chevron-left',
                'level' => 'primary-label',
                'button-class' => 'backButtonMentoring' ]
            ], false);

        view('modules.mentoring.programs.group.bookingsMentee.mybookings', $data);
        
    }else{
        redirect_to_home_page("modules/mentoring/programs/show_programs.php");
    }
    
}




