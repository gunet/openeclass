<?php
// $Id$
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


// the exercise form has been submitted
if(isset($_POST['submitExercise'])) {
	$exerciseTitle       = trim($exerciseTitle);
	$exerciseDescription = purify($exerciseDescription);
	$randomQuestions     = (isset($_POST['questionDrawn'])) ? intval($_POST['questionDrawn']) : 0;

	// no title given
	if(empty($exerciseTitle))
	{
		$msgErr = $langGiveExerciseName;
	} else {
		if ((!is_numeric($exerciseTimeConstraint))||(!is_numeric($exerciseAttemptsAllowed))) {
			$msgErr = $langGiveExerciseInts;
		} else {
			$objExercise->updateTitle($exerciseTitle);
			$objExercise->updateDescription($exerciseDescription);
			$objExercise->updateType($exerciseType);
			$objExercise->updateStartDate($exerciseStartDate);
			$objExercise->updateEndDate($exerciseEndDate);
			$objExercise->updateTimeConstraint($exerciseTimeConstraint);
			$objExercise->updateAttemptsAllowed($exerciseAttemptsAllowed);
			$objExercise->setRandom($randomQuestions);
			$objExercise->updateResults($dispresults);
			$objExercise->updateScore($dispscore);
			$objExercise->save();
			// reads the exercise ID (only usefull for a new exercise)
			$exerciseId = $objExercise->selectId();
			unset($_GET['modifyExercise']);
		}
	}
}
else
{
        $exerciseTitle           = $objExercise->selectTitle();
        $exerciseDescription     = $objExercise->selectDescription();
        $exerciseType            = $objExercise->selectType();
        $exerciseStartDate       = $objExercise->selectStartDate();
        $exerciseEndDate         = $objExercise->selectEndDate();
        $exerciseTimeConstraint  = $objExercise->selectTimeConstraint();
        $exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
        $randomQuestions         = $objExercise->isRandom();
        $displayResults          = $objExercise->selectResults();
        $displayScore            = $objExercise->selectScore();
}

