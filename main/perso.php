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
 * @file perso.php
 * @brief displays user courses and courses activity
 */
$require_help = true;
$helpTopic = 'portfolio';
$helpSubTopic = 'my_courses';
require_once 'perso_functions.php';

if (!isset($_SESSION['uid'])) {
    die("Unauthorized Access!");
    exit;
}

$_user['persoLastLogin'] = last_login($uid);
$_user['lastLogin'] = str_replace('-', ' ', $_user['persoLastLogin']);

$user_announcements = '';

//  Get user's course info
$user_lesson_info = getUserCourseInfo($uid);
//if user is registered to at least one lesson
if (count($lesson_ids) > 0) {
    // get user announcements
    $user_announcements = getUserAnnouncements($lesson_ids);
}

// Main platform and Collaboration platform are always enabled
if(get_config('show_collaboration') && !get_config('show_always_collaboration')){
    if(count($collaboration_ids) > 0){
        $lesson_collaboration_ids = array_merge($collaboration_ids,$lesson_ids);
        $user_announcements = getUserAnnouncements($lesson_collaboration_ids);
    }
}
// Main platform and Collaboration platform are always enabled
if(get_config('show_collaboration') && get_config('show_always_collaboration')){
    if(count($collaboration_ids) > 0){
        $user_announcements = getUserAnnouncements($collaboration_ids);
    }
}

// get user latest personal messages
$user_messages = getUserMessages();

// create array with content
$today = getdate();
$day = $today['mday'];
$month = $today['mon'];
$year = $today['year'];
Calendar_Events::get_calendar_settings();
$user_personal_calendar = Calendar_Events::small_month_calendar($day, $month, $year);

// create array with personalised content
$perso_tool_content = array(
    'lessons_content' => $user_lesson_info,
    'personal_calendar_content' => $user_personal_calendar
);
