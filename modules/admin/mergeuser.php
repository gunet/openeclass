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


/**
  @file mergeuser.php
  @Description: Merge two users
 */

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

$toolName = $langUserMerge;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listusers.php', 'name' => $langListUsersActions);

$data['merge_completed'] = false;

if (isset($_REQUEST['u'])) {
    $data['u'] = $u = intval($_REQUEST['u']);
    $navigation[] = array('url' => "edituser.php?u=$u", 'name' => $langEditUser);
    if ($u == 1 or get_admin_rights($u) >= 0) {
        Session::flash('message',$langUserMergeAdminForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/admin/edituser.php?u=$u");
    }
    $info = Database::get()->querySingle("SELECT * FROM user WHERE id = ?s", $u);
    if ($info) {
        $info = (array) $info;
        if (in_array($info['password'], $auth_ids)) {
            $temp = array_keys($auth_ids, $info['password']);// to avoid strict standards warning
            $data['auth_id'] = array_pop($temp);
        } else {
            $data['auth_id'] = 1; // eclass default method
        }

        $legend = q(sprintf($langUserMergeLegend, $info['username']));
        $data['status_names'] = $status_names = array(USER_GUEST => $langGuest, USER_TEACHER => $langTeacher, USER_STUDENT => $langStudent);
        $target = false;

        $pageName = $legend;
        $data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                'url' => "index.php",
                'icon' => 'fa-reply',
                'level' => 'primary')));

        if (isset($_POST['target'])) {
            $target = Database::get()->querySingle("SELECT * FROM user WHERE username COLLATE utf8mb4_bin = ?s", $_POST['target']);
            if ($target) {
                if ($target->id == $u) {
                    $target = false;
                    Session::flash('message',$langMergeUserWithSelf);
                    Session::flash('alert-class', 'alert-warning');
                } else {
                    $target = (array) $target;
                }
            } else {
                $target = false;
                Session::flash('message',q(sprintf($langChangeUserNotFound, $_POST['target'])));
                Session::flash('alert-class', 'alert-warning');
            }
        }

        $data['target_field'] = $data['target_user_input'] = '';
        $data['submit_button'] = $langSearch;

        if ($target) {
            if (in_array($target['password'], $auth_ids)) {
                $temp = array_keys($auth_ids, $target['password']);// to avoid strict standards warning
                $target_auth_id = array_pop($temp);
            } else {
                $target_auth_id = 1; // eclass default method
            }
            $data['target_field'] .= "<div class='form-group mt-3'><div class='col-sm-12 control-label-notes'>$langUserMergeTarget:</div>
                                              <div class='col-sm-12'><p class='form-control-static'>" . display_user($target['id']) .
                    " (" . q($target['username']) . ")</p></div></div>
                            <div class='form-group mt-3'>
                                <div class='col-sm-12 control-label-notes'>$langEditAuthMethod:</div>
                                <div class='col-sm-12'>" . get_auth_info($target_auth_id) . "</div>
                            </div>
                            <div class='form-group mt-3'>
                                <div class='col-sm-12 control-label-notes'>$langProperty:</div>                                          
                                <div class='col-sm-12'>" . q($status_names[$target['status']]) . "</div></div>";
            if ($info['status'] == USER_TEACHER and $target['status'] != USER_TEACHER) {
                $target = false;
                $data['target_field'] .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langUserMergeForbidden</span></div>";
            } else {
                if ($_POST['submit'] == $langUserMerge) {
                    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
                    checkSecondFactorChallenge();
                    do_user_merge($info, $target);
                    $data['merge_completed'] = true;
                }
                $data['submit_button'] = $langUserMerge;
                $data['target_user_input'] = '<input type="hidden" name="target" value="' .
                        q($target['username']) . '">';
            }
        }
        if (!$target) {
            $data['target_field'] .= "<div class='form-group mt-3'><label for='target_id' class='col-sm-12 control-label-notes'>$langUserMergeTarget:</label>
                                              <div class='col-sm-12'><input id='target_id' class='form-control' type='text' name='target' size='30'></div></div>";
        }
    }
    $data['info'] = $info;
}
view('admin.users.mergeuser', $data);


/**
 * merge users
 * @global type $langUserMergeSuccess
 * @global type $langBack
 * @param type $source
 * @param type $target
 */
