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
 * @file perso.php
 * @brief displays user courses and courses activity
 */
if (!isset($_SESSION['uid'])) {
    die("Unauthorized Access!");
    exit;
}

require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'main/personal_calendar/calendar_events.class.php';

if ($_SESSION['status'] == USER_TEACHER) {
    $extra = "AND course.visible != " . COURSE_INACTIVE;
} else {
    $extra = '';
}

$result2 = Database::get()->queryArray("SELECT course.id cid, course.code code, course.public_code,
                        course.title title, course.prof_names profs, course_user.status status
                FROM course JOIN course_user ON course.id = course_user.course_id
                WHERE course_user.user_id = ?d $extra ORDER BY status, course.title, course.prof_names", $uid);

$courses = array();
if (count($result2) > 0) {
    foreach ($result2 as $mycours) {
        $courses[$mycours->code] = $mycours->status;
    }
}
$_SESSION['courses'] = $courses;

$_user['persoLastLogin'] = last_login($uid);
$_user['lastLogin'] = str_replace('-', ' ', $_user['persoLastLogin']);

//  Get user's course info
$user_lesson_info = getUserLessonInfo($uid);
//if user is registered to at least one lesson
if (count($lesson_ids) > 0) {
    // get user assignments    
    $user_assignments = getUserAssignments($lesson_ids);
    // get user announcements    
    $user_announcements = getUserAnnouncements($lesson_ids);
    // get user documents
    $user_documents = getUserDocuments($lesson_ids);
    // get user agenda    
    $user_agenda = getUserAgenda($lesson_ids);
    // get user forum posts    
    $user_forumPosts = getUserForumPosts($lesson_ids);
} else {
    //show a "-" in all blocks if the user is not enrolled to any lessons
    // (except of the lessons block which is handled before)
    $user_assignments = "<p>-</p>";
    $user_announcements = '';
    $user_documents = "<p>-</p>";
    $user_agenda = "<p>-</p>";
    $user_forumPosts = "<p>-</p>";
}

// create array with content
//BEGIN - Get user personal calendar
$today = getdate();
$day = $today['mday'];
$month = $today['mon'];
$year = $today['year'];
Calendar_Events::get_calendar_settings();
$user_personal_calendar = Calendar_Events::small_month_calendar($day, $month, $year);
//END - Get personal calendar
// ==  BEGIN create array with personalised content

$perso_tool_content = array(
    'lessons_content' => $user_lesson_info,
    'assigns_content' => $user_assignments,
    'docs_content' => $user_documents,
    'agenda_content' => $user_agenda,
    'forum_content' => $user_forumPosts,
    'personal_calendar_content' => $user_personal_calendar
);

/**
 * @brief display user courses
 * @global type $session
 * @global array $lesson_ids
 * @global type $urlServer
 * @global type $themeimg
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
    global $session, $lesson_ids, $urlServer, $themeimg, $langUnregCourse, $langAdm;
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
    if ($myCourses) {
        $lesson_content .= "<table width='100%' class='tbl_lesson'>";
        foreach ($myCourses as $data) {
            array_push($lesson_ids, $data->course_id);
            $lesson_content .= "<tr>
			  <td align='left'><ul class='custom_list'><li>
			  <b><a href='${urlServer}courses/$data->code/'>" . q($data->title) . "</a></b><span class='smaller'>&nbsp;(" . q($data->public_code) . ")</span>
			  <div class='smaller'>" . q($data->professor) . "</div></li></ul></td>";
            $lesson_content .= "<td align='center'>";
            if ($data->status == USER_STUDENT) {
                $lesson_content .= "<a href='${urlServer}main/unregcours.php?cid=" . $data->course_id . "&amp;uid=" . $uid . "'>
				   <img src='$themeimg/cunregister.png' title='$langUnregCourse' alt='$langUnregCourse'></a>";
            } elseif ($data->status == USER_TEACHER) {
                $lesson_content .= "<a href='${urlServer}modules/course_info/?from_home=true&amp;course=" . $data->code . "'>
				    <img src='$themeimg/tools.png' title='$langAdm' alt='$langAdm'></a>";
            }
            $lesson_content .= "</td></tr>";
        }
        $lesson_content .= "</table>";
    } else { // if we are not registered to courses
        $lesson_content .= "<p class='alert1'>$langNotEnrolledToLessons !</p><p><u>$langWelcomeSelect</u>:</p>";
        $lesson_content .= "<table width='100%'>";
        $lesson_content .= "<tr>";
        $lesson_content .= "<td align='left' width='10'><img src='$themeimg/arrow.png' alt='' /></td>";
        if ($session->status == USER_TEACHER) {
            $lesson_content .= "<td align='left'>$langWelcomeProfPerso</td>";
        } else {
            $lesson_content .= "<td align='left'>$langWelcomeStudPerso</td>";
        }
        $lesson_content .= "</tr>";
        $lesson_content .= "</table>";
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
function getUserAnnouncements($lesson_id) {

    global $urlAppend, $langMore, $dateFormatLong;

    $ann_content = '';
    $last_month = strftime('%Y %m %d', strtotime('now -1 month'));

    $course_id_sql = implode(', ', array_fill(0, count($lesson_id), '?d'));
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
                        ORDER BY announcement.`date` DESC LIMIT 5", $lesson_id, $last_month, MODULE_ID_ANNOUNCE);
    if ($q) { // if announcements exist
        foreach ($q as $ann) {
            $course_title = q(ellipsize($ann->course_title, 30));
            $ann_url = $urlAppend . 'modules/announcements/?course=' . $ann->code . '&amp;an_id=' . $ann->id;
            $ann_date = claro_format_locale_date($dateFormatLong, strtotime($ann->date));
            $ann_content .= "<li class='list-item'>" .
                "<span class='item-title'><a href='$ann_url'>" . q(ellipsize($ann->title, 60)) .
                "</a><br><i>$course_title</i> - $ann_date</span></li>";
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
        return "<p class='alert1'>$langNoEventsExist</p>";
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
        return "<p class='alert1'>$langNoEventsExist</p>";
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
        return "<p class='alert1'>$langNoPosts</p>";
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
        return "<p class='alert1'>$langNoDocsExist</p>";
    }
}

/**
 * @brief display course user assingment
 * @global type $langNoAssignmentsExist
 * @global type $langGroupWorkSubmitted
 * @global type $langGroupWorkNotSubmitted
 * @global type $langGroupWorkDeadline_of_Submission
 * @global type $langGroupWorkSubmitted
 * @global type $urlServer
 * @param type $param
 * @param type $type
 * @return string
 */
function getUserAssignments($lesson_id) {

    global $langNoAssignmentsExist, $langGroupWorkSubmitted, $langDays, $langDaysLeft,
    $langGroupWorkDeadline_of_Submission, $langGroupWorkSubmitted, $urlServer, $uid;

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
        return "<p class='alert1'>$langNoAssignmentsExist</p>";
    }
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
