<?php

/* ========================================================================
 * Open eClass 3.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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

//Set link to previous screen and text for that link
if (isset($exerciseId)) {
    $linkback = "admin.php?course=$course_code&amp;exerciseId=$exerciseId";
    $textback = $langBackExerciseManagement;
} else {
    $linkback = "question_pool.php?course=$course_code";
    $textback = $langGoBackToQuestionPool;
}

//Action Bar
$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => $linkback,
        'icon' => 'fa-reply',
        'level' => 'primary-label'
    )
));
    
// is picture set ?
$okPicture = file_exists($picturePath . '/quiz-' . $questionId) ? true : false;
$questionDescription = standard_text_escape($questionDescription);

$tool_content .= "
    <div class='panel panel-primary'>
      <div class='panel-heading'>
        <h3 class='panel-title'>$langQuestion &nbsp;".icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . "?course=$course_code".(isset($exerciseId) ? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyQuestion=" . $questionId)."</h3>
      </div>
      <div class='panel-body'>
        <h4><small>$questionTypeWord</small><br>" . nl2br(q_math($questionName)) . "</h4>
        <p>$questionDescription</p>
        ".(($okPicture)? "<div class='text-center'><img src='../../$picturePath/quiz-$questionId'></div>":"")."
      </div>
    </div>    
";

if ($questionType != 6) {
    $tool_content .= "
        <div class='panel panel-info'>
          <div class='panel-heading'>
            <h3 class='panel-title'>$langQuestionAnswers &nbsp;&nbsp;".icon('fa-edit', $langModify, "$_SERVER[SCRIPT_NAME]?course=$course_code".((isset($exerciseId)) ? "&amp;exerciseId=$exerciseId" : "")."&amp;modifyAnswers=$questionId")."</h3>
          </div>
<!--      <div class='panel-body'>
            Answers should be placed here
          </div>
-->          
        </div>    
    ";   
}

$tool_content .= "<div class='pull-right'><a href='$linkback'>$textback</a></div>";
