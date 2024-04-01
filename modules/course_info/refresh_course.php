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
 * @brief course clean up
 */

$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_login = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/work/functions.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/log.class.php';

$from_user = false;
if (isset($_REQUEST['from_user'])) { // checks if we are coming from /modules/user/index.php
    $from_user = true;
}

$url = "$_SERVER[SCRIPT_NAME]?course=$course_code";

if (!$from_user) {
    $toolName = $langCourseInfo;
    $pageName = $langRefreshCourse;
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langCourseInfo);
} else {
    $toolName = $langUsers;
    $pageName = $langDelUsers;
    $navigation[] = array('url' => "../user/index.php?course=$course_code", 'name' => $langUsers);
    $url .= "&from_user=true";
}

$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "$url",
        'class' => 'back_btn',
        'icon' => 'fa-reply',
        'level' => 'primary'
    )));


if (isset($_POST['reg_flag'])) {
    $reg_flag = $_POST['reg_flag'];
} else {
    $reg_flag = 'before';
}
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    $output = array();
    if (isset($_POST['delusersdate']) or isset($_POST['delusersdept']) or
        isset($_POST['delusersid']) or isset($_POST['delusersinactive'])) {
            $output[] = delete_users();
    }
    if (isset($_POST['delannounces'])) {
        $output[] = delete_announcements();
    }
    if (isset($_POST['delagenda'])) {
        $output[] = delete_agenda();
    }
    if (isset($_POST['hideworks'])) {
        $output[] = hide_work();
    }
    if (isset($_POST['delworkssubs'])) {
        $output[] = del_work_subs();
    }
    if (isset($_POST['hideexercises'])) {
        $output[] = hide_exercises();
    }
    if (isset($_POST['purgeexercises'])) {
        $output[] = purge_exercises();
    }
    if (isset($_POST['clearstats'])) {
        $output[] = clear_stats();
    }
    if (isset($_POST['delwallposts'])) {
        $output[] = del_wall_posts();
    }
    if (isset($_POST['delblogposts'])) {
        $output[] = del_blog_posts();
    }

    if (($count_events = count($output)) > 0) {
        $data['count_events'] = $count_events;
        $data['output'] = $output;
        $data['menuTypeID'] = 2;
        view('modules.course_info.refresh_course_results', $data);
    }
} else { // display form
    load_js('jstree3');
    $tree = new Hierarchy();
    list($js, $html) = $tree->buildUserNodePicker(array('multiple' => true));
    $head_content .= $js;
    $data['buildusernode'] = $html;
    $data['reg_flag'] = $reg_flag;

    $data['selection_date'] = selection(array('before' => $langBefore, 'after' => $langAfter), 'reg_flag', $reg_flag);
    $data['selection_department'] = selection(array('yes' => $langWithDepartment, 'no' => $langWithoutDepartment), 'dept_flag', 'yes');
    $data['selection_am'] = selection(array('am' => $langWithStudentId, 'uname' => $langWithUsernames), 'id_flag', 'am');
    $data['date_format'] = date("d-m-Y", time());

    $data['form_url'] = "$_SERVER[SCRIPT_NAME]?course_code=$course_code";
    $data['form_url_from_user'] = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;from_user=true";

    $data['menuTypeID'] = 2;
    view('modules.course_info.refresh_course', $data);
}

/**
 * @brief unregister users from course
 * @return string
 */
