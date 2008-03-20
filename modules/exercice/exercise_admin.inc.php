<?php // $Id$
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*****************************************************************************
		Create the HTML for a jscalendar field 
******************************************************************************/


// the exercise form has been submitted
if(isset($submitExercise))
{
	$exerciseTitle=trim($exerciseTitle);
	$exerciseDescription=trim($exerciseDescription);
	@$randomQuestions=$randomQuestions?$questionDrawn:0;

	// no title given
	if(empty($exerciseTitle))
	{
		$msgErr=$langGiveExerciseName;
	} else {
		if ((!is_numeric($exerciseTimeConstrain))||(!is_numeric($exerciseAttemptsAllowed))) {
			$msgErr=$langGiveExerciseInts;
		} else {
			$objExercise->updateTitle($exerciseTitle);
			$objExercise->updateDescription($exerciseDescription);
			$objExercise->updateType($exerciseType);
			$objExercise->updateStartDate($exerciseStartDate);
			$objExercise->updateEndDate($exerciseEndDate);
			$objExercise->updateTimeConstrain($exerciseTimeConstrain);
			$objExercise->updateAttemptsAllowed($exerciseAttemptsAllowed);			
			$objExercise->setRandom($randomQuestions);
			$objExercise->save();
			// reads the exercise ID (only usefull for a new exercise)
			$exerciseId=$objExercise->selectId();
			unset($modifyExercise);
		}
	}
}
else
{
	$exerciseTitle=$objExercise->selectTitle();
	$exerciseDescription=$objExercise->selectDescription();
	$exerciseType=$objExercise->selectType();
	$exerciseStartDate=$objExercise->selectStartDate();
	$exerciseEndDate=$objExercise->selectEndDate();
	$exerciseTimeConstrain=$objExercise->selectTimeConstrain();
	$exerciseAttemptsAllowed=$objExercise->selectAttemptsAllowed();
	$randomQuestions=$objExercise->isRandom();
}