function do_user_merge($source, $target) {
    global $langUserMergeSuccess, $langBack;

    $source_id = $source['id'];
    $target_id = $target['id'];
    $courses = array();
    Database::get()->queryFunc("SELECT code FROM course_user, course
                                     WHERE course.id = course_user.course_id AND
                                           user_id = ?d"
            , function($row) use(&$courses) {
        $courses[] = $row->code;
    }, $target_id);
    $tmp_table = "user_merge_{$source_id}_{$target_id}";
    $q = Database::get()->query("CREATE TEMPORARY TABLE `$tmp_table` AS
                              SELECT course_id, $target_id AS user_id,
                                     MIN(status) AS status,
                                     MAX(tutor) AS tutor,
                                     MAX(editor) AS editor,
                                     MAX(course_reviewer) AS course_reviewer,
                                     MAX(reviewer) AS reviewer,
                                     MIN(reg_date) AS reg_date,
                                     MAX(receive_mail) AS receive_mail,
                                     MAX(document_timestamp) AS document_timestamp,
                                     favorite
                                 FROM course_user
                                 WHERE user_id IN ($source_id, $target_id)
                                 GROUP BY course_id");
    if ($q) {
        fix_table('user_badge_criterion', 'user', $source_id, $target_id);
        fix_table('user_badge', 'user', $source_id, $target_id);
        fix_table('user_certificate_criterion', 'user', $source_id, $target_id);
        fix_table('user_certificate', 'user', $source_id, $target_id);
        Database::get()->query("DELETE FROM user WHERE id = ?d", $source_id);
        Database::get()->query("DELETE FROM course_user WHERE user_id IN ($source_id, $target_id)");
        Database::get()->query("INSERT INTO course_user SELECT * FROM `$tmp_table`");
        Database::get()->query("DROP TEMPORARY TABLE `$tmp_table`");
        fix_table('loginout', 'id_user', $source_id, $target_id);
        fix_table('log', 'user_id', $source_id, $target_id);
        fix_table('assignment_submit', 'uid', $source_id, $target_id);
        fix_table('group_members', 'user_id', $source_id, $target_id);
        fix_table('dropbox_index', 'recipient_id', $source_id, $target_id);
        fix_table('dropbox_msg', 'author_id', $source_id, $target_id);
        fix_table('exercise_user_record', 'uid', $source_id, $target_id);
        fix_table('logins', 'user_id', $source_id, $target_id);
        fix_table('lp_user_module_progress', 'user_id', $source_id, $target_id);
        fix_table('poll', 'creator_id', $source_id, $target_id);
        fix_table('poll_user_record', 'uid', $source_id, $target_id);
        fix_table('forum_notify', 'user_id', $source_id, $target_id);
        fix_table('forum_post', 'poster_id', $source_id, $target_id);
        fix_table('forum_topic', 'poster_id', $source_id, $target_id);
        fix_table('wiki_pages', 'owner_id', $source_id, $target_id);
        fix_table('wiki_pages_content', 'editor_id', $source_id, $target_id);
        fix_table('gradebook_users', 'uid', $source_id, $target_id);
        fix_table('gradebook_book', 'uid', $source_id, $target_id);
        fix_table('attendance_users', 'uid', $source_id, $target_id);
        fix_table('attendance_book', 'uid', $source_id, $target_id);
        fix_table('blog_post', 'user_id', $source_id, $target_id);
        fix_table('comments', 'user_id', $source_id, $target_id);
        fix_table('personal_calendar', 'user_id', $source_id, $target_id);
        fix_table('personal_calendar_settings', 'user_id', $source_id, $target_id);
        fix_table('rating', 'user_id', $source_id, $target_id);
        fix_table('user_department', 'user', $source_id, $target_id);
        fix_table('custom_profile_fields_data', 'user_id', $source_id, $target_id);

        Session::flash('message',sprintf($langUserMergeSuccess, '<strong>' . q($source['username']) . '</strong>', '<strong>' . q($target['username']) . '</strong>'));
        Session::flash('alert-class', 'alert-success');
    }
}

/**
 * update table with new user_id
 * @param type $table
 * @param type $field
 * @param type $source
 * @param type $target
 */
function fix_table($table, $field, $source, $target): void
{
    Database::get()->query("UPDATE IGNORE `$table`
                         SET `$field` = $target
                         WHERE `$field` = $source");
    Database::get()->query("DELETE FROM `$table` WHERE `$field` = $source");
}
