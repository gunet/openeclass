<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/**
 * @file exercise_admin.inc.php
 * @brief Create new exercise or modify an existing one
 */
require_once 'modules/search/exerciseindexer.class.php';

// the exercise form has been submitted
if (isset($_POST['submitExercise'])) {
    
    $exerciseTitle = trim($exerciseTitle);
    $exerciseDescription = purify($exerciseDescription);
    $randomQuestions = (isset($_POST['questionDrawn'])) ? intval($_POST['questionDrawn']) : 0;

    // no title given
    if (empty($exerciseTitle)) {
        $msgErr = $langGiveExerciseName;
    } else {
        if ((!is_numeric($exerciseTimeConstraint)) or (!is_numeric($exerciseAttemptsAllowed))) {
            $msgErr = $langGiveExerciseInts;
        } else {
            
            $objExercise->updateTitle($exerciseTitle);
            $objExercise->updateDescription($exerciseDescription);
            $objExercise->updateType($exerciseType);
            $startDateTime_obj = DateTime::createFromFormat('d-m-Y H:i',$exerciseStartDate);
            $objExercise->updateStartDate($startDateTime_obj->format('Y-m-d H:i:s'));
            $endDateTime_obj = DateTime::createFromFormat('d-m-Y H:i',$exerciseEndDate);
            $objExercise->updateEndDate($endDateTime_obj->format('Y-m-d H:i:s'));
            $objExercise->updateTempSave($exerciseTempSave);
            $objExercise->updateTimeConstraint($exerciseTimeConstraint);
            $objExercise->updateAttemptsAllowed($exerciseAttemptsAllowed);
            $objExercise->setRandom($randomQuestions);
            $objExercise->updateResults($dispresults);
            $objExercise->updateScore($dispscore);
            $objExercise->save();
            // reads the exercise ID (only useful for a new exercise)
            $exerciseId = $objExercise->selectId();
            $eidx = new ExerciseIndexer();
            $eidx->store($exerciseId);
            redirect_to_home_page('modules/exercise/admin.php?course='.$course_code.'&exerciseId='.$exerciseId);
        }
    }
} else {
    $exerciseId = $objExercise->selectId();
    $exerciseTitle = $objExercise->selectTitle();
    $exerciseDescription = $objExercise->selectDescription();
    $exerciseType = $objExercise->selectType();
    $startDateTime_obj = DateTime::createFromFormat('Y-m-d H:i:s', $objExercise->selectStartDate());
    $exerciseStartDate = $startDateTime_obj->format('d-m-Y H:i');
    $exerciseEndDate = $objExercise->selectEndDate();
    if ($exerciseEndDate == '') {
        $endDateTime_obj = new DateTime;
        $endDateTime_obj->add(new DateInterval('P1Y'));
        $exerciseEndDate = $endDateTime_obj->format('d-m-Y H:i');
    } else {
        $endDateTime_obj = DateTime::createFromFormat('Y-m-d H:i:s', $objExercise->selectEndDate());
        $exerciseEndDate = $endDateTime_obj->format('d-m-Y H:i'); 
    }
    $exerciseTempSave = $objExercise->selectTempSave();
    $exerciseTimeConstraint = $objExercise->selectTimeConstraint();
    $exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
    $randomQuestions = $objExercise->isRandom();
    $displayResults = $objExercise->selectResults();
    $displayScore = $objExercise->selectScore();
}

