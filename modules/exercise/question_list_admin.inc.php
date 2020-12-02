<?php

/* ========================================================================
 * Open eClass 3.10
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2020  Greek Universities Network - GUnet
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
/**
 * @file question_list_admin.inc.php
 */
$exerciseId = $_GET['exerciseId'];
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    if (isset($_POST['toReorder'])) {
        reorder_table('exercise_with_questions', 'exercise_id', $exerciseId, $_POST['toReorder'],
            isset($_POST['prevReorder'])? $_POST['prevReorder']: null,'id','q_position');
        exit;
    }

    $action = $_POST['action'];
    $category = $_POST['category'];
    $difficulty = $_POST['difficulty'];
    $query_vars = array($course_id);
    $extraSql = '';
    if ($difficulty > -1) {
        $query_vars[] = $difficulty;
        $extraSql .= " AND difficulty = ?d";
    }
    if ($category > -1) {
        $query_vars[] = $category;
        $extraSql .= " AND category = ?d";
    }
    $query_vars[] = $exerciseId;
    if ($action == 'random_difficulty_criteria') { // random criteria (based upon difficulty)

        $questionDifficultyDrawn = intval($_POST['questionDifficultyDrawn']);
        $difficultyId = intval($_POST['difficultyId']);
        $random_criteria = serialize(array($questionDifficultyDrawn => $difficultyId));

        $m = Database::get()->querySingle("SELECT MAX(q_position) AS position FROM exercise_with_questions WHERE exercise_id = ?d", $exerciseId);
        if ($m) {
            $new_q_position = $m->position + 1;
        } else {
            $new_q_position = 1;
        }

        Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id, q_position, random_criteria) 
                                            VALUES (?d, ?d, ?d, ?s)",
                                        NULL, $exerciseId, $new_q_position, $random_criteria);

        Database::get()->query("UPDATE exercise SET random = 0 WHERE exercise_id = ?d", $exerciseId);

        $data = array('success' => true);

    } else if ($action == 'add_questions') { // add questions
        $qnum = $_POST['qnum'];
        $query_vars[] = $qnum;
        if ($qnum > 0) {
            $q_ids = Database::get()->queryArray("SELECT id FROM exercise_question 
                                          WHERE course_id = ?d$extraSql 
                                          AND id NOT IN   
                                            (SELECT question_id FROM exercise_with_questions 
                                              WHERE exercise_id = ?d 
                                              AND question_id IS NOT NULL) 
                                          ORDER BY RAND() 
                                          LIMIT ?d", $query_vars);

            $q_ids_count = count($q_ids);
            $i=1;
            foreach ($q_ids as $q_id) {
                $values .= "(?d, ?d)";
                if ($i!=$q_ids_count) $values .= ",";
                $insert_query_vars[] = $q_id->id;
                $insert_query_vars[] = $exerciseId;
                $i++;
            }
            Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id) VALUES $values", $insert_query_vars);
        }
        $data = array('success' => true);
    } else {
        $results = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_question WHERE course_id = ?d$extraSql AND id NOT IN (SELECT question_id FROM exercise_with_questions WHERE exercise_id = ?d)", $query_vars)->count;
        $data = array('results' => $results);
    }
    echo json_encode($data);
    exit();
}


// shuffle (aka random questions)
if (isset($_POST['shuffleQuestions'])) {
    if (isset($_POST['enableShuffleQuestions']) and ($_POST['enableShuffleQuestions'] == 1)) {
        $objExercise->setShuffle(1); // shuffle all questions
        if (($_POST['numberOfRandomQuestions']) > 0) {
            $objExercise->setRandom($_POST['numberOfRandomQuestions']);  // shuffle some questions
        } else {
            $objExercise->setRandom(0);
        }
    } else {
        $objExercise->setShuffle(0);
    }
    $objExercise->save();
}

$formRandomQuestions = '';
if ($objExercise->hasQuestionListWithRandomCriteria()) {
    $formRandomQuestions = 'disable';
}

$q_cats = Database::get()->queryArray("SELECT * FROM exercise_question_cats WHERE course_id = ?d", $course_id);
$cat_options = "<option value=\"-1\">-- $langQuestionAllCats --</option><option value=\"0\">-- $langQuestionWithoutCat --</option>";
foreach ($q_cats as $qcat) {
    $cat_options .= "<option value=\"$qcat->question_cat_id\">$qcat->question_cat_name</option>";
}

