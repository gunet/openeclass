<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/**
 * @file: register.php
 * @brief: course registration page
 */
$require_current_course = true;
$course_guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'include/log.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';

if (isset($_SESSION['courses'][$course_code]) and $_SESSION['courses'][$course_code]) {
    redirect_to_home_page("courses/$course_code/");
}

$course = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $course_id);
$professor = $course->prof_names;
$langUserPortfolio = q($course->title);

if (isset($_POST['register'])) {
    if ($course->visible == COURSE_REGISTRATION or $course->visible == COURSE_OPEN) {
        if ($course->password !== '' and !(isset($_POST['pass']) and $course->password == $_POST['pass'])) {
            Session::Messages($langWrongPassCourse, 'alert-danger');
            if ($_POST['register'] == 'from-home') {
                redirect_to_home_page("courses/$course_code/");
            } else {
                redirect_to_home_page('modules/course_home/register.php?course=' . $course_code);
            }
        }
        Database::get()->query("INSERT IGNORE INTO `course_user` (`course_id`, `user_id`, `status`, `reg_date`)
            VALUES (?d, ?d, " . USER_STUDENT . ", NOW())", $course_id, $uid);
        Log::record($cid, MODULE_ID_USERS, LOG_INSERT, array('uid' => $uid, 'right' => USER_STUDENT));
        Session::Messages($langNotifyRegUser1, 'alert-success');
        redirect_to_home_page("courses/$course_code/");
    }
}

if ($course->visible == COURSE_OPEN) {
    redirect_to_home_page("courses/$course_code/");
} elseif ($course->visible == COURSE_INACTIVE) {
    redirect_to_home_page('main/portfolio.php');
}

$pageTitle = $langRegCourses;

if ($course->visible == COURSE_CLOSED) {
    $accessInfo = $langClosedCourse;
    $accessIcon = "<span class='fa fa-lock fa-fw' style='font-size:23px;'></span>";
    $accessHelp = $langClosedCourseShort;
    if (setting_get(SETTING_COURSE_USER_REQUESTS_DISABLE)) {
        $registerLink = '';
    } else {
        $registerLink = "
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langRegistration:</label>
                <div class='col-sm-10'>
                    <p class='form-control-static'>
                        <em><a href='{$urlAppend}modules/contact/index.php?course_id=$course_id'>$langLabelCourseUserRequest</a></em>
                    </p>
                </div>
            </div>";
    }
} elseif ($course->visible == COURSE_REGISTRATION) {
    $accessInfo = $langTypeRegistration;
    $accessIcon = "<span class='fa fa-lock fa-fw' style='font-size:23px;'><span class='fa fa-pencil text-danger fa-custom-lock' style='font-size:16px; position:absolute; top:7px; left:30px;'></span></span>";
    $accessHelp = $langPrivOpen;
    $registerLink = "
            <div class='form-group'>
                <div class='col-sm-12 text-center'>
                   <input type='submit' name='register' class='btn btn-default' value='$langRegEnterCourse'>
                </div>
            </div>";
    if ($course->password) {
        $registerLink = "
            <div class='form-group'>
                <label class='col-sm-2 control-label' for='pass'>$langPassword:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='password' name='pass' id='pass' autocomplete='off'>
                </div>
            </div>" . $registerLink;
    }
}

$tree = new Hierarchy();
$courseObject = new Course();
$departments = array();
foreach ($courseObject->getDepartmentIds($course_id) as $dep) {
    $departments[] = q($tree->getFullPath($dep));
}
$departments = implode('<br>', $departments);

$tool_content .= action_bar(array(
    array('title' => $langBack,
          'url' => $urlServer . 'main/portfolio.php',
          'icon' => 'fa-reply',
          'level' => 'primary-label',
          'button-class' => 'btn-default')),false) . "
<div class='row'><div class='panel'><div class='panel-body'>
    <form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <fieldset>
            <div class='col-xs-12'>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCode:</label>
                <div class='col-sm-10'>
                    <p class='form-control-static'>" . q($course->public_code) . "</p>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langFaculty:</label>
                <div class='col-sm-10'>
                    <p class='form-control-static'>$departments</p>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langConfidentiality:</label>
                <div class='col-sm-10'>
                    <p class='form-control-static'>
                        $accessIcon&nbsp;$accessInfo
                        <span class='help-block'><small>$accessHelp</small></span>
                    </p>
                </div>
            </div>
            $registerLink
            </div>
        </fieldset>
    </form>
</div></div></div>";


draw($tool_content, 1, null, $head_content);

