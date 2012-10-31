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

$TBL_EXERCISE_QUESTION = 'exercise_with_questions';
$TBL_EXERCISE = 'exercise';
$TBL_QUESTION = 'exercise_question';
$TBL_ANSWER = 'exercise_answer';

$nameTools = $langQuestionPool;
$navigation[]=array("url" => "index.php?course=$course_code","name" => $langExercices);
if (isset($_GET['fromExercise'])) {
	$fromExercise = $_GET['fromExercise'];
	$navigation[]= array ("url" => "admin.php?course=$course_code&amp;exerciseId=$fromExercise", "name" => $langExerciseManagement);
}
if (isset($_GET['exerciseId'])) {
	$exerciseId = $_GET['exerciseId'];
}
if (isset($_SESSION['objExercise'])) {
    $objExercise = $_SESSION['objExercise'];
}

// maximum number of questions on a same page
define('QUESTIONS_PER_PAGE', 15);

if (!isset($_GET['page'])) {
	$page = 0;
} else {
	$page = $_GET['page'];
}
if($is_editor) {
	// deletes a question from the data base and all exercises
	if(isset($_GET['delete'])) {
		$delete = intval($_GET['delete']);
		// construction of the Question object
		$objQuestionTmp=new Question();
		// if the question exists
		if($objQuestionTmp->read($delete))
		{
			// deletes the question from all exercises
			$objQuestionTmp->delete();
		}
		// destruction of the Question object
		unset($objQuestionTmp);
	}
	// gets an existing question and copies it into a new exercise
	elseif(isset($_GET['recup']) && isset($fromExercise)) {
		$recup = intval($_GET['recup']);
		// construction of the Question object
		$objQuestionTmp=new Question();
		// if the question exists
		if($objQuestionTmp->read($recup)) {
			// adds the exercise ID into the list of exercises for the current question
			$objQuestionTmp->addToList($fromExercise);
		}
		// destruction of the Question object
		unset($objQuestionTmp);
		// adds the question ID into the list of questions for the current exercise
		$objExercise->addToList($recup);
		$tool_content .= "<div class='success'>$langQuestionReused</div><br>";
	}

	// get the number of available question (used for pagination)
	if (isset($exerciseId) and $exerciseId != 0) {
		if ($exerciseId > 0) {
			$sql = "SELECT * FROM `$TBL_EXERCISE_QUESTION`, `$TBL_QUESTION`
				WHERE course_id = $course_id AND question_id = id AND exercise_id = '$exerciseId'";
		} elseif($exerciseId == -1) {
			$sql = "SELECT * FROM `$TBL_QUESTION` LEFT JOIN `$TBL_EXERCISE_QUESTION`
				ON question_id = id WHERE course_id = $course_id AND exercise_id IS NULL";
		}
	} else {
		$sql = "SELECT * from`$TBL_QUESTION` WHERE course_id = $course_id";
	}
	$result = db_query($sql, $mysqlMainDb);
	$num_of_questions = mysql_num_rows($result);

	$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\"><li>";
	if(isset($fromExercise)) {
		$tool_content .= "<a href=\"admin.php?course=$course_code\">&lt;&lt; ".$langGoBackToEx."</a>";
	} else {
		$tool_content .= "<a href=\"admin.php?course=$course_code&amp;newQuestion=yes\">".$langNewQu."</a>";
	}

	$tool_content .= "</li></ul></div>";

	$tool_content .= "<form name='qfilter' method='get' action='$_SERVER[SCRIPT_NAME]'><input type='hidden' name='course' value='$course_code'>";
	if (isset($fromExercise)) {
		$tool_content .= "<input type='hidden' name='fromExercise' value='$fromExercise'>";
	}

	$tool_content .= "<table width='100%' class='tbl_alt'><tr>";
	if (isset($fromExercise)) {
		$tool_content .= "<td colspan='3'>";
	} else {
		$tool_content .= "<td colspan='4'>";
	}
        $tool_content .= "<div align='right'><b>".$langFilter."</b>:
	   <select onChange='document.qfilter.submit();' name='exerciseId' class='FormData_InputText'>"."
	     <option value=\"0\">-- ".$langAllExercises." --</option>"."
	     <option value=\"-1\" ";

	if(isset($exerciseId) && $exerciseId == -1) {
		$tool_content .= "selected=\"selected\"";
	}
	$tool_content .= ">-- ".$langOrphanQuestions." --</option>\n";

	mysql_select_db($mysqlMainDb);
	if (isset($fromExercise)) {
		$sql = "SELECT id, title FROM `$TBL_EXERCISE` WHERE course_id = $course_id AND id <> '$fromExercise' ORDER BY id";
	} else {
		$sql = "SELECT id, title FROM `$TBL_EXERCISE` WHERE course_id = $course_id ORDER BY id";
	}
	$result = db_query($sql);

	// shows a list-box allowing to filter questions
	while($row = mysql_fetch_array($result)) {
		$tool_content .= "
             <option value=\"".$row['id']."\"";
		if(isset($exerciseId) && $exerciseId == $row['id']) {
			$tool_content .= "selected=\"selected\"";
		}
		$tool_content .= ">". q($row['title']) ."</option>\n";
	}
	$tool_content .= "</select></div></td></tr>\n";

	$from = $page * QUESTIONS_PER_PAGE;

	// if we have selected an exercise in the list-box 'Filter'
	if(isset($exerciseId) && $exerciseId > 0)
	{
		$sql = "SELECT id, question, type FROM `$TBL_EXERCISE_QUESTION`, `$TBL_QUESTION`
			WHERE course_id = $course_id AND question_id = id AND exercise_id = '$exerciseId'
			ORDER BY q_position LIMIT $from,".(QUESTIONS_PER_PAGE+1);
		$result = db_query($sql);
	}
	// if we have selected the option 'Orphan questions' in the list-box 'Filter'
	elseif(isset($exerciseId) && $exerciseId == -1)
	{
		$sql = "SELECT id, question, type FROM `$TBL_QUESTION` LEFT JOIN `$TBL_EXERCISE_QUESTION`
			ON question_id = id WHERE course_id = $course_id AND exercise_id IS NULL ORDER BY question
			LIMIT $from,".(QUESTIONS_PER_PAGE+1);
		$result = db_query($sql);
	}
	// if we have not selected any option in the list-box 'Filter'
	else
	{
		@$sql = "SELECT id, question, type FROM `$TBL_QUESTION` LEFT JOIN `$TBL_EXERCISE_QUESTION`
			ON question_id = id WHERE course_id = $course_id AND (exercise_id IS NULL OR exercise_id <> '$fromExercise')
			GROUP BY id ORDER BY question LIMIT $from,".(QUESTIONS_PER_PAGE+1);
		$result = db_query($sql);
		// forces the value to 0
		$exerciseId = 0;
	}
	$nbrQuestions = mysql_num_rows($result);

	$tool_content .= "
	<tr>
	  <th>&nbsp;</th>
	  <th><div align='left'>$langQuesList</div></th>";

	if(isset($fromExercise)) {
		$tool_content .= "<th width='70'>$langReuse</th>";
	} else {
		$tool_content .= "<th colspan='2' width='30'>$langActions</th>";
	}
	$tool_content .= "</tr>";
	$i = 1;
	while ($row = mysql_fetch_array($result)) {
		if(isset($fromExercise) || !is_object(@$objExercise) || !$objExercise->isInList($row['id'])) {
			if ($row['type'] == 1) {
				$answerType = $langUniqueSelect;
			} elseif ($row['type'] == 2) {
				$answerType = $langMultipleSelect;
			} elseif ($row['type'] == 3) {
				$answerType = $langFillBlanks;
			} elseif ($row['type'] == 4) {
				$answerType = $langMatching;
			} elseif ($row['type'] == 5) {
				$answerType = $langTrueFalse;
			}
			if ($i%2 == 0) {
				$tool_content .= "\n    <tr class='even'>";
			} else {
				$tool_content .= "\n    <tr class='odd'>";
			}

			if(!isset($fromExercise)) {
				$tool_content .= "
				<td width='1'><div style='padding-top:4px;'>
				  <img src='$themeimg/arrow.png' alt='bullet'></div>
				</td>
				<td>
				  <a href=\"admin.php?course=$course_code&amp;editQuestion=".$row['id']."&amp;fromExercise=\">". q($row['question']) ."</a><br/>".$answerType."
				</td>
				<td width='3'><div align='center'><a href=\"admin.php?course=$course_code&amp;editQuestion=".$row['id']."\">
				  <img src='$themeimg/edit.png' title='$langModify' alt='$langModify'></a></div>
				</td>";
			} else {
				$tool_content .= "
				<td width='1'><div style='padding-top:4px;'>
				  <img src='$themeimg/arrow.png'></div>
				</td>
				<td><a href=\"admin.php?course=$course_code&amp;editQuestion=".$row['id']."&amp;fromExercise=".$fromExercise."\">". q($row['question']) ."</a><br/>".$answerType."</td>
				<td class='center'><div align='center'>
				  <a href=\"".$_SERVER['SCRIPT_NAME']."?course=$course_code&amp;recup=".$row['id'].
				"&amp;fromExercise=".$fromExercise."\"><img src='$themeimg/enroll.png' title='$langReuse' /></a>
				</td>";
			}
			//$tool_content .= "</td>";
			if(!isset($fromExercise)) {
				$tool_content .= "
				<td width='3' align='center'>
				  <a href=\"".$_SERVER['SCRIPT_NAME']."?course=$course_code&amp;exerciseId=".$exerciseId."&amp;delete=".$row['id']."\"".
				  " onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langConfirmYourChoice)).
				  "')) return false;\"><img src='$themeimg/delete.png' title='$langDelete' alt='$langDelete'></a>
				</td>";
			}
			$tool_content .= "</tr>";
			// skips the last question,only used to know if we must create a link "Next page"
			if($i == QUESTIONS_PER_PAGE) {
				break;
			}
			$i++;
		}
	}
	if(!$nbrQuestions) {
		$tool_content .= "<tr>";
		if (isset($fromExercise) && ($fromExercise)) {
			$tool_content .= "<td colspan='3'>";
		} else {
			$tool_content .= "<td colspan='4'>";
		}
		$tool_content .= $langNoQuestion."</td></tr>";
	}
	// questions pagination
	$numpages = intval($num_of_questions / QUESTIONS_PER_PAGE);
	if ($numpages > 0) {
		$tool_content .= "<tr>";
		if (isset($fromExercise)) {
			$tool_content .= "<th align='right' colspan='3'>";
		} else {
			$tool_content .= "<th align='right' colspan='4'>";
		}
		if ($page > 0) {
			$prevpage = $page-1;
			if (isset($fromExercise)) {
				$tool_content .= "<small>&lt;&lt; <a href=\"".$_SERVER['SCRIPT_NAME']."?course=$course_code&amp;exerciseId=".$exerciseId.
				"&amp;fromExercise=".$fromExercise.
				"&amp;page=".$prevpage."\">".$langPreviousPage."</a>&nbsp;</small>";
			} else {
				$tool_content .= "<small>&lt;&lt;
				<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$prevpage'>$langPreviousPage</a></small>";
			}
		}
		if ($page < $numpages) {
			$nextpage = $page+1;
			if (isset($fromExercise)) {
				$tool_content .= "<small><a href='".$_SERVER['SCRIPT_NAME']."?course=$course_code&amp;exerciseId=".$exerciseId.
				"&amp;fromExercise=".$fromExercise.
				"&amp;page=".$nextpage."'>".$langNextPage.
				"</a> &gt;&gt;</small>";
			} else {
				$tool_content .= "<small>
				<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$nextpage'>$langNextPage</a> &gt;&gt;
				</small>";
			}
		}
	}
	$tool_content .= "</table></form>";
} else { // if not admin of course
	$tool_content .= $langNotAllowed;
}
draw($tool_content, 2);
