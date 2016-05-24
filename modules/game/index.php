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

$require_login = true;
$require_current_course = true;
$require_help = true;
//$helpTopic = 'Attendance';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'functions.php';
require_once 'CommentEvent.php';
require_once 'BlogEvent.php';
require_once 'WikiEvent.php';
require_once 'ForumEvent.php';

//$toolName = $langAttendance;
$toolName = "Πιστοποιήσεις";

/*
// needed for updating users lists
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['assign_type'])) {
        if ($_POST['assign_type'] == 2) {
            $data = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
        } else {
            $data = array();
            // users who don't participate in attendance
            $d1 = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                            FROM user, course_user
                                                WHERE user.id = course_user.user_id
                                                AND course_user.course_id = ?d
                                                AND course_user.status = " . USER_STUDENT . "
                                            AND user.id NOT IN (SELECT uid FROM attendance_users WHERE attendance_id = $_REQUEST[attendance_id]) ORDER BY surname", $course_id);
            $data[0] = $d1;
            // users who already participate in attendance
            $d2 = Database::get()->queryArray("SELECT uid AS id, givenname, surname FROM user, attendance_users
                                        WHERE attendance_users.uid = user.id AND attendance_id = $_REQUEST[attendance_id] ORDER BY surname");
            $data[1] = $d2;
        }
    }
    echo json_encode($data);
    exit;
}
*/

//Datepicker
load_js('tools.js');
load_js('jquery');
load_js('bootstrap-datetimepicker');
load_js('datatables');
load_js('datatables_filtering_delay');

