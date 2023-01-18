<?php

/* ========================================================================
 * Open eClass 3.13
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2022  Greek Universities Network - GUnet
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


require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
require_once 'exercise.lib.php';

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
$tool_content .= "
    <table class = 'table-default'>
      <tr class='active'>
        <td colspan='$colspan'>
          <strong><u>$langQuestion</u>:</strong>
          <a target='_blank' href='$editUrl'><span class='fa fa-edit' data-toggle='tooltip' data-original-title='$langModify'></span></a>
        </td>
      </tr>
      <tr>
        <td colspan='$colspan'>
          <strong>" . q_math($questionName) . "</strong><br>" .
            standard_text_escape($questionDescription) . "<br>
        </td>
      </tr>";

if (file_exists($picturePath)) {
    $tool_content .= "<tr><td class='text-center' colspan='$colspan'><img class='img-responsive' src='{$urlAppend}$picturePath' alt=''></td></tr>";
}

if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
    $tool_content .= "
      <tr>
        <td colspan='2'><strong>$langAnswer</strong></td>
        <td><strong>$langComment</strong></td>
      </tr>";
} elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
    $tool_content .= "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";
} elseif ($answerType == MATCHING) {
    $tool_content .= "
      <tr>
        <td><strong>$langChoice</strong></td>
        <td><strong>$langCorrespondsTo</strong></td>
      </tr>";
}

if ($answerType != FREE_TEXT) {
    $answer = new Answer($qid);
    $nbrAnswers = $answer->selectNbrAnswers();

    for ($answer_id = 1; $answer_id <= $nbrAnswers; $answer_id++) {
        $answerTitle = $answer->selectAnswer($answer_id);
        $answerComment = standard_text_escape($answer->selectComment($answer_id));
        $answerCorrect = $answer->isCorrect($answer_id);
        $answerWeighting = $answer->selectWeighting($answer_id);

        if ($answerType == FILL_IN_BLANKS or $answerType == FILL_IN_BLANKS_TOLERANT) {
            list($answerTitle, $answerWeighting) = Question::blanksSplitAnswer($answerTitle);
        } elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
            $answer_array = unserialize($answerTitle);
            $answer_text = $answer_array[0]; // answer text
            $correct_answer = $answer_array[1]; // correct answer
            $answer_weight = implode(' : ', $answer_array[2]); // answer weight
        } elseif ($answerType == MATCHING) {
            $answerTitle = q($answerTitle);
        } else {
            $answerTitle = standard_text_escape($answerTitle);
        }
        if ($answerType != MATCHING || $answerCorrect) {
            if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
                if ($answerCorrect) {
                    $icon_choice = icon("fa-check-square-o");
                } else {
                    $icon_choice = icon("fa-square-o");
                }
                $tool_content .= "
                  <tr>
                    <td style='width: 70px;' class='text-center'>$icon_choice</td>
                    <td style='width: 500px;'>" . standard_text_escape($answerTitle) . " <strong><small>($langScore: $answerWeighting)</small></strong></td>
                    <td style='width: 250px;'>" . $answerComment . "</td>
                  </tr>";
            } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
                $tool_content .= "
                  <tr>
                    <td>" . standard_text_escape(nl2br($answerTitle)) . " <strong><small>($langScore: " . preg_replace('/,/', ' : ', "$answerWeighting") . ")</small></strong></td>
                  </tr>";
            } elseif ($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                $possible_answers = [];
                // fetch all possible answers
                preg_match_all('/\[[^]]+\]/', $answer_text, $out);
                foreach ($out[0] as $output) {
                    $possible_answers[] = explode("|", str_replace(array('[',']'), '', q($output)));
                }
                // find correct answers
                foreach ($possible_answers as $possible_answer_key => $possible_answer) {
                    $possible_answer = reindex_array_keys_from_one($possible_answer);
                    $correct_answer_string[] = '['. $possible_answer[$correct_answer[$possible_answer_key]] . ']';
                }

                $formatted_answer_text = preg_replace_callback($correct_answer_string,
                    function ($string) {
                        return "<span style='color: red;'>$string[0]</span>";
                    },
                    standard_text_escape(nl2br($answer_text)));
                // format correct answers
                $tool_content .= "
                  <tr>
                    <td>$formatted_answer_text&nbsp;&nbsp;&nbsp;<strong><small>($langScore: $answer_weight)</small></strong>
                    </td>
                  </tr>";
            } else {
                $tool_content .= "
                  <tr>
                    <td style='width: 450px;'>" . standard_text_escape($answerTitle) . "</td>
                    <td>{$answer->answer[$answerCorrect]}&nbsp;&nbsp;&nbsp;<strong><small>($langScore: $answerWeighting)</small></strong></td>
                  </tr>";
            }
        }
    }
}

if (!is_null($questionFeedback)) {
    $tool_content .= "<tr class='active'><td colspan='$colspan'><strong>$langQuestionFeedback:</strong></tr>
                      <tr><td colspan='$colspan'>" . standard_text_escape($questionFeedback) . "</td></tr>";
}

$tool_content .= "
      <tr class='active'>
        <th colspan='$colspan'>
          <span style='float:right;'>$langQuestionScore: <strong>" . round($questionWeighting, 2) . "</strong></span>
        </th>
      </tr>
    </table>";

echo $tool_content;
