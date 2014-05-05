<?php

/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


/* * ===========================================================================
  mergeuser.php
  ==============================================================================
  @Description: Merge two users
  ==============================================================================
 */

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
$nameTools = $langUserMerge;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listusers.php', 'name' => $langListUsersActions);

if (isset($_REQUEST['u'])) {
    $u = intval($_REQUEST['u']);
    $navigation[] = array('url' => "edituser.php?u=$u", 'name' => $langEditUser);
    if ($u == 1 or get_admin_rights($u) >= 0) {
        $tool_content = "<p class='caution'>$langUserMergeAdminForbidden</p>";
        draw($tool_content, 3);
        exit;
    }
    $info = Database::get()->querySingle("SELECT * FROM user WHERE user_id = ?s", $u);
    if ($info) {
        $info = (array) $info;
        $auth_id = isset($auth_ids[$info['password']]) ? $auth_ids[$info['password']] : 1;
        $legend = q(sprintf($langUserMergeLegend, $info['username']));
        $status_names = array(10 => $langGuest, 1 => $langTeacher, 5 => $langStudent);
        $target = false;
        if (isset($_POST['target'])) {
            $target = Database::get()->querySingle("SELECT * FROM user WHERE username COLLATE utf8_bin = ?s", $_POST['target']);
            if ($target)
                $target = (array) $target;
            else
                $target = false;
        }
        $target_field = $target_user_input = '';
        $submit_button = $langSearch;
        if ($target) {
            $target_auth_id = isset($auth_ids[$target['password']]) ? $auth_ids[$target['password']] : 1;
            $target_field .= "<tr><th width='170' class='left'>$langUserMergeTarget:</th>
                                              <td>" . display_user($target) .
                    " (" . q($target['username']) . ")</td></tr>
                                          <tr><th width='170' class='left'>$langEditAuthMethod</th>
                                              <td>" . get_auth_info($target_auth_id) . "</td></tr>
                                          <tr><th width='170' class='left'>$langProperty:</th>
                                              <td>" . q($status_names[$target['status']]) . "</td></tr>";
            if ($info['status'] == 1 and $target['status'] != 1) {
                $target = false;
                $target_field .= "<tr><td colspan='2' class='alert1'>$langUserMergeForbidden</td></tr>";
            } else {
                if ($_POST['submit'] == $langUserMerge) {
                    do_user_merge($info, $target);
                }
                $submit_button = $langUserMerge;
                $target_user_input = '<input type="hidden" name="target" value="' .
                        q($target['username']) . '">';
            }
        }
        if (!$target) {
            $target_field .= "<tr><th width='170' class='left'>$langUserMergeTarget:</th>
                                              <td><input type='text' name='target' size='50'></td></tr>";
        }
        $tool_content = "<form method='post' action='$_SERVER[SCRIPT_NAME]'>
                 <fieldset>
                   <legend>$legend</legend>
                   <table class='tbl' width='100%'>
                     <tr><th width='170' class='left'>$langUser:</th>
                         <td>" . display_user($info) . "</td></tr>
                     <tr><th width='170' class='left'>$langEditAuthMethod</th>
                         <td>" . get_auth_info($auth_id) . "</td></tr>
                     <tr><th width='170' class='left'>$langProperty:</th>
                         <td>" . q($status_names[$info['status']]) . "</td></tr>
                     $target_field
                     <tr><th>&nbsp;</th>
                         <td class='right'>
                           <input type='hidden' name='u' value='$u'>
                           <input type='submit' name='submit' value='$submit_button'></td></tr>
                   </table>
                 </fieldset>
                 $target_user_input
               </form>";
    }
} else {
    $tool_content .= "<h1>$langError</h1>\n<p><a href='search_user.php'>$langBack</p>\n";
}

draw($tool_content, 3, null, $head_content);

function do_user_merge($source, $target) {
    global $langUserMergeSuccess, $langBack;

    $source_id = $source['user_id'];
    $target_id = $target['user_id'];
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
                                     MIN(status) AS status, MAX(team) AS team, MAX(tutor) AS tutor,
                                     MAX(editor) AS editor, MAX(reviewer) AS reviewer, MIN(reg_date) AS reg_date,
                                     MAX(receive_mail) AS receive_mail
                                 FROM course_user
                                 WHERE user_id IN ($source_id, $target_id)
                                 GROUP BY course_id");
    if ($q) {
        Database::get()->query("DELETE FROM user WHERE user_id = ?d", $source_id);
        Database::get()->query("DELETE FROM course_user WHERE user_id IN ($source_id, $target_id)");
        Database::get()->query("INSERT INTO course_user SELECT * FROM `$tmp_table`");
        Database::get()->query("DROP TEMPORARY TABLE `$tmp_table`");
        fix_table('forum_notify', 'user_id', $source_id, $target_id);
        fix_table('loginout', 'id_user', $source_id, $target_id);
        fix_table('log', 'user_id', $source_id, $target_id);
        fix_table('assignment_submit', 'uid', $source_id, $target_id);
        fix_table('dropbox_file', 'uploaderId', $source_id, $target_id);
        fix_table('dropbox_person', 'personId', $source_id, $target_id);
        fix_table('dropbox_post', 'recipientId', $source_id, $target_id);
        fix_table('exercise_user_record', 'uid', $source_id, $target_id);
        fix_table('logins', 'user_id', $source_id, $target_id);
        fix_table('lp_user_module_progress', 'user_id', $source_id, $target_id);
        fix_table('poll', 'creator_id', $source_id, $target_id);
        fix_table('poll_answer_record', 'user_id', $source_id, $target_id);
        fix_table('forum_post', 'poster_id', $source_id, $target_id);
        fix_table('forum_topic', 'poster_id', $source_id, $target_id);
        fix_table('wiki_pages', 'owner_id', $source_id, $target_id);
        fix_table('wiki_pages_content', 'editor_id', $source_id, $target_id);

        $tool_content = sprintf('<p class="success">' . $langUserMergeSuccess . '</p>', '<b>' . q($source['username']) . '</b>', '<b>' . q($target['username']) . '</b>') .
                "\n<p><a href='search_user.php'>$langBack</p>\n";

        draw($tool_content, 3);
        exit;
    }
}

function fix_table($table, $field, $source, $target) {
    Database::get()->query("UPDATE IGNORE `$table`
                         SET `$field` = $target
                         WHERE `$field` = $source");
    Database::get()->query("DELETE FROM `$table` WHERE `$field` = $source");
}
