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


// if the question we are modifying is used in several exercises
if (isset($usedInSeveralExercises)) {
    @$tool_content .= "
	<h3>" . q($questionName) . "</h3>
	<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modifyQuestion=$_GET[modifyQuestion]&amp;modifyAnswers=$_GET[modifyAnswers]'>
	<table class='tbl' width='100%'><tr><td>";

    // submit question
    if (isset($_POST['submitQuestion'])) {
        $tool_content .= "<input type=\"hidden\" name=\"questionName\" value=\"" . htmlspecialchars($questionName) . "\">";
        $tool_content .= "<input type=\"hidden\" name=\"questionDescription\"" . "value=\"" . htmlspecialchars($questionDescription) . "\">";
        $tool_content .= "<input type='hidden' name='deletePicture' value='$deletePicture'>";
    }
    // submit answers
    else {
        if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
            $tool_content .= "
			<input type=\"hidden\" name=\"correct\" value=\"" . htmlspecialchars(serialize($correct)) . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"reponse\" value=\"" . htmlspecialchars(serialize($reponse)) . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"comment\" value=\"" . htmlspecialchars(serialize($comment)) . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"weighting\" value=\"" . htmlspecialchars(serialize($weighting)) . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"nbrAnswers\" value=\"" . $nbrAnswers . "\">";
        } elseif ($answerType == MATCHING) {
            $tool_content .= "
			<input type=\"hidden\" name=\"option\" value=\"" . htmlspecialchars(serialize($option)) . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"match\" value=\"" . htmlspecialchars(serialize($match)) . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"sel\" value=\"" . htmlspecialchars(serialize($sel)) . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"weighting\" value=\"" . htmlspecialchars(serialize($weighting)) . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"nbrOptions\" value=\"" . $nbrOptions . "\">";
            $tool_content .= "
			<input type=\"hidden\" name=\"nbrMatches\" value=\"" . $nbrMatches . "\">";
        } else {
            $tool_content .= "<input type=\"hidden\" name=\"reponse\" value=\"" . htmlspecialchars(serialize($reponse)) . "\">";
            $tool_content .= "<input type=\"hidden\" name=\"comment\" value=\"" . htmlspecialchars(serialize($comment)) . "\">";
            $tool_content .= "<input type=\"hidden\" name=\"blanks\" value=\"" . htmlspecialchars(serialize($blanks)) . "\">";
            $tool_content .= "<input type=\"hidden\" name=\"weighting\" value=\"" . htmlspecialchars(serialize($weighting)) . "\">" . "
			<input type=\"hidden\" name=\"setWeighting\" value=\"1\">";
        }
    } // end submit answers

    $tool_content .= "<input type='hidden' name='answerType' value='$answerType'>
	<table width='100%' class='tbl'><tr>
	<td>$langUsedInSeveralExercises:</td>
	</tr>
	<tr>
	<td><input type='radio' name='modifyIn' value='allExercises'>
	$langModifyInAllExercises</td>
	</tr>
	<tr><td><input type='radio' name='modifyIn' value='thisExercise' checked='checked'>$langModifyInThisExercise</td>
	</tr><tr><td>";

    $tool_content .= "<input type=\"submit\" name=\"";
    if (isset($_POST['submitQuestion'])) {
        $tool_content .= "submitQuestion \" ";
    } else {
        $tool_content .= "submitAnswers \" ";
    }
    $tool_content .= "value='$langOk'>&nbsp;&nbsp;<input type='submit' name='buttonBack' value='$langCancel'>";
    $tool_content .= "</td></tr></table></td></tr></table></form>";
} else {
    // selects question information
    $questionName = $objQuestion->selectTitle();
    $questionDescription = $objQuestion->selectDescription();
    $questionId = $objQuestion->selectId();
    // is picture set ?
    $okPicture = file_exists($picturePath . '/quiz-' . $questionId) ? true : false;
    $tool_content .= "
	<fieldset>
        <legend>$langQuestion &nbsp;";
    // doesn't show the edit link if we come from the question pool to pick a question for an exercise
    if (!isset($fromExercise)) {
        $tool_content .= "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;modifyQuestion=" . $questionId . "\">
                <img src='$themeimg/edit.png' title='$langModify' alt='$langModify'></a>";
    }

    $tool_content .= "</legend>
	<b>" . nl2br(q($questionName)) . "</b>&nbsp;&nbsp;";

    $questionDescription = standard_text_escape($questionDescription);
    $tool_content .= "<br/><i>$questionDescription</i>";
    // show the picture of the question
    if ($okPicture) {
        $tool_content .= "<br/><center><img src='../../$picturePath/quiz-$questionId' /></center><br/>";
    }
    $tool_content .= "
	</fieldset>
	<table width='100%' class='tbl'>
	<tr>
	<th><b><u>$langQuestionAnswers</u>:</b>";

    // doesn't show the edit link if we come from the question pool to pick a question for an exercise
    if (!isset($fromExercise)) {
        $tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modifyAnswers=$questionId'>
		<img src='$themeimg/edit.png' title='$langModify' alt='$langModify'></a>";
    }
    $tool_content .= "<br/></th>
	</tr>
	</table>
	<br/>
	<div class='right'><a href='admin.php?course=$course_code&amp;exerciseId=$exerciseId'>$langBackExerciseManagement</a></div>";
}
