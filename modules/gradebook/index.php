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
 

$display = TRUE;
if (isset($_REQUEST['gradebook_id'])) {
    $gradebook_id = $_REQUEST['gradebook_id'];
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
    $pageName = $langEditChange;
}

if ($is_editor) {
    // change gradebook visibility
    if (isset($_GET['vis'])) {   
        Database::get()->query("UPDATE gradebook SET active = ?d WHERE id = ?d AND course_id = ?d", $_GET['vis'], $_GET['gradebook_id'], $course_id);
        Session::Messages($langGlossaryUpdated, 'alert-success');
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code");
    }
    //add a new gradebook
    if (isset($_POST['newGradebook']) && strlen($_POST['title'])) {
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
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code");   
    }    
    //delete user from gradebook list
    if (isset($_GET['deleteuser']) and isset($_GET['ruid'])) {
        Database::get()->query("DELETE FROM gradebook_users WHERE uid = ?d AND gradebook_id = ?d", $_GET['ruid'], $_GET['gb']);
        Session::Messages($langGradebookEdit,"alert-success");
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$_GET[gb]&gradebookBook=1");        
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
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$gradebook_id&amp;gradebookBook=1",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
        
    } elseif (isset($_GET['new'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $pageName = $langNewGradebook;
        $tool_content .= action_bar(
            array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));
    } elseif (isset($_GET['gradebook_id']) && $is_editor && !isset($_GET['direct_link'])) {
        $book_id = $_GET['gradebook_id'];
        $gradebook_title = Database::get()->querySingle("SELECT title FROM gradebook WHERE id = ?d AND course_id = ?d", $book_id, $course_id)->title;
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebook_id=$book_id&amp;direct_link=1", "name" => $gradebook_title);
        $pageName = $langEditChange;
    }  elseif (!isset($_GET['direct_link'])) {
        $tool_content .= action_bar(
            array(
                array('title' => $langNewGradebook,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;new=1",
                      'icon' => 'fa-plus',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success')));                
    }
    $tool_content .= "</div></div>";
    
    //EDIT: edit range
    if (isset($_POST['submitGradebookRange'])) {
        $gradebook_range = intval($_POST['degreerange']);
        if ($gradebook_range == 10 or $gradebook_range == 100 or $gradebook_range == 5 or $gradebook_range == 20) {
            Database::get()->querySingle("UPDATE gradebook SET `range` = ?d WHERE id = ?d ", $gradebook_range, $gradebook_id);
            Session::Messages($langGradebookEdit,"alert-success");
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$gradebook_id");
        }
    }
    
    //EDIT: edit title
    if (isset($_POST['title']) && strlen($_POST['title'])) {
        $gradebook_title = $_POST['title'];
        Database::get()->querySingle("UPDATE gradebook SET `title` = ?s WHERE id = ?d ", $gradebook_title, $gradebook_id);
            Session::Messages($langGradebookEdit,"alert-success");
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$gradebook_id");
    }
    
    //FORM: create / edit new activity
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){
        add_gradebook_other_activity($gradebook_id);
        $display = FALSE;
    }

    //UPDATE/INSERT DB: new activity from exersices, assignments, learning paths
    elseif(isset($_GET['addCourseActivity'])) {
        $id = $_GET['addCourseActivity'];
        $type = intval($_GET['type']);
        add_gradebook_activity($gradebook_id, $id, $type);
        Session::Messages("$langGradebookSucInsert","alert-success");
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$gradebook_id");        
        $display = FALSE;
    }

    //UPDATE/INSERT DB: add or edit activity to gradebook module (edit concerns and course activities like lps)
    elseif(isset($_POST['submitGradebookActivity'])) {        
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
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$gradebook_id");
        } elseif ((empty($weight) or ($weight == 0))) {
            Session::Messages("$langGradebookGradeAlert2", "alert-warning");
            redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$gradebook_id");
        } else {
            if ($_POST['id']) {               
                //update
                $id = $_POST['id'];
                Database::get()->query("UPDATE gradebook_activities SET `title` = ?s, date = ?t, description = ?s,
                                            `auto` = ?d, `weight` = ?d, `activity_type` = ?d, `visible` = ?d 
                                            WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $weight, $type, $visible, $id);                
                Session::Messages("$langGradebookEdit", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$gradebook_id");
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, 
                                                            `date` = ?t, description = ?s, weight = ?d, `activity_type` = ?d", 
                                                    $gradebook_id, $actTitle, $actDate, $actDesc, $weight, $type);                
                Session::Messages("$langGradebookSucInsert","alert-success");
                redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$gradebook_id");
            }
        }
        $display = FALSE;
    }

    //delete gradebook activity
    elseif (isset($_GET['delete'])) {        
        delete_gradebook_activity($gradebook_id, $_GET['delete']);
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code&gradebook_id=$gradebook_id");
    
    // delete gradebook
    } elseif (isset($_GET['delete_gb'])) {        
        delete_gradebook($_GET['delete_gb']);
        redirect_to_home_page("modules/gradebook/index.php?course=$course_code");
    }
   
    //DISPLAY: list of users and form for each user
    elseif(isset($_GET['gradebookBook']) || isset($_GET['book'])) {        
        if (isset($_GET['update']) and $_GET['update']) {
            $tool_content .= "<div class='alert alert-success'>$langAttendanceUsers</div>";
        }
        //record booking
        if(isset($_POST['bookUser'])) {
            $userID = intval($_POST['userID']); //user
            //get all the gradebook activies --> for each gradebook activity update or insert grade
            $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d", $gradebook_id);
            if ($result) {
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
        } else {  // display all users
            display_all_users_grades($gradebook_id);            
        }
        $display = FALSE;
    }
    elseif (isset($_GET['new'])) {
        new_gradebook(); // create new gradebook
        $display = FALSE;
    }
    //EDIT DB: display all the gradebook users (reset the list, remove users)
    elseif (isset($_GET['editUsers'])) { // gradebook settings
        gradebook_settings($gradebook_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityAs'])) { //display available assignments       
        display_available_assignments($gradebook_id);
        $display = FALSE;
    }
    elseif (isset($_GET['addActivityEx'])) { // display available exercises
        display_available_exercises($gradebook_id);
        $display = FALSE;
    }
    elseif (isset($_GET['addActivityLp'])) { // display available lps
        display_available_lps($gradebook_id);
        $display = FALSE;
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
        register_user_grades($gradebook_id, $actID);
        $display = FALSE;
    } 
}

if (isset($display) and $display == TRUE) {
    // display gradebook
    if (isset($gradebook_id)) {
        if ($is_editor && !isset($_GET['direct_link'])) {
            display_gradebook($gradebook_id);
        } else {
            $gradebook_title = Database::get()->querySingle("SELECT title FROM gradebook WHERE id = ?d AND course_id = ?d", $gradebook_id, $course_id)->title;
            $pageName = $gradebook_title;
            student_view_gradebook($gradebook_id); // student view
        }
    } else { // display all gradebooks
        display_gradebooks();
    }
}

draw($tool_content, 2, null, $head_content);  