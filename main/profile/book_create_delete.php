<?php         

/**
 *
 * @file booking_create_delete.php
 * @brief Display user available date
 */
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';

//show all events
if(isset($_POST['action']) or isset($_GET['view'])) {

    if(isset($_GET['view']) and isset($_GET['show_tutor'])){

        $tutor_id = intval($_GET['show_tutor']);
        $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

        $eventArr = array();

        $result_events = Database::get()->queryArray("SELECT id,user_id,start,end FROM date_availability_user
                                                        WHERE start BETWEEN (?s) AND (?s)
                                                        AND user_id = ?d",$start,$end,$tutor_id);

        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'tutor' => $row->user_id,
                    'title' => TitleBooking($row->start,$row->end,$row->user_id),
                    'start' => $row->start,
                    'end' => $row->end,
                    'className' => classNameBooking($row->start,$row->end,$row->user_id),
                    'backgroundColor' => ColorExistBooking($row->start,$row->end,$row->user_id)
                ];
            }
        }
        
        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();
       

    }

    // add new event section
    elseif($_POST['action'] == "add"){   

        //Before add booking, check if tutor has deleted the current date for booking
        $checkDateTutorExist = Database::get()->querySingle("SELECT COUNT(id) as c FROM date_availability_user
                                                                WHERE user_id = ?d
                                                                AND start = ?t
                                                                AND end = ?t",$_POST['tutor_Id'],$_POST["start"],$_POST["end"])->c;

        if($checkDateTutorExist > 0){

            //check if another user has made booking before continue
            $checkOtherUserBooking = Database::get()->querySingle("SELECT COUNT(id) as c FROM date_booking
                                                                        WHERE teacher_id = ?d
                                                                        AND start = ?t
                                                                        AND end = ?t",$_POST['tutor_Id'],$_POST["start"],$_POST["end"])->c;


            if($checkOtherUserBooking == 0){

                $add = Database::get()->query("INSERT INTO date_booking SET
                                    teacher_id = ?d,
                                    title = ?s,
                                    start = ?s,
                                    end = ?s",$_POST["tutor_Id"],$_POST['title'],date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s',strtotime($_POST["end"])));
            

                
                $add_bookind_by_user = Database::get()->query("INSERT INTO date_booking_user SET
                                                booking_id = ?d,
                                                student_id = ?d",$add->lastInsertID,$uid);
                    
                    
                //send email to the tutor about the booking from user
                $userName = $_POST['title'];
                $tutorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$_POST["tutor_Id"])->givenname;
                $tutorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$_POST["tutor_Id"])->surname;
                $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$_POST["tutor_Id"])->email;
                $dateFrom = $_POST["start"];
                $dateEnd = $_POST["end"];

                $emailHeader = "
                <!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langAddBookingByUser</div>
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
                                <li><span><b>$langTutor: </b></span> <span>$tutorName $tutorSurname</span></li>
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

                $emailsubject = $siteName.':'.$langAddBookingByUser;

                $emailbody = $emailHeader.$emailMain;

                $emailPlainBody = html2text($emailbody);
                
                send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);
                
                echo 1;
                   
                
            }else{
                echo 0;
            }
            
        }else{
            echo 2;
        }
            
       
        
        
        exit();

    }

    // remove event
    elseif($_POST['action'] == "delete"){
        
        $event_id = $_POST['id'];
        $tutor_availabity_group_id = Database::get()->queryArray("SELECT * FROM date_availability_user WHERE id = ?d",$event_id);

        $tutor_user = '';
        $tutor_start = '';
        $tutor_end = '';
        if(count($tutor_availabity_group_id) > 0){
            foreach($tutor_availabity_group_id as $m){
                $tutor_user = $m->user_id;
                $tutor_start = $m->start;
                $tutor_end = $m->end;
            }

            $bookingId = Database::get()->querySingle("SELECT id FROM date_booking
                                                        WHERE teacher_id = ?d
                                                        AND start = ?t
                                                        AND end = ?t",$tutor_user,$tutor_start,$tutor_end)->id;
            
            //send email to the tutor about canceling booking by user
            $userName = Database::get()->querySingle("SELECT title FROM date_booking WHERE id = ?d",$bookingId)->title;
            $tutorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id IN (SELECT teacher_id FROM date_booking WHERE id = ?d)",$bookingId)->givenname;
            $tutorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id IN (SELECT teacher_id FROM date_booking WHERE id = ?d)",$bookingId)->surname;
            $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT teacher_id FROM date_booking WHERE id = ?d)",$bookingId)->email;
            $dateFrom = Database::get()->querySingle("SELECT start FROM date_booking WHERE id = ?d",$bookingId)->start;
            $dateEnd = Database::get()->querySingle("SELECT end FROM date_booking WHERE id = ?d",$bookingId)->end;

            $del = Database::get()->query("DELETE FROM date_booking 
                                            WHERE teacher_id = ?d
                                            AND start = ?t
                                            AND end = ?t",$tutor_user,$tutor_start,$tutor_end);

            if($del){

                $emailHeader = "
                <!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langDeleteBookingByUser</div>
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
                                <li><span><b>$langTutor: </b></span> <span>$tutorName $tutorSurname</span></li>
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

                $emailsubject = $siteName.':'.$langDeleteBookingByUser.'--'.$userName;

                $emailbody = $emailHeader.$emailMain;

                $emailPlainBody = html2text($emailbody);
                
                send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);

                echo 1;
            }

        }else{
            echo 0;
        }
        
        exit();

    }

}


