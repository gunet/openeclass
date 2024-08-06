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

$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'groups';

require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';
require_once 'include/log.class.php';

initialize_group_id();
initialize_group_info($group_id);

$toolName = $langGroups;
$pageName = q($group_name) . ' - ' . $langAddManyUsers;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);
$navigation[] = array('url' => "group_space.php?course=$course_code&amp;group_id=$group_id", 'name' => $group_name);

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    if ($_POST['type'] == 'am') {
        $field = 'am';
    } else {
        $field = 'username';
    }
    $users = explode(' ', canonicalize_whitespace(str_replace(["\n", "\r"], ' ', $_POST['user_info'])));
    $placeholders = '(' . implode(', ', array_fill(0, count($users), '?s')) . ')';
    $info = Database::get()->queryArray("SELECT id, username, am, course_user.user_id AS registered
        FROM user LEFT JOIN course_user ON user.id = course_user.user_id AND course_id = ?d
        WHERE user.`$field` IN $placeholders", $course_id, $users);
    $not_found_users = array_flip($users);
    $not_registered_users = [];
    $found_users = [];
    $identifiers = [];
    foreach ($info as $item) {
        $identifier = ($field == 'am')? $item->am: $item->username;
        unset($not_found_users[$identifier]);
        if (!$item->registered) {
            $not_registered_users[] = $identifier;
        } else {
            $found_users[] = $item->id;
        }
        $identifiers[$item->id] = $identifier;
    }
    $errors = [];
    if ($not_found_users) {
        $errors[] = "$langUsersNotFound:<br>" . implode('<br>', array_map('q', array_keys($not_found_users)));
    }
    if ($not_registered_users) {
        $errors[] = "$langUsersNotRegistered:<br>" . implode('<br>', array_map('q', $not_registered_users));
    }
    $multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);
    $placeholders = '(' . implode(', ', array_fill(0, count($found_users), '?d')) . ')';
    if ($found_users and ($multi_reg == 0 or $multi_reg == 2)) {
        if ($multi_reg == 2) {
            // Users can register to one group per category
            $other_group_users = Database::get()->queryArray("SELECT user_id, `group`.name
                FROM group_members, `group`
                WHERE course_id = ?d AND group_id <> ?d AND category_id = ?d AND
                      group_id = `group`.id AND user_id IN $placeholders",
                $course_id, $group_id, $group_category, $found_users);
        } else {
            // Users can only register to single group
            $other_group_users = Database::get()->queryArray("SELECT user_id, `group`.name
                FROM group_members, `group`
                WHERE course_id = ?d AND group_id <> ?d AND group_id = `group`.id AND
                      user_id IN $placeholders",
                $course_id, $group_id, $found_users);
        }
        if ($other_group_users) {
            $message = $langUsersInOtherGroups . ':';
            foreach ($other_group_users as $item) {
                $message .= '<br>' . q($identifiers[$item->user_id]) . ': ' . q($item->name);
            }
            $errors[] = $message;
        }
    }
    if ($found_users and $max_members) {
        $current_members = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM group_members
            WHERE group_id = ?d AND is_tutor = 0 AND user_id NOT IN $placeholders",
            $group_id, $found_users)->cnt;
        $future_members = $current_members + count($found_users);
        if ($future_members > $max_members) {
            $errors[] = sprintf($langUsersOverMaximum, $future_members, $max_members);
        }
    }
    if ($errors) {
        //Session::Messages($langUsersNotAdded, 'alert-warning');
        Session::flash('message', $langUsersNotAdded);
        Session::flash('alert-class', 'alert-warning');
        //Session::Messages($errors, 'alert-info');
        Session::flash('message', $errors);
        Session::flash('alert-class', 'alert-info');
        Session::flashPost();
        redirect_to_home_page("modules/group/muladduser.php?course=$course_code&group_id=$group_id");
    } else {
        $user_group_data = array_map(function ($user) {
            global $group_id;
            return [$group_id, $user];
        }, $found_users);
        $placeholders = implode(', ', array_fill(0, count($found_users), '(?d, ?d, 0, \'\')'));
        Database::get()->query('INSERT IGNORE INTO group_members
            (group_id, user_id, is_tutor, description) VALUES ' . $placeholders,
            $user_group_data);
        //Session::Messages($langUsersAddedToGroup, 'alert-success');
        Session::flash('message', $langUsersAddedToGroup);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/group/group_space.php?course=$course_code&group_id=$group_id");
    }
}

$field = Session::get('type');
if (!$field) {
    $checked_uname = 'checked';
    $checked_am = '';
} else {
    $checked_uname = ($field == 'uname')? 'checked': '';
    $checked_am = ($field == 'am')? 'checked': '';
}
$tool_content .= "
<div class='d-lg-flex gap-4 mt-4'>
<div class='flex-grow-1'><div class='form-wrapper form-edit rounded'><p class='mb-4'>$langGroupManyUsers</p>
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;group_id=$group_id'>
        <fieldset>
            <div class='form-group'>
               <div class='col-sm-12 radio mb-2'><label><input type='radio' name='type' value='uname' $checked_uname> $langUsername</label></div>
               <div class='col-sm-12 radio'><label><input type='radio' name='type' value='am' $checked_am> $langAm</label></div>
            </div>
            <div class='form-group mt-4'>
                <textarea aria-label='$langTypeOutMessage' class='form-control' name='user_info' rows='10'>" . q(Session::get('user_info')) . "</textarea>
            </div>
            <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2'>
                <input class='btn submitAdminBtn' type='submit' name='submit' value='$langAdd'>
                <a href='group_space.php?course=$course_code&amp;group_id=$group_id' class='btn cancelAdminBtn'>$langCancel</a>
            </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."
        </form>
    </div></div><div class='d-none d-lg-block'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div>
</div>";

draw($tool_content, 2);
