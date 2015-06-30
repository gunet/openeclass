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

//Datepicker
load_js('tools.js');
load_js('jquery');
load_js('bootstrap-datetimepicker');
load_js('datatables');
load_js('datatables_filtering_delay');

$head_content .= "
<script type='text/javascript'>
$(function() {
    $('input[name=date]').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
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
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
    });
});
</script>";

//change the gradebook
if (isset($_POST['selectGradebook'])){
    $gradebook_id = intval($_POST['gradebookYear']);
    $gradebook = Database::get()->querySingle("SELECT id, students_semester,`range` FROM gradebook WHERE course_id = ?d AND id = ?d  ", $course_id, $gradebook_id);
    if ($gradebook) {
      //make the others inactive
      Database::get()->querySingle("UPDATE gradebook SET active = 0 WHERE course_id = ?d AND active = 1 ", $course_id);
      //make the new active
      Database::get()->querySingle("UPDATE gradebook SET active = 1 WHERE id = ?d ", $gradebook->id);
    }
    Session::Messages($langChangeGradebookSuccess, 'alert-success');
    redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradeBooks=1");
}    
    
//add a new gradebook
if (isset($_POST['newGradebook']) && strlen($_POST['title'])){
    //make the others inactive
    Database::get()->querySingle("UPDATE gradebook SET active = 0 WHERE course_id = ?d AND active = 1 ", $course_id);
    
    $newTitle = $_POST['title'];
    $gradebook_id = Database::get()->query("INSERT INTO gradebook SET course_id = ?d, active = 1, title = ?s", $course_id, $newTitle)->lastInsertID;   
    //create gradebook users (default the last six months)
    $limitDate = date('Y-m-d', strtotime(' -6 month'));
    Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid) 
                            SELECT $gradebook_id, user_id FROM course_user
                            WHERE course_id = ?d AND status = ".USER_STUDENT." AND reg_date > ?s",
                                    $course_id, $limitDate);
        
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count 
                                        FROM gradebook_users WHERE gradebook_id=?d ", $gradebook_id)->count;
    
    Session::Messages($langCreateGradebookSuccess, 'alert-success');
    redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradeBooks=1");   
}

//gradebook_id for the course: check if there is an gradebook module for the course. If not insert it
$gradebook = Database::get()->querySingle("SELECT id, students_semester,`range`, `title` FROM gradebook WHERE course_id = ?d AND active = 1", $course_id);

if ($gradebook) {
    $gradebook_id = $gradebook->id;
    $gradebook_title = $gradebook->title;
    $gradebook_range = $gradebook->range;
    $showSemesterParticipants = $gradebook->students_semester;
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count FROM gradebook_users WHERE gradebook_id=?d ", $gradebook_id)->count;    
} else {
    //new gradebook
    $gradebook_id = Database::get()->query("INSERT INTO gradebook SET course_id = ?d, active = 1", $course_id)->lastInsertID;   
    //create gradebook users (default the last six months)
    $limitDate = date('Y-m-d', strtotime(' -6 month'));
    Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid) 
                            SELECT $gradebook_id, user_id FROM course_user
                            WHERE course_id = ?d AND status = ".USER_STUDENT." AND reg_date > ?s",
                                    $course_id, $limitDate);
        
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count 
                                        FROM gradebook_users WHERE gradebook_id=?d ", $gradebook_id)->count;
}