// shows the form to modify the exercise
if (isset($_GET['modifyExercise']) or isset($_GET['NewExercise'])) {
    load_js('bootstrap-datetimepicker');
    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#startdatepicker, #enddatepicker').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', 
                pickerPosition: 'bottom-left', 
                language: '".$language."',
                autoclose: true    
            });
        });
    </script>";
    if (isset($_GET['modifyExercise'])) {
        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId'>";
    } else {
        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;NewExercise=Yes'>";
    }
    @$tool_content .="
	<fieldset>
        <legend>$langInfoExercise </legend>
	<table width='99%' class='tbl'>";
    if (!empty($msgErr)) {
        $tool_content .= "<tr><td colspan='2'><p class='caution'>$msgErr</td></tr>";
    }
    $tool_content .= "
        <tr>
        <th width='180'>" . $langExerciseName .":</th>
        <td><input type='text' name='exerciseTitle' " . "size='50' maxlength='200' value='" . q($exerciseTitle) . "' style='width:400px;'></td>
        </tr>
        <tr>
        <th>" . $langExerciseDescription . ":</th>
        <td>" . rich_text_editor('exerciseDescription', 4, 30, $exerciseDescription, "style='width:400px;' class='FormData_InputText'") . "</td>
        </tr>
        <tr>
        <th>" . $langExerciseType . ":</th>
        <td>" . "<input type='radio' name='exerciseType' value='1'";

    if ($exerciseType <= 1) {
        $tool_content .= " checked='checked'";
    }
    $tool_content .= "> " . $langSimpleExercise . "
	  <br />
	  <input type='radio' name='exerciseType' value='2'";

    if ($exerciseType >= 2) {
        $tool_content .= 'checked="checked"';
    }
    $tool_content .= "> " . $langSequentialExercise . "</td>
	</tr>";


    $start_cal_Excercise = "<div class='input-append date form-group' id='startdatepicker' data-date='$exerciseStartDate' data-date-format='dd-mm-yyyy'>
        <div class='col-xs-11'>        
            <input name='exerciseStartDate' type='text' value='$exerciseStartDate'>
        </div>
        <span class='add-on'><i class='fa fa-times'></i></span>
        <span class='add-on'><i class='fa fa-calendar'></i></span>
    </div>";

    $end_cal_Excercise = "<div class='input-append date form-group' id='enddatepicker' data-date='$exerciseStartDate' data-date-format='dd-mm-yyyy'>
        <div class='col-xs-11'>        
            <input name='exerciseEndDate' type='text' value='$exerciseEndDate'>
        </div>
        <span class='add-on'><i class='fa fa-times'></i></span>
        <span class='add-on'><i class='fa fa-calendar'></i></span>
    </div>";
    
    $tool_content .= "
        <tr>
          <th>" . $langExerciseStart . ":</th>
	  <td>$start_cal_Excercise</td>
        </tr>
        <tr>
	  <th>" . $langExerciseEnd . ":</th>
	  <td>$end_cal_Excercise</td>
	</tr>
        <tr>
            <th>$langTemporarySave:</th>
            <td>
                <input type='radio' name='exerciseTempSave' value='0' ".(($exerciseTempSave==0)?'checked':'')."> $langDeactivate <br>
                <input type='radio' name='exerciseTempSave' value='1' ".(($exerciseTempSave==1)?'checked':'')."> $langActivate
            </td>
        </tr>
	<tr>
	  <th>" . $langExerciseConstrain . ":</th>
	  <td><input type=\"text\" name=\"exerciseTimeConstraint\" size=\"3\" maxlength=\"3\" " .
            "value=\"" . htmlspecialchars($exerciseTimeConstraint) . "\">&nbsp;&nbsp;" .
            $langExerciseConstrainUnit . " &nbsp;&nbsp;&nbsp;&nbsp;(" . $langExerciseConstrainExplanation . ")</td>
	</tr>
	<tr>
	  <th>" . $langExerciseAttemptsAllowed . ":</th>
	  <td><input type='text' name='exerciseAttemptsAllowed' size='3' maxlength='2'" .
            "value=\"" . htmlspecialchars($exerciseAttemptsAllowed) . "\">&nbsp;&nbsp;" .
            $langExerciseAttemptsAllowedUnit . " &nbsp;&nbsp;&nbsp;(" . $langExerciseAttemptsAllowedExplanation . ")</td>
	</tr>";

    // Random Questions
    $tool_content .= "<tr><th>" . $langRandomQuestions . ":</th>" .
            "<td>" . $langSelection . "&nbsp;" .
            "<input type='text' name='questionDrawn' size='2' value='" . $randomQuestions . "' />&nbsp;" .
            $langFromRandomQuestions . "</td></tr>";


    if (isset($displayResults) and $displayResults == 1) {
        $extra = 'checked';
        $extra2 = '';
    } else {
        $extra = '';
        $extra2 = 'checked';
    }
    if (isset($displayScore) and $displayScore == 1) {
        $extras = 'checked';
        $extras2 = '';
    } else {
        $extras = '';
        $extras2 = 'checked';
    }

    $tool_content .= "
        <tr>
	  <th>" . $langAnswers . ":</th>" . "
	  <td><input type='radio' name='dispresults' value='1'" . $extra . ">&nbsp;$langAnswersDisp
	  <br /><input type='radio' name='dispresults' value='0'" . $extra2 . ">&nbsp;$langAnswersNotDisp
	  </td>
	</tr>
	<tr>
	  <th>" . $langScore . ":</th>" . "
	  <td><input type='radio' name='dispscore' value='1'" . $extras . ">&nbsp;$langScoreDisp
	  <br /><input type='radio' name='dispscore' value='0'" . $extras2 . ">&nbsp;$langScoreNotDisp
	  </td>
	</tr>
	<tr>
          <th>&nbsp;</th>";
    if (isset($_GET['NewExercise'])) {
        $tool_content .= "<td><input type='submit' name='submitExercise' value='$langCreate'>&nbsp;&nbsp;";
    } else {
        $tool_content .= "<td><input type='submit' name='submitExercise' value='$langModify'>&nbsp;&nbsp;";
    }
    $tool_content .= "<input type='submit' name='cancelExercise' value='$langCancel'></td>
	</tr>
        </table>
	</fieldset>
	</form>";
} else {
    
    $disp_results_message = ($displayResults == 1) ? $langAnswersDisp : $langAnswersNotDisp;
    $disp_score_message = ($displayScore == 1) ? $langScoreDisp : $langScoreNotDisp;
    $exerciseDescription = standard_text_escape($exerciseDescription);
    $exerciseStartDate = nice_format(date("Y-m-d H:i", strtotime($exerciseStartDate)), true);
    
    $exerciseEndDate = nice_format(date("Y-m-d H:i", strtotime($exerciseEndDate)), true);
    $exerciseType = ($exerciseType == 1) ? $langSimpleExercise : $langSequentialExercise ;
    $exerciseTempSave = ($exerciseTempSave ==1) ? $langActive : $langDeactivate;
    $tool_content .= "
        <fieldset>
        <legend>$langInfoExercise&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;modifyExercise=yes'>
              <img src='$themeimg/edit.png' title='$langModify' alt='$langModify'></a></legend>
        <table width='99%' class='tbl'>
	<tr>
	  <th width='180'>$langExerciseName:</th>
	  <td>" . q($exerciseTitle) . "</td>
	</tr>
	<tr>
	  <th>$langExerciseDescription:</th>
	  <td>$exerciseDescription</td>
	</tr>
        <tr>
            <th>$langExerciseType:</th>
            <td>$exerciseType</td>
        </tr>
	<tr>
	  <th>$langExerciseStart:</th>
	  <td>$exerciseStartDate</td>
	</tr>
	<tr>
	  <th>$langExerciseEnd:</th>
	  <td>$exerciseEndDate</td>
	</tr>
        <tr>
            <th>$langTemporarySave:</th>
            <td>$exerciseTempSave</td>
        </tr>
	<tr>
	  <th>$langExerciseConstrain:</th>
	  <td>" . q($exerciseTimeConstraint) . " $langExerciseConstrainUnit</td>
	</tr>
	<tr>
	  <th>$langExerciseAttemptsAllowed:</th>
  	  <td>" . q($exerciseAttemptsAllowed) . " $langExerciseAttemptsAllowedUnit</td>
	</tr>
        <tr>
            <th>$langRandomQuestions</th>
            <td>$langSelection $randomQuestions $langFromRandomQuestions</td>
        </tr>
	<tr>
	  <th>$langAnswers:</th>
	  <td>$disp_results_message</td>
	</tr>
	<tr>
	  <th>$langScore:</th>
	  <td>$disp_score_message</td>
	</tr>
	</table>
        </fieldset>";
}
