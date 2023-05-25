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

$require_login = true;
$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_help = TRUE;
$helpTopic = 'course_users';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'include/course_settings.php';

//Identifying ajax request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $is_editor) {
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $unregister_gid = intval(getDirectReference($_POST['value']));
        $unregister_ok = true;
        // Security: don't remove myself except if there is another prof
        if ($unregister_gid == $uid) {
            $result = Database::get()->querySingle("SELECT COUNT(user_id) AS cnt FROM course_user
                                            WHERE course_id = ?d AND
                                                  status = " . USER_TEACHER . " AND
                                                  user_id != ?d
                                            LIMIT 1", $course_id, $uid);

            if ($result) {
                if ($result->cnt == 0) {
                    $unregister_ok = false;
                }
            }
        }
        if ($unregister_ok) {
            Database::get()->query("DELETE FROM course_user
                                            WHERE user_id = ?d AND
                                                course_id = ?d", $unregister_gid, $course_id);
            Database::get()->query("DELETE FROM user_badge_criterion WHERE user = ?d AND 
                                    badge_criterion IN
                                           (SELECT id FROM badge_criterion WHERE badge IN
                                           (SELECT id FROM badge WHERE course_id = ?d))", $unregister_gid, $course_id);
            Database::get()->query("DELETE FROM user_badge WHERE user = ?d AND                 
                                      badge IN (SELECT id FROM badge WHERE course_id = ?d)", $unregister_gid, $course_id);
            Database::get()->query("DELETE FROM user_certificate_criterion WHERE user = ?d AND 
                                    certificate_criterion IN
                                    (SELECT id FROM certificate_criterion WHERE certificate IN
                                        (SELECT id FROM certificate WHERE course_id = ?d))", $unregister_gid, $course_id);
            Database::get()->query("DELETE FROM user_certificate WHERE user = ?d AND 
                                 certificate IN (SELECT id FROM certificate WHERE course_id = ?d)", $unregister_gid, $course_id);

            if (check_guest($unregister_gid)) {
                Database::get()->query("DELETE FROM user WHERE id = ?d", $unregister_gid);
            }
            Database::get()->query("DELETE FROM group_members
                                    WHERE user_id = ?d AND
                                          group_id IN (SELECT id FROM `group` WHERE course_id = ?d)", $unregister_gid, $course_id);
            Log::record($course_id, MODULE_ID_USERS, LOG_DELETE, array('uid' => $unregister_gid,
                                                                       'right' => '-5'));

        }
        exit();
    }

    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);

    if (!empty($_GET['sSearch'])) {
        $search_values = array_fill(0, 4, '%' . $_GET['sSearch'] . '%');
        $search_sql = 'AND (user.surname LIKE ?s OR user.givenname LIKE ?s OR user.username LIKE ?s OR user.email LIKE ?s)';
    } else {
        $search_sql = '';
        $search_values = array();
    }
    // user status
    if (!empty($_GET['sSearch_1'])) {
        $filter = $_GET['sSearch_1'];
        if ($filter == 'editor') {
            $search_sql .= ' AND course_user.editor = 1 AND course_user.status = '.USER_STUDENT;
        } elseif ($filter == 'teacher') {
            $search_sql .= ' AND course_user.status = '.USER_TEACHER;
        } elseif ($filter == 'student') {
            $search_sql .= ' AND course_user.editor <> 1 AND course_user.status = '.USER_STUDENT;
        } elseif ($filter == 'tutor') {
            $search_sql .= ' AND course_user.tutor = 1';
        } elseif ($filter == 'reviewer') {
            $search_sql .= ' AND course_user.reviewer = 1';
        }
    }
    $sortDir = ($_GET['sSortDir_0'] == 'desc')? 'DESC': '';
    $order_sql = 'ORDER BY ' .
        (($_GET['iSortCol_0'] == 0) ? "user.surname $sortDir, user.givenname $sortDir" : "course_user.reg_date $sortDir");

    $limit_sql = ($limit > 0) ? "LIMIT $offset,$limit" : "";

    $all_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user
                                                WHERE `user`.`id` = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d", $course_id)->total;
    $filtered_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user
                                                WHERE `user`.`id` = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d $search_sql", $course_id, $search_values)->total;
    $result = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname, user.email,
                           user.am, user.has_icon, course_user.status,
                           course_user.tutor, course_user.editor, course_user.reviewer,
                           DATE(course_user.reg_date) AS reg_date
                    FROM course_user, user
                    WHERE `user`.`id` = `course_user`.`user_id`
                    AND `course_user`.`course_id` = ?d
                    $search_sql $order_sql $limit_sql", $course_id, $search_values);

    $data['iTotalRecords'] = $all_users;
    $data['iTotalDisplayRecords'] = $filtered_users;
    $data['aaData'] = array();
    foreach ($result as $myrow) {
        $full_name = sanitize_utf8($myrow->givenname . " " . $myrow->surname);
        $am_message = sanitize_utf8(empty($myrow->am) ? '' : ("<div class='right'>" . q($myrow->am) . "</div>"));
        $stats_icon = icon('fa-bar-chart', $langUserStats, "../usage/userduration.php?course=$course_code&amp;u=$myrow->id");
        //create date field with unregister button
        $date_field = $myrow->reg_date ? format_locale_date(strtotime($myrow->reg_date), 'short', false) : $langUnknownDate;

        // Create appropriate role control buttons
        // Admin right
        $user_role_controls = '';
        if ($myrow->id != $_SESSION["uid"] && $myrow->reviewer == '1') {
            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeReviewer=$myrow->id'><img src='$themeimg/reviewer_remove.png' alt='$langRemoveRightReviewer' title='$langRemoveRightReviewer'></a>";
        } else {
            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveReviewer=$myrow->id'><img src='$themeimg/reviewer_add.png' alt='$langGiveRightReviewer' title='$langGiveRightReviewer'></a>";
        }
        // opencourses reviewer right
        if (get_config('opencourses_enable')) {
            if ($myrow->reviewer == '1') {
                $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeReviewer=$myrow->id'><img src='$themeimg/reviewer_remove.png' alt='$langRemoveRightReviewer' title='$langRemoveRightReviewer'></a>";
            } else {
                $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveReviewer=$myrow->id'><img src='$themeimg/reviewer_add.png' alt='$langGiveRightReviewer' title='$langGiveRightReviewer'></a>";
            }
        }
        $user_role_controls = action_button(array(
            array(
              'title' => $langUnregCourse,
              'level' => 'primary',
              'url' => '#',
              'icon' => 'fa-times',
              'btn_class' => 'delete_btn deleteAdminBtn'
            ),
            array(
                'title' => $langGiveRightTutor,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".($myrow->tutor == '0' ? "give" : "remove")."Tutor=". getIndirectReference($myrow->id),
                'icon' => $myrow->tutor == '0' ? "fa-square-o" : "fa-check-square-o"
            ),
            array(
                'title' => $langGiveRightEditor,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".($myrow->editor == '0' ? "give" : "remove")."Editor=". getIndirectReference($myrow->id),
                'icon' => $myrow->editor == '0' ? "fa-square-o" : "fa-check-square-o"
            ),
            array(
                'title' => $langGiveRightAdmin,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".($myrow->status == '1' ? "remove" : "give")."Admin=". getIndirectReference($myrow->id),
                'icon' => $myrow->status != '1' ? "fa-square-o" : "fa-check-square-o",
                'disabled' => $myrow->id == $_SESSION["uid"] || ($myrow->id != $_SESSION["uid"] && get_config('opencourses_enable') && $myrow->reviewer == '1')
            ),
            array(
                'title' => $langGiveRightReviewer,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".($myrow->reviewer == '1' ? "remove" : "give")."Reviewer=". getIndirectReference($myrow->id),
                'icon' => $myrow->reviewer != '1' ? "fa-square-o" : "fa-check-square-o",
                'disabled' => $myrow->id == $_SESSION["uid"],
                'show' => get_config('opencourses_enable') &&
                            (
                                ($myrow->id == $_SESSION["uid"] && $myrow->reviewer == '1') ||
                                ($myrow->id != $_SESSION["uid"] && $is_opencourses_reviewer && $is_admin)
                            )
            )
        ));
        if ($myrow->editor == '1' and $myrow->status != USER_TEACHER) {
            $user_roles = array($langEditor);
        } elseif ($myrow->status == USER_TEACHER) {
            $user_roles = array($langTeacher);
        } else {
            $user_roles = array($langStudent);
        }
        if ($myrow->tutor == '1') array_push($user_roles, $langTutor);
        if ($myrow->reviewer == '1') array_push($user_roles, $langOpenCoursesReviewer);

        $user_role_string = implode(',<br>', $user_roles);

        if (!get_user_email_notification($myrow->id, $course_id)) {
            $email_exlamation_icon = "&nbsp;&nbsp;<span class='fa fa-exclamation-triangle space-after-icon' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-html='true' data-bs-title='$langNoUserEmailLegend'></span>";
        } else {
            $email_exlamation_icon = '';
        }

        $nameColumn = "
                        <div class='pull-left' style='width: 32px ; margin-right: 10px;'>
                            <img style='border-radius:50%; border:solid 2px #e8e8e8; 'class='img-circle' src='".user_icon($myrow->id) . "' />
                            <div style='padding-left:8px; padding-top: 5px;'>$stats_icon</div>
                        </div>
                        <div class='pull-left'>
                            <div style='padding-bottom:2px;'>".display_user($myrow->id, false, false, '', $course_code)."</div>
                            <div><small><a href='mailto:" . $myrow->email . "'>" . $myrow->email . "</a>$email_exlamation_icon</small></div>
                            <div class='text-muted'><small>$am_message</small></div>
                        </div>";
        $roleColumn = "<div class='text-muted'>$user_role_string</div>";
        // search for inactive users
        $inactive_user = is_inactive_user($myrow->id);
        //setting data table column data
        $data['aaData'][] = array(
            'DT_RowId' => getIndirectReference($myrow->id),
            'DT_RowClass' => 'smaller',
            '0' => $nameColumn,
            '1' => $roleColumn,
            '2' => user_groups($course_id, $myrow->id),
            '3' => "<div class='text-center text-muted'>$date_field</div>",
            '4' => $user_role_controls,
            '5' => $inactive_user
        );
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

$limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;

$toolName = $langUsers;
load_js('tools.js');
load_js('datatables');

$limit_sql = '';
// Handle user removal / status change
if (isset($_GET['giveAdmin'])) {
    $new_admin_gid = intval(getDirectReference($_GET['giveAdmin']));
    Database::get()->query("UPDATE course_user SET status = " . USER_TEACHER . "
                        WHERE user_id = ?d
                        AND course_id = ?d", $new_admin_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $new_admin_gid,
                                                               'right' => '+1'));
} elseif (isset($_GET['giveTutor'])) {
    $new_tutor_gid = intval(getDirectReference($_GET['giveTutor']));
    Database::get()->query("UPDATE course_user SET tutor = 1
                        WHERE user_id = ?d
                        AND course_id = ?d", $new_tutor_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $new_tutor_gid,
                                                               'right' => '+3'));
    Database::get()->query("UPDATE group_members, `group` SET is_tutor = 0
                        WHERE `group`.id = group_members.group_id AND
                              `group`.course_id = ?d AND
                              group_members.user_id = ?d", $course_id, $new_tutor_gid);
} elseif (isset($_GET['giveEditor'])) {
    $new_editor_gid = intval(getDirectReference($_GET['giveEditor']));
    Database::get()->query("UPDATE course_user SET editor = 1
                        WHERE user_id = ?d
                        AND course_id = ?d", $new_editor_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $new_editor_gid,
                                                               'right' => '+2'));
} elseif (isset($_GET['removeAdmin'])) {
    $removed_admin_gid = intval(getDirectReference($_GET['removeAdmin']));
    Database::get()->query("UPDATE course_user SET status = " . USER_STUDENT . "
                        WHERE user_id <> ?d AND
                              user_id = ?d AND
                              course_id = ?d", $uid, $removed_admin_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $removed_admin_gid,
                                                               'right' => '-1'));
} elseif (isset($_GET['removeTutor'])) {
    $removed_tutor_gid = intval(getDirectReference($_GET['removeTutor']));
    Database::get()->query("UPDATE course_user SET tutor = 0
                        WHERE user_id = ?d
                              AND course_id = ?d", $removed_tutor_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $removed_tutor_gid,
                                                               'right' => '-3'));
} elseif (isset($_GET['removeEditor'])) {
    $removed_editor_gid = intval(getDirectReference($_GET['removeEditor']));
    Database::get()->query("UPDATE course_user SET editor = 0
                        WHERE user_id = ?d
                        AND course_id = ?d", $removed_editor_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $removed_editor_gid,
                                                               'right' => '-2'));
}

if (get_config('opencourses_enable')) {
    if (isset($_GET['giveReviewer'])) {
        $new_reviewr_gid = intval(getDirectReference($_GET['giveReviewer']));
        Database::get()->query("UPDATE course_user SET status = " . USER_TEACHER . ", reviewer = 1
                        WHERE user_id = ?d
                        AND course_id = ?d", $new_reviewr_gid, $course_id);
    } elseif (isset($_GET['removeReviewer'])) {
        $removed_reviewer_gid = intval(getDirectReference($_GET['removeReviewer']));
        Database::get()->query("UPDATE course_user SET status = " . USER_STUDENT . ", reviewer = 0
                        WHERE user_id <> ?d AND
                              user_id = ?d AND
                              course_id = ?d", $uid, $removed_reviewer_gid, $course_id);
    }
}

// show help link and link to Add new user, search new user and management page of groups
$num_requests = '';
$course_user_requests = FALSE;
if (course_status($course_id) == COURSE_CLOSED) {
    if (!setting_get(SETTING_COURSE_USER_REQUESTS_DISABLE, $course_id)) {
        $num_requests = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM course_user_request WHERE course_id = ?d AND status = 1", $course_id)->cnt;
        $course_user_requests = TRUE;
    }
}

$data['ajaxUrl'] = "$_SERVER[SCRIPT_NAME]?course=$course_code";
$data['action_bar'] = action_bar([
    ['title' => $langOneUser,
      'url' => "adduser.php?course=$course_code",
      'icon' => 'fa-plus-circle',
      'button-class' => 'btn-success',
      'level' => 'primary-label'],
    ['title' => $langManyUsers,
      'url' => "muladduser.php?course=$course_code",
      'icon' => 'fa-plus-circle',
      'button-class' => 'btn-success',
      'level' => 'primary-label'],
    ['title' => $langAddGUser,
      'url' => "guestuser.php?course=$course_code",
      'icon' => 'fa-plane',
      'show' => get_config('course_guest') != 'off'],
    ['title' => $num_requests . ' ' . trans('langsUserRequests'),
      'url' => "course_user_requests.php?course=$course_code",
      'icon' => 'fa-child',
      'level' => 'primary-label',
      'show' => $course_user_requests ],
    ['title' => $langGroupUserManagement,
      'url' => "../group/index.php?course=$course_code",
      'icon' => 'fa-users'],
    ['title' => $langDumpUser,
      'url' => "dumpuser.php?course=$course_code",
      'icon' => 'fa-file-archive-o'],
    ['title' => $langDelUsers,
      'url' => "../course_info/refresh_course.php?course=$course_code&amp;from_user=true",
      'icon' => 'fa-times',
      'button-class' => 'btn-danger']
]);

view('modules.user.index', $data);
