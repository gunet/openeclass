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


// selects question information
$questionName = $objQuestion->selectTitle();
$questionDescription = $objQuestion->selectDescription();
$questionId = $objQuestion->selectId();
$questionType = $objQuestion->selectType();
$questionTypeWord = $objQuestion->selectTypeWord($questionType);
// is picture set ?
$okPicture = file_exists($picturePath . '/quiz-' . $questionId) ? true : false;
$tool_content .= "
    <fieldset>
    <legend>$langQuestion &nbsp;";

$tool_content .= "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code".(isset($exerciseId) ? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyQuestion=" . $questionId . "\">
            <img src='$themeimg/edit.png' title='$langModify sdfsdfsd' alt='$langModify'></a>";


$tool_content .= "</legend>
    <em><small>$questionTypeWord</small><em><br>
    <b>" . nl2br(q($questionName)) . "</b>&nbsp;&nbsp;";

$questionDescription = standard_text_escape($questionDescription);
$tool_content .= "<br/><i>$questionDescription</i>";
// show the picture of the question
if ($okPicture) {
    $tool_content .= "<br/><center><img src='../../$picturePath/quiz-$questionId' /></center><br/>";
}
$tool_content .= "</fieldset>";

if ($questionType !=6) {
$tool_content .= "
    <table width='100%' class='tbl'>
    <tr>
    <th><b><u>$langQuestionAnswers</u>:</b>";

// doesn't show the edit link if we come from the question pool to pick a question for an exercise
if (!isset($fromExercise)) {
    $tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId)) ? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=$questionId'>
            <img src='$themeimg/edit.png' title='$langModify' alt='$langModify'></a>";
}
$tool_content .= "<br/></th>
    </tr>
    </table>
    <br/>";
}
if (isset($exerciseId)) {
    $linkback = "admin.php?course=$course_code&amp;exerciseId=$exerciseId";
    $textback = $langBackExerciseManagement;
} else {
    $linkback = "question_pool.php?course=$course_code";
    $textback = $langGoBackToQuestionPool;
}
$tool_content .= "<div class='right'><a href='$linkback'>$textback</a></div>";