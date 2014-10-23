<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

$require_current_course = TRUE;

include '../../include/baseTheme.php';

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

$nameTools = $langQuestionPool;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

if (isset($_GET['fromExercise'])) {
    $objExercise = new Exercise();
    $fromExercise = intval($_GET['fromExercise']);
    $objExercise->read($fromExercise);
    $navigation[] = array("url" => "admin.php?course=$course_code&amp;exerciseId=$fromExercise", "name" => $langExerciseManagement);
}

if (isset($_GET['exerciseId'])) {
    $exerciseId = intval($_GET['exerciseId']);
}

// maximum number of questions on a same page
define('QUESTIONS_PER_PAGE', 15);

if (!isset($_GET['page'])) {
    $page = 0;
} else {
    $page = $_GET['page'];
}
if ($is_editor) {
    // deletes a question from the data base and all exercises
    if (isset($_GET['delete'])) {
        $delete = intval($_GET['delete']);
        // construction of the Question object
        $objQuestionTmp = new Question();
        // if the question exists
        if ($objQuestionTmp->read($delete)) {
            // deletes the question from all exercises
            $objQuestionTmp->delete();
        }
        // destruction of the Question object
        unset($objQuestionTmp);
        //Session::set_flashdata($message, $class);
        redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code".(isset($fromExercise) ? "&amp;fromExercise=$fromExercise" : "")."&exerciseId=$exerciseId");
    }
    // gets an existing question and copies it into a new exercise
    elseif (isset($_GET['recup']) && isset($fromExercise)) {
        $recup = intval($_GET['recup']);
        // construction of the Question object
        $objQuestionTmp = new Question();
        // if the question exists
        if ($objQuestionTmp->read($recup)) {
            // adds the exercise ID into the list of exercises for the current question
            $objQuestionTmp->addToList($fromExercise);
        }
        // destruction of the Question object
        unset($objQuestionTmp);
        // adds the question ID into the list of questions for the current exercise
        $objExercise->addToList($recup);
        Session::Messages($langQuestionReused, 'alert-success');
        redirect_to_home_page("modules/exercise/question_pool.php?course=$course_code".(isset($fromExercise) ? "&fromExercise=$fromExercise" : "")."&exerciseId=$exerciseId");        
    }
    
    if (isset($fromExercise)) {
        $action_bar_options[] = array('title' => $langGoBackToEx,
                'url' => "admin.php?course=$course_code&amp;exerciseId=$fromExercise",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
         );        
    } else {
        $action_bar_options = array(
            array('title' => $langNewQu,
                'url' => "admin.php?course=$course_code&amp;newQuestion=yes",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success')
         );          
    }
    $tool_content .= action_bar($action_bar_options);
    
    if (isset($fromExercise)) {
        $result = Database::get()->queryArray("SELECT id, title FROM `exercise` WHERE course_id = ?d AND id <> ?d ORDER BY id", $course_id, $fromExercise);
    } else {
        $result = Database::get()->queryArray("SELECT id, title FROM `exercise` WHERE course_id = ?d ORDER BY id", $course_id);
    }    
    //Start of filtering Component
    $tool_content .= "<form class='form-horizontal' role='form' name='qfilter' method='get' action='$_SERVER[SCRIPT_NAME]'><input type='hidden' name='course' value='$course_code'>
                        <div class='form-group'>
                            <label for='exerciseTitle' class='col-sm-2 control-label'>$langFilter:</label>
                            <div class='col-sm-4'>
                                <select onChange = 'document.qfilter.submit();' name='exerciseId' class='form-control'>
                                  <option value = '0'>-- $langAllExercises --</option>
                                  <option value = '-1' ".(isset($exerciseId) && $exerciseId == -1 ? "selected='selected'": "").">-- $langOrphanQuestions --</option>";

    foreach ($result as $row) {
        $tool_content .= "
             <option value='" . $row->id . "' ".(isset($exerciseId) && $exerciseId == $row->id ? "selected='selected'":"").">$row->title</option>\n";
    }   
    $tool_content .= "</select></div></div></form>";      
    //End of filtering Component
    
    if (isset($fromExercise)) {
        $tool_content .= "<input type='hidden' name='fromExercise' value='$fromExercise'>";
    }

    $tool_content .= "<table class='table-default'>";

    $from = $page * QUESTIONS_PER_PAGE;

    // if we have selected an exercise in the list-box 'Filter'
    if (isset($exerciseId) && $exerciseId > 0) {
        if (isset($fromExercise)) {
            $result = Database::get()->queryArray("SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = id WHERE course_id = ?d  AND exercise_id = ?d AND (exercise_id IS NULL OR exercise_id <> ?d AND
                            question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))
                            GROUP BY id ORDER BY question LIMIT ?d, ?d", $course_id, $exerciseId, $fromExercise, $fromExercise, $from, QUESTIONS_PER_PAGE + 1);
        } else {
            $result = Database::get()->queryArray("SELECT id, question, type FROM `exercise_with_questions`, `exercise_question`
                            WHERE course_id = ?d AND question_id = id AND exercise_id = ?d
                            ORDER BY q_position LIMIT ?d, ?d", $course_id, $exerciseId, $from, QUESTIONS_PER_PAGE + 1);
        }
    }
    // if we have selected the option 'Orphan questions' in the list-box 'Filter'
    elseif (isset($exerciseId) && $exerciseId == -1) {
        $result = Database::get()->queryArray("SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
			ON question_id = id WHERE course_id = ?d AND exercise_id IS NULL ORDER BY question
			LIMIT ?d, ?d", $course_id, $from, QUESTIONS_PER_PAGE + 1);
    }
    // if we have not selected any option in the list-box 'Filter'
    else {
        if (isset($fromExercise)) {
            $result = Database::get()->queryArray("SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = id WHERE course_id = ?d AND (exercise_id IS NULL OR exercise_id <> ?d AND
                            question_id NOT IN (SELECT question_id FROM `exercise_with_questions` WHERE exercise_id = ?d))
                            GROUP BY id ORDER BY question LIMIT ?d, ?d", $course_id, $fromExercise, $fromExercise, $from, QUESTIONS_PER_PAGE + 1);
        } else {
            $result = Database::get()->queryArray("SELECT id, question, type FROM `exercise_question` LEFT JOIN `exercise_with_questions`
                            ON question_id = id WHERE course_id = ?d
                            GROUP BY id ORDER BY question LIMIT ?d, ?d", $course_id, $from, QUESTIONS_PER_PAGE + 1);            
        }
        // forces the value to 0
        $exerciseId = 0;
    }
    $nbrQuestions = count($result);

    $tool_content .= "
	<tr>
	  <th>$langQuesList</th>
          <th class='text-center'>".icon('fa-gears')."</th>
        </tr>";
    $i = 1;
    foreach ($result as $row) {
        $exercise_ids = Database::get()->queryArray("SELECT exercise_id FROM `exercise_with_questions` WHERE question_id = ?d", $row->id);
        if (isset($fromExercise) || !is_object(@$objExercise) || !$objExercise->isInList($row->id)) {
            if ($row->type == 1) {
                $answerType = $langUniqueSelect;
            } elseif ($row->type == 2) {
                $answerType = $langMultipleSelect;
            } elseif ($row->type == 3) {
                $answerType = $langFillBlanks;
            } elseif ($row->type == 4) {
                $answerType = $langMatching;
            } elseif ($row->type == 5) {
                $answerType = $langTrueFalse;
            } elseif ($row->type == 6) {
                $answerType = $langFreeText;
            }
            $tool_content .= "<tr>";
            if (!isset($fromExercise)) {
                $tool_content .= "<td><a ".((count($exercise_ids)>0)? "class='warnLink' data-toggle='modal' data-target='#modalWarning' data-remote='false'" : "")."href=\"admin.php?course=$course_code&amp;editQuestion=" . $row->id . "&amp;fromExercise=\">" . q($row->question) . "</a><br/>" . $answerType . "</td>";
            } else {
                $tool_content .= "<td><a href=\"admin.php?course=$course_code&amp;editQuestion=" . $row->id . "&amp;fromExercise=" . $fromExercise . "\">" . q($row->question) . "</a><br/>" . $answerType . "</td>";
            }
            $tool_content .= "<td class='option-btn-cell'>".
            action_button(array(
                    array('title' => $langModify,
                          'url' => "admin.php?course=$course_code&amp;editQuestion=" . $row->id,
                          'icon-class' => 'warnLink',
                          'icon-extra' => ((count($exercise_ids)>0)? " data-toggle='modal' data-target='#modalWarning' data-remote='false'" : ""),
                          'icon' => 'fa-edit',
                          'show' => !isset($fromExercise)),
                    array('title' => $langDelete,
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;delete=$row->id",
                          'icon' => 'fa-times',
                          'class' => 'delete',
                          'confirm' => $langConfirmYourChoice,
                          'show' => !isset($fromExercise)),
                    array('title' => $langReuse,
                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;recup=$row->id&amp;fromExercise=".(isset($fromExercise) ? $fromExercise : '')."&amp;exerciseId=$exerciseId",
                          'icon' => 'fa-plus-square',
                          'show' => isset($fromExercise))
                ))."</td></tr>";       
            // skips the last question,only used to know if we must create a link "Next page"
            if ($i == QUESTIONS_PER_PAGE) {
                break;
            }
            $i++;
        }
    }
    if (!$nbrQuestions) {
        $tool_content .= "<tr>";
        if (isset($fromExercise) && ($fromExercise)) {
            $tool_content .= "<td colspan='3'>";
        } else {
            $tool_content .= "<td colspan='4'>";
        }
        $tool_content .= $langNoQuestion . "</td></tr>";
    }
    // questions pagination
    $numpages = intval(($nbrQuestions-1) / QUESTIONS_PER_PAGE);
    if ($numpages > 0) {
        $tool_content .= "<tr>";
        if (isset($fromExercise)) {
            $tool_content .= "<th align='right' colspan='3'>";
        } else {
            $tool_content .= "<th align='right' colspan='4'>";
        }
        if ($page > 0) {
            $prevpage = $page - 1;
            if (isset($fromExercise)) {
                $tool_content .= "<small>&lt;&lt; <a href=\"" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;exerciseId=" . $exerciseId .
                        "&amp;fromExercise=" . $fromExercise .
                        "&amp;page=" . $prevpage . "\">" . $langPreviousPage . "</a>&nbsp;</small>";
            } else {
                $tool_content .= "<small>&lt;&lt;
				<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$prevpage'>$langPreviousPage</a></small>";
            }
        }
        if ($page < $numpages) {
            $nextpage = $page + 1;
            if (isset($fromExercise)) {
                $tool_content .= "<small><a href='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;exerciseId=" . $exerciseId .
                        "&amp;fromExercise=" . $fromExercise .
                        "&amp;page=" . $nextpage . "'>" . $langNextPage .
                        "</a> &gt;&gt;</small>";
            } else {
                $tool_content .= "<small>
				<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$nextpage'>$langNextPage</a> &gt;&gt;
				</small>";
            }
        }
    }
    $tool_content .= "</table>";
} else { // if not admin of course
    $tool_content .= $langNotAllowed;
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
        <a href='#' id='modifyOne' class='btn btn-success'>$langModifyInQuestionPool</a>
      </div>
    </div>
  </div>
</div>    
";
draw($tool_content, 2, null, $head_content);
