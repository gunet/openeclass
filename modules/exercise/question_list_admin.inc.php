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
load_js('jquery');
load_js('jquery-ui');
$head_content .= "
<script>
  $(function() {
    $('.modal_warning').click( function(e){
        var link = $(this).parent().attr('href');
        e.preventDefault();
        $('#dialog').dialog({
            resizable: false,
            width: 500,
            modal: true,
            buttons: {
               '$langModifyInAllExercises': function() {
                $( this ).dialog( 'close' );
                window.location = link;
              },            
              '$langModifyInThisExercise': function() {
                $( this ).dialog( 'close' );
                window.location = link.concat('&clone=true');
              }
            }        
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
}

// moves a question down in the list
if (isset($_GET['moveDown'])) {
    $objExercise->moveDown($_GET['moveDown']);
    $objExercise->save();
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


$tool_content .= "
    <div align='left' id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;newQuestion=yes'>$langNewQu</a>
	&nbsp;|&nbsp;
	<a href='question_pool.php?course=$course_code&amp;fromExercise=$exerciseId'>$langGetExistingQuestion</a></li>
      </ul>
    </div>";


if ($nbrQuestions) {
    $questionList = $objExercise->selectQuestionList();
    $i = 1;
    $tool_content .= "
	    <table width='100%' class='tbl_alt'>
	    <tr>
	      <th colspan='2' class='left'>$langQuestionList</th>
	      <th colspan='4' class='center'>$langActions</th>
	    </tr>";

    foreach ($questionList as $id) {
        $objQuestionTmp = new Question();
        $objQuestionTmp->read($id);
        
        if ($i % 2 == 0) {
            $tool_content .= "\n    <tr class='odd'>";
        } else {
            $tool_content .= "\n    <tr class='even'>";
        }

        $tool_content .= "
			<td align='right' width='1'>" . $i . ".</td>
			<td> " . q($objQuestionTmp->selectTitle()) . "<br />
			" . $aType[$objQuestionTmp->selectType() - 1] . "</td>
			<td class='right' width='50'>" .
                icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;exerciseId=$exerciseId&amp;editQuestion=$id", (($objQuestionTmp->selectNbrExercises()>1)? "class='modal_warning'" : "")) . "&nbsp;" .
                icon('fa-times', $langDelete, $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;exerciseId=$exerciseId&amp;deleteQuestion=$id", "onclick=\"if(!confirm('" . js_escape($langConfirmYourChoice) . "')) return false;\"") .
                "</td><td width='20'>";
        if ($i != 1) {
            $tool_content .= icon('fa-arrow-up', $langUp, $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;exerciseId=$exerciseId&amp;moveUp=$id");
        }
        $tool_content .= "</td><td width='20'>";
        if ($i != $nbrQuestions) {
            $tool_content .= icon('fa-arrow-down', $langDown, $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;exerciseId=$exerciseId&amp;moveDown=$id");
        }
        $tool_content .= "</td></tr>";
        $i++;
        unset($objQuestionTmp);
    }
    $tool_content .= "</table>";
}
if (!isset($i)) {
    $tool_content .= "<p class='alert1'>$langNoQuestion</p>";
}
