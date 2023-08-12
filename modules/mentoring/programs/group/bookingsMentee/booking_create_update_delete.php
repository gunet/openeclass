<?php         

$require_login = TRUE;


require_once '../../../../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

//show all events
if(isset($_POST['action']) or isset($_GET['view'])) {

    if(isset($_GET['view']) and isset($_GET['show_mentor']) and isset($_GET['show_group'])){
        $mentoring_program_id = show_mentoring_program_id($mentoring_program_code);

        $group_id = getDirectReference($_GET['show_group']);
        $mentor_id = getDirectReference($_GET['show_mentor']);

        $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

        $eventArr = array();

        $result_events = Database::get()->queryArray("SELECT id,user_id,group_id,start,end FROM mentoring_mentor_availability_group
                                                        WHERE start BETWEEN (?s) AND (?s)
                                                        AND user_id = ?d
                                                        AND group_id = ?d",$start,$end,$mentor_id,$group_id);

        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'mentor' => $row->user_id,
                    'group' => $row->group_id,
                    'title' => TitleBooking($row->start,$row->end,$row->user_id,$row->group_id),
                    'start' => $row->start,
                    'end' => $row->end,
                    'className' => classNameBooking($row->start,$row->end,$row->user_id,$row->group_id),
                    'backgroundColor' => ColorExistBooking($row->start,$row->end,$row->user_id,$row->group_id)
                ];
            }
        }
        
        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();
       

    }

    // add new event section
    elseif($_POST['action'] == "add"){   

        //Before add booking, check if mentor has deleted the current date for booking
        $checkDateMentorExist = Database::get()->querySingle("SELECT COUNT(id) as c FROM mentoring_mentor_availability_group
                                                                WHERE user_id = ?d
                                                                AND group_id = ?d
                                                                AND start = ?t
                                                                AND end = ?t
                                                                AND mentoring_program_id = ?d",$_POST['mentor_id'],$_POST['group_id'],$_POST["start"],$_POST["end"],$_POST['program_id'])->c;

        if($checkDateMentorExist > 0){

            //check if other mentee has made booking before continue
            $checkOtherMenteeBooking = Database::get()->querySingle("SELECT COUNT(id) as c FROM mentoring_booking
                                                                        WHERE mentoring_program_id = ?d
                                                                        AND group_id = ?d
                                                                        AND mentor_id = ?d
                                                                        AND start = ?t
                                                                        AND end = ?t",$_POST['program_id'],$_POST['group_id'],$_POST['mentor_id'],$_POST["start"],$_POST["end"])->c;


            if($checkOtherMenteeBooking == 0){

                $add = Database::get()->query("INSERT INTO mentoring_booking SET
                                    mentoring_program_id = ?d,
                                    group_id = ?d,
                                    mentor_id = ?d,
                                    title = ?s,
                                    start = ?s,
                                    end = ?s",$_POST["program_id"], $_POST['group_id'], $_POST["mentor_id"],$_POST['title'],date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s',strtotime($_POST["end"])));
            

                
                $add_bookind_by_mentee = Database::get()->query("INSERT INTO mentoring_booking_user SET
                                                mentoring_booking_id = ?d,
                                                mentee_id = ?d",$add->lastInsertID,$uid);
                    
                    
                //send email to mentor about the booking from mentee
                $userName = $_POST['title'];
                $mentorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$_POST["mentor_id"])->givenname;
                $mentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$_POST["mentor_id"])->surname;
                $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$_POST["mentor_id"])->email;
                $dateFrom = $_POST["start"];
                $dateEnd = $_POST["end"];

                $emailHeader = "
                <!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langAddBookingByMentee</div>
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

                $emailsubject = 'Mentoring:'.$langAddBookingByMentee;

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
        $mentor_availabity_group_id = Database::get()->queryArray("SELECT *FROM mentoring_mentor_availability_group WHERE id = ?d",$event_id);

        $mentor_user = '';
        $mentor_group = '';
        $mentor_start = '';
        $mentor_end = '';
        if(count($mentor_availabity_group_id)){
            foreach($mentor_availabity_group_id as $m){
                $mentor_user = $m->user_id;
                $mentor_group = $m->group_id;
                $mentor_start = $m->start;
                $mentor_end = $m->end;
            }

            $bookingId = Database::get()->querySingle("SELECT id FROM mentoring_booking
                                                        WHERE mentoring_program_id = ?d
                                                        AND group_id = ?d
                                                        AND mentor_id = ?d
                                                        AND start = ?t
                                                        AND end = ?t",$mentoring_program_id,$mentor_group,$mentor_user,$mentor_start,$mentor_end)->id;
            
            //send email to mentor about the booking from mentee about cancel booking
            $userName = Database::get()->querySingle("SELECT title FROM mentoring_booking WHERE id = ?d",$bookingId)->title;
            $mentorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id IN (SELECT mentor_id FROM mentoring_booking WHERE id = ?d)",$bookingId)->givenname;
            $mentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id IN (SELECT mentor_id FROM mentoring_booking WHERE id = ?d)",$bookingId)->surname;
            $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT mentor_id FROM mentoring_booking WHERE id = ?d)",$bookingId)->email;
            $dateFrom = Database::get()->querySingle("SELECT start FROM mentoring_booking WHERE id = ?d",$bookingId)->start;
            $dateEnd = Database::get()->querySingle("SELECT end FROM mentoring_booking WHERE id = ?d",$bookingId)->end;

            $del = Database::get()->query("DELETE FROM mentoring_booking 
                                            WHERE mentoring_program_id = ?d
                                            AND group_id = ?d
                                            AND mentor_id = ?d
                                            AND start = ?t
                                            AND end = ?t",$mentoring_program_id,$mentor_group,$mentor_user,$mentor_start,$mentor_end);

            if($del){

                $emailHeader = "
                <!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langDeleteBookingByMentee</div>
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

                $emailsubject = 'Mentoring:'.$langDeleteBookingByMentee.'--'.$userName;

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


function classNameBooking($bookingMentorStart,$bookingMentorEnd,$mentor_id,$group_id){
    global $uid, $mentoring_program_id;

    $html_bookingClassName = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingMentorEnd < $now){
        $hasExpired = true;
    }

    //check if other mentee has booking with this mentor before continue
    $checkBookingByOtherMenteeOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM mentoring_booking
                                                                      WHERE id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id NOT IN (?d))
                                                                      AND mentoring_program_id = ?d AND group_id = ?d
                                                                      AND mentor_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$mentoring_program_id,$group_id,$mentor_id,$bookingMentorStart,$bookingMentorEnd)->id;

    if($checkBookingByOtherMenteeOfGroup == 0 and !$hasExpired){
        $BookingByMentee = Database::get()->queryArray("SELECT *FROM mentoring_booking 
                                                    WHERE mentoring_program_id = ?d 
                                                    AND group_id = ?d 
                                                    AND mentor_id = ?d
                                                    AND start = ?t 
                                                    AND end = ?t
                                                    AND id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id = ?d)",$mentoring_program_id,$group_id,$mentor_id,$bookingMentorStart,$bookingMentorEnd,$uid);

        if(count($BookingByMentee) > 0){
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

function TitleBooking($bookingMentorStart,$bookingMentorEnd,$mentor_id,$group_id){
    global $uid, $mentoring_program_id ,$langHaveDoneBooking, $langDoBooking, $langDisableBooking, $langBookingIsDone, $langAcceptBooking;

    $html_bookingTitle = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingMentorEnd < $now){
        $hasExpired = true;
    }

    //check if other mentee has booking with this mentor before continue
    $checkBookingByOtherMenteeOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM mentoring_booking
                                                                      WHERE id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id NOT IN (?d))
                                                                      AND mentoring_program_id = ?d AND group_id = ?d
                                                                      AND mentor_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$mentoring_program_id,$group_id,$mentor_id,$bookingMentorStart,$bookingMentorEnd)->id;

    if($checkBookingByOtherMenteeOfGroup == 0 and !$hasExpired){
        $BookingByMentee = Database::get()->queryArray("SELECT *FROM mentoring_booking 
                                                    WHERE mentoring_program_id = ?d 
                                                    AND group_id = ?d 
                                                    AND mentor_id = ?d
                                                    AND start = ?t 
                                                    AND end = ?t
                                                    AND id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id = ?d)",$mentoring_program_id,$group_id,$mentor_id,$bookingMentorStart,$bookingMentorEnd,$uid);

        if(count($BookingByMentee) > 0){
            $html_bookingTitle .= "<p class='text-center TextBold smallText'>$langHaveDoneBooking</p>";
            foreach($BookingByMentee as $b){
                if($b->accepted == 1){
                    $html_bookingTitle .= "<p class='text-center TextBold smallText mt-1'>$langAcceptBooking: <span class='fa fa-check text-white TextBold'></span></p>";
                }else{
                    $html_bookingTitle .= "<p class='text-center TextBold smallText mt-1'>$langAcceptBooking: <span class='fa-solid fa-trash-can text-danger TextBold'></span></p>";
                }
            }
        }else{
            $html_bookingTitle .= "<p class='text-center TextBold smallText'>$langDoBooking<p>";
        }
    }else{
        if($hasExpired){
            $html_bookingTitle .= "<p class='text-center TextBold smallText'>$langDisableBooking</p>";
        }else{
            $html_bookingTitle .= "<p class='text-center TextBold smallText'>$langBookingIsDone</p>";
        }
        
    }
    

    return $html_bookingTitle;
}


function ColorExistBooking($bookingMentorStart,$bookingMentorEnd,$mentor_id,$group_id){
    global $uid, $mentoring_program_id;

    $html_bookingExist = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingMentorEnd < $now){
        $hasExpired = true;
    }

    //check if other mentee has booking with this mentor before continue
    $checkBookingByOtherMenteeOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM mentoring_booking
                                                                      WHERE id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id NOT IN (?d))
                                                                      AND mentoring_program_id = ?d AND group_id = ?d
                                                                      AND mentor_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$mentoring_program_id,$group_id,$mentor_id,$bookingMentorStart,$bookingMentorEnd)->id;

    if($checkBookingByOtherMenteeOfGroup == 0 and !$hasExpired){
        $BookingByMentee = Database::get()->queryArray("SELECT *FROM mentoring_booking 
                                                        WHERE mentoring_program_id = ?d 
                                                        AND group_id = ?d 
                                                        AND mentor_id = ?d
                                                        AND start = ?t 
                                                        AND end = ?t
                                                        AND id IN (SELECT mentoring_booking_id FROM mentoring_booking_user WHERE mentee_id = ?d)",$mentoring_program_id,$group_id,$mentor_id,$bookingMentorStart,$bookingMentorEnd,$uid);

        if(count($BookingByMentee) > 0){
            $html_bookingExist = '#82AC64';
            foreach($BookingByMentee as $b){
                if($b->accepted == 1){
                    $html_bookingExist = '#FFC0CB';
                }else{
                    $html_bookingExist = '#82AC64';
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




