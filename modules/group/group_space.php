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
require_once 'group_functions.php';

if (!$uid or !$courses[$course_code]) {
    forbidden();
}

initialize_group_id();
initialize_group_info($group_id);

$toolName = $langGroups;
$pageName = $group_name;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);

if (isset($_GET['selfReg'])) {
    if (!$is_member and $status != USER_GUEST and ($max_members == 0 or $member_count < $max_members)) {
        $id = Database::get()->query("INSERT INTO group_members SET user_id = ?d, group_id = ?d, description = ''", $uid, $group_id);
        $group = gid_to_name($group_id);
        Log::record($course_id, MODULE_ID_GROUPS, LOG_MODIFY, array('id' => $id,
            'uid' => $uid,
            'name' => $group));

        Session::Messages($langGroupNowMember, 'alert-success');
        redirect_to_home_page("modules/group/group_space.php?course=$course_code&group_id=$group_id");
    } else {
        $tool_content .= "<div class='alert alert-danger'>$langForbidden</div>";
        draw($tool_content, 2);
        exit;
    }
}
if (!$is_member and !$is_editor) {
    $tool_content .= "<div class='alert alert-danger'>$langForbidden</div>";
    draw($tool_content, 2);
    exit;
}

$tool_content .= action_bar(array(
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
            array(
                'title' => $langBack,
                'level' => 'primary-label',
                'icon' => 'fa-reply',
                'url' => "javascript:history.back();"
            )
        ));

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
        $tutors[] = display_user($user->id, true, false);
    } else {
        $members[] = $user;
    }
}

if ($tutors) {
    $tool_content_tutor = implode(', ', $tutors);
} else {
    $tool_content_tutor = ' &nbsp;&nbsp;-&nbsp;&nbsp;  ';
}

$group_description = trim($group_description);
if (empty($group_description)) {
    $tool_content_description = ' &nbsp;&nbsp;-&nbsp;&nbsp;  ';
} else {
    $tool_content_description = q($group_description);
}

$tool_content .= "
    <div class='panel panel-action-btn-primary'>
        <div class='panel-heading'>
            <div class='pull-right'>
            ". (($is_editor) ? 
                    action_button(array(
                        array(
                            'title' => $langEditGroup,
                            'url' => "group_edit.php?course=$course_code&group_id=$group_id&from=group",
                            'level' => 'primary-label',
                            'icon' => 'fa-edit',
                            'show' => $is_editor or $is_tutor                            
                        )
                    )) : "")."    
            </div>        
            <h3 class='panel-title'>
            $langGroupInfo
            </h3>
        </div>
        <div class='panel-body'>
            <div class='row'>
                <div class='col-sm-3'><strong>$langGroupName:</strong></div>
                <div class='col-sm-9'>" . q($group_name) . "</div>
            </div>
            <div class='row'>
                <div class='col-sm-3'><strong>$langGroupTutor:</strong></div>
                <div class='col-sm-9'>$tool_content_tutor</div>
            </div>
            <div class='row'>
                <div class='col-sm-3'><strong>$langDescription:</strong></div>
                <div class='col-sm-9'>$tool_content_description</div>
            </div>
        </div>
    </div>";

// members
if (count($members) > 0) { 
$tool_content .= "   
                    <div class='row'>
                        <div class='col-xs-12'>
                          <ul class='list-group'>
                              <li class='list-group-item list-header'>
                                <div class='row'>
                                    <div class='text-center'>
                                        <b>$langGroupMembers</b>
                                    </div>
                                </div>
                                  <div class='row'>
                                      <div class='col-xs-4'>$langSurnameName</div>
                                      <div class='col-xs-4'>$langAm</div>
                                      <div class='col-xs-4'>$langEmail</div>
                                  </div>
                              </li>";
   
    foreach ($members as $member) {
        $user_group_description = $member->description;        
        $tool_content .= "<li class='list-group-item'><div class='row'><div class='col-xs-4'>" . display_user($member->id, false, true);
        if ($user_group_description) {
            $tool_content .= "<br />" . q($user_group_description);
        }
        $tool_content .= "</div><div class='col-xs-4'>";
        if (!empty($member->am)) {
            $tool_content .= q($member->am);
        } else {
            $tool_content .= '-';
        }
        $tool_content .= "</div><div class='col-xs-4'>";
        $email = q(trim($member->email));
        if (!empty($email)) {
            $tool_content .= "<a href='mailto:$email'>$email</a>";
        } else {
            $tool_content .= '-';
        }
        $tool_content .= "</div></div></li>";     
    }
    $tool_content .= "</ul>";
    $tool_content .= "</div></div>";
} else {
    $tool_content .= "<div class='alert alert-warning'>$langGroupNoneMasc</div>";
}

draw($tool_content, 2);

