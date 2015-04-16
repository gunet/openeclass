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
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/multimediahelper.class.php';
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
    global $teacher_courses_count, $student_courses_count, $langAllCourses;
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
        $lesson_content .= "<thead style='display:none'><tr><th></th><th></th></tr></thead>";
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
                $lesson_content .= icon('fa-wrench', $langAdm, "${urlServer}modules/course_info/?from_home=true&amp;course=" . $data->code);
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
 * @brief displays last 5 course user agenda items
 * @global type $langNoEventsExist
 * @global type $langUnknown
 * @global type $langDuration
 * @global type $langMore
 * @global type $langHours
 * @global type $langHour
 * @global type $langExerciseStart
 * @global type $urlServer
 * @global type $dateFormatLong
 * @param type $param
 * @param type $type
 * @return string
 */
function getUserAgenda($lesson_id) {

    global $langNoEventsExist, $langUnknown, $langDuration, $langMore, $langHours, $langHour;
    global $langExerciseStart, $urlServer, $dateFormatLong;

    $course_id = $lesson_id;
    $found = false;
    $course_ids = array();
    // exclude courses with disabled agenda modules    
    foreach ($course_id as $cid) {
        $q = Database::get()->queryArray("SELECT visible FROM course_module WHERE
                                                      module_id = " . MODULE_ID_AGENDA . " AND
                                                      course_id = ?d", $cid);
        foreach ($q as $row) {
            if ($row->visible == 1) {
                array_push($course_ids, $cid);
            }
        }
    }    
    $course_ids = implode(",", $course_ids);
    if (empty($course_ids)) {// in case there aren't any enabled agenda modules
        return "<div class='alert alert-warning'>$langNoEventsExist</div>";
    }
               
    $result = Database::get()->queryArray("SELECT agenda.title, agenda.content, agenda.start,
                                                  agenda.duration, course.code, course.title AS course_title
                                             FROM agenda, course WHERE agenda.course_id IN ($course_ids)
                                                  AND agenda.course_id = course.id
                                                  AND agenda.visible = 1
                                             HAVING (TO_DAYS(start) - TO_DAYS(NOW())) >= 0
                                             ORDER BY start ASC
                                             LIMIT 5");
          
    $agenda_content = "<table width='100%'>";
    if ($result > 0) {        
        foreach ($result as $data) {
            $agenda_content .= "<tr><td class='sub_title1'>" . claro_format_locale_date($dateFormatLong, strtotime($data->start)) . "</td></tr>";                        
            $url = $urlServer . "modules/agenda/index.php?course=" . $data->code;
            if (strlen($data->duration) == 0) {
                $data->duration = "$langUnknown";
            } elseif ($data->duration == 1) {
                $data->duration = $data->duration . " $langHour";
            } else {
                $data->duration = $data->duration . " $langHours";
            }
            $agenda_content .= "<tr><td><ul class='custom_list'>
                            <li><a href='$url'><b>" . q($data->title) . "</b></a><br /><b>" . q(ellipsize($data->course_title, 80)) . "</b>
                            <div class='smaller'>" . $langExerciseStart . ": <b>" . date('H:i', strtotime($data->start)) . "</b> | $langDuration: <b>" . $data->duration . "</b>
                            <br />" . standard_text_escape(ellipsize_html($data->content, 150, "... <a href='$url'>[$langMore]</a>")) . "</div></li></ul></td></tr>";
            $found = true;
        }
    }
    $agenda_content .= "</table>";
    if ($found) {
        return $agenda_content;
    } else {
        return "<div class='alert alert-warning'>$langNoEventsExist</div>";
    }
}

/**
 * @brief display forum posts from users courses
 * @global type $langNoPosts
 * @global type $langMore
 * @global type $urlServer
 * @param type $param
 * @param type $type
 * @return string
 */
function getUserForumPosts($lesson_id) {

    global $langNoPosts, $langMore, $urlServer;

    $last_month = strftime('%Y %m %d', strtotime('now -1 month'));

    $found = false;
    $forum_content = '<table width="100%">';
    foreach ($lesson_id as $lid) {
        $q = Database::get()->queryArray("SELECT forum.id AS forumid,
                                                 forum.name,
                                                 forum_topic.id AS topicid,
                                                 forum_topic.title,                                                 
                                                 forum_post.post_time,
                                                 forum_post.poster_id,
                                                 forum_post.post_text
                                         FROM forum, forum_topic, forum_post, course_module
                                         WHERE CONCAT(forum_topic.title, forum_post.post_text) != ''
                                                 AND forum.id = forum_topic.forum_id
                                                 AND forum_post.topic_id = forum_topic.id
                                                 AND forum.course_id = ?d
                                                 AND DATE_FORMAT(forum_post.post_time, '%Y %m %d') >= ?t
                                                 AND course_module.visible = 1
                                                 AND course_module.module_id = " . MODULE_ID_FORUM . "
                                                 AND course_module.course_id = ?d
                                         ORDER BY forum_post.post_time LIMIT 10", $lid, $last_month, $lid);
        if ($q) {
            $found = true;
            $forum_content .= "<tr><td class='sub_title1'>" . q(ellipsize(course_id_to_title($lid), 70)) . "</td></tr>";
            foreach ($q as $data) {
                $url = $urlServer . "modules/forum/viewtopic.php?course=" . course_id_to_code($lid) . "&amp;topic=" . $data->topicid . "&amp;forum=" . $data->forumid;
                $forum_content .= "<tr><td><ul class='custom_list'><li><a href='$url'>
				<b>" . q($data->title) . " (" . nice_format(date("Y-m-d", strtotime($data->post_time))) . ")</b>
                                </a><div class='smaller grey'><b>" . q(uid_to_name($data->poster_id)) .
                        "</b></div><div class='smaller'>" .
                        standard_text_escape(ellipsize_html($data->post_text, 150, "<b>&nbsp;...<a href='$url'>[$langMore]</a></b>")) .
                        "</div></li></ul></td></tr>";
            }
        }
    }
    $forum_content .= "</table>";

    if ($found) {
        return $forum_content;
    } else {
        return "<div class='alert alert-warning'>$langNoPosts</div>";
    }
}

/**
 * @brief get user documents newer than one month
 * @global type $langNoDocsExist
 * @param type $param
 * @return string
 */
function getUserDocuments($lesson_id) {

    global $langNoDocsExist, $group_sql;

    $last_month = strftime('%Y-%m-%d', strtotime('now -1 month'));

    $found = false;
    $doc_content = '<table width="100%">';
    foreach ($lesson_id as $lid) {
        $q = Database::get()->queryArray("SELECT document.path, document.course_id, document.filename,
                                            document.title, document.date_modified,
                                            document.format, document.visible,
                                            document.id
                                     FROM document, course_module
                                     WHERE document.course_id = ?d AND                             
                                            subsystem = " . MAIN . " AND
                                            document.visible = 1 AND
                                            date_modified >= '$last_month' AND
                                            format <> '.dir' AND
                                            course_module.module_id = " . MODULE_ID_DOCS . " AND
                                            course_module.visible = 1 AND
                                            course_module.course_id = ?d
                                    ORDER BY date_modified DESC", $lid, $lid);


        if ($q) {
            $found = true;
            $doc_content .= "<tr><td class='sub_title1'>" . q(ellipsize(course_id_to_title($lid), 70)) . "</td></tr>";
            foreach ($q as $course_file) {
                $group_sql = "course_id = " . $lid . " AND subsystem = " . MAIN;
                $url = file_url($course_file->path, $course_file->filename, course_id_to_code($lid));
                $dObj = MediaResourceFactory::initFromDocument($course_file);
                $dObj->setAccessURL($url);
                $dObj->setPlayURL(file_playurl($course_file->path, $course_file->filename, course_id_to_code($lid)));
                $href = MultimediaHelper::chooseMediaAhref($dObj);
                $doc_content .= "<tr><td class='smaller'><ul class='custom_list'><li>" .
                        $href . ' - (' . nice_format(date('Y-m-d', strtotime($course_file->date_modified))) . ")</li></ul></td></tr>";
            }
        }
    }
    $doc_content .= "</table>";
    if ($found) {
        return $doc_content;
    } else {
        return "<div class='alert alert-warning'>$langNoDocsExist</div>";
    }
}

/**
 * @brief display course user assingment
 * @global type $langNoAssignmentsExist
 * @global type $langGroupWorkSubmitted
 * @global type $langGroupWorkDeadline_of_Submission
 * @global type $langGroupWorkSubmitted
 * @global type $urlServer
 * @param type $param
 * @param type $type
 * @return string
 */
function getUserAssignments($lesson_id) {

    global $langNoAssignmentsExist, $langGroupWorkSubmitted, $langDays, $langDaysLeft,
    $langGroupWorkDeadline_of_Submission, $urlServer, $uid;

    $found = false;
    $assign_content = '<table width="100%">';
    foreach ($lesson_id as $lid) {
        $q = Database::get()->queryArray("SELECT DISTINCT assignment.id, assignment.title, assignment.deadline,
                                        (TO_DAYS(assignment.deadline) - TO_DAYS(NOW())) AS days_left
                                    FROM assignment, course, course_module
                                        WHERE (TO_DAYS(deadline) - TO_DAYS(NOW())) >= '0'
                                        AND assignment.active = 1
                                        AND assignment.course_id = ?d
                                        AND course.id = ?d
                                        AND course_module.course_id = course.id
                                        AND course_module.visible = 1 AND course_module.module_id = " . MODULE_ID_ASSIGN . "
                                    ORDER BY assignment.deadline", $lid, $lid);

        if ($q) {
            $found = true;
            $assign_content .= "<tr><td class='sub_title1'>" . q(ellipsize(course_id_to_title($lid), 70)) . "</td></tr>";
            foreach ($q as $data) {
                $url = $urlServer . "modules/work/index.php?course=" . course_id_to_code($lid) . "&amp;i=" . $data->id;
                if (submitted($uid, $data->id, $lid)) {
                    $submit_status = $langGroupWorkSubmitted;
                } else {
                    $submit_status = "($langDaysLeft $data->days_left $langDays)";
                }
                $assign_content .= "<tr><td><ul class='custom_list'><li><a href='$url'><b>" .
                        q($data->title) .
                        "</b></a><div class='smaller'>$langGroupWorkDeadline_of_Submission: <b>" .
                        nice_format($data->deadline, true) . "</b><div class='grey'>" .
                        $submit_status . "</div></div></li></ul></td></tr>";
            }
        }
    }
    $assign_content .= "</table>";
    if ($found) {
        return $assign_content;
    } else {
        return "<div class='alert alert-warning'>$langNoAssignmentsExist</div>";
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


/**
 *
 *  returns whether the user has submitted an assignment
 */
function submitted($uid, $assignment_id, $lesson_id) {
    // find prefix
    $prefix = './modules';
    if (!file_exists($prefix) && file_exists('../group') && file_exists('../work'))
        $prefix = '..';

    require_once($prefix . '/group/group_functions.php');
    require_once($prefix . '/work/work_functions.php');

    $gids = user_group_info($uid, $lesson_id);
    $GLOBALS['course_id'] = $lesson_id;

    if ($submission = find_submissions(is_group_assignment($assignment_id), $uid, $assignment_id, $gids))
        return true;
    else
        return false;
}
