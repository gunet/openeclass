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

use Widgets\WidgetArea;

$require_current_course = true;
$guest_allowed = true;

define('HIDE_TOOL_TITLE', 1);
define('STATIC_MODULE', 1);

require_once '../../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/action.php';
require_once 'include/course_settings.php';
require_once 'include/log.class.php';
require_once 'modules/sharing/sharing.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/comments/class.comment.php';
require_once 'modules/comments/class.commenting.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/document/doc_init.php';
require_once 'main/personal_calendar/calendar_events.class.php';
require_once 'modules/course_metadata/CourseXML.php';
require_once 'modules/progress/process_functions.php';
require_once 'modules/wall/wall_wrapper.php';
require_once 'modules/session/functions.php';

doc_init();
$tree = new Hierarchy();
$course = new Course();

$up = new Permissions();
$course_users_permission = $up->has_course_users_permission();
$allow_clone = $up->has_course_clone_permission();
$allow_course_backup = $up->has_course_backup_permission();
$allow_course_tools = $up->has_course_modules_permission();

$pageName = ''; // delete $pageName set in doc_init.php

$main_content = $cunits_content = $course_info_extra = $data['countUnits'] = "";

add_units_navigation(TRUE);

load_js('tools.js');

define('QTYPE_SINGLE', 1);
define('QTYPE_MULTIPLE', 3);


if (isset($_POST['submitPoll'])) {
    $qtype = $_POST['qtype'];
    $answer = $_POST['answer'];
    $pqid = $_POST['pqid'];
    $pid = $_POST['pid'];
    $multiple_submissions = $_POST['multiple_submissions'];

    if ($multiple_submissions) {
        Database::get()->query("DELETE FROM poll_answer_record WHERE poll_user_record_id IN (SELECT id FROM poll_user_record WHERE uid = ?d AND pid = ?d)", $uid, $pid);
        Database::get()->query("DELETE FROM poll_user_record WHERE uid = ?d AND pid = ?d", $uid, $pid);
    }

    $user_record_id = Database::get()->query("INSERT INTO poll_user_record (pid, uid, email, email_verification, verification_code) VALUES (?d, ?d, ?s, ?d, ?s)", $pid, $uid, NULL, NULL, NULL)->lastInsertID;

    if ($qtype == QTYPE_MULTIPLE) {
        foreach ($answer[$pqid] as $aid) {
            $aid = intval($aid);
            Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date)
                            VALUES (?d, ?d, ?d, '', NOW())", $user_record_id, $pqid, $aid);
        }
    } else {
        Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date)
                            VALUES (?d, ?d, ?d, '', NOW())", $user_record_id, $pqid, $answer);
    }

}

$data['course_info'] = $course_info = Database::get()->querySingle("SELECT title, keywords, visible, prof_names, public_code, course_license,
                                               view_type, start_date, end_date, description, home_layout, course_image, flipped_flag, password
                                          FROM course WHERE id = ?d", $course_id);

// Handle unit reordering
if ($is_editor and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toReorder'])) {
        reorder_table('course_units', 'course_id', $course_id, $_POST['toReorder'],
            $_POST['prevReorder'] ?? null);
        exit;
    }
}

// other actions in course unit
if ($is_editor) {
    // update index and refresh course metadata
    require_once 'modules/search/classes/ConstantsUtil.php';
    require_once 'modules/search/classes/SearchEngineFactory.php';
    $searchEngine = SearchEngineFactory::create();

    if (isset($_REQUEST['del'])) { // delete course unit
        $id = intval(getDirectReference($_REQUEST['del']));
        if ($course_info->view_type == 'units') {
            Database::get()->query('DELETE FROM course_units WHERE id = ?d', $id);
            Database::get()->query('DELETE FROM unit_resources WHERE unit_id = ?d', $id);
            Database::get()->query("DELETE FROM course_units_to_specific WHERE unit_id = ?d", $id);
            $searchEngine->indexResource(ConstantsUtil::REQUEST_REMOVE, ConstantsUtil::RESOURCE_UNIT, $id);
            $searchEngine->indexResource(ConstantsUtil::REQUEST_REMOVEBYUNIT, ConstantsUtil::RESOURCE_UNITRESOURCE, $id);
            $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_COURSE, $course_id);
            CourseXMLElement::refreshCourse($course_id, $course_code);
            Session::flash('message',$langCourseUnitDeleted);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("courses/$course_code/");
        }
    } elseif (isset($_REQUEST['vis'])) { // modify visibility
        $id = intval(getDirectReference($_REQUEST['vis']));
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_units WHERE id = ?d", $id)->visible;
        $newvis = ($vis == 1) ? 0 : 1;
        Database::get()->query("UPDATE course_units SET visible = ?d WHERE id = ?d AND course_id = ?d", $newvis, $id, $course_id);
        $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_UNIT, $id);
        $searchEngine->indexResource(ConstantsUtil::REQUEST_STORE, ConstantsUtil::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['access'])) {
        $id = intval(getDirectReference($_REQUEST['access']));
        $access = Database::get()->querySingle("SELECT `public` FROM course_units WHERE id = ?d", $id);
        $newaccess = ($access->public == '1') ? '0' : '1';
        Database::get()->query("UPDATE course_units SET public = ?d WHERE id = ?d AND course_id = ?d", $newaccess, $id, $course_id);
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['down'])) {
        $id = intval(getDirectReference($_REQUEST['down'])); // change order down
        if ($course_info->view_type == 'units' or $course_info->view_type == 'simple') {
            move_order('course_units', 'id', $id, 'order', 'down', "course_id=$course_id");
        }
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['up'])) { // change order up
        $id = intval(getDirectReference($_REQUEST['up']));
        if ($course_info->view_type == 'units' or $course_info->view_type == 'simple') {
            move_order('course_units', 'id', $id, 'order', 'up', "course_id=$course_id");
        }
        redirect_to_home_page("courses/$course_code/");
    }
}

