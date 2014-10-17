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
$head_content .= "
<script>
  $(function() {
    $('.warnLink').click( function(e){
          var modidyAllLink = $(this).attr('href');
          var modifyOneLink = modidyAllLink.concat('&clone=true');
          $('a#modifyAll').attr('href', modidyAllLink);
          $('a#modifyOne').attr('href', modifyOneLink); 
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
        array('title' => $langGetExistingQuestion,
            'url' => "question_pool.php?course=$course_code&amp;fromExercise=$exerciseId",
            'icon' => 'fa-bank',
            'level' => 'primary-label'
        )       
    ));  

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
			<td> " . q($objQuestionTmp->selectTitle()) . "<br />
			" . $aType[$objQuestionTmp->selectType() - 1] . "</td>
			<td class='option-btn-cell'>".            
                    action_button(array(
                        array('title' => $langModify,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;editQuestion=$id",
                              'icon-class' => 'warnLink',
                              'icon-extra' => $objQuestionTmp->selectNbrExercises()>1? "data-toggle='modal' data-target='#modalWarning' data-remote='false'" : "",
                              'icon' => 'fa-edit'),
                        array('title' => $langDelete,
                              'url' => "?course=$course_code&amp;exerciseId=$exerciseId&amp;deleteQuestion=$id",
                              'icon' => 'fa-times',
                              'class' => 'delete',
                              'confirm' => $langConfirmYourChoice,
                              'show' => !isset($fromExercise)),
                        array('title' => $langUp,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;moveUp=$id",
                              'icon' => 'fa-arrow-up',
                              'show' => $i != 1
                            ),
                        array('title' => $langDown,
                              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;moveDown=$id",
                              'icon' => 'fa-arrow-down',
                              'show' => $i != $nbrQuestions
                            )                
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
if (!isset($i)) {
    $tool_content .= "<div class='alert alert-warning'>$langNoQuestion</div>";
}
