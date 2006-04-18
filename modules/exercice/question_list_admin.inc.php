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

		/*>>>>>>>>>>>>>>>>>>>> QUESTION LIST ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage the question list
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// moves a question up in the list
if(isset($moveUp))
{
	$objExercise->moveUp($moveUp);
	$objExercise->save();
}

// moves a question down in the list
if(isset($moveDown))
{
	$objExercise->moveDown($moveDown);
	$objExercise->save();
}

// deletes a question from the exercise (not from the data base)
if(isset($deleteQuestion))
{
	// construction of the Question object
	$objQuestionTmp=new Question();

	// if the question exists
	if($objQuestionTmp->read($deleteQuestion))
	{
		$objQuestionTmp->delete($exerciseId);

		// if the question has been removed from the exercise
		if($objExercise->removeFromList($deleteQuestion))
		{
			$nbrQuestions--;
		}
	}

	// destruction of the Question object
	unset($objQuestionTmp);
}
?>

<hr size="1" noshade="noshade">

<a href="<?php echo $PHP_SELF; ?>?newQuestion=yes"><?php echo $langNewQu; ?></a> | <a href="question_pool.php?fromExercise=<?php echo $exerciseId; ?>"><?php echo $langGetExistingQuestion; ?></a>

<br><br>

<b><?php echo $langQuestionList; ?></b>

<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">

<?php
if($nbrQuestions)
{
	$questionList=$objExercise->selectQuestionList();

	$i=1;

	foreach($questionList as $id)
	{
		$objQuestionTmp=new Question();

		$objQuestionTmp->read($id);
?>

<tr>
  <td><?php echo "$i. ".$objQuestionTmp->selectTitle(); ?><br><small><?php echo $aType[$objQuestionTmp->selectType()-1]; ?></small></td>
</tr>
<tr>
  <td>
	<a href="<?php echo $PHP_SELF; ?>?editQuestion=<?php echo $id; ?>"><img src="../../images/edit.gif" border="0" align="absmiddle" alt="<?php echo $langModify; ?>"></a>
	<a href="<?php echo $PHP_SELF; ?>?deleteQuestion=<?php echo $id; ?>" onclick="javascript:if(!confirm('<?php echo addslashes(htmlspecialchars($langConfirmYourChoice)); ?>')) return false;"><img src="../../images/delete.gif" border="0" align="absmiddle" alt="<?php echo $langDelete; ?>"></a>

<?php
		if($i != 1)
		{
?>

	<a href="<?php echo $PHP_SELF; ?>?moveUp=<?php echo $id; ?>"><img src="../../images/up.gif" border="0" align="absmiddle" alt="<?php echo $langMoveUp; ?>"></a>

<?php
		}

		if($i != $nbrQuestions)
		{
?>

	<a href="<?php echo $PHP_SELF; ?>?moveDown=<?php echo $id; ?>"><img src="../../images/down.gif" border="0" align="absmiddle" alt="<?php echo $langMoveDown; ?>"></a>

<?php
		}
?>

  </td>
</tr>

<?php
		$i++;

		unset($objQuestionTmp);
	}
}

if(!isset($i))
{
?>

<tr>
  <td><?php echo $langNoQuestion; ?></td>
</tr>

<?php
}
?>

</table>