$diff_options = "<option value=\"-1\">-- $langQuestionAllDiffs --</option>"
    . "<option value=\"0\">-- $langQuestionNotDefined --</option>"
    . "<option value=\"1\">$langQuestionVeryEasy</option>"
    . "<option value=\"2\">$langQuestionEasy</option>"
    . "<option value=\"3\">$langQuestionModerate</option>"
    . "<option value=\"4\">$langQuestionDifficult</option>"
    . "<option value=\"5\">$langQuestionVeryDifficult</option>";


$ajax_shuffle_url = "$_SERVER[SCRIPT_NAME]?course=$course_code&exerciseId=$exerciseId&shuffleQuestions=";
// for sorting
$head_content .= "
    <script>
        $(document).ready(function(){
            Sortable.create(q_sort,{
                handle: '.fa-arrows',
                animation: 150,
                onEnd: function (evt) {

                var itemEl = $(evt.item);

                var idReorder = itemEl.attr('data-id');
                var prevIdReorder = itemEl.prev().attr('data-id');

                $.ajax({
                  type: 'post',
                  dataType: 'text',
                  data: {
                          toReorder: idReorder,
                          prevReorder: prevIdReorder,
                        }
                    });
                }
            });            
        });
    </script>
";

$head_content .= "
<script>
    function RandomizationForm() {
        var formRandomQuestions = '" . $formRandomQuestions ."';
        if (formRandomQuestions == 'disable') {
            $('#RandomizationForm *').prop('disabled', true);
        }
    }
  $(function() {
     RandomizationForm();
    $('.questionSelection').click( function(e){
        e.preventDefault();
        bootbox.dialog({
            title: '$langSelection $langFrom2 $langQuestionPool ($langWithCriteria)',
            message: '<div class=\"row\">' +
                        '<div class=\"col-md-12\">' +
                            '<form class=\"form-horizontal\"> ' +
                                '<h4>$langSelectionRule</h4>' +
                                    '<div class=\"form-group\">' +
                                        '<div class=\"col-sm-3\">' +
                                            '<select name=\"category\" class=\"form-control\" id=\"cat\">$cat_options</select>' +
                                        '</div>' +
                                        '<div class=\"col-sm-4\">' +
                                            '<select name=\"difficulty\" class=\"form-control\" id=\"diff\">$diff_options</select>' +
                                        '</div>' +
                                        '<div class=\"col-sm-2\">' +
                                            '<input class=\"form-control\" type=\"text\" id=\"q_num\" name=\"q_num\" value=\"\"> $langQuestions' +
                                        '</div>' +
                                    '</div>' +
                            '</form>' +
                        '</div>' +
                    '</div>',
                        buttons: {
                            success: {
                                label: '$langSelection',
                                className: 'btn-success',
                                callback: function () {
                                    var catValue = $('select#cat').val();
                                    var diffValue = $('select#diff').val();
                                    var qnumValue = $('input#q_num').val();
                                    $.ajax({
                                      type: 'POST',
                                      url: '',
                                      datatype: 'json',
                                      data: {
                                         action: 'add_questions',
                                         category: catValue,
                                         difficulty: diffValue,
                                         qnum: qnumValue
                                      },
                                      success: function(data){
                                        window.location.href = '$_SERVER[REQUEST_URI]';
                                      },
                                      error: function(xhr, textStatus, error){
                                          console.log(xhr.statusText);
                                          console.log(textStatus);
                                          console.log(error);
                                      }
                                    });
                                }
                            }
                        }
                    }
                ).find('div.modal-dialog').addClass('modal-lg');                
    });    
    $('.randomWithCriteria').click(function(e) {
        e.preventDefault();
        bootbox.dialog({
            title: '$langSelectRandomCriteria',
            message: '<div class=\"row\">' +
                        '<div class=\"col-md-12\">' +
                            '<form class=\"form-horizontal\">' +
                                '<h4>$langQuestionDiffGrade</h4>' +                                                            
                                '<div class=\"form-group\">' +
                                    '<div class=\"col-sm-5\">' +
                                        '<select id=\"difficultyId\" class=\"form-control\">' +
                                            '<option value=\"1\">$langQuestionVeryEasy</option>' +
                                            '<option value=\"2\">$langQuestionEasy</option>' +
                                            '<option value=\"3\">$langQuestionModerate</option>' +
                                            '<option value=\"4\">$langQuestionDifficult</option>' +
                                            '<option value=\"5\">$langQuestionVeryDifficult</option>' +
                                        '</select>' +
                                    '</div>' +                                    
                                    '<div class=\"col-sm-2\">' +
                                        '<input class=\"form-control\" type=\"text\" id=\"questionDifficultyDrawn\" value=\"2\"> $langQuestions' +
                                    '</div>' +
                                '</div>' +
                            '</form>' +
                        '</div>' +
                      '</div>',
            buttons: {
                success: {
                    label: '$langSubmit',
                    className: 'btn-success',
                    callback: function () {
                        var difficultyIdValue = $('select#difficultyId').val();
                        var questionDifficultyDrawnValue = $('input#questionDifficultyDrawn').val();                                                
                        $.ajax({
                          type: 'POST',
                          url: '',
                          datatype: 'json',
                          data: {
                             action: 'random_difficulty_criteria',
                             difficultyId: difficultyIdValue,
                             questionDifficultyDrawn: questionDifficultyDrawnValue                             
                          },
                          success: function(data) {
                            window.location.href = '$_SERVER[REQUEST_URI]';
                          },
                          error: function(xhr, textStatus, error){
                              console.log(xhr.statusText);
                              console.log(textStatus);
                              console.log(error);
                          }
                        });
                    }
                }
            }        
        });
    });    
    $('.menu-popover').on('shown.bs.popover', function () {
        $('.warnLink').on('click', function(e){
              var modifyAllLink = $(this).attr('href');
              var modifyOneLink = modifyAllLink.concat('&clone=true');
              $('a#modifyAll').attr('href', modifyAllLink);
              $('a#modifyOne').attr('href', modifyOneLink);
        });
    });    
  });
