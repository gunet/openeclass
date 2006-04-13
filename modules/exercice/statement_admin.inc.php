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

		/*>>>>>>>>>>>>>>>>>>>> STATEMENT ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage statement of questions
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

// the question form has been submitted
if(isset($submitQuestion))
{
	$questionName=trim($questionName);
	$questionDescription=trim($questionDescription);

	// no name given
	if(empty($questionName))
	{
		$msgErr=$langGiveQuestion;
	}
	// checks if the question is used in several exercises
	elseif($exerciseId && !isset($modifyIn) && $objQuestion->selectNbrExercises() > 1)
	{
		$usedInSeveralExercises=1;

        // if a picture has been set
        if($imageUpload_size)
        {
            // saves the picture into a temporary file
            $objQuestion->setTmpPicture($imageUpload);
        }
	}
	else
	{
        // if the user has chosed to modify the question only in the current exercise
        if(isset($modifyIn) && $modifyIn == 'thisExercise')
        {
        	// duplicates the question
        	$questionId=$objQuestion->duplicate();

            // deletes the old question
            $objQuestion->delete($exerciseId);

            // removes the old question ID from the question list of the Exercise object
            $objExercise->removeFromList($modifyQuestion);

            $nbrQuestions--;

            // construction of the duplicated Question
            $objQuestion=new Question();

            $objQuestion->read($questionId);

			// adds the exercise ID into the exercise list of the Question object
            $objQuestion->addToList($exerciseId);

            // construction of the Answer object
            $objAnswerTmp=new Answer($modifyQuestion);

            // copies answers from $modifyQuestion to $questionId
            $objAnswerTmp->duplicate($questionId);

            // destruction of the Answer object
            unset($objAnswerTmp);
        }

		$objQuestion->updateTitle($questionName);
		$objQuestion->updateDescription($questionDescription);
		$objQuestion->updateType($answerType);
		$objQuestion->save($exerciseId);

		$questionId=$objQuestion->selectId();

		// if a picture has been set or checkbox "delete" has been checked
		if($imageUpload_size || isset($deletePicture))
		{
			// we remove the picture
			$objQuestion->removePicture();

			// if we add a new picture
			if($imageUpload_size)
			{
                // image is already saved in a temporary file
                if($modifyIn)
                {
                    $objQuestion->getTmpPicture();
                }
                // saves the picture coming from POST FILE
                else
                {
                    $objQuestion->uploadPicture($imageUpload);
                }
			}
		}

		if($exerciseId)
		{
			// adds the question ID into the question list of the Exercise object
			if($objExercise->addToList($questionId))
			{
				$objExercise->save();

				$nbrQuestions++;
			}
		}

		if($newQuestion)
		{
			// goes to answer administration
			$modifyAnswers=$questionId;
		}
		else
		{
			// goes to exercise viewing
			$editQuestion=$questionId;
		}

		unset($newQuestion,$modifyQuestion);
	}
}
else
{
	// if we don't come here after having cancelled the warning message "used in serveral exercises"
	if(!isset($buttonBack))
	{
		$questionName=$objQuestion->selectTitle();
		$questionDescription=$objQuestion->selectDescription();
		$answerType=$objQuestion->selectType();
	}
}

if((isset($newQuestion) || isset($modifyQuestion)) && !isset($usedInSeveralExercises))
{
	// is picture set ?
	$okPicture=file_exists($picturePath.'/quiz-'.$questionId)?true:false;
?>

<h3>
  <?php echo $questionName; ?>
</h3>

<form enctype="multipart/form-data" method="post" action="<?= $PHP_SELF; ?>?modifyQuestion=<?= @$modifyQuestion; ?>&newQuestion=<?= @$newQuestion; ?>">
<table border="0" cellpadding="5">

<?php
	if($okPicture)
	{
?>

<tr>
  <td colspan="2" align="center"><img src="<?php echo $picturePath.'/quiz-'.$questionId; ?>" border="0"></td>
</tr>

<?php
	}

	// if there is an error message
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
  <td><?php echo $langQuestion; ?> :</td>
  <td><input type="text" name="questionName" size="50" maxlength="200" value="<?php echo htmlspecialchars($questionName); ?>" style="width:400px;"></td>
</tr>
<tr>
  <td valign="top"><?php echo $langQuestionDescription; ?> :</td>
  <td><textarea wrap="virtual" name="questionDescription" cols="50" rows="4" style="width:400px;"><?php echo htmlspecialchars($questionDescription); ?></textarea></td>
</tr>
<tr>
  <td><?php echo $okPicture?$langReplacePicture:$langAddPicture; ?> :</td>
  <td><input type="file" name="imageUpload" size="30" style="width:390px;">

<?php
	if($okPicture)
	{
?>

	<br><input type="checkbox" name="deletePicture" value="1" <?php if(isset($deletePicture)) echo 'checked="checked"'; ?>> <?= $langDeletePicture; ?>

<?php
	}
?>

  </td>
</tr>
<tr>
  <td valign="top"><?php echo $langAnswerType; ?> :</td>
  <td><input type="radio" name="answerType" value="1" <?php if($answerType <= 1) echo 'checked="checked"'; ?>> <?php echo $langUniqueSelect; ?><br>
	  <input type="radio" name="answerType" value="2" <?php if($answerType == 2) echo 'checked="checked"'; ?>> <?php echo $langMultipleSelect; ?><br>
	  <input type="radio" name="answerType" value="4" <?php if($answerType >= 4) echo 'checked="checked"'; ?>> <?php echo $langMatching; ?><br>
	  <input type="radio" name="answerType" value="3" <?php if($answerType == 3) echo 'checked="checked"'; ?>> <?php echo $langFillBlanks; ?>
  </td>
</tr>
<tr>
  <td colspan="2" align="center">
	<input type="submit" name="submitQuestion" value="<?php echo $langOk; ?>">
	&nbsp;&nbsp;<input type="submit" name="cancelQuestion" value="<?php echo $langCancel; ?>">
  </td>
</tr>
</table>
</form>

<?php
}
?>
