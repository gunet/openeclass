<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**
 * @file: register.php
 * @brief: course registration page
 */
$require_current_course = true;
$course_guest_allowed = true;
$require_login = true;

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

if (!is_enabled_course_registration($uid)) {
    redirect_to_home_page();
}

if (isset($_POST['register'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if ($course->visible == COURSE_REGISTRATION or $course->visible == COURSE_OPEN) {
        if ($course->password !== '' and !(isset($_POST['password']) and $course->password == $_POST['password'])) {
            Session::flash('message',$langWrongPassCourse);
            Session::flash('alert-class', 'alert-danger');
            if ($_POST['register'] == 'from-home') {
                redirect_to_home_page("courses/$course_code/");
            } else {
                redirect_to_home_page('modules/course_home/register.php?course=' . $course_code);
            }
        }

        // check for prerequisites
        $prereq1 = Database::get()->queryArray("SELECT cp.prerequisite_course
                                 FROM course_prerequisite cp
                                 WHERE cp.course_id = ?d", $course_id);
        if (count($prereq1) > 0) {
            $completion = true;

            foreach ($prereq1 as $prereqCourseId) {
                $prereq2 = Database::get()->queryArray("SELECT id
                                  FROM user_badge
                                  WHERE user = ?d
                                  AND badge IN (SELECT id FROM badge WHERE course_id = ?d AND bundle = -1)
                                  AND completed = 1", $uid, $prereqCourseId);
                if (count($prereq2) <= 0) {
                    $completion = false;
                    break;
                }
            }

            if (!$completion) {
                Session::flash('message',$langPrerequisitesNotComplete);
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page("courses/$course_code/");
            }
        }

        Database::get()->query("INSERT IGNORE INTO `course_user` (`course_id`, `user_id`, `status`, `reg_date`, `document_timestamp`)
            VALUES (?d, ?d, " . USER_STUDENT . ", NOW(), NOW())", $course_id, $uid);
        Log::record($course_id, MODULE_ID_USERS, LOG_INSERT, array('uid' => $uid, 'right' => USER_STUDENT));
        Session::flash('message',$langNotifyRegUser1);
        Session::flash('alert-class', 'alert-success');
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
            <div class='form-group mt-4'>
                <div class='col-sm-12 control-label-notes'>$langRegistration:</div>
                <div class='col-sm-12'>
                    <p class='form-control-static'>
                        <em><a class='TextBold text-decoration-underline' href='{$urlAppend}modules/contact/index.php?course_id=$course_id'>$langLabelCourseUserRequest</a></em>
                    </p>
                </div>
            </div>";
    }
} elseif ($course->visible == COURSE_REGISTRATION) {
    $accessInfo = $langTypeRegistration;
    $accessIcon = "
                  
                   <div class='d-inline-flex align-items-center'>
                        <span class='fa fa-lock fa-lg fa-fw access'></span>
                        <span class='fa fa-pencil text-danger fa-custom-lock mt-0' style='margin-left:-5px;'></span>
                    </div>
                  
                  
                  ";
    $accessHelp = $langPrivOpen;
    $registerLink = "
            <div class='form-group mt-5'>
                <div class='col-sm-12'>
                   <input type='submit' name='register' class='btn submitAdminBtn' value='$langRegEnterCourse'>
                </div>
            </div>";
    if ($course->password) {
        $registerLink = "
            <div class='form-group mt-4'>
                <label class='col-sm-12 control-label-notes' for='password-field'>$langPassword</label>
                <div class='col-lg-6 col-sm-12'>
                    <input class='form-control' type='password' name='password' id='password-field' autocomplete='off'>
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
          'level' => 'primary',
          'button-class' => 'btn-default')),false) . "
        
<div class='row m-auto'>
    <div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
        <div class='card-body'>
            <form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>" .
                generate_csrf_token_form_field() . "
                <fieldset>
                    <legend class='mb-0' aria-label='$langForm'></legend>
                    <div class='col-12'>
                        <div class='form-group'>
                            <div class='col-sm-12 control-label-notes'>$langCode</div>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>" . q($course->public_code) . "</p>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <div class='col-sm-12 control-label-notes'>$langFaculty</div>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>$departments</p>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <div class='col-sm-12 control-label-notes'>$langConfidentiality</div>
                            <div class='col-sm-12'>
                                <p class='form-control-static'>
                                    $accessIcon&nbsp;$accessInfo
                                    <div class='help-block'>$accessHelp</div>
                                </p>
                            </div>
                        </div>
                        $registerLink
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>";


draw($tool_content, 1, null, $head_content);
