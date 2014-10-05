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
 * 
 * @file group_space.php
 * @brief Display user group info
 */
$require_login = true;
$require_current_course = true;
$require_help = true;
$helpTopic = 'Group';

require_once '../../include/baseTheme.php';
require_once 'include/log.php';

$nameTools = $langGroupSpace;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);
require_once 'group_functions.php';

initialize_group_id();
initialize_group_info($group_id);

if (isset($_GET['selfReg'])) {
    if (isset($uid) and ! $is_member and $status != USER_GUEST) {
        if ($max_members == 0 or $member_count < $max_members) {
            $id = Database::get()->query("INSERT INTO group_members SET user_id = ?d, group_id = ?d, description = ''", $uid, $group_id);
            $group = gid_to_name($group_id);
            Log::record($course_id, MODULE_ID_GROUPS, LOG_MODIFY, array('id' => $id,
                'uid' => $uid,
                'name' => $group));

            $message = "<font color=red>$langGroupNowMember</font>";
            $regDone = $is_member = true;
        }
    } else {
        $tool_content .= "<p class='caution'>$langForbidden</p>";
        draw($tool_content, 2);
        exit;
    }
}
if (!$is_member and ! $is_editor and ( !$self_reg or $member_count >= $max_members)) {
    $tool_content .= $langForbidden;
    draw($tool_content, 2);
    exit;
}

if (isset($regDone)) {
    $tool_content .= "$message&nbsp;";
}

$tool_content .= "<div id='operations_container'>" .
        action_bar(array(
            array('title' => $langEditGroup,
                'url' => "group_edit.php?course=$course_code&amp;group_id=$group_id",
                'icon' => 'fa-edit',
                'level' => 'primary',
                'show' => $is_editor or $is_tutor),
            array('title' => $langRegIntoGroup,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;registration=1&amp;group_id=$group_id",
                'icon' => 'fa-plus-circle',
                'level' => 'primary',
                'show' => !($is_editor or $is_tutor) && ($max_members == 0 or $member_count < $max_members)),
            array('title' => $langForums,
                'url' => "../forum/viewforum.php?course=$course_code&amp;forum=$forum_id",
                'icon' => 'fa-comments',
                'level' => 'primary',
                'show' => $has_forum and $forum_id <> 0),
            array('title' => $langGroupDocumentsLink,
                'url' => "document.php?course=$course_code&amp;group_id=$group_id",
                'icon' => 'fa-folder-open',
                'level' => 'primary',
                'show' => $documents),
            array('title' => $langWiki,
                'url' => "../wiki/?course=$course_code&amp;gid=$group_id",
                'icon' => 'fa-globe',
                'level' => 'primary',
                'show' => $wiki),
            array('title' => $langEmailGroup,
                'url' => "group_email.php?course=$course_code&amp;group_id=$group_id",
                'icon' => 'fa-envelope',
                'level' => 'primary',
                'show' => $is_editor or $is_tutor),
        )) .
        "</div>";

$tool_content .= "<br />
    <fieldset>
    <legend>$langGroupInfo</legend>
    <table width='100%' class='tbl'>
    <tr>
      <th class='left' width='180'>$langGroupName:</th>
      <td>" . q($group_name) . "</td>
    </tr>";

$tutors = array();
$members = array();
$q = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname, user.email, user.am, user.has_icon, group_members.is_tutor, 
		      group_members.description
                      FROM group_members, user
                      WHERE group_members.group_id = ?d AND
                            group_members.user_id = user.id
                      ORDER BY user.surname, user.givenname", $group_id);
foreach ($q as $user) {
    if ($user->is_tutor) {
        $tutors[] = display_user($user->id, true);
    } else {
        $members[] = $user;
    }
}

if ($tutors) {
    $tool_content_tutor = implode(', ', $tutors);
} else {
    $tool_content_tutor = $langGroupNoTutor;
}

$tool_content .= "<tr><th class='left'>$langGroupTutor:</th>
                <td>$tool_content_tutor</td></tr>";

$group_description = trim($group_description);
if (empty($group_description)) {
    $tool_content_description = $langGroupNone;
} else {
    $tool_content_description = q($group_description);
}

$tool_content .= "<tr><th class='left'>$langDescription:</th>
      <td>$tool_content_description</td></tr>";

// members
$tool_content .= "
    <tr>
      <th class='left' valign='top'>$langGroupMembers:</th>
      <td>
        <table width='100%' align='center' class='tbl_alt'>
        <tr>
          <th class='left'>$langSurnameName</th>
          <th class='center' width='120'>$langAm</th>
          <th class='center' width='150'>$langEmail</th>
        </tr>";

if (count($members) > 0) {
    $i = 0;
    foreach ($members as $member) {
        $user_group_description = $member->description;
        if ($i % 2 == 0) {
            $tool_content .= "<tr class='even'>";
        } else {
            $tool_content .= "<tr class='odd'>";
        }
        $tool_content .= "<td>" . display_user($member->id);
        if ($user_group_description) {
            $tool_content .= "<br />" . q($user_group_description);
        }
        $tool_content .= "</td><td class='center'>";
        if (!empty($member->am)) {
            $tool_content .= q($member->am);
        } else {
            $tool_content .= '-';
        }
        $tool_content .= "</td><td class='center'>";
        $email = q(trim($member->email));
        if (!empty($email)) {
            $tool_content .= "<a href='mailto:$email'>$email</a>";
        } else {
            $tool_content .= '-';
        }
        $tool_content .= "</td></tr>";
        $i++;
    }
} else {
    $tool_content .= "<tr><td colspan='3'>$langGroupNoneMasc</td></tr>";
}

$tool_content .= "</table>";
$tool_content .= "</td></tr></table></fieldset>";
draw($tool_content, 2);

