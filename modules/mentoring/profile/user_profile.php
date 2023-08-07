<?php

$require_login = TRUE;


require_once '../../../include/baseTheme.php';
require_once 'modules/tags/eclasstag.class.php';
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

unset($_SESSION['program_code']);
unset($_SESSION['program_id']);
unset($_SESSION['mentoring_group_id']);
unset($mentoring_program_code);
unset($mentoring_program_id);
unset($is_editor_mentoring_program);

if(isset($_GET['user_id'])){
    if(intval(getDirectReference($_GET['user_id'])) == 0){
        after_reconnect_go_to_mentoring_homepage();
    }else{
        $data['user_id'] = $user_id = getDirectReference($_GET['user_id']);
        $toolName = $langCompactProfile;
    }
}else{
    $data['user_id'] = $user_id = $uid;
    $toolName = $langMyProfile;
}

load_js('bootstrap-datetimepicker');

if(isset($_POST['mentor_date_availability'])){
    if($_POST['startdate'] < $_POST['enddate']){
         $exist = Database::get()->queryArray("SELECT *FROM mentoring_mentor_availability WHERE user_id = ?d",$uid);
        if(count($exist) > 0){
            Database::get()->query("UPDATE mentoring_mentor_availability SET 
                                start = ?t, end = ?t 
                                WHERE user_id = ?d",date('Y-m-d H:i:s', strtotime($_POST["startdate"])),date('Y-m-d H:i:s', strtotime($_POST["enddate"])),$uid);
        }else{
            Database::get()->query("INSERT INTO mentoring_mentor_availability SET 
                                user_id = ?d, start = ?t, end = ?t",$uid,date('Y-m-d H:i:s', strtotime($_POST["startdate"])),date('Y-m-d H:i:s', strtotime($_POST["enddate"])));
        }
        Session::flash('message',$langAddAvailabilityMentor);
        Session::flash('alert-class', 'alert-success');
    }else{
        Session::flash('message',$langInvalidDates);
        Session::flash('alert-class', 'alert-danger');
    }
   
    redirect_to_home_page("modules/mentoring/profile/user_profile.php");
}

// user profile for mentor or guided or tutor of program
$data['is_mentor_user'] = $is_mentor_user = Database::get()->querySingle("SELECT is_mentor FROM user WHERE id = ?d",$user_id)->is_mentor;
$data['profile_img'] = profile_image($user_id, IMAGESIZE_LARGE, 'card-img-top ProfileProgramCard');
$data['user_info'] = Database::get()->queryArray("SELECT *FROM user WHERE id = ?d",$user_id);
$data['user_student_is_mentor'] = $user_student_is_mentor = Database::get()->querySingle("SELECT COUNT(id) as ui FROM user WHERE id = ?d AND status = ?d AND is_mentor = ?d",$user_id,USER_STUDENT,1)->ui;
$data['available_start_end'] = array();
if($is_mentor_user == 1 or $user_student_is_mentor == 1){
    $data['available_start_end'] = Database::get()->queryArray("SELECT start,end FROM mentoring_mentor_availability WHERE user_id = ?d",$user_id);
}

view('modules.mentoring.profile.user_profile', $data);