</script>
";

$tool_content .= "<div id='dialog' style='display:none;'>$langUsedInSeveralExercises</div>";

// deletes a question from the exercise
if (isset($_GET['deleteQuestion'])) {
    $deleteQuestion = $_GET['deleteQuestion'];
    $objQuestionTmp = new Question();
    // if the question exists and it is not random
    if ($objQuestionTmp->read($deleteQuestion)) {
        $objQuestionTmp->delete($exerciseId);
        // if the question has been removed from the exercise
        if ($objExercise->removeFromList($deleteQuestion)) {
            $nbrQuestions--;
        }
    } else { // random
        $objQuestionTmp->removeRandomQuestionsFromList($deleteQuestion, $exerciseId);
    }
    redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
}

$randomQuestions = $objExercise->isRandom();
$shuffleQuestions = $objExercise->selectShuffle();

$disabled = '';
if ($objExercise->hasQuestionListWithRandomCriteria()) {
    $disabled = ' disabled';
}

$tool_content .= action_bar(array(
    array('title' => $langNewQu,
          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;newQuestion=yes",
          'icon' => 'fa-plus-circle',
          'level' => 'primary-label',
          'button-class' => 'btn-success'),
    array('title' => $langRandomQuestionsWithCriteria,
          'class' => 'randomWithCriteria',
          'url' => "#",
          'icon' => 'fa-random',
          'level' => 'primary-label',
           'show' => !$randomQuestions),
    array('title' => "$langImport $langFrom2 $langQuestionPool ($langWithoutCriteria)",
          'url' => "question_pool.php?course=$course_code&amp;fromExercise=$exerciseId",
          'icon' => 'fa-bank'),
    array('title' => "$langImport $langFrom2 $langQuestionPool ($langWithCriteria)",
        'class' => 'questionSelection',
        'url' => "#",
        'icon' => 'fa-bank')),
    false);

