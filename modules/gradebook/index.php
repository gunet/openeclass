<?php
/* ========================================================================
 * Open eClass 3.5
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
 * ======================================================================== */

$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'gradebook';

require_once '../../include/baseTheme.php';
require_once 'modules/progress/GradebookEvent.php';
require_once 'functions.php';
require_once 'include/log.class.php';
//Module name
$toolName = $langGradebook;
// needed for updating users lists
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['assign_type'])) {
        if ($_POST['assign_type'] == 2) {
            $data = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
        } else {
            $data = array();
            $gradebook_id = intval(getDirectReference($_REQUEST['gradebook_id']));
            // active users who don't participate in gradebook
            $d1 = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                            FROM user, course_user
                                                WHERE user.id = course_user.user_id
                                                AND course_user.course_id = ?d
                                                AND course_user.status = " . USER_STUDENT . "
                                                AND user.expires_at >= CURRENT_DATE()
                                            AND user.id NOT IN
                                            (SELECT uid FROM gradebook_users WHERE gradebook_id = ?d) ORDER BY surname", $course_id, $gradebook_id);
            $data[0] = $d1;
            // active users who already participate in gradebook
            $d2 = Database::get()->queryArray("SELECT uid AS id, givenname, surname FROM user, gradebook_users
                                        WHERE gradebook_users.uid = user.id
                                        AND gradebook_id = ?d
                                        AND user.expires_at >= CURRENT_DATE()
                                        ORDER BY surname", $gradebook_id);
            $data[1] = $d2;
        }
    }
    echo json_encode($data);
    exit;
}

//Datepicker
load_js('tools.js');
load_js('jquery');
load_js('datatables');
if ($is_editor) {
    // disable ordering for action button column
    $columns = 'null, null, null, null, null, { orderable: false }';
} else if ($is_course_reviewer) {
    $columns = 'null, null, null, null, null';
}

