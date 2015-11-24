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
$helpTopic = 'Attendance';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'functions.php';

$toolName = $langAttendance;

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
if (isset($_REQUEST['attendance_id'])) {
    $attendance_id = $_REQUEST['attendance_id'];
    $attendance = Database::get()->querySingle("SELECT * FROM attendance WHERE id = ?d", $attendance_id);
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
    if (isset($_GET['dup'])) {
        clone_attendance($attendance_id);
        Session::Messages($langCopySuccess, 'alert-success');
        redirect_to_home_page("modules/attendance/index.php?course=$course_code");
    }
    
    //add a new attendance
    if (isset($_POST['newAttendance'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'limit', 'start_date', 'end_date'));
        $v->rule('numeric', array('limit'));
        $v->rule('date', array('start_date', 'end_date'));
        if (!empty($_POST['end_date'])) {
            $v->rule('dateBefore', 'start_date', $_POST['end_date']);
        }        
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'start_date' => "$langTheField $langStart",
            'end_date' => "$langTheField $langEnd",            
            'limit' => "$langTheField $langAttendanceLimitNumber"
        ));
        if($v->validate()) {           
            $newTitle = $_POST['title'];
            $attendance_limit = intval($_POST['limit']);
            $start_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_date'])->format('Y-m-d H:i:s');
            $end_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['end_date'])->format('Y-m-d H:i:s');            
            $attendance_id = Database::get()->query("INSERT INTO attendance SET course_id = ?d, `limit` = ?d, active = 1, title = ?s, start_date = ?t, end_date = ?t", $course_id, $attendance_limit, $newTitle, $start_date, $end_date)->lastInsertID;   

            Session::Messages($langChangeAttendanceCreateSuccess, 'alert-success');
            redirect_to_home_page("modules/attendance/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&new=1");
        }
    }    
    //delete user from attendance list
    if (isset($_GET['deleteuser']) and isset($_GET['ruid'])) {
        delete_attendance_user($_GET['at'], $_GET['ruid']);        
        redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=".urlencode($_GET[at])."&attendanceBook=1");        
    }
    
    //reset attendance users
    if (isset($_POST['resetAttendanceUsers'])) {               
        if ($_POST['specific_attendance_users'] == 2) { // specific users group
            foreach ($_POST['specific'] as $g) {
                $ug = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $g);
                $already_inserted_users = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
                $already_inserted_ids = [];
                foreach ($already_inserted_users as $already_inserted_user) {
                    array_push($already_inserted_ids, $already_inserted_user->uid);
                }                
                foreach ($ug as $u) {
                    if (!in_array($u->user_id, $already_inserted_ids)) {
                        $newUsersQuery = Database::get()->query("INSERT INTO attendance_users (attendance_id, uid) 
                                SELECT $attendance_id, user_id FROM course_user
                                WHERE course_id = ?d AND user_id = ?d", $course_id, $u);
                        update_user_attendance_activities($attendance_id, $u->user_id);
                    }
                }
            }
        } elseif ($_POST['specific_attendance_users'] == 1) { // specific users            
            $active_attendance_users = '';
            $extra_sql_not_in = "";
            $extra_sql_in = "";
            if (isset($_POST['specific'])) {
                foreach ($_POST['specific'] as $u) {
                    $active_attendance_users .= $u . ",";
                }
            }
            $active_attendance_users = substr($active_attendance_users, 0, -1);
            if ($active_attendance_users) {
                $extra_sql_not_in .= " NOT IN ($active_attendance_users)";
                $extra_sql_in .= " IN ($active_attendance_users)";
            }            
            $gu = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d
                                                AND uid$extra_sql_not_in", $attendance_id);            
            foreach ($gu as $u) {
                delete_attendance_user($attendance_id, $u);
            }
            $already_inserted_users = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d
                                                AND uid$extra_sql_in", $attendance_id);
            $already_inserted_ids = [];
            foreach ($already_inserted_users as $already_inserted_user) {
                array_push($already_inserted_ids, $already_inserted_user->uid);
            }
            if (isset($_POST['specific'])) {
                foreach ($_POST['specific'] as $u) {
                    if (!in_array($u, $already_inserted_ids)) {
                        $newUsersQuery = Database::get()->query("INSERT INTO attendance_users (attendance_id, uid) 
                                SELECT $attendance_id, user_id FROM course_user
                                WHERE course_id = ?d AND user_id = ?d", $course_id, $u); 
                        update_user_attendance_activities($attendance_id, $u);
                    }
                }
            }
        } else { // if we want all users between dates            
            $usersstart = new DateTime($_POST['UsersStart']);
            $usersend = new DateTime($_POST['UsersEnd']);
            // Delete all students not in the Date Range
            $gu = Database::get()->queryArray("SELECT attendance_users.uid FROM attendance_users, course_user "
                    . "WHERE attendance_users.uid = course_user.user_id "
                    . "AND attendance_users.attendance_id = ?d "
                    . "AND course_user.status = " . USER_STUDENT . " "
                    . "AND DATE(course_user.reg_date) NOT BETWEEN ?s AND ?s", $attendance_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));
                    
            foreach ($gu as $u) {
                delete_attendance_user($attendance_id, $u);
            }
            //Add students that are not already registered to the gradebook
            $already_inserted_users = Database::get()->queryArray("SELECT attendance_users.uid FROM attendance_users, course_user "
                    . "WHERE attendance_users.uid = course_user.user_id "
                    . "AND attendance_users.attendance_id = ?d "
                    . "AND course_user.status = " . USER_STUDENT . " "
                    . "AND DATE(course_user.reg_date) BETWEEN ?s AND ?s", $attendance_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));                         
            $already_inserted_ids = [];
            foreach ($already_inserted_users as $already_inserted_user) {
                array_push($already_inserted_ids, $already_inserted_user->uid);
            }
            $valid_users_for_insertion = Database::get()->queryArray("SELECT user_id 
                        FROM course_user
                        WHERE course_id = ?d 
                        AND status = " . USER_STUDENT . " "
                    . "AND DATE(reg_date) BETWEEN ?s AND ?s",$course_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));

            foreach ($valid_users_for_insertion as $u) {
                if (!in_array($u->user_id, $already_inserted_ids)) {
                    Database::get()->query("INSERT INTO attendance_users (attendance_id, uid) VALUES (?d, ?d)", $attendance_id, $u->user_id);
                    update_user_attendance_activities($attendance_id, $u->user_id);
                }
            }            
        }
        Session::Messages($langGradebookEdit,"alert-success");                    
        redirect_to_home_page('modules/attendance/index.php?course=' . $course_code . '&attendance_id=' . $attendance_id . '&attendanceBook=1');
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
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id", "name" => $attendance->title);
        $pageName = $langConfig;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id",
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
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id", "name" => $attendance->title);
        $pageName = $langEditChange;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id",
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
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id", "name" => $attendance->title);
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
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id",
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
        $pageName = $langNewAttendance;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } elseif (isset($_GET['attendance_id']) && $is_editor) {        
        $pageName = get_attendance_title($attendance_id);
    }  elseif (!isset($_GET['attendance_id'])) {
        $tool_content .= action_bar(
            array(
                array('title' => $langNewAttendance,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;new=1",
                      'icon' => 'fa-plus',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success')));
    }
    $tool_content .= "</div></div>";
    
    // update attendance settings
    if (isset($_POST['submitAttendanceBookSettings'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'limit', 'start_date', 'end_date'));
        $v->rule('numeric', array('limit'));
        $v->rule('date', array('start_date', 'end_date'));
        if (!empty($_POST['end_date'])) {
            $v->rule('dateBefore', 'start_date', $_POST['end_date']);
        }        
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'start_date' => "$langTheField $langStart",
            'end_date' => "$langTheField $langEnd",            
            'limit' => "$langTheField $langAttendanceLimitNumber"
        ));
        if($v->validate()) {           
            $attendance_limit = $_POST['limit'];
            $attendance_title = $_POST['title'];
            $start_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['start_date'])->format('Y-m-d H:i:s');
            $end_date = DateTime::createFromFormat('d-m-Y H:i', $_POST['end_date'])->format('Y-m-d H:i:s');             
            Database::get()->querySingle("UPDATE attendance SET `title` = ?s, `limit` = ?d, `start_date` = ?t, `end_date` = ?t WHERE id = ?d ", $attendance_title, $attendance_limit, $start_date, $end_date, $attendance_id);
            Session::Messages($langGradebookEdit,"alert-success");
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id&editSettings=1");           
        }
    }
    //FORM: create / edit new activity
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){
        add_attendance_other_activity($attendance_id);
        $display = FALSE;
    }
    //UPDATE/INSERT DB: new activity from exersices, assignments, learning paths
    elseif(isset($_GET['addCourseActivity'])) {
        $id = $_GET['addCourseActivity'];
        $type = intval($_GET['type']);
        add_attendance_activity($attendance_id, $id, $type);
        Session::Messages("$langGradebookSucInsert","alert-success");
        redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");        
        $display = FALSE;
    }
    
    //UPDATE/INSERT DB: add or edit activity to attendance module (edit concerns and course activities like lps)
    elseif(isset($_POST['submitAttendanceActivity'])) {   
        $v = new Valitron\Validator($_POST);      
        $v->rule('date', array('date'));
        $v->labels(array(
            'date' => "$langTheField $langGradebookActivityDate2"
        ));
        if($v->validate()) {
            $actTitle = isset($_POST['actTitle']) ? trim($_POST['actTitle']) : "";
            $actDesc = purify($_POST['actDesc']);
            $auto = isset($_POST['auto']) ? $_POST['auto'] : "";
            $actDate = !empty($_POST['date']) ? $_POST['date'] : null;
            $visible = isset($_POST['visible']) ? 1 : 0;
            if ($_POST['id']) {              
                //update
                $id = $_POST['id'];
                Database::get()->query("UPDATE attendance_activities SET `title` = ?s, date = ?t, 
                                                description = ?s, `auto` = ?d
                                            WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $id);
                Session::Messages("$langGradebookEdit", "alert-success");
                redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s, 
                                                            `date` = ?t, description = ?s", 
                                                    $attendance_id, $actTitle, $actDate, $actDesc);
                Session::Messages("$langGradebookSucInsert","alert-success");
                redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");
            }            
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            $new_or_edit = $_POST['id'] ?  "&modify=".getIndirectReference($_POST['id']) : "&addActivity=1";
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=".$attendance_id.$new_or_edit);            
        }        
    }
    
    elseif (isset($_GET['delete'])) {
        delete_attendance_activity($attendance_id, getDirectReference($_GET['delete']));
        redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");
    
    // delete attendance
    } elseif (isset($_GET['delete_at'])) {        
        delete_attendance($_GET['delete_at']);
        redirect_to_home_page("modules/attendance/index.php?course=$course_code");
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
        new_attendance(); // create new attendance
        $display = FALSE;
    } elseif (isset($_GET['editUsers'])) { // edit attendance users
        user_attendance_settings($attendance_id);
        $display = FALSE;
    } elseif (isset($_GET['editSettings'])) { // attendance settings
        attendance_settings($attendance_id);
        $display = FALSE;    
    } elseif (isset($_GET['addActivityAs'])) { //display available assignments       
        attendance_display_available_assignments($attendance_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityEx'])) { // display available exercises
        attendance_display_available_exercises($attendance_id);
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
    // display attendance
    if (isset($attendance_id)) {
        if ($is_editor) {
            display_attendance_activities($attendance_id);            
        } else {
            $pageName = $attendance->title;
            student_view_attendance($attendance_id); // student view
        }
    } else { // display all attendances
        display_attendances();
    }
}  

//Display content in template
draw($tool_content, 2, null, $head_content);