if ($nbrQuestions) {
    $info_random_text = '';
    if ($randomQuestions > 0) {
        $info_random_text = "<small><span class='help-block'>$langShow $randomQuestions $langFromRandomQuestions</span></small>";
    }

    $questionList = $objExercise->selectQuestionList();
    $tool_content .= "<div id='RandomizationForm' class='form-wrapper'>   
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId'>
                <div class='form-group'>
                    <div class='col-sm-12'>                        
                        <label class='col-sm-3'>
                             <input type='checkbox' name='enableShuffleQuestions' value='1' ".(($shuffleQuestions == 1)? 'checked' : '').">
                             $langShuffleQuestions 
                         </label>
                         <span class='col-sm-1'><input type='text' class='form-control' name='numberOfRandomQuestions' value=".(($randomQuestions > 0)? $randomQuestions : '')."></span>
                         <span class='col-sm-8'>$langFromRandomQuestions</span>                        
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-12'>
                        <input class='btn btn-primary' type='submit' value='$langSubmit' name='shuffleQuestions'>
                    </div>
                </div>
            </form>
        </div>";

    $i = 1;
    $tool_content .= "
        <div class='table-responsive'>
        <table class='table-default'>
        <thead>        
        <tr>
          <th colspan='2' class='text-left'>$langQuestionList $info_random_text</th>
          <th class='text-center'>".icon('fa-gears', $langActions)."</th>
        </tr>
        </thead>
        <tbody id='q_sort'>";

    foreach ($questionList as $id) {

        $objQuestionTmp = new Question();
        $objQuestionTmp->read($id);
        $q = Database::get()->querySingle("SELECT id FROM exercise_with_questions 
                                    WHERE exercise_id = ?d 
                                    AND (question_id = ?d OR question_id IS NULL)", $exerciseId, $id);
        $ewq_id = $q->id;
        $aType = $objQuestionTmp->selectType();
        $question_difficulty_legend = $objQuestionTmp->selectDifficultyIcon($objQuestionTmp->selectDifficulty());
        $addon = '';
        if ($objQuestionTmp->selectType() == MATCHING) {
            $sql = Database::get()->querySingle("SELECT * from exercise_answer WHERE question_id = ?d", $id);
            if (!$sql) $addon = "&amp;htopic=4";
        }

        if (is_array($id)) {
            foreach ($id as $num_of_q => $d) {
                $legend = "<span style='color: red;'>$num_of_q $langFromRandomDifficultyQuestions " . $objQuestionTmp->selectDifficultyLegend($d) . "</span>";
            }
        } else {
            $legend = q_math($objQuestionTmp->selectTitle()) . "<br>
            <small>" . $objQuestionTmp->selectTypeLegend($aType) . "</small>&nbsp;$question_difficulty_legend";
        }

        $tool_content .= "<tr data-id='$ewq_id'>
            <td style='text-align: right;' width='1'>" . $i . ".</td>
            <td>" . $legend . "</td>";
        $tool_content .= "<td class='option-btn-cell' style='width: 85px;'>";
        $tool_content .= "<div class='reorder-btn pull-left' style='padding:5px 10px 0; font-size: 16px; cursor: pointer; vertical-align: bottom;'>
                            <span class='fa fa-arrows' style='cursor: pointer;' data-toggle='tooltip' data-placement='top' title='$langReorder'></span>
                        </div>";

        $tool_content .= "<div class='pull-left'>";
        if (!is_array($id)) {
            $tool_content .=
                action_button(array(
                    array('title' => $langEditChange,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;modifyAnswers=$id$addon",
                        'icon-class' => 'warnLink',
                        'icon-extra' => $objQuestionTmp->selectNbrExercises() > 1 ? "data-toggle='modal' data-target='#modalWarning' data-remote='false'" : "",
                        'icon' => 'fa-edit'),
                    array('title' => $langDelete,
                        'url' => "?course=$course_code&amp;exerciseId=$exerciseId&amp;deleteQuestion=$id",
                        'icon' => 'fa-times',
                        'class' => 'delete',
                        'confirm' => $langConfirmYourChoice,
                        'show' => !isset($fromExercise))
                ));
        } else {
            $tool_content .=
                action_button(array(
                    array('title' => $langDelete,
                        'url' => "?course=$course_code&amp;exerciseId=$exerciseId&amp;deleteQuestion=$ewq_id",
                        'icon' => 'fa-times',
                        'class' => 'delete',
                        'confirm' => $langConfirmYourChoice,
                        'show' => !isset($fromExercise))
                ));
        }
        $tool_content .= "</div>";
        $tool_content .= "</td>";
        $tool_content .= "</tr>";
        $i++;
        unset($objQuestionTmp);
    }
    $tool_content .= "</tbody></table></div>";
}
$tool_content .= "
<!-- Modal -->
<div class='modal fade' id='modalWarning' tabindex='-1' role='dialog' aria-labelledby='modalWarningLabel' aria-hidden='true'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>
      </div>
      <div class='modal-body'>
        $langUsedInSeveralExercises
      </div>
      <div class='modal-footer'>
        <a href='#' id='modifyAll' class='btn btn-primary'>$langModifyInAllExercises</a>
        <a href='#' id='modifyOne' class='btn btn-success'>$langModifyInThisExercise</a>
      </div>
    </div>
  </div>
</div>
";
if ($nbrQuestions == 0) {
    $tool_content .= "<div class='alert alert-warning'>$langNoQuestion</div>";
}