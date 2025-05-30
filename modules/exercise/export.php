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


include 'exercise.class.php';
include 'question.class.php';
include 'answer.class.php';

$require_editor = true;
$require_current_course = true;

include '../../include/baseTheme.php';

$exerciseId = $fromExercise = $difficultyId = $categoryId = null;
if (isset($_GET['fromExercise'])) {
    $objExercise = new Exercise();
    $fromExercise = intval($_GET['fromExercise']);
    $objExercise->read($fromExercise);
}
if (isset($_GET['exerciseId'])) {
    $exerciseId = intval($_GET['exerciseId']);
}
if (isset($_GET['difficultyId'])) {
    $difficultyId = intval($_GET['difficultyId']);
}
if (isset($_GET['categoryId'])) {
    $categoryId = intval($_GET['categoryId']);
}

if ($fromExercise) {
    $result = Database::get()->queryArray("SELECT id, title FROM `exercise` WHERE course_id = ?d AND id <> ?d ORDER BY id", $course_id, $fromExercise);
} else {
    $result = Database::get()->queryArray("SELECT id, title FROM `exercise` WHERE course_id = ?d ORDER BY id", $course_id);
}

$extraSql = '';
if ($exerciseId) { // Export questions from specific exercise
    $result_query_vars = [$course_id, $exerciseId];
    if ($difficultyId and $difficultyId != -1) {
        $result_query_vars[] = $difficultyId;
        $extraSql .= " AND difficulty = ?d";
    }
    if ($categoryId and $categoryId != -1) {
        $result_query_vars[] = $categoryId;
        $extraSql .= " AND category = ?d";
    }
    if ($fromExercise) {
        $result_query_vars = array_merge($result_query_vars, [$fromExercise, $fromExercise]);
        $result_query = "SELECT exercise_question.id FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                        ON question_id = exercise_question.id WHERE course_id = ?d  AND exercise_id = ?d $extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
                        question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))
                        GROUP BY exercise_question.id ORDER BY question";
    } else {
        $result_query = "SELECT exercise_question.id FROM `exercise_with_questions`, `exercise_question`
                        WHERE course_id = ?d AND question_id = exercise_question.id AND exercise_id = ?d $extraSql
                        ORDER BY q_position";
    }
} else { // Export either orphan questions or all questions
    $result_query_vars[] = $course_id;
    if ($difficultyId and $difficultyId != -1) {
        $result_query_vars[] = $difficultyId;
        $extraSql .= " AND difficulty = ?d";
    }
    if ($categoryId and $categoryId != -1) {
        $result_query_vars[] = $categoryId;
        $extraSql .= " AND category = ?d";
    }
    // If user selected all questions and comes to question pool from an exercise
    if (!$exerciseId and $fromExercise) {
        $result_query_vars = array_merge($result_query_vars, [$fromExercise, $fromExercise]);
    }
    // If user selected orphan questions
    if ($exerciseId == -1) {
        $result_query = "SELECT exercise_question.id, question, `type` FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                        ON question_id = exercise_question.id WHERE course_id = ?d AND exercise_id IS NULL $extraSql ORDER BY question";
    } else { // If user selected all questions
        if ($fromExercise) { // If coming to question pool from an exercise
            $result_query = "SELECT exercise_question.id, question, `type` FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = exercise_question.id WHERE course_id = ?d $extraSql AND (exercise_id IS NULL OR exercise_id <> ?d AND
                            question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))
                            GROUP BY exercise_question.id, question, `type` ORDER BY question";
        } else {
            $result_query = "SELECT exercise_question.id, question, `type` FROM `exercise_question` 
                            LEFT JOIN `exercise_with_questions`
                                ON question_id = exercise_question.id 
                            LEFT JOIN exercise_question_cats 
                                ON exercise_question.category = question_cat_id
                            WHERE exercise_question.course_id = ?d $extraSql
                            GROUP BY exercise_question.id, question, type 
                            ORDER BY question_cat_name, question";
        }
        // forces the value to 0
        $exerciseId = 0;
    }
}

$exercises = Database::get()->queryArray('SELECT title, id FROM exercise WHERE course_id = ?d', $course_id);
$exercise_titles = [];
foreach ($exercises as $exercise) {
    $exercise_titles[$exercise->id] = q($exercise->title);
}

$result = Database::get()->queryArray($result_query, $result_query_vars);

