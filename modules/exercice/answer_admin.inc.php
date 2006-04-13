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

		/*>>>>>>>>>>>>>>>>>>>> ANSWER ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage answers
 *
 * It is included from the script admin.php
 */

// ALLOWED_TO_INCLUDE is defined in admin.php
if(!defined('ALLOWED_TO_INCLUDE'))
{
	exit();
}

$questionName=$objQuestion->selectTitle();
$answerType=$objQuestion->selectType();

$okPicture=file_exists($picturePath.'/quiz-'.$questionId)?true:false;

// if we come from the warning box "this question is used in serveral exercises"
if(isset($modifyIn))
{
	// if the user has chosed to modify the question only in the current exercise
	if($modifyIn == 'thisExercise')
	{
		// duplicates the question
		$questionId=$objQuestion->duplicate();

		// deletes the old question
		$objQuestion->delete($exerciseId);

		// removes the old question ID from the question list of the Exercise object
		$objExercise->removeFromList($modifyAnswers);

		// adds the new question ID into the question list of the Exercise object
		$objExercise->addToList($questionId);

		// construction of the duplicated Question
		$objQuestion=new Question();

		$objQuestion->read($questionId);

		// adds the exercise ID into the exercise list of the Question object
		$objQuestion->addToList($exerciseId);

		// copies answers from $modifyAnswers to $questionId
		$objAnswer->duplicate($questionId);

		// construction of the duplicated Answers
		$objAnswer=new Answer($questionId);
	}

	if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
	{
		$correct=unserialize($correct);
		$reponse=unserialize($reponse);
		$comment=unserialize($comment);
		$weighting=unserialize($weighting);
	}
	elseif($answerType == MATCHING)
	{
		$option=unserialize($option);
		$match=unserialize($match);
		$sel=unserialize($sel);
		$weighting=unserialize($weighting);
	}
	else
	{
		$reponse=unserialize($reponse);
		$comment=unserialize($comment);
		$blanks=unserialize($blanks);
		$weighting=unserialize($weighting);
	}

	unset($buttonBack);
}