// Student Register to course
if (isset($_REQUEST['register'])) {
    if ($course_info) {
        $allow_reg = $course_info->visible == COURSE_REGISTRATION
                     || $course_info->visible == COURSE_OPEN;
        if ($allow_reg) {
            if (empty($course_info->password) || $course_info->password == $_POST['password']) {
                Database::get()->query("INSERT IGNORE INTO `course_user` (`course_id`, `user_id`, `status`, `reg_date`)
                                    VALUES (?d, ?d, ?d, NOW())", $course_id, $uid, USER_STUDENT);
                Session::flash('message',$langNotifyRegUser1);
                Session::flash('alert-class', 'alert-success');
            } else {
                Session::flash('message',$langInvalidCode);
                Session::flash('alert-class', 'alert-warning');
            }
        }
        redirect_to_home_page("courses/$course_code");
    }
}

$data['tree'] = new Hierarchy();
$course = new Course();

$pageName = ''; // delete $pageName set in doc_init.php
$require_help = TRUE;
$helpTopic = 'course';

add_units_navigation(TRUE);

load_js('bootstrap-calendar');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');
load_js('sortable/Sortable.min.js');

ModalBoxHelper::loadModalBox();

$registerUrl = js_escape($urlAppend . 'modules/course_home/register.php?course=' . $course_code);

// course email notification
if (isset($uid) and isset($_SESSION['status']) and $_SESSION['status'] != USER_GUEST) {
    if (get_mail_ver_status($uid) == EMAIL_VERIFIED) {
        if (isset($_GET['email_un'])) {
            if ($_GET['email_un'] == 1) {
                Database::get()->query("UPDATE course_user SET receive_mail = " . EMAIL_NOTIFICATIONS_DISABLED . " WHERE user_id = ?d AND course_id = ?d", $uid, $course_id);
                Log::record(0, 0, LOG_PROFILE, array(
                    'uid' => $uid,
                    'email_notifications' => 0,
                    'course_title' => $course_info->title
                ));
            } else if ($_GET['email_un'] == 0) {
                Database::get()->query("UPDATE course_user SET receive_mail = " . EMAIL_NOTIFICATIONS_ENABLED . " WHERE user_id = ?d AND course_id = ?d", $uid, $course_id);
                Log::record(0, 0, LOG_PROFILE, array(
                    'uid' => $uid,
                    'email_notifications' => 1,
                    'course_title' => $course_info->title
                ));
            }
        }
    }
}

// For statistics: record login
Database::get()->query("INSERT INTO logins
    SET user_id = ?d, course_id = ?d, ip = ?s, date_time = " . DBHelper::timeAfter(),
    $uid, $course_id, Log::get_client_ip());

// opencourses hits summation
$visitsopencourses = 0;
$hitsopencourses = 0;
if (get_config('opencourses_enable')) {
    $cxml = CourseXMLElement::initFromFile($course_code);
    $reslastupdate = Database::get()->querySingle("select datestamp from oai_record where course_id = ?d and deleted = ?d", $course_id, 0);
    $lastupdate = null;
    if ($reslastupdate) {
        $lastupdate = strtotime($reslastupdate->datestamp);
    }
    if ($cxml && $lastupdate && (time() - $lastupdate > 24 * 60 * 60)) {
        // need to refresh hits when no update occurred during the last 24 hours
        CourseXMLElement::refreshCourse($course_id, $course_code);
        $cxml = CourseXMLElement::initFromFile($course_code);
    }
    $visitsopencourses = ($cxml && $cxml->visits) ? intval((string) $cxml->visits) : 0;
    $hitsopencourses = ($cxml && $cxml->hits) ? intval((string) $cxml->hits) : 0;
}

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_GET['from_search'])) { // if we come from home page search
    header("Location: {$urlServer}modules/search/search_incourse.php?all=true&search_terms=$_GET[from_search]");
}

$visible = $data['visible'] = $course_info->visible;

$res = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
                                    LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
                                    WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $course_id);
$course_descriptions_modals = "";

if (count($res) > 0) {
    foreach ($res as $key => $row) {
        $desctype = intval($row->type) - 1;
        $hidden_id = "hidden_" . $key;
        $next_id = '';
        $previous_id = '';
        if ($key + 1 < count($res)) $next_id = "hidden_" . ($key + 1);
        if ($key > 0) $previous_id = "hidden_" . ($key - 1);

        $course_descriptions_modals .= "<div class='modal fade' id='$hidden_id' tabindex='-1' role='dialog' aria-labelledby='myModalLabel_$key' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                        <div class='modal-title' id='myModalLabel_$key'>" . q($row->title) . "</div>
                                        <button type='button' class='close' data-bs-dismiss='modal' aria-label='$langClose'></button>

                                    </div>
                                    <div class='modal-body' style='max-height: calc(100vh - 210px); overflow-y: auto;'>".
                                        standard_text_escape($row->comments)
                                    ."</div>
                                    <div class='modal-footer'>";
                                        if ($previous_id) {
                                            $course_descriptions_modals .= "<a id='prev_btn' class='btn cancelAdminBtn' data-bs-dismiss='modal' data-bs-toggle='modal' href='#$previous_id'><span class='fa fa-arrow-left'></span></a>";
                                        }
                                        if ($next_id) {
                                            $course_descriptions_modals .= "<a id='next_btn' class='btn cancelAdminBtn' data-bs-dismiss='modal' data-bs-toggle='modal' href='#$next_id'><span class='fa fa-arrow-right'></span></a>";
                                        }
        $course_descriptions_modals .=    "
                                    </div>
                                    </div>
                                </div>
                                </div>";
        $course_info_extra .= "<a class='list-group-item' data-bs-modal='syllabus-prof' data-bs-toggle='modal' data-bs-target='#$hidden_id' href='javascript:void(0);'>".q($row->title) ."</a>";
    }
} else {
    $course_info_extra = "<div class='text-muted'>$langNoInfoAvailable</div>";
}

$data['course_info_popover'] = "<div class='list-group'>$course_info_extra</div>";
$data['course_descriptions_modals'] = $course_descriptions_modals;

