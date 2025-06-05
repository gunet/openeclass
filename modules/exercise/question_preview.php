<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
require_once 'exercise.lib.php';
require_once 'QuestionType.php';
require_once 'MultipleChoiceUniqueAnswer.php';
require_once 'MultipleChoiceMultipleAnswer.php';
require_once 'MatchingAnswer.php';
require_once 'FillInBlanksAnswer.php';
require_once 'FillInPredefinedAnswer.php';

$require_editor = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';

if (!isset($_GET['question'])) {
    forbidden();
}
$qid = intval($_GET['question']);
$question = new Question();
$question->read($qid);
$questionName = $question->selectTitle();
$questionDescription = $question->selectDescription();
$questionFeedback = $question->selectFeedback();
$questionWeighting = $question->selectWeighting();
$answerType = $question->selectType();

if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
    $colspan = 3;
} elseif ($answerType == MATCHING) {
    $colspan = 2;
} else {
    $colspan = 1;
}
$editUrl = $urlAppend . "modules/exercise/admin.php?course=$course_code&amp;modifyAnswers=$qid&amp;fromExercise=";
$picturePath = "courses/$course_code/image/quiz-$qid";
$tool_content .= "<div class='table-responsive'>
    <table class = 'table-default'>
      <thead>
        <tr class='active'>
          <td colspan='$colspan'>
            <strong><u>$langQuestion</u>:</strong>
            <a target='_blank' href='$editUrl' aria-label='$langOpenNewTab' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$langModify' aria-label='$langModify'><i class='fa fa-edit'></i></a>
          </td>
        </tr>
      </thead>
      <tr>
        <td colspan='$colspan'>
          <strong>" . q_math($questionName) . "</strong><br>" .
            standard_text_escape($questionDescription) . "<br>
        </td>
      </tr>";

if (file_exists($picturePath)) {
    $tool_content .= "<tr><td colspan='$colspan'><img class='img-responsive' src='{$urlAppend}$picturePath' alt=''></td></tr>";
}

// display answers
$tool_content .= preview_question($qid, $answerType);

if (!is_null($questionFeedback)) {
    $tool_content .= "<tr class='active'><td colspan='$colspan'><strong>$langQuestionFeedback:</strong></tr>
                      <tr><td colspan='$colspan'>" . standard_text_escape($questionFeedback) . "</td></tr>";
}

$tool_content .= "
      <tr class='active'>
        <th colspan='$colspan'>
          <span>$langQuestionScore: <strong>" . round($questionWeighting, 2) . "</strong></span>
        </th>
      </tr>
    </table></div>";

echo $tool_content;