$tool_content = "
<!DOCTYPE html>
<html lang='el'>
<head>
  <meta charset='utf-8'>
  <title>" . q("$currentCourseName - $langQuesList") . "</title>
  <style>
    * { font-family: 'opensans'; }
    body { font-family: 'opensans'; font-size: 10pt; }
    small, .small { font-size: 8pt; }
    h1, h2, h3, h4 { font-family: 'roboto'; margin: .8em 0 0; }
    h1 { font-size: 16pt; }
    h2 { font-size: 12pt; border-bottom: 1px solid black; }
    h3 { font-size: 10pt; color: #158; border-bottom: 1px solid #158; }
    table.answers { border: 1px solid #999; margin: 4px 0; }
    .img-responsive { max-width: 100%; }
    .label { color: #158; }
    th { text-align: left; border-bottom: 1px solid #999; }
    td { text-align: left; }
  </style>
</head>
<body>
<h1>" . q($currentCourseName) . "</h1><h2>$langQuesList</h2>";
foreach ($result as $row) {
    $question = new Question();
    $question->read($row->id);
    $question_title = q_math($question->selectTitle());
    $question_description = $question->selectDescription();
    $question_difficulty = $question->selectDifficulty();
    $question_difficulty_legend = $question->selectDifficultyLegend($question_difficulty);
    if ($question_difficulty_legend) {
        $question_difficulty_legend = "<span class='label'>$langQuestionDiffGrade:</span> $question_difficulty_legend ($question_difficulty)<br>";
    }
    $question_category_legend = $question->selectCategoryName($question->selectCategory());
    if ($question_category_legend) {
        $question_category_legend = "<span class='label'>$langQuestionCat:</span> $question_category_legend<br>";
    }
    $question_type_legend = $question->selectTypeLegend($question->selectType());
    $exercise_ids = $question->selectExerciseList();
    if ($exercise_ids) {
        $exercises_used_in = "<span class='label'>$langQuestionUsedInExercises:</span> " .
            implode(' / ', array_map(function ($ex_id) use ($exercise_titles) {
                return q($exercise_titles[$ex_id]);
            }, $exercise_ids)) . '<br>';
    } else {
        $exercises_used_in = '';
    }
    $questionWeighting = $question->selectWeighting();
    $answer_legend = $question->hasAnswered()? "<span class='label'>$langHasAnswered</span>": '';
    $picturePath = "courses/$course_code/image/quiz-{$row->id}";
    $tool_content .= "
        <h3>$question_title</h3>
        <small>$question_type_legend &ndash; Νο: {$row->id}</small><br>" .
        (file_exists($picturePath)? "<img class='img-responsive' src='$picturePath' alt=''>": '') .
        $question_description .
        question_html($question, $row->id) . "
        <div><span class='label'>$langQuestionScore:</span> " . round($questionWeighting, 2) . "</div>
        <small>$question_difficulty_legend $question_category_legend $exercises_used_in $answer_legend</small>";
    unset($question);
}

$tool_content .= "</body></html>\n";

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'tempDir' => _MPDF_TEMP_PATH,
    'fontDir' => array_merge($fontDirs, [ $webDir . '/template/modern/fonts' ]),
    'fontdata' => $fontData + [
        'opensans' => [
            'R' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-regular.ttf',
            'B' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700.ttf',
            'I' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-italic.ttf',
            'BI' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700italic.ttf'
        ],
        'roboto' => [
            'R' => 'roboto-v15-latin_greek_cyrillic_greek-ext-regular.ttf',
            'I' => 'roboto-v15-latin_greek_cyrillic_greek-ext-italic.ttf',
        ]
    ]
]);

$mpdf->setFooter('||{PAGENO} / {nb}');
$mpdf->SetCreator(course_id_to_prof($course_id));
$mpdf->SetAuthor(course_id_to_prof($course_id));
$mpdf->WriteHTML($tool_content);
$mpdf->Output("$course_code questions.pdf", 'D'); // 'D' or 'I' for download / inline display

function question_html($question, $qid) {
    global $langAnswer, $langScore, $langChoice, $langCorrespondsTo, $langComment, $langSelect;

    $checkbox_checked = '<label class="label-container" aria-label="'.$langSelect.'"><input type="checkbox" checked="checked"><span class="checkmark"></span></label>';
    $checkbox_empty = '<label class="label-container" aria-label="'.$langSelect.'"><input type="checkbox"><span class="checkmark"></span></label>';

    $answerType = $question->selectType();
    if ($answerType == FREE_TEXT) {
        return '';
    }

    $html = "<table class='answers'>";

    if ($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER || $answerType == TRUE_FALSE) {
        $html .= "
          <tr>
            <th colspan='2'>$langAnswer</th>
            <th>$langComment</th>
          </tr>";
    } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) {
        $html .= "<tr><th>$langAnswer</th></tr>";
    } elseif ($answerType == MATCHING) {
        $html .= "
          <tr>
            <th>$langChoice</th>
            <th>$langCorrespondsTo</th>
          </tr>";
    }

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
                    $icon_choice = $checkbox_checked;
                } else {
                    $icon_choice = $checkbox_empty;
                }
                $html .= "
          <tr>
            <td style='width: 70px;'>$icon_choice</td>
            <td style='width: 500px;'>" . standard_text_escape($answerTitle) . " <strong><small>($langScore: $answerWeighting)</small></strong></td>
            <td style='width: 250px;'>" . $answerComment . "</td>
          </tr>";
            } elseif ($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT) {
                $html .= "
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
                $html .= "
          <tr>
            <td>$formatted_answer_text&nbsp;&nbsp;&nbsp;<strong><small>($langScore: $answer_weight)</small></strong>
            </td>
          </tr>";
            } else {
                $html .= "
          <tr>
            <td>" . standard_text_escape($answerTitle) . "</td>
            <td>{$answer->answer[$answerCorrect]}&nbsp;&nbsp;&nbsp;<strong><small>($langScore: $answerWeighting)</small></strong></td>
          </tr>";
            }
        }
    }

    $html .= "</table>";
    return $html;
}
