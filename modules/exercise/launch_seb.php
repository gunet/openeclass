<?php

require_once 'exercise.class.php';

// Got token and uid params - will skip automatic access control and just check the token
if (isset($_GET['uid']) and isset($_GET['token']) and isset($_GET['exerciseId'])) {
    define('COURSE_VISIBILITY_MANUAL_CHECK', true);
    define('SKIP_DOUBLE_LOGIN_LOCK', true);
    $got_token = true;
} else {
    $got_token = false;
}
$require_current_course = true;

require_once '../../include/baseTheme.php';

// Login the user via token - used when launching the exercise from Safe Exam Browser (SEB)
if ($got_token) {
    $uid = intval($_GET['uid']);
    // consider token valid for 100 sec
    if (token_validate($course_code . $uid . $_GET['exerciseId'], $_GET['token'], 10000)) {
        $user_info = Database::get()->querySingle("SELECT id, surname, givenname, password,
            username, status, email, lang, verified_mail, am
            FROM user WHERE id = ?d", $uid);
        if ($user_info) {
            $_SESSION['uid'] = $user_info->id;
            $_SESSION['uname'] = $user_info->username;
            $_SESSION['surname'] = $user_info->surname;
            $_SESSION['givenname'] = $user_info->givenname;
            $_SESSION['email'] = $user_info->email;
            $_SESSION['SKIP_DOUBLE_LOGIN_LOCK'] = true;
            $session->setLoginMethod('eclass');
        } else {
            Session::Messages($langMailVerifyNoId, 'alert-warning');
            redirect_to_home_page();
        }
    } else {
        Session::Messages($langMailVerifyNoId, 'alert-warning');
        redirect_to_home_page();
    }
}

$unit = $unit ?? null;
$back_url = $unit?
    "modules/units/index.php?course=$course_code&id=$unit":
    "modules/exercise/index.php?course=$course_code";

if (isset($_REQUEST['exerciseId'])) {
    $exerciseId = intval($_REQUEST['exerciseId']);
    // Check if an exercise object exists in session
    if (isset($_SESSION['objExercise'][$exerciseId])) {
        $objExercise = $_SESSION['objExercise'][$exerciseId];
    } else {
        // construction of Exercise
        $objExercise = new Exercise();
        // if the specified exercise is disabled (this only applies to students)
        // or doesn't exist, redirect and show error
        if (!$objExercise->read($exerciseId) || (!$is_editor && $objExercise->selectStatus($exerciseId) == 0)) {
            Session::Messages($langExerciseNotFound, 'alert-warning');
            redirect_to_home_page($back_url);
        }
        // saves the object into the session
        $_SESSION['objExercise'][$exerciseId] = $objExercise;
    }
} else {
    redirect_to_home_page($back_url);
}
$objExercise->LaunchSafeExamBrowser();
