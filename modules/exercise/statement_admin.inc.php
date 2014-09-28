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

$head_content .= " 
<script>
$(function() {
    $('input[name=answerType]').click(hideGrade);
    $('input#free_text_selector').click(showGrade);
    function hideGrade(){
        $('input[name=questionGrade]').prop('disabled', true).closest('tr').hide();    
    }
    function showGrade(){
        $('input[name=questionGrade]').prop('disabled', false).closest('tr').show();    
    }    
 });
</script>
 ";
// the question form has been submitted
if (isset($_POST['submitQuestion'])) {
    $questionName = trim($questionName);
    $questionDescription = purify($questionDescription);
    // no name given
    if (empty($questionName)) {
        $msgErr = $langGiveQuestion;
    }
    if (isset($_GET['modifyQuestion'])) {
        $objQuestion->read($_GET['modifyQuestion']);
    }
    $objQuestion->updateTitle($questionName);
    $objQuestion->updateDescription($questionDescription);
    $objQuestion->updateType($answerType);
    
    //If grade field set (only in Free text questions)
    if (isset($questionGrade)) {
        $objQuestion->updateWeighting($questionGrade);
    }
    (isset($exerciseId)) ? $objQuestion->save($exerciseId) : $objQuestion->save();
    $questionId = $objQuestion->selectId();
    // upload or delete picture
    if (isset($_POST['deletePicture'])) {
        $objQuestion->removePicture();
    } elseif (isset($_FILES['imageUpload']) && is_uploaded_file($_FILES['imageUpload']['tmp_name'])) {

        require_once 'include/lib/fileUploadLib.inc.php';
        validateUploadedFile($_FILES['imageUpload']['name'], 2);

        $type = $_FILES['imageUpload']['type'];
        if (!$objQuestion->uploadPicture($_FILES['imageUpload']['tmp_name'], $type)) {
            $tool_content .= "<div class='caution'>$langInvalidPicture</div>";
        }
    }
    if (isset($exerciseId)) {
        // adds the question ID into the question list of the Exercise object
        if ($objExercise->addToList($questionId)) {
            $objExercise->save();
            $nbrQuestions++;
        }
    }
    //if the answer type is free text (which means doesn't have predefined answers) 
    //redirects to either pool or edit exercise page
    //else it redirect to modifyanswers page in order to add answers to question
    if ($answerType == FREE_TEXT) {
        $redirect_url = (isset($exerciseId)) ? "modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId" : "modules/exercise/question_pool.php?course=$course_code";
    } else {
        $redirect_url = "modules/exercise/admin.php?course=$course_code".((isset($exerciseId))? "&exerciseId=$exerciseId" : "")."&modifyAnswers=$questionId";
    }
    redirect_to_home_page($redirect_url);
} else {
// if we don't come here after having cancelled the warning message "used in several exercises"
    if (!isset($buttonBack)) {
        $questionName = $objQuestion->selectTitle();
        $questionDescription = $objQuestion->selectDescription();
        $answerType = $objQuestion->selectType();
        $questionWeight = $objQuestion->selectWeighting();
    }
}
if (isset($_GET['newQuestion']) || isset($_GET['modifyQuestion'])) {
    $questionId = $objQuestion->selectId();
    // is picture set ?
    $okPicture = file_exists($picturePath . '/quiz-' . $questionId) ? true : false;
    // if there is an error message
    if (!empty($msgErr)) {
        $tool_content .= "<p class='caution'>$msgErr</p>\n";
    }
    if (isset($_GET['newQuestion'])){
        $tool_content .= "<form enctype='multipart/form-data' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".((isset($exerciseId))? "exerciseId=$exerciseId" : "")."&amp;newQuestion=" . urlencode($_GET[newQuestion]) . "'>";
    } else {
        $tool_content .= "<form enctype='multipart/form-data' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".((isset($exerciseId))? "exerciseId=$exerciseId" : "")."&amp;modifyQuestion=" . urlencode($_GET[modifyQuestion]) . "'>";
    }
    @$tool_content .= "
	<fieldset>
	  <legend>$langInfoQuestion</legend>
	  <table class='tbl'>
	  <tr>
	    <th>" . q($langQuestion) . ":</th>
	    <td><input type='text' name='questionName'" . "size='50' value='" . q($questionName) . "'></td>
	  </tr>
	  <tr>
	    <th valign='top'>$langQuestionDescription:</th>
	    <td>"
            . rich_text_editor('questionDescription', 4, 50, $questionDescription) .
            "</td>
	  </tr>
	  <tr>
	    <th valign='top'>";

    if ($okPicture) {
        $tool_content .= "$langReplacePicture:";
    } else {
        $tool_content .= "$langAddPicture:";
    }

    $tool_content .= "</th><td>";
    if ($okPicture) {
        $tool_content .= "<img src='../../$picturePath/quiz-$questionId'><br/><br/>";
    }
    $tool_content .= "<input type='file' name='imageUpload' size='30'></td></tr>";

    if ($okPicture) {
        $tool_content .= "<tr>
		<th>$langDeletePicture:</th>
		<td><input type='checkbox' name='deletePicture' value='1' ";
        if (isset($_POST['deletePicture'])) {
            $tool_content .= 'checked="checked"';
        }
        $tool_content .= "> ";
        $tool_content .= "</td></tr>";
    }
    $tool_content .= "<tr>
        <th valign='top'>$langAnswerType: </th>
	<td><input type='radio' name='answerType' value='1' ";
    if ($answerType == 1) {
        $tool_content .= 'checked="checked"';
    }
    $tool_content .= "> " . $langUniqueSelect . "<br>";
    $tool_content .= "<input type='radio' name='answerType' value='2' ";
    if ($answerType == 2) {
        $tool_content .= 'checked="checked"';
    }
    $tool_content .= "> " . $langMultipleSelect . "
	<br>";
    $tool_content .= "<input type='radio' name='answerType' value='4' ";
    if ($answerType == 4) {
        $tool_content .= 'checked="checked"';
    }
    $tool_content .= "> " . $langMatching . "
	<br>";
    $tool_content .= "<input type='radio' name='answerType' value='3' ";
    if ($answerType == 3) {
        $tool_content .= 'checked="checked"';
    }
    $tool_content .= "> " . $langFillBlanks . "
	<br>";
    $tool_content .= "<input type='radio' name='answerType' value='5' ";
    if ($answerType == 5) {
        $tool_content .= 'checked="checked"';
    }  
    $tool_content .= "> " . $langTrueFalse. "<br>";
    $tool_content .= "<input type='radio' name='answerType' id='free_text_selector' value='6' ";
    if ($answerType == 6) {
        $tool_content .= 'checked="checked"';
    }
    $tool_content .= "> " . $langFreeText .'</td></tr>';    
    
    $tool_content .= "
        <tr".(($answerType != 6) ? " style='display:none'": "").">
        <th>$m[grade]:</th>
        <td>
	  <input type='text' name='questionGrade' value='$questionWeight'".(($answerType != 6) ? " disabled": "").">
	</td>
	</tr>
        ";
    
    $tool_content .= " 
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
