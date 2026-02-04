<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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


$require_current_course = true;
$require_help = true;
$helpTopic = 'questionnaire';

require_once '../../include/baseTheme.php';

$toolName = $langQuestionnaire;
$pageName = $langIndividuals;

$pid = intval($_GET['pid']);
$thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);

// Results per user
$r_users = [];
if ($thePoll->assign_to_specific == 1) { // specific users
    $r_users = Database::get()->queryArray("SELECT user_id,group_id FROM poll_to_specific WHERE poll_id = ?d AND group_id IS NULL", $pid);
} elseif ($thePoll->assign_to_specific == 2) { // specific groups
    $r_users = Database::get()->queryArray("SELECT group_members.group_id,group_members.user_id FROM group_members 
                                            LEFT JOIN poll_to_specific ON poll_to_specific.group_id=group_members.group_id
                                            WHERE poll_to_specific.poll_id = ?d AND poll_to_specific.group_id > ?d", $pid, 0);
} else {
    $r_users = Database::get()->queryArray("SELECT user_id FROM course_user 
                                            WHERE course_id = ?d AND
                                            status = " . USER_STUDENT . " AND tutor = 0 AND
                                            editor = 0 AND reviewer = 0", $course_id);

}

$action_bar = action_bar(array(
                    array(
                        'title' => $langBack,
                        'url' => "{$urlAppend}modules/questionnaire/pollresults.php?course=$course_code&pid=$pid",
                        'icon' => 'fa-reply',
                        'level' => 'primary'
                    )
                ));
$tool_content .= $action_bar;

$tool_content .= "
        <div class='col-12'>
            <div class='table-responsive'>
                <table class='table-default'>
                    <thead>
                        <tr>
                            <th>$langSurnameName</th>
                            <th>$langGroup</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>";
                        foreach ($r_users as $u) {
                            $groupName = '-';
                            if (isset($u->group_id)) {
                                $groupName = Database::get()->querySingle("SELECT `name` FROM group WHERE id = ?d", $u->group_id)->name;
                            }
                            $tool_content .= "<tr>
                                                <td>" . display_user($u->user_id) . "</td>
                                                <td>$groupName</td>
                                                <td class='text-end'>
                                                    <a href='{$urlAppend}modules/questionnaire/pollresults.php?course=$course_code&amp;pid=$pid&amp;res_per_u={$u->user_id}' aria-label='$langDetails' aria-pressed='' role='button'>
                                                        <span class='fa fa-line-chart' data-bs-original-title='$langDetails' data-bs-toggle='tooltip' data-bs-placement='bottom'></span>
                                                    </a>
                                                </td>
                                              </tr>";
                        }
$tool_content .= "  </tbody>
                </table>
            </div>
        </div>
";

draw($tool_content, 2, null, $head_content);