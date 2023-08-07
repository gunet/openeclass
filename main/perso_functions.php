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
 * @file perso_functions.php
 * @brief user course and course activity functions
 */
require_once 'include/lib/mediaresource.factory.php';
require_once 'main/personal_calendar/calendar_events.class.php';
require_once 'modules/message/class.mailbox.php';
require_once 'modules/message/class.msg.php';

/**
 * @brief display user courses
 * @param integer $uid
 * @return string
 */
function getUserLessonInfo($uid) {
    global $teacher_courses_count, $student_courses_count, $langCourse, $langActions;
    global $session, $lesson_ids, $courses, $urlServer, $langUnregCourse, $langAdm, $langFavorite;
    global $langNotEnrolledToLessons, $langWelcomeProfPerso, $langWelcomeStudPerso, $langWelcomeSelect;

    $lesson_content = '';

    $lesson_ids = array();
    $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                             course.code code,
                             course.public_code,
                             course.title title,
                             course.prof_names professor,
                             course.lang,
                             course.visible visible,
                             course_user.status status,
                             course_user.favorite favorite
                        FROM course JOIN course_user
                            ON course.id = course_user.course_id
                            AND course_user.user_id = ?d
                            AND (course.visible != " . COURSE_INACTIVE . " OR course_user.status = " . USER_TEACHER . " OR course_user.editor = 1)
                        ORDER BY favorite DESC, status ASC, visible ASC, title ASC", $uid);

    $courses = [];
    if ($myCourses) {
        foreach ($myCourses as $myCourse) {
            $courses[$myCourse->code] = $myCourse->status;
        }
    }
    $_SESSION['courses'] = $courses;

    //getting user's lesson info
    $teacher_courses_count = 0;
    $student_courses_count = 0;
    if ($myCourses) {
        $lesson_content .= "<table id='portfolio_lessons' class='table table-striped'>";
        $lesson_content .= "<thead><tr><th>$langCourse</th><th>$langActions</th></tr></thead>";
        foreach ($myCourses as $data) {
            array_push($lesson_ids, $data->course_id);
            $visclass = '';
            if ($data->visible == COURSE_INACTIVE) {
                $visclass = "not_visible";
            }
            if (isset($data->favorite)) {
                $favorite_icon = 'fa-star';
                $fav_status = 0;
                $fav_message = '';
            } else {
                $favorite_icon = 'fa-bookmark-o';
                $fav_status = 1;
                $fav_message = $langFavorite;
            }
            $lesson_content .= "<tr class='$visclass'>
			  <td class='text-left'>
			  <strong><a href='{$urlServer}courses/$data->code/'>" . q(ellipsize($data->title, 64)) . "</a></strong>&nbsp;(" . q($data->public_code) . ")
			  <div><small>" . q($data->professor) . "</small></div></td>";
            $lesson_content .= "<td class='text-center'>";
            $lesson_content .= icon($favorite_icon, $fav_message, "course_favorite.php?course=" . $data->code . "&amp;fav=$fav_status");
            $lesson_content .= "&nbsp;&nbsp;";
            if ($data->status == USER_STUDENT) {
                if (get_config('disable_student_unregister_cours') == 0) {
                    $lesson_content .= icon('fa-minus-circle', $langUnregCourse, "{$urlServer}main/unregcours.php?cid=$data->course_id&amp;uid=$uid");
                    $student_courses_count++;
                }
            } elseif ($data->status == USER_TEACHER) {
                $lesson_content .= icon('fa-wrench', $langAdm, "{$urlServer}modules/course_info/?from_home=true&amp;course=" . $data->code, '', true, true);
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
 * @param $lesson_id
 * @param string $type
 * @param false $to_ajax
 * @param string $filter
 * @return array|string
 */
function getUserAnnouncements($lesson_id, $type='', $to_ajax=false, $filter='') {

    global $urlAppend, $langAdminAn;

    if ($type == 'more') {
        $sql_append = '';
    } else {
        $sql_append = 'LIMIT 5';
    }

    if (!empty($filter)) {
        $admin_filter_sql = 'AND admin_announcement.title LIKE ?s';
        $course_filter_sql = 'AND announcement.title LIKE ?s';
        $filter_param = '%' . $filter . '%';
    } else {
        $admin_filter_sql = $course_filter_sql = '';
        $filter_param = array();
    }

    if (!count($lesson_id)) {
        $q = Database::get()->queryArray("
                                SELECT admin_announcement.title,
                                             admin_announcement.`date` AS an_date,
                                             admin_announcement.id
                                FROM admin_announcement
                                WHERE admin_announcement.visible = 1
                                        AND (admin_announcement.begin <= " . DBHelper::timeAfter() . " OR admin_announcement.begin IS NULL)
                                        AND (admin_announcement.end >= " . DBHelper::timeAfter() . " OR admin_announcement.end IS NULL)
                                        AND admin_announcement.`date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) $admin_filter_sql
                                 ORDER BY an_date DESC
                         $sql_append", $filter_param);
    } else {

        $course_id_sql = implode(', ', array_fill(0, count($lesson_id), '?d'));

        $q = Database::get()->queryArray("(SELECT announcement.title,
                                             announcement.`date` AS an_date,
                                             announcement.id,
                                             announcement.content,
                                             course.code,
                                             course.title course_title
                            FROM course, course_module, announcement
                            WHERE course.id IN ($course_id_sql)
                                    AND course.id = course_module.course_id
                                    AND course.id = announcement.course_id
                                    AND announcement.visible = 1
                                    AND (announcement.start_display <= " . DBHelper::timeAfter() . " OR announcement.start_display IS NULL)
                                    AND (announcement.stop_display >= " . DBHelper::timeAfter() . " OR announcement.stop_display IS NULL)
                                    AND announcement.`date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                                    AND course_module.module_id = " . MODULE_ID_ANNOUNCE . "
                                    AND course_module.visible = 1 $course_filter_sql)
                            UNION
                                (SELECT admin_announcement.title,
                                             admin_announcement.`date` AS admin_an_date,
                                             admin_announcement.id, admin_announcement.body AS content, '', ''
                                FROM admin_announcement
                                WHERE admin_announcement.visible = 1
                                      AND (admin_announcement.begin <= " . DBHelper::timeAfter() . " OR admin_announcement.begin IS NULL)
                                      AND (admin_announcement.end >= " . DBHelper::timeAfter() . " OR admin_announcement.end IS NULL)
                                      AND admin_announcement.`date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) $admin_filter_sql
                                ) ORDER BY an_date DESC
                         $sql_append", $lesson_id, $filter_param, $filter_param);
    }
    if ($to_ajax) {
        $arr_an = array();
        foreach ($q as $arr_q) {
            $arr_an[] = $arr_q;
        }
        return $arr_an;
    } else {
        $ann_content = '';
        foreach ($q as $ann) {
            if (isset($ann->code) & $ann->code != '') {
                $course_title = q(ellipsize($ann->course_title, 80));
                $ann_url = $urlAppend . 'modules/announcements/?course=' . $ann->code . '&amp;an_id=' . $ann->id;
                $ann_date = format_locale_date(strtotime($ann->an_date));
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
            } else {
                $ann_url = $urlAppend . 'main/system_announcements.php?an_id=' . $ann->id;
                $ann_date = format_locale_date(strtotime($ann->an_date));
                $ann_content .= "
                <li class='list-item'>
                    <div class='item-wholeline'>
                            <div class='text-title'>
                                <a href='$ann_url'>" . q(ellipsize($ann->title, 60)) . "</a>
                            </div>

                        <div class='text-grey'>$langAdminAn&nbsp; <span class='fa fa-user text-danger'></span></div>

                        <div>$ann_date</div>
                    </div>
                </li>";
            }
        }
        return $ann_content;
    }
}

/**
 * @brief get user personal messages
 * @return string
 */
function getUserMessages() {

    global $uid, $urlServer, $langFrom;

    $message_content = '';

    $mbox = new Mailbox($uid, 0);
    $msgs = $mbox->getInboxMsgs('', 5);
    foreach ($msgs as $message) {
        if ($message->course_id > 0) {
            $course_title = q(ellipsize(course_id_to_title($message->course_id), 30));
        } else {
            $course_title = '';
        }
        $message_date = format_locale_date($message->timestamp);
        $message_content .= "<li class='list-item'>
                                <div class='item-wholeline'>
                                    <div class='text-title'>$langFrom ".display_user($message->author_id, false, false).":
                                        <a href='{$urlServer}modules/message/index.php?mid=$message->id'>" .q($message->subject)."</a>
                                    </div>
                                    <div class='text-grey'>$course_title</div>
                                    <div>$message_date</div>
                                </div>
                            </li>";
    }
    return $message_content;
}


/**
 * @brief check if user has accepted or rejected the current privacy policy
 * @global integer $uid
 * @return boolean
 */
function user_has_accepted_policy($uid) {
    $q = Database::get()->querySingle('SELECT ts FROM user_consent
        WHERE user_id = ?d', $uid);
    if ($q and $q->ts >= get_config('privacy_policy_timestamp')) {
        return true;
    } else {
        return false;
    }
}


/**
 * @brief update user consent
 * @param $uid
 * @param bool $accept
 */
function user_accept_policy($uid, $accept = true) {
    $accept = $accept? 1: 0;
    Database::get()->query('INSERT INTO user_consent
        (has_accepted, user_id, ts) VALUES (?d, ?d, NOW())
        ON DUPLICATE KEY UPDATE has_accepted = ?d, ts = NOW()', $accept, $uid, $accept);
}
