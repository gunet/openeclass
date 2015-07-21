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
  @file mergeuser.php
  @Description: Merge two users  
 */

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

$toolName = $langUserMerge;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listusers.php', 'name' => $langListUsersActions);

if (isset($_REQUEST['u'])) {
    $u = intval($_REQUEST['u']);
    $navigation[] = array('url' => "edituser.php?u=$u", 'name' => $langEditUser);
    if ($u == 1 or get_admin_rights($u) >= 0) {
        $tool_content = "<div class='alert alert-danger'>$langUserMergeAdminForbidden</div>";
        draw($tool_content, 3);
        exit;
    }
    $info = Database::get()->querySingle("SELECT * FROM user WHERE id = ?s", $u);
    if ($info) {
        $info = (array) $info;
        $auth_id = isset($auth_ids[$info['password']]) ? $auth_ids[$info['password']] : 1;
        $legend = q(sprintf($langUserMergeLegend, $info['username']));
        $status_names = array(USER_GUEST => $langGuest, USER_TEACHER => $langTeacher, USER_STUDENT => $langStudent);
        $target = false;
        
        $pageName = $legend;
        $tool_content .= action_bar(array(            
            array('title' => $langBack,
                'url' => "index.php",
                'icon' => 'fa-reply',
                'level' => 'primary')));
        
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
            $target_field .= "<div class='form-group'><label class='col-sm-3 control-label'>$langUserMergeTarget:</label>
                                              <div class='col-sm-9'>" . display_user($target['id']) .
                    " (" . q($target['username']) . ")</div></div>
                                    <div class='form-group'><label class='col-sm-3 control-label'>$langEditAuthMethod:</label>
                                              <div class='col-sm-9'>" . get_auth_info($target_auth_id) . "</div></div>
                                                  <div class='form-group'><label class='col-sm-3 control-label'>$langProperty:</label>                                          
                                              <div class='col-sm-9'>" . q($status_names[$target['status']]) . "</div></div>";
            if ($info['status'] == USER_TEACHER and $target['status'] != USER_TEACHER) {
                $target = false;
                $target_field .= "<div class='alert alert-warning'>$langUserMergeForbidden</div>";
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
            $target_field .= "<div class='form-group'><label class='col-sm-3 control-label'>$langUserMergeTarget:</label>
                                              <div class='col-sm-9'><input type='text' name='target' size='50'></div></div>";
        }                
        $tool_content = "<div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]'>
                 <fieldset>                                    
                   <div class='form-group'>
                     <label class='col-sm-3 control-label'>$langUser:</label>
                        <div class='col-sm-9'>" . display_user($info['id']) . "</div>
                    </div>
                    <div class='form-group'>
                    <label class='col-sm-3 control-label'>$langEditAuthMethod:</label>
                         <div class='col-sm-9'>" . get_auth_info($auth_id) . "</div>
                    </div>
                    <div class='form-group'>
                    <label class='col-sm-3 control-label'>$langProperty:</label>                     
                         <div class='col-sm-9'>" . q($status_names[$info['status']]) . "</div>
                    </div>                    
                     $target_field
                    <input type='hidden' name='u' value='$u'>
                     <div class='col-sm-offset-3 col-sm-9'>                                                  
                           <input class='btn btn-primary' type='submit' name='submit' value='$submit_button'>
                    </div>                                                  
                 </fieldset>
                 $target_user_input
               </form></div>";
    }
} else {
    $tool_content .= "<div class='alert alert-danger'>$langError<br><a href='search_user.php'>$langBack</div>";
}

draw($tool_content, 3, null, $head_content);


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
                                     MIN(status) AS status, MAX(tutor) AS tutor,
                                     MAX(editor) AS editor, MAX(reviewer) AS reviewer, MIN(reg_date) AS reg_date,
                                     MAX(receive_mail) AS receive_mail,
                                     MAX(document_timestamp) AS document_timestamp
                                 FROM course_user
                                 WHERE user_id IN ($source_id, $target_id)
                                 GROUP BY course_id");
    if ($q) {
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
        fix_table('poll_answer_record', 'user_id', $source_id, $target_id);
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
        

        $tool_content = sprintf('<div class="alert alert-success">' . $langUserMergeSuccess . '</div>', '<b>' . q($source['username']) . '</b>', '<b>' . q($target['username']) . '</b>') .
                "<p><a href='search_user.php'>$langBack</p>";

        draw($tool_content, 3);
        exit;
    }
}

/**
 * update table with new user_id
 * @param type $table
 * @param type $field
 * @param type $source
 * @param type $target
 */
function fix_table($table, $field, $source, $target) {
    Database::get()->query("UPDATE IGNORE `$table`
                         SET `$field` = $target
                         WHERE `$field` = $source");
    Database::get()->query("DELETE FROM `$table` WHERE `$field` = $source");
}
