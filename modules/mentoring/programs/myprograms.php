<?php

$require_login = TRUE;


require_once '../../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';

//after_reconnect_go_to_mentoring_homepage();
if(get_config('mentoring_always_active') ){
    if ($language != get_config('default_language')) {
        $language = get_config('default_language');
        // include_messages
        include "lang/$language/common.inc.php";
        $extra_messages = "config/{$language_codes[$language]}.inc.php";
        if (file_exists($extra_messages)) {
            include $extra_messages;
        } else {
            $extra_messages = false;
        }
        include "lang/$language/messages.inc.php";
        if ($extra_messages) {
            include $extra_messages;
        }
    }
}

$toolName = $langMyPrograms;

unset($_SESSION['program_code']);
unset($_SESSION['program_id']);
unset($_SESSION['mentoring_group_id']);
unset($mentoring_program_code);
unset($mentoring_program_id);
unset($is_editor_mentoring_program);

$data['is_editor_mentoring'] = is_editor_mentoring($uid);

// get all available mentoring programs by uid as tutor or mentor or tutor_mentor
$data['mentoring_programs_as_tutor_or_mentor'] = get_all_available_mentoring_programs_as_mentor_or_tutor_or_tutor_mentor_by_uid($uid);

// get all unavailable mentoring programs by uid as tutor or mentor
$data['no_available_mentoring_programs'] = get_all_unavailable_mentoring_programs_as_mentor_or_tutor_or_tutor_mentor_by_uid($uid);

// get all mentoring programs for uid as guided
$data['programs_as_guided'] = show_all_mentoring_programs_for_uid_as_guided($uid);

//if simple user is a mentor
$data['user_student_is_mentor'] = Database::get()->querySingle("SELECT COUNT(id) as ui FROM user WHERE id = ?d AND status = ?d AND is_mentor = ?d",$uid,USER_STUDENT,1)->ui;

//if coordinator status or mentor status user participate as mentee in a program
$data['tutor_mentor_as_mentee'] = Database::get()->queryArray("SELECT id,code,title,tutor,start_date,finish_date,program_image,description,allow_unreg_mentee FROM mentoring_programs
                                                                WHERE (start_date <= NOW() OR start_date IS NULL) AND (finish_date >= NOW() OR finish_date IS NULL)
                                                                AND id IN
                                                                (SELECT mentoring_program_id FROM mentoring_programs_user
                                                                    WHERE user_id = ?d AND is_guided = 1
                                                                    AND user_id IN (SELECT id FROM user WHERE status = ?d)
                                                                )",$uid,USER_TEACHER);

// mentee can unsubscribe from program
if(isset($_POST['unreg_mentee_from_program'])){
    $from_program = $_POST['del_program_id'];
    $mentee_unreg = $_POST['del_mentee_id'];

    $checkProgram = Database::get()->querySingle("SELECT allow_unreg_mentee FROM mentoring_programs WHERE id = ?d",$from_program)->allow_unreg_mentee;
    if($checkProgram == 1){
        $del = delete_guides_from_mentoring_program($from_program,$mentee_unreg);
        if($del){
            Session::flash('message',$langHasUnregisteredSuccess);
            Session::flash('alert-class', 'alert-success');
        }else{
            Session::flash('message',$langNolangHasUnregisteredSuccess);
            Session::flash('alert-class', 'alert-danger');
        }
    }
    redirect_to_home_page('modules/mentoring/programs/myprograms.php');
}

view('modules.mentoring.programs.myprograms', $data);


