<?php

/* ========================================================================
 * Open eClass 3.15
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

$require_current_course = TRUE;
$require_course_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'archive_functions.php';

$toolName = $langCourseInfo;
$pageName = $langDelCourse;

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langCourseInfo);
if (isset($_POST['delete'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    // first archive course
    $zipfile = doArchive($course_id, $course_code);

    $garbage = "$webDir/courses/garbage";
    $target = "$garbage/$course_code.$_SESSION[csrf_token]";
    is_dir($target) or make_dir($target);
    touch("$garbage/index.html");
    rename($zipfile, "$target/$course_code.zip");

    // send email to course admins
    $profs = Database::get()->queryArray("SELECT user.id AS prof_uid, user.email AS email,
                              user.surname, user.givenname
                           FROM course_user JOIN user ON user.id = course_user.user_id
                           WHERE course_id = ?d AND course_user.status = " . USER_TEACHER, $course_id);

    $subject = "$langCourseDeleted " . q($currentCourseName) . " ($course_code)";

    $mailHeader = "
    <!-- Header Section -->
	<div id='mail-header'>
		<div>
			<br>
			<div id='header-title'>$langCourseDeleted '" . q($currentCourseName) . " ($course_code)'</div>
		</div>
	</div>";

    $mailMain = "
    <!-- Body Section -->
	<div id='mail-body-inner'>
		<br>
		<div>$langCourseDeletedBy <strong>" . uid_to_name($uid) . "</strong>.</div>
		<br>		
	</div>";

    $mailFooter = "    
	<div id='mail-footer'>
		<br>
		<div><small class='notice'>$langNoticeCourseDeleted</small></div>
	</div>";

    $message = $mailHeader.$mailMain.$mailFooter;
    $plainMessage = html2text($message);
    foreach ($profs as $prof) {
        if (!get_user_email_notification_from_courses($prof->prof_uid) or (!get_user_email_notification($prof->prof_uid, $course_id))) {
            continue;
        } else {
            $to_name = $prof->givenname . ' ' . $prof->surname;
            if (!send_mail_multipart('', '', '', $prof->email, $subject, $plainMessage, $message)) {
                $tool_content .= "<div class='alert alert-warning'>$GLOBALS[langErrorSendingMessage]</div>";
            }
        }
    }

    // delete course
    delete_course($course_id);
    // logging
    Log::record(0, 0, LOG_DELETE_COURSE, array('id' => $course_id,
                                               'code' => $course_code,
                                               'title' => $currentCourseName));

    Session::flash('message',"$langTheCourse <b>" . q($currentCourseName) . " ($course_code)</b> $langHasDel");
    Session::flash('alert-class', 'alert-info');
    unset($_SESSION['dbname']);
    redirect_to_home_page('main/portfolio.php');
}

view('modules.course_info.delete_course');
