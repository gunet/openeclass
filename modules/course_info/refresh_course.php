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
 * @file refresh_course.php
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

load_js('bootstrap-datepicker');

$head_content .= "
<script type='text/javascript'>
$(function() {
$('#reg_date').datepicker({
        format: 'dd-mm-yyyy',
        language: '".$language."',
        autoclose: true
    });
});
</script>";

$from_user = false;
if (isset($_REQUEST['from_user'])) { // checks if we are coming from /modules/user/index.php
    $from_user = true;
}

if (!$from_user) {
    $toolName = $langCourseInfo;
    $pageName = $langRefreshCourse;
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langCourseInfo);
} else {
    $toolName = $langUsers;
    $pageName = $langDelUsers;
    $navigation[] = array('url' => "../user/index.php?course=$course_code", 'name' => $langUsers);
}

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

    if (count($output)) {
        if (!$from_user) {
            Session::Messages($langRefreshSuccess, 'alert-success');
        }

        foreach ($output as $msg) {
            Session::Messages($msg, 'alert-info');
        }

        $url = "modules/course_info/refresh_course.php?course=$course_code";
        if ($from_user) {
            $url .= "&from_user=true";
        }
        redirect_to_home_page($url);
    }
} else {

    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "#",
                  'class' => 'back_btn',
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'
                 )));

    if (!$from_user) {
        $tool_content .= "<div class='alert alert-info'>$langRefreshInfo $langRefreshInfo_A</div>";
    }
    $tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>";
    if ($from_user) {
        $tool_content .= "<input type='hidden' name='from_user' value='true'>";
    }
    load_js('jstree3');
    $tree = new Hierarchy();
    list($js, $html) = $tree->buildUserNodePicker(array('multiple' => true));
    $head_content .= $js;
    $tool_content .= "<fieldset>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langUsers</label>
                <div class='col-sm-10'>
                    <p class='form-control-static'>$langUserDelCourse:</p>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2 checkbox'>
                    <label><input type='checkbox' name='delusersinactive'>$langInactiveUsers</label>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-3 col-sm-offset-2 checkbox'>
                    <label><input type='checkbox' name='delusersdate'>$langWithRegistrationDate:</label>
                </div>
                <div class='col-sm-3'>
                    " . selection(array('before' => $langBefore, 'after' => $langAfter), 'reg_flag', $reg_flag) . "
                </div>
                <div class='col-sm-3'>
                    <input type='text' name='reg_date' id='reg_date' value='" .date("d-m-Y", time()) ."'>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-1 col-sm-offset-2 checkbox'>
                    <label><input type='checkbox' name='delusersdept'>$langWho</label>
                </div>
                <div class='col-sm-2'>
                    " . selection(array('yes' => $langWithDepartment, 'no' => $langWithoutDepartment), 'dept_flag', 'yes') . "
                </div>
                <div class='col-sm-6'>
                    $html
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-1 col-sm-offset-2 checkbox'>
                    <label><input type='checkbox' name='delusersid'>$langWith</label>
                </div>
                <div class='col-sm-2'>
                    " . selection(array('am' => $langWithStudentId, 'uname' => $langWithUsernames), 'id_flag', 'am') . "
                </div>
                <div class='col-sm-6'>
                    <textarea name='idlist' class='form-control' rows='5'></textarea>
                </div>
            </div>";
    if (!$from_user) {
            $tool_content .= "<div class='form-group'>
                <label for='delannounces' class='col-sm-2 control-label'>$langAnnouncements</label>
                <div class='col-sm-10 checkbox'><label><input type='checkbox' name='delannounces'>$langAnnouncesDel</label></div>
            </div>
            <div class='form-group'>
              <label for-'delagenda' class='col-sm-2 control-label'>$langAgenda</label>
              <div class='col-sm-10 checkbox'><label><input type='checkbox' name='delagenda'>$langAgendaDel</label></div>
            </div>
            <div class='form-group'>
              <label for='hideworks' class='col-sm-2 control-label'>$langWorks</label>
                <div class='col-sm-10 checkbox'>
                    <label><input type='checkbox' name='hideworks'>$langHideWork</label>
                  </div>
                <div class='col-sm-offset-2 col-sm-10 checkbox'>
                    <label><input type='checkbox' name='delworkssubs'>$langDelAllWorkSubs</label>
                </div>
            </div>
            <div class='form-group'>
              <label for='purgeexercises' class='col-sm-2 control-label'>$langExercises</label>
              <div class='col-sm-10 checkbox'><label><input type='checkbox' name='purgeexercises'>$langPurgeExercisesResults</label></div>
            </div>
            <div class='form-group'>
              <label for='clearstats' class='col-sm-2 control-label'>$langUsage</label>
              <div class='col-sm-10 checkbox'><label><input type='checkbox' name='clearstats'>$langClearStats</label></div>
            </div>
            <div class='form-group'>
              <label for='delwallposts' class='col-sm-2 control-label'>$langWall</label>
              <div class='col-sm-10 checkbox'><label><input type='checkbox' name='delwallposts'>$langDelWallPosts</label></div>
            </div>
            <div class='form-group'>
              <label for='delblogposts' class='col-sm-2 control-label'>$langBlog</label>
              <div class='col-sm-10 checkbox'><label><input type='checkbox' name='delblogposts'>$langDelBlogPosts</label></div>
            </div>";
            }
        $tool_content .= "
            <div class='col-sm-offset-2 col-sm-10'>
            <input class='btn btn-primary' type='submit' value='$langSubmitActions' name='submit'>
            </div>
            </fieldset>
            ". generate_csrf_token_form_field() ."
            </form>
            </div>";
}