//==============================================
//tutor view
//==============================================
if ($is_editor) {    
    //delete users from gradebook list
    if (isset($_GET['deleteuser']) and isset($_GET['ruid'])) {
        Database::get()->query("DELETE FROM gradebook_users WHERE uid = ?d AND gradebook_id = ?d", $_GET['ruid'], $_GET['gb']);
        $_GET['gradebookBook'] = 1;
    }
    // Top menu
    $tool_content .= "<div class='row'><div class='col-sm-12'>";
    
    if(isset($_GET['editUsers']) || isset($_GET['gradeBooks'])){
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $pageName = isset($_GET['editUsers']) ? $langConfig : $langGradebookManagement;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['gradebookBook'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $pageName = $langUsers;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['modify'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $pageName = $langEditChange;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['ins'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $pageName = $langGradebookBook;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['addActivity']) or isset($_GET['addActivityAs']) or isset($_GET['addActivityEx']) or isset($_GET['addActivityLp'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
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
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['book'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $pageName = $langGradebookBook;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary-label'),
            array('title' => $langGradebookBook,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebookBook=1",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    } else {
        $pageName = ($gradebook && $gradebook->title) ? $gradebook->title : $langGradebookNoTitle2;
        $tool_content .= action_bar(
            array(
                array('title' => $langConfig,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;editUsers=1",
                      'icon' => 'fa-cog ',
                      'level' => 'primary-label'),
                array('title' => $langUsers,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebookBook=1",
                      'icon' => 'fa-users',
                      'level' => 'primary-label'),
                array('title' => $langGradebooks,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradeBooks=1",
                      'icon' => 'fa-list',
                      'level' => 'primary-label'),            
                array('title' => $langGradebookAddActivity,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1",
                      'icon' => 'fa-plus'),
                array('title' => "$langInsertWorkCap",
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityAs=1",
                      'icon' => 'fa-flask'),
                array('title' => "$langInsertExerciseCap",
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityEx=1",
                      'icon' => 'fa-edit'),
                array('title' => "$langLearningPath",
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityLp=1",
                      'icon' => 'fa-ellipsis-h')
            ),
            true,
            array(
                'secondary_title' => $langAdd,
                'secondary_icon' => 'fa-plus'
            )
        );
    }               
    $tool_content .= "</div></div>";

    //FLAG: flag to show the activities
    $showGradebookActivities = 1;
    
    //EDIT: edit range
    if (isset($_POST['submitGradebookRange'])) {
        $gradebook_range = intval($_POST['degreerange']);
        if ($gradebook_range == 10 or $gradebook_range == 100 or $gradebook_range == 5 or $gradebook_range == 20) {
            Database::get()->querySingle("UPDATE gradebook SET `range` = ?d WHERE id = ?d ", $gradebook_range, $gradebook_id);
            Session::Messages($langGradebookEdit,"alert-success");
            redirect_to_home_page("modules/gradebook/index.php");
        }
    }
    
    //EDIT: edit title
    if (isset($_POST['title']) && strlen($_POST['title'])) {
        $gradebook_title = $_POST['title'];
        Database::get()->querySingle("UPDATE gradebook SET `title` = ?s WHERE id = ?d ", $gradebook_title, $gradebook_id);
            Session::Messages($langGradebookEdit,"alert-success");
            redirect_to_home_page("modules/gradebook/index.php");
    }
    
    //FORM: create / edit new activity
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){
        add_gradebook_other_activity($gradebook_id);        
        //do not show the activities list
        $showGradebookActivities = 0;
    }

    //UPDATE/INSERT DB: new activity from exersices, assignments, learning paths
    elseif(isset($_GET['addCourseActivity'])) {
        $id = $_GET['addCourseActivity'];
        $type = intval($_GET['type']);
        add_gradebook_activity($gradebook_id, $id, $type);                
        //show gradebook activities
        $showGradebookActivities = 1;
    }

    //UPDATE/INSERT DB: add or edit activity to gradebook module (edit concerns and course activities like lps)
    elseif(isset($_POST['submitGradebookActivity'])){
        
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
        if (($_POST['id'] && $weight>(weightleft($gradebook_id, $_POST['id'])) && $weight != 100) 
                           || (!$_POST['id'] && $weight>(weightleft($gradebook_id, $_POST['id'])))) {
            Session::Messages("$langGradebookWeightAlert", "alert-warning");
            redirect_to_home_page("modules/gradebook/index.php");            
        } elseif ((empty($weight) or ($weight == 0))) {
            Session::Messages("$langGradebookGradeAlert2", "alert-warning");
            redirect_to_home_page("modules/gradebook/index.php");
        } else {
            if ($_POST['id']) {               
                //update
                $id = $_POST['id'];
                Database::get()->query("UPDATE gradebook_activities SET `title` = ?s, date = ?t, description = ?s,
                                            `auto` = ?d, `weight` = ?d, `activity_type` = ?d, `visible` = ?d 
                                            WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $weight, $type, $visible, $id);                
                Session::Messages("$langGradebookEdit", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, 
                                                            `date` = ?t, description = ?s, weight = ?d, `activity_type` = ?d", 
                                                    $gradebook_id, $actTitle, $actDate, $actDesc, $weight, $type);                
                Session::Messages("$langGradebookSucInsert","alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            }
        }
        //show activities list
        $showGradebookActivities = 1;        
    }

    //DELETE DB: delete activity form to gradebook module (plus delete all activity student marks)
    elseif (isset($_GET['delete'])) {
            $delete = $_GET['delete'];
            $delAct = Database::get()->query("DELETE FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $delete, $gradebook_id)->affectedRows;
            $delActBooks = Database::get()->query("DELETE FROM gradebook_book WHERE gradebook_activity_id = ?d", $delete)->affectedRows;
            $showGradebookActivities = 1; //show list activities
            if($delAct) {
                Session::Messages("$langGradebookDel", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            } else {
                Session::Messages("$langGradebookDelFailure", "alert-danger");
                redirect_to_home_page("modules/gradebook/index.php");
            }            
        }
   
    //DISPLAY: list of users and form for each user
    elseif(isset($_GET['gradebookBook']) || isset($_GET['book'])){        
        if (isset($_GET['update']) and $_GET['update']) {
            $tool_content .= "<div class='alert alert-success'>$langAttendanceUsers</div>";
        }        
        //record booking
        if(isset($_POST['bookUser'])){

            $userID = intval($_POST['userID']); //user
            //get all the gradebook activies --> for each gradebook activity update or insert grade
            $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d", $gradebook_id);

            if ($result){
                foreach ($result as $activity) {
                    $attend = floatval($_POST[$activity->id]); //get the record from the teacher (input name is the activity id)
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
        } else {  // display all students
            $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID, 
                                                                gradebook_users.uid as userID,                                                             
                                                                user.am as am, DATE(course_user.reg_date) as reg_date 
                                                     FROM gradebook_users, user, course_user 
                                                        WHERE gradebook_id = ?d 
                                                        AND gradebook_users.uid = user.id 
                                                        AND `user`.id = `course_user`.`user_id` 
                                                        AND `course_user`.`course_id` = ?d", $gradebook_id, $course_id);            
            if (count($resultUsers)> 0) {                
                $tool_content .= "<table id='users_table{$course_id}' class='table-default custom_list_order'>
                    <thead>
                        <tr>
                          <th width='1'>$langID</th>
                          <th><div align='left' width='100'>$langName $langSurname</div></th>
                          <th>$langRegistrationDateShort</th>
                          <th>$langGradebookGrade</th>
                          <th class='text-center'><i class='cogs'></i></th>
                        </tr>
                    </thead>
                    <tbody>";
                $cnt = 0;                
                foreach ($resultUsers as $resultUser) {
                    $cnt++;
                    $tool_content .= "
                        <tr>
                        <td>$cnt</td>
                        <td>" . display_user($resultUser->userID). " ($langAm: $resultUser->am)</td>
                        <td>" . nice_format($resultUser->reg_date) . "</td>
                        <td>";
                        if(weightleft($gradebook_id, 0) == 0) {                            
                            $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID);
                        } elseif (userGradeTotal($gradebook_id, $resultUser->userID) != "-") { //alert message only when grades have been submitted
                            $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID) . " (<small>" . $langGradebookGradeAlert . "</small>)";
                        }
                        if (userGradeTotal($gradebook_id, $resultUser->userID) > $gradebook_range) {
                            $tool_content .= "<br><div class='smaller'>" . $langGradebookOutRange . "</div>";
                        }
                    $tool_content .="</td><td class='option-btn-cell'>".
                            action_button(array(
                                array('title' => $langGradebookBook,
                                        'icon' => 'fa-plus',
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;book=$resultUser->userID"),
                                array('title' => $langGradebookDelete,
                                        'icon' => 'fa-times',
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gb=$gradebook_id&amp;ruid=$resultUser->userID&amp;deleteuser=yes",
                                        'class' => 'delete',
                                        'confirm' => $langConfirmDelete)))
                                ."</td></tr>";
                }
                $tool_content .= "</tbody></table>";
            } else {
                $tool_content .= "<div class='alert alert-warning'>$langNoRegStudent <a href='$_SERVER[PHP_SELF]?course=$course_code&amp;editUsers=1'>$langHere</a>.</div>";
            }
        }
        //do not show activities list
        $showGradebookActivities = 0;
    }
    
    //EDIT DB: display all the gradebook users (reset the list, remove users)
    elseif (isset($_GET['editUsers'])) {
        //delete users from gradebook list
        if (isset($_POST['deleteSelectedUsers'])) {
            foreach ($_POST['recID'] as $value) {
                $value = intval($value);
                //delete users from gradebook users table
                Database::get()->query("DELETE FROM gradebook_users WHERE id=?d ", $value);
            }
        }

        //query to reset users in attedance list
        if (isset($_POST['resetAttendance'])) {
            $usersLimit = intval($_POST['usersLimit']);
            if ($usersLimit == 1) {
                $limitDate = date('Y-m-d', strtotime(' -6 month'));
            } elseif ($usersLimit == 2) {
                $limitDate = date('Y-m-d', strtotime(' -3 month'));
            } elseif ($usersLimit == 3) {
                $limitDate = "0000-00-00";
            }

            //update the main gradebook table
            Database::get()->querySingle("UPDATE gradebook SET `students_semester` = ?d WHERE id = ?d ", $usersLimit, $gradebook_id);
            //clear gradebook users table
            Database::get()->querySingle("DELETE FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
            //check the rest value and rearrange the table            
            $newUsersQuery = Database::get()->query("INSERT INTO gradebook_users (gradebook_id, uid) 
                        SELECT $gradebook_id, user_id FROM course_user
                        WHERE course_id = ?d AND status = ".USER_STUDENT." AND reg_date > ?s",
                                $course_id, $limitDate);
            if ($newUsersQuery) {
                redirect_to_home_page('modules/gradebook/index.php?course=' . $course_code . '&gradebookBook=1&update=true');
            } else {
                $tool_content .= "<div class='alert alert-warning'>$langNoStudents</div>";
            }
        }
        
        //===================================================
        //section to insert or edit the title of the gradebook
        //===================================================
        
        $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">
                        <div class='form-group'>
                            <label class='col-xs-12'>$langTitle</label>                           
                            <div class='col-xs-12'>
                                <input class='form-control' type='text' placeholder='$langTitle' name='title' value='$gradebook_title'/>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-xs-12'>".form_buttons(array(
                                    array(
                                        'text' => $langSave,
                                        'value'=> $langInsert
                                    ),
                                    array(
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                    )
                                ))."</div>                        
                        </div>
                    </form>
                </div>
            </div>
        </div>";
        
        //==============================================
        //section to reset the gradebook users list
        //==============================================
        
        $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">
                        <div class='form-group'>
                            <label class='col-xs-12'>$langRefreshList<small class='help-block'>($langGradebookInfoForUsers)</small></label></div>                            
                                <div class='form-group'>
                                    <div class='col-xs-12'>".
                            selection(array('1' => $langAttendanceActiveUsers6, 
                                            '2' => $langAttendanceActiveUsers3, 
                                            '3' => $langAttendanceActiveUsersAll), 
                                        'usersLimit', $langAttendanceActiveUsers6, "class='form-control'")."                                        
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <div class='col-xs-12'>".form_buttons(array(
                                    array(
                                        'text' => $langSave,
                                        'name' => 'resetAttendance',
                                        'value'=> $langAttendanceUpdate
                                    ),
                                    array(
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                    )
                                ))."</div>
                                </div>
                    </form>
                </div>
            </div>
        </div>";
  
        //==============================================
        //show degree range
        //==============================================
        
        $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
                        <fieldset>
                        <div class='form-group'><label class='col-xs-12'>$langGradebookRange</label></div>                            
                            <div class='form-group'>
                                <div class='col-xs-12'>
                                    <select name='degreerange' class='form-control'><option value=10";
                                        if (isset($gradebook_range) and $gradebook_range == 10) {
                                            $tool_content .= " selected ";
                                        }
                                        $tool_content .= ">0-10</option><option value=20";
                                        if (isset($gradebook_range) and $gradebook_range == 20) {
                                            $tool_content .= " selected ";
                                        }
                                        $tool_content .= ">0-20</option><option value=5";
                                        if (isset($gradebook_range) and $gradebook_range == 5) {
                                            $tool_content .= " selected ";
                                        }
                                        $tool_content .= ">0-5</option><option value=100";
                                        if (isset($gradebook_range) and $gradebook_range == 100) {
                                            $tool_content .= " selected ";
                                        }
                                        $tool_content .= ">0-100</option></select>";
                            $tool_content .= "</div>
                            </div>
                            <div class='form-group'>
                                <div class='col-xs-12'>".form_buttons(array(
                                    array(
                                        'text' => $langSave,
                                        'name' => 'submitGradebookRange',
                                        'value'=> $langGradebookUpdate
                                    ),
                                    array(
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                    )
                                ))."</div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>";
                            
        
        //do not show activities list
        $showGradebookActivities = 0;
    } elseif (isset($_GET['gradeBooks'])) {
        //===================================================
        //section to insert new gradebook and select another
        //===================================================        
        $result = Database::get()->queryArray("SELECT * FROM gradebook  WHERE course_id = ?d", $course_id);

        $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='form-wrapper'>    
                    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">
                        <div class='form-group'>
                            <label class='col-xs-12'>$langChangeGradebook<small class='help-block'>$langChangeGradebook2</small></label>                            
                            <div class='col-xs-12'>
                                <select class='form-control' name='gradebookYear'>";
                                if ($result){
                                    foreach ($result as $year){
                                        if($year->title == ""){
                                            $title = $langGradebookNoTitle2;
                                        } else{
                                            $title = $year->title;
                                        }
                                        $tool_content .= "<option value='$year->id'";
                                            if ($gradebook_id == $year->id) {
                                                $tool_content .= " selected";
                                            }
                                            $tool_content .= ">$title</option>";
                                    }
                                }
                 $tool_content .="
                                </select>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-xs-12'>".form_buttons(array(
                                    array(
                                        'text' => $langSelect,
                                        'name' => 'selectGradebook',
                                        'value'=> $langSelect
                                    ),
                                    array(
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                    )
                                ))."</div>
                        </div>
                    </form>
                </div>
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">
                        <div class='form-group'>
                            <label class='col-xs-12'>$langNewGradebook<small class='help-block'>$langNewGradebook2</small></label></div>                            
                            <div class='form-group'> 
                                <div class='col-xs-12'>
                                    <input class='form-control' type='text' placeholder='$langTitle' name='title'/>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-xs-12'>".form_buttons(array(
                                    array(
                                            'text' => $langSave,
                                            'name' => 'newGradebook',
                                            'value'=> $langInsert
                                        ),
                                    array(
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                        )
                                    ))."</div>
                            </div>
                    </form>
                </div>                
            </div>
        </div>";
        $showGradebookActivities = 0;
        
    } elseif (isset($_GET['addActivityAs'])) { //display available assignments       
        display_available_assignments($gradebook_id);        
        $showGradebookActivities = 0;
    }
    elseif (isset($_GET['addActivityEx'])) { // display available exercises
        display_available_exercises($gradebook_id);
        $showGradebookActivities = 0;
    }    
    elseif (isset($_GET['addActivityLp'])) { // display available lps
        display_available_lps($gradebook_id);
        $showGradebookActivities = 0;
    }

    //DISPLAY - EDIT DB: insert grades for each activity
    elseif (isset($_GET['ins'])) {        
        $actID = intval($_GET['ins']);
        $error = false;
        if (isset($_POST['bookUsersToAct'])) {
            insert_grades($gradebook_id, $actID);
        }
        if (isset($_POST['updateUsersToAct'])) {            
            update_grades($gradebook_id, $actID);
        }
        display_gradebook_users($gradebook_id, $actID);
        $showGradebookActivities = 0;
    }
    
    if ($showGradebookActivities == 1) {
        display_gradebook($gradebook_id); //DISPLAY: list of gradebook activities
    }

} else {
    student_view_gradebook($gradebook_id); // student view
}

draw($tool_content, 2, null, $head_content);  