function delete_users() {
    global $course_id, $langUsersDeleted;

    $details = array('multiple' => true, 'params' => array());

    if (isset($_POST['delusersdept']) and isset($_POST['dept_flag']) and isset($_POST['department'])) {
        $filter_department = true;
        $sql_department = 'LEFT JOIN user_department ON user.id = user_department.user';
    } else {
        $filter_department = false;
        $sql_department = '';
    }

    $sql = 'SELECT user.id AS user_id
        FROM course_user, user ' . $sql_department . '
        WHERE course_user.user_id = user.id
          AND course_user.status <> ' . USER_TEACHER . '
          AND (course_user.editor = 0 OR course_user.editor IS NULL)
          AND (course_user.reviewer = 0 OR course_user.reviewer IS NULL)
          AND course_id = ?d';
    $args = array($course_id);

    if (isset($_POST['delusersinactive'])) {
        $sql .= ' AND user.expires_at < ' . DBHelper::timeAfter();
        $details['params'][] = "inactive\n";
    }

    if (isset($_POST['delusersdate']) and isset($_POST['reg_date']) and isset($_POST['reg_flag'])) {
        $date_obj = DateTime::createFromFormat('d-m-Y', $_POST['reg_date']);
        $operator = ($_POST['reg_flag'] == 'before')? '<': '>=';
        $sql .= " AND reg_date $operator ?t";
        $args[] = $del_date = $date_obj->format('Y-m-d');
        $details['params'][] = "reg_date $operator $del_date\n";
    }

    if ($filter_department) {
        if ($_POST['dept_flag'] == 'no') {
            $sql .= ' AND (user_department.department IS NULL OR user_department.department NOT IN (';
            $close = '))';
            $operator = 'not in';
        } else {
            $sql .= ' AND user_department.department IN (';
            $close = ')';
            $operator = 'in';
        }
        $sql .= implode(', ', array_fill(0, count($_POST['department']), '?d')) . $close;
        $args[] = $_POST['department'];

        $details['params'][] = "department $_POST[dept_flag] $operator (" .
            implode(', ', $_POST['department']) . ")\n";
    }

    if (isset($_POST['delusersid']) and isset($_POST['id_flag']) and isset($_POST['idlist'])) {
        if ($_POST['id_flag'] == 'am') {
            $what = 'user.am';
        } else {
            $what = 'user.username';
        }
        $ids = array();
        foreach (preg_split('/$\R?^/m', $_POST['idlist']) as $id) {
            $id = canonicalize_whitespace($id);
            if ($id !== '') {
                $ids[] = $id;
            }
        }
        $sql .= " AND $what IN (" .
            implode(', ', array_fill(0, count($ids), '?s')) . ')';
        $args[] = $ids;

        $details['params'][] = "$what IN (" . implode(', ', $ids) . ")";
    }

    $del_uids = array();
    Database::get()->queryFunc($sql, function ($item) use (&$del_uids) {
        $del_uids[] = $item->user_id;
    }, $args);

    if (count($del_uids)) {
        $placeholders = '(' . implode(', ', array_fill(0, count($del_uids), '?d')) . ')';
        Database::get()->query('DELETE FROM course_user
            WHERE course_id = ?d AND user_id IN ' . $placeholders,
            $course_id, $del_uids);

        $details['uid'] = $del_uids;
        Log::record($course_id, MODULE_ID_USERS, LOG_DELETE, $details);

        Database::get()->query("DELETE FROM group_members
                             WHERE group_id IN (SELECT id FROM `group` WHERE course_id = ?d) AND
                                   user_id NOT IN (SELECT user_id FROM course_user WHERE course_id = ?d)", $course_id, $course_id);
    }

    return $langUsersDeleted;
}

/**
 * @brief delete announcements
 * @return string
 */
function delete_announcements() {
    global $course_id, $langAnnDeleted;

    Database::get()->query("DELETE FROM announcement WHERE course_id = ?d", $course_id);
    return "<p>$langAnnDeleted</p>";
}

/**
 * @brief delete calendar events
 * @return string
 */
function delete_agenda() {
    global $langAgendaDeleted, $course_id;

    Database::get()->query("DELETE FROM agenda WHERE course_id = ?d", $course_id);
    return "<p>$langAgendaDeleted</p>";
}

/**
 * @brief hide documents
 * @return string
 */
function hide_doc() {
    global $langDocsDeleted, $course_id;

    Database::get()->query("UPDATE document SET visible=0, public=0 WHERE course_id = ?d", $course_id);
    return "<p>$langDocsDeleted</p>";
}

/**
 * @brief hide assignments
 * @return string
 */
function hide_work() {
    global $langWorksDeleted, $course_id;

    Database::get()->query("UPDATE assignment SET active=0 WHERE course_id = ?d", $course_id);
    return "<p>$langWorksDeleted</p>";
}
/**
 *
 @brief hide assignments submission
 * @return string
 */
function del_work_subs()  {
    global $langAllAssignmentSubsDeleted, $webDir, $course_id, $course_code;

    $workPath = $webDir."/courses/".$course_code."/work";

    $result = Database::get()->queryArray("SELECT id FROM assignment WHERE course_id = ?d", $course_id);

    foreach ($result as $row) {
        $secret =  Database::get()->querySingle("SELECT secret_directory FROM assignment
                            WHERE course_id = ?d AND id = ?d", $course_id, $row->id);
        if ($secret) {
            if (is_dir("$workPath/$secret->secret_directory")) { // if exists secret directory
                if (count(scandir("$workPath/$secret->secret_directory")) > 2) { // and is not empty
                    move_dir("$workPath/$secret->secret_directory",
                       "$webDir/courses/garbage/{$course_code}_work_".$row->id."_$secret->secret_directory");
                }
            }
            Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $row->id);
        }
    }
    return "<p>$langAllAssignmentSubsDeleted</p>";
}


/**
 * @brief hide exercises
 * @return string
 */
function hide_exercises() {
    global $langExercisesDeleted, $course_id;

    Database::get()->query("UPDATE exercise SET active = 0 WHERE course_id = ?d", $course_id);
    return "<p>$langExercisesDeleted</p>";
}

/**
 * @brief purge exercise results
 * @return string
 */
function purge_exercises() {
    global $langPurgeExercisesResults, $course_id;

    Database::get()->query("DELETE d FROM exercise_answer_record d,exercise_question s
                    WHERE d.question_id =s.id AND s.course_id = ?d", $course_id);
    Database::get()->query("DELETE d FROM exercise_user_record d,exercise s
                    WHERE d.eid=s.id AND s.course_id = ?d", $course_id);

    return "<p>$langPurgeExercisesResults</p>";
}

/**
 * @brief clear statistics
 * @return string
 */
function clear_stats() {
    global $langStatsCleared;

    require_once 'include/action.php';
    $action = new action();
    $action->summarizeAll();

    return "<p>$langStatsCleared</p>";
}

/**
 * @brief delete wall posts
 * @return string
 */
function del_wall_posts() {
    global $langWallPostsDeleted, $course_id;

    Database::get()->query("DELETE `rating` FROM `rating` INNER JOIN `wall_post` ON `rating`.`rid` = `wall_post`.`id`
                            WHERE `rating`.`rtype` = ?s AND `wall_post`.`course_id` = ?d", 'wallpost', $course_id);
    Database::get()->query("DELETE `rating_cache` FROM `rating_cache` INNER JOIN `wall_post` ON `rating_cache`.`rid` = `wall_post`.`id`
                            WHERE `rating_cache`.`rtype` = ?s AND `wall_post`.`course_id` = ?d", 'wallpost', $course_id);
    Database::get()->query("DELETE `comments` FROM `comments` INNER JOIN `wall_post` ON `comments`.`rid` = `wall_post`.`id`
                            WHERE `comments`.`rtype` = ?s AND `wall_post`.`course_id` = ?d", 'wallpost', $course_id);
    Database::get()->query("DELETE `wall_post_resources` FROM `wall_post_resources` INNER JOIN `wall_post` ON `wall_post_resources`.`post_id` = `wall_post`.`id`
                            WHERE `wall_post`.`course_id` = ?d", $course_id);
    Database::get()->query("DELETE FROM abuse_report WHERE rtype = ?s AND course_id = ?d", 'wallpost', $course_id);
    Database::get()->query("DELETE FROM `wall_post` WHERE `course_id` = ?d", $course_id);

    return "<p>$langWallPostsDeleted</p>";
}

/**
 *
 * @brief delete blog posts
 * @return string
 */
function del_blog_posts() {
    global $langBlogPostsDeleted, $course_id;

    Database::get()->query("DELETE `comments` FROM `comments` INNER JOIN `blog_post` ON `comments`.`rid` = `blog_post`.`id`
                            WHERE `comments`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $course_id);
    Database::get()->query("DELETE `rating` FROM `rating` INNER JOIN `blog_post` ON `rating`.`rid` = `blog_post`.`id`
                            WHERE `rating`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $course_id);
    Database::get()->query("DELETE `rating_cache` FROM `rating_cache` INNER JOIN `blog_post` ON `rating_cache`.`rid` = `blog_post`.`id`
                            WHERE `rating_cache`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $course_id);
    Database::get()->query("DELETE FROM `blog_post` WHERE `course_id` = ?d", $course_id);

    return "<p>$langBlogPostsDeleted</p>";
}
