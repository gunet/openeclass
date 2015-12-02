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
/**
 * @file question_list_admin.inc.php
 */
$exerciseId = $_GET['exerciseId'];
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
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
    if ($action == 'add_questions') {
        $qnum = $_POST['qnum'];
        $query_vars[] = $qnum; 
        if ($qnum>0) {
            $q_ids = Database::get()->queryArray("SELECT id FROM exercise_question WHERE course_id = ?d$extraSql AND id NOT IN (SELECT question_id FROM exercise_with_questions WHERE exercise_id = ?d) ORDER BY RAND() LIMIT ?d", $query_vars);
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
$q_cats = Database::get()->queryArray("SELECT * FROM exercise_question_cats WHERE course_id = ?d", $course_id);
$total_questions = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_question WHERE course_id = ?d AND id NOT IN (SELECT question_id FROM exercise_with_questions WHERE exercise_id = ?d)", $course_id, $exerciseId)->count;
$q_number_options = "<option value=\"0\">0 $langQuestions</option>";
for($i=1;$i<=$total_questions;$i++){
    $q_number_options .= "<option value=\"$i\">$i $langQuestions</option>";
}
$diff_options = "<option value=\"-1\">-- $langQuestionAllDiffs --</option>"
                . "<option value=\"0\">-- $langQuestionNotDefined --</option>"
                . "<option value=\"1\">$langQuestionVeryEasy</option>"
                . "<option value=\"2\">$langQuestionEasy</option>"
                . "<option value=\"3\">$langQuestionModerate</option>"
                . "<option value=\"4\">$langQuestionDifficult</option>"
                . "<option value=\"5\">$langQuestionVeryDifficult</option>";
$cat_options = "<option value=\"-1\">-- $langQuestionAllCats --</option><option value=\"0\">-- $langQuestionWithoutCat --</option>";
foreach ($q_cats as $qcat) {
    $cat_options .= "<option value=\"$qcat->question_cat_id\">$qcat->question_cat_name</option>";
}

$head_content .= "
<script>
  $(function() { 
    function initAjaxSelect() {
        $('select#diff, select#cat').bind('change', function () {
            var name = $(this).attr('name');
            if (name == 'difficulty') {              
                var diffValue = $(this).val();
                var catValue = $('select#cat').val();
            } else {
                var catValue = $(this).val();
                var diffValue = $('select#diff').val();
            }
                    $.ajax({
                      type: 'POST',
                      url: '',
                      datatype: 'json',
                      data: {
                         action: 'count_questions',
                         category: catValue, 
                         difficulty: diffValue
                      },
                      success: function(data){
                        data = $.parseJSON(data);
                        var options = '';
                        if (data.results > 0) {
                            for (var i = 1; i <= data.results; i++) {
                                options += '<option value=\"'+i+'\">'+i+' ερωτήσεις</option>';
                            }
                        }
                        
                        $('select#q_num').find('option:not(:first)').remove().end().find('option:first').after(options);
                      },
                      error: function(xhr, textStatus, error){
                          console.log(xhr.statusText);
                          console.log(textStatus);
                          console.log(error);
                      }
                    });              
        });  
    }     
    $('.randomSelection').click( function(e){
        e.preventDefault();
        bootbox.dialog({
            title: '$langSelection $langWithCriteria',
            message: '<div class=\"row\">  ' +
                        '<div class=\"col-md-12\"> ' +
                            '<form> ' +
                            '<h4>$langSelectionRule</h4>' +
                            '<div id=\"rule\" class=\"form-inline well well-sm\">' +
                            '<div class=\"form-group\"> ' +
                            '<select name=\"category\" class=\"form-control\" id=\"cat\">$cat_options</select>' +
                            '</div>' +
                            '<div class=\"form-group\"> ' +
                            '<select name=\"difficulty\" class=\"form-control\" id=\"diff\">$diff_options</select>' +
                            '</div>' +
                            '<div class=\"form-group\"> ' +
                            '<select name=\"q_num\" class=\"form-control\" id=\"q_num\">$q_number_options</select>' +                                
                            '</div></div>' +                            
                            '</div>' +
                            '</form> </div>  </div>',
                        buttons: {
                            success: {
                                label: '$langSelection',
                                className: 'btn-success',
                                callback: function () {
                                    var catValue = $('select#cat').val();
                                    var diffValue = $('select#diff').val();
                                    var qnumValue = $('select#q_num').val();
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
                initAjaxSelect();
           
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
// moves a question up in the list
if (isset($_GET['moveUp'])) {
    $objExercise->moveUp($_GET['moveUp']);
    $objExercise->save();
    redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
}

// moves a question down in the list
if (isset($_GET['moveDown'])) {
    $objExercise->moveDown($_GET['moveDown']);
    $objExercise->save();
    redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
}

// deletes a question from the exercise (not from the data base)
if (isset($_GET['deleteQuestion'])) {
    $deleteQuestion = $_GET['deleteQuestion'];
    // construction of the Question object
    $objQuestionTmp = new Question();
    // if the question exists
    if ($objQuestionTmp->read($deleteQuestion)) {
        $objQuestionTmp->delete($exerciseId);
        // if the question has been removed from the exercise
        if ($objExercise->removeFromList($deleteQuestion)) {
            $nbrQuestions--;
        }
    }
    redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId");
}

    $tool_content .= action_bar(array(
        array('title' => $langNewQu,
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;newQuestion=yes",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ),
        array('title' => $langSelection.' '.$langWithCriteria,
            'class' => 'randomSelection',
            'url' => "#",
            'icon' => 'fa-random',
            'level' => 'primary-label'
        ),          
        array('title' => $langSelection.' '.$langFrom2.' '.$langQuestionPool,
            'url' => "question_pool.php?course=$course_code&amp;fromExercise=$exerciseId",
            'icon' => 'fa-bank',
            'level' => 'primary-label'
        )       
    ), false);  
    
if ($nbrQuestions) {
    $questionList = $objExercise->selectQuestionList();
    $i = 1;
    $tool_content .= "
        <div class='table-responsive'>
	    <table class='table-default'>
	    <tr>
	      <th colspan='2' class='text-left'>$langQuestionList</th>
	      <th class='text-center'>".icon('fa-gears', $langActions)."</th>
	    </tr>";

    foreach ($questionList as $id) {
        $objQuestionTmp = new Question();
        $objQuestionTmp->read($id);
         
        $tool_content .= "<tr>
			<td align='right' width='1'>" . $i . ".</td>
			<td> " . q_math($objQuestionTmp->selectTitle()) . "<br />
			" . $aType[$objQuestionTmp->selectType() - 1] . "</td>
			<td class='option-btn-cell'>".            
                    action_button(array(
                        array('title' => $langEditChange,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;editQuestion=$id",
                                'icon-class' => 'warnLink',
                                'icon-extra' => $objQuestionTmp->selectNbrExercises()>1? "data-toggle='modal' data-target='#modalWarning' data-remote='false'" : "",
                                'icon' => 'fa-edit'),
                        array('title' => $langUp,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;moveUp=$id",
                                'level' => 'primary',
                                'icon' => 'fa-arrow-up',
                                'disabled' => $i == 1
                            ),
                        array('title' => $langDown,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;moveDown=$id",
                                'level' => 'primary',
                                'icon' => 'fa-arrow-down',
                                'disabled' => $i == $nbrQuestions
                            ),
                        array('title' => $langDelete,
                                'url' => "?course=$course_code&amp;exerciseId=$exerciseId&amp;deleteQuestion=$id",
                                'icon' => 'fa-times',
                                'class' => 'delete',
                                'confirm' => $langConfirmYourChoice,
                                'show' => !isset($fromExercise))           
                    ))."</td></tr>";
        $i++;
        unset($objQuestionTmp);
    }
    $tool_content .= "</table></div>";
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
