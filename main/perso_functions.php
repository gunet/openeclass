<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * @file perso_functions.php
 * @brief user course and course activity functions
 */
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'main/personal_calendar/calendar_events.class.php';
require_once 'modules/dropbox/class.mailbox.php';
require_once 'modules/dropbox/class.msg.php';
/**
 * @brief display user courses
 * @global type $session
 * @global array $lesson_ids
 * @global type $urlServer 
 * @global type $langUnregCourse
 * @global type $langAdm
 * @global type $langNotEnrolledToLessons
 * @global type $langWelcomeProfPerso
 * @global type $langWelcomeStudPerso
 * @global type $langWelcomeSelect
 * @param type $uid
 * @return string
 */
function getUserLessonInfo($uid) {
    global $teacher_courses_count, $student_courses_count, $langCourse, $langActions;
    global $session, $lesson_ids, $urlServer, $langUnregCourse, $langAdm;
    global $langNotEnrolledToLessons, $langWelcomeProfPerso, $langWelcomeStudPerso, $langWelcomeSelect;

    $lesson_content = '';
    $lesson_ids = array();
    if ($session->status == USER_TEACHER) {
        $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                             course.code code,
                             course.public_code,
                             course.title title,
                             course.prof_names professor,
                             course.lang,
                             course_user.status status	                        
                       FROM course, course_user, user
                       WHERE course.id = course_user.course_id AND
                             course_user.user_id = ?d AND
                             user.id = ?d
                       ORDER BY course.title, course.prof_names", $uid, $uid);
    } else {
        $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                             course.code code,
                             course.public_code,
                             course.title title,
                             course.prof_names professor,
                             course.lang,
                             course_user.status status                                
                       FROM course, course_user, user
                       WHERE course.id = course_user.course_id AND
                             course_user.user_id = ?d AND
                             user.id = ?d AND
                             course.visible != ?d
                       ORDER BY course.title, course.prof_names", $uid, $uid, COURSE_INACTIVE);
    }

    //getting user's lesson info
    $teacher_courses_count = 0;
    $student_courses_count = 0;
    if ($myCourses) {
        $lesson_content .= "<table id='portfolio_lessons' class='table table-striped'>";
        $lesson_content .= "<thead class='sr-only'><tr><th>$langCourse</th><th>$langActions</th></tr></thead>";
        foreach ($myCourses as $data) {
            array_push($lesson_ids, $data->course_id);
            $lesson_content .= "<tr>
			  <td class='text-left'>
			  <b><a href='${urlServer}courses/$data->code/'>" . q(ellipsize($data->title, 64)) . "</a></b><span class='smaller'>&nbsp;(" . q($data->public_code) . ")</span>
			  <div class='smaller'>" . q($data->professor) . "</div></td>";
            $lesson_content .= "<td class='text-center right-cell'>";
            if ($data->status == USER_STUDENT) {
                $lesson_content .= icon('fa-sign-out', $langUnregCourse, "${urlServer}main/unregcours.php?cid=$data->course_id&amp;uid=$uid");
                $student_courses_count++;
            } elseif ($data->status == USER_TEACHER) {
                $lesson_content .= icon('fa-wrench', $langAdm, "${urlServer}modules/course_info/?from_home=true&amp;course=" . $data->code, '', true, true);
                $teacher_courses_count++;
            }
            $lesson_content .= "</td></tr>";
        }
        $lesson_content .= "</tbody></table>";
    } else { // if we are not registered to courses
        $lesson_content .= "<div class='alert alert-warning'>$langNotEnrolledToLessons!</div>";
        if ($session->status == USER_TEACHER) {
            $lesson_content .= "<div class='alert alert-info'>$langWelcomeSelect $langWelcomeProfPerso</div>";
        } else {
            $lesson_content .= "<div class='alert alert-info'>$langWelcomeSelect $langWelcomeStudPerso</div>";
        }        
    }
    return $lesson_content;
}

/**
 * @brief get last month course announcements 
 * @global type $urlAppend
 * @global type $langMore
 * @global type $dateFormatLong
 * @global type $langNoAnnouncementsExist
 * @param type $param
 * @return string
 */ 
function getUserAnnouncements($lesson_id, $type = '') {

    global $urlAppend, $dateFormatLong;

    if (!count($lesson_id)) {
        return '';
    }
            
    $ann_content = '';
    $last_month = strftime('%Y-%m-%d', strtotime('now -1 month'));

    $course_id_sql = implode(', ', array_fill(0, count($lesson_id), '?d'));
    if ($type == 'more') {
        $sql_append = '';
    } else {
        $sql_append = 'LIMIT 5';
    }
    $q = Database::get()->queryArray("SELECT announcement.title,
                                             announcement.`date`,
                                             announcement.id,
                                             course.code,
                                             course.title course_title
                        FROM course, course_module, announcement
                        WHERE course.id IN ($course_id_sql)
                                AND course.id = course_module.course_id
                                AND course.id = announcement.course_id
                                AND announcement.visible = 1
                                AND announcement.`date` >= ?s
                                AND course_module.module_id = ?d
                                AND course_module.visible = 1
                        ORDER BY announcement.`date` DESC $sql_append", $lesson_id, $last_month, MODULE_ID_ANNOUNCE);
    if ($q) { // if announcements exist
        foreach ($q as $ann) {
            $course_title = q(ellipsize($ann->course_title, 80));
            $ann_url = $urlAppend . 'modules/announcements/?course=' . $ann->code . '&amp;an_id=' . $ann->id;
            $ann_date = claro_format_locale_date($dateFormatLong, strtotime($ann->date));
            $ann_content .= "
            <li class='list-item'>
                <div class='item-wholeline'>
                        <div class='text-title'>
                            <a href='$ann_url'>" . q(ellipsize($ann->title, 60)) . "</a>
                        </div>

                    <div class='text-grey'>$course_title</div>
                    
                    <div>$ann_date</div>
                </div>
            </li>";
        }
        return $ann_content;
    } else {
        return '';
    }
}

/**
 * @brief get user personal messages
 * @global type $uid
 * @global type $urlServer
 * @global type $langFrom
 * @global type $dateFormatLong
 * @param type $lesson_id
 * @return string
 */
function getUserMessages() {
           
    global $uid, $urlServer, $langFrom, $dateFormatLong;
    
    $message_content = '';    
               
    $mbox = new Mailbox($uid, 0);
    $msgs = $mbox->getInboxMsgs('', 5);
    foreach ($msgs as $message) {
        if ($message->course_id > 0) {
            $course_title = q(ellipsize(course_id_to_title($message->course_id), 30));
        } else {
            $course_title = '';
        }
        $message_date = claro_format_locale_date($dateFormatLong, $message->timestamp);
        $message_content .= "<li class='list-item'>
                                <div class='item-wholeline'>                                    
                                    <div class='text-title'>$langFrom ".display_user($message->author_id, false, false).":
                                        <a href='{$urlServer}modules/dropbox/index.php?mid=$message->id'>" .q($message->subject)."</a>
                                    </div>                                    
                                    <div class='text-grey'>$course_title</div>
                                    <div>$message_date</div>
                                </div>
                            </li>";
    }    
    return $message_content;
}

