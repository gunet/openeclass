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

$require_current_course = true;
$require_editor = TRUE;
$require_help = true;
$helpTopic = 'Exercise';

require_once '../../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langExercices);

if (isset($_GET['exerciseId'])) {
    $exerciseId = getDirectReference($_GET['exerciseId']);
    $exerciseIdIndirect = $_GET['exerciseId'];
}
// if the object is not in the session
if (!isset($_SESSION['objExercise'][$exerciseId])) {
    // construction of Exercise
    $objExercise = new Exercise();
    // if the specified exercise doesn't exist or is disabled
    if (!$objExercise->read($exerciseId) && (!$is_editor)) {
        $tool_content .= "<p>$langExerciseNotFound</p>";
        draw($tool_content, 2);
        exit();
    }
    if(!$objExercise->selectScore() && !$is_editor) {
        redirect_to_home_page("modules/exercise/index.php?course=$course_code");
    }
}

if (isset($_SESSION['objExercise'][$exerciseIdIndirect])) {
    $objExercise = $_SESSION['objExercise'][$exerciseIdIndirect];
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$pageName = q_math($exerciseTitle) ;
$questionList = $objExercise->selectQuestionList();
//display exercise description if there is one
if($exerciseDescription) {
    // $tool_content .= "<h3>$langExerciseDescription</h3>
    //     <div class='table-responsive'>
    //         <table class='table-default'>
    //             <tr>
    //                 <td>" . standard_text_escape($exerciseDescription) . "</td>
    //             </tr>
    //         </table>
    //     </div>";

    $tool_content .= "<div class='panel panel-default mb-4'>
                        <div class='panel-heading'>
                            <h3 class='mb-0'>$langExerciseDescription</h3>
                        </div>
                        <div class='panel-body'>
                            " . standard_text_escape($exerciseDescription) . "
                        </div>
                      </div>";
}

$tool_content .= "
   
    <div class='panel panel-default'>
    <div class='panel-heading'>
        <h3 class='mb-0'>$langTableFreeText</h3>
    </div>
    <div class='panel-body'>
    <div class='table-responsive mt-0'>
        <table class='table-default'>
            <thead>
                <tr>
                    <th>$langTitle</th>
                    <th>$langSuccessPercentage</th>
                </tr>
            </thead>
            <tbody>";

foreach($questionList as $id) {
    $objQuestionTmp = new Question();
    $objQuestionTmp->read($id);
    if ($objQuestionTmp->selectType() == FREE_TEXT) {
        // $tool_content .= "
        // <tr>
        //     <td>".q_math($objQuestionTmp->selectTitle())."</th>
        //     <td>
        //         <div class='progress'>
        //             <div class='progress-bar progress-bar-success progress-bar-striped' role='progressbar' aria-valuenow='".$objQuestionTmp->successRateInQuestion($exerciseId)."' aria-valuemin='0' aria-valuemax='100' style='width: ".$objQuestionTmp->successRateInQuestion($exerciseId)."%;'>
        //               ".$objQuestionTmp->successRateInQuestion()."%
        //             </div>
        //         </div>
        //     </td>
        // </tr>";
        $tool_content .= "
        <tr>
            <td>".q_math($objQuestionTmp->selectTitle())."</th>
            <td>
                <div class='progress-circle-bar' role='progressbar' aria-valuenow='".$objQuestionTmp->successRateInQuestion($exerciseId)."' aria-valuemin='0' aria-valuemax='100' style='--value: ".$objQuestionTmp->successRateInQuestion($exerciseId)."; --size: 6rem;'></div>
            </td>
        </tr>";
    }
}

$tool_content .= "
            </tbody>
        </table>
    </div></div></div>";

$tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3 mt-4'>
  <div class='card-header border-0 d-flex justify-content-between align-items-center'>
    <h3 class='mb-0'>" . $langOpenQuestionPageTitle . "</h3>
  </div>
    <div class='card-body'>";
    $question_types = Database::get()->queryArray("SELECT exq.question, exwq.q_position, exq.id, eur.eid "
            . "FROM exercise_question AS exq "
            . "JOIN exercise_with_questions AS exwq ON exq.id = exwq.question_id "
            . "JOIN exercise_answer_record AS ear ON ear.question_id = exq.id "
            . "JOIN exercise_user_record AS eur ON eur.eurid = ear.eurid "
            . "WHERE eur.eid = ?d AND ear.weight IS NULL AND exq.type = ". FREE_TEXT . " "
            . "GROUP BY exq.id, eur.eid ORDER BY exwq.q_position",$exerciseId);

    $questions_table = "<table id=\'my-grade-table\' class='table-default'><thead class='list-header'><tr><th>$langOpenQuestionTitle</th><th>$langChoice</th></tr></thead><tbody>";
    $i=0;
    foreach ($question_types as $row){
        $question_id = $row->id;
        $i++;
        $questions_table .= "<tr>"
             . "<td>$row->question</td>"
                 . "<td> <input type='radio' name='question_id' value='$question_id' required><strong> $i </strong></td>"
             . "</tr>";
    }
$questions_table .= "</tbody></table>";
//create form
$action_url = "exercise_result_by_question.php?exerciseId={$exerciseIdIndirect}";
$tool_content .= "<form id='grade_form' method='POST' action='$action_url'>$questions_table </form></div></div>";

//creating buttons at the end of page
$tool_content .= "<br><div class='col-12 d-flex justify-content-center align-items-center gap-2'>";
//submit button
$tool_content .= "<input type='submit' value='$langSubmit' form='grade_form' class='btn submitAdminBtn' id='submitButton'>"
        . "<a class='btn cancelAdminBtn' href='index.php?course=$course_code'>
           $langReturn
       </a></div>";

draw($tool_content, 2, null, $head_content);
