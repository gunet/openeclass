<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
 */

$require_login = true;
$require_current_course = TRUE;

require_once '../../include/baseTheme.php';

//Identifying ajax request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);

    if (!empty($_GET['sSearch'])) {
        $search_values = array_fill(0, 3, '%' . $_GET['sSearch'] . '%');
        $search_sql = 'AND (user.surname LIKE ?s OR user.givenname LIKE ?s OR user.username LIKE ?s)';
    } else {
        $search_sql = '';
        $search_values = array();
    }

    $sortDir = ($_GET['sSortDir_0'] == 'desc')? 'DESC': '';
    $order_sql = 'ORDER BY ' .
        (($_GET['iSortCol_0'] == 0) ? "user.surname $sortDir, user.givenname $sortDir" : "course_user.status ASC");

    $limit_sql = ($limit > 0) ? "LIMIT $offset,$limit" : "";

    $all_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user
                                                WHERE `user`.`id` = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d", $course_id)->total;
    $filtered_users = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course_user, user
                                                WHERE `user`.`id` = `course_user`.`user_id`
                                                AND `course_user`.`course_id` = ?d $search_sql", $course_id, $search_values)->total;
    $result = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname,
                           user.has_icon, course_user.status,
                           course_user.tutor, course_user.editor, course_user.reviewer
                    FROM course_user, user
                    WHERE `user`.`id` = `course_user`.`user_id`
                    AND `course_user`.`course_id` = ?d
                    $search_sql $order_sql $limit_sql", $course_id, $search_values);

    $data['iTotalRecords'] = $all_users;
    $data['iTotalDisplayRecords'] = $filtered_users;
    $data['aaData'] = array();
    foreach ($result as $myrow) {
        $user_roles = array();
        ($myrow->status == '1') ? array_push($user_roles, $langTeacher) : array_push($user_roles, $langStudent);
        if ($myrow->tutor == '1') array_push($user_roles, $langTutor);
        if ($myrow->editor == '1') array_push($user_roles, $langEditor);
        if ($myrow->reviewer == '1') array_push($user_roles, $langOpenCoursesReviewer);
        //setting datables column data
        $data['aaData'][] = array(
            'DT_RowId' => $myrow->id,
            'DT_RowClass' => 'smaller',
            '0' => sanitize_utf8(display_user($myrow->id)),
            '1' => "<small>".implode(', ', $user_roles)."</small>"
        );
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

$limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;

$toolName = $langUsers;
load_js('tools.js');
load_js('datatables');
$head_content .= "
<script type='text/javascript'>
        $(document).ready(function() {
           var oTable = $('#users_table{$course_id}').DataTable ({
                'bStateSave': true,
                'bProcessing': true,
                'bServerSide': true,
                'sScrollX': true,
                'fnDrawCallback': function( oSettings ) {
                    tooltip_init();
                    popover_init();
                },
                'sAjaxSource': '$_SERVER[REQUEST_URI]',
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],
                'sPaginationType': 'full_numbers',
                'bSort': true,
                'aaSorting': [[0, 'desc']],
                'aoColumnDefs': [{'bSortable': true, 'aTargets':[-1]}, {'bSortable': false, 'aTargets': [ 1 ] }],
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
            $('.dataTables_filter input').attr({style: 'width:200px', class:'form-control input-sm', placeholder: '$langName, Username'});
            $('.success').delay(3000).fadeOut(1500);
        });
        </script>";

$limit_sql = '';

$tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "../../courses/$course_code/",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));

$tool_content .= "
    <table id='users_table{$course_id}' cellspacing = '0' class='table-default'>
        <thead>
            <tr>
              <th>$langSurnameName</th>
              <th class='text-center'>$langRole</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>";
draw($tool_content, 2, null, $head_content);
