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

/**===========================================================================
	showExerciseResult.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================
    @Description: This script is a replicate from
                  exercice/exercise_result.php, but it is modified for the
                  displaying needs of the learning path tool. The core
                  application logic remains the same.

    @Comments:

    @todo:
==============================================================================
*/

require_once('../../exercice/exercise.class.php');
require_once('../../exercice/question.class.php');
require_once('../../exercice/answer.class.php');

session_start();

// answer types
define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$require_current_course = TRUE;
require_once("../../../config/config.php");
require_once('../../../include/init.php');
require_once('../../../include/lib/learnPathLib.inc.php');
require_once('../../../include/lib/textLib.inc.php');

$nameTools = $langExercices;
// calculate time needed to complete the exercise
if (isset($_SESSION['exeStartTime']))
{
   $timeToCompleteExe =  time() - $_SESSION['exeStartTime'];
}

// Destroy cookie
if (!setcookie("marvelous_cookie", "", time()-3600, "/")) {
	header('Location: ../../exercice/exercise_redirect.php');
	exit();
}

// latex support
include_once "$webDir"."/modules/latexrender/latex.php";

echo "<html>"."\n"
    .'<head>'."\n"
    .'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">'."\n"
    .'<link href="../../../template/classic/tool_content.css" rel="stylesheet" type="text/css" />'."\n"
    .'<link href="../tool.css" rel="stylesheet" type="text/css" />'."\n"
    .'<title>'.$langExercices.'</title>'."\n"
    .'</head>'."\n"
    .'<body style="margin: 2px;">'."\n"
    .'<div align="left">';


// if the above variables are empty or incorrect, stops the script
if(!is_array($exerciseResult) || !is_array($questionList) || !is_object($objExercise))
{
	die($langExerciseNotFound);
}

$exerciseTitle=$objExercise->selectTitle();
$exerciseId=$objExercise->selectId();

// ------------ calculata exercise constrains ----------------------------
$eid=$objExercise->selectId();
mysql_select_db($currentCourseID);
$sql="SELECT RecordStartDate FROM `exercise_user_record` WHERE eid='$eid' AND uid='$uid'";
$result=db_query($sql, $currentCourseID);
$attempt = count($result);
$row=mysql_fetch_array($result);
$RecordStartDate = ($RecordStartTime_temp = $row[count($result)-1]);
$RecordStartTime_temp = mktime(substr($RecordStartTime_temp, 11,2),substr($RecordStartTime_temp, 14,2),substr($RecordStartTime_temp, 17,2),substr($RecordStartTime_temp, 5,2),substr($RecordStartTime_temp, 8,2),substr($RecordStartTime_temp, 0,4));
$exerciseTimeConstrain=$objExercise->selectTimeConstrain();
$exerciseTimeConstrain = $exerciseTimeConstrain*60;
$RecordEndDate = ($SubmitDate = date("Y-m-d H:i:s"));
$SubmitDate = mktime(substr($SubmitDate, 11,2),substr($SubmitDate, 14,2),substr($SubmitDate, 17,2),substr($SubmitDate, 5,2),substr($SubmitDate, 8,2),substr($SubmitDate, 0,4));
if (!$exerciseTimeConstrain) {
  $exerciseTimeConstrain = (7 * 24 * 60 * 60);
}
$OnTime = $RecordStartTime_temp + $exerciseTimeConstrain - $SubmitDate;

