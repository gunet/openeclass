<?php // $Id$
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

$require_current_course = TRUE;

include '../../include/baseTheme.php';

$tool_content = "";
$nameTools=$langQuestionPool;
$navigation[]=array("url" => "exercice.php","name" => $langExercices);
if (isset($fromExercise)) {
	$navigation[]= array ("url" => "admin.php?exerciseId=$fromExercise", "name" => $langExerciseManagement);
}

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

// maximum number of questions on a same page
$limitQuestPage = 15;
if (!isset($page)) {
	$page = 0;
} else {
	$page = $_GET['page'];
}
if($is_adminOfCourse) {
	// deletes a question from the data base and all exercises
	if(isset($delete))
	{
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
	elseif(isset($recup) && $fromExercise)
	{
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
			$sql="SELECT * FROM `$TBL_EXERCICE_QUESTION`,`$TBL_QUESTIONS` 
				WHERE question_id=id AND exercice_id='$exerciseId'";
		} elseif($exerciseId == -1) {
			$sql="SELECT * FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` 
				ON question_id=id WHERE exercice_id IS NULL";
		}
	} else {
		$sql = "SELECT * from`$TBL_QUESTIONS`";
	}
	$result = db_query($sql, $currentCourseID);
	$num_of_questions = mysql_num_rows($result);	

	$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\"><li>";
	if(isset($fromExercise)) {
		$tool_content .= "<a href=\"admin.php\">&lt;&lt; ".$langGoBackToEx."</a>";
	} else {
		$tool_content .= "<a href=\"admin.php?newQuestion=yes\">".$langNewQu."</a>";
	}
	
	$tool_content .= "</li></ul></div>";

	$tool_content .= "<form method='get' action='$_SERVER[PHP_SELF]'>";
	if (isset($fromExercise)) {
		$tool_content .= "<input type='hidden' name='fromExercise' value='$fromExercise'>";
	}
	
	$tool_content .= "<table width='99%' class='FormData'><thead><tr>";
	$tool_content .= "<td align=\"right\" class=\"right\">";
	$tool_content .= "<b>".$langFilter."</b>:
	<select name=\"exerciseId\" class=\"FormData_InputText\">"."
	<option value=\"0\">-- ".$langAllExercises." --</option>"."
	<option value=\"-1\" ";
		
	if(isset($exerciseId) && $exerciseId == -1) 
		$tool_content .= "selected=\"selected\""; 
	$tool_content .= ">-- ".$langOrphanQuestions." --</option>";
	
	mysql_select_db($currentCourseID);
	if (isset($fromExercise)) {
		$sql="SELECT id,titre FROM `$TBL_EXERCICES` WHERE id <> '$fromExercise' ORDER BY id";
	} else {
		$sql="SELECT id,titre FROM `$TBL_EXERCICES` ORDER BY id";
	}
	$result = mysql_query($sql);
	
	// shows a list-box allowing to filter questions
	while($row=mysql_fetch_array($result)) {
		$tool_content .= "<option value=\"".$row['id']."\"";
		if(isset($exerciseId) && $exerciseId == $row['id']) 
			$tool_content .= "selected=\"selected\"";
		$tool_content .= ">".$row['titre']."</option>";
	}
	$tool_content .= "</select><input type='submit' value='$langQuestionView'></td></tr></thead></table>";

	$from = $page*$limitQuestPage;
	
	// if we have selected an exercise in the list-box 'Filter'
	if(isset($exerciseId) && $exerciseId > 0)
	{
		$sql="SELECT id,question,type FROM `$TBL_EXERCICE_QUESTION`,`$TBL_QUESTIONS` 
			WHERE question_id=id AND exercice_id='$exerciseId' 
			ORDER BY q_position LIMIT $from,".($limitQuestPage+1);
		$result = mysql_query($sql);
	}
	// if we have selected the option 'Orphan questions' in the list-box 'Filter'
	elseif(isset($exerciseId) && $exerciseId == -1)
	{
		$sql="SELECT id,question,type FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` 
			ON question_id=id WHERE exercice_id IS NULL ORDER BY question 
			LIMIT $from,".($limitQuestPage+1);
		$result = mysql_query($sql);
	}
	// if we have not selected any option in the list-box 'Filter'
	else
	{		
		@$sql="SELECT id,question,type FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` 
			ON question_id=id WHERE exercice_id IS NULL OR exercice_id<>'$fromExercise' 
			GROUP BY id ORDER BY question LIMIT $from,".($limitQuestPage+1);
		$result = mysql_query($sql);
		// forces the value to 0
		$exerciseId = 0;
	}
	$nbrQuestions = mysql_num_rows($result);
	
	$tool_content .= "<table width='99%' class='Question'><tbody><tr>";
	$tool_content .= "<th class='left' width='90%' colspan='2'>$langQuesList</th>";
	
	if(isset($fromExercise)) {
		$tool_content .= "<th width='10%' align='center'>$langReuse</th>";
	} else {
		$tool_content .= "<th width='10%' align='center' colspan='2'>$langActions</th>";
	}

	$tool_content .= "</tr>";
	$i = 1;
	while ($row = mysql_fetch_array($result)) {
		if(isset($fromExercise) || !is_object(@$objExercise) || !$objExercise->isInList($row['id'])) {
			if ($row['type'] <= 1)
				$answerType = $langUniqueSelect;
			elseif ($row['type'] == 2)
				$answerType = $langMultipleSelect;
			elseif ($row['type'] >= 4)
				$answerType = $langMatching;
			elseif ($row['type'] == 3)
				$answerType = $langFillBlanks;
				
			if(!isset($fromExercise)) {
				$tool_content .= "<tr>
				<td width='1%'><div style='padding-top:4px;'>
				<img src='../../template/classic/img/arrow_grey.gif' border='0' alt='bullet'></div></td>
				<td>
				<a href=\"admin.php?editQuestion=".$row['id']."&fromExercise=\"\">".$row['question']."</a><br/><small class='invisible'>".$answerType."</small></td>
				<td><div align='center'><a href=\"admin.php?editQuestion=".$row['id']."\">
				<img src='../../template/classic/img/edit.gif' border='0' title='$langModify'></a></div>";
			} else {
				$tool_content .= "<tr><td width='1%'><div style='padding-top:4px;'>
				<img src='../../template/classic/img/arrow_grey.gif' border='0'></div></td>
				<td><a href=\"admin.php?editQuestion=".$row['id']."&fromExercise=".$fromExercise."\">".$row['question']."</a><br/><small class='invisible'>".$answerType."</small></td>
				<td class='center'><div align='center'>";
				$tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?recup=".$row['id'].
					"&fromExercise=".$fromExercise."\"><img src='../../template/classic/img/enroll.gif' border='0' title='$langReuse'></a>";
			}
			$tool_content .= "</td>";	
			if(!isset($fromExercise)) {
				$tool_content .= "<td><div align='center'>
					<a href=\"".$_SERVER['PHP_SELF']."?exerciseId=".$exerciseId."&delete=".$row['id']."\"". 
					" onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langConfirmYourChoice)).
					"')) return false;\"><img src='../../template/classic/img/delete.gif' border='0' title='$langDelete'></a></div></td>";
			}
			$tool_content .= "</tr>";
			// skips the last question,only used to know if we must create a link "Next page"
			if($i == $limitQuestPage) {
				break;
			}
			$i++;
		}
	}
	if(!$nbrQuestions) {
		$tool_content .= "<tr><td colspan='";
		if (isset($fromExercise)&&($fromExercise)) {
			$tool_content .= "3";
		} else {
			$tool_content .= "4";
		}	
		$tool_content .= "\">".$langNoQuestion."</td></tr>";
	}
	// questions pagination 
	$numpages = intval($num_of_questions / $limitQuestPage);
	if ($numpages > 0) {
		$tool_content .= "<tr><th align='right' colspan='";
		if (isset($fromExercise)) {
			$tool_content .= "3";
		} else {
			$tool_content .= "4";
		}
		$tool_content .= "'><div align='center'>";
		if ($page > 0) {
			$prevpage = $page-1;
			if (isset($fromExercise)) {
				$tool_content .= "<small>&lt;&lt; <a href=\"".$_SERVER['PHP_SELF'].
				"?exerciseId=".$exerciseId.
				"&fromExercise=".$fromExercise.
				"&page=".$prevpage."\">".$langPreviousPage."</a></small>";
			} else {
				$tool_content .= "<small>&lt;&lt; 
				<a href='$_SERVER[PHP_SELF]?page=$prevpage'>$langPreviousPage</a></small>";
			}
		}
		if ($page < $numpages) {
			$nextpage = $page+1;
			if (isset($fromExercise)) {
				$tool_content .= "<small><a href='".$_SERVER['PHP_SELF'].
				"?exerciseId=".$exerciseId.
				"&fromExercise=".$fromExercise.
				"&page=".$nextpage."'>".$langNextPage.
				"</a> &gt;&gt;</small>";
			} else {
				$tool_content .= "<small>
				<a href='$_SERVER[PHP_SELF]?page=$nextpage'>$langNextPage</a> &gt;&gt;
				</small>";
			}
		}
	}	 
	$tool_content .= "</div></th></tr>";
	$tool_content .= "</tbody></table></form>";
} else { // if not admin of course
	$tool_content .= $langNotAllowed;
}
draw($tool_content, 2, 'exercice');
?>
