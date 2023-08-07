<?php


require_once '../../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';

//after_reconnect_go_to_mentoring_homepage();
if(get_config('mentoring_always_active') ){
    if ($language != get_config('default_language') && isset($_SESSION['uid'])) {
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

$toolName = $langOurMentoringPrograms;

unset($_SESSION['program_code']);
unset($_SESSION['program_id']);
unset($_SESSION['mentoring_group_id']);
unset($mentoring_program_code);
unset($mentoring_program_id);
unset($is_editor_mentoring_program);

$data['is_editor_mentoring'] = is_editor_mentoring($uid);

// get all mentoring programs for all users
if(!isset($_SESSION['uid'])){
    $data['all_programs'] = Database::get()->queryArray("SELECT *FROM mentoring_programs 
                                                    WHERE (start_date <= NOW() OR start_date IS NULL) 
                                                    AND (finish_date >= NOW() OR finish_date IS NULL)
                                                    AND lang = ?s",$language);
}else{
    $data['all_programs'] = show_all_mentoring_programs();
}

view('modules.mentoring.programs.show_programs', $data);