function classNameBooking($bookingTutorStart,$bookingTutorEnd,$tutor_id){
    global $uid;

    $html_bookingClassName = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingTutorEnd < $now){
        $hasExpired = true;
    }

    //check if other mentee has booking with this mentor before continue
    $checkBookingByOtherUserOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM date_booking
                                                                      WHERE id IN (SELECT booking_id FROM date_booking_user WHERE student_id NOT IN (?d))
                                                                      AND teacher_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$tutor_id,$bookingTutorStart,$bookingTutorEnd)->id;

    if($checkBookingByOtherUserOfGroup == 0 and !$hasExpired){
        $BookingByUser = Database::get()->queryArray("SELECT * FROM date_booking 
                                                    WHERE teacher_id = ?d
                                                    AND start = ?t 
                                                    AND end = ?t
                                                    AND id IN (SELECT booking_id FROM date_booking_user WHERE student_id = ?d)",$tutor_id,$bookingTutorStart,$bookingTutorEnd,$uid);

        if(count($BookingByUser) > 0){
            $html_bookingClassName = 'bookingDelete';
        }else{
            $html_bookingClassName = 'bookingAdd';
        }
    }else{
        if($hasExpired){
            $html_bookingClassName = 'pe-none opacity-help';
        }else{
            $html_bookingClassName = 'pe-none';
        }
        
    }
    

    return $html_bookingClassName;
}

function TitleBooking($bookingTutorStart,$bookingTutorEnd,$tutor_id){
    global $uid ,$langHaveDoneBooking, $langDoBooking, $langDisableBooking, $langBookingIsDone, $langAcceptBooking, $langYes, $langNo;

    $html_bookingTitle = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingTutorEnd < $now){
        $hasExpired = true;
    }

    //check if another user has booked with this tutor before continue
    $checkBookingByOtherUserOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM date_booking
                                                                      WHERE id IN (SELECT booking_id FROM date_booking_user WHERE student_id NOT IN (?d))
                                                                      AND teacher_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$tutor_id,$bookingTutorStart,$bookingTutorEnd)->id;

    if($checkBookingByOtherUserOfGroup == 0 and !$hasExpired){
        $BookingByUser = Database::get()->queryArray("SELECT * FROM date_booking 
                                                    WHERE teacher_id = ?d
                                                    AND start = ?t 
                                                    AND end = ?t
                                                    AND id IN (SELECT booking_id FROM date_booking_user WHERE student_id = ?d)",$tutor_id,$bookingTutorStart,$bookingTutorEnd,$uid);

        if(count($BookingByUser) > 0){
            $html_bookingTitle .= "<p class='text-center TextBold smallText simple-user-booking-event'>$langHaveDoneBooking</p>";
            foreach($BookingByUser as $b){
                if($b->accepted == 1){
                    $html_bookingTitle .= "<p class='text-center TextBold smallText simple-user-booking-event mt-1'>$langAcceptBooking: $langYes</p>";
                }else{
                    $html_bookingTitle .= "<p class='text-center TextBold smallText simple-user-booking-event mt-1'>$langAcceptBooking: $langNo</p>";
                }
            }
        }else{
            $html_bookingTitle .= "<p class='text-center TextBold simple-user-booking-event smallText'>$langDoBooking<p>";
        }
    }else{
        if($hasExpired){
            $html_bookingTitle .= "<p class='text-center TextBold simple-user-booking-event smallText'>$langDisableBooking</p>";
        }else{
            $html_bookingTitle .= "<p class='text-center TextBold simple-user-booking-event smallText'>$langBookingIsDone</p>";
        }
        
    }
    

    return $html_bookingTitle;
}


function ColorExistBooking($bookingTutorStart,$bookingTutorEnd,$tutor_id){
    global $uid;

    $html_bookingExist = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingTutorEnd < $now){
        $hasExpired = true;
    }

    //check if other mentee has booking with this mentor before continue
    $checkBookingByOtherUserOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM date_booking
                                                                      WHERE id IN (SELECT booking_id FROM date_booking_user WHERE student_id NOT IN (?d))
                                                                      AND teacher_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$tutor_id,$bookingTutorStart,$bookingTutorEnd)->id;

    if($checkBookingByOtherUserOfGroup == 0 and !$hasExpired){
        $BookingByUser = Database::get()->queryArray("SELECT * FROM date_booking 
                                                        WHERE teacher_id = ?d
                                                        AND start = ?t 
                                                        AND end = ?t
                                                        AND id IN (SELECT booking_id FROM date_booking_user WHERE student_id = ?d)",$tutor_id,$bookingTutorStart,$bookingTutorEnd,$uid);

        if(count($BookingByUser) > 0){
            $html_bookingExist = '#1E7E0E';
            foreach($BookingByUser as $b){
                if($b->accepted == 1){
                    $html_bookingExist = '#FFC0CB';
                }else{
                    $html_bookingExist = '#1E7E0E';
                }
            }
        }else{
            $html_bookingExist = '#337ab7';
        }
    }else{
        if($hasExpired){
            $html_bookingExist = '#000000';
        }else{
            $html_bookingExist = '#ffa500';
        }
        
    }

    return $html_bookingExist;
}




