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

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage an exercise
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// the exercise form has been submitted
if(isset($submitExercise))
{
	$exerciseTitle=trim($exerciseTitle);
	$exerciseDescription=trim($exerciseDescription);
	@$randomQuestions=$randomQuestions?$questionDrawn:0;

	// no title given
	if(empty($exerciseTitle))
	{
		$msgErr=$langGiveExerciseName;
	}
	else
	{
		$objExercise->updateTitle($exerciseTitle);
		$objExercise->updateDescription($exerciseDescription);
		$objExercise->updateType($exerciseType);
		
		$objExercise->updateStartDate($exerciseStartDate);
		$objExercise->updateEndDate($exerciseEndDate);
		$objExercise->updateTimeConstrain($exerciseTimeConstrain);
		$objExercise->updateAttemptsAllowed($exerciseAttemptsAllowed);
		
		$objExercise->setRandom($randomQuestions);
		$objExercise->save();

		// reads the exercise ID (only usefull for a new exercise)
		$exerciseId=$objExercise->selectId();

		unset($modifyExercise);
	}
}
else
{
	$exerciseTitle=$objExercise->selectTitle();
	$exerciseDescription=$objExercise->selectDescription();
	$exerciseType=$objExercise->selectType();
	$exerciseStartDate=$objExercise->selectStartDate();
	$exerciseEndDate=$objExercise->selectEndDate();
	$exerciseTimeConstrain=$objExercise->selectTimeConstrain();
	$exerciseAttemptsAllowed=$objExercise->selectAttemptsAllowed();
	$randomQuestions=$objExercise->isRandom();
}

// shows the form to modify the exercise
if(isset($modifyExercise))
{
?>

<form method="post" action="<?php echo $PHP_SELF; ?>?modifyExercise=<?php echo $modifyExercise; ?>">
<table border="0" cellpadding="5">

<?php
	if(!empty($msgErr))
	{
?>

<tr>
  <td colspan="2">
	<table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
	<tr>
	  <td><?php echo $msgErr; ?></td>
	</tr>
	</table>
  </td>
</tr>

<?php
	}
?>

<tr>
  <td><?php echo $langExerciseName; ?> :</td>
  <td><input type="text" name="exerciseTitle" size="50" maxlength="200" 
  	value="<?php echo htmlspecialchars($exerciseTitle); ?>" style="width:400px;"></td>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseDescription; ?> :</td>
  <td><textarea wrap="virtual" name="exerciseDescription" cols="50" rows="4" style="width:400px;"><?php echo htmlspecialchars($exerciseDescription); ?></textarea></td>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseType; ?> :</td>
  <td><input type="radio" name="exerciseType" value="1" <?php if($exerciseType <= 1) echo 'checked="checked"'; ?>> <?php echo $langSimpleExercise; ?><br>
      <input type="radio" name="exerciseType" value="2" <?php if($exerciseType >= 2) echo 'checked="checked"'; ?>> <?php echo $langSequentialExercise; ?></td>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseStart; ?> :</td>
  <td><input type="text" name="exerciseStartDate" value="<?php echo htmlspecialchars($exerciseStartDate); ?>" 
  	size="22" maxlength="19"> 
  (<?php echo $langExerciseEg; ?> 1977-06-29 12:00:00)</td>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseEnd; ?> :</td>
  <td><input type="text" name="exerciseEndDate" value="<?php echo htmlspecialchars($exerciseEndDate); ?>" 
  	size="22" maxlength="19"> 
  (<?php echo $langExerciseEg; ?> 1977-06-29 12:00:00)</td>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseConstrain; ?> :</td>
  <td><input type="text" name="exerciseTimeConstrain" size="3" maxlength="3" 
  	value="<?php echo htmlspecialchars($exerciseTimeConstrain); ?>"> 
  	<?php echo $langExerciseConstrainUnit; ?> (<?php echo $langExerciseConstrainExplanation; ?>)</td>
</tr>
<tr>
  <td valign="top"><?php echo $langExerciseAttemptsAllowed; ?> :</td>
  <td><input type="text" name="exerciseAttemptsAllowed" size="2" maxlength="2" 
  	value="<?php echo htmlspecialchars($exerciseAttemptsAllowed); ?>"> 
  	<?php echo $langExerciseAttemptsAllowedUnit; ?> (<?php echo $langExerciseAttemptsAllowedExplanation; ?>)</td>
</tr>
<?php
	if($exerciseId && $nbrQuestions)
	{
?>

<tr>
  <td valign="top"><?php echo $langRandomQuestions; ?> :</td>
  <td><input type="checkbox" name="randomQuestions" value="1" <?php if($randomQuestions) echo 'checked="checked"'; ?>> <?php echo $langYes; ?>, <?php echo $langTake; ?>
    <select name="questionDrawn">

<?php
		for($i=1;$i <= $nbrQuestions;$i++)
		{
?>

	<option value="<?php echo $i; ?>" <?php if((isset($formSent) && $questionDrawn == $i) || (!isset($formSent) && ($randomQuestions == $i || ($randomQuestions <= 0 && $i == $nbrQuestions)))) echo 'selected="selected"'; ?>><?php echo $i; ?></option>

<?php
		}
?>

	</select> <?php echo strtolower($langQuestions).' '.$langAmong.' '.$nbrQuestions; ?>
  </td>
</tr>

<?php
	}
?>

<tr>
  <td colspan="2" align="center">
	<input type="submit" name="submitExercise" value="<?php echo $langOk; ?>">
	&nbsp;&nbsp;<input type="submit" name="cancelExercise" value="<?php echo $langCancel; ?>">
	
  </td>
</tr>
</table>
</form>

<?php
}
else
{
?>

<h3>
  <?php echo $exerciseTitle; ?>
</h3>

<blockquote>
	<table border="0">
		<tr>
		  <td valign="top"><?php echo $langExerciseDescription; ?> :</td>
		  <td><?php echo nl2br($exerciseDescription); ?></td>
		</tr>
		<tr>
		  <td valign="top"><?php echo $langExerciseStart; ?> :</td>
		  <td><?php echo $exerciseStartDate; ?></td>
		</tr>
		<tr>
		  <td valign="top"><?php echo $langExerciseEnd; ?> :</td>
		  <td><?php echo $exerciseEndDate; ?></td>
		</tr>
		<tr>
		  <td valign="top"><?php echo $langExerciseConstrain; ?> :</td>
		  <td><?php echo $exerciseTimeConstrain; ?></td>
		</tr>
		<tr>
		  <td valign="top"><?php echo $langExerciseAttemptsAllowed; ?> :</td>
		  <td><?php echo $exerciseAttemptsAllowed; ?></td>
		</tr>
	</table>
</blockquote>

<a href="<?php echo $PHP_SELF; ?>?modifyExercise=yes"><img src="../../images/edit.gif" border="0" align="absmiddle" alt="<?php echo $langModify; ?>"></a>

<?php
}
?>