if ($course_info->description) {
    $description = standard_text_escape($course_info->description);

    // Text button for read more & read less
    $postfix_truncate_more = "<a href='#' class='more_less_btn'>$langReadMore &nbsp;<span class='fa fa-arrow-down'></span></a>";
    $postfix_truncate_less = "<a href='#' class='more_less_btn'>$langReadLess &nbsp;<span class='fa fa-arrow-up'></span></a>";

    // Create full description text & truncated text
    $data['full_description'] = $description.$postfix_truncate_less;
    $data['truncated_text'] = ellipsize_html($description, 1000, $postfix_truncate_more);
}

// offline course setting
$data['offline_course'] = $offline_course = get_config('offline_course') && (setting_get(SETTING_OFFLINE_COURSE, $course_id));

if (setting_get(SETTING_COURSE_COMMENT_ENABLE, $course_id) == 1) {
    commenting_add_js();
    $comm = new Commenting('course', $course_id);
    $data['comment_content'] = $comm->put($course_code, $is_editor, $uid);
}
if (setting_get(SETTING_COURSE_RATING_ENABLE, $course_id) == 1) {
    $rating = new Rating('fivestar', 'course', $course_id);
    $data['rating_content'] = $rating->put($is_editor, $uid, $course_id);
}
if (is_sharing_allowed($course_id)) {
    if (setting_get(SETTING_COURSE_SHARING_ENABLE, $course_id) == 1) {
        $data['social_content'] = print_sharing_links($urlServer."courses/$course_code", $currentCourseName);
    }
}

$data['course_descriptions'] = $res;
$data['courseDescriptionVisible'] = $courseDescriptionVisible = count($res);

