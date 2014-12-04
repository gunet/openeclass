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

//Module name
$nameTools = $langGradebook;

//Datepicker
load_js('tools.js');
load_js('jquery');
load_js('jquery-ui');
load_js('jquery-ui-timepicker-addon.min.js');
load_js('datatables');
load_js('datatables_filtering_delay');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
<script type='text/javascript'>
$(function() {
    $('input[name=date]').datetimepicker({
        dateFormat: 'yy-mm-dd', 
        timeFormat: 'hh:mm'
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


//gradebook_id for the course: check if there is an gradebook module for the course. If not insert it
$gradebook = Database::get()->querySingle("SELECT id, students_semester,`range` FROM gradebook WHERE course_id = ?d ", $course_id);
if ($gradebook) {
    $gradebook_id = $gradebook->id;
    $gradebook_range = $gradebook->range;
    $showSemesterParticipants = $gradebook->students_semester;
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count FROM gradebook_users WHERE gradebook_id=?d ", $gradebook_id)->count;    
}else{
    //new gradebook
    $gradebook_id = Database::get()->query("INSERT INTO gradebook SET course_id = ?d ", $course_id)->lastInsertID;   
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
    
    if(isset($_GET['editUsers'])){
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $nameTools = $langConfig;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['gradebookBook'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $nameTools = $langUsers;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['modify'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $nameTools = $langModify;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['ins'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $nameTools = $langGradebookBook;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['addActivity']) or isset($_GET['addActivityAs']) or isset($_GET['addActivityEx']) or isset($_GET['addActivityLp'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        if (isset($_GET['addActivityAs'])) {
            $nameTools = "$langAdd $langInsertWork";
        } elseif (isset($_GET['addActivityEx'])) {
            $nameTools = "$langAdd $langInsertExercise";
        } else {
            $nameTools = $langGradebookAddActivity;
        }
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['book'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langGradebook);
        $nameTools = $langGradebookBook;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label'),
            array('title' => $langGradebookBook,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebookBook=1",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    } else {
        $nameTools = $langGradebook;
        $tool_content .= action_bar(array(
            array('title' => $langConfig,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;editUsers=1",
                  'icon' => 'fa fa-cog space-after-icon',
                  'level' => 'primary-label'),
            array('title' => $langUsers,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebookBook=1",
                  'icon' => 'fa fa-users'),
            array('title' => $langGradebookAddActivity,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1",
                  'icon' => 'fa fa-plus'),
            array('title' => "$langAdd $langInsertWork",
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityAs=1",
                  'icon' => 'fa fa-flask'),
            array('title' => "$langAdd $langInsertExercise",
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityEx=1",
                  'icon' => 'fa fa-edit'),
            array('title' => "$langAdd $langLearningPath1",
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityLp=1",
                  'icon' => 'fa fa-ellipsis-h'),
            ));
    }               
    $tool_content .= "</div></div>";

    //FLAG: flag to show the activities
    $showGradebookActivities = 1;
    
    //EDIT: edit range
    if (isset($_POST['submitGradebookRange'])) {
        $gradebook_range = intval($_POST['degreerange']);
        if($gradebook_range == 10 || $gradebook_range == 100 || $gradebook_range == 5){
            Database::get()->querySingle("UPDATE gradebook SET `range` = ?d WHERE id = ?d ", $gradebook_range, $gradebook_id);
            Session::Messages($langGradebookEdit,"alert-success");
            redirect_to_home_page("modules/gradebook/index.php");
        }
    }
    
    
    /*
    //UPDATE/INSERT DB: edit users display number 
    if (isset($_POST['submitGradebookActiveUsers'])) {
        $gradebook_users_limit = intval($_POST['usersLimit']);
        if($gradebook_users_limit ==1 || $gradebook_users_limit == 0){
            Database::get()->querySingle("UPDATE gradebook SET `students_semester` = ?d WHERE id = ?d ", $gradebook_users_limit, $gradebook_id);
            $message = "<p class='success'>$langGradebookEdit</p>";
            $tool_content .= $message . "<br/>";
            //update value for the check box and the users query
            $showSemesterParticipants = $gradebook_users_limit;
        }
    }

    //Number of students for this gradebook book and limit_date (depends on the limit of the last semester selection - if $showSemesterParticipants = 1 --> Users that have logged in to the course the last 6 months)
    if ($showSemesterParticipants) {
        //Six months limit
        $limitDate = date('Y-m-d', strtotime(' -6 months'));
        $participantsNumber = Database::get()->querySingle("SELECT COUNT(DISTINCT user_id) as count FROM actions_daily, user WHERE actions_daily.user_id = user.id AND user.status = ?d AND course_id = ?d AND actions_daily.day > ?t ", USER_STUDENT, $course_id, $limitDate)->count;
    } else {
        $limitDate = "0000-00-00";
        $participantsNumber = Database::get()->querySingle("SELECT COUNT(user.id) as count FROM course_user, user WHERE course_user.course_id = ?d AND course_user.user_id = user.id AND user.status = ?d ", $course_id, USER_STUDENT)->count;
    }
    */
    
    //FORM: new activity (or edit) form to gradebook module
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){

        $tool_content .= "
            
            <div class='row'>
                <div class='col-sm-12'>
                    <div class='form-wrapper'>
                    <h4>$langGradebookActAttend</h4>
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
                                        <input type='checkbox' class='form-control' id='visible' name='visible' value='1'";
                                        if($visible){
                                            $tool_content .= " checked";
                                        }
                                    $tool_content .= " /></div>
                                </div>
                                <div class='form-group'>
                                    <label for='actDesc' class='col-sm-2 control-label'>$langGradebookActivityWeight:</label>
                                    <div class='col-sm-10'>
                                        " . rich_text_editor('actDesc', 4, 20, $contentToModify) . "
                                    </div>
                                </div>";
                                if (isset($module_auto_id)) { //accept the auto booking mechanism
                                    $tool_content .= "<div class='form-group'>
                                    <label for='weight' class='col-sm-2 control-label'>$langGradebookInsAut:</label> 
                                            <div class='col-sm-10'><input type='checkbox' class='form-control' value='1' name='auto' ";
                                    if ($auto) {
                                        $tool_content .= " checked";
                                    }
                                    $tool_content .= "
                                        /></div>";
                                }
                                $tool_content .= "<div class='col-sm-offset-2 col-sm-10'>
                                                <input class='btn btn-primary' type='submit' name='submitGradebookActivity' value='$langAdd' />
                                            </div>";
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

    //UPDATE/INSERT DB: new activity from exersices, assignments, lps or scorm
    elseif(isset($_GET['addCourseActivity'])){

        $id = intval($_GET['addCourseActivity']);
        $type = intval($_GET['type']);

        //check the type of the module (1 for assignments)
        if ($type == 1) {
            //checking if it is new or not
            $checkForAss = Database::get()->querySingle("SELECT * FROM assignment WHERE assignment.course_id = ?d "
                                                      . "AND  assignment.active = 1 "
                                                      . "AND assignment.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 1) AND assignment.id = ?d", $course_id, $id);        
            if ($checkForAss) {
                $module_auto_id = $checkForAss->id;
                $module_auto_type = 1; //one for assignments
                $module_auto = 1; //auto grade enabled by default
                $actTitle = $checkForAss->title;
                $actDate = $checkForAss->deadline;
                $actDesc = $checkForAss->description;
                Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $gradebook_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
            }
        }
        //check the type of the module (2 for exercises)
        if ($type == 2) {
            //checking if it is new or not
            $checkForExe = Database::get()->querySingle("SELECT * FROM exercise WHERE exercise.course_id = ?d "
                                                         . "AND  exercise.active = 1 "
                                                         . "AND exercise.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 2) AND exercise.id = ?d", $course_id, $id);        
            if ($checkForExe) {
                $module_auto_id = $checkForExe->id;
                $module_auto_type = 2; //one for assignments
                $module_auto = 1;
                $actTitle = $checkForExe->title;
                $actDate = $checkForExe->end_date;
                $actDesc = $checkForExe->description;

                Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $gradebook_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
            }
        }
        //check the type of the module (3 for LP - scorm and exercises)
        if ($type == 3) {
            //checking if it is new or not
            $checkForLp = Database::get()->querySingle("SELECT 
                lp_module.module_id, lp_module.name, lp_module.contentType, lp_learnPath.name as lp_name
                FROM lp_module, lp_rel_learnPath_module,lp_learnPath 
                WHERE lp_module.course_id = ?d 
                AND lp_module.module_id = lp_rel_learnPath_module.module_id
                AND lp_rel_learnPath_module.learnPath_id = lp_learnPath.learnPath_id
                AND lp_learnPath.visible = 1
                AND lp_module.module_id = ?d
                AND (lp_module.contentType = 'EXERCISE' OR lp_module.contentType = 'SCORM_ASSET' OR lp_module.contentType = 'SCORM')
                AND lp_module.module_id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 3)", $course_id, $id);        
            if ($checkForLp) {
                $module_auto_id = $checkForLp->module_id;
                $module_auto_type = 3; //3 for lp
                $module_auto = 1;
                $actTitle = $checkForLp->lp_name;
                $actDate = date("Y-m-d");
                $actDesc = $langLearningPath . ": " . $checkForLp->lp_name;
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
        $visible = isset($_POST['visible']) ? 1 : 0;
        
        if (($_POST['id'] && $weight>(weightleft($gradebook_id, $_POST['id'])) && $weight != 100) || (!$_POST['id'] && $weight>(weightleft($gradebook_id, $_POST['id'])))){
            $message = "<p class='alert1'>$langGradebookWeightAlert</p>";
            $tool_content .= $message . "<br/>";
        } else {            
            if ($_POST['id']) {               
                //update
                $id = $_POST['id'];
                Database::get()->query("UPDATE gradebook_activities SET `title` = ?s, date = ?t, description = ?s, `auto` = ?d, `weight` = ?d, `activity_type` = ?d, `visible` = ?d WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $weight, $type, $visible, $id);
                $langAnnDel = "$langGradebookEdit";
                Session::Messages("$langAnnDel", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, weight = ?d, `activity_type` = ?d", $gradebook_id, $actTitle, $actDate, $actDesc, $weight, $type);
                $langAnnDel = "$langGradebookSucInsert";
                Session::Messages("$langAnnDel","alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            }
        }
        //show activities list
        $showGradebookActivities = 1;
        Session::Messages("ok", "alert-success");
        redirect_to_home_page("modules/gradebook/index.php");
    }

    //DELETE DB: delete activity form to gradebook module (plus delete all the marks for alla students for this activity)
    elseif (isset($_GET['delete'])) {
            $delete = intval($_GET['delete']);
            $delAct = Database::get()->query("DELETE FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $delete, $gradebook_id)->affectedRows;
            $delActBooks = Database::get()->query("DELETE FROM gradebook_book WHERE gradebook_activity_id = ?d", $delete)->affectedRows;
            $showGradebookActivities = 1; //show list activities
            if($delAct){
                Session::Messages("$langAnnDel", "alert-success");
                redirect_to_home_page("modules/gradebook/index.php");
            }else{
                $langAnnDel = $langGradebookDelFailure;
                Session::Messages("$langAnnDel");
                redirect_to_home_page("modules/gradebook/index.php");
            }
            $tool_content .= $message . "<br/>";
        }
   

    //DISPLAY: list of users and form for each user
    elseif(isset($_GET['gradebookBook']) || isset($_GET['book'])){        
        if (isset($_GET['update']) and $_GET['update']) {
            $tool_content .= "<div class='alert-success'>$langAttendanceUsers</div>";
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
                $message = "<div class='alert-success'>$langGradebookEdit</div>";
            }
        }

        //View activities for a user - (check for auto mechanism)
        if(isset($_GET['book'])){
            if(weightleft($gradebook_id, 0) == 0){
                $userID = intval($_GET['book']); //user

                //check if there are booking records for the user, otherwise alert message for first input
                $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id AND uid = ?d AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;
                if(!$checkForRecords){
                    $tool_content .="<div class='alert-success'>$langGradebookNewUser</div>";
                }

                //get all the activities
                $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
                $actNumber = count($result);

                if ($actNumber > 0) {
                    $tool_content .= "<h4>" . display_user($userID) . "</h4>";
                    $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&book=" . $userID . "' onsubmit=\"return checkrequired(this, 'antitle');\">
                                      <table class='table-default'>";
                    $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langGradebookType</th><th>$langGradebookWeight</th>";
                    $tool_content .= "<th width='10' class='text-center'>$langGradebookBooking</th>";
                    $tool_content .= "</tr>";
                } else {
                    $tool_content .= "<div class='alert-warning'>$langGradebookNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookNoActMessage2</a> $langGradebookNoActMessage3</p>\n";
                }
                //ui counter
                $k = 0;

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

                        if ($k % 2 == 0) {
                            $tool_content .= "<tr class='even'>";
                        } else {
                            $tool_content .= "<tr class='odd'>";
                        }

                        $tool_content .= "<td width='16' valign='top'>
                            <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                            <td><b>";

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
                        @$tool_content .= "<td  class='text-center'>
                        <input style='width:30px' type='text' value='".$userGrade."' name='" . $activity->id . "'"; //SOS 4 the UI!!
                        $tool_content .= ">
                        <input type='hidden' value='" . $userID . "' name='userID'>    
                        </td>";
                        $k++;
                    } // end of while
                }
                $tool_content .= "<tr><td colspan=7 class='right'><input type='submit' name='bookUser' value='$langGradebookBooking' /></td></tr>";
                
                $tool_content .= "<tr><td colspan=7 >". $langGradebookGrade . ":" . userGradeTotal($gradebook_id, $userID);
                
                if(userGradeTotal($gradebook_id, $userID) > $gradebook_range){
                    $tool_content .= "<br>" . $langGradebookOutRange;
                }
                
                $tool_content .= "</td></tr>";
                $tool_content .= "<tr><td colspan=7 class='smaller'>" . $langGradebookUpToDegree . $gradebook_range . "</td></tr></table></form>";

            } else {
                $tool_content .="<div class='alert1'>$langGradeNoBookAlert " . weightleft($gradebook_id, 0) . "%</div>";
            }
        } else {            
        //========================
        //show all the students
        //========================        
            $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID, 
                                                                gradebook_users.uid as userID,                                                             
                                                                user.am as am, course_user.reg_date as reg_date 
                                                     FROM gradebook_users, user, course_user 
                                                        WHERE gradebook_id = ?d 
                                                        AND gradebook_users.uid = user.id 
                                                        AND `user`.id = `course_user`.`user_id` 
                                                        AND `course_user`.`course_id` = ?d", $gradebook_id, $course_id);            
            if (count($resultUsers)> 0) {
                //table to display the users
                $tool_content .= "
                <table id='users_table{$course_id}' class='table-default custom_list_order'>
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
                                array('title' => $langGradebookDelete,
                                        'icon' => 'fa-times',
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gb=$gradebook_id&amp;ruid=$resultUser->userID&amp;deleteuser=yes",
                                        'class' => 'delete',
                                        'confirm' => $langConfirmDelete),
                                array('title' => $langGradebookBook,
                                        'icon' => 'fa-plus',
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;book=$resultUser->userID")))
                                ."</td></tr>";
                }
                $tool_content .= "</tbody></table>";
            } else {
                $tool_content .= "<div class='alert-warning'>$langNoRegStudent <a href='$_SERVER[PHP_SELF]?course=$course_code&amp;editUsers=1'>$langHere</a>.</div>";
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
                $tool_content .= "<div class='alert-warning'>$langNoStudents</div>";
            }
        }

        //section to reset the gradebook users list
        
        $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='form-wrapper'>
                    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">
                        <fieldset>
                            <h3>$langRefreshList</h3><small>($langAttendanceInfoForUsers)</small><br><br>
                            <div class='form-group'>
                                <div class='col-sm-12'>
                                    <select name='usersLimit' class='form-control'>                
                                        <option value='1'>$langAttendanceActiveUsers6</option>
                                        <option value='2'>$langAttendanceActiveUsers3</option>
                                        <option value='3'>$langAttendanceActiveUsersAll</option>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-sm-10'>
                                    <input class='btn btn-primary' type='submit' name='resetAttendance' value='$langAttendanceUpdate' />
                                </div>
                            </div>
                        </fieldset>
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
                            <h3>$langGradebookRange</h3><br>
                            <div class='form-group'>
                                <div class='col-sm-10'>
                                    <select name='degreerange'><option value=10";
                                        if (isset($gradebook_range) and $gradebook_range == 10) {
                                            $tool_content .= " selected ";
                                        }
                                        $tool_content .= ">0-10</option><option value=5";
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
                                <div class='col-sm-12'>
                                    <input class='btn btn-primary' type='submit' name='submitGradebookRange' value='$langGradebookUpdate' />
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>";
        
        //do not show activities list
        $showGradebookActivities = 0;
    }

    elseif (isset($_GET['addActivityAs'])) {
        //Assignments
        $checkForAss = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 1)", $course_id);

        $checkForAssNumber = count($checkForAss);
        
        if ($checkForAssNumber > 0) {
            $tool_content .= "
                <div class='row'><div class='col-sm-12'><div class='table-responsive'>
                <h4>$langWorks</h4>
                              <table class='table-default'";
            $tool_content .= "<tr><th>$langTitle</th><th>$m[deadline]</th><th>$langDescription</th>";
            $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>"; 
            $tool_content .= "</tr>";           
            foreach ($checkForAss as $newAssToGradebook) {
                $content = ellipsize_html($newAssToGradebook->description, 50);
                if($newAssToGradebook->assign_to_specific){
                    $content .= "($langGradebookAssignSpecific)<br>";
                    $checkForAssSpec = Database::get()->queryArray("SELECT user_id, user.surname , user.givenname FROM `assignment_to_specific`, user WHERE user_id = user.id AND assignment_id = ?d", $newAssToGradebook->id);
                    foreach ($checkForAssSpec as $checkForAssSpecR) {
                        $content .= q($checkForAssSpecR->surname). " " . q($checkForAssSpecR->givenname) . "<br>";
                    }
                }

                if((int) $newAssToGradebook->deadline){
                    $d = strtotime($newAssToGradebook->deadline);
                    $date_str = ucfirst(claro_format_locale_date($dateFormatLong, $d));
                    $hour_str = "($langHour: " . ucfirst(date('H:i', $d)).")";
                }else{
                    $date_str = $m['no_deadline'];
                    $hour_str = "";
                }

                $tool_content .= "<tr><td><b>";

                if (empty($newAssToGradebook->title)) {
                    $tool_content .= $langAnnouncementNoTille;
                } else {
                    $tool_content .= q($newAssToGradebook->title);
                }
                $tool_content .= "</b>";
                $tool_content .= "</td>"
                        . "<td><div class='smaller'><span class='day'>$date_str</span> $hour_str </div></td>"
                        . "<td>" . $content . "</td>";
                $tool_content .= "<td width='70' class='text-center'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=" . $newAssToGradebook->id . "&amp;type=1");
            } // end of while        
            $tool_content .= "</tr></table></div></div></div>";
        } else {
               $tool_content .= "<p class='alert1'>$langAttendanceNoActMessageAss4</p>";
        }
        $showGradebookActivities = 0;
    }

    elseif (isset($_GET['addActivityEx'])){
        //Exercises
        $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d 
                                AND exercise.active = 1 AND exercise.id 
                                NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 2)", $course_id);
        $checkForExerNumber = count($checkForExer);
        if ($checkForExerNumber > 0) {
            $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>";
            $tool_content .= "<tr><th>$langTitle</th><th>$langGradebookActivityDate2</th><th>Περιγραφή</th>";
            $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
            $tool_content .= "</tr>";
            
            foreach ($checkForExer as $newExerToGradebook) {
                $content = ellipsize_html($newExerToGradebook->description, 50);
                $d = strtotime($newExerToGradebook->end_date);
               

                $tool_content .= "<tr>
                        <td><b>";

                if (empty($newExerToGradebook->title)) {
                    $tool_content .= $langAnnouncementNoTille;
                } else {
                    $tool_content .= q($newExerToGradebook->title);
                }
                $tool_content .= "</b>";
                $tool_content .= "</td>"
                        . "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>"
                        . "<td>" . $content . "</td>";

                $tool_content .= "<td class='text-center option-btn-cell'>".  action_button(array(
                    array('title' => $langAdd,
                          'icon' => 'fa-plus',
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=" . $newExerToGradebook->id . "&amp;type=2")));
            } // end of while        
            $tool_content .= "</td></tr></table></div></div></div>";
        } else {
            $tool_content .= "<div class='alert-warning'>$langAttendanceNoActMessageExe4</div>";
        }
        $showGradebookActivities = 0;
    }

    elseif (isset($_GET['addActivityLp'])) {
        //Learning paths - SCORMS
        $checkForLp = Database::get()->queryArray("SELECT 
                lp_module.module_id, lp_module.name, lp_module.contentType, lp_learnPath.name as lp_name
                FROM lp_module, lp_rel_learnPath_module,lp_learnPath 
                WHERE lp_module.course_id = ?d 
                AND lp_module.module_id = lp_rel_learnPath_module.module_id
                AND lp_rel_learnPath_module.learnPath_id = lp_learnPath.learnPath_id
                AND lp_learnPath.visible = 1
                AND (lp_module.contentType = 'EXERCISE' OR lp_module.contentType = 'SCORM_ASSET' OR lp_module.contentType = 'SCORM')
                AND lp_module.module_id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 3)", $course_id);

        $checkForLpNumber = count($checkForLp);
        
        if ($checkForLpNumber > 0) {
            $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
            $tool_content .= "<table class='table-default' id='t1'>";
            $tool_content .= "<tr><th colspan='2'>$langLearningPath</th></tr>";
            $tool_content .= "<tr><th colspan='2'>$langTitle</th><th>$langLearningPath</th><th>$langGradebookType</th>";
            $tool_content .= "<th class='text-center'>$langActions</th>";
            $tool_content .= "</tr>";
             
            foreach ($checkForLp as $newExerToGradebook) {
                $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td><b>";
                $tool_content .= q($newExerToGradebook->name);
                $tool_content .= "</b>";
                $tool_content .= "</td>";
                $tool_content .= "<td>" .$newExerToGradebook->lp_name. "</td>";
                $tool_content .= "<td>";
                if($newExerToGradebook->contentType == "EXERCISE"){
                    $tool_content .= $langGradebookActivityLpExe;
                }else{
                    $tool_content .= $newExerToGradebook->contentType;
                }
                $tool_content .= "</td>";
                $tool_content .= "
                <td width='70' class='right'>";
                $tool_content .= "<td width='70' class='text-center'>".icon('add', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newExerToGradebook->module_id&amp;type=3")."&nbsp;";
                $k++;
            } // end of while        
            $tool_content .= "</table></div></div></div>";
        } else {
            $tool_content .= "<div class='alert-warning'>$langAttendanceNoActMessageLp4</div>";
        }
        $showGradebookActivities = 0;
    }

    //DISPLAY - EDIT DB: insert grades for each activity
    elseif (isset($_GET['ins'])){

        $actID = intval($_GET['ins']);

        //record booking
        if(isset($_POST['bookUsersToAct'])){                        

            //get all the active users 
            $activeUsers = Database::get()->queryArray("SELECT uid as userID FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);

            if ($activeUsers){                
                foreach ($activeUsers as $result) {
                    
                    $userInp = intval(@$_POST[$result->userID]); //get the record from the teacher (input name is the user id)    
                    
                    // //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT COUNT(id) as count, id FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $actID, $result->userID);
                    
                    if($checkForBook->count){                        
                        //update
                        Database::get()->query("UPDATE gradebook_book SET grade = ?d WHERE id = ?d ", $userInp, $checkForBook->id);
                    }else{                        
                        //insert
                        Database::get()->query("INSERT INTO gradebook_book SET uid = ?d, gradebook_activity_id = ?d, grade = ?d, comments = ?s", $result->userID, $actID, $userInp, '');
                    }
                }
                
                $message = "<p class='success'>$langGradebookEdit</p>";
                $tool_content .= $message . "<br/>";
            }
        }

        //display the form and the list
        
        $result = Database::get()->querySingle("SELECT * FROM gradebook_activities  WHERE id = ?d", $actID);
        
        $tool_content .= "<h3>" . $langGradebookBook . ": " . $result->title . "</h3><br>";               

        //show all the students
        $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID, gradebook_users.uid as userID, user.surname as surname, user.givenname as name, user.am as am, course_user.reg_date as reg_date   FROM gradebook_users, user, course_user  WHERE gradebook_id = ?d AND gradebook_users.uid = user.id AND `user`.id = `course_user`.`user_id` AND `course_user`.`course_id` = ?d ", $gradebook_id, $course_id);

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

                        $tool_content .= "><input type='hidden' value='" . $actID . "' name='actID'>
                        </td>";   
                        $tool_content .= "
                    </tr>";
            }
            $tool_content .= "</tbody></table> <input type='submit' class='btn btn-default' name='bookUsersToAct' value='$langGradebookBooking' /></form>";
        }
        $showGradebookActivities = 0;
    }

    //DISPLAY: list of gradebook activities
    if($showGradebookActivities == 1){

        //check if there is spare weight
        if(weightleft($gradebook_id, 0)){
            $weightLeftMessage = "<div class='alert1'>$langGradebookGradeAlert (" . weightleft($gradebook_id, 0) . "%)</div>";
        } else {
            $weightLeftMessage = "";
        }

        //get all the availiable activities
        $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
        $activityNumber = count($result);

        if ($activityNumber > 0) {
            $tool_content .= "<h3>$langGradebookActList</h3>";
            $tool_content .= $weightLeftMessage;
            $tool_content .= "
                <div class='row'><div class='col-sm-12'><div class='table-responsive'>
                              <table class='table-default'>
                              <tr><th>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langGradebookType</th><th>$langGradebookWeight</th>
                              <th class='text-center'>$langView</th>
                              <th class='text-center'>$langScore</th>
                              <th class='text-center'><i class='fa fa-cogs'></i></th>
                              </tr>";
        }
        else{
            $tool_content .= "<div class='alert alert-warning'>$langGradebookNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookNoActMessage2</a> $langGradebookNoActMessage3</div>";
        }
        if ($result){
            foreach ($result as $announce) {                
                $content = ellipsize_html($announce->description, 50);
                $announce->date = claro_format_locale_date($dateFormatLong, strtotime($announce->date));

                $tool_content .= "
                        <tr><td><b>";

                if (empty($announce->title)) {
                    $tool_content .= "$langGradebookNoTitle<br>";
                    $tool_content .= "<div class='smaller'>";
                    switch ($announce->activity_type) {
                        case 1: $tool_content .= "($gradebook_oral)"; break;
                        case 2: $tool_content .= "($gradebook_labs)"; break;
                        case 3: $tool_content .= "($gradebook_progress)"; break;
                        case 4: $tool_content .= "($gradebook_exams)"; break;
                        case 5: $tool_content .= "($gradebook_other_type)"; break;
                        default : $tool_content .= "";
                    }
                    $tool_content .= "</div>";
                } else {
                    $tool_content .= q($announce->title);
                    $tool_content .= "<div class='smaller'>";
                    switch ($announce->activity_type) {
                        case 1: $tool_content .= "($gradebook_oral)"; break;
                        case 2: $tool_content .= "($gradebook_labs)"; break;
                        case 3: $tool_content .= "($gradebook_progress)"; break;
                        case 4: $tool_content .= "($gradebook_exams)"; break;
                        case 5: $tool_content .= "($gradebook_other_type)"; break;
                        default : $tool_content .= "";
                    }
                    $tool_content .= "</div>";
                }
                $tool_content .= "</b>";
                $tool_content .= "</td>"
                        . "<td><div class='smaller'>" . nice_format($announce->date) . "</div></td>";

                if($announce->module_auto_id) {
                    if($announce->module_auto_type == 1){
                        $tool_content .= "<td class='smaller'>$langGradebookAss";
                    }
                    if($announce->module_auto_type == 2){
                        $tool_content .= "<td class='smaller'>$langExercise ";
                    }
                    if($announce->module_auto_type == 3){
                        $tool_content .= "<td class='smaller'>$langGradebookActivityAct";
                    }

                    if($announce->auto){
                        $tool_content .= "<br>($langGradebookInsAut)";
                    }else{
                        $tool_content .= "<br>($langGradebookInsMan)";
                    }
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td class='smaller'>$langAttendanceActivity</td>";
                }

                $tool_content .= "<td class='text-center'>" . $announce->weight . "%</td>";
                $tool_content .= "<td width='' class='text-center'>";
                if ($announce->visible) {
                    $tool_content .= $langYes;
                } else {
                    $tool_content .= $langNo;
                }
                $tool_content .= "</td>";
                $tool_content .= "<td width='120' class='text-center'>" . userGradebookTotalActivityStats($announce->id, $gradebook_id) . "</td>";
                $tool_content .= "<td class='option-btn-cell text-center'>".
                        action_button(array(
                                    array('title' => $langDelete,
                                        'icon' => 'fa-times',
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$announce->id",
                                        'confirm' => $langConfirmDelete,
                                        'class' => 'delete'),
                                    array('title' => $langModify,
                                        'icon' => 'fa-edit',
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$announce->id"),
                                    array('title' => $langGradebookBook,
                                        'icon' => 'fa-plus',
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ins=$announce->id"))).
                        "</td>";
            } // end of while
        }
        $tool_content .= "</table></div></div></div>";       
    }

} else { //==========Student View==============
    $userID = $uid;
    //visible flag
    $visible = 1;
    //check if there are grade records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id AND gradebook_activities.visible = ?d AND uid = ?d AND gradebook_activities.gradebook_id = ?d", $visible, $userID, $gradebook_id)->count;
    if (!$checkForRecords) {
        $tool_content .="<div class='alert1'>$langGradebookTotalGradeNoInput</div>";
    }

    $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_activities.visible = ?d AND gradebook_id = ?d  ORDER BY `DATE` DESC", $visible, $gradebook_id);
    $announcementNumber = count($result);

    if ($announcementNumber > 0) {
        $tool_content .= "<h4>$langGradebookGrades</h4>";
        $tool_content .= "<div class='info'>$langGradebookTotalGrade: <b>" . userGradeTotal($gradebook_id, $userID) . "</b> </div><br>";

        if(weightleft($gradebook_id, 0) != 0){
            $tool_content .= "<p class='alert1'>$langGradebookAlertToChange</p>";
        }

        $tool_content .= "
                            <table class='table-default' >";
        $tool_content .= "<tr><th>$langTitle</th><th>$langGradebookActivityDate2</th><th>$langGradebookActivityDescription</th><th>$langGradebookActivityWeight</th><th>$langGradebookGrade</th></tr>";
    } else {
        $tool_content .= "<div class='alert-warning'>$langGradebookNoActMessage5</div>";
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

//================================================

//function to help selected option
function typeSelected($type, $optionType){
    if($type == $optionType){
        return "selected";
    }
}


/**
 * @brief calculate the weight left
 * @param type $gradebook_id
 * @param type $currentActivity
 * @return int
 */
function weightleft($gradebook_id, $currentActivity){
    
    if($currentActivity){
        $left = Database::get()->querySingle("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d AND id != ?d", $gradebook_id, $currentActivity)->count;         
    } else {
        $left = Database::get()->querySingle("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id)->count;
    }    
    if($left >= 0 ){
        return 100-$left;
    } else {
        return 0;
    }
    
}

/**
 * @brief return auto grades
 * @param type $userID
 * @param type $exeID
 * @param type $exeType
 * @param type $range
 * @return string
 */
function attendForAutoGrades($userID, $exeID, $exeType, $range) {
    
    if ($exeType == 1) { //asignments: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT grade, max_grade FROM assignment_submit,assignment  WHERE assignment.id = assignment_id AND uid = ?d AND assignment_id = ?d", $userID, $exeID);
       if ($autoAttend) {
           $score = $autoAttend->grade;
           $scoreMax = $autoAttend->max_grade;
           if ($score >= 0) {
                if ($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    } else if($exeType == 2){ //exercises (if there are more than one attemps we take the last)
       $autoAttend = Database::get()->querySingle("SELECT total_score, total_weighting FROM exercise_user_record WHERE uid = ?d AND eid = ?d ORDER BY `record_end_date` DESC LIMIT 1", $userID, $exeID); 
       if ($autoAttend) {
           $score = $autoAttend->total_score;
           $scoreMax = $autoAttend->total_weighting;
           if($score >= 0) {
                if($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    } else if($exeType == 3){ //lps (exes and scorms)
       $autoAttend = Database::get()->querySingle("SELECT raw, scoreMax
               FROM lp_user_module_progress, lp_rel_learnPath_module, lp_module 
               WHERE lp_module.module_id = ?d  
               AND lp_user_module_progress.user_id = ?d 
               AND lp_module.module_id = lp_rel_learnPath_module.module_id
               AND lp_rel_learnPath_module.learnPath_module_id = lp_user_module_progress.learnPath_module_id
               AND (lp_user_module_progress.lesson_status = 'FAILED' OR lp_user_module_progress.lesson_status = 'PASSED' OR lp_user_module_progress.lesson_status = 'COMPLETED')
               ", $exeID, $userID);
       if ($autoAttend) {
           $score = $autoAttend->raw;
           $scoreMax = $autoAttend->scoreMax;
           if($score >= 0){ //to avoid the -1 for no score
                if ($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    }
}



/**
 * @brief get the total grade for a user in a course gradebook
 * @param type $gradebook_id
 * @param type $userID
 * @return string
 */
function userGradeTotal ($gradebook_id, $userID){
    $userGradeTotal = Database::get()->querySingle("SELECT SUM(grade * weight) AS count FROM gradebook_book, gradebook_activities 
                                                WHERE gradebook_book.uid = ?d 
                                                    AND gradebook_book.gradebook_activity_id = gradebook_activities.id 
                                                    AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;

    if ($userGradeTotal) {
        return round($userGradeTotal/100, 2);
    } else {
        return "-";
    }
}


//function to get the total gradebook number 
function userGradebookTotalActivityStats ($activityID, $gradebook_id) {
    
    global $langUsers, $langMeanValue, $langMinValue, $langMaxValue;
    
    $users = Database::get()->querySingle("SELECT SUM(grade) as count, COUNT(gradebook_users.uid) as users FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ", $activityID, $gradebook_id);
    
    $sumGrade = $users->count;
    //this is different than global participants number (it is limited to those that have taken degree)
    $participantsNumber = $users->users;
    

    $q = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade ASC limit 1 ", $activityID, $gradebook_id);
    if ($q) {
        $userGradebookTotalActivityMin = $q->grade;
    }
    $q = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade DESC limit 1 ", $activityID, $gradebook_id);
    if ($q) {
        $userGradebookTotalActivityMax = $q->grade;
    }    
    
//check if participantsNumber is zero
    if ($participantsNumber) {
        $mean = round($sumGrade/$participantsNumber, 2);
        return "<i>$langUsers:</i> $participantsNumber<br>$langMinValue: $userGradebookTotalActivityMin<br> $langMaxValue: $userGradebookTotalActivityMax<br> <i>$langMeanValue:</i> $mean";
    } else {
        return "-";
    }        
}

draw($tool_content, 2, null, $head_content);  