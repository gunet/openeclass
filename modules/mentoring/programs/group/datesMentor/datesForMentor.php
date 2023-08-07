<?php         

$require_login = TRUE;


require_once '../../../../../include/baseTheme.php';   
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

//show all events
if(isset($_GET['view'])) {

    if(isset($_GET['view']) and isset($_GET['show_mentor']) and isset($_GET['show_group'])){
        $mentoring_program_id = show_mentoring_program_id($mentoring_program_code);

        $group_id = $_GET['show_group'];
        $mentor_id = $_GET['show_mentor'];

       
        $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

        $eventArr = array();

        
        $result_events = Database::get()->queryArray("SELECT id,mentoring_program_id,user_id,group_id,start,end FROM mentoring_mentor_availability_group
                                                        WHERE start BETWEEN (?s) AND (?s)
                                                        AND end > NOW()
                                                        AND mentoring_program_id = ?d
                                                        AND group_id = ?d
                                                        AND user_id = ?d",$start,$end,$mentoring_program_id,$group_id,$mentor_id);

    

        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'program' => $row->mentoring_program_id,
                    'title' => showTimeEvent($row->start,$row->end,$row->id),
                    'start' => $row->start,
                    'end' => $row->end,
                    'group_id' => $row->group_id,
                    'user_id' => $row->user_id
                ];
            }
        }
        
        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();
        

    }

   
}

function showTimeEvent($startTime,$endTime,$evtId){

    global $urlAppend,$langViewHour;

    // $availableTime = "<a class='btn btn-sm small-text normalBlueText TextBold rounded-2 eventTimeTutor m-auto d-block' data-bs-toggle='collapse' href='#collapseExample$evtId' role='button' aria-expanded='false' aria-controls='collapseExample$evtId'>
    //                     <img class='img-info-programs' src='{$urlAppend}template/modern/img/info_a.svg'>
    //                   </a>";


    // $availableTime .= "<div class='collapse' id='collapseExample$evtId'>
    //                         <div class='col-12 d-flex justify-content-center align-items-center flex-wrap bgNormalBlueText rounded-2 text-white TextBold'>
    //                             <span class='badge bgNormalBlueText'>".date('H:i', strtotime($startTime)).'--'.date('H:i', strtotime($endTime))."</span>
    //                         </div>
    //                     </div>";

    $availableTime = "<span style='background-color:#2673ca;' class='badge'>".date('H:i', strtotime($startTime)).'--'.date('H:i', strtotime($endTime))."</span>";

    return $availableTime;

}

