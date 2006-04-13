<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE RESULT <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script gets informations from the script "exercise_submit.php",
 * through the session, and calculates the score of the student for
 * that exercise.
 *
 * Then it shows results at screen.
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

session_start();

// answer types
define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);

$require_current_course = TRUE;
$langFiles='exercice';
include '../../include/init.php';

$nameTools = $langExercices;
$navigation[]= array ("url"=>"exercice.php", "name"=> $langExercices);

begin_page($nameTools);

// latex support
include_once "$webDir"."/modules/latexrender/latex.php";


include('../../include/lib/textLib.inc.php');

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

// if the above variables are empty or incorrect, stops the script
if(!is_array($exerciseResult) || !is_array($questionList) || !is_object($objExercise))
{
	die($langExerciseNotFound);
}

$exerciseTitle=$objExercise->selectTitle();

?>

<h3>
  <?php echo stripslashes($exerciseTitle).' : '.$langResult; ?>
</h3>

<form method="get" action="exercice.php">

<?php
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
?>

<table width="100%" border="0" cellpadding="3" cellspacing="2">
<tr bgcolor="#E6E6E6">
  <td colspan="<?php echo $colspan; ?>">
	<?php echo $langQuestion.' '.($i+1); ?>
  </td>
</tr>
<tr>
  <td colspan="<?php echo $colspan; ?>">
	<?php echo $questionName; ?>
  </td>
</tr>

<?
		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
		{
?>

<tr>
  <td width="5%" valign="top" align="center" nowrap="nowrap">
	<small><i><?php echo $langChoice; ?></i></small>
  </td>
  <td width="5%" valign="top" nowrap="nowrap">
	<small><i><?php echo $langExpectedChoice; ?></i></small>
  </td>
  <td width="45%" valign="top">
	<small><i><?php echo $langAnswer; ?></i></small>
  </td>
  <td width="45%" valign="top">
	<small><i><?php echo $langComment; ?></i></small>
  </td>
</tr>

<?
	}
	elseif($answerType == FILL_IN_BLANKS)
	{
?>

<tr>
  <td>
	<small><i><?= $langAnswer; ?></i></small>
  </td>
</tr>

<?
	} else {
?>

<tr>
  <td width="50%">
	<small><i><?= $langElementList; ?></i></small>
  </td>
  <td width="50%">
	<small><i><?= $langCorrespondsTo; ?></i></small>
  </td>
</tr>

<?
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
				case MATCHING :			if($answerCorrect)
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
?>

<tr>
  <td width="5%" align="center">
	<img src="../../images/<?php echo ($answerType == UNIQUE_ANSWER)?'radio':'checkbox'; echo $studentChoice?'_on':'_off'; ?>.gif" border="0">
  </td>
  <td width="5%" align="center">
	<img src="../../images/<?php echo ($answerType == UNIQUE_ANSWER)?'radio':'checkbox'; echo $answerCorrect?'_on':'_off'; ?>.gif" border="0">
  </td>
  <td width="45%">
	<?php echo $answer; ?>
  </td>
  <td width="45%">
	<?php if($studentChoice) echo nl2br(make_clickable($answerComment)); else echo '&nbsp;'; ?>
  </td>
</tr>

<?
	} elseif($answerType == FILL_IN_BLANKS) {
?>

<tr>
  <td>
	<?php echo nl2br($answer); ?>
  </td>
</tr>

<?
	} else {
?>

<tr>
  <td width="50%">
	<?= $answer; ?>
  </td>
  <td width="50%">
	<? echo $choice[$answerId]; ?> / <font color="green"><b><?php echo $matching[$answerCorrect]; ?></b></font>
  </td>
</tr>

<?
		}
	}
}	// end for()
?>

<tr>
  <td colspan="<?= $colspan; ?>" align="right">
	<b><?php echo "$langScore : $questionScore/$questionWeighting"; ?></b>
  </td>
</tr>
</table>

<?
		// destruction of Answer
		unset($objAnswerTmp);

		$i++;

		$totalWeighting+=$questionWeighting;
	}	// end foreach()
?>

<table width="100%" border="0" cellpadding="3" cellspacing="2">
<tr>
  <td align="center">
	<b><?php echo "$langYourTotalScore $totalScore/$totalWeighting"; ?> !</b>
  </td>
</tr>
<tr>
  <td align="center">
    <br>
	<input type="submit" value="<?= $langFinish; ?>">
  </td>
</tr>
</table>

</form>

<br>

<?
/*******************************/
/* Tracking of results         */
/*******************************/

// if tracking is enabled
if(isset($is_trackingEnabled))
{
	include($includePath.'/libs/events.lib.inc.php');

	event_exercice($objExercise->selectId(),$totalScore,$totalWeighting);
}



?>
