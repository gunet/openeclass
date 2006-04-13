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

		/*>>>>>>>>>>>>>>>>>>>> QUESTION POOL <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows administrators to manage questions and add them
 * into their exercises.
 *
 * One question can be in several exercises.
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

$require_current_course = TRUE;
$langFiles='exercice';
include ('../../include/init.php');

$nameTools=$langQuestionPool;
$navigation[]=array("url" => "exercice.php","name" => $langExercices);

begin_page($nameTools);

$is_allowedToEdit=$is_adminOfCourse;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

// maximum number of questions on a same page
$limitQuestPage=50;

if($is_allowedToEdit)
{
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
		if($objQuestionTmp->read($recup))
		{
			// adds the exercise ID represented by $fromExercise into the list of exercises for the current question
			$objQuestionTmp->addToList($fromExercise);
		}

		// destruction of the Question object
		unset($objQuestionTmp);

		// adds the question ID represented by $recup into the list of questions for the current exercise
		$objExercise->addToList($recup);

		header("Location: admin.php?editQuestion=$recup");
		exit();
	}
}


// if admin of course
if($is_allowedToEdit)
{
?>

<form method="get" action="<?= $PHP_SELF; ?>">
<input type="hidden" name="fromExercise" value="<?= @$fromExercise; ?>">
<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
<tr>
  <td colspan="<?php echo $fromExercise?2:3; ?>" align="right">
	<?php echo $langFilter; ?> : <select name="exerciseId">
	<option value="0">-- <?php echo $langAllExercises; ?> --</option>
	<option value="-1" <?php if(isset($exerciseId) && $exerciseId == -1) echo 'selected="selected"'; ?>>-- <?php echo $langOrphanQuestions; ?> --</option>

<?php
	$sql="SELECT id,titre FROM `$TBL_EXERCICES` WHERE id<>'$fromExercise' ORDER BY id";
	$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);

	// shows a list-box allowing to filter questions
	while($row=mysql_fetch_array($result))
	{
?>

	<option value="<?php echo $row['id']; ?>" <?php if(isset($exerciseId) && $exerciseId == $row['id']) echo 'selected="selected"'; ?>><?php echo $row['titre']; ?></option>

<?php
	}
?>

    </select> <input type="submit" value="<?php echo $langOk; ?>">
  </td>
</tr>

<?php
	@$from=$page*$limitQuestPage;

	// if we have selected an exercise in the list-box 'Filter'
	if(isset($exerciseId) && $exerciseId > 0)
	{
		$sql="SELECT id,question,type FROM `$TBL_EXERCICE_QUESTION`,`$TBL_QUESTIONS` WHERE question_id=id AND exercice_id='$exerciseId' ORDER BY q_position LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
	}
	// if we have selected the option 'Orphan questions' in the list-box 'Filter'
	elseif(isset($exerciseId) && $exerciseId == -1)
	{
		$sql="SELECT id,question,type FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` ON question_id=id WHERE exercice_id IS NULL ORDER BY question LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
	}
	// if we have not selected any option in the list-box 'Filter'
	else
	{		
		@$sql="SELECT id,question,type FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` ON question_id=id WHERE exercice_id IS NULL OR exercice_id<>'$fromExercise' GROUP BY id ORDER BY question LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);

		// forces the value to 0
		$exerciseId=0;
	}

	$nbrQuestions=mysql_num_rows($result);
?>

<tr>
  <td colspan="<?php echo $fromExercise?2:3; ?>">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
	  <td>

<?php
	if(isset($fromExercise))
	{
?>

		<a href="admin.php">&lt;&lt; <?php echo $langGoBackToEx; ?></a>

<?php
	}
	else
	{
?>

		<a href="admin.php?newQuestion=yes"><?php echo $langNewQu; ?></a>

<?php
	}
?>

	  </td>
	  <td align="right">

<?php
	if(isset($page))
	{
?>

	<small><a href="<?php echo $PHP_SELF; ?>?exerciseId=<?php echo $exerciseId; ?>&fromExercise=<?php echo $fromExercise; ?>&page=<?php echo ($page-1); ?>">&lt;&lt; <?php echo $langPreviousPage; ?></a></small> |

<?php
	}
	elseif($nbrQuestions > $limitQuestPage)
	{
?>

	<small>&lt;&lt; <?php echo $langPreviousPage; ?> |</small>

<?php
	}

	if($nbrQuestions > $limitQuestPage)
	{
?>

	<small><a href="<?php echo $PHP_SELF; ?>?exerciseId=<?php echo $exerciseId; ?>&fromExercise=<?php echo $fromExercise; ?>&page=<?php echo ($page+1); ?>"><?php echo $langNextPage; ?> &gt;&gt;</a></small>

<?php
	}
	elseif(isset($page))
	{
?>

	<small><?php echo $langNextPage; ?> &gt;&gt;</small>

<?php
	}
?>

	  </td>
	</tr>
	</table>
  </td>
</tr>
<tr bgcolor="#E6E6E6">

<?php
	if(isset($fromExercise))
	{
?>

  <td width="80%" align="center"><?php echo $langQuestion; ?></td>
  <td width="20%" align="center"><?php echo $langReuse; ?></td>

<?php
	}
	else
	{
?>

  <td width="60%" align="center"><?php echo $langQuestion; ?></td>
  <td width="20%" align="center"><?php echo $langModify; ?></td>
  <td width="20%" align="center"><?php echo $langDelete; ?></td>

<?php
	}
?>

</tr>

<?php
	$i=1;

	while($row=mysql_fetch_array($result))
	{
		// if we come from the exercise administration to get a question, doesn't show the question already used by that exercise
		if(!isset($fromExercise) || !$objExercise->isInList($row['id']))
		{
?>

<tr>
  <td><a href="admin.php?editQuestion=<?= $row['id']; ?>&fromExercise=<?= @$fromExercise; ?>"><?= $row['question']; ?></a></td>
  <td align="center">

<?php
			if(!isset($fromExercise))
			{
?>

	<a href="admin.php?editQuestion=<?= $row['id']; ?>"><img src="../../images/edit.gif" border="0" alt="<?php echo $langModify; ?>"></a>

<?php
			}
			else
			{
?>

	<a href="<?= $PHP_SELF; ?>?recup=<?= $row['id']; ?>&fromExercise=<?= $fromExercise; ?>"><img src="../../images/enroll.gif" border="0" alt="<?= $langReuse; ?>"></a>

<?php
			}
?>

  </td>

<?php
			if(!isset($fromExercise))
			{
?>

  <td align="center">
    <a href="<?= $PHP_SELF; ?>?exerciseId=<?= $exerciseId; ?>&delete=<?= $row['id']; ?>" 
onclick="javascript:if(!confirm('<?php echo addslashes(htmlspecialchars($langConfirmYourChoice)); ?>')) return false;"><img src="../../images/delete.gif" border="0" alt="<?= $langDelete; ?>"></a>
  </td>

<?php
			}
?>

</tr>

<?php
			// skips the last question, that is only used to know if we have or not to create a link "Next page"
			if($i == $limitQuestPage)
			{
				break;
			}

			$i++;
		}
	}

	if(!$nbrQuestions)
	{
?>

<tr>
  <td colspan="<?php echo $fromExercise?2:3; ?>"><?php echo $langNoQuestion; ?></td>
</tr>

<?php
	}
?>

</table>
</form>

<?php
}
// if not admin of course
else
{
	echo $langNotAllowed;
}

?>
