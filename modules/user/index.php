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

$require_login = true;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'course_users';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'include/course_settings.php';

$up = new Permissions();
if (!$up->has_course_users_permission()) {
    Session::Messages($langCheckCourseAdmin, 'alert-danger');
    redirect_to_home_page('courses/'. $course_code);
}

//Identifying ajax request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    && $up->has_course_users_permission()
    && isset($_POST['action'])
    && $_POST['action'] === 'updateRights') {

    $userId = intval($_POST['userID'] ?? 0);
    $adminModules = intval($_POST['userRights_CourseAdminTools'] ?? 0);
    $adminUsers = intval($_POST['userRights_AdminUsers'] ?? 0);
    $courseBackup = intval($_POST['userRights_ArchiveCourse'] ?? 0);
    $courseClone = intval($_POST['userRights_CloneCourse'] ?? 0);

    Database::get()->query(
        "INSERT INTO `user_permissions` (`course_id`, `user_id`, `admin_modules`, `admin_users`, `course_backup`, `course_clone`)
         VALUES (?d, ?d, ?d, ?d, ?d, ?d)
         ON DUPLICATE KEY UPDATE
            `admin_modules` = VALUES(`admin_modules`),
            `admin_users` = VALUES(`admin_users`),
            `course_backup` = VALUES(`course_backup`),
            `course_clone` = VALUES(`course_clone`)",
        $course_id, $userId, $adminModules, $adminUsers, $courseBackup, $courseClone
    );

}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    && $up->has_course_users_permission()
    && isset($_POST['action'])
    && $_POST['action'] === 'getUserRights') {

    $userId = intval($_POST['userID'] ?? 0);

    $result = Database::get()->querySingle("SELECT `admin_modules`, `admin_users`, `course_backup`, `course_clone`
                     FROM `user_permissions`
                     WHERE `course_id` = ?d AND `user_id` = ?d", $course_id, $userId);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit();

}

//if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && $up->has_course_users_permission()) {
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    && $up->has_course_users_permission()
    && !(isset($_POST['action']) && $_POST['action'] === 'updateRights')) {

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

    $limit = intval($_POST['length']);
    $offset = intval($_POST['start']);

    if (!empty($_POST['search']['value'])) {
        $search_values = array_fill(0, 4, '%' . $_POST['search']['value'] . '%');
        $search_sql = 'AND (user.surname LIKE ?s OR user.givenname LIKE ?s OR user.username LIKE ?s OR user.email LIKE ?s)';
    } else {
        $search_sql = '';
        $search_values = array();
    }
    // user status
    if (!empty($_POST['columns'][1]['search']['value'])) {
        $filter = $_POST['columns'][1]['search']['value'];
        if ($filter == 'editor') {
            $search_sql .= ' AND ((course_user.editor = 1 AND course_user.status = ' . USER_STUDENT . ') OR course_user.status = ' . USER_TEACHER . ')';
        } elseif ($filter == 'teacher') {
            $search_sql .= ' AND course_user.status = ' . USER_TEACHER;
        } elseif ($filter == 'student') {
            $search_sql .= ' AND course_user.editor <> 1 AND course_user.status = ' . USER_STUDENT;
        } elseif ($filter == 'guest') {
           $search_sql .= ' AND course_user.status = ' . USER_GUEST;
        } elseif ($filter == 'course_reviewer') {
            $search_sql .= ' AND course_user.course_reviewer = 1';
        } elseif ($filter == 'reviewer') {
            $search_sql .= ' AND course_user.reviewer = 1';
        }
    }
    $sortDir = 'ASC';
    if (isset($_POST['order'][0]['column']) && $_POST['order'][0]['column'] == 0) {
        $sortDir = (isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] == 'desc') ? 'DESC' : '';
        $order_sql = "ORDER BY user.surname $sortDir, user.givenname $sortDir";
    } else {
        $sortDir = (isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] == 'desc') ? 'DESC' : '';
        $order_sql = "ORDER BY course_user.reg_date $sortDir";
    }

    $limit_sql = ($limit > 0) ? "LIMIT $offset,$limit" : "";

    $all_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user
                                                WHERE `user`.`id` = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d", $course_id)->total;
    $filtered_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user
                                                WHERE `user`.`id` = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d $search_sql", $course_id, $search_values)->total;
    $result = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname, user.email,
                           user.am, user.has_icon, course_user.status,
                           course_user.tutor, course_user.editor, course_user.course_reviewer, course_user.reviewer,
                           DATE(course_user.reg_date) AS reg_date
                    FROM course_user, user
                    WHERE `user`.`id` = `course_user`.`user_id`
                    AND `course_user`.`course_id` = ?d
                    $search_sql $order_sql $limit_sql", $course_id, $search_values);

    $data['recordsTotal'] = $all_users;
    $data['recordsFiltered'] = $filtered_users;
    $data['aaData'] = array();
    foreach ($result as $myrow) {
        $full_name = q(sanitize_utf8($myrow->givenname . " " . $myrow->surname));
        $am = trim(q(sanitize_utf8($myrow->am)));
        $am_message = ($am !== '') ? "<div class='right'>$am</div>" : '';
        $stats_icon = icon('fa-bar-chart', $langUserStats, "../usage/userduration.php?course=$course_code&amp;u=$myrow->id");
        // create date field with unregister button
        $date_field = $myrow->reg_date ? format_locale_date(strtotime($myrow->reg_date), 'short', false) : $langUnknownDate;
        // checks if user is group tutor
        $is_tutor = false;
        $q_tutor = Database::get()->queryArray("SELECT is_tutor FROM group_members
                                                    WHERE user_id = ?d
                                                    AND group_id IN (SELECT id FROM `group` WHERE course_id = ?d)",
            $myrow->id, $course_id);
        if (count($q_tutor) > 0) {
            foreach ($q_tutor as $tutor_data) {
                if ($tutor_data->is_tutor == 1) {
                    $is_tutor = true;
                    break;
                }
            }
        }

        // Create appropriate role control buttons
        // Admin right
        $user_role_controls = '';
        if ($myrow->id != $_SESSION["uid"] && $myrow->reviewer == '1') {
            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;removeReviewer=$myrow->id'><img src='$themeimg/reviewer_remove.png' alt='$langRemoveRightReviewer' title='$langRemoveRightReviewer'></a>";
        } else {
            $user_role_controls .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;giveReviewer=$myrow->id'><img src='$themeimg/reviewer_add.png' alt='$langGiveRightReviewer' title='$langGiveRightReviewer'></a>";
        }

        if ($myrow->id == $_SESSION["uid"]) {
            $self_id = 1;
        } else {
            $self_id = 0;
        }

        // open-courses reviewer right
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
                'icon' => 'fa-xmark',
                'disabled' => ($myrow->id == $_SESSION["uid"] && get_number_of_course_admins($course_id, $myrow->id) == 0),
                'btn_class' => 'delete_btn deleteAdminBtn'
            ),
            array(
                'title' => $langCourseReviewer,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;" . ($myrow->course_reviewer == '0' ? "give" : "remove") . "CourseReviewer=" . getIndirectReference($myrow->id),
                'icon' => $myrow->course_reviewer == '0' ? "fa-square" : "fa-square-check",
                'show' => $myrow->status != USER_GUEST,
            ), array(
                'title' => $langConsultant,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;" . (($myrow->status == USER_STUDENT && $myrow->tutor == '1' && $myrow->editor == '0') ? "remove" : "give") . "Consultant=" . getIndirectReference($myrow->id),
                'icon' => ($myrow->status == USER_STUDENT && $myrow->tutor == '1' && $myrow->editor == '0') ? "fa-square-check" : "fa-square",
                'show' => ($myrow->status != USER_GUEST && isset($is_collaborative_course) && $is_collaborative_course && !is_module_disable(MODULE_ID_SESSION)),
            ),
            array(
                'title' => $langTeacher,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;" . ($myrow->editor == '0' ? "give" : "remove") . "Editor=" . getIndirectReference($myrow->id),
                'icon' => $myrow->editor == '0' ? "fa-square" : "fa-square-check",
                'show' => $myrow->status != USER_GUEST,
            ),
            array(
                'title' => $langCourseAdminTeacher,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;" . ($myrow->status == '1' ? "remove" : "give") . "Admin=" . getIndirectReference($myrow->id),
                'icon' => $myrow->status != '1' ? "fa-square" : "fa-square-check",
                'disabled' => $myrow->id == $_SESSION["uid"] || ($myrow->id != $_SESSION["uid"] && get_config('opencourses_enable') && $myrow->reviewer == '1'),
                'show' => $myrow->status != USER_GUEST,
            ),
            array(
                'title' => $langUserPermissions,
                'url' => "#",
                'icon' => "fa-list-check",
                'class' => 'rights_menu',
                'show' => ($myrow->status != USER_TEACHER && $myrow->status != USER_GUEST),
            ),
            array(
                'title' => $langGiveRightReviewer,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;" . ($myrow->reviewer == '1' ? "remove" : "give") . "Reviewer=" . getIndirectReference($myrow->id),
                'icon' => $myrow->reviewer != '1' ? "fa-square" : "fa-square-check",
                'disabled' => $myrow->id == $_SESSION["uid"],
                'show' => get_config('opencourses_enable') && $myrow->status != USER_GUEST &&
                    (
                        ($myrow->id == $_SESSION["uid"] && $myrow->reviewer == '1') ||
                        ($myrow->id != $_SESSION["uid"] && $is_opencourses_reviewer && $is_admin)
                    ) && ((isset($is_collaborative_course) && !$is_collaborative_course) or is_null($is_collaborative_course))
            ),
            array(
                'title' => $langModify,
                'url' => "guestuser.php?course=$course_code",
                'icon' => "fa-edit",
                'show' => $myrow->status == USER_GUEST,
            )
        ));
        if ($myrow->editor == '1' and $myrow->status != USER_TEACHER) {
            $user_roles = [$langTeacher];
        } elseif ($myrow->course_reviewer == '1' and $myrow->status != USER_TEACHER) {
            $user_roles = [$langCourseReviewer];
        } elseif ($myrow->status == USER_TEACHER) {
            $user_roles = [$langCourseAdminTeacher];
        } elseif ($myrow->status == USER_GUEST) {
            $user_roles = [$langGuestName];
        } elseif ($myrow->status == USER_STUDENT and $myrow->tutor == '1' and $myrow->editor == '0') {
            $user_roles = [$langConsultant];
        } else {
            $user_roles = [$langStudent];
        }

        if ($is_tutor) {
            $user_roles[] = $langGroupTutor;
        }
        if ($myrow->reviewer == '1') {
            $user_roles[] = $langOpenCoursesReviewer;
        }

        $user_role_string = implode(',<br>', $user_roles);
        // check if the user has extra permissions
        $user_extra_rights = '';
        $up->set_user_id($myrow->id);
        $user_array_permissions = $up->get_course_permissions();
        if (count($user_array_permissions) > 0) {
            $user_extra_rights = "<details><summary>$langUserPermissions</summary>";
            foreach ($user_array_permissions as $key => $permission) {
                $user_extra_rights .= "<div><small>". $up->get_permissions_legend($key) . "</small></div>";
            }
             $user_extra_rights .= "</details>";
        }
        // check for mail notifications
        if (!get_user_email_notification($myrow->id, $course_id)) {
            $email_exclamation_icon = "&nbsp;&nbsp;" . icon('fa-exclamation-triangle', $langNoUserEmailLegend);
        } else {
            $email_exclamation_icon = '';
        }

        $nameColumn = "<div>
                            <div class='d-flex justify-content-start align-items-start gap-2'>
                                <img style='width:32px; height:32px; border-radius:50%; border:solid 2px #e8e8e8;' alt='".$langUser."' class='img-circle' src='".user_icon($myrow->id) . "' />
                                <div style='padding-left:8px; padding-top: 5px;'>$stats_icon</div>
                            </div>
                            <div class='pull-left'>
                                <div data-userid='$myrow->id' style='padding-bottom:2px;'>".display_user($myrow->id, false, false, '', $course_code)."</div>
                                <div><small><a class='text-nowrap' aria-label='".$langProfileSendMail."' href='mailto:" . q($myrow->email) . "'>" . q($myrow->email) . "</a>$email_exclamation_icon</small></div>
                                <div class='text-muted'><small>$am_message</small></div>
                            </div>
                        </div>";
        $roleColumn = "<div class='text-muted' data-id='$self_id'>$user_role_string
                          $user_extra_rights  
                       </div>";
        // search for inactive users
        $inactive_user = is_inactive_user($myrow->id);
        //setting data table column data
        $data['aaData'][] = array(
            'DT_RowId' => getIndirectReference($myrow->id),
            'DT_RowClass' => 'smaller',
            'DT_RowData' => [
                'self_id' => $self_id
            ],
            '0' => $nameColumn,
            '1' => $roleColumn,
            '2' => "<div style='width: 200px;'>" . user_groups($course_id, $myrow->id) . "</div>",
            '3' => "<div class='text-start text-muted'>$date_field</div>",
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
} elseif (isset($_GET['giveEditor'])) {
    $new_editor_gid = intval(getDirectReference($_GET['giveEditor']));
    Database::get()->query("UPDATE course_user SET editor = 1
                        WHERE user_id = ?d
                        AND course_id = ?d", $new_editor_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $new_editor_gid,
                                                               'right' => '+2'));
} elseif (isset($_GET['giveCourseReviewer'])) {
    $new_course_reviewer_gid = intval(getDirectReference($_GET['giveCourseReviewer']));
    Database::get()->query("UPDATE course_user SET course_reviewer = 1
                            WHERE user_id = ?d
                            AND course_id = ?d", $new_course_reviewer_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
        'dest_uid' => $new_course_reviewer_gid,
        'right' => '+4'));
} elseif (isset($_GET['removeAdmin'])) {
    $removed_admin_gid = intval(getDirectReference($_GET['removeAdmin']));
    Database::get()->query("UPDATE course_user SET status = " . USER_STUDENT . "
                        WHERE user_id <> ?d AND
                              user_id = ?d AND
                              course_id = ?d", $uid, $removed_admin_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $removed_admin_gid,
                                                               'right' => '-1'));
} elseif (isset($_GET['removeEditor'])) {
    $removed_editor_gid = intval(getDirectReference($_GET['removeEditor']));
    Database::get()->query("UPDATE course_user SET editor = 0
                        WHERE user_id = ?d
                        AND course_id = ?d", $removed_editor_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
                                                               'dest_uid' => $removed_editor_gid,
                                                               'right' => '-2'));
} elseif (isset($_GET['removeCourseReviewer'])) {
    $removed_course_reviewer_gid = intval(getDirectReference($_GET['removeCourseReviewer']));
    Database::get()->query("UPDATE course_user SET course_reviewer = 0
                        WHERE user_id = ?d
                        AND course_id = ?d", $removed_course_reviewer_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
        'dest_uid' => $removed_course_reviewer_gid,
        'right' => '-4'));
} elseif(isset($_GET['removeConsultant'])){
    $removed_consultant_gid = intval(getDirectReference($_GET['removeConsultant']));
    Database::get()->query("UPDATE course_user SET tutor = 0, status = " . USER_STUDENT . ", editor = 0
                        WHERE user_id = ?d
                        AND course_id = ?d", $removed_consultant_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
        'dest_uid' => $removed_consultant_gid,
        'right' => '-5'));
} elseif(isset($_GET['giveConsultant'])){
    $give_consultant_gid = intval(getDirectReference($_GET['giveConsultant']));
    Database::get()->query("UPDATE course_user SET tutor = 1, status = " . USER_STUDENT . ", editor = 0
                        WHERE user_id = ?d
                        AND course_id = ?d", $give_consultant_gid, $course_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_MODIFY, array('uid' => $uid,
        'dest_uid' => $give_consultant_gid,
        'right' => '+5'));
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
    ['title' => "$langAdd $langOneUser",
      'url' => "adduser.php?course=$course_code",
      'icon' => 'fa-solid fa-user',
      'button-class' => 'btn-success',
      'level' => 'primary-label'],
    ['title' => "$langAdd $langManyUsers"   ,
      'url' => "muladduser.php?course=$course_code",
      'icon' => 'fa-solid fa-users',
      'button-class' => 'btn-success',
      'level' => 'primary-label'],
    ['title' => $langAddGUser,
      'url' => "guestuser.php?course=$course_code",
      'icon' => 'fa-solid fa-circle-user',
      'show' => get_config('course_guest') != 'off'],
    ['title' => $num_requests . ' ' . trans('langsUserRequests'),
      'url' => "course_user_requests.php?course=$course_code",
      'icon' => 'fa-solid fa-hand',
      'level' => 'primary-label',
      'show' => $course_user_requests ],
    ['title' => $langcourseExternalUsersInviation,
      'url' => "invite.php?course=$course_code",
      'icon' => 'fa-plus-circle',
      'show' => get_config('course_invitation')],
    ['title' => $langGroupUserManagement,
      'url' => "../group/index.php?course=$course_code",
      'icon' => 'fa-solid fa-user-group'],
    ['title' => $langDumpExcel,
      'url' => "dumpuser.php?course=$course_code",
      'icon' => 'fa-file-zipper'],
    ['title' => $langDelUsers,
      'url' => "../course_info/refresh_course.php?course=$course_code&amp;from_user=true",
      'icon' => 'fa-xmark',
      'button-class' => 'btn-danger',
      'show' => $is_course_admin]
]);

view('modules.user.index', $data);


/**
 * @brief get number of course admins
 * @param $course_id
 * @param $uid
 * @return int
 */
function get_number_of_course_admins($course_id, $uid) {

    $result = Database::get()->querySingle("SELECT COUNT(user_id) AS cnt FROM course_user
                                            WHERE course_id = ?d AND
                                                  status = " . USER_TEACHER . " AND
                                                  user_id != ?d
                                            LIMIT 1", $course_id, $uid);

    if ($result) {
        return $result->cnt;
    } else {
        return 0;
    }
}
