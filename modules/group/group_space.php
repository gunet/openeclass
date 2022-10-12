<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
$require_user_registration = TRUE;
$require_help = true;
$helpTopic = 'groups';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';

if ((isset($_GET['selfReg']) or isset($_GET['selfUnReg'])) and isset($_GET['group_id'])) {
    $group_id = getDirectReference($_GET['group_id']);
} else {
    initialize_group_id();
}

if (!is_group_visible($group_id, $course_id) and !$is_editor) {
    //Session::Messages($langForbidden, 'alert-danger');
    Session::flash('message',$langForbidden); 
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/group/index.php?course=$course_code");
}

initialize_group_info($group_id);
$user_groups = user_group_info($uid, $course_id);
$user_visible_groups = user_visible_groups($uid, $course_id);

$toolName = $langGroups;
$pageName = $group_name;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);

$multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);

if ((!$is_editor) and ($status != USER_GUEST)) {
    if (!$is_member and !$self_reg) { // check if we are group member
        //Session::Messages($langForbidden, 'alert-danger');
        Session::flash('message',$langForbidden); 
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    }
    if (isset($_GET['selfReg']) and $_GET['selfReg'] == 1) {

        if (($multi_reg == 0) and (!$user_visible_groups)) {
            $user_can_register_to_group = true;
        } else if ($multi_reg == 1) {
            $user_can_register_to_group = true;
        } else if (($multi_reg == 2) and (is_user_register_to_group_category_course($uid, $group_category, $course_id))) {
            $user_can_register_to_group = true;
        } else {
            $user_can_register_to_group = false;
        }
        if ($user_can_register_to_group and (!$max_members or $member_count < $max_members)) {
            $id = Database::get()->query("INSERT INTO group_members SET user_id = ?d, group_id = ?d, description = ''", $uid, $group_id);
            $group = gid_to_name($group_id);
            Log::record($course_id, MODULE_ID_GROUPS, LOG_MODIFY, array( 'uid' => $uid, 'name' => $group));

            //Session::Messages($langGroupNowMember, 'alert-success');
            Session::flash('message',$langGroupNowMember); 
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/group/group_space.php?course=$course_code&group_id=$group_id");
        } else {
            //Session::Messages($langForbidden, 'alert-danger');
            Session::flash('message',$langForbidden); 
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/group/index.php?course=$course_code");
        }

    }
    if (isset($_GET['selfUnReg']) and $_GET['selfUnReg'] == 1) {
        if ($is_member and $allow_unreg and $status != USER_GUEST) { // if registration is possible

            Database::get()->query("DELETE FROM group_members WHERE user_id = ?d AND group_id = ?d", $uid, $group_id);
            $group = gid_to_name($group_id);
            Log::record($course_id, MODULE_ID_GROUPS, LOG_DELETE, array('uid' => $uid, 'name' => $group));

            //Session::Messages($langGroupNowNotMember, 'alert-success');
            Session::flash('message',$langGroupNowNotMember); 
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/group/index.php?course=$course_code");
        } else {
           // Session::Messages($langForbidden, 'alert-danger');
            Session::flash('message',$langForbidden); 
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/group/index.php?course=$course_code");
        }
    }
}

