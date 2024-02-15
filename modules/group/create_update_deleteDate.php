<?php         

/**
 *
 * @file date_available.php
 * @brief Display user available date
 */
$require_login = true;
$require_current_course = true;
$require_user_registration = TRUE;
$require_help = true;
$helpTopic = 'available_dates';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';

//show all events
if(isset($_POST['action']) or isset($_GET['view'])) {

    if(isset($_GET['view']) and isset($_GET['show_m']) and isset($_GET['show_g'])){

        $lesson_id = $_GET['show_l'];
        $group_id = $_GET['show_g'];
        $tutor_id = $_GET['show_m'];

        $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

        $eventArr = array();

        
        $result_events = Database::get()->queryArray("SELECT id,lesson_id,user_id,group_id,start,end FROM tutor_availability_group
                                                        WHERE start BETWEEN (?s) AND (?s)
                                                        AND group_id IN (SELECT group_id FROM group_members 
                                                                            WHERE user_id = ?d AND is_tutor = ?d)",$start,$end,$tutor_id,1);

    

        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'idCourse' => $row->lesson_id,
                    'title' => nameTutor($row->user_id,$row->lesson_id,$row->group_id,$row->start,$row->end,$tutor_id,$group_id),
                    'start' => $row->start,
                    'end' => $row->end,
                    'group_id' => $row->group_id,
                    'user_id' => $row->user_id,
                    'className' => dontShowTutorIfNotTutorOfGroup($row->lesson_id,$row->user_id,$tutor_id,$row->group_id,$group_id),
                    'backgroundColor' => getBackgroundEvent($row->lesson_id,$row->group_id,$row->user_id,$row->start,$row->end,$tutor_id,$group_id)
                ];
            }
        }
        
        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();
        
    }

    // add new event section
    elseif($_POST['action'] == "add"){   

        $add = Database::get()->query("INSERT INTO tutor_availability_group SET
                            lesson_id = ?d,
                            user_id = ?d,
                            start = ?s,
                            end = ?s,
                            group_id = ?d",$_POST["idCourse"], $_POST['user'], date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s',strtotime($_POST["end"])),$_POST['group_id']);
            
            if($add){
                //Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_MEETING, MENTORING_LOG_INSERT, array('tc_type' => $tc_type ,'title' => $_POST["title"],'from' =>date('Y-m-d H:i:s', strtotime($_POST["start"])), 'until' => date('Y-m-d H:i:s',strtotime($_POST["end"]))));
                echo 1;
            }else{
                 echo 0;
            }

        exit();

    }

    //update event
    elseif($_POST['action'] == "update"){

        //get old date mentor before change it
        $old_date = Database::get()->querySingle("SELECT * FROM tutor_availability_group WHERE id = ?d",$_POST['id']);
        $old_tutor = $old_date->user_id;
        $old_group_id = $old_date->group_id;
        $old_course = $old_date->lesson_id;
        $old_start = $old_date->start;
        $old_end = $old_date->end;

        //check if exist mentee who have made booking 
        $checkExistMentee = Database::get()->querySingle("SELECT COUNT(id) as c FROM booking
                                                            WHERE lesson_id = ?d
                                                            AND group_id = ?d
                                                            AND tutor_id = ?d
                                                            AND start = ?t
                                                            AND end = ?t",$old_course,$old_group_id,$old_tutor,$old_start,$old_end)->c;

        $is_editor_course = access_update_delete_Date();

        if($checkExistMentee == 0 and (($old_tutor == $uid && $is_editor_course) or $is_course_admin)){
            if($_POST['user_id'] == $uid or $is_editor_course or $is_course_admin){
                $update = Database::get()->query("UPDATE tutor_availability_group SET start = ?s, end = ?s
                                                WHERE id = ?d
                                                AND lesson_id = ?d 
                                                AND user_id = ?d
                                                AND group_id = ?d",date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s', strtotime($_POST["end"])), $_POST["id"], $_POST["idCourse"], $_POST["user_id"],$_POST['group_id']);
                
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

        //get old date mentor before change it
        $old_date = Database::get()->querySingle("SELECT * FROM tutor_availability_group WHERE id = ?d",$_POST['id']);
        if($old_date){
            $old_tutor = $old_date->user_id;
            $old_group_id = $old_date->group_id;
            $old_course = $old_date->mentoring_program_id;
            $old_start = $old_date->start;
            $old_end = $old_date->end;
        }
        

         //check if exist mentee who have made booking 
         $checkExistMentee = Database::get()->querySingle("SELECT COUNT(id) as c FROM booking
                                                            WHERE lesson_id = ?d
                                                            AND group_id = ?d
                                                            AND tutor_id = ?d
                                                            AND start = ?t
                                                            AND end = ?t",$old_course,$old_group_id,$old_tutor,$old_start,$old_end)->c;
        
        if($checkExistMentee == 0){
            $event_id = $_POST['id'];
            $check = Database::get()->querySingle("SELECT user_id FROM tutor_availability_group WHERE id = ?d",$event_id)->user_id;

            $is_editor_course = access_update_delete_Date();

            if(($check == $uid && $is_editor_course) or $is_course_admin){
                $del = Database::get()->query("DELETE FROM tutor_availability_group WHERE id = ?d",$event_id);
                if($del){
                    echo 1; 
                }
            }else{
                echo 0;
            }
        }else{
            echo 2;
        }
        
        exit();

    }

}


function getBackgroundEvent($lessonId,$group,$userId,$start,$end,$tutor_id,$group_id){
    global $course_id, $uid, $is_editor_course, $is_course_admin;
  
    $color = '';

    if($course_id == $lessonId && $group_id == $group){
        if($tutor_id == $userId){// afora ton current upeuthino gia prasino xrwma kai roz xrwma an uparxei booking apo mentee
            //if exist booking
            $existBooking = Database::get()->querySingle("SELECT COUNT(id) as c FROM booking
                                                            WHERE lesson_id = ?d
                                                            AND group_id = ?d
                                                            AND tutor_id = ?d
                                                            AND start = ?t
                                                            AND end = ?t",$lessonId,$group,$userId,$start,$end)->c;
            if($existBooking > 0){
                $color = '#FFC0CB';
            }else{
                $color .= '#50C878';
            }
        }else{//afora allon upeuthino sthn idia omada gia portokali xrwma
            $color = '#f0ad4e';
        }
    }elseif($course_id == $lessonId && $group_id != $group){
        if($tutor_id == $userId){// afora ton current upeuthino gia allh omada sto idio mathima ara me galazio xrwma
            $color = '#23e1eb';
        }
    }elseif($course_id != $lessonId){
        if($tutor_id == $userId){// afora to kokkino xrwma gia ton current upeuthino
            $color = '#d9534f';
        }
    }
    

    return $color;

}

function nameTutor($userId,$lessonId,$group,$start,$end,$tutor_id,$group_id){
    global $course_id, $langCourse, $langBooking, $langGroup, $langName;

    $name = "";
    $TutorGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$userId)->givenname;
    $TutorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$userId)->surname;

    $course_title = Database::get()->querySingle("SELECT title FROM course WHERE id = ?d",$lessonId)->title;
    
    $group_Name = Database::get()->querySingle("SELECT name FROM group WHERE id = ?d",$group)->name;
    

    $booking = Database::get()->querySingle("SELECT * FROM booking
                                                    WHERE lesson_id = ?d AND group_id = ?d AND tutor_id = ?d
                                                    AND start = ?t AND end = ?t",$lessonId,$group,$userId,$start,$end);

    $name .= "<div class='col-12 bg-white p-2 mt-2 mb-2'>";
    if($booking){
        if($course_id == $lessonId && $group_id == $group && $tutor_id == $userId){
            $name .= "
                        <div class='col-12 mb-3'>
                            <p class='control-label-notes text-decoration-underline mb-0'>$langName</p>
                            <p class='help-block mb-0'>$TutorGivenname $TutorSurname</p>
                        </div>

                        <div class='col-12 mb-3'>
                            <p class='control-label-notes text-decoration-underline mb-0'>$langGroup</p>
                            <p class='help-block mb-0'>$group_Name</p>
                        </div>

                        <div class='col-12 mb-3'>
                            <p class='control-label-notes text-decoration-underline mb-0'>$langBooking</p>
                            <p class='help-block mb-0'>$booking->title</p>
                        </div>
                      ";
        }else{
            $name .= "
                        <div class='col-12 mb-3'>
                            <p class='control-label-notes text-decoration-underline mb-0'>$langName</p>
                            <p class='help-block mb-0'>$TutorGivenname $TutorSurname</p>
                        </div>

                        <div class='col-12 mb-3'>
                            <p class='control-label-notes text-decoration-underline mb-0'>$langGroup</p>
                            <p class='help-block mb-0'>$group_Name</p>
                        </div>
                    ";

            if($course_id != $lessonId){
                $name .= "
                            <div class='col-12 mb-3'>
                                <p class='control-label-notes text-decoration-underline mb-0'>$langCourse</p>
                                <p class='help-block mb-0'>$course_title</p>
                            </div>
                           ";
            }
        }
    }else{
        $name .= "
                    <div class='col-12 mb-3'>
                        <p class='control-label-notes text-decoration-underline mb-0'>$langName</p>
                        <p class='help-block mb-0'>$TutorGivenname $TutorSurname</p>
                    </div>

                    <div class='col-12 mb-3'>
                        <p class='control-label-notes text-decoration-underline mb-0'>$langGroup</p>
                        <p class='help-block mb-0'>$group_Name</p>
                    </div>
                    ";

        if($course_id != $lessonId){
            $name .= "  <div class='col-12 mb-3'>
                            <p class='control-label-notes text-decoration-underline mb-0'>$langCourse</p>
                            <p class='help-block mb-0'>$course_title</p>
                        </div>
                        ";
        }
    }                                             
    
    $name .= "</div>";
    return $name;
}

function dontShowTutorIfNotTutorOfGroup($lessonId,$userId,$tutor_id,$group,$group_id){
    global $course_id, $uid, $is_editor_course, $is_course_admin;
    $html = "";

    // in current group then
    if($course_id == $lessonId && $group_id == $group){
        if($tutor_id == $userId){// afora ton current upeuthino gia prasino xrwma
            $html .= 'd-block';
        }else{//afora allon upeuthino sthn idia omada gia portokali xrwma
            $html .= 'd-block';
        }
    }elseif($course_id == $lessonId && $group_id != $group){
        if($tutor_id == $userId){// afora ton current upeuthino gia allh omada sto idio mathima ara me galazio xrwma
            $html .= 'd-block';
        }else{
            $html .= 'd-none';
        }
    }elseif($course_id != $lessonId){
        if($tutor_id == $userId){// afora to kokkino xrwma gia ton current upeuthino
            $html .= 'd-block';
        }else{
            $html .= 'd-none';
        }
    }

    return $html;
}



function access_update_delete_Date(){
    //an uid einai ekpaideuths-diaxeirisths
    global $course_id, $uid, $is_course_admin;

    $check_2 = Database::get()->queryArray("SELECT * FROM course_user
                                            WHERE course_id = ?d
                                            AND user_id = ?d
                                            AND editor = ?d",$course_id,$uid,1);

    $is_editor_course = false;
    if(count($check_2) > 0 or $is_course_admin){
        $is_editor_course = true;
    }

    return $is_editor_course;
}