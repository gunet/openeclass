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
 * @file refresh_course.php 
 * @brief course clean up
 */

$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_login = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/work/work_functions.php';
require_once 'include/lib/fileManageLib.inc.php';

$data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                  'url' => "#",
                  'class' => 'back_btn',
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'
                 )));

if (isset($_GET['from_user'])) {
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
    checkSecondFactorChallenge();
    if (!isset($_GET['from_user'])) {
        $url = "$_SERVER[SCRIPT_NAME]?course=$course_code";
    } else {
        $url = "../user/index.php?course=$course_code";
    }
    
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => $url,
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'
                 )));
       
    $output = array();
    if (isset($_POST['delusers'])) {
        if (isset($_POST['reg_date']) and isset($_POST['reg_flag'])) {
            $date_obj = DateTime::createFromFormat('d-m-Y', $_POST['reg_date']);
            $date = $date_obj->format('Y-m-d');            
            $output[] = delete_users(q($date), $_POST['reg_flag']);
        } else {
            $output[] = delete_users();
        }
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
    if (isset($_POST['delblogposts'])) {
        $output[] = del_blog_posts();
    }
    if (isset($_POST['delwallposts'])) {
        $output[] = del_wall_posts();
    }
    // output results
    if (($count_events = count($output)) > 0) {
        $data['count_events'] = $count_events;
        $data['output'] = $output;
        $data['menuTypeID'] = 2;
        view('modules.course_info.refresh_course_results', $data);
    }    
} else { // display form                 
    $data['selection_date'] = selection(array('before' => $langBefore, 'after' => $langAfter), 'reg_flag', $reg_flag, 'class="form-control"');
    $data['date_format'] = date("d-m-Y", time());
        
    $data['form_url'] = "$_SERVER[SCRIPT_NAME]?course_code=$course_code";
    $data['form_url_from_user'] = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;from_user=true";
    
    $data['menuTypeID'] = 2;
    view('modules.course_info.refresh_course', $data);
}

/**
 * 
 * @global type $course_id
 * @global type $langUsersDeleted
 * @param type $date
 * @param type $duration
 * @return type
 */
function delete_users($date = '', $duration = '') {
    global $course_id, $langUsersDeleted;

    if (isset($date)) {
         if ($duration == 'before') {
             $operator = '<';
         } else {
             $operator = '>';
         }
                  
        Database::get()->query("DELETE FROM course_user WHERE course_id = ?d AND
                                status != ". USER_TEACHER ." AND
                                reg_date $operator ?t", $course_id, $date);
    } else {
        Database::get()->query("DELETE FROM course_user WHERE course_id = ?d AND status != " . USER_TEACHER . "", $course_id);
    }
    Database::get()->query("DELETE FROM group_members
                         WHERE group_id IN (SELECT id FROM `group` WHERE course_id = ?d) AND
                               user_id NOT IN (SELECT user_id FROM course_user WHERE course_id = ?d)", $course_id, $course_id);
    
    return "$langUsersDeleted";
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
    return "$langAnnDeleted";
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
    return "$langAgendaDeleted";
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
    return "$langDocsDeleted";
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
    return "$langWorksDeleted";
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
    return "$langAllAssignmentSubsDeleted";
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

    return "$langPurgeExercisesResults";
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

    return "$langStatsCleared";
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
    
    return "$langBlogPostsDeleted";
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

    return "$langWallPostsDeleted";
}

