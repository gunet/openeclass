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


// the question form has been submitted
if(isset($_POST['submitQuestion'])) {
	$questionName = trim($questionName);
	$questionDescription = purify($questionDescription);
	// no name given
	if(empty($questionName))
	{
		$msgErr = $langGiveQuestion;
	}
	// checks if the question is used in several exercises	
	elseif($exerciseId && !isset($_POST['modifyIn']) && $objQuestion->selectNbrExercises() > 1)
	{
		// duplicates the question
		$questionId=$objQuestion->duplicate();
		// deletes the old question
		$objQuestion->delete($exerciseId);
		// removes the old question ID from the question list of the Exercise object
		$objExercise->removeFromList($modifyQuestion);
		$nbrQuestions--;
		// construction of the duplicated Question
		$objQuestion=new Question();
		$objQuestion->read($questionId);
		// adds the exercise ID into the exercise list of the Question object
		$objQuestion->addToList($exerciseId);
		// construction of the Answer object
		$objAnswerTmp=new Answer($modifyQuestion);
		// copies answers from $modifyQuestion to $questionId
		$objAnswerTmp->duplicate($questionId);
		// destruction of the Answer object
		unset($objAnswerTmp);	
	}
	
	$objQuestion->read($_GET['modifyQuestion']);
	$objQuestion->updateTitle($questionName);
	$objQuestion->updateDescription($questionDescription);
	$objQuestion->updateType($answerType);
	$objQuestion->save($exerciseId);
	$questionId = $objQuestion->selectId();
	// upload or delete picture
	if (isset($_POST['deletePicture'])) {
		$objQuestion->removePicture();
	} elseif (isset($_FILES['imageUpload']) && is_uploaded_file($_FILES['imageUpload']['tmp_name'])) {
		$type = $_FILES['imageUpload']['type'];
		if (!$objQuestion->uploadPicture($_FILES['imageUpload']['tmp_name'], $type)) {
			$tool_content .= "<div class='caution'>$langInvalidPicture</div>";
		}
	}
	if($exerciseId)  {
		// adds the question ID into the question list of the Exercise object
		if($objExercise->addToList($questionId)) {
			$objExercise->save();
			$nbrQuestions++;
		}
	}
	if(isset($_GET['newQuestion'])) {
		// goes to answer administration
		$_GET['modifyAnswers'] = $questionId;
	} else {
		// goes to exercise viewing
		$editQuestion = $questionId;
	}
	unset($_GET['newQuestion'], $_GET['modifyQuestion']);
}
else
{
// if we don't come here after having cancelled the warning message "used in several exercises"
	if(!isset($buttonBack)) {
		$questionName=$objQuestion->selectTitle();
		$questionDescription=$objQuestion->selectDescription();
		$answerType=$objQuestion->selectType();
	}
}
if(isset($_GET['newQuestion']) || isset($_GET['modifyQuestion'])) {
	$questionId = $objQuestion->selectId();
	// is picture set ?        
	$okPicture = file_exists($picturePath.'/quiz-'.$questionId)?true:false;
        // if there is an error message
        if(!empty($msgErr)) {
                $tool_content .= "<p class='caution'>$msgErr</p>\n";
        }

	@$tool_content .= "
	<form enctype='multipart/form-data' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modifyQuestion=$_GET[modifyQuestion]&amp;newQuestion=$_GET[newQuestion]'>
	<fieldset>
	  <legend>$langInfoQuestion</legend>
	  <table class='tbl'>
	  <tr>
	    <th>".$langQuestion.":</th>
	    <td><input type='text' name='questionName'" ."size='50' value='".htmlspecialchars($questionName)."'></td>
	  </tr>
	  <tr>
	    <th valign='top'>$langQuestionDescription:</th>
	    <td>"
	    .rich_text_editor('questionDescription', 4, 50, $questionDescription).
	    "</td>
	  </tr>
	  <tr>
	    <th valign='top'>";

	if ($okPicture) {
		$tool_content .= "$langReplacePicture";
	} else { 
		$tool_content .= "$langAddPicture";
	}	

	$tool_content .= ":</th><td>";
	if($okPicture) {         
		$tool_content .= "<img src='../../$picturePath/quiz-$questionId'><br/><br/>";
	}
	$tool_content .= "<input type='file' name='imageUpload' size='30'></td></tr>";

	if ($okPicture) {
		$tool_content .= "<tr>
		<th>$langDeletePicture:</th>
		<td><input type='checkbox' name='deletePicture' value='1' ";
		if(isset($_POST['deletePicture'])) {
			$tool_content .= 'checked="checked"'; 
		}
		$tool_content .= "> ";
		$tool_content .= "</td></tr>";
	}
	$tool_content .= "<tr>
        <th valign='top'>$langAnswerType:</th>
	<td><input type='radio' name='answerType' value='1' ";
        if ($answerType == 1) {
                $tool_content .= 'checked="checked"';
        }
        $tool_content .= "> ".$langUniqueSelect."<br>";
        $tool_content .= "<input type='radio' name='answerType' value='2' ";
	if ($answerType == 2) {
		$tool_content .= 'checked="checked"';
	}
	$tool_content .= "> ".$langMultipleSelect."
	<br>";
        $tool_content .= "<input type='radio' name='answerType' value='4' ";
	if ($answerType == 4) {
		$tool_content .= 'checked="checked"';
	}
	$tool_content .= "> ".$langMatching."
	<br>";
	$tool_content .= "<input type='radio' name='answerType' value='3' ";
	if ($answerType == 3) {
		$tool_content .= 'checked="checked"';
	}
	$tool_content .= "> ".$langFillBlanks."
	<br>";
	$tool_content .= "<input type='radio' name='answerType' value='5' ";
	if ($answerType == 5) {
		$tool_content .= 'checked="checked"';
	}
	$tool_content .= "> ".$langTrueFalse;
	$tool_content .= "</td>
        <tr>
        <th>&nbsp;</th>
        <td>
	  <input type='submit' name='submitQuestion' value='$langOk'>
	  &nbsp;&nbsp;<input type='submit' name='cancelQuestion' value='$langCancel'>
	</td>
	</tr>
	</table>
	</fieldset>
	</form>";
}