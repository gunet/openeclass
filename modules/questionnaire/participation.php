<?php

/* ========================================================================
 * Open eClass 3.13
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2022  Greek Universities Network - GUnet
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
 * ========================================================================

  ============================================================================
  @Description: Display users who have not submitted a response to a poll
  ============================================================================
 */

$require_current_course = true;
$require_editor = true;
$helpTopic = 'questionnaire';

require_once '../../include/baseTheme.php';

$poll = Database::get()->querySingle('SELECT * FROM poll
    WHERE course_id = ?d AND pid = ?d',
    $course_id, $_GET['pid']);

if (!$poll or $poll->anonymized) {
    forbidden();
}

$allUsers = [];
if ($poll->assign_to_specific) {
    if ($poll->assign_to_specific == 1) {
        $assign_details = $m['WorkToUser'];
    } elseif ($poll->assign_to_specific == 2) {
        $assign_details = $m['WorkToGroup'] . ':<ul>';
    }
    $assign = Database::get()->queryArray('SELECT * FROM poll_to_specific
        WHERE poll_id = ?d', $poll->pid);
    foreach ($assign as $item) {
        if ($item->user_id) {
            $allUsers[] = $item->user_id;
        } elseif ($item->group_id) {
            $group_members = Database::get()->queryArray('SELECT user_id
                FROM group_members WHERE is_tutor = 0 AND group_id = ?d',
                $item->group_id);
            foreach ($group_members as $member) {
                $allUsers[] = $member->user_id;
            }
            $assign_details .= '<li>' .
                q(Database::get()->querySingle('SELECT name FROM `group` WHERE id = ?d',
                    $item->group_id)->name) .
                '</li>';
        }
    }
    if ($poll->assign_to_specific == 2) {
        $assign_details .= '</ul>';
    }
} else {
    $assign_details = $m['WorkToAllUsers'];
    $allUsers = Database::get()->queryArray('SELECT user_id FROM course_user
        WHERE course_id = ?d AND editor = 0 AND status = ' . USER_STUDENT,
        $course_id);
    $allUsers = array_map(function ($user) {
        return $user->user_id;
    }, $allUsers);
}

$polledUsers = Database::get()->queryArray('SELECT id, uid, email, email_verification
    FROM poll_user_record WHERE pid = ?d', $poll->pid);
$okUsers = [];
$emailUsers = [];
$timestamp = [];
foreach ($polledUsers as $user) {
    $ts = Database::get()->querySingle('SELECT submit_date
            FROM poll_answer_record WHERE poll_user_record_id = ?d LIMIT 1',
            $user->id)->submit_date;
    if ($user->uid) {
        $okUsers[] = $user->uid;
        $timestamp[$user->uid] = $ts;
    } elseif ($user->email_verification) {
        $emailUsers[] = $user->email;
        $timestamp[$user->email] = $ts;
    }
}

$allUsers = array_unique(array_merge($allUsers, $okUsers));

load_js('datatables');

$toolName = $langUserDuration;
$navigation[] = ['url' => "index.php?course=$course_code", 'name' => $langQuestionnaire];

$tool_content .= "
    <div class='card panelCard card-default px-lg-4 py-lg-3'>
        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
            <h3>$langSurvey</h3>
        </div>
        <div class='card-body'>
            <div class='row margin-bottom-thin p-2'>
                <div class='col-sm-3'>
                    <strong>$langTitle:</strong>
                </div>
                <div class='col-sm-9'>
                    " . q($poll->name) . "
                </div>
            </div>" . ($poll->start_date? ("
            <div class='row margin-bottom-thin p-2'>
                <div class='col-sm-3'>
                    <strong>$langStart:</strong>
                </div>
                <div class='col-sm-9'>
                    " . format_locale_date(strtotime($poll->start_date)) . "
                </div>
            </div>"): '') . ($poll->end_date? ("
            <div class='row margin-bottom-thin p-2'>
                <div class='col-sm-3'>
                    <strong>$langPollEnd:</strong>
                </div>
                <div class='col-sm-9'>
                    " . format_locale_date(strtotime($poll->end_date)) . "
                </div>
            </div>"): '') . "
            <div class='row margin-bottom-thin p-2'>
                <div class='col-sm-3'>
                    <strong>$m[WorkAssignTo]:</strong>
                </div>
                <div class='col-sm-9'>
                    $assign_details
                </div>
            </div>
            <div class='row margin-bottom-thin p-2'>
                <div class='col-sm-3'>
                    <strong>$langParticipants:</strong>
                </div>
                <div class='col-sm-9'>" .
                    count($polledUsers) . ' / ' . count($allUsers) . " $langUsersS
                </div>
            </div>
            <div class='row p-2'>
                <div class='col-sm-3'>
                    <strong>$langViewShow:</strong>
                </div>
                <div class='col-sm-9'>
                    <select id='user_filter' class='form-select'>
                        <option value='' selected>$langAllUsers</option>
                        <option value='yes'>$langOnlySubmissions</options>
                        <option value='no'>$langOnlyNonSubmissions</options>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class='table-responsive'>
        <table id='users' class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th>$m[username]</th>
                    <th>$langEmail</th>
                    <th>$langAm</th>
                    <th>$langDate / $langHour</th>
                    <th>$m[subm]</th>
                </tr>
            </thead>
            <tbody>";

foreach ($allUsers as $user_id) {
    $ok = in_array($user_id, $okUsers);
    $key = $ok? 'yes': 'no';
    $participation_icon = icon($ok? 'fa-square-check': 'fa-square');
    if (isset($timestamp[$user_id])) {
        $ts = format_locale_date(strtotime($timestamp[$user_id]), 'short');
        $data_ts = " data-sort='{$timestamp[$user_id]}'";
    } else {
        $ts = '';
        $data_ts = " data-sort='0'";
    }
    $tool_content .= "
                <tr>
                    <td>" . display_user($user_id) . "</td>
                    <td>" . q(uid_to_email($user_id)) . "</td>
                    <td>" . q(uid_to_am($user_id)) . "</td>
                    <td>$ts</td>
                    <td data-filter='$key' data-sort='$ok'>$participation_icon</td>
                </tr>";
}

foreach ($emailUsers as $mail) {
    $participation_icon = icon('fa-square-check');
    $ts = format_locale_date(strtotime($timestamp[$email]), 'short');
    $data_ts = " data-sort='{$timestamp[$email]}'";
    $tool_content .= "
                <tr>
                    <td>" . ($email) . "</td>
                    <td></td>
                    <td>$ts</td>
                    <td data-filter='ok' data-sort='1'>$participation_icon</td>
                </tr>";
}

$tool_content .= "
            </tbody>
        </table>
    </div>
    <script>
        $(function() {
            var table = $('#users').DataTable ({
                'aLengthMenu': [
                   [10, 20, 30 , -1],
                   [10, 20, 30, '$langAllOfThem']
                ],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'order' : [[1, 'asc']],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input').attr({
                'class' : 'form-control input-sm ms-0 mb-3',
                'placeholder' : '$langSearch...'
            });
            $('.dataTables_filter label').attr('aria-label', '$langSearch');  
            $('#user_filter').on('change', function () {
                table.column(4).search($(this).val()).draw();
            });
        });
        </script>";

draw($tool_content, 2, null, $head_content);