if ($OnTime <= 0)  { // exercise time limit has expired

echo <<<cData
      <br/>
      <table width="99%" class="Question">
      <thead>
      <tr>
        <td class="alert1">${langExerciseExpiredTime}</td>
      </tr>
      </thead>
      </table>
  <h3>${exerciseTitle}</h3>
  <p>${langExerciseExpired}</p>
cData;

exit;

} else {

    echo "
      <table class=\"Exercise\" width=\"99%\">
      <thead>
      <tr>
        <td colspan=\"2\">
        <b>".stripslashes($exerciseTitle)."</b>
        <br/><br/>
        ".stripslashes($exerciseDescription_temp)."
        </td>
      </tr>
      </thead>
      </table>";


echo "
	<form method=\"GET\" action=\"backFromExercise.php\">".
	"<input type=\"hidden\" name=\"op\" value=\"finish\">";

	$i=$totalScore=$totalWeighting=0;

	// for each question
	foreach($questionList as $questionId)
	{
		// gets the student choice for this question
		$choice=@$exerciseResult[$questionId];

		// creates a temporary Question object
		$objQuestionTmp=new Question();

		$objQuestionTmp->read($questionId);

		$questionName=$objQuestionTmp->selectTitle();
		$questionName=latex_content($questionName);
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
$iplus = $i+1;
echo <<<cData
      <br/>
      <table width="99%" class="Question">
      <thead>
      <tr>
        <td colspan="${colspan}"><b><u>$langQuestion</u>: $iplus</b></td>
      </tr>
      <tr>
        <td colspan="${colspan}"><b>${questionName}</b><br/>
        <small>${questionDescription_temp}</small><br/><br/></td>
      </tr>
      </thead>
      <tbody>
cData;

		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
		{
echo <<<cData
      <tr>
        <td width="5%" align="center" style="background: #fff;"><b>${langChoice}</b></td>
        <td width="5%" align="center" style="background: #fff;"><b>${langExpectedChoice}</b></td>
        <td width="45%" align="center" style="background: #fff;"><b>${langAnswer}</b></td>
        <td width="45%" align="center" style="background: #fff;"><b>${langComment}</b></td>
      </tr>
cData;

	}
	elseif($answerType == FILL_IN_BLANKS)
	{

echo <<<cData
      <tr>
        <td style="background: #fff;"><b>${langAnswer}</b></td>
      </tr>
cData;

	} else {

echo <<<cData
      <tr>
        <td width="50%" style="background: #fff;"><b>${langElementList}</b></td>
        <td width="50%" style="background: #fff;"><b>${langCorrespondsTo}</b></td>
      </tr>
cData;

		}
		// construction of the Answer object
		$objAnswerTmp=new Answer($questionId);
		$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
		$questionScore=0;
		for($answerId=1;$answerId <= $nbrAnswers;$answerId++)
		{
			$answer=$objAnswerTmp->selectAnswer($answerId);
			$answerComment=$objAnswerTmp->selectComment($answerId);
			$answerCorrect=$objAnswerTmp->isCorrect($answerId);
			$answerWeighting=$objAnswerTmp->selectWeighting($answerId);
			$answer=latex_content($answer);

			switch($answerType)
			{
				// for unique answer
				case UNIQUE_ANSWER :	$studentChoice=($choice == $answerId)?1:0;

								if($studentChoice)
								{
							  	$questionScore+=$answerWeighting;
								$totalScore+=$answerWeighting;
								}
								break;
				// for multiple answers
				case MULTIPLE_ANSWER :	$studentChoice=@$choice[$answerId];
							if($studentChoice)
							{
								$questionScore+=$answerWeighting;
								$totalScore+=$answerWeighting;
							}
							break;
				// for fill in the blanks
				case FILL_IN_BLANKS :	// splits text and weightings that are joined with the character '::'
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
						if(($pos = strpos($temp,'[')) === false)
							{
							// adds the end of the text
							$answer.=$temp;
							break;
							}
					// adds the piece of text that is before the blank and ended by [
							$answer.=substr($temp,0,$pos+1);
							$temp=substr($temp,$pos+1);
					// quits the loop if there are no more blanks
						if(($pos = strpos($temp,']')) === false)
							{
							// adds the end of the text
							$answer.=$temp;
							break;
							}
							$choice[$j]=trim(stripslashes($choice[$j]));
					// if the word entered by the student IS the same as the one defined by the professor
						if(strtolower(substr($temp,0,$pos)) == strtolower($choice[$j]))
						{
						// gives the related weighting to the student
						$questionScore+=$answerWeighting[$j];
						// increments total score
						$totalScore+=$answerWeighting[$j];
						// adds the word in green at the end of the string
						$answer.=$choice[$j];
						}
					// else if the word entered by the student IS NOT the same as the one defined by the professor
						elseif(!empty($choice[$j]))
						{
						// adds the word in red at the end of the string, and strikes it
						$answer.='<font color="red"><s>'.$choice[$j].'</s></font>';
						}
						else
						{
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
				case MATCHING :	if($answerCorrect)
						{
							if($answerCorrect == $choice[$answerId])
								{
								$questionScore+=$answerWeighting;
									$totalScore+=$answerWeighting;
									$choice[$answerId]=$matching[$choice[$answerId]];
								}
									elseif(!$choice[$answerId])
									{
									$choice[$answerId]='&nbsp;&nbsp;&nbsp;';
									}
									else
									{
									$choice[$answerId]='<font color="red"><s>'.$matching[$choice[$answerId]].'</s></font>';
									}
								}
								else
								{
									$matching[$answerId]=$answer;
								}
							break;
			}	// end switch()

			if($answerType != MATCHING || $answerCorrect)
			{
				if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
				{
echo <<<cData
      <tr>
        <td width="5%" style="background: #fff;"><div align="center">
        <img src="../../../template/classic/img/
cData;

	if ($answerType == UNIQUE_ANSWER)
		echo "radio";
	else
		echo "checkbox";

	if ($studentChoice)
		echo "_on";
	else
		echo '_off';

	echo <<<cData
		.gif" border="0"></div>
        </td>
        <td width="5%" style="background: #fff;"><div align="center">
cData;


	if ($answerType == UNIQUE_ANSWER)
		echo "<img src=\"../../../template/classic/img/radio";
	else
		echo "<img src=\"../../../template/classic/img/checkbox";
	if ($answerCorrect)
		echo "_on";
	else
		echo "_off";

	echo ".gif\" border=\"0\">";

  echo <<<cData
        </td>
        <td width="45%" style="background: #fff;">${answer}</td>
        <td width="45%" style="background: #fff;">
cData;

if($studentChoice)
	echo nl2br(make_clickable($answerComment));
else
	echo '&nbsp;';

  echo "</td></tr>";

	} elseif($answerType == FILL_IN_BLANKS) {

echo "
      <tr>
        <td style=\"background: #fff;\">".nl2br($answer)."</td>
      </tr>";

	} else {

echo <<<cData
      <tr>
        <td width="50%" style="background: #fff;">${answer}</td>
        <td width="50%" style="background: #fff;">${choice[$answerId]} / <font color="green"><b>${matching[$answerCorrect]}</b></font></td>
      </tr>
cData;

		}
	}
}	// end for()

echo <<<cData
      <tr>
        <td colspan="${colspan}" class="score">
        ${langQuestionScore}: <b>${questionScore}/${questionWeighting}</b>
        </td>
      </tr>
      </tbody>
      </table>
cData;

		// destruction of Answer
		unset($objAnswerTmp);
		$i++;
		$totalWeighting+=$questionWeighting;
	}	// end foreach()



/////////////////////////////////////////////////////////////////////////////
// UPDATE results to DB
/////////////////////////////////////////////////////////////////////////////

$sql="SELECT eurid FROM `exercise_user_record` WHERE eid='$eid' AND uid='$uid'";
  $result = mysql_query($sql);
  $row=mysql_fetch_array($result);
  $x = $row[count($result)-1];
  $eurid = $row[count($result)-1];
  // record end/results of exercise
  $sql="UPDATE `exercise_user_record` SET RecordEndDate='$RecordEndDate',TotalScore='$totalScore', TotalWeighting='$totalWeighting', attempt='$attempt' WHERE eurid='$eurid'";
  db_query($sql,$currentCourseID);

echo <<<cData
      <br/>
      <table width="99%" class="Exercise">
      <thead>
      <tr>
        <td class="score">
        ${langYourTotalScore}: <b>${totalScore}/${totalWeighting}</b>
        </td>
      </tr>
      </thead>
      </table>

      <br/>
      <div align="center">
        <input type="submit" value="${langFinish}">
      </div>


      <br/></form><br>
cData;

// record progression
// update raw in DB to keep the best one, so update only if new raw is better  AND if user NOT anonymous
if($uid)
{
	// exercices can have a negative score, we don't accept that in LP
	// so if totalScore is negative use 0 as result
	$totalScore = max($totalScore, 0);
	if ( $totalWeighting != 0 )
	{
			$newRaw = @round($totalScore/$totalWeighting*100);
	}
	else
	{
			$newRaw = 0;
	}

	$scoreMin = 0;
	$scoreMax = $totalWeighting;
	// need learningPath_module_id and raw_to_pass value
	$sql = "SELECT LPM.`raw_to_pass`, LPM.`learnPath_module_id`, UMP.`total_time`, UMP.`raw`
			FROM `".$TABLELEARNPATHMODULE."` AS LPM, `".$TABLEUSERMODULEPROGRESS."` AS UMP
			WHERE LPM.`learnPath_id` = '".(int)$_SESSION['path_id']."'
			AND LPM.`module_id` = '".(int)$_SESSION['lp_module_id']."'
			AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
			AND UMP.`user_id` = ".(int)$uid;
	$query = db_query($sql);
	$row = mysql_fetch_array($query);

	$scormSessionTime = seconds_to_scorm_time($timeToCompleteExe);

	// build sql query
	$sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."` SET ";
	// if recorded score is less then the new score => update raw, credit and status

	if ($row['raw'] < $totalScore)
	{
		// update raw
		$sql .= "`raw` = $totalScore,";
		// update credit and statut if needed ( score is better than raw_to_pass )
		if ($newRaw >= $row['raw_to_pass'])
		{
			$sql .= "`credit` = 'CREDIT',`lesson_status` = 'PASSED',";
		}
		else // minimum raw to pass needed to get credit
		{
			$sql .= "`credit` = 'NO-CREDIT',`lesson_status` = 'FAILED',";
		}
	}// else don't change raw, credit and lesson_status

	// default query statements
	$sql .= "	`scoreMin` 	= " . (int)$scoreMin . ",
				`scoreMax` 	= " . (int)$scoreMax . ",
				`total_time`	= '".addScormTime($row['total_time'], $scormSessionTime)."',
				`session_time`	= '".$scormSessionTime."'
				WHERE `learnPath_module_id` = ". (int)$row['learnPath_module_id']."
				AND `user_id` = " . (int)$uid . "";
	db_query($sql);
	}
}
echo "</div></body></html>"."\n";
?>