// the answer form has been submitted
if(isset($submitAnswers) || isset($buttonBack))
{
	if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
	{
		$questionWeighting=$nbrGoodAnswers=0;

		for($i=1;$i <= $nbrAnswers;$i++)
		{
			$reponse[$i]=trim($reponse[$i]);
			$comment[$i]=trim($comment[$i]);
			$weighting[$i]=intval($weighting[$i]);

			if($answerType == UNIQUE_ANSWER)
			{
				$goodAnswer=($correct == $i)?1:0;
			}
			else
			{
				$goodAnswer=@$correct[$i];
			}

			if($goodAnswer)
			{
				$nbrGoodAnswers++;

				// a good answer can't have a negative weighting
				$weighting[$i]=abs($weighting[$i]);

				// calculates the sum of answer weighting only if it is different from 0 and the answer is good
				if($weighting[$i])
				{
					$questionWeighting+=$weighting[$i];
				}
			}
			else
			{
				// a bad answer can't have a positive weighting
				$weighting[$i]=0-abs($weighting[$i]);
			}

			// checks if field is empty
			if(empty($reponse[$i]))
			{
				$msgErr=$langGiveAnswers;

				// clears answers already recorded into the Answer object
				$objAnswer->cancel();

				break;
			}
			else
			{
				// adds the answer into the object
				$objAnswer->createAnswer($reponse[$i],$goodAnswer,$comment[$i],$weighting[$i],$i);
			}
		}  // end for()

		if(empty($msgErr))
		{
			if(!$nbrGoodAnswers)
			{
				$msgErr=($answerType == UNIQUE_ANSWER)?$langChooseGoodAnswer:$langChooseGoodAnswers;

				// clears answers already recorded into the Answer object
				$objAnswer->cancel();
			}
			// checks if the question is used in several exercises
			elseif($exerciseId && !isset($modifyIn) && $objQuestion->selectNbrExercises() > 1)
			{
				$usedInSeveralExercises=1;
			}
			else
			{
				// saves the answers into the data base
				$objAnswer->save();

				// sets the total weighting of the question
				$objQuestion->updateWeighting($questionWeighting);
				$objQuestion->save($exerciseId);

				$editQuestion=$questionId;

				unset($modifyAnswers);
			}
		}
	}
	elseif($answerType == FILL_IN_BLANKS)
	{
		$reponse=trim($reponse);

		if(!isset($buttonBack))
		{
			if($setWeighting)
			{
				@$blanks=unserialize($blanks);

				// checks if the question is used in several exercises
				if($exerciseId && !isset($modifyIn) && $objQuestion->selectNbrExercises() > 1)
				{
					$usedInSeveralExercises=1;
				}
				else
				{
					// separates text and weightings by '::'
					$reponse.='::';

					$questionWeighting=0;

					foreach($weighting as $val)
					{
						// a blank can't have a negative weighting
						$val=abs($val);

						$questionWeighting+=$val;

						// adds blank weighting at the end of the text
						$reponse.=$val.',';
					}

					$reponse=substr($reponse,0,-1);

					$objAnswer->createAnswer($reponse,0,'',0,'');
					$objAnswer->save();

					// sets the total weighting of the question
					$objQuestion->updateWeighting($questionWeighting);
					$objQuestion->save($exerciseId);

					$editQuestion=$questionId;

					unset($modifyAnswers);
				}
			}
			// if no text has been typed or the text contains no blank
			elseif(empty($reponse))
			{
				$msgErr=$langGiveText;
			}
			elseif(!ereg('\[.+\]',$reponse))
			{
				$msgErr=$langDefineBlanks;
			}
			else
			{
				// now we're going to give a weighting to each blank
				$setWeighting=1;

				unset($submitAnswers);

				// removes character '::' possibly inserted by the user in the text
				$reponse=str_replace('::','',$reponse);

				// we save the answer because it will be modified
				$temp=$reponse;

				// blanks will be put into an array
				$blanks=Array();

				$i=1;

				// the loop will stop at the end of the text
				while(1)
				{
					// quits the loop if there are no more blanks
					if(($pos = strpos($temp,'[')) === false)
					{
						break;
					}

					// removes characters till '['
					$temp=substr($temp,$pos+1);

					// quits the loop if there are no more blanks
					if(($pos = strpos($temp,']')) === false)
					{
						break;
					}

					// stores the found blank into the array
					$blanks[$i++]=substr($temp,0,$pos);

					// removes the character ']'
					$temp=substr($temp,$pos+1);
				}
			}
		}
		else
		{
			unset($setWeighting);
		}
	}
	elseif($answerType == MATCHING)
	{
		for($i=1;$i <= $nbrOptions;$i++)
		{
			$option[$i]=trim($option[$i]);

			// checks if field is empty
			if(empty($option[$i]))
			{
				$msgErr=$langFillLists;

				// clears options already recorded into the Answer object
				$objAnswer->cancel();

				break;
			}
			else
			{
				// adds the option into the object
				$objAnswer->createAnswer($option[$i],0,'',0,$i);
			}
		}

		$questionWeighting=0;

		if(empty($msgErr))
		{
			for($j=1;$j <= $nbrMatches;$i++,$j++)
			{
				$match[$i]=trim($match[$i]);
				$weighting[$i]=abs(intval($weighting[$i]));

				$questionWeighting+=$weighting[$i];

				// checks if field is empty
				if(empty($match[$i]))
				{
					$msgErr=$langFillLists;

					// clears matches already recorded into the Answer object
					$objAnswer->cancel();

					break;
				}
				// check if correct number
				else
				{
					// adds the answer into the object
					$objAnswer->createAnswer($match[$i],$sel[$i],'',$weighting[$i],$i);
				}
			}
		}

		if(empty($msgErr))
		{
			// checks if the question is used in several exercises
			if($exerciseId && !isset($modifyIn) && $objQuestion->selectNbrExercises() > 1)
			{
				$usedInSeveralExercises=1;
			}
			else
			{
				// all answers have been recorded, so we save them into the data base
				$objAnswer->save();

				// sets the total weighting of the question
				$objQuestion->updateWeighting($questionWeighting);
				$objQuestion->save($exerciseId);

				$editQuestion=$questionId;

				unset($modifyAnswers);
			}
		}
	}
}