@$head_content .= "
<script type='text/javascript'>
$(function() {
    $('#startdatepicker, #enddatepicker').datetimepicker({
            format: 'dd-mm-yyyy',
            pickerPosition: 'bottom-left',
            language: '".$language."',
            autoclose: true
        });
    var oTable = $('#users_table{$course_id}').DataTable ({
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],
               'fnDrawCallback': function( oSettings ) {
                            $('#users_table{$course_id}_wrapper label input').attr({
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
    $('#user_attendances_form').on('submit', function (e) {
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
        var assign_to_specific = $('input:radio[name=specific_attendance_users]:checked').val();
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
        var type = $('input:radio[name=specific_attendance_users]:checked').val();
        $.post('$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=".q($_REQUEST['attendance_id'])."&editUsers=1',
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
if (isset($_REQUEST['certificate_id'])) {
    $certificate_id = $_REQUEST['certificate_id'];
    $certificate = Database::get()->querySingle("SELECT * FROM certificate WHERE id = ?d", $certificate_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAttendance);
    $pageName = $langEditChange;
}


if ($is_editor) {
    // change attendance visibility
    if (isset($_GET['vis'])) {
        Database::get()->query("UPDATE attendance SET active = ?d WHERE id = ?d AND course_id = ?d", $_GET['vis'], $_GET['attendance_id'], $course_id);
        Session::Messages($langGlossaryUpdated, 'alert-success');
        redirect_to_home_page("modules/attendance/index.php?course=$course_code");
    }

    //add a new certificate
    if (isset($_POST['newCertificate'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'start_date', 'end_date', 'autoassign', 'active'));
        $v->rule('date', array('start_date', 'end_date'));
        $v->rule('numeric', array('autoassign')); //check
        $v->rule('numeric', array('active')); //check
        if (!empty($_POST['end_date'])) {
            $v->rule('dateBefore', 'start_date', $_POST['end_date']);
        }
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'start_date' => "$langTheField $langStart",
            'end_date' => "$langTheField $langEnd"//,
            //'limit' => "$langTheField $langAttendanceLimitNumber"
        ));
        if($v->validate()) {
            $newTitle = $_POST['title'];
            $start_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_date'])->format('Y-m-d H:i:s');
            $end_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['end_date'])->format('Y-m-d H:i:s');

            $autoassign = $_POST['autoassign'];
            $active = $_POST['active'];

            $certificate_id = Database::get()->query("INSERT INTO certificate SET course = ?d, author = 1, active = ?d, autoassign = ?d, title = ?s, created = ?t, expires = ?t", $course_id, $active, $autoassign, $newTitle, $start_date, $end_date)->lastInsertID;

            Session::Messages("Δημιουργήθηκε το πιστοποιητικό", 'alert-success');
            redirect_to_home_page("modules/game/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/game/index.php?course=$course_code&new=1");
        }
    }


    // Top menu
    $tool_content .= "<div class='row'><div class='col-sm-12'>";

    if (isset($_GET['editUsers']) or isset($_GET['Book'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id", "name" => $attendance->title);
        $pageName = isset($_GET['editUsers']) ? $langRefreshList : $langAttendanceManagement;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;attendanceBook=1",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['editSettings'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id", "name" => $certificate->title);
        $pageName = $langConfig;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['attendanceBook'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id", "name" => $attendance->title);
        $pageName = $langAttendanceActiveUsers;
        $tool_content .= action_bar(array(
            array('title' => $langRefreshList,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;editUsers=1",
                  'icon' => 'fa-users',
                  'level' => 'primary-label'),
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['modify'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id", "name" => $certificate->title);
        $pageName = $langEditChange;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['ins'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id", "name" => $attendance->title);
        $pageName = $langGradebookBook;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['addActivity']) or isset($_GET['addActivityAs']) or isset($_GET['addActivityEx']) or isset($_GET['addActivityLp'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id", "name" => $certificate->title);
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
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;certificate_id=$certificate_id",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['book'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id", "name" => $attendance->title);
        $pageName = $langGradebookBook;
        $tool_content .= action_bar(array(
            array('title' => $langGradebookBook,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;attendanceBook=1",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label'),
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success')
            ));

    } elseif (isset($_GET['new'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAttendance);
        $pageName = "Νέο πιστοποιητικό";//$langNewAttendance;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } elseif (isset($_GET['certificate_id']) && $is_editor) {
        $pageName = get_certificate_title($certificate_id);
    }  elseif (!isset($_GET['certificate_id'])) {
        $tool_content .= action_bar(
            array(
                array('title' => "Νέο πιστοποιητικό", //$langNewAttendance
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;new=1",
                      'icon' => 'fa-plus',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success')));
    }
    $tool_content .= "</div></div>";

    // update certificate settings
    if (isset($_POST['submitAttendanceBookSettings'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'start_date', 'end_date'));
        //$v->rule('numeric', array('limit'));
        $v->rule('date', array('start_date', 'end_date'));
        if (!empty($_POST['end_date'])) {
            $v->rule('dateBefore', 'start_date', $_POST['end_date']);
        }
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'start_date' => "$langTheField $langStart",
            'end_date' => "$langTheField $langEnd",
        ));
        if($v->validate()) {
            //$attendance_limit = $_POST['limit'];
            $certificate_title = $_POST['title'];
            if(isset($_POST['autoassign'])) echo $autoassign=1; else $autoassign=0;
            if(isset($_POST['active'])) $active=1; else $active=0;
            $start_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_date'])->format('Y-m-d H:i:s');
            $end_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['end_date'])->format('Y-m-d H:i:s');

            Database::get()->querySingle("UPDATE certificate SET `title` = ?s,`autoassign` = ?s,`active` = ?s, `created` = ?t, `expires` = ?t WHERE id = ?d ", $certificate_title, $autoassign, $active, $start_date, $end_date, $certificate_id);

            Session::Messages($langGradebookEdit,"alert-success");
            //redirect_to_home_page("modules/game/index.php?course=$course_code&certificate_id=$certificate_id");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/game/index.php?course=$course_code&certificate_id=$certificate_id&editSettings=1");
        }
    }
    //FORM: create / edit new activity
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){
        add_certificate_other_activity($certificate_id);
        $display = FALSE;
    }
    //UPDATE/INSERT DB: new activity from exersices, assignments, learning paths
    elseif(isset($_GET['addCourseActivity'])) {
        $id = $_GET['addCourseActivity'];
        $type = intval($_GET['type']);
        $lastID = getIndirectReference(add_certificate_activity($certificate_id, $id, $type));
        Session::Messages("Πραγματοποιήθηκε η εισαγωγή της δραστηριότητας","alert-success");
        if ($lastID == 1) {
          redirect_to_home_page("modules/game/index.php?course=$course_code&certificate_id=$certificate_id");
        } else {
          redirect_to_home_page("modules/game/index.php?course=$course_code&certificate_id=$certificate_id&modify=$lastID");
        }
        $display = FALSE;
    }

    //UPDATE/INSERT DB: add or edit activity to attendance module (edit concerns and course activities like lps)
    elseif(isset($_POST['submitCertificateActivity'])) {

        $threshold = isset($_POST['threshold']) ? $_POST['threshold'] : 0;
        $operator = isset($_POST['operator']) ? $_POST['operator'] : 0;

        if(isset($_POST['type'])){
          $type = $_POST['type'];
          if($type == MODULE_ID_BLOG){
            $activity = "blog";
          }
          if($type == MODULE_ID_COMMENTS){
            $activity = CommentEvent::BLOG_ACTIVITY;
          }
          if($type == "38a"){
            $type = MODULE_ID_COMMENTS;
            $activity = CommentEvent::COURSE_ACTIVITY;
          }
          if($type == MODULE_ID_FORUM){
            $activity = "forum";
          }
          if($type == 39){
            $activity = "social bookmark likes";
          }
          if($type == "39a"){
            $type == 39;
            $activity = "forum likes";
          }
          if($type == MODULE_ID_WIKI){
            $activity = "wiki";
          }
          Database::get()->query("INSERT INTO certificate_criterion
                                      SET certificate = ?d, activity_type = ?s, module = ?s, `operator` = ?s, threshold = ?d",
                                  $certificate_id, $activity, $type, $operator, $threshold);
        }elseif ($_POST['id']) {
            //update
            $id = $_POST['id'];
            Database::get()->query("UPDATE certificate_criterion SET `operator` = ?s, threshold = ?d
                                        WHERE id = ?d", $operator, $threshold, $id);
            Session::Messages("$langGradebookEdit", "alert-success");
            redirect_to_home_page("modules/game/index.php?course=$course_code&certificate_id=$certificate_id");
        }

    }

    elseif (isset($_GET['delete'])) {
        delete_certificate_activity($certificate_id, getDirectReference($_GET['delete']));
        redirect_to_home_page("modules/game/index.php?course=$course_code&certificate_id=$certificate_id");

    // delete certificate
    } elseif (isset($_GET['delete_at'])) {
        delete_certificate($_GET['delete_at']);
        redirect_to_home_page("modules/game/index.php?course=$course_code");
    }

    //DISPLAY: list of users and form for each user
    elseif(isset($_GET['attendanceBook']) or isset($_GET['book'])) {
        if (isset($_GET['update']) and $_GET['update']) {
            $tool_content .= "<div class='alert alert-success'>$langAttendanceUsers</div>";
        }
        //record booking
        if(isset($_POST['bookUser'])) {
            $userID = intval($_POST['userID']); //user
            //get all the attendance activies --> for each attendance activity update or insert grade
            $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d", $attendance_id);
            if ($result) {
                foreach ($result as $activity) {
                    $attend = @ intval($_POST[$activity->id]); //get the record from the teacher (input name is the activity id)
                    //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT id FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if($checkForBook){
                        //update
                        Database::get()->query("UPDATE attendance_book SET attend = ?d WHERE id = ?d ", $attend, $checkForBook->id);
                    } else {
                        //insert
                        Database::get()->query("INSERT INTO attendance_book SET uid = ?d, attendance_activity_id = ?d, attend = ?d, comments = ?s", $userID, $activity->id, $attend, '');
                    }
                }
                $message = "<div class='alert alert-success'>$langGradebookEdit</div>";
            }
        }
        // display user grades
        if(isset($_GET['book'])) {
            display_user_presences($attendance_id);
        } else {  // display all users
            display_all_users_presences($attendance_id);
        }
        $display = FALSE;
    }

 elseif (isset($_GET['new'])) {
        new_certificate(); // create new attendance

        $display = FALSE;
    } elseif (isset($_GET['editUsers'])) { // edit attendance users
        user_attendance_settings($attendance_id);
        $display = FALSE;
    } elseif (isset($_GET['editSettings'])) { // certificate settings
        certificate_settings($certificate_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityAs'])) { //display available assignments
        certificate_display_available_assignments($certificate_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityEx'])) { // display available exercises
        certificate_display_available_exercises($certificate_id);
        $display = FALSE;
    }elseif (isset($_GET['addActivityBlog'])) { // display available exercises
        //certificate_display_available_Blog($certificate_id);
        add_certificate_other_activity_only_value($certificate_id, MODULE_ID_BLOG);
        $display = FALSE;
    }elseif (isset($_GET['addActivityCom'])) { // display available exercises
        add_certificate_other_activity_only_value($certificate_id, MODULE_ID_COMMENTS);
        $display = FALSE;
    }elseif (isset($_GET['addActivityComCourse'])) { // display available exercises
        add_certificate_other_activity_only_value($certificate_id, "38a");
        $display = FALSE;
    }elseif (isset($_GET['addActivityFor'])) { // display available exercises
        add_certificate_other_activity_only_value($certificate_id, MODULE_ID_FORUM);
        $display = FALSE;
    }elseif (isset($_GET['addActivityLp'])) { // display available exercises
        certificate_display_available_Lp($certificate_id);
        $display = FALSE;
    }elseif (isset($_GET['addActivityRat'])) { // display available exercises
        add_certificate_other_activity_only_value($certificate_id, 39);
        $display = FALSE;
    }elseif (isset($_GET['addActivityRatPosts'])) { // display available exercises
        add_certificate_other_activity_only_value($certificate_id, "39a");
        $display = FALSE;
    }elseif (isset($_GET['addActivityDoc'])) { // display available exercises
        certificate_display_available_Doc($certificate_id);
        $display = FALSE;
    }elseif (isset($_GET['addActivityMul'])) { // display available exercises
        certificate_display_available_Mul($certificate_id);
        $display = FALSE;
    }elseif (isset($_GET['addActivityVid'])) { // display available exercises
        certificate_display_available_Vid($certificate_id);
        $display = FALSE;
    }elseif (isset($_GET['addActivityBook'])) { // display available exercises
        certificate_display_available_Book($certificate_id);
        $display = FALSE;
    }elseif (isset($_GET['addActivityQue'])) { // display available exercises
        certificate_display_available_Que($certificate_id);
        $display = FALSE;
    }elseif (isset($_GET['addActivityWi'])) { // display available exercises
        add_certificate_other_activity_only_value($certificate_id, MODULE_ID_WIKI);
        $display = FALSE;
    }






    //DISPLAY - EDIT DB: insert grades for each activity
    elseif (isset($_GET['ins'])) {
        $actID = intval(getDirectReference($_GET['ins']));
        $error = false;
        if (isset($_POST['bookUsersToAct'])) {
            insert_presence($attendance_id, $actID);
        }
//        if (isset($_POST['updateUsersToAct'])) {
//            update_presence($attendance_id, $actID);
//        }
        register_user_presences($attendance_id, $actID);
        $display = FALSE;
    }

}

if (isset($display) and $display == TRUE) {
    // display certificate
    if (isset($certificate_id)) {
        if ($is_editor) {
            display_certificate_activities($certificate_id);
        } else {
            $pageName = $certificate->title;
            student_view_certificate($certificate_id); // student view
        }
    } else { // display all attendances
        display_certificates();
    }
}

//Display content in template
draw($tool_content, 2, null, $head_content);