$data['departments'] = $course->getDepartmentIds($course_id);
if ($is_course_admin) {
    $data['numUsers'] = $numUsers = Database::get()->querySingle("SELECT COUNT(user_id) AS numUsers
                FROM course_user
                WHERE course_id = ?d", $course_id)->numUsers;

} else if (setting_get(SETTING_USERS_LIST_ACCESS, $course_id) == 1) {
    $data['numUsers'] = $numUsers = Database::get()->querySingle("SELECT COUNT(*) AS numUsers FROM course_user, user
                WHERE `user`.`id` = `course_user`.`user_id`
                AND user.expires_at > " . DBHelper::timeAfter() . "
                AND `course_user`.`course_id` = ?d", $course_id)->numUsers;
} else {
    $data['numUsers'] = $numUsers = '';
}

$data['lessonStatus'] = course_access_icon($visible);

$data['action_bar'] = action_bar([
    [   'title' => $langDescription,
        'url' => "{$urlAppend}modules/course_home/editdesc.php?course=$course_code",
        'icon' => 'fa-pen-to-square',
        'show' => $is_editor
    ],
    [
        'title' => "$numUsers $langRegistered",
        'url' => "{$urlAppend}modules/user/index.php?course=$course_code",
        'icon' => 'fa-users',
        'level' => 'primary',
        'show' => ($uid && ($is_course_admin || $course_users_permission))
    ],
    [
        'title' => "$numUsers $langRegistered",
        'url' => "{$urlAppend}modules/user/userslist.php?course=$course_code",
        'icon' => 'fa-users',
        'show' => ($uid && !$is_course_admin && !$course_users_permission && (setting_get(SETTING_USERS_LIST_ACCESS, $course_id) == 1))
    ],
    [
        'title' => $langUserEmailNotification,
        'url' => "{$urlAppend}modules/course_home/course_home.php?course=$course_code&amp;email_un=1",
        'icon' => 'fa-envelope',
        'show' => ($uid && get_user_email_notification($uid, $course_id)),
        'link-attrs' => "id='email_notification'"
    ],
    [
        'title' => $langNoUserEmailNotification,
        'url' => "{$urlAppend}modules/course_home/course_home.php?course=$course_code&amp;email_un=0",
        'icon' => 'fa-triangle-exclamation',
        'show' => ($uid && !(get_user_email_notification($uid, $course_id))),
        'link-attrs' => "id='email_notification'"
    ],
    [
        'title' => $langUsage,
        'url' => "{$urlAppend}modules/usage/index.php?course=$course_code",
        'icon' => 'fa-chart-line',
        'show' => ($uid && $is_course_reviewer && !$is_course_admin)
    ],
    [
        'title' => $langCourseParticipation,
        'url' => "{$urlAppend}modules/usage/userduration.php?course=$course_code&u=$uid",
        'icon' => 'fa-chart-line',
        'show' => ($uid && !$is_course_reviewer)
    ],
    [
        'title' => $langDownloadCourse,
        'url' => "{$urlAppend}modules/offline/index.php?course=$course_code",
        'icon' => 'fa-download',
        'show' => $offline_course
    ],
    [
        'title' => $langCourseTools,
        'url' => "{$urlAppend}modules/course_tools/index.php?course=$course_code",
        'icon' => 'fa-screwdriver-wrench',
        'show' => ($uid && !$is_course_admin && $allow_course_tools)
    ],
    [
        'title' => $langBackupCourse,
        'url' => "{$urlAppend}modules/course_info/archive_course.php?course=$course_code&" . generate_csrf_token_link_parameter(),
        'icon' => 'fa-archive',
        'show' => ($uid && !$is_course_admin && $allow_course_backup)
    ],
    [
        'title' => $langCloneCourse,
        'url' => "{$urlAppend}modules/course_info/clone_course.php?course=$course_code",
        'icon' => 'fa-archive',
        'show' => ($uid && !$is_course_admin && $allow_clone)
    ],
    [
        'title' => $langCitation,
        'url' => "javascript:void(0);",
        'link-attrs' => "data-bs-modal='citation' data-bs-toggle='modal' data-bs-target='#citation'",
        'icon' => 'fa-link'
    ],
    [
        'title' => $langHelp,
        'url' => "{$urlServer}modules/help/help.php?language=$language&topic=$helpTopic",
        'link-attrs' => "id='help-btn'",
        'icon' => 'fa-circle-question'
    ]
]);

if ($uid) {
    $data['course_completion_id'] = $course_completion_id = is_course_completion_active(); // is course completion active?
    if ($course_completion_id) {
        if ($is_editor) {
            $data['studentUsers'] = $student_users = Database::get()->querySingle("SELECT COUNT(*) AS studentUsers FROM course_user
                                        WHERE status = " .USER_STUDENT . "
                                            AND editor = 0
                                            AND course_id = ?d", $course_id)->studentUsers;
            $data['certified_users'] = Database::get()->querySingle("SELECT COUNT(*) AS t FROM user_badge
                                                              JOIN course_user ON user_badge.user=course_user.user_id
                                                                    AND status = " .USER_STUDENT . "
                                                                    AND editor = 0
                                                                    AND course_id = ?d
                                                                    AND completed = 1
                                                                    AND badge = ?d", $course_id, $course_completion_id)->t;
            if ($student_users == 0) {
                $data['percentage_t'] = $percentage_t = 0;
                $data['angle'] = 0;
            } else {
                $data['percentage_t'] = $percentage_t = round($data['certified_users'] / $data['studentUsers'] * 100, 0);
                $data['angle'] = $percentage_t * 100 / 360;
            }
        } else {
            $course_completion_status = has_certificate_completed($uid, 'badge', $course_completion_id);
            $data['percentage'] = $percentage = get_cert_percentage_completion('badge', $course_completion_id);
        }
    }
}


// display open-courses level in bar
$level = ($levres = Database::get()->querySingle("SELECT level FROM course_review WHERE course_id =  ?d", $course_id)) ? CourseXMLElement::getLevel($levres->level) : false;
$data['level'] = $level;
if (isset($level) && !empty($level)) {
    $metadataUrl = $urlServer . 'modules/course_metadata/info.php?course=' . $course_code;
    $data['opencourses_level'] = "
        <div class='row px-0'>
            <div class='col-12 d-flex justify-content-center'>
                <img class='img-responsive center-block' src='$themeimg/open_courses_logo_small.png' title='" . $langOpenCourses . "' alt='" . $langOpenCourses . "' />
            </div>
            <div class='col-12 mt-4'>
                <div style='border-bottom:1px solid #ccc; margin-bottom: 5px;'>$langOpenCoursesLevel: $level</div>
                <p class='not_visible'>
                <small>$langVisitsShort : &nbsp;$visitsopencourses</small>
                <br />
                <small>$langHitsShort : &nbsp;$hitsopencourses</small>
                </p>
            </div>
        </div>";
    $data['opencourses_level_footer'] = "
        <div class='row px-0'>
            <div class='col-12 text-center'>
                <small><a href='javascript:showMetadata(\"$course_code\");'>$langCourseMetadata</a>".icon('fa-tags', $langCourseMetadata, "javascript:showMetadata(\"$course_code\");")."</small>
            </div>
        </div>";
}

if ($is_editor) {
    warnCourseInvalidDepartment(true);
}

if ($is_editor or $is_course_reviewer) { // teacher or course reviewer
    $data['last_id'] = $last_id = Database::get()->querySingle("SELECT id FROM course_units
                                                   WHERE course_id = ?d AND `order` >= 0
                                                   ORDER BY `order` DESC LIMIT 1", $course_id);
    if ($last_id) {
        $last_id = $last_id->id;
    }
    $query = "SELECT id, title, start_week, finish_week, comments, visible, public, `order`, assign_to_specific FROM course_units WHERE course_id = ?d AND `order` >= 0 ORDER BY `order`";
} else { // student
    $query = "SELECT id, title, start_week, finish_week, comments, visible, public, `order`, assign_to_specific FROM course_units WHERE course_id = ?d AND (visible = 1 OR visible = 2) AND `order` >= 0 ORDER BY `order`";
}

$data['all_units'] = $all_units = Database::get()->queryArray($query, $course_id);

foreach ($all_units as $unit) {
    check_unit_progress($unit->id);  // check unit completion - call to Game.php
}

$visible_units_id = [];
if (!$is_editor && !$is_course_reviewer) {
    $visible_user_units = findUserVisibleUnits($uid, $all_units);
    foreach ($visible_user_units as $d) {
        $visible_units_id[] = $d->id;
    }
}

/****************** CAROUSEL OR ROW UNITS PREFERENCE ******************/
if ($is_editor) {
    if (isset($_GET['viewUnit'])){
        Database::get()->query("UPDATE course SET view_units = ?d  WHERE id = ?d", $_GET['viewUnit'], $course_id);
    }
}
$show_course = Database::get()->querySingle("SELECT view_units FROM course WHERE id =  ?d", $course_id);
$carousel_or_row = $show_course->view_units;
/***************************************************************************/

/** Quick Poll */
$data['displayQuickPoll'] = $displayQuickPoll = Database::get()->querySingle("SELECT * FROM poll
                            WHERE display_position = ?d
                            AND type = ?d
                            AND course_id = ?d
                            AND CURRENT_TIMESTAMP BETWEEN start_date AND end_date
                            ORDER BY pid DESC", 1, 3, $course_id);

if ($displayQuickPoll) {
    $data['pid'] = $pid = $displayQuickPoll->pid;
    $data['multiple_submissions'] = $multiple_submissions = $displayQuickPoll->multiple_submissions;
    $data['show_results'] = $show_results = $displayQuickPoll->show_results;
    $data['has_participated'] = $has_participated = Database::get()->querySingle("SELECT COUNT(*) AS count FROM poll_user_record WHERE uid = ?d AND pid = ?d", $uid, $pid)->count;
    $data['theQuestion'] = $theQuestion = Database::get()->querySingle("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position ASC", $pid);

    if ($theQuestion) {
        $data['pqid'] = $pqid = $theQuestion->pqid;
        $data['qtype'] = $qtype = $theQuestion->qtype;
        $user_answers = null;

        if ($qtype == QTYPE_SINGLE || $qtype == QTYPE_MULTIPLE) {
            $user_answers = Database::get()->queryArray("SELECT a.aid
                                FROM poll_user_record b, poll_answer_record a
                                LEFT JOIN poll_question_answer c
                                    ON a.aid = c.pqaid
                                WHERE a.poll_user_record_id = b.id
                                    AND a.qid = ?d
                                    AND b.uid = ?d", $pqid, $uid);
        }
        $answers = Database::get()->queryArray("SELECT * FROM poll_question_answer
                                WHERE pqid = ?d ORDER BY pqaid", $pqid);
        $name_ext = ($qtype == QTYPE_SINGLE)? '': '[]';
        $type_attr = ($qtype == QTYPE_SINGLE)? "radio": "checkbox";

        if ($show_results) {
            load_js('d3/d3.min.js');
            load_js('c3-0.7.20/c3.min.js');
            $default_answer = $displayQuickPoll->default_answer;
            $head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.7.20/c3.css' />";

            $names_array = [];
            $all_answers = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d", $theQuestion->pqid);
            foreach ($all_answers as $row) {
                $this_chart_data['answer'][] = q($row->answer_text);
                $this_chart_data['percentage'][] = 0;
                $this_chart_data['count'][] = 0;
            }
            $set_default_answer = false;
            $answers_r = Database::get()->queryArray("SELECT a.aid AS aid, MAX(b.answer_text) AS answer_text, count(a.aid) AS count
                            FROM poll_user_record c, poll_answer_record a
                            LEFT JOIN poll_question_answer b
                            ON a.aid = b.pqaid
                            WHERE a.qid = ?d
                            AND a.poll_user_record_id = c.id
                            AND (c.email_verification = 1 OR c.email_verification IS NULL)
                            GROUP BY a.aid ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid);
            $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record, poll_user_record
                                                                        WHERE poll_user_record_id = id
                                                                        AND (email_verification=1 OR email_verification IS NULL)
                                                                        AND qid= ?d", $theQuestion->pqid)->total;

            foreach ($answers_r as $answer) {
                $percentage_r = round(100 * ($answer->count / $answer_total),2);
                if (isset($answer->answer_text)) {
                    $q_answer = q_math($answer->answer_text);
                    $aid = $answer->aid;
                } else {
                    $q_answer = $langPollUnknown;
                    $aid = -1;
                }
                if (!$set_default_answer and (($theQuestion->qtype == QTYPE_SINGLE && $default_answer) or $aid == -1)) {
                    $this_chart_data['answer'][] = $langPollUnknown;
                    $this_chart_data['percentage'][] = 0;
                }

                if (isset($this_chart_data['answer'])) { // skip answers that don't exist
                    $this_chart_data['percentage'][array_search($q_answer,$this_chart_data['answer'])] = $percentage_r;
                }

                $this_chart_data['count'][array_search($q_answer,$this_chart_data['answer'])] = $answer->count;
            }
            $data['this_chart_data'] = $this_chart_data;

        }

        $quick_poll_answers_content = '';
        $labelContainer = '';
        $checkMark = '';
        foreach ($answers as $theAnswer) {
            $checked = '';
            if ($user_answers) {
                if (count($user_answers) > 1) { // multiple answers
                    foreach ($user_answers as $ua) {
                        if ($ua->aid == $theAnswer->pqaid) {
                            $checked = 'checked';
                        }
                    }
                } else {
                    if (count($user_answers) == 1) { // single answer
                        if ($user_answers[0]->aid == $theAnswer->pqaid) {
                            $checked = 'checked';
                        }
                    }
                }
            }
            if($type_attr == 'checkbox'){
                $labelContainer = 'label-container';
                $checkMark = '<span class="checkmark"></span>';
            }else{
                $labelContainer = '';
                $checkMark = '';
            }
            $quick_poll_answers_content .= "
                    <div class='form-group'>
                        <div class='col-sm-12'>
                            <div class='$type_attr'>
                                <label class='$labelContainer' aria-label='$langSelect'>
                                    <input type='$type_attr' name='answer[$pqid]$name_ext' value='$theAnswer->pqaid' $checked>
                                    $checkMark
                                    ".q_math($theAnswer->answer_text)."

                                </label>
                            </div>
                        </div>
                    </div>";
        }
        $data['quick_poll_answers_content'] = $quick_poll_answers_content;
    }
}

/** end of quick poll --------------------------- */


$total_cunits = count($all_units);
$data['total_cunits'] = $total_cunits;
if ($total_cunits > 0) {
    $cunits_content .= "";
    $count_index = 0;
    $counterUnits = 0;
    $countUnits = count($all_units);
    $data['countUnits'] = $countUnits;

    if($carousel_or_row == 0) {
        $cunits_content .= "<div class='card panelCard card-default px-lg-2 py-lg-2 h-100'><div class='card-body'><div id='carouselUnitsControls' class='carousel slide' data-bs-ride='carousel'>";

        //this is foreach for indicator carousel-units
        $counterIndicator = 0;

        $cunits_content .=  "<div class='carousel-indicators h-auto mb-1'>";
        foreach ($all_units as $cu) {
            if($counterIndicator == 0){
                $cunits_content .=  "<button type='button' data-bs-target='#carouselUnitsControls' data-bs-slide-to='$counterIndicator' class='active' aria-current='true' aria-label='Carousel'></button>";
            }else{
                $cunits_content .=  "<button type='button' data-bs-target='#carouselUnitsControls' data-bs-slide-to='$counterIndicator' aria-current='true' aria-label='Carousel'></button>";
            }
            $counterIndicator++;
        }
        $cunits_content .=  "</div>";

        $cunits_content .= "<div class='carousel-inner'>";
        foreach ($all_units as $cu) {
            $access = $cu->public;
            $vis = $cu->visible;
            if ($vis == 2) { // don't display divider
                continue;
            }
            $not_shown = false;
            $icon = '';
            if (!$is_editor && !$is_course_reviewer) {
                if (!has_access_to_units($cu->id, $cu->assign_to_specific, $uid)) { // unit has assigned to users or groups?
                    $not_shown = true;
                } else if (!(is_null($cu->start_week)) and (date('Y-m-d') < $cu->start_week)) { // unit has started?
                    $not_shown = true;
                    $icon = icon('fa-clock fa-md', $langUnitNotStarted);
                } else if (!in_array($cu->id, $visible_units_id)) { //  has completed units (if any)?
                    $not_shown = true;
                    $icon = icon('fa-minus-circle fa-md', $langUnitNotCompleted);
                } else {
                    if (in_array($cu->id, $visible_units_id)) {
                        $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND unit_id = ?d", $course_id, $cu->id);
                        if ($sql_badge) {
                            $badge_id = $sql_badge->id;
                            $per = get_cert_percentage_completion('badge', $badge_id);
                            if ($per == 100) {
                                $icon = icon('fa-check-circle fa-md', $langInstallEnd);
                            } else {
                                $icon = icon('fa-hourglass-2 fa-md', $per . "%");
                            }
                        }
                    }
                }
            }
            // check visibility
            if ($vis == 1) {
                $count_index++;
            }
            $class_vis = ($vis == 0 or $not_shown) ? 'not_visible' : '';
            $cu_indirect = getIndirectReference($cu->id);

            if($counterUnits == 0){
                $cunits_content .= "<div class='carousel-item active'>";
            }else{
                $cunits_content .= "<div class='carousel-item'>";
            }

            $cunits_content .= "<div id='unit_$cu_indirect' class='col-12' data-id='$cu->id'><div class='panel clearfix'><div class='col-12'>
                <div class='item-content mb-2'>
                    <div class='item-header clearfix'>
                        <div class='item-title d-flex justify-content-between $class_vis gap-3'>";

            $cunits_content .= "<div class='item-title-container d-flex flex-column justify-content-center'>";
            if ($not_shown) {
                $cunits_content .= q($cu->title) ;
            } else {
                $unit_legend = '';
                if ($is_editor) {
                    $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND unit_id = ?d", $course_id, $cu->id);
                    if ($sql_badge) {
                        $unit_legend = "&nbsp;&nbsp;<span class='fas fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-html='true' data-bs-title='$langUnitCompletionLegend' data-bs-original-title='' title=''>";
                    }
                }
                $cunits_content .= "<div class='line-height-default'><a class='TextBold fs-6 $class_vis' href='{$urlServer}modules/units/index.php?course=$course_code&amp;id=$cu->id'>" . q($cu->title) . "</a>$unit_legend</div>";
            }

            $cunits_content .= "<p><small><span class='help-block'>";
            if (!(is_null($cu->start_week))) {
                $cunits_content .= "$langFrom2 " . format_locale_date(strtotime($cu->start_week), 'short', false);
            }
            if (!(is_null($cu->finish_week))) {
                $cunits_content .= " $langTill " . format_locale_date(strtotime($cu->finish_week), 'short', false);
            }
            $cunits_content .= "</span></small></p>";

            $cunits_content .= "</div>";

            if ($is_editor) {
                $cunits_content .= "<span class='float-end d-flex justify-content-center text-end'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                            'url' => $urlAppend . "modules/units/info.php?course=$course_code&amp;edit=$cu->id",
                            'icon' => 'fa-edit'),
                        array('title' => $vis == 1? $langViewHide : $langViewShow,
                            'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;vis=$cu_indirect",
                            'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $access == 1? $langResourceAccessLock : $langResourceAccessUnlock,
                            'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;access=$cu_indirect",
                            'icon' => $access == 1? 'fa-lock' : 'fa-unlock',
                            'show' => $visible == COURSE_OPEN),
                        array('title' => $langDelete,
                            'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;del=$cu_indirect&amp;order=".$cu->order,
                            'icon' => 'fa-solid fa-xmark Accent-200-cl',
                            'class' => 'delete',
                            'confirm' => $langCourseUnitDeleteConfirm))) .
                    "</span>";
            } else {
                $cunits_content .= "<span class='float-end d-flex justify-content-center mb-3 text-end'>";
                $cunits_content .= $icon;
                $cunits_content .= "</span>";
            }


            $cunits_content .= "</div>";
            $cunits_content .= "</div>
                <div class='item-body" . ($cu->comments ? " mt-3" : "") . "'>";
            if (!is_null($cu->comments)) {
                $cunits_content .= standard_text_escape($cu->comments);
            }
            $cunits_content .= "</div>";

            $cunits_content .= "<div style='height:1px;' class='border-top-default mt-3 mb-3'></div>
                        <div class='col-sm-12 bg-transparent'>

                            <button class='carousel-prev-btn' type='button' data-bs-target='#carouselUnitsControls' data-bs-slide='prev' aria-label='Carousel previous'>
                                <i class='fa-solid fa-chevron-circle-left fa-xl'></i>
                            </button>";

            $cunits_content .=  "<button class='carousel-next-btn float-end' type='button' data-bs-target='#carouselUnitsControls' data-bs-slide='next' aria-label='Carousel next'>
                                    <i class='fa-solid fa-chevron-circle-right fa-xl'></i>
                            </button>

                        </div>";

            $cunits_content .= "</div></div></div></div></div>";
            $counterUnits++;
        }

        // end carousel-inner
        $cunits_content .= "</div>";

        //end courseUnitsControls
        $cunits_content .= "</div></div></div>";
    } else {
        $counter_hr = 0;
        foreach ($all_units as $cu) {
            $counter_hr++;
            $not_shown = false;
            $icon = '';

            if (!$is_editor && !$is_course_reviewer) {
                if (!has_access_to_units($cu->id, $cu->assign_to_specific, $uid)) { // unit has assigned to users or groups ?
                    $not_shown = true;
                } else if (!(is_null($cu->start_week)) and (date('Y-m-d') < $cu->start_week)) { // unit has started ?
                    $not_shown = true;
                    $icon = icon('fa-clock fa-md', $langUnitNotStarted);
                } else if (!in_array($cu->id, $visible_units_id)) { //  has completed units (if any) ?
                    $not_shown = true;
                    $icon = icon('fa-minus-circle fa-md', $langUnitNotCompleted);
                } else {
                    if (in_array($cu->id, $visible_units_id)) {
                        $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND unit_id = ?d", $course_id, $cu->id);
                        if ($sql_badge) {
                            $badge_id = $sql_badge->id;
                            $per = get_cert_percentage_completion('badge', $badge_id);
                            if ($per == 100) {
                                $icon = icon('fa-check-circle fa-md', $langInstallEnd);
                            } else {
                                $icon = icon('fa-hourglass-2 fa-md', $per . "%");
                            }
                        }
                    }
                }
            }
            // check visibility
            if ($cu->visible == 1) {
                $count_index++;
            }
            $access = $cu->public;
            $vis = $cu->visible;
            $class_vis = ($vis == 0 or $not_shown) ? 'not_visible' : '';
            $cu_indirect = getIndirectReference($cu->id);
            $legendViewContent = '';
            if ($counter_hr < $countUnits){
                $legendViewContent = 'legendViewContent';
            }

            $cunits_content .= "<div id='unit_$cu_indirect' class='col-12 $legendViewContent my-3' data-id='$cu->id'>";
            if ($vis == 2) {
                $cunits_content .= "<div class='px-lg-2 py-lg-2 h-100'><div class='card-body'>";
            } else {
                $cunits_content .= "<div class='card panelCard card-default px-lg-2 py-lg-2 h-100'><div class='card-body'>";
            }
            $cunits_content .= "<div class='item-content'>
                        <div class='item-header clearfix'>
                            <div class='item-title d-flex justify-content-between $class_vis gap-3'>";

            $cunits_content .= "<div class='item-title-container d-flex flex-column justify-content-center'>";
            if ($not_shown) {
                $cunits_content .= q($cu->title);
            } else {
                if ($vis == 2) {
                    $cunits_content .= "<div class='line-height-default'><div class='TextBold fs-6' style='margin-left:-25px;'>" . q($cu->title) . "</div></div>";
                } else {
                    $unit_legend = '';
                    if ($is_editor) {
                        $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND unit_id = ?d", $course_id, $cu->id);
                        if ($sql_badge) {
                            $unit_legend = "&nbsp;&nbsp;<span class='fas fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-html='true' data-bs-title='$langUnitCompletionLegend' data-bs-original-title='' title=''>";
                        }
                    }
                    $cunits_content .= "<div class='line-height-default'><a class='TextBold fs-6 $class_vis' href='{$urlServer}modules/units/index.php?course=$course_code&amp;id=$cu->id'>" . q($cu->title) . "</a>$unit_legend</div>";
                }
            }

            if (!(is_null($cu->start_week)) || !(is_null($cu->finish_week))) {
                $cunits_content .= "<p><span class='help-block $class_vis'>";
                if (!(is_null($cu->start_week))) {
                    $cunits_content .= "$langFrom2 " . format_locale_date(strtotime($cu->start_week), 'short', false);
                }
                if (!(is_null($cu->finish_week))) {
                    $cunits_content .= " $langTill " . format_locale_date(strtotime($cu->finish_week), 'short', false);
                }
                $cunits_content .= "</span></p>";
            }

            $cunits_content .= "</div>";

            if ($is_editor) {
                $cunits_content .= "<span class='float-end d-flex justify-content-center align-items-center " . ($carousel_or_row < 2 ? "" : "") . "'>
                                        <span class='reorder-btn me-3'>
                                            <span class='fa fa-arrows' data-bs-toggle='tooltip' data-bs-placement='top' title='$langReorder' style='cursor: grab;'></span>
                                        </span>

                                        ".action_button(array(
                                                array('title' => $langEditChange,
                                                    'url' => $urlAppend . "modules/units/info.php?course=$course_code&amp;edit=$cu->id" . (($vis == 2) ? "&amp;divider=1" : ""),
                                                    'icon' => 'fa-edit'),
                                                array('title' => ($vis == 1)? $langViewHide : $langViewShow,
                                                    'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;vis=$cu_indirect",
                                                    'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye',
                                                    'show' => ($vis != 2)),
                                                array('title' => $access == 1? $langResourceAccessLock : $langResourceAccessUnlock,
                                                    'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;access=$cu_indirect",
                                                    'icon' => $access == 1? 'fa-lock' : 'fa-unlock',
                                                    'show' => $visible == COURSE_OPEN),
                                                array('title' => $langDelete,
                                                    'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;del=$cu_indirect&amp;order=".$cu->order,
                                                    'icon' => 'fa-solid fa-xmark Accent-200-cl',
                                                    'class' => 'delete',
                                                    'confirm' => $langCourseUnitDeleteConfirm))).
                                    "</span>";
            } else {
                $cunits_content .= "<span class='float-end d-flex justify-content-center align-items-center mb-3'>";
                    $cunits_content .= $icon;
                $cunits_content .= "</span>";
            }
            $cunits_content .= "</div>";
            $cunits_content .= "</div>";
            if ($carousel_or_row == 1) {
                $cunits_content .= "<div class='item-body $class_vis" . ($cu->comments ? " mt-3" : "") . "'>";
                $cunits_content .= ($cu->comments == ' ')? '': standard_text_escape($cu->comments);
                $cunits_content .= "</div>";
            }
            $cunits_content .= "</div></div></div></div>";


        }
    }
} else {
    if ($is_editor) {
        $cunits_content .= "<div class='col-12 text-center'>
                                <div class='alert alert-warning'>" . $langNoUnits . "</div>
                            </div>";
    }
}

$data['cunits_content'] = $cunits_content;

if (($total_cunits > 0 or $is_editor) and ($course_info->view_type != 'simple')) {
    $data['alter_layout'] = $alter_layout = FALSE;
    $data['cunits_sidebar_columns'] = $cunits_sidebar_columns = 4;
    $data['cunits_sidebar_subcolumns'] = $cunits_sidebar_subcolumns = 12;
} else {
    $data['alter_layout'] = $alter_layout = TRUE;
    $data['cunits_sidebar_columns'] = $cunits_sidebar_columns = 12;
    $data['cunits_sidebar_subcolumns'] = $cunits_sidebar_subcolumns = 6;
}

$data['course_home_main_area_widgets'] = '';
$course_home_page_main = new WidgetArea(COURSE_HOME_PAGE_MAIN);
foreach ($course_home_page_main->getCourseAndAdminWidgets($course_id) as $key => $widget) {
    $data['course_home_main_area_widgets'] .= $widget->run($key);
}


//BEGIN - Get user personal calendar
$today = getdate();
$day = $today['mday'];
$month = $today['mon'];
$year = $today['year'];
if (isset($uid)) {
    Calendar_Events::get_calendar_settings();
}
$data['user_personal_calendar'] = Calendar_Events::small_month_calendar($day, $month, $year);
//END - Get personal calendar

$data['course_home_sidebar_widgets'] = '';
$course_home_page_sidebar = new WidgetArea(COURSE_HOME_PAGE_SIDEBAR);
foreach ($course_home_page_sidebar->getCourseAndAdminWidgets($course_id) as $key => $widget) {
    $data['course_home_sidebar_widgets'] .= $widget->run($key);
}

$data['registered'] = false;
if ($uid) {
    $myCourses = [];
    Database::get()->queryFunc("SELECT course.code  course_code, course.public_code public_code,
                                        course.id course_id, status
                                        FROM course_user, course
                                        WHERE course_user.course_id = course.id
                                        AND user_id = ?d", function ($course) use (&$myCourses) {
                                            $myCourses[$course->course_id] = $course;
                                        }, $uid);
    if (!$is_editor && !in_array($course_id, array_keys($myCourses))) {
        $data['action_bar'] = action_bar([[
            'title' => trans('langRegister'),
            'url' => $urlAppend . "modules/course_home/register.php?course=$course_code",
            'icon' => 'fa-check',
            'link-attrs' => "id='passwordModal'",
            'level' => 'primary-label',
            'button-class' => 'btn-success']]);
    } else {
        $data['registered'] = true;
    }
}

/////////////////////////////////////////////// Regarding course sessions ///////////////////////////////////////////////////

$limit = "LIMIT 1";
$sql_session = "";
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));
$data['next_session'] = array();
$data['course_sessions'] = array();
if($course_info->view_type == 'sessions' && isset($_SESSION['uid'])){
    if($is_consultant && !$is_coordinator){
        $sql_session = "AND creator = $uid";
    }elseif($is_simple_user){
        $sql_session = "AND id IN (SELECT session_id FROM mod_session_users
                                    WHERE participants = $uid AND is_accepted = 1)";
    }
    if(($is_consultant && !$is_coordinator) or ($is_simple_user)){
        $data['next_session'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                WHERE course_id = ?d
                                                                AND start > NOW()
                                                                AND visible = 1
                                                                $sql_session
                                                                ORDER BY start ASC $limit",$course_id);
    }elseif($is_coordinator){
        // Get the minimum datetime from the current date
        $minDate = Database::get()->querySingle("SELECT MIN(start) AS st FROM mod_session
                                                WHERE course_id = ?d
                                                AND start > NOW()
                                                AND visible = 1", $course_id);

        if($minDate){
            $data['next_session'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                WHERE course_id = ?d
                                                                AND start = ?t", $course_id, $minDate->st);
        }
    }

    $data['course_sessions'] = $course_sessions = Database::get()->queryArray("SELECT * FROM mod_session
                                                            WHERE course_id = ?d
                                                            AND visible = ?d
                                                            $sql_session
                                                            ORDER BY start ASC",$course_id,1);
}

// Hide agenda, announcements, course completion and widgets on course home
$right_col_display = false;
$hidden_sql = setting_get(SETTING_AGENDA_ANNOUNCEMENT_COURSE_COMPLETION, $course_id);
if ($hidden_sql) {
    $right_col_display = true;
}
$data['right_col_display'] = $right_col_display;

view('modules.course.home.index', $data);

/**
 * @brief check if user has access to unit if it is assigned to specific users or groups
 * @param $unit_id
 * @return bool
 */
function has_access_to_units($unit_id, $assign_to_specific, $user_id)
{
    switch ($assign_to_specific) {
        case 0:
            return true;
        case 1:
            $q = Database::get()->querySingle("SELECT user_id FROM course_units_to_specific WHERE unit_id = ?d AND user_id = ?d", $unit_id, $user_id);
            if ($q) {
                return true;
            } else {
                return false;
            }
        case 2:
            $unit_to_group_ids = Database::get()->queryArray("SELECT group_id FROM course_units_to_specific WHERE unit_id = ?d", $unit_id);
            foreach ($unit_to_group_ids as $g) {
                $q = Database::get()->querySingle("SELECT * FROM group_members WHERE group_id = ?d AND user_id = ?d", $g->group_id, $user_id);
                if ($q) {
                    return true;
                }
            }
            return false;
    }
}


/**
 * @brief fetch course announcements
 * @return string
 */
function course_announcements() {
    global $course_id, $course_code, $langNoAnnounce, $urlAppend, $indexOfAnnounce;

    $type_course = '';
    if (visible_module(MODULE_ID_ANNOUNCE)) {
        $q = Database::get()->queryArray("SELECT title, `date`, id
                            FROM announcement
                            WHERE course_id = ?d
                                AND visible = 1
                                AND (start_display <= NOW() OR start_display IS NULL)
                                AND (stop_display >= NOW() OR stop_display IS NULL)
                            ORDER BY `date` DESC LIMIT 5", $course_id);

        $typeViewOfCourse = Database::get()->queryArray("SELECT view_type FROM course WHERE id = ?d", $course_id);
        foreach($typeViewOfCourse as $t) {
            $type_course = $t->view_type;
        }
        if ($type_course == 'simple') {
            $indexOfAnnounce = 5;
        } else {
            $indexOfAnnounce = 3;
        }


        if ($q) { // if announcements exist
            $ann_content = '';
            $counter_ann = 1;
            foreach ($q as $ann) {
                if($counter_ann <= $indexOfAnnounce){
                $ann_url = $urlAppend . "modules/announcements/index.php?course=$course_code&amp;an_id=" . $ann->id;
                $ann_date = format_locale_date(strtotime($ann->date));
                $ann_content .= "<li class='list-group-item element'>
                                    <div class='line-height-default'><a class='TextBold' href='$ann_url'>" . q(ellipsize($ann->title, 60)) ."</a></div>
                                    <div class='TextRegular Neutral-900-cl'>$ann_date</div>
                                </li>";
                }
                $counter_ann++;
            }
            return $ann_content;
        }
    }
    return "<li style='list-style-type: none;' class='list-item pt-3 pb-3'><span class='item-wholeline'><div class='text-title text-start not_visible'> - $langNoAnnounce - </div></span></li>";
}
