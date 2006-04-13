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

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE TOOL LIBRARY <<<<<<<<<<<<<<<<<<<<*/

/**
 * shows a question and its answers
 *
 * @returns 'number of answers' if question exists, otherwise false
 *
 * @author Olivier Brouckaert <oli.brouckaert@skynet.be>
 *
 * @param integer	$questionId		ID of the question to show
 * @param boolean	$onlyAnswers	set to true to show only answers
 */

function showQuestion($questionId, $onlyAnswers=false)
{
	global $picturePath, $webDir;
 	include_once "$webDir"."/modules/latexrender/latex.php";

	// construction of the Question object
	$objQuestionTmp=new Question();

	// reads question informations
	if(!$objQuestionTmp->read($questionId))
	{
		// question not found
		return false;
	}

	$answerType=$objQuestionTmp->selectType();

	if(!$onlyAnswers)
	{
		$questionName=$objQuestionTmp->selectTitle();
		$questionDescription=$objQuestionTmp->selectDescription();
	// latex support

		$questionName=latex_content($questionName);
		$questionDescription=latex_content($questionDescription);

?>

	<tr>
	  <td valign="top" colspan="2">
		<?php echo $questionName; ?>
	  </td>
	</tr>
	<tr>
	  <td valign="top" colspan="2">
		<i><?php echo nl2br(make_clickable($questionDescription)); ?></i>
	  </td>
	</tr>

<?php
		if(file_exists($picturePath.'/quiz-'.$questionId))
		{
?>

	<tr>
	  <td align="center" colspan="2"><img src="<?php echo $picturePath.'/quiz-'.$questionId; ?>" border="0"></td>
	</tr>

<?php
		}
	}  // end if(!$onlyAnswers)

	// construction of the Answer object
	$objAnswerTmp=new Answer($questionId);

	$nbrAnswers=$objAnswerTmp->selectNbrAnswers();

	// only used for the answer type "Matching"
	if($answerType == MATCHING)
	{
		$cpt1='A';
		$cpt2=1;
		$Select=array();
	}

	for($answerId=1;$answerId <= $nbrAnswers;$answerId++)
	{
		$answer=$objAnswerTmp->selectAnswer($answerId);
		$answerCorrect=$objAnswerTmp->isCorrect($answerId);
		// latex support
		$answer=latex_content($answer);
		if($answerType == FILL_IN_BLANKS)
		{
			// splits text and weightings that are joined with the character '::'
			list($answer)=explode('::',$answer);

			// replaces [blank] by an input field
			$answer=ereg_replace('\[[^]]+\]','<input type="text" name="choice['.$questionId.'][]" size="10">',nl2br($answer));
		}

		// unique answer
		if($answerType == UNIQUE_ANSWER)
		{
?>

	<tr>
	  <td width="5%" align="center">
		<input type="radio" name="choice[<?php echo $questionId; ?>]" value="<?php echo $answerId; ?>">
	  </td>
	  <td width="95%">
		<?php echo $answer; ?>
	  </td>
	</tr>

<?php
		}
		// multiple answers
		elseif($answerType == MULTIPLE_ANSWER)
		{
?>

	<tr>
	  <td width="5%" align="center">
		<input type="checkbox" name="choice[<?php echo $questionId; ?>][<?php echo $answerId; ?>]" value="1">
	  </td>
	  <td width="95%">
		<?php echo $answer; ?>
	  </td>
	</tr>

<?php
		}
		// fill in blanks
		elseif($answerType == FILL_IN_BLANKS)
		{
?>

	<tr>
	  <td colspan="2">
		<?php echo $answer; ?>
	  </td>
	</tr>

<?php
		}
		// matching
		else
		{
			if(!$answerCorrect)
			{
				// options (A, B, C, ...) that will be put into the list-box
				$Select[$answerId]['Lettre']=$cpt1++;
				// answers that will be shown at the right side
				$Select[$answerId]['Reponse']=$answer;
			}
			else
			{
?>

	<tr>
	  <td colspan="2">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
		  <td width="40%" valign="top"><?php echo '<b>'.$cpt2.'.</b> '.$answer; ?></td>
		  <td width="20%" align="center">&nbsp;&nbsp;<select name="choice[<?php echo $questionId; ?>][<?php echo $answerId; ?>]">
			<option value="0">--</option>

<?php
	            // fills the list-box
	            foreach($Select as $key=>$val)
	            {
?>

			<option value="<?php echo $key; ?>"><?php echo $val['Lettre']; ?></option>

<?php
				}  // end foreach()
?>

		  </select>&nbsp;&nbsp;</td>
		  <td width="40%" valign="top"><?php if(isset($Select[$cpt2])) echo '<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse']; else echo '&nbsp;'; ?></td>
		</tr>
		</table>
	  </td>
	</tr>

<?php
				$cpt2++;

				// if the left side of the "matching" has been completely shown
				if($answerId == $nbrAnswers)
				{
					// if it remains answers to shown at the right side
					while(isset($Select[$cpt2]))
					{
?>

	<tr>
	  <td colspan="2">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
		  <td width="60%" colspan="2">&nbsp;</td>
		  <td width="40%" align="right" valign="top"><?php echo '<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse']; ?></td>
		</tr>
		</table>
	  </td>
	</tr>

<?php
						$cpt2++;
					}	// end while()
				}  // end if()
			}
		}
	}	// end for()

	// destruction of the Answer object
	unset($objAnswerTmp);

	// destruction of the Question object
	unset($objQuestionTmp);

	return $nbrAnswers;
}
?>