// shows the form to modify the exercise
if(isset($_GET['modifyExercise']) or isset($_GET['NewExercise']) or !isset($_POST['submitExercise'])) {
	//@$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]?course=$code_cours&amp;modifyExercise=$exerciseId'>
	@$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]?course=$code_cours&amp;modifyExercise=$_GET[modifyExercise]'>
	<fieldset>
        <legend>$langInfoExercise </legend>
	<table width='99%' class='tbl'>";
	if(!empty($msgErr)) {
		$tool_content .= "
		<tr>
		  <td colspan='2'><p class='caution'>$msgErr</td>
		</tr>";
	}
	$tool_content .= "
        <tr>
        <th width='180'>".$langExerciseName.":</th>
        <td><input type='text' name='exerciseTitle' "."size='50' maxlength='200' value='".htmlspecialchars($exerciseTitle)."' style='width:400px;'></td>
        </tr>
        <tr>
        <th>".$langExerciseDescription.":</th>
        <td>". rich_text_editor('exerciseDescription', 4, 50, $exerciseDescription, "style='width:400px;' class='FormData_InputText'") ."</td>
        </tr>
        <tr>
        <th>".$langExerciseType.":</th>
        <td>"."<input type='radio' name='exerciseType' value='1'";
	
	if ($exerciseType <= 1) {
		$tool_content .= " checked='checked'";
	}
	$tool_content .= "> ".$langSimpleExercise."
	  <br />
	  <input type='radio' name='exerciseType' value='2'";
	
	if ($exerciseType >= 2) {
		$tool_content .= 'checked="checked"';
	}
	$tool_content .= "> ".$langSequentialExercise."</td>
	</tr>";
	
	if (isset($exerciseStartDate)) {
		$start_cal_Excercise = jscal_html('exerciseStartDate', $exerciseStartDate);
	} else {
		$start_cal_Excercise = jscal_html('exerciseStartDate', strftime('%Y-%m-%d %H:%M', strtotime('now -0 day')));
	}
	if (isset($exerciseEndDate) and $exerciseEndDate != '') {
		$end_cal_Excercise = jscal_html('exerciseEndDate', $exerciseEndDate);
	} else {
		$end_cal_Excercise = jscal_html('exerciseEndDate', strftime('%Y-%m-%d %H:%M', strtotime('now +1 year')));
	}
	$tool_content .= "
        <tr>
          <th>".$langExerciseStart.":</th>
	  <td>$start_cal_Excercise</td>
        </tr>
        <tr>
	  <th>".$langExerciseEnd.":</th>
	  <td>$end_cal_Excercise</td>
	</tr>	
	<tr>
	  <th>".$langExerciseConstrain.":</th>
	  <td><input type=\"text\" name=\"exerciseTimeConstraint\" size=\"3\" maxlength=\"3\" ".
	  "value=\"".htmlspecialchars($exerciseTimeConstraint)."\">&nbsp;&nbsp;".
	  $langExerciseConstrainUnit." &nbsp;&nbsp;&nbsp;&nbsp;(".$langExerciseConstrainExplanation.")</td>
	</tr>	
	<tr>
	  <th>".$langExerciseAttemptsAllowed.":</th>
	  <td><input type='text' name='exerciseAttemptsAllowed' size='3' maxlength='2'".
	"value=\"".htmlspecialchars($exerciseAttemptsAllowed)."\">&nbsp;&nbsp;".
	$langExerciseAttemptsAllowedUnit." &nbsp;&nbsp;&nbsp;(".$langExerciseAttemptsAllowedExplanation.")</td>
	</tr>";
        
        // Random Questions
        $tool_content .= "<tr><th>". $langRandomQuestions .":</th>".
                         "<td>". $langSelection ."&nbsp;".
                         "<input type='text' name='questionDrawn' size='2' value='". $randomQuestions ."' />&nbsp;".
                         $langFromRandomQuestions ."</td></tr>";


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
	  <th>".$langAnswers.":</th>"."
	  <td><input type='radio' name='dispresults' value='1'". $extra .">&nbsp;$langAnswersDisp
	  <br /><input type='radio' name='dispresults' value='0'".  $extra2 .">&nbsp;$langAnswersNotDisp
	  </td>
	</tr>
	<tr>
	  <th>".$langScore.":</th>"."
	  <td><input type='radio' name='dispscore' value='1'". $extras .">&nbsp;$langScoreDisp
	  <br /><input type='radio' name='dispscore' value='0'".  $extras2 .">&nbsp;$langScoreNotDisp
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
	$displayResults = $objExercise->selectResults();
	if ($displayResults == 1) {
		$disp_results_message = $langAnswersDisp;
	} else {
		$disp_results_message = $langAnswersNotDisp;
	}
	$displayScore = $objExercise->selectScore();
	if ($displayScore == 1) {
		$disp_score_message = $langScoreDisp;
	} else {
		$disp_score_message = $langScoreNotDisp;
	}
	$tool_content .= "
        <fieldset>
        <legend>$langInfoExercise&nbsp;<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;modifyExercise=yes'>
              <img src='$themeimg/edit.png' align='middle' title='$langModify' /></a></legend>
        <table width='99%' class='tbl'>
	<tr>
	  <th width='180'>$langExerciseName :</th>
	  <td>$exerciseTitle</td>
	</tr>
	<tr>
	  <th>$langExerciseDescription :</th>
	  <td>";
	
	$exerciseDescription = standard_text_escape($exerciseDescription);
        $tool_content       .= $exerciseDescription;
        $exerciseStartDate   = nice_format(date("Y-m-d H:i", strtotime($exerciseStartDate)), true);
        $exerciseEndDate     = nice_format(date("Y-m-d H:i", strtotime($exerciseEndDate)), true);
        $tool_content       .= "</td>
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
	  <th>$langExerciseConstrain:</th>
	  <td>$exerciseTimeConstraint $langExerciseConstrainUnit</td>
	</tr>
	<tr>
	  <th>$langExerciseAttemptsAllowed:</th>
  	  <td>$exerciseAttemptsAllowed $langExerciseAttemptsAllowedUnit</td>
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