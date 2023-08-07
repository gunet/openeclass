<?php         

$require_login = TRUE;


require_once '../../../include/baseTheme.php';   
require_once 'include/sendMail.inc.php';
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

        $result_events = Database::get()->queryArray("SELECT id,mentoring_program_id,mentor_id,title,start,end,group_id FROM mentoring_rentezvous
                                                        WHERE start BETWEEN (?s) AND (?s)
                                                        AND group_id IN (SELECT group_id FROM mentoring_group_members 
                                                                            WHERE user_id = ?d AND is_tutor = ?d AND status_request = ?d)",$start,$end,$mentor_id,1,1);

        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'program' => $row->mentoring_program_id,
                    'title' => nameMeeting($row->id,$row->title,$row->mentoring_program_id,$row->group_id,$row->mentor_id,$mentor_id,$group_id),
                    'start' => $row->start,
                    'end' => $row->end,
                    'group_id' => $row->group_id,
                    'user_id' => $row->mentor_id,
                    'className' => dontShowMentorIfNotTutorOfGroup($row->mentoring_program_id,$row->group_id,$row->mentor_id,$mentor_id,$group_id),
                    'backgroundColor' => getBackgroundEvent($row->mentoring_program_id,$row->group_id,$row->start,$row->end,$row->mentor_id,$mentor_id,$group_id)
                ];
            }
        }
        
        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();
        
    }

    // add new event section
    elseif($_POST['action'] == "add"){   

        if($_POST['tc_type_id'] == 1){
            $tc_type = 'googlemeet';
        }elseif($_POST['tc_type_id'] == 2){
            $tc_type = 'zoommeet';
        }elseif($_POST['tc_type_id'] == 3){
            $tc_type = 'skypemeet';
        }else{
            $tc_type = '';
        }
      
        $members_array = explode(",",$_POST['members_box']);

        if(count($members_array) > 0){
            
            $add = Database::get()->query("INSERT INTO mentoring_rentezvous SET
                                    mentoring_program_id = ?d,
                                    mentor_id = ?d,
                                    title = ?s,
                                    start = ?s,
                                    end = ?s,
                                    group_id = ?d,
                                    type_tc = ?s,
                                    api_url = ?s,
                                    meeting_id = ?s,
                                    passcode = ?s",$_POST["program_id"], $_POST['user'], $_POST["title"], date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s',strtotime($_POST["end"])),$_POST['group_id'],$tc_type,$_POST['url'],$_POST['meeting_id'],$_POST['passcode_id']);
            
            if($add){
                foreach($members_array as $member){
                    $add_mentee = Database::get()->query("INSERT INTO mentoring_rentezvous_user SET
                                                   mentoring_rentezvous_id = ?d,
                                                   mentee_id = ?d",$add->lastInsertID,$member);
                }

               

                if($add and $add_mentee){

                    Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_MEETING, MENTORING_LOG_INSERT, array('tc_type' => $tc_type ,'title' => $_POST["title"],'from' =>date('Y-m-d H:i:s', strtotime($_POST["start"])), 'until' => date('Y-m-d H:i:s',strtotime($_POST["end"]))));
                    echo 1;

                    //Send email to all participants about the meeting
                    $titleMeeting = Database::get()->querySingle("SELECT title FROM mentoring_rentezvous WHERE id = ?d",$add->lastInsertID)->title;
                    $typeMeeting = Database::get()->querySingle("SELECT type_tc FROM mentoring_rentezvous WHERE id = ?d",$add->lastInsertID)->type_tc;
                    $urlMeeting = Database::get()->querySingle("SELECT api_url FROM mentoring_rentezvous WHERE id = ?d",$add->lastInsertID)->api_url;
                    $urlMeetingId = Database::get()->querySingle("SELECT meeting_id FROM mentoring_rentezvous WHERE id = ?d",$add->lastInsertID)->meeting_id;
                    $urlMeetingPassCode = Database::get()->querySingle("SELECT passcode FROM mentoring_rentezvous WHERE id = ?d",$add->lastInsertID)->passcode;
                    $urlMeetingDateS = Database::get()->querySingle("SELECT start FROM mentoring_rentezvous WHERE id = ?d",$add->lastInsertID)->start;
                    $urlMeetingDate = date('d-m-Y H:i', strtotime($urlMeetingDateS));
                    $mentorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id IN (SELECT mentor_id FROM mentoring_rentezvous WHERE id = ?d)",$add->lastInsertID)->givenname;
                    $mentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id IN (SELECT mentor_id FROM mentoring_rentezvous WHERE id = ?d)",$add->lastInsertID)->surname;
                    
                    foreach($members_array as $member){

                        $userEmail = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$member)->email;

                        $emailHeader = "
                        <!-- Header Section -->
                                <div id='mail-header'>
                                    <br>
                                    <div>
                                        <div id='header-title'>$langAvailableMeeting</div>
                                    </div>
                                </div>";

                        $emailMain = "
                        <!-- Body Section -->
                            <div id='mail-body'>
                                <br>
                                <div>$langDetailsMeeting</div>
                                <div id='mail-body-inner'>
                                    <ul id='forum-category'>
                                        <li><span><b>$langWithTutorOfGroup: </b></span> <span>$mentorName $mentorSurname</span></li>
                                        <li><span><b>$typeMeetings: </b></span> <span>$typeMeeting</span></li>
                                        <li><span><b>URL: </b></span> <span>$urlMeeting</span></li>
                                        <li><span><b>Meeting id: </b></span> <span>$urlMeetingId</span></li>
                                        <li><span><b>$langPassword: </b></span> <span>$urlMeetingPassCode</span></li>
                                        <li><span><b>$langStartDate: </b></span> <span>$urlMeetingDate</span></li>
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

                        $emailsubject = 'Mentoring:'.$langAvailableMeeting;

                        $emailbody = $emailHeader.$emailMain;

                        $emailPlainBody = html2text($emailbody);
                        
                        send_mail_multipart('', '', '', $userEmail, $emailsubject, $emailPlainBody, $emailbody);
                    }


                }
            }
            
        }else{
            echo 0;
        }
        
        exit();

    }

    //update event
    elseif($_POST['action'] == "update"){

        $is_editor_mentoring_program = access_update_delete_meeting();

        if($_POST['user_id'] == $uid or $is_editor_mentoring_program or $is_admin){
            $update = Database::get()->query("UPDATE mentoring_rentezvous SET start = ?s, end = ?s
                                            WHERE id = ?d
                                            AND mentoring_program_id = ?d 
                                            AND mentor_id = ?d
                                            AND group_id = ?d",date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s', strtotime($_POST["end"])), $_POST["id"], $_POST["program_id"], $_POST["user_id"],$_POST['group_id']);

            if($update){
                Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_MEETING, MENTORING_LOG_MODIFY, array('tc_type' => '' ,'title' => $_POST["title"],'from' =>date('Y-m-d H:i:s', strtotime($_POST["start"])), 'until' => date('Y-m-d H:i:s',strtotime($_POST["end"]))));
                echo 1; 
            }
        }else{
            echo 0;
        }
       
        exit();

    }

    // remove event
    elseif($_POST['action'] == "delete"){
        
        $event_id = $_POST['id'];
        $check = Database::get()->querySingle("SELECT mentor_id FROM mentoring_rentezvous WHERE id = ?d",$event_id)->mentor_id;

        $is_editor_mentoring_program = access_update_delete_meeting();

        if($check == $uid or $is_editor_mentoring_program or $is_admin){
            $del_record = Database::get()->queryArray("SELECT type_tc,title,start,end FROM mentoring_rentezvous WHERE id = ?d",$event_id);
            $del = Database::get()->query("DELETE FROM mentoring_rentezvous WHERE id = ?d",$event_id);
            if($del){
                foreach($del_record as $d){
                    Mentoring_Log::record($mentoring_program_id, MENTORING_MODULE_ID_MEETING, MENTORING_LOG_DELETE, array('tc_type' => $d->type_tc ,'title' => $d->title,'from' => $d->start, 'until' => $d->end)); 
                }
                
                echo 1; 
            }
        }else{
            echo 0;
        }
        
        exit();

    }

}


