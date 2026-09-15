<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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

/**
 * @file multicoursedit.php
 * @brief Search and mass edit courses
 */

$require_departmentmanage_user = true;
$require_help = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';
require_once 'modules/course_info/archive_functions.php';
require_once 'modules/work/functions.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';

// Handle Bulk Form Submissions (e.g. Course Deletion / Refresh)
if (isset($_POST['bulk_submit']) || (isset($_POST['bulk_action']) && $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_SERVER['HTTP_X_REQUESTED_WITH']))) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }

    $lessons = $_POST['lessons'] ?? array();
    $action = $_POST['bulk_action'] ?? '';

    if (empty($lessons) || !is_array($lessons)) {
        Session::flash('message', $langNoCourseSelected ?? 'Δεν έχετε επιλέξει κανένα μάθημα.');
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/admin/multicoursedit.php');
    }

    if ($action === 'delete') {
        $deleted_count = 0;

        foreach ($lessons as $cId) {
            $cId = intval($cId);
            if ($cId <= 0) continue;

            // Validate permissions if department admin
            if (isDepartmentAdmin()) {
                if (!validateCourseNodes($cId, true)) {
                    continue;
                }
            }

            $course_code = course_id_to_code($cId);
            $course_title = course_id_to_title($cId);

            if (empty($course_code)) continue;

            // 1. Archive course first (same as delete_course.php)
            $zipfile = doArchive($cId, $course_code);

            $garbage = "$webDir/courses/garbage";
            $target = "$garbage/$course_code.$_SESSION[csrf_token]";
            if (!is_dir($target)) {
                make_dir($target);
            }
            touch("$garbage/index.html");
            if (file_exists($zipfile)) {
                rename($zipfile, "$target/$course_code.zip");
            }

            // 2. Email course teachers (same as delete_course.php)
            $profs = Database::get()->queryArray("SELECT user.id AS prof_uid, user.email AS email,
                                      user.surname, user.givenname
                                   FROM course_user JOIN user ON user.id = course_user.user_id
                                   WHERE course_id = ?d AND course_user.status = " . USER_TEACHER, $cId);

            $subject = "$langCourseDeleted " . q($course_title) . " ($course_code)";

            $mailHeader = "
            <!-- Header Section -->
            <div id='mail-header'>
                <div>
                    <br>
                    <div id='header-title'>$langCourseDeleted '" . q($course_title) . " ($course_code)'</div>
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

            $message = $mailHeader . $mailMain . $mailFooter;
            $plainMessage = html2text($message);
            foreach ($profs as $prof) {
                if (!get_user_email_notification_from_courses($prof->prof_uid) or (!get_user_email_notification($prof->prof_uid, $cId))) {
                    continue;
                } else {
                    send_mail_multipart('', '', '', $prof->email, $subject, $plainMessage, $message);
                }
            }

            // 3. Delete course from database and files
            delete_course($cId);

            // 4. Log event
            Log::record(0, 0, LOG_DELETE_COURSE, array(
                'id' => $cId,
                'code' => $course_code,
                'title' => $course_title
            ));

            $deleted_count++;
        }

        if ($deleted_count > 0) {
            Session::flash('message', $langCourseDelSuccess ?? 'Τα επιλεγμένα μαθήματα διαγράφηκαν επιτυχώς.');
            Session::flash('alert-class', 'alert-success');
        }

        redirect_to_home_page('modules/admin/multicoursedit.php');
    } elseif ($action === 'refresh') {
        $refreshed_count = 0;

        foreach ($lessons as $cId) {
            $cId = intval($cId);
            if ($cId <= 0) continue;

            if (isDepartmentAdmin()) {
                if (!validateCourseNodes($cId, true)) {
                    continue;
                }
            }

            $cCode = course_id_to_code($cId);
            $cTitle = course_id_to_title($cId);
            if (empty($cCode)) continue;

            // Execute selected refresh options based on refresh_course.php
            if (isset($_POST['delusersdate']) or isset($_POST['delusersdept']) or isset($_POST['delusersid']) or isset($_POST['delusersinactive'])) {
                refresh_delete_users_for_course($cId);
            }
            if (isset($_POST['delannounces'])) {
                refresh_delete_announcements($cId);
            }
            if (isset($_POST['delagenda'])) {
                refresh_delete_agenda($cId);
            }
            if (isset($_POST['hideworks'])) {
                refresh_hide_work($cId);
            }
            if (isset($_POST['delworkssubs'])) {
                refresh_del_work_subs($cId, $cCode);
            }
            if (isset($_POST['hideexercises'])) {
                refresh_hide_exercises($cId);
            }
            if (isset($_POST['purgeexercises'])) {
                refresh_purge_exercises($cId);
            }
            if (isset($_POST['clearstats'])) {
                refresh_clear_stats();
            }
            if (isset($_POST['delwallposts'])) {
                refresh_del_wall_posts($cId);
            }
            if (isset($_POST['delblogposts'])) {
                refresh_del_blog_posts($cId);
            }

            $refreshed_count++;
        }

        if ($refreshed_count > 0) {
            Session::flash('message', $langRefreshSuccess ?? 'Η ανανέωση των επιλεγμένων μαθημάτων ολοκληρώθηκε επιτυχώς.');
            Session::flash('alert-class', 'alert-success');
        }

        redirect_to_home_page('modules/admin/multicoursedit.php');
    }
}