// shows the form to modify the exercise
if(isset($modifyExercise))
{

$tool_content .= <<<cData
    <form method="post" action="${PHP_SELF}?modifyExercise=${modifyExercise}">
    <table width="99%" class='FormData'>
    <tbody>
cData;

	if(!empty($msgErr)){
		$tool_content .= <<<cData
    <tr>
      <td colspan="2">
      <table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
        <tr><td>$msgErr</td></tr>
      </table>
      </td>
    </tr>
cData;
	}


$tool_content .= "
    <tr>
      <th class=\"left\" width=\"220\">&nbsp;</th>
      <td><b>$langInfoExercise</b></td>
    </tr>
    <tr>
      <th class=\"left\">".$langExerciseName." :</th>
      <td><input type=\"text\" name=\"exerciseTitle\" "."size=\"50\" maxlength=\"200\" value=\"".htmlspecialchars($exerciseTitle)."\" style=\"width:400px;\" class=\"FormData_InputText\"></td>
    </tr>";

$tool_content .= "
    <tr>
      <th class=\"left\">".$langExerciseDescription." :</th>
      <td><textarea wrap=\"virtual\" ".
	"name=\"exerciseDescription\" cols=\"50\" rows=\"4\" style=\"width:400px;\" class=\"FormData_InputText\">".htmlspecialchars($exerciseDescription)."</textarea></td>
    </tr>";
	
$tool_content .= "
    <tr>
      <th class=\"left\">".$langExerciseType." :</th>
      <td>"."<input type=\"radio\" name=\"exerciseType\" value=\"1\" ";
	
if($exerciseType <= 1) 
	$tool_content .= " checked=\"checked\"";
$tool_content .= "> ".$langSimpleExercise."
      <br>
      <input type=\"radio\" name=\"exerciseType\" value=\"2\" ";

if($exerciseType >= 2) 
	$tool_content .= 'checked="checked"';
$tool_content .= "> ".$langSequentialExercise."</td>
    </tr>";

if (isset($exerciseStartDate))
	$start_cal_Excercise = jscal_html('exerciseStartDate', $exerciseStartDate);
else
	$start_cal_Excercise = jscal_html('exerciseStartDate', strftime('%Y-%m-%d', strtotime('now -0 day')));

if (isset($exerciseEndDate) and $exerciseEndDate != '')
	$end_cal_Excercise = jscal_html('exerciseEndDate', $exerciseEndDate);
else
	$end_cal_Excercise = jscal_html('exerciseEndDate', strftime('%Y-%m-%d', strtotime('now +1 year')));

$tool_content .= "
      <th class=\"left\">".$langExerciseStart." :</th>"."
      <td>$start_cal_Excercise</td></tr>"; 

$tool_content .= "
      <th class=\"left\">".$langExerciseEnd." :</th>"."
      <td>$end_cal_Excercise</td>
    </tr>";

$tool_content .= "
    <tr>
      <th class=\"left\">".$langExerciseConstrain." :</th>"."
      <td><input type=\"text\" name=\"exerciseTimeConstrain\" size=\"3\" maxlength=\"3\" ". 
  "value=\"".htmlspecialchars($exerciseTimeConstrain)."\" class=\"FormData_InputText\">&nbsp;&nbsp;". 
  $langExerciseConstrainUnit." &nbsp;&nbsp;&nbsp;&nbsp;(".$langExerciseConstrainExplanation.")</td>
    </tr>";
  
$tool_content .= "
    <tr>
      <th class=\"left\">".$langExerciseAttemptsAllowed." :</th>"."
      <td><input type=\"text\" name=\"exerciseAttemptsAllowed\" size=\"3\" maxlength=\"2\"". 
  "value=\"".htmlspecialchars($exerciseAttemptsAllowed)."\" class=\"FormData_InputText\">&nbsp;&nbsp;". 
  $langExerciseAttemptsAllowedUnit." &nbsp;&nbsp;&nbsp;(".$langExerciseAttemptsAllowedExplanation.")</td>
    </tr>";

/*
	if($exerciseId && $nbrQuestions) {
		$tool_content .= "<tr><td valign=\"top\">".$langRandomQuestions." :</td>".
  		"<td><input type=\"checkbox\" name=\"randomQuestions\" value=\"1\" "; 
  	if($randomQuestions) 
  		$tool_content .= "checked=\"checked\"";  
  	$tool_content .= ">".$langYes.", $langTake";
  	
    $tool_content .= "<select name=\"questionDrawn\">";

		for($i=1;$i <= $nbrQuestions;$i++) {
			$tool_content .= "<option value=\"".$i." ";
			
			if((isset($formSent) && $questionDrawn == $i) || (!isset($formSent) && ($randomQuestions == $i || ($randomQuestions <= 0 && $i == $nbrQuestions)))) 
				$tool_content .= 'selected="selected"'; 
			
			$tool_content .=">".$i."</option>";
		}
		$tool_content .= "</select> ".strtolower($langQuestions)." ".$langAmong." ".$nbrQuestions." </td></tr>";
	}
*/

	$tool_content .= <<<cData
	   <tr>
      <th class=\"left\">&nbsp;</th>
      <td><input type="submit" name="submitExercise" value="${langOk}">&nbsp;&nbsp;<input type="submit" name="cancelExercise" value="${langCancel}"></td>
    </tr>
    </tbody>
    </table>
    </form>
cData;

} else {

$tool_content .= <<<cData
    <table width="99%" class="FormData">
    <tbody>
    <tr>
      <th width="220" class="left">&nbsp;</th>
      <td><b>$langInfoExercise</b>&nbsp;&nbsp;<a href="$PHP_SELF?modifyExercise=yes"><img src="../../template/classic/img/edit.gif"   border="0" align="absmiddle" title="$langModify"></a>
      </td>
    </tr>
    <tr>
      <th width="220" class="left">$langExerciseName :</th>
      <td>$exerciseTitle</td>
    </tr>
    <tr>
      <th class="left">$langExerciseDescription :</th>
      <td>
cData;

$tool_content .= nl2br($exerciseDescription);

$exerciseStartDate = greek_format($exerciseStartDate);
$exerciseEndDate = greek_format($exerciseEndDate);
$tool_content .= <<<cData
      </td>
    </tr>
    <tr>
      <th class="left">${langExerciseStart} :</th>
      <td>${exerciseStartDate}</td>
    </tr>
    <tr>
      <th class="left">${langExerciseEnd} :</th>
      <td>${exerciseEndDate}</td>
    </tr>
    <tr>
      <th class="left">${langExerciseConstrain} :</th>
      <td>${exerciseTimeConstrain} $langExerciseConstrainUnit</td>
    </tr>
    <tr>
      <th class="left">${langExerciseAttemptsAllowed} :</th>
      <td>${exerciseAttemptsAllowed} $langExerciseAttemptsAllowedUnit</td>
    </tr>
    </tbody>
    </table>

cData;

$tool_content .= "<br>";
}

?>
