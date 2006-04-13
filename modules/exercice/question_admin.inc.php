<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$                            |
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

		/*>>>>>>>>>>>>>>>>>>>> QUESTION ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage a question and its answers
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// if the question we are modifying is used in several exercises
if(isset($usedInSeveralExercises))
{
?>

<h3>
  <?php echo $questionName; ?>
</h3>

<form method="post" action="<?php echo $PHP_SELF; ?>?modifyQuestion=<?php echo $modifyQuestion; ?>&modifyAnswers=<?php echo $modifyAnswers; ?>">
<table border="0" cellpadding="5">
<tr>
  <td>

<?php
	// submit question
	if($submitQuestion)
	{
?>

	<input type="hidden" name="questionName" value="<?php echo htmlspecialchars($questionName); ?>">
    <input type="hidden" name="questionDescription" value="<?php echo htmlspecialchars($questionDescription); ?>">
    <input type="hidden" name="imageUpload_size" value="<?php echo $imageUpload_size; ?>">
    <input type="hidden" name="deletePicture" value="<?php echo $deletePicture; ?>">

<?php
	}
	// submit answers
	else
	{
		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
		{
?>

	<input type="hidden" name="correct" value="<?php echo htmlspecialchars(serialize($correct)); ?>">
	<input type="hidden" name="reponse" value="<?php echo htmlspecialchars(serialize($reponse)); ?>">
	<input type="hidden" name="comment" value="<?php echo htmlspecialchars(serialize($comment)); ?>">
	<input type="hidden" name="weighting" value="<?php echo htmlspecialchars(serialize($weighting)); ?>">
	<input type="hidden" name="nbrAnswers" value="<?php echo $nbrAnswers; ?>">

<?php
		}
		elseif($answerType == MATCHING)
		{
?>

	<input type="hidden" name="option" value="<?php echo htmlspecialchars(serialize($option)); ?>">
	<input type="hidden" name="match" value="<?php echo htmlspecialchars(serialize($match)); ?>">
	<input type="hidden" name="sel" value="<?php echo htmlspecialchars(serialize($sel)); ?>">
	<input type="hidden" name="weighting" value="<?php echo htmlspecialchars(serialize($weighting)); ?>">
	<input type="hidden" name="nbrOptions" value="<?php echo $nbrOptions; ?>">
	<input type="hidden" name="nbrMatches" value="<?php echo $nbrMatches; ?>">

<?php
		}
		else
		{
?>

	<input type="hidden" name="reponse" value="<?php echo htmlspecialchars(serialize($reponse)); ?>">
	<input type="hidden" name="comment" value="<?php echo htmlspecialchars(serialize($comment)); ?>">
	<input type="hidden" name="blanks" value="<?php echo htmlspecialchars(serialize($blanks)); ?>">
	<input type="hidden" name="weighting" value="<?php echo htmlspecialchars(serialize($weighting)); ?>">
	<input type="hidden" name="setWeighting" value="1">

<?php
		}
	} // end submit answers
?>

	<input type="hidden" name="answerType" value="<?php echo $answerType; ?>">

    <table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
    <tr>
      <td><?php echo $langUsedInSeveralExercises.' :'; ?></td>
    </tr>
    <tr>
      <td><input type="radio" name="modifyIn" value="allExercises" checked="checked"><?php echo $langModifyInAllExercises; ?></td>
    </tr>
    <tr>
      <td><input type="radio" name="modifyIn" value="thisExercise"><?php echo $langModifyInThisExercise; ?></td>
    </tr>
    <tr>
      <input type="submit" name="<?php echo $submitQuestion?'submitQuestion':'submitAnswers'; ?>" value="<?php echo $langOk; ?>"></td>
      &nbsp;&nbsp;<td align="center"><input type="submit" name="buttonBack" value="<?php echo $langCancel; ?>">
    </tr>
    </table>
  </td>
</tr>
</table>
</form>

<?php
}
else
{
	// selects question informations
	$questionName=$objQuestion->selectTitle();
	$questionDescription=$objQuestion->selectDescription();

	// is picture set ?
	$okPicture=file_exists($picturePath.'/quiz-'.$questionId)?true:false;
?>

<h3>
  <?php echo $questionName; ?>
</h3>

<?php
	// show the picture of the question
	if($okPicture)
	{
?>

<center><img src="<?php echo $picturePath.'/quiz-'.$questionId; ?>" border="0"></center>

<?php
	}
?>

<blockquote>
  <?php echo nl2br($questionDescription); ?>
</blockquote>

<?php
	// doesn't show the edit link if we come from the question pool to pick a question for an exercise
	if(!isset($fromExercise))
	{
?>

<a href="<?php echo $PHP_SELF; ?>?modifyQuestion=<?php echo $questionId; ?>"><img src="../../images/edit.gif" border="0" align="absmiddle" alt="<?php echo $langModify; ?>"></a>

<?php
	}
?>

<hr size="1" noshade="noshade">

<?php
	// we are in an exercise
	if(isset($exerciseId))
	{
?>

<a href="<?php echo $PHP_SELF; ?>">&lt;&lt; <?php echo $langGoBackToQuestionList; ?></a>

<?php
	}
	// we are not in an exercise, so we come from the question pool
	else
	{
?>

<a href="question_pool.php?fromExercise=<?php echo $fromExercise; ?>">&lt;&lt; <?php echo $langGoBackToQuestionPool; ?></a>

<?php
	}
?>

<br><br>

<b><?php echo $langQuestionAnswers; ?></b>

<br><br>

<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
<form>

<?php
	// shows answers of the question. 'true' means that we don't show the question, only answers
	if(!showQuestion($questionId,true))
	{
?>

<tr>
  <td><?php echo $langNoAnswer; ?></td>
</tr>

<?php
	}
?>

</form>
</table>

<br>

<?php
	// doesn't show the edit link if we come from the question pool to pick a question for an exercise
	if(!isset($fromExercise))
	{
?>

<a href="<?php echo $PHP_SELF; ?>?modifyAnswers=<?php echo $questionId; ?>"><img src="../../images/edit.gif" border="0" align="absmiddle" alt="<?php echo $langModify; ?>"></a>

<?php
	}
}
?>