// Check if request is AJAX (DataTables request or XMLHttpRequest)
if (isset($_POST['draw']) || isset($_GET['draw']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
    $search_submitted = $_POST['search_submitted'] ?? $_GET['search_submitted'] ?? 0;

    // If search has not been submitted yet, return empty dataset
    if (empty($search_submitted)) {
        $data['recordsTotal'] = 0;
        $data['recordsFiltered'] = 0;
        $data['aaData'] = array();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $tree = new Hierarchy();
    $course = new Course();
    $user = new User();

    // Search parameters submitted via form
    $searchtitle = $_POST['formsearchtitle'] ?? $_GET['formsearchtitle'] ?? '';
    $searchcode = $_POST['formsearchcode'] ?? $_GET['formsearchcode'] ?? '';

    // Handle formsearchfaculte (Category / Department ID)
    $raw_faculte = $_REQUEST['formsearchfaculte'] ?? 0;
    $searchfaculte = 0;
    if (!empty($raw_faculte)) {
        $direct = getDirectReference($raw_faculte);
        if (!empty($direct) && is_numeric($direct)) {
            $searchfaculte = intval($direct);
        } else if (is_numeric($raw_faculte)) {
            $searchfaculte = intval($raw_faculte);
        }
    }

    // DataTables pagination parameters
    $limit = intval($_POST['length'] ?? 10);
    $offset = intval($_POST['start'] ?? 0);

    // Search query conditions
    $query = '';
    $terms = array();

    if (!empty($searchtitle)) {
        $query .= ' AND title LIKE ?s';
        $terms[] = '%' . $searchtitle . '%';
    }

    if (!empty($searchcode)) {
        $query .= ' AND (course.code LIKE ?s OR public_code LIKE ?s)';
        $terms[] = '%' . $searchcode . '%';
        $terms[] = '%' . $searchcode . '%';
    }

    if ($searchfaculte > 0) {
        $subs = $tree->buildSubtrees(array($searchfaculte));
        $ids = 0;
        foreach ($subs as $key => $id) {
            $terms[] = $id;
            $ids++;
        }
        if ($ids > 0) {
            $query .= ' AND hierarchy.id IN (' . implode(', ', array_fill(0, $ids, '?d')) . ')';
        }
    }

    // DataTables global search filter
    $filter_terms = array();
    if (!empty($_POST['search']['value'])) {
        $filter_query = ' AND (title LIKE ?s OR prof_names LIKE ?s OR course.code LIKE ?s)';
        $filter_terms[] = '%' . $_POST['search']['value'] . '%';
        $filter_terms[] = '%' . $_POST['search']['value'] . '%';
        $filter_terms[] = '%' . $_POST['search']['value'] . '%';
    } else {
        $filter_query = '';
    }

    // Limit department admin search only to subtrees of own departments
    if (isDepartmentAdmin()) {
        $begin = true;
        foreach ($user->getAdminDepartmentIds($uid) as $department) {
            if ($begin) {
                $query .= ' AND (';
                $begin = false;
            } else {
                $query .= ' OR ';
            }
            $nodeLftRgt = $tree->getNodeLftRgt($department);
            $query .= 'hierarchy.lft BETWEEN ' . $nodeLftRgt->lft . ' AND ' . $nodeLftRgt->rgt;
        }
        $query .= ')';
    }

    // Sorting
    $extra_query = "ORDER BY course.title " . (isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] == 'desc' ? 'DESC' : '');

    // Pagination
    if ($limit > 0) {
        $extra_query .= " LIMIT ?d, ?d";
        $extra_terms = array($offset, $limit);
    } else {
        $extra_terms = array();
    }

    $query_collaboration = '';
    if (get_config('show_collaboration') && get_config('show_always_collaboration')) {
        $query_collaboration = ' AND course.is_collaborative = 1';
    }

    $sql = Database::get()->queryArray("SELECT DISTINCT course.code, course.title, course.prof_names, course.visible, course.id, course.created, course.popular_course
                               FROM course, course_department, hierarchy
                              WHERE course.id = course_department.course
                                AND hierarchy.id = course_department.department
                                    $query $filter_query $query_collaboration $extra_query", $terms, $filter_terms, $extra_terms);

    $all_results = Database::get()->querySingle("SELECT COUNT(DISTINCT course.id) as total FROM course, course_department, hierarchy
                                                WHERE course.id = course_department.course
                                                AND hierarchy.id = course_department.department
                                                $query $query_collaboration", $terms)->total;

    $filtered_results = Database::get()->querySingle("SELECT COUNT(DISTINCT course.id) as total FROM course, course_department, hierarchy
                                                WHERE course.id = course_department.course
                                                AND hierarchy.id = course_department.department
                                                $query $filter_query $query_collaboration", $terms, $filter_terms)->total;

    $data['recordsTotal'] = $all_results;
    $data['recordsFiltered'] = $filtered_results;
    $data['aaData'] = array();

    foreach ($sql as $logs) {
        $popular_icon = '';
        if ($logs->popular_course) {
            $popular_icon = icon('fa-star');
        }
        $course_title = "<a href='{$urlServer}courses/" . $logs->code . "/'>" . q($logs->title) . "
                        </a> (" . q($logs->code) . ") " . $popular_icon . "<br><i>" . q($logs->prof_names) . "</i>
                        <br><span class='help-block'>$langCreatedIn: " . format_locale_date(strtotime($logs->created), null, false). "</span>";

        $departments = $course->getDepartmentIds($logs->id);
        $i = 1;
        $dep = '';
        foreach ($departments as $department) {
            $br = ($i < count($departments)) ? '<br/>' : '';
            $dep .= $tree->getFullPath($department) . $br;
            $i++;
        }

        $checkbox = "<input type='checkbox' class='select_course_checkbox' name='lessons[]' value='{$logs->id}'>";

        $data['aaData'][] = array(
            '0' => $checkbox,
            '1' => $course_title,
            '2' => $dep
        );
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

$tree = new Hierarchy();
$user = new User();

load_js('jstree3');
load_js('tools.js');
load_js('datatables');

$toolName = $langAdmin;
$pageName = $langMultiCourseEdit;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$data['action_bar'] = action_bar(array(
    array(
        'title' => $langAllCourses,
        'url' => "listcours.php",
        'icon' => 'fa-search',
        'level' => 'primary-label'
    )
));

if (isDepartmentAdmin()) {
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
} else {
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false));
}

$head_content .= $js;
$data['html'] = $html;

view('admin.courses.multicoursedit', $data);

// =========================================================================
// Course Refresh Helper Functions (based on modules/course_info/refresh_course.php)
// =========================================================================

function refresh_delete_users_for_course(int $course_id): void
{
    $details = array('multiple' => true, 'params' => array());

    $sql = 'SELECT user.id AS user_id
        FROM course_user, user
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
        if ($date_obj) {
            $operator = ($_POST['reg_flag'] == 'before') ? '<' : '>=';
            $sql .= " AND reg_date $operator ?t";
            $args[] = $del_date = $date_obj->format('Y-m-d');
            $details['params'][] = "reg_date $operator $del_date\n";
        }
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
}

function refresh_delete_announcements(int $course_id): void
{
    Database::get()->query("DELETE FROM announcement WHERE course_id = ?d", $course_id);
}

function refresh_delete_agenda(int $course_id): void
{
    Database::get()->query("DELETE FROM agenda WHERE course_id = ?d", $course_id);
}

function refresh_hide_work(int $course_id): void
{
    Database::get()->query("UPDATE assignment SET active=0 WHERE course_id = ?d", $course_id);
}

function refresh_del_work_subs(int $course_id, string $course_code): void
{
    global $webDir;
    $workPath = $webDir . "/courses/" . $course_code . "/work";

    $result = Database::get()->queryArray("SELECT id FROM assignment WHERE course_id = ?d", $course_id);

    foreach ($result as $row) {
        $secret = Database::get()->querySingle("SELECT secret_directory FROM assignment
                            WHERE course_id = ?d AND id = ?d", $course_id, $row->id);
        if ($secret && !empty($secret->secret_directory)) {
            if (is_dir("$workPath/$secret->secret_directory")) {
                if (count(scandir("$workPath/$secret->secret_directory")) > 2) {
                    move_dir("$workPath/$secret->secret_directory",
                       "$webDir/courses/garbage/{$course_code}_work_" . $row->id . "_$secret->secret_directory");
                }
            }
            Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $row->id);
        }
    }
}

function refresh_hide_exercises(int $course_id): void
{
    Database::get()->query("UPDATE exercise SET active = 0 WHERE course_id = ?d", $course_id);
}

function refresh_purge_exercises(int $course_id): void
{
    Database::get()->query("DELETE d FROM exercise_answer_record d,exercise_question s
                    WHERE d.question_id =s.id AND s.course_id = ?d", $course_id);
    Database::get()->query("DELETE d FROM exercise_user_record d,exercise s
                    WHERE d.eid=s.id AND s.course_id = ?d", $course_id);
}

function refresh_clear_stats(): void
{
    require_once 'include/action.php';
    $action = new action();
    $action->summarizeAll();
}

function refresh_del_wall_posts(int $course_id): void
{
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
}

function refresh_del_blog_posts(int $course_id): void
{
    Database::get()->query("DELETE `comments` FROM `comments` INNER JOIN `blog_post` ON `comments`.`rid` = `blog_post`.`id`
                            WHERE `comments`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $course_id);
    Database::get()->query("DELETE `rating` FROM `rating` INNER JOIN `blog_post` ON `rating`.`rid` = `blog_post`.`id`
                            WHERE `rating`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $course_id);
    Database::get()->query("DELETE `rating_cache` FROM `rating_cache` INNER JOIN `blog_post` ON `rating_cache`.`rid` = `blog_post`.`id`
                            WHERE `rating_cache`.`rtype` = ?s AND `blog_post`.`course_id` = ?d", 'blogpost', $course_id);
    Database::get()->query("DELETE FROM `blog_post` WHERE `course_id` = ?d", $course_id);
}