function getBackgroundEvent($programId,$group,$start,$end,$userId,$mentor_id,$group_id){
    global $mentoring_program_id;

    $color = "";

    if($mentoring_program_id == $programId && $group_id == $group){
        if($mentor_id == $userId){// afora ton current upeuthino gia prasino xrwma
            $color .= '#50C878';
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



function access_update_delete_meeting(){
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


function dontShowMentorIfNotTutorOfGroup($programId,$group,$userId,$mentor_id,$group_id){
    global $uid, $is_editor_mentoring_program, $is_admin, $mentoring_program_id;
    $html = "";

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


function nameMeeting($meetingId,$title,$programId,$group,$userId,$mentor_id,$group_id){
    global $mentoring_program_id, $langTitle, $langParticipants,$langName,$langGroup,$langProgram;

    $name = "";

    $MentorGivenname = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$userId)->givenname;
    $MentorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$userId)->surname;

    $groupName = Database::get()->querySingle("SELECT name FROM mentoring_group WHERE id = ?d",$group)->name;

    if($programId != $mentoring_program_id){
        $programName = Database::get()->querySingle("SELECT title FROM mentoring_programs WHERE id = ?d",$programId)->title;
    }

    //$name = $MentorGivenname.''.$MentorSurname.'--'.$title;

    $participants = Database::get()->queryArray("SELECT givenname,surname FROM user
                                                WHERE id IN (SELECT mentee_id FROM mentoring_rentezvous_user WHERE mentoring_rentezvous_id = ?d)",$meetingId);

    $name .= "<div class='col-12 bg-white p-2 mt-2 mb-2'>
                <div class='col-12 mb-3'>
                    <p class='control-label-notes mb-1'>$langName</p>
                    <p class='help-block'>$MentorGivenname&nbsp$MentorSurname</p>
                </div>";

                if($programId != $mentoring_program_id){
                    $name .= "<div class='col-12 mb-3'>
                                    <p class='control-label-notes mb-1'>$langProgram</p>
                                    <p class='help-block'>$programName</p>
                                </div>";
                }

    $name .= "
                <div class='col-12 mb-3'>
                    <p class='control-label-notes mb-1'>$langGroup</p>
                    <p class='help-block'>$groupName</p>
                </div>

                <div class='col-12 mb-3'>
                    <p class='control-label-notes mb-1'>$langTitle</p>
                    <p class='help-block'>$title</p>
                </div>

              <div class='col-12'>
                <p class='control-label-notes mb-1'>$langParticipants</p>
                <ul>";
                    foreach($participants as $p){
                        $name .= "<li class='help-block mt-1'>$p->givenname&nbsp$p->surname</li>";
                    }
        $name .= "</ul>
              </div>
              </div>";

    return $name;
}