draw($tool_content, 2, null, $head_content);

/**
 *
 * @global type $course_id
 * @global type $langUsersDeleted
 * @param type $date
 * @param type $duration
 * @return type
 */
function delete_users() {
    global $course_id, $langUsersDeleted;

    $details = array('multiple' => true, 'params' => array());

    $sql = 'SELECT user.id AS user_id FROM course_user, user, user_department
        WHERE course_user.user_id = user.id
          AND user_department.user = user.id
          AND course_user.status <> ' . USER_TEACHER . '
          AND course_user.editor = 0
          AND course_user.reviewer = 0
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

    if (isset($_POST['delusersdept']) and isset($_POST['dept_flag']) and isset($_POST['department'])) {
        $operator = $_POST['dept_flag'] == 'no'? 'NOT IN': 'IN';
        $sql .= ' AND user_department.department ' .
            $operator . '(' .
            implode(', ', array_fill(0, count($_POST['department']), '?d')) . ')';
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
 *
 * @global type $course_id
 * @global type $langAnnDeleted
 * @return type
 */
function delete_announcements() {
    global $course_id, $langAnnDeleted;

    Database::get()->query("DELETE FROM announcement WHERE course_id = ?d", $course_id);
    return "<p>$langAnnDeleted</p>";
}

/**
 *
 * @global type $langAgendaDeleted
 * @global type $course_id
 * @return type
 */
function delete_agenda() {
    global $langAgendaDeleted, $course_id;

    Database::get()->query("DELETE FROM agenda WHERE course_id = ?d", $course_id);
    return "<p>$langAgendaDeleted</p>";
}

/**
 *
 * @global type $langDocsDeleted
 * @global type $course_id
 * @return type
 */
function hide_doc() {
    global $langDocsDeleted, $course_id;

    Database::get()->query("UPDATE document SET visible=0, public=0 WHERE course_id = ?d", $course_id);
    return "<p>$langDocsDeleted</p>";
}

/**
 *
 * @global type $langWorksDeleted
 * @global type $course_id
 * @return type
 */
function hide_work() {
    global $langWorksDeleted, $course_id;

    Database::get()->query("UPDATE assignment SET active=0 WHERE course_id = ?d", $course_id);
    return "<p>$langWorksDeleted</p>";
}
/**
 *
 * @global type $langAllAssignmentSubsDeleted
 * @global type $webDir
 * @global type $course_id
 * @global type $course_code
 * @return type
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
                       "$webDir/courses/garbage/${course_code}_work_".$row->id."_$secret->secret_directory");
                }
            }
            Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $row->id);
        }
    }
    return "<p>$langAllAssignmentSubsDeleted</p>";
}

/**
 *
 * @global type $langPurgeExercisesResults
 * @return type
 */
function purge_exercises() {
    global $langPurgeExercisesResults, $course_id;

    Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid IN (
                                SELECT eurid FROM exercise_user_record WHERE eid IN
                                    (SELECT id FROM exercise WHERE course_id = ?d))", $course_id);
    Database::get()->query("DELETE FROM exercise_user_record WHERE eid IN
                                (SELECT id FROM exercise WHERE course_id = ?d)",$course_id);

    return "<p>$langPurgeExercisesResults</p>";
}

/**
 *
 * @global type $langStatsCleared
 * @return type
 */
function clear_stats() {
    global $langStatsCleared;

    require_once 'include/action.php';
    $action = new action();
    $action->summarizeAll();

    return "<p>$langStatsCleared</p>";
}

/**
 *
 * @global type $langWallPostsDeleted
 * @return type
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
 * @global type $langAllBlogPostsDeleted
 * @return type
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