if (isset($_GET['group_as'])) {
    $pageName = $langGroupAssignments;
    $navigation[] = array('url' => "group_space.php?course=$course_code&amp;group_id=$group_id", 'name' => $group_name);
    $group_id = $_GET['group_id'];

    $result = Database::get()->queryArray("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time FROM assignment AS a LEFT JOIN assignment_to_specific AS b ON a.id=b.assignment_id
                                                        WHERE a.course_id = ?d AND a.group_submissions= ?d AND (b.group_id= ?d OR b.group_id is null) AND a.active = 1 ORDER BY a.id", $course_id, 1, $group_id);
    $tool_content .= action_bar(array(
                array('title' => "$langBack",
                      'level' => "primary-label",
                      'url' => "group_space.php?course=$course_code&amp;group_id=$group_id",
                      'icon' => 'fa-reply')));
    if (count($result)>0) {
            $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='table-responsive'>
                <table class='table-default'>
                <tr class='list-header'>
                  <th class='text-white' style='width:45%;'>$langTitle</th>
                  <th class='text-white text-center'>$m[subm]</th>
                  <th class='text-white text-center'>$m[nogr]</th>
                  <th class='text-white text-center'>$langGroupWorkDeadline_of_Submission</th>
                </tr>";
        foreach ($result as $row) {
            // Check if assignment contains submissions
            $num_submitted = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit WHERE assignment_id = ?d", $row->id)->count;
            $num_ungraded = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit WHERE assignment_id = ?d AND grade IS NULL", $row->id)->count;
            if (!$num_ungraded) {
                if ($num_submitted > 0) {
                    $num_ungraded = '0';
                } else {
                    $num_ungraded = '-';
                }
            }

            $tool_content .= "<tr class='".(!$row->active ? "not_visible":"")."'>";
            $deadline = (int)$row->deadline ? format_locale_date(strtotime($row->deadline)) : $langNoDeadline;
            $tool_content .= "<td>
                                <a href='../work/index.php?course=$course_code&amp;id={$row->id}'>" . q($row->title) . "</a>
                                <br><small class='text-muted'>".($row->group_submissions? $m['group_work'] : $m['user_work'])."</small>
                            </td>
                            <td class='text-center'>$num_submitted</td>
                            <td class='text-center'>$num_ungraded</td>
                            <td class='text-center'>$deadline";
            if ($row->time > 0) {
                $tool_content .= " <br><span class='label label-warning'><small>$langDaysLeft" . format_time_duration($row->time) . "</small></span>";
            } else if((int)$row->deadline){
                $tool_content .= " <br><span class='label label-danger'><small>$langHasExpiredS</small></span>";
            }
           $tool_content .= "</td></tr>";
        }
        $tool_content .= '</table></div></div></div>';
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'>$langNoAssign</div></div>";
    }
} else {
    $student_to_student_allow = get_config('dropbox_allow_student_to_student');
    $tool_content .= action_bar(array(
                array('title' => $langModify,
                      'url' => "group_edit.php?course=$course_code&group_id=$group_id&from=group",
                      'level' => 'primary-label',
                      'icon' => 'fa-edit',
                      'button-class' => 'btn-success',
                      'show' => $is_editor),
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
                      'url' => "../wiki/index.php?course=$course_code&amp;gid=$group_id",
                      'icon' => 'fa-wikipedia-w',
                      'level' => 'primary',
                      'show' => $wiki),
                array('title' => $langGroupAssignments,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;group_id=$group_id&amp;group_as=1",
                      'icon' => 'fa-globe',
                      'level' => 'primary',
                      'show' => visible_module(MODULE_ID_ASSIGN)),
                array('title' => $langBack,
                      'url' => "index.php?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary'),
                array('title' => $langEmailGroup,
                      'url' => "../message/index.php?course=$course_code&upload=1&type=cm&group_id=$group_id",
                      'icon' => 'fa-envelope',
                      'show' => $is_editor or $is_tutor or $student_to_student_allow),
                array('title' => $langAddManyUsers,
                      'url' => "muladduser.php?course=$course_code&amp;group_id=$group_id",
                      'icon' => 'fa-plus-circle',
                      'show' => $is_editor),
                array('title' => $langDumpUser,
                      'url' => "dumpgroup.php?course=$course_code&amp;group_id=$group_id&amp;u=1",
                      'icon' => 'fa-file-archive-o',
                      'show' => $is_editor),
                array('title' => "$langDumpUser ($langcsvenc2)",
                      'url' => "dumpgroup.php?course=$course_code&amp;group_id=$group_id&amp;u=1&amp;enc=UTF-8",
                      'icon' => 'fa-file-archive-o',
                      'show' => $is_editor)));

    $tutors = array();
    $members = array();
    $q = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname, user.has_icon,
                              group_members.is_tutor, group_members.description, user.am, user.email
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
    <div class='col-sm-12'>
        <div class='panel panel-action-btn-primary rounded-0'>
            <div class='panel-heading rounded-0'>
                <h3 class='panel-title'>$langGroupInfo</h3>
            </div>
            <div class='panel-body rounded-0'>
                <div class='row'>
                    <div class='col-sm-3'><strong>$langGroupTutor:</strong></div>
                    <div class='col-sm-9'>$tool_content_tutor</div>
                </div>
                <div class='row' style='padding-top: 20px; padding-bottom:10px'>
                    <div class='col-sm-3'><strong>$langDescription:</strong></div>
                    <div class='col-sm-9'>$tool_content_description</div>
                </div>
            </div>
        </div>
    </div>";

    // members
    if (count($members) > 0) {
        $tool_content .= "
                        <div class='col-sm-12 mt-3' style='overflow-y:auto;'>
                          <ul class='list-group'>
                              <li class='list-group-item list-header'>
                                  <div class='row'>";
        if ($is_editor or $is_tutor) {
            $tool_content .= "        <div class='col-4'>$langSurnameName</div>
                                      <div class='col-4'>$langAm</div>
                                      <div class='col-4'>$langEmail</div>";
        } else {
            $tool_content .= "        <div class='col-12'>$langSurnameName</div>";
        }
        $tool_content .= "
                                  </div>
                              </li>";

        foreach ($members as $member) {
            $user_group_description = q($member->description);
            $tool_content .= "<li class='list-group-item'>
                                  <div class='row'>";
            if ($is_editor or $is_tutor) {
                $email = q($member->email);
                $tool_content .= "    <div class='col-4'>" .
                                           display_user($member->id, false, true) .
                                           ($user_group_description?
                                            ("<br>" . $user_group_description): '') . "
                                      </div>
                                      <div class='col-4'>" .
                                           ($member->am? q($member->am): '-') . "
                                      </div>
                                      <div class='col-4'>" .
                                           ($email? "<a href='mailto:$email'>$email</a>": '-') . "
                                      </div>
                                 </div>
                              </li>";
            } else {
                $tool_content .= "    <div class='col-12'>" .
                                           display_user($member->id, false, true) .
                                           ($user_group_description?
                                            ("<br>" . $user_group_description): '') . "
                                      </div>";
            }
        }
        $tool_content .= "</ul></div>";
    } else {
        $tool_content .= "<div class='col-sm-12 mt-3'><div class='alert alert-warning'>$langGroupNoneMasc</div></div>";
    }
}
draw($tool_content, 2);
