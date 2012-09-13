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


/* *===========================================================================
mergeuser.php
==============================================================================
@Description: Merge two users
==============================================================================
*/

$require_usermanage_user = true;
include '../../include/baseTheme.php';
include '../auth/auth.inc.php';
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
        $q = db_query("SELECT * FROM user WHERE user_id = $u");
        if ($q and mysql_num_rows($q)) {
                $info = mysql_fetch_assoc($q);
                $auth_id = isset($auth_ids[$info['password']])? $auth_ids[$info['password']]: 1;
                $legend = q(sprintf($langUserMergeLegend, $info['username']));
                $status_names = array(10 => $langGuest, 1 => $langTeacher, 5 => $langStudent);
                $target = false;
                if (isset($_POST['target'])) {
                        $q1 = db_query("SELECT * FROM user WHERE username COLLATE utf8_bin = " . quote($_POST['target']));
                        if ($q1 and mysql_num_rows($q1)) {
                                $target = mysql_fetch_assoc($q1);
                        }
                }
                $target_field = $target_user_input = '';
                $submit_button = $langSearch;
                if ($target) {
                        $target_auth_id = isset($auth_ids[$target['password']])? $auth_ids[$target['password']]: 1;
                        $target_field .= "<tr><th width='170' class='left'>$langUserMergeTarget:</th>
                                              <td>".display_user($target).
                                                  " (".q($target['username']).")</td></tr>
                                          <tr><th width='170' class='left'>$langEditAuthMethod</th>
                                              <td>".get_auth_info($target_auth_id)."</td></tr>
                                          <tr><th width='170' class='left'>$langProperty:</th>
                                              <td>".q($status_names[$target['statut']])."</td></tr>";
                        if ($info['statut'] == 1 and $target['statut'] != 1) {
                                $target = false;
                                $target_field .= "<tr><td colspan='2' class='alert1'>$langUserMergeForbidden</td></tr>";
                        } else {
                                if ($_POST['submit'] == $langUserMerge) {
                                        do_user_merge($info, $target);
                                }
                                $submit_button = $langUserMerge;
                                $target_user_input = '<input type="hidden" name="target" value="'.
                                        q($target['username']).'">';
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
                         <td>".display_user($info)."</td></tr>
                     <tr><th width='170' class='left'>$langEditAuthMethod</th>
                         <td>".get_auth_info($auth_id)."</td></tr>
                     <tr><th width='170' class='left'>$langProperty:</th>
                         <td>".q($status_names[$info['statut']])."</td></tr>
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


function do_user_merge($source, $target)
{
        global $langUserMergeSuccess, $langBack;

        $source_id = $source['user_id'];
        $target_id = $target['user_id'];
        $courses = array();
        $q_course = db_query("SELECT code FROM cours_user, cours
                                     WHERE cours.cours_id = cours_user.cours_id AND
                                           user_id = $target_id");
        while (list($code) = mysql_fetch_row($q_course)) {
                $courses[] = $code;
        }
        $tmp_table = "user_merge_{$source_id}_{$target_id}";
        $q = db_query("CREATE TEMPORARY TABLE `$tmp_table` AS
                              SELECT cours_id, $target_id AS user_id,
                                     MIN(statut) AS statut, MAX(team) AS team, MAX(tutor) AS tutor,
                                     MAX(editor) AS editor, MIN(reg_date) AS reg_date,
                                     MAX(receive_mail) AS receive_mail
                                 FROM cours_user
                                 WHERE user_id IN ($source_id, $target_id)
                                 GROUP BY cours_id");
        if ($q) {
                db_query("DELETE FROM user WHERE user_id = $source_id");
                db_query("DELETE FROM cours_user WHERE user_id IN ($source_id, $target_id)");
                db_query("INSERT INTO cours_user SELECT * FROM `$tmp_table`");
                db_query("DROP TEMPORARY TABLE `$tmp_table`");
                fix_table('forum_notify', 'user_id', $source_id, $target_id);
                fix_table('loginout', 'id_user', $source_id, $target_id);
                foreach ($courses as $code) {
                        mysql_select_db($code);
                        fix_table('actions', 'user_id', $source_id, $target_id);
                        fix_table('assignment_submit', 'uid', $source_id, $target_id);
                        fix_table('dropbox_file', 'uploaderId', $source_id, $target_id);
                        fix_table('dropbox_person', 'personId', $source_id, $target_id);
                        fix_table('dropbox_post', 'recipientId', $source_id, $target_id);
                        fix_table('exercise_user_record', 'uid', $source_id, $target_id);
                        fix_table('logins', 'user_id', $source_id, $target_id);
                        fix_table('lp_user_module_progress', 'user_id', $source_id, $target_id);
                        fix_table('poll', 'creator_id', $source_id, $target_id);
                        fix_table('poll_answer_record', 'user_id', $source_id, $target_id);
                        fix_table('posts', 'poster_id', $source_id, $target_id);
                        fix_table('topics', 'topic_poster', $source_id, $target_id);
                        fix_table('wiki_pages', 'owner_id', $source_id, $target_id);
                        fix_table('wiki_pages_content', 'editor_id', $source_id, $target_id);
                }
                $tool_content = sprintf('<p class="success">'.$langUserMergeSuccess.'</p>',
                                        '<b>'.q($source['username']).'</b>',
                                        '<b>'.q($target['username']).'</b>') .
                                "\n<p><a href='search_user.php'>$langBack</p>\n";

                draw($tool_content, 3);
                exit;
        }
}

function fix_table($table, $field, $source, $target)
{
        db_query("UPDATE IGNORE `$table`
                         SET `$field` = $target
                         WHERE `$field` = $source");
        db_query("DELETE FROM `$table` WHERE `$field` = $source");
}

