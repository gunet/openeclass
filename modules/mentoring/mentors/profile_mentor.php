<?php


require_once '../../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';
require_once 'main/eportfolio/eportfolio_functions.php';

if(isset($_GET['mentor']) and intval(getDirectReference($_GET['mentor'])) == 0){
    after_reconnect_go_to_mentoring_homepage();
}

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

$toolName = $langMentoringMentors;

//uid is mentor
$is_user_mentor = false;
if(isset($_GET['mentor']) and intval(getDirectReference($_GET['mentor'])) != 0){
    if(getDirectReference($_GET['mentor']) == $uid){
        $is_user_mentor = true;
    }
}
$data['is_user_mentor'] = $is_user_mentor;

//uid is mentee status as mentor
$is_user_guided = false;
$user = Database::get()->querySingle("SELECT *FROM user WHERE id = ?d",$uid);
if ($user) {
    if ($user->status != USER_TEACHER) {
       $is_user_guided = true;
       $data['is_user_guided'] = $is_user_guided ;
    }
}
$data['is_user_guided'] = $is_user_guided;

$data['is_editor_mentoring'] = is_editor_mentoring($uid);

$data['details_mentor'] = array();
$data['mentoring_programs_as_mentor'] = array();
$data['eportfolio_fields'] = array();
if(isset($_GET['mentor']) and intval(getDirectReference($_GET['mentor'])) != 0){
    $data['mentor_id'] = getDirectReference($_GET['mentor']);
    $data['details_mentor'] = show_details_of_mentor(getDirectReference($_GET['mentor']));
    $data['eportfolio_fields'] = render_eportfolio_fields_content(getDirectReference($_GET['mentor']));
    $data['mentoring_programs_as_mentor'] = get_all_available_mentoring_programs_as_mentor_by_uid(getDirectReference($_GET['mentor']));
}

view('modules.mentoring.mentors.profile_mentor', $data);


