<?php

require_once 'AikenParser/Contracts/Arrayable.php';
require_once 'AikenParser/AikenParser.php';
require_once 'AikenParser/Distractor.php';
require_once 'AikenParser/DistractorCollection.php';
require_once 'AikenParser/TestItem.php';
require_once 'AikenParser/TestItemCollection.php';

require_once 'question.class.php';
require_once 'answer.class.php';

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'exercises';
$helpSubTopic = 'import_aiken';

use Aiken\Parser\AikenParser;

$pageName = $langImportAiken;
$tool_content .= action_bar([
                    [
                        'title' => $langBack,
                        'url' => "question_pool.php?course=$course_code&amp;exerciseId=0",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label'
                    ]
                ]);

$html = $quiz = '';
$weight = 1;
if (isset($_POST['validate'])) {
    $quiz = $_POST['quiz'];
    $weight = $_POST['weight'];
    $aiken = new AikenParser();
    $aiken->setQuiz($quiz);
    try {
        $itemCollection = $aiken->buildTestItemCollection($weight);
        $html = $itemCollection->toHTML();
    } catch (\Throwable | Exception $e) {
        $html = "<h4>$langDelTitle</h4>";
        $html .= $aiken->getWarnings();
        $html .= "<h4>$langErrors</h4>";
        $html .= $e->getMessage();
    }
} else if (isset($_POST['aiken_import'])) {
    $quiz = html_entity_decode($_POST['quiz'], ENT_QUOTES);
    $weight = $_POST['weight'];
    $aiken = new AikenParser();
    $aiken->setQuiz($quiz);
    try {
        $itemCollection = $aiken->buildTestItemCollection($weight);
        $res = $itemCollection->toArray();
        importToQuestionPool($res);
        Session::Messages($langImportWithSuccess, 'alert-success');
        redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code");
    } catch (\Throwable | Exception $e) {
        $html = "<h4>$langDelTitle</h4>";
        $html .= $aiken->getWarnings();
        $html .= "<h4>$langErrors</h4>";
        $html = $e->getMessage();
    }
}

$tool_content .= "
    <div class='alert alert-info text-center'>$langAikenFormatNote</div>
    <div class='alert alert-info'>$langAikenFormatExplain</div> 
    <div class='col-sm-6'>
        <label>$langExercises</label>
        <div class='form-wrapper'>
            <div class='form-group'>
                <form class='form-horizontal' name='myform' method='post' role='form' action=''>
                    <div class='form-group'>
                        <div class='col-sm-12'>
                            <textarea name='quiz' rows='45'style='width:100%;height:calc(100vh - 194px);font-family: monospace;' placeholder='$langAikenFormatExample'>$quiz</textarea>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-12'>
                            <label>$langGradebookGrade:</label>
                                <input type='number' style='width:70px' step='0.01' name='weight' value='$weight' />
                            <span class='help-block'>($langAikenWithNoGrades)</span>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-12'>
                            <input class='btn btn-primary' type='submit' name='validate' value='$langCheck'>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class='col-sm-6'>
        <label>$langCheckResults</label>
        <div class='form-wrapper'>
            <div class='form-group'>
                <form class='form-horizontal' role='form' name='myform' method='post' action=''>
                    <div class='form-group'>
                        <div style='height:calc(100vh - 155px); overflow-y: scroll;'>
                             $html
                        </div>
                    </div>                                    
                    <div class='form-group'>
                        <div class='col-sm-12'>
                            <input class='btn btn-primary' type='submit' name='aiken_import' value='$langImport'>
                        </div>
                    </div>
                    <input type='hidden' name='quiz' value='" . htmlentities($quiz, ENT_QUOTES) . "'>
                    <input type='hidden' name='weight' value='$weight'>
                </form>
            </div>
        </div>
    </div>
";

/**
 * @brief import questions in question pool
 * @param $data
 * @return void
 */
function importToQuestionPool($data): void
{
    foreach ($data as $question_data) { // question
        $q = new Question();
        $q->updateTitle($question_data['question']);
        $q->updateType(UNIQUE_ANSWER);
        $q->updateWeighting($question_data['score']);
        $q->save();
        $qid = $q->selectId();
        $position = 0;
        foreach ($question_data['answers'] as $answer_data) { // answers
            $position++;
            $a = new Answer($qid);
            $right_answer = ($answer_data['isCorrect'] == 'yes') ? 1 : 0;
            $a->createAnswer($answer_data['answer'], $right_answer, '', $answer_data['weighting'], $position);
            $a->save();
        }
    }
}
