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

$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Gradebook';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'functions.php';

//Module name
$toolName = $langGradebook;

// needed for updating users lists
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['assign_type'])) {
        if ($_POST['assign_type'] == 2) {
            $data = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d", $course_id);
        } else {
            $data = array();
            // users who don't participate in gradebook
            $d1 = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                            FROM user, course_user
                                                WHERE user.id = course_user.user_id 
                                                AND course_user.course_id = ?d 
                                                AND course_user.status = " . USER_STUDENT . "
                                            AND user.id NOT IN (SELECT uid FROM gradebook_users WHERE gradebook_id = $_REQUEST[gradebook_id])", $course_id);
            $data[0] = $d1;
            // users who already participate in gradebook
            $d2 = Database::get()->queryArray("SELECT uid AS id, givenname, surname FROM user, gradebook_users 
                                        WHERE gradebook_users.uid = user.id AND gradebook_id = $_REQUEST[gradebook_id]");
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
               ],'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],
               'fnDrawCallback': function( oSettings ) {
                    popover_init();
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
                       'sSearch':       '".$langSearch."',
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
        $.post('$_SERVER[SCRIPT_NAME]?course=$course_code&gradebook_id=" . urlencode($_REQUEST[gradebook_id]) . "&editUsers=1',
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
    $gradebook_title = get_gradebook_title($gradebook_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
    $pageName = $langEditChange;
}

if ($is_editor) {
    // change gradebook visibility
    if (isset($_GET['vis'])) {
        Database::get()->query("UPDATE gradebook SET active = ?d WHERE id = ?d AND course_id = ?d", $_GET['vis'], getDirectReference($_GET['gradebook_id']), $course_id);
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
        $v->rule('required', array('title', 'degreerange'));
        $v->rule('numeric', array('degreerange'));
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'degreerange' => "$langTheField $langGradebookRange"
        ));
        if($v->validate()) {        
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            $newTitle = trim($_POST['title']);
            $gradebook_range = intval($_POST['degreerange']);
            $gradebook_id = Database::get()->query("INSERT INTO gradebook SET course_id = ?d, `range` = ?d, active = 1, title = ?s", $course_id, $gradebook_range, $newTitle)->lastInsertID;
            //create gradebook users (default the last six months)
            $limitDate = date('Y-m-d', strtotime(' -6 month'));
            Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid) 
                                    SELECT $gradebook_id, user_id FROM course_user
                                    WHERE course_id = ?d AND status = ".USER_STUDENT." AND reg_date > ?s",
                                            $course_id, $limitDate);

            $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count 
                                                FROM gradebook_users WHERE gradebook_id=?d ", $gradebook_id)->count;

            Session::Messages($langCreateGradebookSuccess, 'alert-success');
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&new=1");
        }
    }    
    //delete user from gradebook list
    if (isset($_GET['deleteuser']) and isset($_GET['ruid'])) {
        delete_gradebook_user(getDirectReference($_GET['gb']), getDirectReference($_GET['ruid']));        
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=".urlencode($_GET['gb'])."&gradebookBook=1");        
    }
    
    //reset gradebook users
    if (isset($_POST['resetGradebookUsers'])) {  
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();              
        if ($_POST['specific_gradebook_users'] == 2) { // specific users group
            foreach ($_POST['specific'] as $g) {
                $ug = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", getDirectReference($g));
                foreach ($ug as $u) {
                    $newUsersQuery = Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid) 
                            SELECT $gradebook_id, user_id FROM course_user
                            WHERE course_id = ?d AND user_id = ?d", $course_id, $u);                        
                }
            }
        } elseif ($_POST['specific_gradebook_users'] == 1) { // specific users            
            $active_gradebook_users = '';
            foreach ($_POST['specific'] as $u) {
                $active_gradebook_users .= getDirectReference($u) . ",";
            }
            $active_gradebook_users = substr($active_gradebook_users, 0, -1);
            $gu = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d
                                                AND uid NOT IN ($active_gradebook_users)", $gradebook_id);            
            foreach ($gu as $u) {
                delete_gradebook_user($gradebook_id, $u);
            }
            foreach ($_POST['specific'] as $u) {
                $sql = Database::get()->querySingle("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d AND uid = ?d", $gradebook_id, getDirectReference($u));                
                if (!isset($sql->uid)) {
                    $newUsersQuery = Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid) 
                            SELECT $gradebook_id, user_id FROM course_user
                            WHERE course_id = ?d AND user_id = ?d", $course_id, getDirectReference($u)); 
                }
            }
        } else { // if we want all users between dates            
            $usersstart = new DateTime($_POST['UsersStart']);
            $usersend = new DateTime($_POST['UsersEnd']);
            $gu = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
            foreach ($gu as $u) {
                delete_gradebook_user($gradebook_id, $u);
            }
            //check the rest value and rearrange the table            
            $newUsersQuery = Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid) 
                        SELECT $gradebook_id, user_id FROM course_user
                        WHERE course_id = ?d AND status = " . USER_STUDENT . " AND reg_date BETWEEN ?s AND ?s",
                                $course_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));
        }
        Session::Messages($langGradebookEdit,"alert-success");                    
        redirect_to_home_page('modules/gradebook/index.php?course=' . $course_code . '&gradebook_id=' . getIndirectReference($gradebook_id) . '&gradebookBook=1');
    }
    
    // Top menu
    $tool_content .= "<div class='row'><div class='col-sm-12'>";
    
    if (isset($_GET['editUsers']) or isset($_GET['gradeBooks'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook_title);
        $pageName = isset($_GET['editUsers']) ? $langRefreshList : $langGradebookManagement;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id) . "&amp;gradebookBook=1",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['editSettings'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook_title);
        $pageName = $langConfig;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['gradebookBook'])) {                
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook_title);
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
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id" . getIndirectReference($gradebook_id), "name" => $gradebook_title);
        $pageName = $langEditChange;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['ins'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook_title);
        $pageName = $langGradebookBook;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id),
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['addActivity']) or isset($_GET['addActivityAs']) or isset($_GET['addActivityEx']) or isset($_GET['addActivityLp'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook_title);
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
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=" . getIndirectReference($gradebook_id), "name" => $gradebook_title);
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
    
    // update gradebook settings
    if (isset($_POST['submitGradebookSettings'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'degreerange'));
        $v->rule('numeric', array('degreerange'));
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'degreerange' => "$langTheField $langGradebookRange"
        ));
        if($v->validate()) {          
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            if (isset($_POST['degreerange'])) { // update gradebook range
                $gradebook_range = intval($_POST['degreerange']);
                Database::get()->querySingle("UPDATE gradebook SET `range` = ?d WHERE id = ?d ", $gradebook_range, $gradebook_id);
            }
            if (isset($_POST['title']) && strlen(trim($_POST['title']))) { // upgrade gradebook title
                $gradebook_title = trim($_POST['title']);
                Database::get()->querySingle("UPDATE gradebook SET `title` = ?s WHERE id = ?d ", $gradebook_title, $gradebook_id);

            }
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

    //UPDATE/INSERT DB: new activity from exersices, assignments, learning paths
    elseif(isset($_GET['addCourseActivity'])) {
        $id = getDirectReference($_GET['addCourseActivity']);
        $type = intval($_GET['type']);
        add_gradebook_activity($gradebook_id, $id, $type);
        Session::Messages("$langGradebookSucInsert","alert-success");
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));        
        $display = FALSE;
    }

    //UPDATE/INSERT DB: add or edit activity to gradebook module (edit concerns and course activities like lps)
    elseif(isset($_POST['submitGradebookActivity'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();        
        if (isset($_POST['actTitle'])) {
            $actTitle = $_POST['actTitle'];
        } else {
            $actTitle = "";
        }        
        $actDesc = purify($_POST['actDesc']);
        if (isset($_POST['auto'])) {
            $auto = $_POST['auto'];
        } else {
            $auto = "";
        }
        $weight = $_POST['weight'];
        $type = $_POST['activity_type'];
        $actDate = $_POST['date'];
        if (empty($_POST['date'])) {
            $actDate = '0000-00-00 00:00:00';
        } else {
            $actDate = $_POST['date'];    
        }        
        $visible = isset($_POST['visible']) ? 1 : 0;        
        if (($_POST['id'] && $weight>(weightleft($gradebook_id, getDirectReference($_POST['id']))) && $weight != 100) 
                           || (!$_POST['id'] && $weight>(weightleft($gradebook_id, getDirectReference($_POST['id']))))) {
            Session::Messages("$langGradebookWeightAlert", "alert-warning");
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
        } elseif ((empty($weight) or ($weight == 0))) {
            Session::Messages("$langGradebookGradeAlert2", "alert-warning");
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
        } else {
            if ($_POST['id']) {               
                //update
                $id = getDirectReference($_POST['id']);
                Database::get()->query("UPDATE gradebook_activities SET `title` = ?s, date = ?t, description = ?s,
                                            `auto` = ?d, `weight` = ?d, `activity_type` = ?d, `visible` = ?d 
                                            WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $weight, $type, $visible, $id);                
                Session::Messages("$langGradebookEdit", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, 
                                                            `date` = ?t, description = ?s, weight = ?d, `activity_type` = ?d, visible = ?d", 
                                                    $gradebook_id, $actTitle, $actDate, $actDesc, $weight, $type, $visible);
                Session::Messages("$langGradebookSucInsert","alert-success");
                redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
            }
        }
        $display = FALSE;
    }

    //delete gradebook activity
    elseif (isset($_GET['delete'])) {        
        delete_gradebook_activity($gradebook_id, getDirectReference($_GET['delete']));
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=" . getIndirectReference($gradebook_id));
    
    // delete gradebook
    } elseif (isset($_GET['delete_gb'])) {        
        delete_gradebook(getDirectReference($_GET['delete_gb']));
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code");
    }
   
    //DISPLAY: list of users and form for each user
    elseif(isset($_GET['gradebookBook']) or isset($_GET['book'])) {        
        if (isset($_GET['update']) and $_GET['update']) {
            $tool_content .= "<div class='alert alert-success'>$langAttendanceUsers</div>";
        }
        //record booking
        if(isset($_POST['bookUser'])) {
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            $userID = intval(getDirectReference($_POST['userID'])); //user
            //get all the gradebook activies --> for each gradebook activity update or insert grade
            $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d", $gradebook_id);
            if ($result) {
                foreach ($result as $activity) {
                    $attend = floatval($_POST[getIndirectReference($activity->id)]); //get the record from the teacher (input name is the activity id)
                    //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT id FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if($checkForBook){
                        //update
                        Database::get()->query("UPDATE gradebook_book SET grade = ?f WHERE id = ?d ", $attend, $checkForBook->id);
                    } else {
                        //insert
                        Database::get()->query("INSERT INTO gradebook_book SET uid = ?d, gradebook_activity_id = ?d, grade = ?f, comments = ?s", $userID, $activity->id, $attend, '');
                    }
                }
                $message = "<div class='alert alert-success'>$langGradebookEdit</div>";
            }
        }
        // display user grades 
        if(isset($_GET['book'])) {
            display_user_grades($gradebook_id);             
        } else {  // display all users
            display_all_users_grades($gradebook_id);            
        }
        $display = FALSE;
    }
    elseif (isset($_GET['new'])) {
        new_gradebook(); // create new gradebook
        $display = FALSE;
    } elseif (isset($_GET['editUsers'])) { // edit gradebook users
        user_gradebook_settings($gradebook_id);
        $display = FALSE;
    } elseif (isset($_GET['editSettings'])) { // gradebook settings
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
    }
    //DISPLAY - EDIT DB: insert grades for each activity
    elseif (isset($_GET['ins'])) {
        $actID = intval(getDirectReference($_GET['ins']));
        $error = false;
        if (isset($_POST['bookUsersToAct'])) {
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            insert_grades($gradebook_id, $actID);
        }
        if (isset($_POST['updateUsersToAct'])) {  
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();          
            update_grades($gradebook_id, $actID);
        }
        register_user_grades($gradebook_id, $actID);
        $display = FALSE;
    } 
}

if (isset($display) and $display == TRUE) {
    // display gradebook
    if (isset($gradebook_id)) {
        if ($is_editor) {
            display_gradebook($gradebook_id);
        } else {
            $pageName = $gradebook_title;
            student_view_gradebook($gradebook_id); // student view
        }
    } else { // display all gradebooks
        display_gradebooks();
    }
}

draw($tool_content, 2, null, $head_content);  