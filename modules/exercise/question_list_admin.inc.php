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
    if ($action == 'random_criteria') { // random criteria (based upon difficulty)
        if (isset($_POST['questionRandomDrawn']) and intval($_POST['questionRandomDrawn']) > 0) { // random difficulty questions
            $difficultyId = intval($_POST['difficultyId']);
            $categoryId = intval($_POST['categoryId']);
            $questionRandomDrawn = intval($_POST['questionRandomDrawn']);
            if ($difficultyId > 0 and $categoryId > 0) { // random difficulty and category questions
                $random_criteria = serialize(array('criteria' => 'difficultycategory', $questionRandomDrawn => array($difficultyId, $categoryId)));
                $m = Database::get()->querySingle("SELECT MAX(q_position) AS position FROM exercise_with_questions WHERE exercise_id = ?d", $exerciseId);
                if ($m) {
                    $new_q_position = $m->position + 1;
                } else {
                    $new_q_position = 1;
                }
                Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id, q_position, random_criteria)
                                            VALUES (?d, ?d, ?d, ?s)",
                    NULL, $exerciseId, $new_q_position, $random_criteria);
            } else if ($difficultyId > 0) { // random difficulty questions
                $random_criteria = serialize(array('criteria' => 'difficulty', $questionRandomDrawn => $difficultyId));
                $m = Database::get()->querySingle("SELECT MAX(q_position) AS position FROM exercise_with_questions WHERE exercise_id = ?d", $exerciseId);
                if ($m) {
                    $new_q_position = $m->position + 1;
                } else {
                    $new_q_position = 1;
                }
                Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id, q_position, random_criteria)
                                            VALUES (?d, ?d, ?d, ?s)",
                    NULL, $exerciseId, $new_q_position, $random_criteria);
            } else if ($categoryId > 0) { // random category questions
                $random_criteria = serialize(array('criteria' => 'category', $questionRandomDrawn => $categoryId));
                $m = Database::get()->querySingle("SELECT MAX(q_position) AS position FROM exercise_with_questions WHERE exercise_id = ?d", $exerciseId);
                if ($m) {
                    $new_q_position = $m->position + 1;
                } else {
                    $new_q_position = 1;
                }
                Database::get()->query("INSERT INTO exercise_with_questions (question_id, exercise_id, q_position, random_criteria)
                                            VALUES (?d, ?d, ?d, ?s)",
                    NULL, $exerciseId, $new_q_position, $random_criteria);
            }
            // cancel shuffling (if any)
            Database::get()->query("UPDATE exercise SET shuffle = 0 WHERE id = ?d", $exerciseId);
            Database::get()->query("UPDATE exercise SET random = 0 WHERE id = ?d", $exerciseId);
        }

        $data = array('success' => true);

    } else if ($action == 'add_questions') { // add questions

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
if (isset($_POST['shuffleQuestions'])) {  // shuffle all questions
    if (isset($_POST['enableShuffleQuestions'])) {
        $objExercise->setShuffle(1);
        $objExercise->setRandom(0);
    } else if (isset($_POST['enableRandomQuestions'])) {
        if (isset($_POST['numberOfRandomQuestions']) and ($_POST['numberOfRandomQuestions']) > 0) {
            $objExercise->setRandom($_POST['numberOfRandomQuestions']);  // shuffle some questions
            $objExercise->setShuffle(0);
        }
    } else { // reset everything
        $objExercise->setRandom(0);
        $objExercise->setShuffle(0);
    }
    $objExercise->save();
}

$formRandomQuestions = '';
if ($objExercise->hasQuestionListWithRandomCriteria()) {
    $formRandomQuestions = 'disable';
}

$q_cats = Database::get()->queryArray("SELECT * FROM exercise_question_cats WHERE course_id = ?d ORDER BY question_cat_name", $course_id);
$cat_options = "<option value=\"-1\">-- " . js_escape($langQuestionAllCats) . " --</option><option value=\"0\">-- $langQuestionWithoutCat --</option>";
$cat_options_2 = "<option value=\"0\"> ---- </option>";
foreach ($q_cats as $qcat) {
    $cat_options .= "<option value=\"$qcat->question_cat_id\">" . js_escape($qcat->question_cat_name) . "</option>";
    $cat_options_2 .= "<option value=\"$qcat->question_cat_id\">" . js_escape($qcat->question_cat_name) . "</option>";
}

$diff_options = "<option value=\"-1\">-- " . js_escape($langQuestionAllDiffs) . " --</option>"
    . "<option value=\"0\">-- " . js_escape($langQuestionNotDefined) . " --</option>"
    . "<option value=\"1\">" . js_escape($langQuestionVeryEasy) . "</option>"
    . "<option value=\"2\">" . js_escape($langQuestionEasy) . "</option>"
    . "<option value=\"3\">" . js_escape($langQuestionModerate) . "</option>"
    . "<option value=\"4\">" . js_escape($langQuestionDifficult) . "</option>"
    . "<option value=\"5\">" . js_escape($langQuestionVeryDifficult) . "</option>";

// for sorting
$head_content .= "
    <script>
        $(document).ready(function(){
            if (typeof(q_sort) !== 'undefined') {
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
            }
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
     $('#checkboxShuffleQuestions').click(function() {
         if ($(this).is(':checked')) {
            $('#checkboxRandomQuestions').prop('disabled', true);
            $('#inputRandomQuestions').prop('disabled', true);
            $('#divcheckboxRandomQuestions').addClass('not_visible');
         } else {
             $('#inputRandomQuestions').prop('disabled', false);
             $('#checkboxRandomQuestions').prop('disabled', false);
             $('#divcheckboxRandomQuestions').removeClass('not_visible');
         }
     });
     $('#checkboxRandomQuestions').click(function() {
         if ($(this).is(':checked')) {
             $('#checkboxShuffleQuestions').prop('disabled', true);
             $('#divcheckboxShuffleQuestions').addClass('not_visible');
         } else {
             $('#checkboxShuffleQuestions').prop('disabled', false);
             $('#divcheckboxShuffleQuestions').removeClass('not_visible');
         }
     });
    $('.questionSelection').click( function(e){
        e.preventDefault();
        bootbox.dialog({
            title: '".js_escape($langWithCriteria)."',
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
                                            '<input class=\"form-control\" type=\"text\" id=\"q_num\" name=\"q_num\" value=\"\">".js_escape($langQuestions)."' +
                                        '</div>' +
                                    '</div>' +
                            '</form>' +
                        '</div>' +
                    '</div>',
                        buttons: {
                            success: {
                                label: '".js_escape($langSelection)."',
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
            title: '<span class=\"fa fa-random\" style=\"margin-right: 10px; color:grey\"></span>".js_escape($langRandomQuestionsWithCriteria)."',
            message: '<div class=\"row\">' +
                        '<div class=\"col-md-12\">' +
                            '<form class=\"form-horizontal\">' +
                                '<div class=\"row\" style=\"margin-bottom: 10px;\">' +
                                '<span class=\"col-sm-5\">".js_escape($langQuestionDiffGrade)."</span>' +
                                '<span class=\"col-sm-5\">".js_escape($langQuestionCats)."</span>' +
                                '<span class=\"col-sm-2\">".js_escape($langNumQuestions)."</span>' +
                                '</div>'+
                                '<div class=\"form-group\">' +
                                    '<div class=\"col-sm-5\">' +
                                        '<select id=\"difficultyId\" class=\"form-control\">' +
                                            '<option value=\"0\">  ----  </option>' +
                                            '<option value=\"1\">".js_escape($langQuestionVeryEasy)."</option>' +
                                            '<option value=\"2\">".js_escape($langQuestionEasy)."</option>' +
                                            '<option value=\"3\">".js_escape($langQuestionModerate)."</option>' +
                                            '<option value=\"4\">".js_escape($langQuestionDifficult)."</option>' +
                                            '<option value=\"5\">".js_escape($langQuestionVeryDifficult)."</option>' +
                                        '</select>' +
                                    '</div>' +
                                    '<div class=\"col-sm-5\">' +
                                        '<select id=\"categoryId\" class=\"form-control\">$cat_options_2</select>' +
                                    '</div>' +
                                    '<div class=\"col-sm-2\">' +
                                        '<input class=\"form-control\" type=\"text\" id=\"questionRandomDrawn\" value=\"\">' +
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
                        var categoryIdValue = $('select#categoryId').val();
                        var questionRandomDrawnValue = $('input#questionRandomDrawn').val();
                        $.ajax({
                          type: 'POST',
                          url: '',
                          datatype: 'json',
                          data: {
                             action: 'random_criteria',
                             difficultyId: difficultyIdValue,
                             categoryId: categoryIdValue,
                             questionRandomDrawn: questionRandomDrawnValue
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
        if ($objExercise->removeFromList($deleteQuestion)) {
            $nbrQuestions--;
        }
    } else { // random question
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
          'button-class' => 'btn-default',
          'show' => !$randomQuestions),
    array('title' => $langWithoutCriteria,
          'url' => "question_pool.php?course=$course_code&amp;fromExercise=$exerciseId",
          'icon' => 'fa-bank'),
    array('title' => $langWithCriteria,
          'class' => 'questionSelection',
          'url' => "#",
          'icon' => 'fa-bank')),
    false);

if ($nbrQuestions) {
    $info_random_text = '';
    if ($randomQuestions > 0) {
        $info_random_text = "<small><span class='help-block'>$langShow $randomQuestions $langFromRandomQuestions</span></small>";
    }

    $tool_content .= "<div id='RandomizationForm' class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId'>
                <div class='form-group'>
                    <div class='col-sm-12'>
                        <div class='checkbox' id='divcheckboxShuffleQuestions'>
                            <label class='form-control-static'>
                                 <input id='checkboxShuffleQuestions' type='checkbox' name='enableShuffleQuestions' value='1' ".(($shuffleQuestions == 1)? 'checked' : '').">
                                 $langShuffleQuestions
                             </label>
                         </div>
                     </div>
                     <div class='col-sm-12'>
                        <div class='checkbox' id='divcheckboxRandomQuestions'>
                            <label class='form-control-static'>
                             <input id='checkboxRandomQuestions'type='checkbox' name='enableRandomQuestions' value='1' ".(($randomQuestions > 0)? 'checked' : '').">
                             $langChooseRandomQuestions</label>
                             <input id='inputRandomQuestions' type='text' name='numberOfRandomQuestions' value=".(($randomQuestions > 0)? $randomQuestions : '').">&nbsp;$langsQuestions
                         </div>
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

    $questionList = $objExercise->selectQuestionList();
    $limit = 0;
    foreach ($questionList as $id) {
        $objQuestionTmp = new Question();
        if (!is_array($id)) {
            $objQuestionTmp->read($id);
            $q = Database::get()->querySingle("SELECT id FROM exercise_with_questions
                                        WHERE exercise_id = ?d
                                      AND question_id = ?d", $exerciseId, $id);
            $ewq_id = $q->id;
        } else {
            $next_limit = $limit+1;
            $q = Database::get()->querySingle("SELECT id FROM exercise_with_questions
                                        WHERE exercise_id = ?d
                                      AND question_id IS NULL
                                          ORDER BY q_position
                                          ASC
                                          LIMIT $limit,$next_limit", $exerciseId);
            $ewq_id = $q->id;
            $limit++;
        }
        $aType = $objQuestionTmp->selectType();
        $question_difficulty_legend = $objQuestionTmp->selectDifficultyIcon($objQuestionTmp->selectDifficulty());
        $question_category_legend = $objQuestionTmp->selectCategoryName($objQuestionTmp->selectCategory());
        $addon = '';
        if ($objQuestionTmp->selectType() == MATCHING) {
            $sql = Database::get()->querySingle("SELECT * FROM exercise_answer WHERE question_id = ?d", $id);
            if (!$sql) $addon = "&amp;htopic=4";
        }

        if (is_array($id)) {
            if ($id['criteria'] == 'difficulty') {
                next($id);
                $number = key($id);
                $difficulty = $id[$number];
                $legend = "<span class='fa fa-random' style='margin-right:10px; color: grey'></span><em>$number $langFromRandomDifficultyQuestions '" . $objQuestionTmp->selectDifficultyLegend($difficulty) . "'</em>";
            } else if ($id['criteria'] == 'category') {
                next($id);
                $number = key($id);
                $category = $id[$number];
                $legend = "<span class='fa fa-random' style='margin-right:10px; color: grey'></span><em>$number $langFromRandomCategoryQuestions '" . $objQuestionTmp->selectCategoryName($category) . "'</em>";
            } else if ($id['criteria'] == 'difficultycategory') {
                next($id);
                $number = key($id);
                $difficulty = $id[$number][0];
                $category = $id[$number][1];
                $legend = "<span class='fa fa-random' style='margin-right:10px; color: grey'></span>
                    <em>$number $langFromRandomDifficultyQuestions '" . $objQuestionTmp->selectDifficultyLegend($difficulty) ."' $langFrom2 '" . $objQuestionTmp->selectCategoryName($category) . "'</em>";
            }
        } else {
            $legend = q_math($objQuestionTmp->selectTitle()) . "<br>
            <small>" . $objQuestionTmp->selectTypeLegend($aType) . "&nbsp;$question_difficulty_legend $question_category_legend</small>";
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
        if (isset($number) and $number > 0) {
            $i = $i + $number;
            $number = 0;
        } else {
            $i++;
        }
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
