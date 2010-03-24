<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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

// answer types
define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);

$require_current_course = TRUE;
$guest_allowed = true;
include '../../include/baseTheme.php';
$tool_content = "";

$nameTools = $langExercicesResult;
$navigation[]= array ("url"=>"exercice.php", "name"=> $langExercices);

// latex support
include_once "$webDir"."/modules/latexrender/latex.php";
include('../../include/lib/textLib.inc.php');
// support for math symbols
include('../../include/phpmathpublisher/mathpublisher.php');
$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

// if the above variables are empty or incorrect, stops the script
if(!is_array($exerciseResult) || !is_array($questionList) || !is_object($objExercise)) {
	$tool_content .= $langExerciseNotFound;
	draw($tool_content, 2, 'exercice');
	exit();
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
$exerciseDescription_temp = mathfilter($exerciseDescription_temp, 12, "../../courses/mathimg/");
$displayResults=$objExercise->selectResults();
$displayScore=$objExercise->selectScore(); 

$tool_content .= "<table class=\"Exercise\" width=\"99%\"><thead><tr>
<td colspan=\"2\"><b>".stripslashes($exerciseTitle)."</b>
<br/><br/>".stripslashes($exerciseDescription_temp)."
</td></tr></thead></table>";

$tool_content .= "<form method='GET' action='exercice.php'>";

$i=$totalScore=$totalWeighting=0;

// for each question

foreach($questionList as $questionId) {
	// gets the student choice for this question
	$choice=@$exerciseResult[$questionId];

	// creates a temporary Question object
	$objQuestionTmp=new Question();
	$objQuestionTmp->read($questionId);

	$questionName=$objQuestionTmp->selectTitle();
	$questionName=latex_content($questionName);
	$questionDescription=$objQuestionTmp->selectDescription();
	$questionDescription=latex_content($questionDescription);
	$questionDescription_temp = nl2br(make_clickable($questionDescription));
	$questionDescription_temp = mathfilter($questionDescription_temp, 12, "../../courses/mathimg/");
	$questionWeighting=$objQuestionTmp->selectWeighting();
	$answerType=$objQuestionTmp->selectType();

	// destruction of the Question object
	unset($objQuestionTmp);

	if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
	{
		$colspan=4;
	}
	elseif($answerType == MATCHING)
	{
		$colspan=2;
	}
	else
	{
		$colspan=1;
	}
	$iplus=$i+1;
	$tool_content .= "<br/><table width='99%' class='Question'>
	<thead>
	<tr><td colspan='${colspan}'><b><u>$langQuestion</u>: $iplus</b></td>
	</tr>
	<tr>
	<td colspan='${colspan}'><b>$questionName</b><br/>
	<small>$questionDescription_temp</small><br/><br/></td>
	</tr>
	</thead><tbody>";

	$questionScore=0;

	if ($displayResults == 1) {

		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
			$tool_content .= "<tr>
			<td width='5%' align='center' style='background: #fff;'><b>${langChoice}</b></td>
			<td width='5%' align='center' style='background: #fff;'><b>${langExpectedChoice}</b></td>
			<td width='45%' align='center' style='background: #fff;'><b>${langAnswer}</b></td>
			<td width='45%' align='center' style='background: #fff;'><b>${langComment}</b></td>
			</tr>";
		} elseif($answerType == FILL_IN_BLANKS) {
			$tool_content .= "<tr>
			<td style='background: #fff;'><b>${langAnswer}</b></td>
			</tr>";
		} else {
			$tool_content .= "<tr>
			<td width='50%' style='background: #fff;'><b>${langElementList}</b></td>
			<td width='50%' style='background: #fff;'><b>${langCorrespondsTo}</b></td></tr>";
		}
	}
			// construction of the Answer object
		$objAnswerTmp=new Answer($questionId);
		$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
		
		for($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
			$answer=$objAnswerTmp->selectAnswer($answerId);
			$answerComment=$objAnswerTmp->selectComment($answerId);
			$answerCorrect=$objAnswerTmp->isCorrect($answerId);
			$answerWeighting=$objAnswerTmp->selectWeighting($answerId);
			// support for math symbols
			$answer = mathfilter($answer, 12, "../../courses/mathimg/");
			$answerComment = mathfilter($answerComment, 12, "../../courses/mathimg/");
	
			switch($answerType)
			{
				// for unique answer
				case UNIQUE_ANSWER :	$studentChoice=($choice == $answerId)?1:0;
					if($studentChoice) {
						$questionScore+=$answerWeighting;
						$totalScore+=$answerWeighting;
					}
					break;
				// for multiple answers
				case MULTIPLE_ANSWER :	$studentChoice=@$choice[$answerId];
					if($studentChoice) {
						$questionScore+=$answerWeighting;
						$totalScore+=$answerWeighting;
						}
					break;
				// for fill in the blanks
				case FILL_IN_BLANKS : // splits text and weightings that are joined with the char '::'
						list($answer,$answerWeighting)=explode('::',$answer);
						// splits weightings that are joined with a comma
						$answerWeighting=explode(',',$answerWeighting);
						// we save the answer because it will be modified
						$temp=$answer;
						$answer='';
						$j=0;
						// the loop will stop at the end of the text
						while(1)
						{
						// quits the loop if there are no more blanks
						if(($pos = strpos($temp,'[')) === false) {
							// adds the end of the text
							$answer.=$temp;
							break;
						}
					// adds the piece of text that is before the blank and ended by [
						$answer.=substr($temp,0,$pos+1);
						$temp=substr($temp,$pos+1);
						// quits the loop if there are no more blanks
						if(($pos = strpos($temp,']')) === false) {
							// adds the end of the text
							$answer.=$temp;
							break;
						}
						$choice[$j]=trim(stripslashes($choice[$j]));
					// if the word entered is the same as the one defined by the professor
					if(strtolower(substr($temp,0,$pos)) == strtolower($choice[$j])) {
						// gives the related weighting to the student
						$questionScore+=$answerWeighting[$j];
						// increments total score
						$totalScore+=$answerWeighting[$j];
						// adds the word in green at the end of the string
						$answer.=$choice[$j];
					}
				// else if the word entered is not the same as the one defined by the professor
						elseif(!empty($choice[$j])) {
							// adds the word in red at the end of the string, and strikes it
							$answer.='<font color="red"><s>'.$choice[$j].'</s></font>';
						} else {
							// adds a tabulation if no word has been typed by the student
							$answer.='&nbsp;&nbsp;&nbsp;';
						}
						// adds the correct word, followed by ] to close the blank
						$answer.=' / <font color="green"><b>'.substr($temp,0,$pos).'</b></font>]';
						$j++;
						$temp=substr($temp,$pos+1);
						}
					break;
				// for matching
				case MATCHING :	if($answerCorrect) {
							if($answerCorrect == $choice[$answerId]) {
								$questionScore+=$answerWeighting;
								$totalScore+=$answerWeighting;
								$choice[$answerId]=$matching[$choice[$answerId]];
							}
							elseif(!$choice[$answerId]) {
								$choice[$answerId]='&nbsp;&nbsp;&nbsp;';
							} else {
								$choice[$answerId]='<font color="red">
								<s>'.$matching[$choice[$answerId]].'</s>
								</font>';
							}
						} else {
							$matching[$answerId]=$answer;
						}
						break;
			}	// end switch()
			if ($displayResults == 1) { 
				if($answerType != MATCHING || $answerCorrect) {
					if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
						$tool_content .= "<tr><td width='5%' style='background: #fff;'>
						<div align='center'>
						<img src='../../template/classic/img/";
		
						if ($answerType == UNIQUE_ANSWER)
							$tool_content .= "radio";
						else
							$tool_content .= "checkbox";
		
						if ($studentChoice)
							$tool_content .= "_on";
						else
							$tool_content .= '_off';
			
						$tool_content .= ".gif' border='0'></div></td>
						<td width='5%' style='background: #fff;'><div align='center'>";
		
						if ($answerType == UNIQUE_ANSWER)
							$tool_content .= "<img src=\"../../template/classic/img/radio";
						else
							$tool_content .= "<img src=\"../../template/classic/img/checkbox";
						if ($answerCorrect)
							$tool_content .= "_on";
						else	
							$tool_content .= "_off";	
						$tool_content .= ".gif\" border=\"0\"></div>";	
						$tool_content .= "</td><td width='45%' style='background: #fff;'>${answer}</td>
						<td width='45%' style='background: #fff;'>";
		
						if ($studentChoice) {
							$tool_content .= nl2br(make_clickable($answerComment)); 
						} else { 
							$tool_content .= '&nbsp;';
						} 
					
					$tool_content .= "</td></tr>";
		
					} elseif($answerType == FILL_IN_BLANKS) {
						$tool_content .= "<tr>
						<td style=\"background: #fff;\">".nl2br($answer)."</td></tr>";
					} else {
						$tool_content .= "<tr><td width='50%' style='background: #fff;'>${answer}</td>
						<td width='50%' style='background: #fff;'>${choice[$answerId]} / 
						<font color='green'><b>${matching[$answerCorrect]}</b></font></td></tr>";
					}
				} 
			} // end of if
		}	// end for()
	 if ($displayScore == 1) {
		$tool_content .= "<tr><td colspan='$colspan' class='score'>
		$langQuestionScore: <b>$questionScore/$questionWeighting</b>
		</td></tr>";
	}
	$tool_content .= "</tbody></table>";
	// destruction of Answer
	unset($objAnswerTmp);
	$i++;
	$totalWeighting+=$questionWeighting;
}	// end foreach()

// update db with results
$eid=$objExercise->selectId();
mysql_select_db($currentCourseID);

$sql="SELECT RecordStartDate FROM `exercise_user_record` WHERE eid='$eid' AND uid='$uid'";
$result=db_query($sql);
$attempt = count($result);

$sql="SELECT MAX(eurid) FROM `exercise_user_record` WHERE eid='$eid' AND uid='$uid'";
$result = db_query($sql);
$row= mysql_fetch_row($result);
$eurid = $row[0];

// record results of exercise
$sql="UPDATE exercise_user_record SET TotalScore='$totalScore', TotalWeighting='$totalWeighting',
	attempt='$attempt' WHERE eurid='$eurid'";
db_query($sql, $currentCourseID);

if ($displayScore == 1) {
	$tool_content .= "<br/><table width='99%' class='Exercise'><thead><tr>
	<td class='score'>$langYourTotalScore: <b>$totalScore/$totalWeighting</b>
	</td></tr>
	</thead></table>
	";
}
$tool_content .= "<br/><div align='center'>
<input type='submit' value='${langFinish}'>
</div>
<br/></form><br>";

draw($tool_content, 2, 'exercice');
?>