if(isset($modifyAnswers))
{
	// construction of the Answer object
	$objAnswer=new Answer($questionId);

	session_register('objAnswer');

	if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
	{
		if(!isset($nbrAnswers))
		{
			$nbrAnswers=$objAnswer->selectNbrAnswers();

			$reponse=Array();
			$comment=Array();
			$weighting=Array();

			// initializing
			if($answerType == MULTIPLE_ANSWER)
			{
				$correct=Array();
			}
			else
			{
				$correct=0;
			}

			for($i=1;$i <= $nbrAnswers;$i++)
			{
				$reponse[$i]=$objAnswer->selectAnswer($i);
				$comment[$i]=$objAnswer->selectComment($i);
				$weighting[$i]=$objAnswer->selectWeighting($i);

				if($answerType == MULTIPLE_ANSWER)
				{
					$correct[$i]=$objAnswer->isCorrect($i);
				}
				elseif($objAnswer->isCorrect($i))
				{
					$correct=$i;
				}
			}
		}

		if(isset($lessAnswers))
		{
			$nbrAnswers--;
		}

		if(isset($moreAnswers))
		{
			$nbrAnswers++;
		}

		// minimum 2 answers
		if($nbrAnswers < 2)
		{
			$nbrAnswers=2;
		}
	}
	elseif($answerType == FILL_IN_BLANKS)
	{
		if(!isset($submitAnswers) && !isset($buttonBack))
		{
			if(!isset($setWeighting))
			{
				$reponse=$objAnswer->selectAnswer(1);

				@list($reponse,$weighting)=explode('::',$reponse);

				$weighting=explode(',',$weighting);

				$temp=Array();

				// keys of the array go from 1 to N and not from 0 to N-1
				for($i=0;$i < sizeof($weighting);$i++)
				{
					$temp[$i+1]=$weighting[$i];
				}

				$weighting=$temp;
			}
			elseif(!$modifyIn)
			{
				$weighting=unserialize($weighting);
			}
		}
	}
	elseif($answerType == MATCHING)
	{
		if(!isset($nbrOptions) || !isset($nbrMatches))
		{
			$option=Array();
			$match=Array();
			$sel=Array();

			$nbrOptions=$nbrMatches=0;

			// fills arrays with data from de data base
			for($i=1;$i <= $objAnswer->selectNbrAnswers();$i++)
			{
				// it is a match
				if($objAnswer->isCorrect($i))
				{
					$match[$i]=$objAnswer->selectAnswer($i);
					$sel[$i]=$objAnswer->isCorrect($i);
					$weighting[$i]=$objAnswer->selectWeighting($i);
					$nbrMatches++;
				}
				// it is an option
				else
				{
					$option[$i]=$objAnswer->selectAnswer($i);
					$nbrOptions++;
				}
			}
		}

		if(isset($lessOptions))
		{
			// keeps the correct sequence of array keys when removing an option from the list
			for($i=$nbrOptions+1,$j=1;$nbrOptions > 2 && $j <= $nbrMatches;$i++,$j++)
			{
				$match[$i-1]=$match[$i];
				$sel[$i-1]=$sel[$i];
				$weighting[$i-1]=$weighting[$i];
			}

			unset($match[$i-1]);
			unset($sel[$i-1]);

			$nbrOptions--;
		}

		if(isset($moreOptions))
		{
			// keeps the correct sequence of array keys when adding an option into the list
			for($i=$nbrMatches+$nbrOptions;$i > $nbrOptions;$i--)
			{
				$match[$i+1]=$match[$i];
				$sel[$i+1]=$sel[$i];
				$weighting[$i+1]=$weighting[$i];
			}

			unset($match[$i+1]);
			unset($sel[$i+1]);

			$nbrOptions++;
		}

		if(isset($lessMatches))
		{
			$nbrMatches--;
		}

		if(isset($moreMatches))
		{
			$nbrMatches++;
		}

		// minimum 2 options
		if($nbrOptions < 2)
		{
			$nbrOptions=2;
		}

		// minimum 2 matches
		if($nbrMatches < 2)
		{
			$nbrMatches=2;
		}

	}

	if(!isset($usedInSeveralExercises))
	{
		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
		{
?>

<h3>
  <?php echo $questionName; ?>
</h3>

<form method="post" action="<?php echo $PHP_SELF; ?>?modifyAnswers=<?php echo $modifyAnswers; ?>">
<input type="hidden" name="formSent" value="1">
<input type="hidden" name="nbrAnswers" value="<?php echo $nbrAnswers; ?>">
<table width="650" border="0" cellpadding="5">

<?php
			if($okPicture)
			{
?>

<tr>
  <td colspan="5" align="center"><img src="<?php echo $picturePath.'/quiz-'.$questionId; ?>" border="0"></td>
</tr>

<?php
			}

			// if there is an error message
			if(!empty($msgErr))
			{
?>

<tr>
  <td colspan="5">
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
  <td colspan="5"><?php echo $langAnswers; ?> :</td>
</tr>
<tr bgcolor="#E6E6E6">
  <td>N°</td>
  <td><?php echo $langTrue; ?></td>
  <td><?php echo $langAnswer; ?></td>
  <td><?php echo $langComment; ?></td>
  <td><?php echo $langQuestionWeighting; ?></td>
</tr>

<?php
			for($i=1;$i <= $nbrAnswers;$i++)
			{
?>

<tr>
  <td valign="top"><?php echo $i; ?></td>

<?php
				if($answerType == UNIQUE_ANSWER)
				{
?>

  <td valign="top"><input type="radio" value="<?= $i; ?>" name="correct" <?php if(isset($correct) and $correct == $i) echo 'checked="checked"'; ?>></td>

<?php
				}
				else
				{
?>

<td valign="top"><input type="checkbox" value="1" name="correct[<?= $i; ?>]" <?php if(isset($correct[$i])) echo 'checked="checked"'; ?>></td>

<?php
				}
?>

  <td align="left"><textarea wrap="virtual" rows="7" cols="25" name="reponse[<?= $i; ?>]"><?php echo @htmlspecialchars($reponse[$i]); ?></textarea></td>
  <td align="left"><textarea wrap="virtual" rows="7" cols="25" name="comment[<?= $i; ?>]"><?php echo @htmlspecialchars($comment[$i]); ?></textarea></td>
  <td valign="top"><input type="text" name="weighting[<?php echo $i; ?>]" size="5" value="<?php echo isset($weighting[$i])?$weighting[$i]:0; ?>"></td>
</tr>

<?php
  			}
?>

<tr>
  <td colspan="5" align="center">
  	<input type="submit" name="submitAnswers" value="<?php echo $langOk; ?>">
	&nbsp;&nbsp;<input type="submit" name="lessAnswers" value="<?php echo $langLessAnswers; ?>">
	&nbsp;&nbsp;<input type="submit" name="moreAnswers" value="<?php echo $langMoreAnswers; ?>">
	&nbsp;&nbsp;<input type="submit" name="cancelAnswers" value="<?php echo $langCancel; ?>">
  </td>
</tr>
</table>
</form>

<?php
		}
		elseif($answerType == FILL_IN_BLANKS)
		{
?>

<h3>
  <?php echo $questionName; ?>
</h3>

<form name="formulaire" method="post" action="<?php echo $PHP_SELF; ?>?modifyAnswers=<?php echo $modifyAnswers; ?>">
<input type="hidden" name="formSent" value="1">
<input type="hidden" name="setWeighting" value="<?php echo $setWeighting; ?>">

<?php
			if(!isset($setWeighting))
			{
?>

<input type="hidden" name="weighting" value="<?php echo $submitAnswers?htmlspecialchars($weighting):htmlspecialchars(serialize($weighting)); ?>">

<table border="0" cellpadding="5" width="500">

<?php
				if($okPicture)
				{
?>

<tr>
  <td align="center"><img src="<?php echo $picturePath.'/quiz-'.$questionId; ?>" border="0"></td>
</tr>

<?php
				}

				// if there is an error message
				if(!empty($msgErr))
				{
?>

<tr>
  <td>
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
  <td><?php echo $langTypeTextBelow.', '.$langAnd.' '.$langUseTagForBlank; ?> :</td>
</tr>
<tr>
  <td><textarea wrap="virtual" name="reponse" cols="65" rows="6"><?php if(!isset($submitAnswers) && empty($reponse)) echo $langDefaultTextInBlanks; else echo htmlspecialchars($reponse); ?></textarea></td>
</tr>
<tr>
  <td colspan="5" align="center">
	<input type="submit" name="submitAnswers" value="<?= $langNext; ?> &gt;">
	&nbsp;&nbsp;<input type="submit" name="cancelAnswers" value="<?php echo $langCancel; ?>">
  </td>
</tr>
</table>

<?php
			}
			else
			{
?>

<input type="hidden" name="blanks" value="<?php echo htmlspecialchars(serialize($blanks)); ?>">
<input type="hidden" name="reponse" value="<?php echo htmlspecialchars($reponse); ?>">

<table border="0" cellpadding="5" width="500">

<?php
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
  <td colspan="2"><?php echo $langWeightingForEachBlank; ?> :</td>
</tr>
<tr>
  <td colspan="2">&nbsp;</td>
</tr>

<?php
				foreach($blanks as $i=>$blank)
				{
?>

<tr>
  <td width="50%"><?php echo $blank; ?> :</td>
  <td width="50%"><input type="text" name="weighting[<?php echo $i; ?>]" size="5" value="<?php echo intval($weighting[$i]); ?>"></td>
</tr>

<?php
	    		}
?>

<tr>
  <td colspan="2">&nbsp;</td>
</tr>
<tr>
  <td colspan="2" align="center">
	<input type="submit" name="buttonBack" value="&lt; <?php echo $langBack; ?>">
	&nbsp;&nbsp;<input type="submit" name="submitAnswers" value="<?php echo $langOk; ?>">
	&nbsp;&nbsp;<input type="submit" name="cancelAnswers" value="<?php echo $langCancel; ?>">
 </td>
</tr>
</table>

<?php
			}
?>

</form>

<?php
		}
		elseif($answerType == MATCHING)
		{
?>

<h3>
  <?php echo $questionName; ?>
</h3>

<form method="post" action="<?php echo $PHP_SELF; ?>?modifyAnswers=<?php echo $modifyAnswers; ?>">
<input type="hidden" name="formSent" value="1">
<input type="hidden" name="nbrOptions" value="<?php echo $nbrOptions; ?>">
<input type="hidden" name="nbrMatches" value="<?php echo $nbrMatches; ?>">
<table border="0" cellpadding="5">

<?php
			if($okPicture)
			{
?>

<tr>
  <td colspan="4" align="center"><img src="<?php echo $picturePath.'/quiz-'.$questionId; ?>" border="0"></td>
</tr>

<?php
			}

			// if there is an error message
			if(!empty($msgErr))
			{
?>

<tr>
  <td colspan="4">
	<table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
	<tr>
	  <td><?php echo $msgErr; ?></td>
	</tr>
	</table>
  </td>
</tr>

<?php
			}

			$listeOptions=Array();

			// creates an array with the option letters
			for($i=1,$j='A';$i <= $nbrOptions;$i++,$j++)
			{
				$listeOptions[$i]=$j;
			}
?>

<tr>
  <td colspan="3"><?php echo $langMakeCorrespond; ?> :</td>
  <td><?php echo $langQuestionWeighting; ?> :</td>
</tr>

<?php
			for($j=1;$j <= $nbrMatches;$i++,$j++)
			{
?>

<tr>
  <td><?= $j; ?></td>
  <td><input type="text" name="match[<?= $i; ?>]" size="58" value="<?php if(!isset($formSent) && !isset($match[$i])) echo ${"langDefaultMakeCorrespond$j"}; else echo @htmlspecialchars($match[$i]); ?>"></td>
  <td align="center"><select name="sel[<?= $i; ?>]">

<?php
				foreach($listeOptions as $key=>$val)
				{
?>

<option value="<?= $key; ?>" <?php if((!isset($submitAnswers) && !isset($sel[$i]) && $j == 2 && $val == 'B') || @$sel[$i] == $key) echo 'selected="selected"'; ?>><?= $val; ?></option>

<?php
				} // end foreach()
?>

  </select></td>
  <td align="center"><input type="text" size="8" name="weighting[<?php echo $i; ?>]" value="<?php if(!isset($submitAnswers) && !isset($weighting[$i])) echo '5'; else echo $weighting[$i]; ?>"></td>
</tr>

<?php
		  	} // end for()
?>

<tr>
  <td colspan="4">
	<input type="submit" name="moreMatches" value="<?php echo $langMoreElements; ?>">
	&nbsp;&nbsp;<input type="submit" name="lessMatches" value="<?php echo $langLessElements; ?>">
  </td>
</tr>
<tr>
  <td colspan="4"><?php echo $langDefineOptions; ?> :</td>
</tr>

<?php
			foreach($listeOptions as $key=>$val)
			{
?>

<tr>
  <td><?php echo $val; ?></td>
  <td colspan="3"><input type="text" name="option[<?= $key; ?>]" size="80" value="<?php if(!isset($formSent) && !isset($option[$key])) echo ${"langDefaultMatchingOpt$val"}; else echo @htmlspecialchars($option[$key]); ?>"></td>
</tr>

<?php
			 } // end foreach()
?>

<tr>
  <td colspan="4">
	<input type="submit" name="moreOptions" value="<?php echo $langMoreElements; ?>">
	&nbsp;&nbsp;<input type="submit" name="lessOptions" value="<?php echo $langLessElements; ?>">
  </td>
</tr>
<tr>
  <td colspan="4" align="center">
  	<input type="submit" name="submitAnswers" value="<?php echo $langOk; ?>">
	&nbsp;&nbsp;<input type="submit" name="cancelAnswers" value="<?php echo $langCancel; ?>">
	
  </td>
</tr>
</table>
</form>

<?php
		}
	}
}
?>
