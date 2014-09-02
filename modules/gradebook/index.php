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
require_once 'modules/admin/admin.inc.php';

define('COURSE_USERS_PER_PAGE', 15);

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
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) as count FROM gradebook_users WHERE gradebook_id=?d ", $gradebook_id)->count;    
}else{
    //new gradebook
    $gradebook_id = Database::get()->query("INSERT INTO gradebook SET course_id = ?d ", $course_id)->lastInsertID;
    
    //create gradebook users (default the last six months)
    $limitDate = date('Y-m-d', strtotime(' -6 month'));
    $newUsersQuery = Database::get()->queryArray("SELECT user.id as userID FROM course_user, user, actions_daily
                               WHERE `user`.id = `course_user`.`user_id`
                               AND (`course_user`.reg_date > ?t)
                               AND `course_user`.`course_id` = ?d
                               AND user.status = ?d 
                               GROUP BY actions_daily.user_id", $limitDate, $course_id, USER_STUDENT);

    if ($newUsersQuery) {
        foreach ($newUsersQuery as $newUsers) {
            Database::get()->querySingle("INSERT INTO gradebook_users (gradebook_id, uid) VALUES (?d, ?d)", $gradebook_id, $newUsers->userID);
        }
    }
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) as count FROM gradebook_users WHERE gradebook_id=?d ", $gradebook_id)->count;
}

