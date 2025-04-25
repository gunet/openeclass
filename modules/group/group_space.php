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
    Session::flash('message',$langForbidden);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/group/index.php?course=$course_code");
}

initialize_group_info($group_id);
$user_groups = user_group_info($uid, $course_id);
$user_visible_groups = user_visible_groups($uid, $course_id);

$toolName = $langGroups;
$pageName = q($group_name);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);

$multi_reg = setting_get(SETTING_GROUP_MULTIPLE_REGISTRATION, $course_id);

if ((!$is_editor) and ($status != USER_GUEST)) {
    if (!$is_member and !$self_reg) { // check if we are group member
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

            Session::flash('message',$langGroupNowMember);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/group/group_space.php?course=$course_code&group_id=$group_id");
        } else {
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

            Session::flash('message',$langGroupNowNotMember);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/group/index.php?course=$course_code");
        } else {
            Session::flash('message',$langForbidden);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/group/index.php?course=$course_code");
        }
    }
}

if (isset($_GET['group_as'])) {
    $pageName = $langGroupAssignments;
    $navigation[] = array('url' => "group_space.php?course=$course_code&amp;group_id=$group_id", 'name' => q($group_name));
    $group_id = $_GET['group_id'];

    $result = Database::get()->queryArray("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time FROM assignment AS a LEFT JOIN assignment_to_specific AS b ON a.id=b.assignment_id
                                                        WHERE a.course_id = ?d AND a.group_submissions= ?d AND (b.group_id= ?d OR b.group_id is null) AND a.active = 1 ORDER BY a.id", $course_id, 1, $group_id);

    if (count($result)>0) {
            $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='table-responsive'>
                <table class='table-default'>
                <thead>
                <tr class='list-header'>
                  <th style='width:45%;'>$langTitle</th>
                  <th>$m[subm]</th>
                  <th>$m[nogr]</th>
                  <th>$langGroupWorkDeadline_of_Submission</th>
                </tr></thead>";
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
                                <br><small class='text-muted'>".($row->group_submissions? $langGroupAssignment : $langUserAssignment)."</small>
                            </td>
                            <td>$num_submitted</td>
                            <td>$num_ungraded</td>
                            <td>$deadline";
            if ($row->time > 0) {
                $tool_content .= " <br><span><small class='label label-warning'>$langDaysLeft" . format_time_duration($row->time) . "</small></span>";
            } else if((int)$row->deadline){
                $tool_content .= " <br><span><small class='label label-danger'>$langHasExpiredS</small></span>";
            }
           $tool_content .= "</td></tr>";
        }
        $tool_content .= '</table></div></div></div>';
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAssign</span></div></div>";
    }
} else {
    $student_to_student_allow = get_config('dropbox_allow_student_to_student');

    $tool_content .= "<div class='d-block d-lg-none'>";
        $tool_content .= action_bar(array(
                    array('title' => $langBack,
                        'url' => "index.php?course=$course_code",
                        'icon' => 'fa-reply',
                        'level' => 'primary'),
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
                        'icon' => 'fa-won-sign',
                        'level' => 'primary',
                        'show' => ($wiki && isset($is_collaborative_course) && !$is_collaborative_course)),
                    array('title' => $langGroupAssignments,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;group_id=$group_id&amp;group_as=1",
                        'icon' => 'fa-globe',
                        'level' => 'primary',
                        'show' => visible_module(MODULE_ID_ASSIGN)),
                    array('title' => $langEmailGroup,
                        'url' => "../message/index.php?course=$course_code&upload=1&type=cm&group_id=$group_id",
                        'icon' => 'fa-envelope',
                        'show' => $is_editor or $is_tutor or $student_to_student_allow),
                    array('title' => $langAddManyUsers,
                        'url' => "muladduser.php?course=$course_code&amp;group_id=$group_id",
                        'icon' => 'fa-plus-circle',
                        'show' => $is_editor),
                    array('title' => $langDumpUser,
                        'url' => "dumpgroup.php?course=$course_code&amp;group_id=$group_id",
                        'icon' => 'fa-file-zipper',
                        'show' => $is_editor),
                    array('title' => $langAddAvailableDateForGroupAdmin,
                        'url' => "date_available.php?course=$course_code&amp;group_id=$group_id",
                        'icon' => 'fa-solid fa-calendar-days',
                        'show' => ($is_editor or $is_tutor)),
                    array('title' => $langBookings,
                        'url' => "booking.php?course=$course_code&amp;group_id=$group_id",
                        'icon' => 'fa-solid fa-calendar-days',
                        'show' => ($is_member && !$is_editor && !$is_tutor))
                    ));

    $tool_content .= "</div>";

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

    $group_description = trim($group_description);
    if (empty($group_description)) {
        $tool_content_description = $langNoInfoAvailable;
    } else {
        $tool_content_description = q($group_description);
    }

    $tool_content .= "
        <div class='col-12'>
            <div class='row row-cols-1 row-cols-lg-2 g-4'>
                <div class='col-xl-7 col-lg-6 col-12'>
                    <div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>" . q($group_name) . "</h3>
                        </div>
                        <div class='card-body'>
                                <p class='form-label'>$langGroupTutor</p>";
                                if ($tutors) {
                                    $tool_content .= "<ul>";
                                    foreach ($tutors as $t){
                                        $tool_content .= "<li class='mt-2'>$t</li>";
                                    }
                                    $tool_content .= "</ul>";
                                } else {
                                    $tool_content .= "<p class='small-text'>$langNoInfoAvailable</p>";
                                }
               $tool_content .= "<p class='form-label mt-4'>$langDescription</p>
                                <p class='small-text' style='white-space: pre-wrap'>$tool_content_description</p>
                                <div class='card-footer d-flex justify-content-end align-items-center border-0 pb-3'>";
                            if ($max_members > 0) {
                                $tool_content .= " <span class='badge Primary-600-bg'>$langGroupMembersNum:&nbsp;$member_count/$max_members</span>";
                            } else {
                                $tool_content .= " <span class='badge Primary-600-bg'>$langGroupMembersNum:&nbsp;$member_count</span>";
                            }
                        $tool_content .= "</div></div>";

                    $tool_content .= "</div>
                </div>
                <div class='col-xl-5 col-lg-6 d-none d-lg-block'>
                    <div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>$langTools</h3>
                        </div>
                        <div class='card-body'>
                            <ul class='list-group list-group-flush list-group-groups'>";
                                if($is_editor){
                                    $tool_content .= "<li class='mb-3'>
                                                        <a class='d-flex justify-content-start align-items-start' href='group_edit.php?course=$course_code&group_id=$group_id&from=group'>
                                                            <span class='fa fa-edit pt-0 pe-1'></span>$langModify
                                                        </a>
                                                      </li>";
                                }

                                if($has_forum and $forum_id <> 0){
                                    $tool_content .= "<li class='mb-3'>
                                                        <a class='d-flex justify-content-start align-items-start' href='../forum/viewforum.php?course=$course_code&amp;forum=$forum_id'>
                                                            <span class='fa fa-comments pt-0 pe-1'></span>$langForums
                                                        </a>
                                                      </li>";
                                }

                                if($documents){
                                    $tool_content .= "<li class='mb-3'>
                                                        <a class='d-flex justify-content-start align-items-start' href='document.php?course=$course_code&amp;group_id=$group_id'>
                                                            <span class='fa fa-folder-open pt-0 pe-1'></span>$langGroupDocumentsLink
                                                        </a>
                                                      </li>";
                                }

                                if($wiki && isset($is_collaborative_course) && !$is_collaborative_course){
                                    $tool_content .= "<li class='mb-3'>
                                                        <a class='d-flex justify-content-start align-items-start' href='../wiki/index.php?course=$course_code&amp;gid=$group_id'>
                                                            <span class='fa-solid fa-won-sign pt-0 pe-1'></span>$langWiki
                                                        </a>
                                                      </li>";
                                }

                                if(visible_module(MODULE_ID_ASSIGN)){
                                    $tool_content .= "<li class='mb-3'>
                                                        <a class='d-flex justify-content-start align-items-start' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;group_id=$group_id&amp;group_as=1'>
                                                            <span class='fa fa-globe pt-0 pe-1'></span>$langGroupAssignments
                                                        </a>
                                                    </li>";
                                }


                                if($is_editor or $is_tutor or $student_to_student_allow){
                                    $tool_content .= "<li class='mb-3'>
                                                        <a class='d-flex justify-content-start align-items-start' href='../message/index.php?course=$course_code&upload=1&type=cm&group_id=$group_id'>
                                                            <span class='fa fa-envelope pt-0 pe-1'></span>$langEmailGroup
                                                        </a>
                                                    </li>";
                                }

                                if($is_editor){
                                    $tool_content .= "<li class='mb-3'>
                                                        <a class='d-flex justify-content-start align-items-start' href='muladduser.php?course=$course_code&amp;group_id=$group_id'>
                                                            <span class='fa fa-plus-circle pt-0 pe-1'></span>$langAddManyUsers
                                                        </a>
                                                    </li>";
                                }

                                if($is_editor){
                                    $tool_content .= "<li class='mb-3'>
                                                        <a class='d-flex justify-content-start align-items-start' href='dumpgroup.php?course=$course_code&amp;group_id=$group_id'>
                                                            <span class='fa-solid fa-file-zipper pt-0 pe-1'></span>$langDumpExcel
                                                        </a>
                                                    </li>";
                                }

                                if($booking && get_config('individual_group_bookings')){
                                    if($is_editor or $is_tutor){
                                        $tool_content .= "<li>
                                                            <a class='d-flex justify-content-start align-items-start' href='date_available.php?course=$course_code&amp;group_id=$group_id'>
                                                                <span class='fa-solid fa-calendar-days pt-0 pe-1'></span>$langAddAvailableDateForGroupAdmin
                                                            </a>
                                                        </li>";
                                    }else{
                                        $tool_content .= "<li>
                                                            <a class='d-flex justify-content-start align-items-start' href='date_available.php?course=$course_code&amp;group_id=$group_id&amp;show_tutor=1'>
                                                                <span class='fa-solid fa-calendar-days pt-0 pe-1'></span>$langBookings
                                                            </a>
                                                        </li>";
                                    }
                                }

          $tool_content .= "</ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>";

    if ($is_editor or $public_users_list) {
        // members
        if (count($members) > 0) {
            $tool_content .= "
                        <div class='col-12 mt-4'>
                            <div class='card panelCard card-default px-lg-4 py-lg-3 h-100'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                <h3>$langGroupMembersInfo</h3>
                            </div>
                            <div class='card-body'>
                          <ul class='list-group list-group-flush'>
                              <li class='list-group-item element'>
                                  <div class='row'>";
            if ($is_editor or $is_tutor) {
                $tool_content .= "<div class='col-4 TextBold' style='font-size:14px;'>$langSurnameName</div>
                                      <div class='col-4 TextBold' style='font-size:14px;'>$langAm</div>
                                      <div class='col-4 TextBold' style='font-size:14px;'>$langEmail</div>";
            } else {
                $tool_content .= "<div class='col-12 TextBold' style='font-size:14px;'>$langSurnameName</div>";
            }
            $tool_content .= "</div></li>";

            foreach ($members as $member) {
                $user_group_description = q($member->description);
                $tool_content .= "<li class='list-group-item element'>
                                  <div class='row'>";
                if ($is_editor or $is_tutor) {
                    $email = q($member->email);
                    $tool_content .= "<div class='col-4 small-text'>" .
                        display_user($member->id, false, true) .
                        ($user_group_description ?
                            ("<br>" . $user_group_description) : '') . "
                                      </div>
                                      <div class='col-4'>" .
                        ($member->am ? q($member->am) : '-') . "
                                      </div>
                                      <div class='col-4'>" .
                        ($email ? "<a href='mailto:$email'>$email</a>" : '-') . "
                                      </div>
                                 </div>
                              </li>";
                } else {
                    $tool_content .= "<div class='col-12 small-text'>" .
                        display_user($member->id, false, true) .
                        ($user_group_description ?
                            ("<br>" . $user_group_description) : '') . "
                                      </div>";
                }
            }
            $tool_content .= "</ul></div></div></div>";
        } else {
            $tool_content .= "<div class='col-sm-12 mt-4'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langGroupNoneMasc</span></div></div>";
        }
    }
}
draw($tool_content, 2);
