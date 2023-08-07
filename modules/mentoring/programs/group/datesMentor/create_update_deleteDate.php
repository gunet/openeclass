<?php         

$require_login = TRUE;


require_once '../../../../../include/baseTheme.php';   
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

//show all events
if(isset($_POST['action']) or isset($_GET['view'])) {

    if(isset($_GET['view']) and isset($_GET['show_m']) and isset($_GET['show_g'])){
        $mentoring_program_id = show_mentoring_program_id($mentoring_program_code);

        $group_id = $_GET['show_g'];
        $mentor_id = $_GET['show_m'];

        $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

        $eventArr = array();

        
        $result_events = Database::get()->queryArray("SELECT id,mentoring_program_id,user_id,group_id,start,end FROM mentoring_mentor_availability_group
                                                        WHERE start BETWEEN (?s) AND (?s)
                                                        AND group_id IN (SELECT group_id FROM mentoring_group_members 
                                                                            WHERE user_id = ?d AND is_tutor = ?d AND status_request = ?d)",$start,$end,$mentor_id,1,1);

    

        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'program' => $row->mentoring_program_id,
                    'title' => nameTutor($row->user_id,$row->mentoring_program_id,$row->group_id,$row->start,$row->end,$mentor_id,$group_id),
                    'start' => $row->start,
                    'end' => $row->end,
                    'group_id' => $row->group_id,
                    'user_id' => $row->user_id,
                    'className' => dontShowMentorIfNotTutorOfGroup($row->mentoring_program_id,$row->user_id,$mentor_id,$row->group_id,$group_id),
                    'backgroundColor' => getBackgroundEvent($row->mentoring_program_id,$row->group_id,$row->user_id,$row->start,$row->end,$mentor_id,$group_id)
                ];
            }
        }
        
        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();
        
    }

    // add new event section
    elseif($_POST['action'] == "add"){   

        $add = Database::get()->query("INSERT INTO mentoring_mentor_availability_group SET
                            mentoring_program_id = ?d,
                            user_id = ?d,
                            start = ?s,
                            end = ?s,
                            group_id = ?d",$_POST["program_id"], $_POST['user'], date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s',strtotime($_POST["end"])),$_POST['group_id']);
            
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
        $old_date = Database::get()->querySingle("SELECT *FROM mentoring_mentor_availability_group WHERE id = ?d",$_POST['id']);
        $old_mentor = $old_date->user_id;
        $old_group_id = $old_date->group_id;
        $old_program = $old_date->mentoring_program_id;
        $old_start = $old_date->start;
        $old_end = $old_date->end;

        //check if exist mentee who have made booking 
        $checkExistMentee = Database::get()->querySingle("SELECT COUNT(id) as c FROM mentoring_booking
                                                            WHERE mentoring_program_id = ?d
                                                            AND group_id = ?d
                                                            AND mentor_id = ?d
                                                            AND start = ?t
                                                            AND end = ?t",$old_program,$old_group_id,$old_mentor,$old_start,$old_end)->c;

        $is_editor_mentoring_program = access_update_delete_Date();

        if($checkExistMentee == 0 and ($old_mentor == $uid or $is_editor_mentoring_program or $is_admin)){
            if($_POST['user_id'] == $uid or $is_editor_mentoring_program or $is_admin){
                $update = Database::get()->query("UPDATE mentoring_mentor_availability_group SET start = ?s, end = ?s
                                                WHERE id = ?d
                                                AND mentoring_program_id = ?d 
                                                AND user_id = ?d
                                                AND group_id = ?d",date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s', strtotime($_POST["end"])), $_POST["id"], $_POST["program_id"], $_POST["user_id"],$_POST['group_id']);
                
                //Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_MEETING, MENTORING_LOG_MODIFY, array('tc_type' => '' ,'title' => $_POST["title"],'from' =>date('Y-m-d H:i:s', strtotime($_POST["start"])), 'until' => date('Y-m-d H:i:s',strtotime($_POST["end"]))));
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
        $old_date = Database::get()->querySingle("SELECT *FROM mentoring_mentor_availability_group WHERE id = ?d",$_POST['id']);
        if($old_date){
            $old_mentor = $old_date->user_id;
            $old_group_id = $old_date->group_id;
            $old_program = $old_date->mentoring_program_id;
            $old_start = $old_date->start;
            $old_end = $old_date->end;
        }
        

         //check if exist mentee who have made booking 
         $checkExistMentee = Database::get()->querySingle("SELECT COUNT(id) as c FROM mentoring_booking
                                                            WHERE mentoring_program_id = ?d
                                                            AND group_id = ?d
                                                            AND mentor_id = ?d
                                                            AND start = ?t
                                                            AND end = ?t",$old_program,$old_group_id,$old_mentor,$old_start,$old_end)->c;
        
        if($checkExistMentee == 0){
            $event_id = $_POST['id'];
            $check = Database::get()->querySingle("SELECT user_id FROM mentoring_mentor_availability_group WHERE id = ?d",$event_id)->user_id;

            $is_editor_mentoring_program = access_update_delete_Date();

            if($check == $uid or $is_editor_mentoring_program or $is_admin){
                //$del_record = Database::get()->queryArray("SELECT type_tc,title,start,end FROM mentoring_rentezvous WHERE id = ?d",$event_id);
                $del = Database::get()->query("DELETE FROM mentoring_mentor_availability_group WHERE id = ?d",$event_id);
                if($del){
                    // foreach($del_record as $d){
                    //     Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_MEETING, MENTORING_LOG_DELETE, array('tc_type' => $d->type_tc ,'title' => $d->title,'from' => $d->start, 'until' => $d->end)); 
                    // }
                    
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


function getBackgroundEvent($programId,$group,$userId,$start,$end,$mentor_id,$group_id){
    global $mentoring_program_id, $uid, $is_editor_mentoring_program, $is_admin;
  
    $color = '';

    if($mentoring_program_id == $programId && $group_id == $group){
        if($mentor_id == $userId){// afora ton current upeuthino gia prasino xrwma kai roz xrwma an uparxei booking apo mentee
            //if exist booking
            $existBooking = Database::get()->querySingle("SELECT COUNT(id) as c FROM mentoring_booking
                                                            WHERE mentoring_program_id = ?d
                                                            AND group_id = ?d
                                                            AND mentor_id = ?d
                                                            AND start = ?t
                                                            AND end = ?t",$programId,$group,$userId,$start,$end)->c;
            if($existBooking > 0){
                $color = '#FFC0CB';
            }else{
                $color .= '#50C878';
            }
        }else{//afora allon upeuthino sthn idia omada gia portokali xrwma
            $color = '#f0ad4e';
        }
    }elseif($mentoring_program_id == $programId && $group_id != $group){
        if($mentor_id == $userId){// afora ton current upeuthino gia allh omada sto idio programma ara me galazio xrwma
            $color = '#23e1eb';
        }
    }elseif($mentoring_program_id != $programId){
        if($mentor_id == $userId){// afora to kokkino xrwma gia ton current upeuthino
            $color = '#d9534f';
        }
    }
    

    return $color;

}

function nameTutor($userId,$programId,$group,$start,$end,$mentor_id,$group_id){
    global $mentoring_program_id, $langProgram, $langBooking, $langGroup, $langName;

    $name = "";
    $MentorGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$userId)->givenname;
    $MentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$userId)->surname;

    $program = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE id = ?d",$programId)->title;
    
    $group_name = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE id = ?d",$group)->name;

    $booking = Database::get()->querySingle("SELECT *FROM mentoring_booking
                                                    WHERE mentoring_program_id = ?d AND group_id = ?d AND mentor_id = ?d
                                                    AND start = ?t AND end = ?t",$programId,$group,$userId,$start,$end);

    $name .= "<div class='col-12 bg-white p-2 mt-2 mb-2'>";
    if($booking){
        if($mentoring_program_id == $programId && $group_id == $group && $mentor_id == $userId){
            $name .= "
                        <div class='col-12 mb-3'>
                            <p class='control-label-notes mb-1'>$langName</p>
                            <p class='help-block'>$MentorGivenname $MentorSurname</p>
                        </div>

                        <div class='col-12 mb-3'>
                            <p class='control-label-notes mb-1'>$langGroup</p>
                            <p class='help-block'>$group_name</p>
                        </div>

                        <div class='col-12 mb-3'>
                            <p class='control-label-notes mb-1'>$langBooking</p>
                            <p class='help-block'>$booking->title</p>
                        </div>
                      ";
        }else{
            $name .= "
                        <div class='col-12 mb-3'>
                            <p class='control-label-notes mb-1'>$langName</p>
                            <p class='help-block'>$MentorGivenname $MentorSurname</p>
                        </div>

                        <div class='col-12 mb-3'>
                            <p class='control-label-notes mb-1'>$langGroup</p>
                            <p class='help-block'>$group_name</p>
                        </div>
                    ";

            if($mentoring_program_id != $programId){
                $name .= "
                            <div class='col-12 mb-3'>
                                <p class='control-label-notes mb-1'>$langProgram</p>
                                <p class='help-block'>$program</p>
                            </div>
                           ";
            }
        }
    }else{
        $name .= "
                    <div class='col-12 mb-3'>
                        <p class='control-label-notes mb-1'>$langName</p>
                        <p class='help-block'>$MentorGivenname $MentorSurname</p>
                    </div>

                    <div class='col-12 mb-3'>
                        <p class='control-label-notes mb-1'>$langGroup</p>
                        <p class='help-block'>$group_name</p>
                    </div>
                    ";

        if($mentoring_program_id != $programId){
            $name .= "  <div class='col-12 mb-3'>
                            <p class='control-label-notes mb-1'>$langProgram</p>
                            <p class='help-block'>$program</p>
                        </div>
                        ";
        }
    }                                             
    
    $name .= "</div>";
    return $name;
}

function dontShowMentorIfNotTutorOfGroup($programId,$userId,$mentor_id,$group,$group_id){
    global $mentoring_program_id, $uid, $is_editor_mentoring_program, $is_admin;
    $html = "";

    // in current group then
    if($mentoring_program_id == $programId && $group_id == $group){
        if($mentor_id == $userId){// afora ton current upeuthino gia prasino xrwma
            $html .= 'd-block';
        }else{//afora allon upeuthino sthn idia omada gia portokali xrwma
            $html .= 'd-block';
        }
    }elseif($mentoring_program_id == $programId && $group_id != $group){
        if($mentor_id == $userId){// afora ton current upeuthino gia allh omada sto ido programma ara me galazio xrwma
            $html .= 'd-block';
        }else{
            $html .= 'd-none';
        }
    }elseif($mentoring_program_id != $programId){
        if($mentor_id == $userId){// afora to kokkino xrwma gia ton current upeuthino
            $html .= 'd-block';
        }else{
            $html .= 'd-none';
        }
    }

    return $html;
}



function access_update_delete_Date(){
    //an uid einai suntonisths
    global $mentoring_program_id,$uid, $is_admin;

    $check_2 = Database::get()->queryArray("SELECT *FROM mentoring_programs_user
                                            WHERE mentoring_program_id = ?d
                                            AND user_id = ?d
                                            AND tutor = ?d",$mentoring_program_id,$uid,1);

    $is_editor_mentoring_program = false;
    if(count($check_2) > 0 or $is_admin){
        $is_editor_mentoring_program = true;
    }

    return $is_editor_mentoring_program;
}