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
}else{
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
                array('title' => "$langInsertWork",
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityAs=1",
                      'icon' => 'fa-flask'),
                array('title' => "$langInsertExercise",
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityEx=1",
                      'icon' => 'fa-edit'),
                array('title' => "$langLearningPath1",
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
    
    //FORM: new activity (or edit) form to gradebook module
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){

        $tool_content .= "            
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='form-wrapper'>                    
                        <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                            <fieldset>";
                                if (isset($_GET['modify'])) { //edit an existed activity            
                                    $id  = filter_var($_GET['modify'], FILTER_VALIDATE_INT);

                                    //All activity data (check if it is in this gradebook)
                                    $modifyActivity = Database::get()->querySingle("SELECT * FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $id, $gradebook_id);
                                    if ($modifyActivity) {
                                        $titleToModify = $modifyActivity->title;
                                        $contentToModify = $modifyActivity->description;
                                        $date = $modifyActivity->date;
                                        $module_auto_id = $modifyActivity->module_auto_id;
                                        $auto = $modifyActivity->auto;
                                        $weight = $modifyActivity->weight;
                                        $activity_type = $modifyActivity->activity_type;
                                        $visible = $modifyActivity->visible;
                                    } else {
                                        $activity_type = '';
                                    }            
                                    $gradebookActivityToModify = $id;

                                } else { //new activity 
                                    $gradebookActivityToModify = "";
                                    $activity_type = "";
                                    $date = date("Y-n-j", time());
                                }

                                if (!isset($contentToModify)) $contentToModify = "";
                                @$tool_content .= "
                                <div class='form-group'>
                                    <label for='activity_type' class='col-sm-2 control-label'>$langGradebookType:</label>
                                    <div class='col-sm-10'>
                                        <select name='activity_type' class='form-control'>
                                            <option value=''  " . typeSelected($activity_type, '') . " >-</option>
                                            <option value='4' " . typeSelected($activity_type, 4) . " >" . $gradebook_exams . "</option>
                                            <option value='2' " . typeSelected($activity_type, 2) . " >" . $gradebook_labs . "</option>
                                            <option value='1' " . typeSelected($activity_type, 1) . " >" . $gradebook_oral . "</option>
                                            <option value='3' " . typeSelected($activity_type, 3) . " >" . $gradebook_progress . "</option>
                                            <option value='5' " . typeSelected($activity_type, 5) . " >" . $gradebook_other_type . "</option>
                                        </select>
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <label for='actTitle' class='col-sm-2 control-label'>$langTitle:</label>
                                    <div class='col-sm-10'>
                                        <input type='text' class='form-control' name='actTitle' value='$titleToModify'/>
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <label for='date' class='col-sm-2 control-label'>$langGradebookActivityDate2:</label>
                                    <div class='col-sm-10'>
                                        <input type='text' class='form-control' name='date' value='" . datetime_remove_seconds($date) . "'/>
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <label for='weight' class='col-sm-2 control-label'>$langGradebookActivityWeight:</label>
                                    <div class='col-sm-10'>
                                        <input type='text' class='form-control' name='weight' value='$weight' size='5' /> (" . weightleft($gradebook_id, '') . " % $langGradebookActivityWeightLeft)
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <label for='visible' class='col-sm-2 control-label'>$langGradeVisible</label>
                                    <div class='col-sm-10'>
                                        <input type='checkbox' id='visible' name='visible' value='1'";
                                        if($visible == 1) {
                                            $tool_content .= " checked";
                                        }
                                    $tool_content .= " /></div>
                                </div>
                                <div class='form-group'>
                                    <label for='actDesc' class='col-sm-2 control-label'>$langComments:</label>
                                    <div class='col-sm-10'>
                                        " . rich_text_editor('actDesc', 4, 20, $contentToModify) . "
                                    </div>
                                </div>";
                                if (isset($module_auto_id) && $module_auto_id != 0) { //accept the auto booking mechanism
                                    $tool_content .= "<div class='form-group'>
                                    <label for='weight' class='col-sm-2 control-label'>$langGradebookInsAut:</label> 
                                            <div class='col-sm-10'><input type='checkbox' value='1' name='auto' ";
                                    if ($auto) {
                                        $tool_content .= " checked";
                                    }
                                    $tool_content .= "
                                        /></div>";
                                }
                                $tool_content .= "<div class='form-group'><div class='col-sm-10 col-sm-offset-2'>".form_buttons(array(
                                    array(
                                        'text' => $langAdd,
                                        'name' => 'submitGradebookActivity',
                                        'value'=> $langAdd
                                    )
                                ))."</div></div>";
                                if (isset($_GET['modify'])) {
                                    $tool_content .= "<input type='hidden' name='id' value='" . $gradebookActivityToModify . "' />";
                                }else{
                                    $tool_content .= " <input type='hidden' name='id' value='' />";
                                }
                            $tool_content .= "</fieldset>
                        </form>
                    </div>
                </div>
            </div>";

                    
        //do not show the activities list
        $showGradebookActivities = 0;
    }

    //UPDATE/INSERT DB: new activity from exersices, assignments, learning paths
    elseif(isset($_GET['addCourseActivity'])){
        $id = $_GET['addCourseActivity'];                
        $type = intval($_GET['type']);
        //check the type of the module (1 for assignments)
        if ($type == 1) {
            //checking if it's new or not
            $checkForAss = Database::get()->querySingle("SELECT * FROM assignment WHERE assignment.course_id = ?d "
                                                      . "AND assignment.active = 1 "
                                                      . "AND assignment.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 1 AND gradebook_id = ?d) AND assignment.id = ?d", $course_id, $gradebook_id, $id);        
            if ($checkForAss) {
                $module_auto_id = $checkForAss->id;
                $module_auto_type = 1; //one for assignments
                $module_auto = 1; //auto grade enabled by default
                $actTitle = $checkForAss->title;
                $actDate = $checkForAss->deadline;
                $actDesc = $checkForAss->description;
                Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $gradebook_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
                $sql = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
                    foreach ($sql as $u) {
                        $grd = Database::get()->querySingle("SELECT grade FROM assignment_submit WHERE assignment_id = ?d AND uid = $u->uid", $id);
                        if ($grd) {
                            update_gradebook_book($u->uid, $id, $grd->grade, 'assignment');
                        }
            	}
            }
        }
        //check the type of the module (2 for exercises)
        if ($type == 2) {
            //checking if it is new or not
            $checkForExe = Database::get()->querySingle("SELECT * FROM exercise WHERE exercise.course_id = ?d "
                                                         . "AND exercise.active = 1 "
                                                         . "AND exercise.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 2 AND gradebook_id = ?d) AND exercise.id = ?d", $course_id, $gradebook_id, $id);        
            if ($checkForExe) {
                $module_auto_id = $checkForExe->id;
                $module_auto_type = 2; //2 for exercises
                $module_auto = 1;
                $actTitle = $checkForExe->title;
                $actDate = $checkForExe->end_date;
                $actDesc = $checkForExe->description;

                Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $gradebook_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
                /*$sql = Database::get()->queryArray("SELECT user_id FROM course_user WHERE course_id = ?d AND status = " . USER_STUDENT . "", $course_id);
                foreach ($sql as $cu) {
                    Database::get()->query("INSERT INTO gradebook_users SET gradebook_id = ?d, uid = ?d", $gradebook_id, $cu->user_id);
                }*/
                $sql = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
                foreach ($sql as $u) {
                    $grd = Database::get()->querySingle("SELECT MAX(total_score) AS total_score, total_weighting FROM exercise_user_record 
                                            WHERE uid = $u->uid AND eid = ?d", $id);      				
                    if ($grd) {     					
                            update_gradebook_book($u->uid, $id, $grd->total_score, 'exercise');
                    }                	
            	}
            }
        }
        //check the type of the module (3 for LP - scorm and exercises)
        if ($type == 3) {            
            //checking if it is new or not
            $checkForLp = Database::get()->querySingle("SELECT * FROM lp_learnPath WHERE course_id = ?d 
                                AND learnPath_id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 3 AND gradebook_id = ?d) AND learnPath_id = ?d", $course_id, $gradebook_id, $id);                        
            if ($checkForLp) {
                $module_auto_id = $checkForLp->learnPath_id;
                $module_auto_type = 3; //3 for lp
                $module_auto = 1;
                $actTitle = $checkForLp->name;
                $actDate = date("Y-m-d");
                $actDesc = $langLearningPath . ": " . $checkForLp->name;
                Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $gradebook_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
            }
        }
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
        
        if (($_POST['id'] && $weight>(weightleft($gradebook_id, $_POST['id'])) && $weight != 100) || (!$_POST['id'] && $weight>(weightleft($gradebook_id, $_POST['id'])))){
            $message = "<p class='alert1'>$langGradebookWeightAlert</p>";
            $tool_content .= $message . "<br/>";
        } else {
            if ($_POST['id']) {                
                //update
                $id = $_POST['id'];
                Database::get()->query("UPDATE gradebook_activities SET `title` = ?s, date = ?t, description = ?s, `auto` = ?d, `weight` = ?d, `activity_type` = ?d, `visible` = ?d WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $weight, $type, $visible, $id);                
                Session::Messages("$langGradebookEdit", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, weight = ?d, `activity_type` = ?d", $gradebook_id, $actTitle, $actDate, $actDesc, $weight, $type);                
                Session::Messages("$langGradebookSucInsert","alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            }
        }
        //show activities list
        $showGradebookActivities = 1;
        Session::Messages("ok", "alert-success");
        redirect_to_home_page("modules/gradebook/index.php");
    }

    //DELETE DB: delete activity form to gradebook module (plus delete all activity student marks)
    elseif (isset($_GET['delete'])) {
            $delete = intval($_GET['delete']);
            $delAct = Database::get()->query("DELETE FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $delete, $gradebook_id)->affectedRows;
            $delActBooks = Database::get()->query("DELETE FROM gradebook_book WHERE gradebook_activity_id = ?d", $delete)->affectedRows;
            $showGradebookActivities = 1; //show list activities
            if($delAct){
                Session::Messages("$langGradebookDel", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            }else{                
                Session::Messages("$langGradebookDelFailure");
                redirect_to_home_page("modules/gradebook/index.php");
            }
            $tool_content .= $message . "<br>";
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
                foreach ($result as $announce) {

                    $attend = floatval($_POST[$announce->id]); //get the record from the teacher (input name is the activity id)
                    //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT id FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $announce->id, $userID);

                    if($checkForBook){
                        //update
                        Database::get()->query("UPDATE gradebook_book SET grade = ?f WHERE id = ?d ", $attend, $checkForBook->id);
                    }else{
                        //insert
                        Database::get()->query("INSERT INTO gradebook_book SET uid = ?d, gradebook_activity_id = ?d, grade = ?f, comments = ?s", $userID, $announce->id, $attend, '');
                    }
                }
                $message = "<div class='alert alert-success'>$langGradebookEdit</div>";
            }
        }

        //View activities for a user - (check for auto mechanism)
        if(isset($_GET['book'])){
            if(weightleft($gradebook_id, 0) == 0){
                $userID = intval($_GET['book']); //user

                //check if there are booking records for the user, otherwise alert message for first input
                $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id AND uid = ?d AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;
                if(!$checkForRecords){
                    $tool_content .="<div class='alert alert-success'>$langGradebookNewUser</div>";
                }

                //get all the activities
                $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
                $actNumber = count($result);

                if ($actNumber > 0) {
                    $tool_content .= "<h5>" . display_user($userID) . " ($langGradebookGrade: " . userGradeTotal($gradebook_id, $userID) . ")</h5>";
                    $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&book=" . $userID . "' onsubmit=\"return checkrequired(this, 'antitle');\">
                                      <table class='table-default'>";
                    $tool_content .= "<tr><th>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langGradebookType</th><th>$langGradebookWeight</th>";
                    $tool_content .= "<th width='10' class='text-center'>$langGradebookBooking</th>";
                    $tool_content .= "</tr>";
                } else {
                    $tool_content .= "<div class='alert alert-warning'>$langGradebookNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookNoActMessage2</a> $langGradebookNoActMessage3</p>\n";
                }
                
                if ($result){
                    foreach ($result as $activity) {
                        //check if there is auto mechanism
                        if($activity->auto == 1){
                            //check for autograde (if there is already a record do not propose the auto grade)
                            //if there is not get the grade from the book table                            
                            $checkForAuto = Database::get()->querySingle("SELECT id FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                            if($activity->module_auto_type && !$checkForAuto) { //assignments, exercises, lp(scorms)
                                $userGrade = attendForAutoGrades($userID, $activity->module_auto_id, $activity->module_auto_type, $gradebook_range);
                            } else {
                                $qusergrade = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                                if ($qusergrade) {
                                    $userGrade = $qusergrade->grade;
                                }
                            }
                        } else {
                            $qusergrade = Database::get()->querySingle("SELECT grade FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                            if ($qusergrade) {
                                $userGrade = $qusergrade->grade;
                            }
                        }
                        
                        $content = standard_text_escape($activity->description);
                        $d = strtotime($activity->date);

                        $tool_content .= "<tr><td><b>";

                        if (empty($activity->title)) {
                            $tool_content .= $langAnnouncementNoTille;
                        } else {
                            $tool_content .= q($activity->title);
                        }
                        $tool_content .= "</b>";
                        $tool_content .= "</td>";
                        if($activity->date){
                            $tool_content .= "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>";
                        } else {
                            $tool_content .= "<td>-</td>";
                        }                                                
                        if ($activity->module_auto_id) {
                            $tool_content .= "<td class='smaller'>$langGradebookActCour";
                            if ($activity->auto) {
                                $tool_content .= "<br>($langGradebookAutoGrade)";
                            } else {
                                $tool_content .= "<br>($langGradebookΝοAutoGrade)";
                            }
                            $tool_content .= "</td>";
                        } else {
                            $tool_content .= "<td class='smaller'>$langGradebookActAttend</td>";
                        }
                        $tool_content .= "<td width='' class='text-center'>" . $activity->weight . "%</td>";
                        @$tool_content .= "<td class='text-center'>
                        <input style='width:30px' type='text' value='".$userGrade."' name='" . $activity->id . "'"; //SOS 4 the UI!!
                        $tool_content .= ">
                        <input type='hidden' value='" . $userID . "' name='userID'>    
                        </td>";
                    } // end of while
                }
                $tool_content .= "</tr></table>";                
                $tool_content .= "<div class='pull-right'><input class='btn btn-primary' type='submit' name='bookUser' value='$langGradebookBooking'></div>";
                                                
                if(userGradeTotal($gradebook_id, $userID) > $gradebook_range){
                    $tool_content .= "<br>" . $langGradebookOutRange;
                }
                $tool_content .= "<span class='help-block'><small>" . $langGradebookUpToDegree . $gradebook_range . "</small></span>";
            } else {
                $tool_content .= "<div class='alert alert-success'>$langGradeNoBookAlert " . weightleft($gradebook_id, 0) . "%</div>";
            }
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
                                        'text' => $langInsert,
                                        'value'=> $langInsert
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
                                    <div class='col-xs-12'>
                                        <select name='usersLimit' class='form-control'>                
                                            <option value='1'>$langAttendanceActiveUsers6</option>
                                            <option value='2'>$langAttendanceActiveUsers3</option>
                                            <option value='3'>$langAttendanceActiveUsersAll</option>
                                        </select>
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <div class='col-xs-12'>".form_buttons(array(
                                    array(
                                        'text' => $langAttendanceUpdate,
                                        'name' => 'resetAttendance',
                                        'value'=> $langAttendanceUpdate
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
                                        'text' => $langGradebookUpdate,
                                        'name' => 'submitGradebookRange',
                                        'value'=> $langGradebookUpdate
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
                            <div class='col-xs-12'>
                                <input class='btn btn-primary' type='submit' name='selectGradebook' value='".$langSelect."' />
                            </div>
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
                                <div class='col-xs-12'>
                                    <input class='btn btn-primary' type='submit' name='newGradebook' value='".$langInsert."' />
                                </div>
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
                //get all the active users 
                $activeUsers = Database::get()->queryArray("SELECT uid as userID FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
                if ($activeUsers) {
                    foreach ($activeUsers as $result) {
                        $userInp = @$_POST[$result->userID]; //get the record from the teacher (input name is the user id)                        
                        $v = new Valitron\Validator($_POST);
                        $v->rule('numeric', $result->userID);
                        $v->rule('min', $result->userID, 0);
                        $v->rule('max', $result->userID, $gradebook_range);
                        if ($v->validate()) {
                            // //check if there is record for the user for this activity
                            $checkForBook = Database::get()->querySingle("SELECT COUNT(id) as count, id FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $actID, $result->userID);                    
                            if ($checkForBook->count) { // update
                                Database::get()->query("UPDATE gradebook_book SET grade = ?d WHERE id = ?d ", $userInp, $checkForBook->id);
                            } else { // insert
                                Database::get()->query("INSERT INTO gradebook_book SET uid = ?d, gradebook_activity_id = ?d, grade = ?d, comments = ?s", $result->userID, $actID, $userInp, '');
                            }
                        } else {
                            @Session::Messages($langFormErrors)->Errors($v->errors());
                            $error = true;
                        }
                    }
                    if (!$error) {
                        $message = "<div class='alert alert-success'>$langGradebookEdit</div>";
                        $tool_content .= $message . "<br/>";
                    }
                }
            }        

        //display the form and the list        
        $result = Database::get()->querySingle("SELECT * FROM gradebook_activities  WHERE id = ?d", $actID);        
        $tool_content .= "<div class='alert alert-info'>" . $result->title . "</div>";

        //show all the students
        $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID, gradebook_users.uid as userID, user.surname as surname, user.givenname as name, user.am as am, DATE(course_user.reg_date) as reg_date   FROM gradebook_users, user, course_user  WHERE gradebook_id = ?d AND gradebook_users.uid = user.id AND `user`.id = `course_user`.`user_id` AND `course_user`.`course_id` = ?d ", $gradebook_id, $course_id);

        if ($resultUsers) {
            //table to display the users
            $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&ins=" . $actID . "'>
            <table id='users_table{$course_id}' class='table-default custom_list_order'>
                <thead>
                    <tr>
                      <th width='1'>$langID</th>
                      <th><div align='left' width='150'>$langName $langSurname</div></th>
                      <th class='text-center' width='80'>$langRegistrationDateShort</th>
                      <th class='text-center'>$langGradebookGrade</th>
                      <th class='text-center'>$langAttendanceBooking</th>
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
                        <td>" . nice_format($resultUser->reg_date) . "</td>";
                        
                        $tool_content .= "
                        <td>";
                        if(weightleft($gradebook_id, 0) == 0) {                            
                            $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID);
                        } elseif (userGradeTotal($gradebook_id, $resultUser->userID) != "-") { //alert message only when grades have been submitted
                            $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID) . " (<small>" . $langGradebookGradeAlert . "</small>)";
                        }
                        if (userGradeTotal($gradebook_id, $resultUser->userID) > $gradebook_range) {
                            $tool_content .= "<br><div class='smaller'>" . $langGradebookOutRange . "</div>";
                        }
                        $tool_content .= "<td class='text-center'>
                            <input type='text' name='" . $resultUser->userID . "'";
                            //check if the user has attendace for this activity already OR if it should be automatically inserted here
                            $q = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $actID, $resultUser->userID);
                            if(isset($q->grade)) {
                                $tool_content .= " value = '$q->grade'";
                            } else{
                                $tool_content .= " value = ''";
                            }

                        $tool_content .= "><input type='hidden' value='" . $actID . "' name='actID'></td>";
                        $tool_content .= "</tr>";                        
                }
            $tool_content .= "</tbody></table> <input type='submit' class='btn btn-default' name='bookUsersToAct' value='$langGradebookBooking' /></form>";
        }
        $showGradebookActivities = 0;
    }
    
    if ($showGradebookActivities == 1) {
        display_gradebook($gradebook_id); //DISPLAY: list of gradebook activities
    }

} else { //==========Student View==============
    $userID = $uid;
    //visible flag
    $visible = 1;
    //check if there are grade records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id AND gradebook_activities.visible = ?d AND uid = ?d AND gradebook_activities.gradebook_id = ?d", $visible, $userID, $gradebook_id)->count;
    if (!$checkForRecords) {
        $tool_content .="<div class='alert alert-warning'>$langGradebookTotalGradeNoInput</div>";
    }

    $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_activities.visible = ?d AND gradebook_id = ?d  ORDER BY `DATE` DESC", $visible, $gradebook_id);
    $announcementNumber = count($result);

    if ($announcementNumber > 0) {        
        $tool_content .= "<div class='alert alert-info'>$langGradebookTotalGrade: <b>" . userGradeTotal($gradebook_id, $userID) . "</b> </div>";

        if(weightleft($gradebook_id, 0) != 0){
            $tool_content .= "<div class='alert alert-warning'>$langGradebookAlertToChange</p>";
        }

        $tool_content .= "<table class='table-default' >";
        $tool_content .= "<tr><th>$langTitle</th><th>$langGradebookActivityDate2</th><th>$langGradebookActivityDescription</th><th>$langGradebookActivityWeight</th><th>$langGradebookGrade</th></tr>";
    }

    if ($result) {
        foreach ($result as $announce) {            
            $content = standard_text_escape($announce->description);
            $announce->date = claro_format_locale_date($dateFormatLong, strtotime($announce->date));

            $tool_content .= "<tr><td><b>";

            if (empty($announce->title)) {
                $tool_content .= $langAnnouncementNoTille;
            } else {
                $tool_content .= q($announce->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'>" . nice_format($announce->date) . "</div></td>"
                    . "<td>" . $content . "</td>"
                    . "<td>" . q($announce->weight) . "%</td>";

            $tool_content .= "<td width='70' class='text-center'>";

            //check user grade for this activity
            $sql = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $announce->id, $userID);
            if ($sql) {
                $tool_content .= $sql->grade;
            } else {
                $tool_content .= "&mdash;";
            }
            $tool_content .= "</td>";
        } // end of while
    }
    $tool_content .= "</table>";
}

draw($tool_content, 2, null, $head_content);  