//==============================================
//tutor view
//==============================================
if ($is_editor) {  

    // Top menu
    $tool_content .= "<div id='operations_container'><ul id='opslist'>";
    if(isset($_GET['editUsers']) || isset($_GET['addActivity']) || isset($_GET['gradebookBook']) || isset($_GET['modify']) || isset($_GET['book']) || isset($_GET['statsGradebook'])){
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langGradebookManagement</a></li>";
    }
    if(!isset($_GET['editUsers'])){
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;editUsers=1'>$langAdminUsers</a></li>";
    }
    if(!isset($_GET['gradebookBook']) && !isset($_GET['book'])) {
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;gradebookBook=1'>$langGradebookBook</a></li>";
    }
    if(!isset($_GET['addActivity'])){
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookAddActivity</a></li>";
    }
    if(!isset($_GET['statsGradebook'])){
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;statsGradebook=1'>$langGradebookStats</a></li>";
    }
    $tool_content .= "</ul></div>";

    //FLAG: flag to show the activities
    $showGradebookActivities = 1;
    
    //EDIT: edit range
    if (isset($_POST['submitGradebookRange'])) {
        $gradebook_range = intval($_POST['degreerange']);
        if($gradebook_range == 10 || $gradebook_range == 100 || $gradebook_range == 5){
            Database::get()->querySingle("UPDATE gradebook SET `range` = ?d WHERE id = ?d ", $gradebook_range, $gradebook_id);
            $message = "<p class='success'>$langGradebookEdit</p>";
            $tool_content .= $message . "<br/>";
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
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <fieldset>
            <legend>$langGradebookActAttend</legend>
            <table class='tbl' width='100%'>";
        
        if (isset($_GET['modify'])) { //edit an existed activity
            $id = intval($_GET['modify']);
            $id  = filter_var($id , FILTER_VALIDATE_INT);
            
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
        }

        if (!isset($contentToModify)) $contentToModify = "";

        @$tool_content .= "
            <tr><th>$langGradebookType:</th></tr>
            <tr>
              <td><select name='activity_type'>
                    <option value=''  " . typeSelected($activity_type, '') . " >-</option>
                    <option value='4' " . typeSelected($activity_type, 4) . " >" . $gradebook_exams . "</option>
                    <option value='2' " . typeSelected($activity_type, 2) . " >" . $gradebook_labs . "</option>
                    <option value='1' " . typeSelected($activity_type, 1) . " >" . $gradebook_oral . "</option>
                    <option value='3' " . typeSelected($activity_type, 3) . " >" . $gradebook_progress . "</option>
                    <option value='5' " . typeSelected($activity_type, 5) . " >" . $gradebook_other_type . "</option>
                  </select>
              </td>
            </tr>

            <tr><th>$langTitle:</th></tr>
            <tr>
              <td><input type='text' name='actTitle' value='$titleToModify' size='50' /></td>
            </tr>
            <tr><th>$langGradebookActivityDate2:</th></tr>
            <tr>
              <td><input type='text' name='date' value='" . datetime_remove_seconds($date) . "'></td>
            </tr>
            <tr><th>$langGradebookActivityWeight:</th></tr>
            <tr>
              <td><input type='text' name='weight' value='$weight' size='5' /> (" . weightleft($gradebook_id, $id) . " % $langGradebookActivityWeightLeft)</td>
            </tr>
            <tr>
                <td><label for='visible'>Ορατό στους μαθητές:</label>
                <input type='checkbox' id='visible' name='visible' value='1'";
                if($visible){
                    $tool_content .= " checked ";
                }
            $tool_content .= "    
                ></td>
            </tr>
            <tr><th>$langGradebookDesc:</th></tr>
            <tr>
              <td>" . rich_text_editor('actDesc', 4, 20, $contentToModify) . "</td>
            </tr>";
        if (isset($module_auto_id)) { //accept the auto booking mechanism
            $tool_content .= "<tr><td>$langGradebookInsAut: <input type='checkbox' value='1' name='auto' ";
            if ($auto) {
                $tool_content .= " checked";
            }
            $tool_content .= "
                /></td>";
        }    
        $tool_content .= "                
                <tr>
                  <td class='right'><input type='submit' name='submitGradebookActivity' value='$langAdd' /></td>
                </tr>
            </table>
            <input type='hidden' name='id' value='$gradebookActivityToModify' />
            </fieldset>
            </form>";
        
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
        
        if (!ctype_alnum($_POST['actTitle'])) {
            $actTitle = $_POST['actTitle'];  
        } else {
            $actTitle = "";
        }
        $actDesc = purify($_POST['actDesc']);
        if (isset($_POST['auto'])) {
            $auto = intval($_POST['auto']);
        }
        $weight = intval($_POST['weight']);
        $type = intval($_POST['activity_type']);
        $actDate = $_POST['date'];
        $visible = $_POST['visible'];
        
        if (($_POST['id'] && $weight>(weightleft($gradebook_id, $_POST['id'])) && $weight != 100) || (!$_POST['id'] && $weight>100)){
            $message = "<p class='alert1'>$langGradebookWeightAlert</p>";
            $tool_content .= $message . "<br/>";
        } else {
            if ($_POST['id']) {
                //update
                $id = intval($_POST['id']);
                Database::get()->query("UPDATE gradebook_activities SET `title` = ?s, date = ?t, description = ?s, `auto` = ?d, `weight` = ?d, `activity_type` = ?d, `visible` = ?d WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $weight, $type, $visible, $id);
                $langAnnDel = "$langGradebookEdit";
                $message = "<p class='success'>$langAnnDel</p>";
                $tool_content .= $message . "<br/>";
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO gradebook_activities SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, weight = ?d, `activity_type` = ?d", $gradebook_id, $actTitle, $actDate, $actDesc, $weight, $type);
                $langAnnDel = "$langGradebookSucInsert";
                $message = "<p class='success'>$langAnnDel</p>";
                $tool_content .= $message . "<br/>";
            }
        }
        //show activities list
        $showGradebookActivities = 1;
    }

    //DELETE DB: delete activity form to gradebook module (plus delete all the marks for alla students for this activity)
    elseif (isset($_GET['delete'])) {
            $delete = intval($_GET['delete']);
            $delAct = Database::get()->query("DELETE FROM gradebook_activities WHERE id = ?d AND gradebook_id = ?d", $delete, $gradebook_id)->affectedRows;
            $delActBooks = Database::get()->query("DELETE FROM gradebook_book WHERE gradebook_activity_id = ?d", $delete)->affectedRows;
            $showGradebookActivities = 1; //show list activities
            if($delAct){
                $langAnnDel = $langGradebookDel;
                $message = "<p class='success'>$langAnnDel</p>";
            }else{
                $langAnnDel = $langGradebookDelFailure;
                $message = "<p class='alert1'>$langAnnDel</p>";
            }
            $tool_content .= $message . "<br/>";
        }

    //DISPLAY: stats -> means for all the activities
    elseif(isset($_GET['statsGradebook'])) {
                        
        //get all the activities
        $result = Database::get()->queryArray("SELECT * FROM gradebook_activities WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
        $actNumber = count($result);

        if ($actNumber > 0) {
            $tool_content .= "<fieldset><legend>" . $langGradebookStats . "</legend>";
            @$tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&book=$userID' onsubmit=\"return checkrequired(this, 'antitle');\">
                            <table width='100%' class='sortable' id='t2'>";
            $tool_content .= "<tr><th colspan='2'>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langGradebookActivityDescription</th><th>$langGradebookType</th><th>$langGradebookWeight</th>";
            $tool_content .= "<th width='10' class='center'>$langGradebookMEANS</th>";
            $tool_content .= "<th width='10' class='center'>Ορατό</th>";
            $tool_content .= "</tr>";
        } else {
            $tool_content .= "<p class='alert1'>$langGradebookNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookNoActMessage2</a> $langGradebookNoActMessage3</p>\n";
        }
        //ui counter 
        $k = 0;
        if ($result) {
            foreach ($result as $activity) {                
                //check if there is auto mechanism
                if ($activity->auto == 1) {                   
                    //check for autograde (if there is already a record do not propose the auto grade)
                    //if there is not get the grade from the book table
                    //$checkForAuto = Database::get()->querySingle("SELECT id FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    $checkForAuto = Database::get()->querySingle("SELECT id FROM gradebook_book WHERE gradebook_activity_id = ?d", $activity->id);
                    if ($activity->module_auto_type && !$checkForAuto) { //assignments, exercises, lp(scroms)
                        $userGrade = attendForAutoGrades($userID, $activity->module_auto_id, $activity->module_auto_type, $gradebook_range);
                    } else {
                     //   $userGrade = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID)->grade;
                        $userGrade = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d", $activity->id)->grade;
                    }
                } else {
                    //$userGrade = Database::get()->querySingle("SELECT grade FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID)->grade;
                    $userGrade = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d", $activity->id)->grade;
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
                if ($activity->date) {
                    $tool_content .= "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>";
                } else {
                    $tool_content .= "<td>-</td>";
                }
                $tool_content .= "<td>" . $content . "</td>";

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
                $tool_content .= "<td width='' class='center'>" . $activity->weight . "%</td>";
                
                $tool_content .= "
                <td width='70' class='center'>" . userGradebookTotalActivityStats($activity->id, $gradebook_id) . "</td>";
                
                $tool_content .= "<td width='' class='center'>" ;
                if($activity->visible){
                    $tool_content .= $langYes;
                }else{
                    $tool_content .= $langNo;
                }
                $tool_content .= "</td>";
                $k++;
            } // end of while
        }
        
        $tool_content .= "<tr><td colspan=7 class='smaller'>" . $langGradebookUpToDegree . $gradebook_range . "</td></tr></table></form></fieldset>";
        
        $showGradebookActivities = 0;
    }    
        
    //DISPLAY: list of users and form for each user
    elseif(isset($_GET['gradebookBook']) || isset($_GET['book'])){
        
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
                $message = "<p class='success'>$langGradebookEdit</p>";
                $tool_content .= $message . "<br/>";
            }
        }

        //View acivities for a user - (check for auto mechanism) 
        if(isset($_GET['book'])){
            
            if(weightleft($gradebook_id, 0) == 0){
                $userID = intval($_GET['book']); //user

                //check if there are booking records for the user, otherwise alert message for first input
                $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id AND uid = ?d AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;
                if(!$checkForRecords){
                    $tool_content .="<div class='alert1'>$langGradebookNewUser</div>";
                }

                //get all the activities
                $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
                $actNumber = count($result);

                if ($actNumber > 0) {
                    $tool_content .= "<fieldset><legend>" . display_user($userID) . "</legend>";
                    $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                                        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&book=$userID' onsubmit=\"return checkrequired(this, 'antitle');\">
                                      <table width='100%' class='sortable' id='t2'>";
                    $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langGradebookActivityDescription</th><th>$langGradebookType</th><th>$langGradebookWeight</th>";
                    $tool_content .= "<th width='10'  class='center'>$langGradebookBooking</th>";
                    $tool_content .= "</tr>";
                } else {
                    $tool_content .= "<p class='alert1'>$langGradebookNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookNoActMessage2</a> $langGradebookNoActMessage3</p>\n";
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
                                $userGrade = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID)->grade;
                            }
                        } else {
                            $userGrade = Database::get()->querySingle("SELECT grade FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $activity->id, $userID)->grade;
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
                        }else{
                            $tool_content .= "<td>-</td>";
                        }
                        $tool_content .= "<td>" . $content . "</td>";
                        
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
                        $tool_content .= "<td width='' class='center'>" . $activity->weight . "%</td>";
                        $tool_content .= "
                    <td  class='center'>
                        <input style='width:30px' type='text' value='" . $userGrade . "' name='" . $activity->id . "'"; //SOS 4 the UI!!

                        $tool_content .= ">
                        <input type='hidden' value='" . $userID . "' name='userID'>    
                    </td>

                    ";
                        $k++;
                    } // end of while
                }
                $tool_content .= "<tr><td colspan=7 class='right'><input type='submit' name='bookUser' value='$langGradebookBooking' /></td></tr>";
                
                $tool_content .= "<tr><td colspan=7 >". $langGradebookGrade . ":" . userGradeTotal($gradebook_id, $userID);
                
                if(userGradeTotal($gradebook_id, $userID) > $gradebook_range){
                    $tool_content .= "<br>" . $langGradebookOutRange;
                }
                
                $tool_content .= "</td></tr>";
                $tool_content .= "<tr><td colspan=7 class='smaller'>" . $langGradebookUpToDegree . $gradebook_range . "</td></tr></table></form></fieldset>";

            }else{
            $tool_content .="<div class='alert1'>$langGradeNoBookAlert " . weightleft($gradebook_id, 0) . "%</div>";
            }
        }

        //========================
        //show all the students
        //========================
        
        
        $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID, gradebook_users.uid as userID, user.surname as surname, user.givenname as name, user.am as am, course_user.reg_date as reg_date   FROM gradebook_users, user, course_user  WHERE gradebook_id = ?d AND gradebook_users.uid = user.id AND `user`.id = `course_user`.`user_id` AND `course_user`.`course_id` = ?d ", $gradebook_id, $course_id);

        if ($resultUsers) {
            //table to display the users
            $tool_content .= "
            <table width='100%' id='users_table{$course_id}' class='tbl_alt custom_list_order'>
                <thead>
                    <tr>
                      <th width='1'>$langID</th>
                      <th><div align='left' width='100'>$langName $langSurname</div></th>
                      <th class='center' width='80'>$langRegistrationDateShort</th>
                      <th class='center'>$langGradebookGrade</th>
                      <th class='center'>$langActions</th>
                    </tr>
                </thead>
                <tbody>";

            $cnt = 0;
            foreach ($resultUsers as $resultUser) {
                $cnt++;
                $tool_content .= "
                    <tr>
                        <td>$cnt</td>
                        <td> " . display_user($resultUser->userID). " ($langAm: $resultUser->am)</td>
                        <td>" . nice_format($resultUser->reg_date) . "</td>
                        <td>";
                            if(weightleft($gradebook_id, 0) == 0) {
                                $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID);
                            } elseif (userGradeTotal($gradebook_id, $resultUser->userID) != "-") { //alert message only when grades have been submitted
                                $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID) . "<div class='alert1'>" . $langGradebookGradeAlert . "</div>";
                            }
                            if (userGradeTotal($gradebook_id, $resultUser->userID) > $gradebook_range) {
                                $tool_content .= "<br><div class='smaller'>" . $langGradebookOutRange . "</div>";
                            }
                $tool_content .=
                        "</td>    
                        <td class='center'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;book=$resultUser->userID'>$langGradebookBook</a></td>
                    </tr>";
            }

            $tool_content .= "
                </tbody>
            </table>";

        }
        
        /*
        $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 0;
        
        //Count only students base on their initial record (not the course)
        $countUser = $participantsNumber;
        
        $limit_sql = '';

        // display navigation links if users > COURSE_USERS_PER_PAGE
        if ($countUser > COURSE_USERS_PER_PAGE and !isset($_GET['all'])) {
            $limit_sql = "LIMIT $limit, " . COURSE_USERS_PER_PAGE;
            $search_params = "&course=".$course_code."&gradebookBook=".$gradebook_id;
            $tool_content .= show_paging($limit, COURSE_USERS_PER_PAGE, $countUser, $_SERVER['SCRIPT_NAME'], $search_params, TRUE);
        }

        if (isset($_GET['all'])) {
            $extra_link = '&amp;all=true';
        } else {
            $extra_link = '&amp;limit=' . $limit;
        }
        

        $tool_content .= "
    <table width='100%' class='tbl_alt custom_list_order'>
    <tr>
      <th width='1'>$langID</th>
      <th><div align='left'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ord=s$extra_link'>$langSurnameName</a></div></th>
      <th class='center' width='90'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ord=rd$extra_link'>$langRegistrationDateShort</a></th>
      <th class='center'>$langRole</th>
      <th class='center'>$langGradebookGrade</th>
      <th class='center'>$langActions</th>
    </tr>";


    // Numerating the items in the list to show: starts at 1 and not 0
        $i = $limit + 1;
        $ord = isset($_GET['ord']) ? $_GET['ord'] : '';

        switch ($ord) {
            case 's': $order_sql = 'ORDER BY surname';
                break;
            case 'rd': $order_sql = 'ORDER BY course_user.reg_date DESC';
                break;
            default: $order_sql = 'ORDER BY user.status, editor DESC, tutor DESC, surname, givenname';
                break;
        }

        DataBase::get()->queryFunc("SELECT user.id as userID, user.surname , user.givenname, user.email,
                               user.am, user.has_icon, course_user.status as courseUserStatus,
                               course_user.tutor, course_user.editor, course_user.reviewer, course_user.reg_date
                               
                               FROM course_user, user, actions_daily
                               WHERE `user`.id = `course_user`.`user_id`
                               AND `user`.id = actions_daily.user_id
                               AND actions_daily.day > ?t
                               AND `course_user`.`course_id` = ?d
                               AND user.status = ?d 
                               GROUP BY actions_daily.user_id 
                               $order_sql $limit_sql", function($myrow) use(&$tool_content, $course_id, &$i, $langAm,$course_code, $userGradeTotal, $gradebook_id, $langGradebookBook, $langGradebookGradeAlert, $gradebook_range, $langGradebookOutRange) {

            // bi colored table
            if ($i % 2 == 0) {
                $tool_content .= "<tr class='odd'>";
            } else {
                $tool_content .= "<tr class='even'>";
            }
            // show public list of users
            $am_message = empty($myrow->am) ? '' : ("<div class='right'>($langAm: " . q($myrow->am) . ")</div>");
            $tool_content .= "
                <td class='smaller right'>$i.</td>\n" .
                    "<td class='smaller'>"
                    . display_user($myrow->userID) 
                    . "&nbsp;&nbsp;(" . mailto($myrow->email) . ")  $am_message</td>\n";
            $tool_content .= "\n" .
                    "\n" .
                    "<td class='smaller center'>";
            if ($myrow->reg_date == '0000-00-00') {
                $tool_content .= $langUnknownDate;
            } else {
                $tool_content .= nice_format($myrow->reg_date);
            }
            $tool_content .= "</td>";
            $tool_content .= "<td class='center' width='30'>";

            // tutor right
            if ($myrow->tutor == '1') {
                $tool_content .= "tutor - ";
            }
            // editor right
            if ($myrow->editor == '1') {
                $tool_content .= "editor";
            }

            $tool_content .= "</td>";

            $tool_content .= "<td class='center'>";
            if(weightleft($gradebook_id, 0) == 0){
                $tool_content .= userGradeTotal($gradebook_id, $myrow->userID);
            }elseif(userGradeTotal($gradebook_id, $myrow->userID) != "-"){ //alert message only when grades have been submitted
                $tool_content .= userGradeTotal($gradebook_id, $myrow->userID)."<div class='alert1'>".$langGradebookGradeAlert."</div>";
            }
            if(userGradeTotal($gradebook_id, $myrow->userID) > $gradebook_range){
                $tool_content .= "<br><div class='smaller'>" . $langGradebookOutRange . "</div>"; 
            }
            
            $tool_content .= "</td>";
            $tool_content .= "<td class='center'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;book=$myrow->userID'>$langGradebookBook</a></td>";

            $i++;
        }, $limitDate, $course_id, USER_STUDENT, $order_sql);

        $tool_content .= "</table>";

        // display number of users
        $tool_content .= "
    <div class='info'><b>$langTotal</b>: <span class='grey'><b>$countUser </b><em>$langStudents &nbsp;</em></span><br />
      <b>$langDumpUser $langCsv</b>: 1. <a href='dumpuser.php?course=$course_code&gradebook=$gradebook_id'>$langcsvenc2</a>
           2. <a href='dumpuser.php?course=$course_code&gradebook=$gradebook_id&amp;enc=1253'>$langcsvenc1</a>
      </div>";
        //========================
         
        */
        
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
            $newUsersQuery = Database::get()->queryArray("SELECT user.id as userID FROM course_user, user, actions_daily
                               WHERE `user`.id = `course_user`.`user_id`
                               AND (`course_user`.reg_date > ?t)
                               AND `course_user`.`course_id` = ?d
                               AND user.status = ?d 
                               GROUP BY user.id", $limitDate, $course_id, USER_STUDENT);

            if ($newUsersQuery) {
                foreach ($newUsersQuery as $newUsers) {
                    Database::get()->querySingle("INSERT INTO gradebook_users (gradebook_id, uid) VALUES (?d, ?d)", $gradebook_id, $newUsers->userID);
                }
            } else {
                $tool_content .= "<div class='alert1'>Δεν υπάρχουν φοιτητές στο διάστημα που επιλέξατε</div>";
            }
        }


        //section to reset the gradebook users list
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">
            <fieldset>
            <h3>Ανανέωση της λίστας μαθητών (reset)</h3>
            <select name='usersLimit'>
                <option value=''>$langChoice</option>
                <option value='1'>$langAttendanceActiveUsersSemester</option>
                <option value='2'>Φοιτητές μόνο τελευταίου τριμήνου</option>
                <option value='3'>Όλοι οι εγγεγραμμένοι φοιτητές</option>
            </select>
            <input type='submit' name='resetAttendance' value='$langAttendanceUpdate'>
            </fieldset>
        </form>";


        //gradebook users
        $tool_content .= "<h3>Μαθητές βαθμολογίου</h3><br><form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">";

        $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID, gradebook_users.uid, user.surname as surname, user.givenname as name, user.am as am, course_user.reg_date as reg_date   FROM gradebook_users, user, course_user  WHERE gradebook_id = ?d AND gradebook_users.uid = user.id AND `user`.id = `course_user`.`user_id` AND `course_user`.`course_id` = ?d ", $gradebook_id, $course_id);

        if ($resultUsers) {
            //table to display the users
            $tool_content .= "
            <table width='100%' id='users_table{$course_id}' class='tbl_alt custom_list_order'>
                <thead>
                    <tr>
                      <th width='1'>$langID</th>
                      <th><div align='left' width='100'>$langName $langSurname</div></th>
                      <th class='center' width='80'>$langRegistrationDateShort</th>
                      <th class='center' width='100'>$langSelect</th>
                    </tr>
                </thead>
                <tbody>";

            $cnt = 0;
            foreach ($resultUsers as $resultUser) {
                $cnt++;
                $tool_content .= "
                    <tr>
                        <td>$cnt</td>
                        <td>$resultUser->name $resultUser->surname ($langAm: $resultUser->am)</td>
                        <td>" . nice_format($resultUser->reg_date) . "</td>
                        <td class='center'><input type='checkbox' name='recID[]' value='$resultUser->recID'></td>
                    </tr>";
            }

            $tool_content .= "
                </tbody>
            </table>";

            $tool_content .= "<input type='Submit' name='deleteSelectedUsers' value='Διαγραφή επιλεγμένων'>";

            $tool_content .= "</form>";
        } else {
            $tool_content .= "<div class='alert1'>Δεν υπάρχουν μαθητές στο παρουσιολόγιο</div>";
        }


        //do not show activities list 
        $showGradebookActivities = 0;
    }

    //DISPLAY: list of gradebook activities
    if($showGradebookActivities == 1){
        
        //check if there is spare weight
        if(weightleft($gradebook_id, 0)){
            $weightLeftMessage = "<div class='alert1'>$langGradebookGradeAlert (" . weightleft($gradebook_id, 0) . "%)</div>";
        }
        else{
            $weightLeftMessage = "";
        }
        
        //get all the availiable activities
        $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
        $activityNumber = count($result);

        if ($activityNumber > 0) {
            $tool_content .= "<fieldset><legend>$langGradebookActList</legend>";
            $tool_content .= $weightLeftMessage;
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t2'>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langGradebookDesc</th><th>$langGradebookType</th><th>$langGradebookWeight</th>";
            $tool_content .= "<th width='60' class='center'>Ορατό</th>";
            $tool_content .= "<th width='60' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
        }
        else{
            $tool_content .= "<p class='alert1'>$langGradebookNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langGradebookNoActMessage2</a> $langGradebookNoActMessage3</p>\n";
        }
        $k = 0;
        if ($result){
            foreach ($result as $announce) {
                $content = standard_text_escape($announce->description);
                $announce->date = claro_format_locale_date($dateFormatLong, strtotime($announce->date));

                if ($k % 2 == 0) {
                    $tool_content .= "<tr class='even'>";
                } else {
                    $tool_content .= "<tr class='odd'>";
                }

                $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td><b>";

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
                        . "<td><div class='smaller'>" . nice_format($announce->date) . "</div></td>"
                        . "<td>" . $content . "</td>";

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
                    $tool_content .= "<td class='smaller'>$langGradebookActAttend</td>";
                }
                
                $tool_content .= "<td class='center'>" . $announce->weight . "%</td>";
                $tool_content .= "<td width='' class='center'>";
                    if ($announce->visible) {
                        $tool_content .= $langYes;
                    } else {
                        $tool_content .= $langNo;
                    }
                $tool_content .= "</td>";
                
                $tool_content .= "
                <td width='70' class='right'>
                      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$announce->id'>
                      <img src='$themeimg/edit.png' title='" . $langModify . "' /></a>&nbsp;
                      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$announce->id' onClick=\"return confirmation('$langGradebookDeleteAlert');\">
                      <img src='$themeimg/delete.png' title='" . $langDelete . "' /></a>&nbsp;</td>";
                $k++;
            } // end of while
        }
        $tool_content .= "</table></fieldset>";

        //==============================================
        //Course activities available for the gradebook
        //==============================================
        
        //Assignments
        $checkForAss = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 1)", $course_id);

        $checkForAssNumber = count($checkForAss);
        
        $tool_content .= "<br><br>";

        if ($checkForAssNumber > 0) {
            $tool_content .= "<fieldset><legend>$langGradebookActToAddAss</legend>";
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t1'>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langDescription</th>";
            $tool_content .= "<th width='60' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
        }
        else{
            $tool_content .= "<p class='alert1'>$langGradebookNoActMessageAss4</p>\n";
        }

        $k = 0;
        if ($checkForAss){
            foreach ($checkForAss as $newAssToGradebook) {
                $content = standard_text_escape($newAssToGradebook->description);
                
                if($newAssToGradebook->assign_to_specific){
                    $content .= "($langGradebookAssignSpecific)<br>";
                    $checkForAssSpec = Database::get()->queryArray("SELECT user_id, user.surname , user.givenname FROM `assignment_to_specific`, user WHERE user_id = user.id AND assignment_id = ?d", $newAssToGradebook->id);
                    foreach ($checkForAssSpec as $checkForAssSpecR) {
                        $content .= $checkForAssSpecR->surname. " " . $checkForAssSpecR->givenname . "<br>";
                    }
                }
                
                $d = strtotime($newAssToGradebook->deadline);

                if ($k % 2 == 0) {
                    $tool_content .= "<tr class='even'>";
                } else {
                    $tool_content .= "<tr class='odd'>";
                }

                $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td><b>";

                if (empty($newAssToGradebook->title)) {
                    $tool_content .= $langAnnouncementNoTille;
                } else {
                    $tool_content .= q($newAssToGradebook->title);
                }
                $tool_content .= "</b>";
                $tool_content .= "</td>"
                        . "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>"
                        . "<td>" . $content . "</td>";
                $tool_content .= "<td width='70' class='center'>".icon('add', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newAssToGradebook->id&amp;type=1")."&nbsp;";
                $k++;
            } // end of while
        }
        $tool_content .= "</table></fieldset>";


        //Exercises
        $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d AND exercise.active = 1 AND exercise.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 2)", $course_id);

        $checkForExerNumber = count($checkForExer);

        $tool_content .= "<br><br>";

        if ($checkForExerNumber > 0) {
            $tool_content .= "<fieldset><legend>$langGradebookActToAddExe</legend>";
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t1'>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langGradebookActivityDate2</th><th>Περιγραφή</th>";
            $tool_content .= "<th width='60' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
        } else {
            $tool_content .= "<p class='alert1'>$langGradebookNoActMessageExe4</p>\n";
        }

        $k = 0;
        if ($checkForExer) {
            foreach ($checkForExer as $newExerToGradebook) {
                $content = standard_text_escape($newExerToGradebook->description);
                
                $d = strtotime($newExerToGradebook->end_date);

                if ($k % 2 == 0) {
                    $tool_content .= "<tr class='even'>";
                } else {
                    $tool_content .= "<tr class='odd'>";
                }

                $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
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

                $tool_content .= "<td width='70' class='center'>".icon('add', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newExerToGradebook->id&amp;type=2")."&nbsp;";
                $k++;
            } // end of while
        }
        $tool_content .= "</table></fieldset>";
        
        
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

        $tool_content .= "<br><br>";

        if ($checkForLpNumber > 0) {
            $tool_content .= "<fieldset><legend>$langGradebookActToAddLp</legend>";
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t1'>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th>$langLearningPath</th><th>$langGradebookType</th>";
            $tool_content .= "<th class='center'>$langActions</th>";
            $tool_content .= "</tr>";
        } else {
            $tool_content .= "<p class='alert1'>$langGradebookNoActMessageExe4</p>\n";
        }

        $k = 0;
        if ($checkForLp) {
            foreach ($checkForLp as $newExerToGradebook) {
                
                if ($k % 2 == 0) {
                    $tool_content .= "<tr class='even'>";
                } else {
                    $tool_content .= "<tr class='odd'>";
                }
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
                $tool_content .= "<td width='70' class='center'>".icon('add', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newExerToGradebook->module_id&amp;type=3")."&nbsp;";
                /*      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newExerToGradebook->module_id&amp;type=3'>
                      $langAdd</a>&nbsp;"; */

                $k++;
            } // end of while
        }
        $tool_content .= "</table></fieldset>";
        
        
        //==============================================
        //show active users limit
        //==============================================
        /*
        $tool_content .= "<br>
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
            <fieldset>
            <legend>$langGradebookActiveUsers</legend>
            <table class='tbl' width='40%'>
                <tr>
                  <th>$langGradebookActiveUsersSemester:</th><td><input type='checkbox' name='usersLimit' value=1";
        if ($showSemesterParticipants) {
            $tool_content .= " checked";
        }

        $tool_content .= " /></td>
                </tr>
                <tr>
                  <td class='left'><input type='submit' name='submitGradebookActiveUsers' value='$langGradebookUpdate' /></td>
                </tr>
            </table>
            </fieldset>
            </form>";
        */
        //==============================================
        //show degree range
        //==============================================
        $tool_content .= "<br>
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
            <fieldset>
            <legend>$langGradebookRange</legend>
            <table class='tbl' width='40%'>
                <tr>
                  <th>
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
        
        $tool_content .= "</td>
                </tr>
                <tr>
                  <td class='left'><input type='submit' name='submitGradebookRange' value='$langGradebookUpdate' /></td>
                </tr>
            </table>
            </fieldset>
            </form>";
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
        $tool_content .= "<fieldset><legend>$langGradebookGrades</legend>";
        $tool_content .= "<div class='center'>$langGradebookTotalGrade: " . userGradeTotal($gradebook_id, $userID) . " </div><br>";
        
        if(weightleft($gradebook_id, 0) != 0){
            $tool_content .= "<p class='alert1'>$langGradebookAlertToChange</p>";
        }
        
        
        $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                            <table width='100%' class='sortable' id='t2'>";
        $tool_content .= "<tr><th  colspan='2'>Τίτλος</th><th >$langGradebookActivityDate2</th><th>$langGradebookActivityDescription</th><th>$langGradebookActivityWeight</th><th>$langGradebookGrade</th></tr>";
    } else {
        $tool_content .= "<p class='alert1'>$langGradebookNoActMessage5</p>";
    }
    $k = 0;

    if ($result) {
        foreach ($result as $announce) {            
            //check if the user has attend for this activity
            $userAttend = Database::get()->querySingle("SELECT grade FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $announce->id, $userID)->grade;

            $content = standard_text_escape($announce->description);
            $announce->date = claro_format_locale_date($dateFormatLong, strtotime($announce->date));

            if ($k % 2 == 0) {
                $tool_content .= "<tr class='even'>";
            } else {
                $tool_content .= "<tr class='odd'>";
            }

            $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td><b>";

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

            $tool_content .= "<td width='70' class='center'>";
                    
            if ($userAttend) {
                $tool_content .= $userAttend;
            } else {
                $tool_content .= $langGradebookAlertNoInput;
            }
            $tool_content .= "</td>";            
            $k++;
        } // end of while
    }
    $tool_content .= "</table></fieldset>";
}

//================================================

//function to help selected option
function typeSelected($type, $optionType){
    if($type == $optionType){
        return "selected";
    }
}

//function to calculate the weight left
function weightleft($gradebook_id, $currentActivity){
    if($currentActivity){
        $left = Database::get()->querySingle("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d AND id != ?d", $gradebook_id, $currentActivity)->count;
    }else{
        $left = Database::get()->querySingle("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id)->count;
    }
    if($left > 0 ){
        return 100-$left;
    }else{
        return 0;
    }
    
}


//function to return auto grades
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


//function to get the total grade for a user in a course gradebook
function userGradeTotal ($gradebook_id, $userID){

    $userGradeTotal = Database::get()->querySingle("SELECT SUM(grade * weight) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.uid = ?d AND  gradebook_book.gradebook_activity_id = gradebook_activities.id AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;

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
    

    $userGradebookTotalActivityMin = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade ASC limit 1 ", $activityID, $gradebook_id)->grade;

    $userGradebookTotalActivityMax = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade DESC limit 1 ", $activityID, $gradebook_id)->grade;
    
//check if participantsNumber is zero
    if ($participantsNumber) {
        $mean = round($sumGrade/$participantsNumber, 2);
        return "<i>$langUsers:</i> $participantsNumber<br>$langMinValue: $userGradebookTotalActivityMin<br> $langMaxValue: $userGradebookTotalActivityMax<br> <i>$langMeanValue:</i> $mean";
    } else {
        return "-";
    }        
}

draw($tool_content, 2, null, $head_content);




  