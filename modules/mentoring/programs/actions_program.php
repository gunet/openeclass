<?php

$require_login = TRUE;

require_once '../../../include/baseTheme.php';
require_once 'modules/mentoring/mentoring_log.class.php';
require_once 'modules/mentoring/functions.php';

mentoring_program_access();

load_js('bootstrap-datetimepicker');

put_session_values_in_db_and_get_this_after_logout($uid,$mentoring_program_code);

$toolName = show_mentoring_program_title($mentoring_program_code).' '.'('.$langMentoringAction.')';

$data['is_tutor_of_mentoring_program'] = $is_tutor_of_mentoring_program = show_mentoring_program_editor($mentoring_program_id,$uid);

$data['logs'] = '';
if(isset($_POST['log_program'])){
    $now = date('Y-m-d H:i:s', strtotime('now'));
    $log = new Mentoring_Log();
    if(isset($_POST['type'])){
        if($_POST['log_program'] == 'doc'){
            $data['logs'] = $log->display($mentoring_program_id,-1,MENTORING_MODULE_ID_DOCS,$_POST['type'],$_POST['startdate'],$now);
        }elseif($_POST['log_program'] == 'forum'){
            $data['logs'] = $log->display($mentoring_program_id,-1,MENTORING_MODULE_ID_FORUM,$_POST['type'],$_POST['startdate'],$now);
        }elseif($_POST['log_program'] == 'request'){
            $data['logs'] = $log->display($mentoring_program_id,-1,MENTORING_MODULE_ID_REQUESTS,$_POST['type'],$_POST['startdate'],$now);
        }elseif($_POST['log_program'] == 'group'){
            $data['logs'] = $log->display($mentoring_program_id,-1,MENTORING_MODULE_ID_GROUP,$_POST['type'],$_POST['startdate'],$now);
        }elseif($_POST['log_program'] == 'program'){
            if(isset($_POST['type']) and $_POST['type'] == 1){
                $_POST['type'] = 4;
                $program_id = $mentoring_program_id;
            }elseif(isset($_POST['type']) and $_POST['type'] == 2){
                $_POST['type'] = 6;
                $program_id = $mentoring_program_id;
            }elseif(isset($_POST['type']) and $_POST['type'] == 3){
                $_POST['type'] = 5;
                $program_id = 0;
            }
            $data['logs'] = $log->display($program_id,-1,MENTORING_MODULE_ID_PROGRAM,$_POST['type'],$_POST['startdate'],$now);
        }elseif($_POST['log_program'] == 'meeting'){
            $data['logs'] = $log->display($mentoring_program_id,-1,MENTORING_MODULE_ID_MEETING,$_POST['type'],$_POST['startdate'],$now);
        }
    }else{

    }
}


$data['action_bar'] = action_bar([
    [ 'title' => $langBackPage,
      'url' => $urlAppend."mentoring_programs/$mentoring_program_code/index.php",
      'icon' => 'fa-chevron-left',
      'level' => 'primary-label',
      'button-class' => 'backButtonMentoring']
]);

view('modules.mentoring.programs.actions_program', $data);