@$head_content .= "
<script type='text/javascript'>
$(function() {
    var oTable = $('#users_table{$course_id}').DataTable ({
                'columns': [ $columns ],
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],
               'fnDrawCallback': function( oSettings ) {
                    $('#users_table{$course_id}_filter label input').attr({
                      class : 'form-control input-sm',
                      placeholder : '$langSearch...'
                    });
                },
               'sPaginationType': 'full_numbers',
                'bSort': true,
                'oLanguage': {
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
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
    $('#user_grades_form').on('submit', function (e) {
        oTable.rows().nodes().page.len(-1).draw();
    });
    $('input[id=button_groups]').click(changeAssignLabel);
    $('input[id=button_some_users]').click(changeAssignLabel);
    $('input[id=button_some_users]').click(ajaxParticipants);
    $('input[id=button_all_users]').click(hideParticipants);
    function hideParticipants()
    {
        $('#participants_tbl').addClass('hide');
        $('#users_box').find('option').remove();
        $('#all_users').show();
    }
    function changeAssignLabel()
    {
        var assign_to_specific = $('input:radio[name=specific_gradebook_users]:checked').val();
        if(assign_to_specific>0){
           ajaxParticipants();
        }
        if (this.id=='button_groups') {
           $('#users').text('$langGroups');
        }
        if (this.id=='button_some_users') {
           $('#users').text('$langUsers');
        }
    }
    function ajaxParticipants()
    {
        $('#all_users').hide();
        $('#participants_tbl').removeClass('hide');
        var type = $('input:radio[name=specific_gradebook_users]:checked').val();
        $.post('$_SERVER[SCRIPT_NAME]?course=$course_code&gradebook_id=" . urlencode($_REQUEST['gradebook_id']) . "&editUsers=1',
        {
          assign_type: type
        },
        function(data,status){
            var index;
            var parsed_data = JSON.parse(data);
            var select_content = '';
            var select_content_2 = '';
            if (type==2) {
                for (index = 0; index < parsed_data.length; ++index) {
                    select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['name'] + '<\/option>';
                }
            }
            if (type==1) {
                for (index = 0; index < parsed_data[0].length; ++index) {
                    select_content += '<option value=\"' + parsed_data[0][index]['id'] + '\">' + parsed_data[0][index]['surname'] + ' ' + parsed_data[0][index]['givenname'] + '<\/option>';
                }
                for (index = 0; index < parsed_data[1].length; ++index) {
                    select_content_2 += '<option value=\"' + parsed_data[1][index]['id'] + '\">' + parsed_data[1][index]['surname'] + ' ' + parsed_data[1][index]['givenname'] + '<\/option>';
                }
            }
            $('#users_box').find('option').remove().end().append(select_content);
            $('#participants_box').find('option').remove().end().append(select_content_2);

        });
    }
});
</script>";


$display = TRUE;
if (isset($_REQUEST['gradebook_id'])) {
    $gradebook_id = getDirectReference($_REQUEST['gradebook_id']);
    $gradebook = Database::get()->querySingle("SELECT * FROM gradebook WHERE id = ?d", $gradebook_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
    $pageName = $langEditChange;
}

if ($is_editor) {
    // change grade book visibility
    if (isset($_GET['vis'])) {
        $grbid = getDirectReference($_GET['gradebook_id']);
        Database::get()->query("UPDATE gradebook SET active = ?d WHERE id = ?d AND course_id = ?d", $_GET['vis'], $grbid, $course_id);
        $log_details = array('id'=>$grbid, 'title'=> get_gradebook_title($grbid), 'action' => 'change gradebook visibility','id' => $_GET['gradebook_id'],  'title' => get_gradebook_title($_GET['gradebook_id']), 'visibility' => $_GET['vis']);
        Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
        Session::Messages($langGlossaryUpdated, 'alert-success');
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code");
    }
    if (isset($_GET['dup'])) {
        clone_gradebook($gradebook_id);
        Session::Messages($langCopySuccess, 'alert-success');
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code");
    }
    //add a new gradebook
    if (isset($_POST['newGradebook'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'degreerange', 'start_date', 'end_date'));
        $v->rule('numeric', array('degreerange'));
        $v->rule('date', array('start_date', 'end_date'));
        if (!empty($_POST['end_date'])) {
            $v->rule('dateBefore', 'start_date', $_POST['end_date']);
        }
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'start_date' => "$langTheField $langStart",
            'end_date' => "$langTheField $langEnd",
            'degreerange' => "$langTheField $langGradebookRange"
        ));
        if($v->validate()) {
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            $newTitle = $_POST['title'];
            $gradebook_range = $_POST['degreerange'];
            $start_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_date'])->format('Y-m-d H:i:s');
            $end_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['end_date'])->format('Y-m-d H:i:s');
            $gradebook_id = Database::get()->query("INSERT INTO gradebook SET course_id = ?d, `range` = ?d, active = 1, title = ?s, start_date = ?t, end_date = ?t", $course_id, $gradebook_range, $newTitle, $start_date, $end_date)->lastInsertID;
            $log_details = array('id' => $gradebook_id, 'gradebook_range' => $gradebook_range, 'title' => $newTitle, 'start_date' => $start_date, 'end_date' => $end_date);
            Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_INSERT, $log_details);
            Session::Messages($langCreateGradebookSuccess, 'alert-success');
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&new=1");
        }
    }
    //delete user from gradebook list
    if (isset($_GET['deleteuser']) and isset($_GET['ruid'])) {
        $userdr = getDirectReference($_GET['ruid']);
        $gbdr = getDirectReference($_GET['gb']);
        delete_gradebook_user($gbdr, $userdr);
        $log_details = array('id'=>$gbdr,'title'=>  get_gradebook_title($gbdr), 'action' => 'delete user', 'user_id' => $userdr, 'user_name' => uid_to_name($userdr));
        Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=".urlencode($_GET['gb'])."&gradebookBook=1");
    }

    //reset grade book users
    $distinct_users_count = 0;
    if (isset($_POST['resetGradebookUsers'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        if ($_POST['specific_gradebook_users'] == 2) { // specific users group
            foreach ($_POST['specific'] as $g) {
                $ug = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $g);
                $already_inserted_users = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
                $already_inserted_ids = [];
                foreach ($already_inserted_users as $already_inserted_user) {
                    array_push($already_inserted_ids, $already_inserted_user->uid);
                }
                foreach ($ug as $u) {
                    if (!in_array($u->user_id, $already_inserted_ids)) {
                        Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid)
                                SELECT $gradebook_id, user_id FROM course_user
                                WHERE course_id = ?d AND user_id = ?d", $course_id, $u->user_id);
                        update_user_gradebook_activities($gradebook_id, $u->user_id);
                        $distinct_users_count++;
                    }
                }
            }
            $log_details = array('id'=>$gradebook_id,'title'=>get_gradebook_title($gradebook_id), 'action' => 'reset users','user_count'=>$distinct_users_count, 'group_count'=>count($_POST['specific']), 'groups'=>$_POST['specific']);
            Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
        } elseif ($_POST['specific_gradebook_users'] == 1) { // specific users
            $active_gradebook_users = '';
            if (isset($_POST['specific']) and count($_POST['specific'])) {
                $active_gradebook_users = $_POST['specific'];
                $sql_placeholders = '(' . implode(', ', array_fill(0, count($active_gradebook_users), '?d')) . ')';
                $users = Database::get()->queryArray("SELECT uid FROM gradebook_users
                    WHERE gradebook_id = ?d AND uid NOT IN $sql_placeholders", $gradebook_id, $active_gradebook_users);
            } else {
                $users = Database::get()->queryArray('SELECT uid FROM gradebook_users
                    WHERE gradebook_id = ?d', $gradebook_id);
            }
            foreach ($users as $u) {
                delete_gradebook_user($gradebook_id, $u->uid);
            }
            $log_details = array('id' => $gradebook_id, 'title' => get_gradebook_title($gradebook_id), 'action' => 'delete users', 'user_count' => count($users), 'users' => $users);
            Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);

            $already_inserted_ids = [];
            if (isset($active_gradebook_users)) {
                $already_inserted_users = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d
                    AND uid IN $sql_placeholders", $gradebook_id, $active_gradebook_users);
                $already_inserted_ids = [];
                foreach ($already_inserted_users as $already_inserted_user) {
                    $already_inserted_ids[] = $already_inserted_user->uid;
                }
                $added_users = array();
                foreach ($active_gradebook_users as $u) {
                    if (!in_array($u, $already_inserted_ids)) {
                        $newUsersQuery = Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid)
                                SELECT ?d, user_id FROM course_user
                                WHERE course_id = ?d AND user_id = ?d", $gradebook_id, $course_id, $u);
                        update_user_gradebook_activities($gradebook_id, $u);
                        $added_users[] = $u;
                    }
                }
            }
            $log_details = array('id' => $gradebook_id, 'title'=> get_gradebook_title($gradebook_id), 'action' => 'add users', 'user_count'=> count($added_users), 'users' => $added_users);
            Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
        } else { // if we want all users between dates
            $usersstart = new DateTime($_POST['UsersStart']);
            $usersend = new DateTime($_POST['UsersEnd']);

            // Delete all students not in the Date Range
            $gu = Database::get()->queryArray("SELECT gradebook_users.uid FROM gradebook_users, course_user "
                    . "WHERE gradebook_users.uid = course_user.user_id "
                    . "AND gradebook_users.gradebook_id = ?d "
                    . "AND course_user.status = " . USER_STUDENT . " "
                    . "AND DATE(course_user.reg_date) NOT BETWEEN ?s AND ?s", $gradebook_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));
            $distinct_users = array();
            foreach ($gu as $u) {
                delete_gradebook_user($gradebook_id, $u);
                $distinct_users[] = $u;
                $distinct_users_count++;
            }
            if($distinct_users_count > 0){
                $log_details = array('id'=>$gradebook_id, 'title'=> get_gradebook_title($gradebook_id), 'action' => 'delete users out of date range','user_count'=> $distinct_users_count, 'users'=>$distinct_users, 'users_start' => $usersstart->format("Y-m-d"), 'users_end' => $usersend->format("Y-m-d"));
                Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
            }
            //Add students that are not already registered to the gradebook
            $already_inserted_users = Database::get()->queryArray("SELECT gradebook_users.uid FROM gradebook_users, course_user "
                    . "WHERE gradebook_users.uid = course_user.user_id "
                    . "AND gradebook_users.gradebook_id = ?d "
                    . "AND course_user.status = " . USER_STUDENT . " "
                    . "AND DATE(course_user.reg_date) BETWEEN ?s AND ?s", $gradebook_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));
            $already_inserted_ids = [];
            foreach ($already_inserted_users as $already_inserted_user) {
                array_push($already_inserted_ids, $already_inserted_user->uid);
            }
            $valid_users_for_insertion = Database::get()->queryArray("SELECT user_id
                        FROM course_user
                        WHERE course_id = ?d
                        AND status = " . USER_STUDENT . " "
                    . "AND DATE(reg_date) BETWEEN ?s AND ?s",$course_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));

            $distinct_users = array();
            $distinct_users_count = 0;
            foreach ($valid_users_for_insertion as $u) {
                if (!in_array($u->user_id, $already_inserted_ids)) {
                    Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid) VALUES (?d, ?d)", $gradebook_id, $u->user_id);
                    update_user_gradebook_activities($gradebook_id, $u->user_id);
                    $distinct_users[] = $u->user_id;
                    $distinct_users_count++;
                }
            }
            if($distinct_users_count > 0){
                $log_details = array('id'=>$gradebook_id,'title'=>  get_gradebook_title($gradebook_id), 'action' => 'add users in date range','user_count'=> $distinct_users_count, 'users'=>$distinct_users, 'users_start' => $usersstart->format("Y-m-d"), 'users_end' => $usersend->format("Y-m-d"));
                Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
            }
        }

        Session::Messages($langGradebookEdit,"alert-success");
        redirect_to_home_page('modules/gradebook/index.php?course=' . $course_code . '&gradebook_id=' . getIndirectReference($gradebook_id) . '&gradebookBook=1');
    }

    // Top menu
    $tool_content .= "<div class='row'><div class='col-sm-12'>";

    if (isset($_GET['editUsers']) or isset($_GET['gradeBooks'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook->title);
        $pageName = isset($_GET['editUsers']) ? $langRefreshList : $langGradebookManagement;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;gradebookBook=1",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['editSettings'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook->title);
        $pageName = $langConfig;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['gradebookBook'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook->title);
        $pageName = $langGradebookActiveUsers;
        $tool_content .= action_bar(array(
            array('title' => $langRefreshList,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;editUsers=1",
                  'icon' => 'fa-users',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success'),
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['modify'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id" . getIndirectReference($gradebook_id), "name" => $gradebook->title);
        $pageName = $langEditChange;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['imp'])) {
        $actID = intval($_GET['imp']);
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook->title);
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;ins=" . getIndirectReference($actID) . "", "name" => get_gradebook_activity_title($gradebook_id, $actID));
        $pageName =  get_gradebook_activity_title($gradebook_id, $actID) . " (" . $langImportGrades . ")";
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;ins=" . getIndirectReference($actID),
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
        ));

    } elseif (isset($_GET['ins'])) {
        $actID = intval(getDirectReference($_GET['ins']));
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook->title);
        $pageName =  get_gradebook_activity_title($gradebook_id, $actID) . " (" . $langGradebookBook .")";
        $tool_content .= action_bar(array(
            array('title' => $langImportGrades,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;imp=$actID",
                  'level' => 'primary-label',
                  'button-class' => 'btn btn-success',
                  'icon' => 'fa-upload'),
            array('title' => $langExportGrades,
                  'url' => "dumpgradebook.php?course=$course_code&amp;t=3&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;activity_id=$actID",
                  'level' => 'primary-label',
                  'button-class' => 'btn btn-success',
                  'icon' => 'fa-file-excel-o'),
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'),
        ));

    } elseif (isset($_GET['addActivity']) or isset($_GET['addActivityAs']) or isset($_GET['addActivityEx']) or isset($_GET['addActivityLp'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook->title);
        if (isset($_GET['addActivityAs'])) {
            $pageName = "$langAdd $langInsertWork";
        } elseif (isset($_GET['addActivityEx'])) {
            $pageName = "$langAdd $langInsertExercise";
        } elseif (isset($_GET['addActivityLp'])) {
            $pageName = "$langAdd $langLearningPath1";
        } else {
            $pageName = $langGradebookAddActivity;
        }
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['book'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook->title);
        $pageName = $langGradebookBook;
        $tool_content .= action_bar(array(
            array('title' => $langGradebookBook,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;gradebookBook=1",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success'),
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));

    } elseif (isset($_GET['new'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $pageName = $langNewGradebook;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } elseif (isset($_GET['gradebook_id']) && $is_editor) {
        $pageName = get_gradebook_title($gradebook_id);
    }  elseif ( !isset($_GET['gradebook_id'])) {
        $tool_content .= action_bar(
            array(
                array('title' => $langNewGradebook,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;new=1",
                      'icon' => 'fa-plus',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success')));
    }
    $tool_content .= "</div></div>";

    // update grade book settings
    if (isset($_POST['submitGradebookSettings'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'degreerange', 'start_date', 'end_date'));
        $v->rule('numeric', array('degreerange'));
        $v->rule('date', array('start_date', 'end_date'));
        if (!empty($_POST['end_date'])) {
            $v->rule('dateBefore', 'start_date', $_POST['end_date']);
        }
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'start_date' => "$langTheField $langStart",
            'end_date' => "$langTheField $langEnd",
            'degreerange' => "$langTheField $langGradebookRange"
        ));
        if($v->validate()) {
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            $gradebook_range = $_POST['degreerange'];
            $gradebook_title = $_POST['title'];
            $start_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_date'])->format('Y-m-d H:i:s');
            $end_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['end_date'])->format('Y-m-d H:i:s');
            Database::get()->querySingle("UPDATE gradebook SET `title` = ?s, `range` = ?d, `start_date` = ?t, `end_date` = ?t WHERE id = ?d ", $gradebook_title, $gradebook_range, $start_date, $end_date, $gradebook_id);
            $log_details = array('id' => $gradebook_id,  'title' => get_gradebook_title($gradebook_id), 'gradebook_range' => $gradebook_range, 'title' => $gradebook_title, 'start_date' => $start_date, 'end_date' => $end_date);
            Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
            Session::Messages($langGradebookEdit,"alert-success");
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id) . "&editSettings=1");
        }
    }
    //FORM: create / edit new activity
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){
        add_gradebook_other_activity($gradebook_id);
        $display = FALSE;
    }

    //UPDATE/INSERT DB: new activity from exercises, assignments, learning paths
    elseif(isset($_GET['addCourseActivity'])) {
        $id = getDirectReference($_GET['addCourseActivity']);
        $type = intval($_GET['type']);
        $ga = add_gradebook_activity($gradebook_id, $id, $type);
        $log_details = array('id'=>$gradebook_id,'title'=>  get_gradebook_title($gradebook_id), 'action' => 'add activity', 'activity_type' => $type, 'activity_id' => $id, 'activity_title' => $ga['act_title'],'activity_date' => $ga['act_date']);
        Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
        Session::Messages("$langGradebookSucInsert","alert-success");
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
        $display = FALSE;
    }

    //UPDATE/INSERT DB: add or edit activity to grade book module (edit concerns and course activities like lps)
    elseif (isset($_POST['submitGradebookActivity'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('numeric', array('weight'));
        $v->rule('min', array('weight'), 0);
        $v->rule('max', array('weight'), weightleft($gradebook_id, getDirectReference($_POST['id'])));
        $v->rule('date', array('date'));
        $v->labels(array(
            'weight' => "$langTheField $langGradebookActivityWeight",
            'date' => "$langTheField $langGradebookActivityDate2"
        ));
        if ($v->validate()) {
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            $actTitle = isset($_POST['actTitle']) ? trim($_POST['actTitle']) : '';
            $actDesc = purify($_POST['actDesc']);
            $auto = isset($_POST['auto']) ? 1 : 0;
            $weight = $_POST['weight'];
            $type = isset($_POST['activity_type'])? $_POST['activity_type']: null;
            $actDate = empty($_POST['date']) ? NULL :
                DateTime::createFromFormat('d-m-Y H:i', $_POST['date'])->format('Y-m-d H:i');
            $visible = isset($_POST['visible']) ? 1 : 0;
            if ($_POST['id']) {
                //update
                $id = getDirectReference($_POST['id']);
                Database::get()->query("UPDATE gradebook_activities SET `title` = ?s, date = ?t, description = ?s,
                                            `auto` = ?d, `weight` = ?f, `activity_type` = ?d, `visible` = ?d
                                            WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $weight, $type, $visible, $id);
                $log_details = array('id'=>$gradebook_id,'title'=>  get_gradebook_title($gradebook_id), 'action' => 'modify activity', 'activity_type' => $type, 'activity_id' => $id, 'activity_title' => $actTitle, 'activity_date' => $actDate, 'auto' => $auto, 'weight' => $weight, 'visible' => $visible);
                Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
                Session::Messages("$langGradebookEdit", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s,
                                                            `date` = ?t, description = ?s, weight = ?f, `activity_type` = ?d, visible = ?d",
                                                    $gradebook_id, $actTitle, $actDate, $actDesc, $weight, $type, $visible)->lastInsertID;
                $log_details = array('action' => 'add activity','id' => $gradebook_id,  'title' => get_gradebook_title($gradebook_id), 'activity_type' => $type, 'activity_id' => $insertAct, 'activity_title' => $actTitle, 'activity_date' => $actDate, 'weight' => $weight, 'visible' => $visible);
                Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
                Session::Messages("$langGradebookSucInsert","alert-success");
                redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
            }
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            $new_or_edit = $_POST['id'] ?  "&modify=".$_POST['id'] : "&addActivity=1";
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=".getIndirectReference($gradebook_id).$new_or_edit);
        }
    }

    //delete grade book activity
    elseif (isset($_GET['delete'])) {
        $log_details = array('action' => 'delete activity',
                             'id' => $gradebook_id,
                             'title' => get_gradebook_title($gradebook_id),
                             'activity_id' => getDirectReference($_GET['delete']),
                             'activity_title' => get_gradebook_activity_title($gradebook_id, getDirectReference($_GET['delete'])));
        delete_gradebook_activity($gradebook_id, getDirectReference($_GET['delete']));
        Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_MODIFY, $log_details);
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));

    // delete grade book
    } elseif (isset($_GET['delete_gb'])) {
        triggerGameGradebook($course_id, 3, getDirectReference($_GET['delete_gb']));
        $log_details = array('id' => getDirectReference($_GET['delete_gb']),
                             'title' => get_gradebook_title(getDirectReference($_GET['delete_gb'])));
        delete_gradebook(getDirectReference($_GET['delete_gb']));
        Log::record($course_id, MODULE_ID_GRADEBOOK, LOG_DELETE, $log_details);
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code");
    }

    //DISPLAY: list of users and form for each user
    elseif(isset($_GET['gradebookBook']) or isset($_GET['book'])) {
        if (isset($_GET['update']) and $_GET['update']) {
            $tool_content .= "<div class='alert alert-success'>$langAttendanceUsers</div>";
        }
        //record booking
        if (isset($_POST['bookUser'])) {
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            $userID = intval(getDirectReference($_POST['userID'])); //user
            $gradebook_range = $_POST['degreerange'];
            // get all the gradebook activies --> for each gradebook activity update or insert grade
            $result = Database::get()->queryArray("SELECT * FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id);
            if ($result) {
                foreach ($result as $activity) {
                    $attend = floatval($_POST[getIndirectReference($activity->id)]); //get the record from the teacher (input name is the activity id)
                    //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT id FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if($checkForBook){
                        //update
                        Database::get()->query("UPDATE gradebook_book SET grade = ?f WHERE id = ?d ", $attend/$gradebook_range, $checkForBook->id);
                    } else {
                        //insert
                        Database::get()->query("INSERT INTO gradebook_book SET uid = ?d, gradebook_activity_id = ?d, grade = ?f, comments = ?s", $userID, $activity->id, $attend/$gradebook_range, '');
                    }
                }
                triggerGameGradebook($course_id, $userID, $gradebook_id);
                $message = "<div class='alert alert-success'>$langGradebookEdit</div>";
            }
        }
        // display user grades
        if(isset($_GET['book'])) {
            display_user_grades($gradebook_id);
        } else {  // display all users grades
            display_all_users_grades($gradebook_id);
        }
        $display = FALSE;
    }
    elseif (isset($_GET['new'])) {
        new_gradebook(); // create new grade book
        $display = FALSE;
    } elseif (isset($_GET['editUsers'])) { // edit grade book users
        user_gradebook_settings($gradebook_id);
        $display = FALSE;
    } elseif (isset($_GET['editSettings'])) { // grade book settings
        gradebook_settings($gradebook_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityAs'])) { //display available assignments
        display_available_assignments($gradebook_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityEx'])) { // display available exercises
        display_available_exercises($gradebook_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityLp'])) { // display available lps
        display_available_lps($gradebook_id);
        $display = FALSE;
    } elseif (isset($_GET['ins'])) { //DISPLAY - EDIT DB: insert grades for each activity
        $actID = intval(getDirectReference($_GET['ins']));
        $error = false;
        if (isset($_POST['bookUsersToAct'])) {
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            insert_grades($gradebook_id, $actID);
        }
        register_user_grades($gradebook_id, $actID);
        $display = FALSE;
    } elseif (isset($_GET['imp'])) {
        if (isset($_GET['import_grades'])) {
            import_grades($gradebook_id, $_GET['imp'], true);
        } else {
            import_grades($gradebook_id, $_GET['imp']);
            $display = FALSE;
        }
    }
} else if ($is_course_reviewer) {
    if (isset($_GET['gradebookBook'])) {
        display_all_users_grades($gradebook_id);
        $display = FALSE;
    }
}

if (isset($display) and $display == TRUE) {
    // display grade book
    if (isset($gradebook)) {
        if ($is_course_reviewer) {
            if (isset($_GET['u'])) {
                student_view_gradebook($gradebook_id, $_GET['u']); // teacher view
            } else {
                display_gradebook($gradebook);
            }
        } else {
            $pageName = $gradebook->title;
            student_view_gradebook($gradebook_id, $uid); // student view
        }
    } else { // display all grade-books
        display_gradebooks();
    }
}

draw($tool_content, 2, null, $head_content);
