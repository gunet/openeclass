<?php // $Id$
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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


$questionName=$objQuestion->selectTitle();
$answerType=$objQuestion->selectType();
$okPicture=file_exists($picturePath.'/quiz-'.$questionId)?true:false;

// if we come from the warning box "this question is used in several exercises"
if (isset($usedInSeveralExercises) or isset($modifyIn)) {
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
if(isset($submitAnswers) || isset($buttonBack)) {
	if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
		$questionWeighting=$nbrGoodAnswers=0;

		for($i=1;$i <= $nbrAnswers;$i++) {
			$reponse[$i]=trim($reponse[$i]);
			$comment[$i]=trim($comment[$i]);
			$weighting[$i]=$weighting[$i];

			if($answerType == UNIQUE_ANSWER) {
				$goodAnswer=($correct == $i)?1:0;
			} else {
				$goodAnswer=@$correct[$i];
			}
			if($goodAnswer) {
				$nbrGoodAnswers++;
				// a good answer can't have a negative weighting
				$weighting[$i]=abs($weighting[$i]);
				// calculates the sum of answer weighting 
				if($weighting[$i]) {
					$questionWeighting+=$weighting[$i];
				}
			} else {
				// a bad answer can't have a positive weighting
				$weighting[$i]=0-abs($weighting[$i]);
			}

			// checks if field is empty
			if(empty($reponse[$i])) {
				$msgErr=$langGiveAnswers;

				// clears answers already recorded into the Answer object
				$objAnswer->cancel();

				break;
			} else {
				// adds the answer into the object
				$objAnswer->createAnswer($reponse[$i],$goodAnswer,$comment[$i],$weighting[$i],$i);
			}
		}  // end for()

		if(empty($msgErr)) {
			if(!$nbrGoodAnswers) {
				$msgErr=($answerType == UNIQUE_ANSWER)?$langChooseGoodAnswer:$langChooseGoodAnswers;
				// clears answers already recorded into the Answer object
				$objAnswer->cancel();
			}
			// checks if the question is used in several exercises
			elseif($exerciseId && !isset($modifyIn) && $objQuestion->selectNbrExercises() > 1)
			{
				
				$usedInSeveralExercises=1;
			} else {
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
	elseif($answerType == FILL_IN_BLANKS) {
		$reponse=trim($reponse);
		if(!isset($buttonBack)) {
			if($setWeighting) {
				@$blanks=unserialize($blanks);
				// checks if the question is used in several exercises
				if($exerciseId && !isset($modifyIn) && $objQuestion->selectNbrExercises() > 1)
				{
					$usedInSeveralExercises=1;
				} else {
					// separates text and weightings by '::'
					$reponse.='::';
					$questionWeighting=0;
					foreach($weighting as $val) {
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
				while(1) {
					if(($pos = strpos($temp,'[')) === false) {
						break;
					}
					// removes characters till '['
					$temp=substr($temp,$pos+1);
					// quits the loop if there are no more blanks
					if(($pos = strpos($temp,']')) === false) {
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
			unset($setWeighting); //tsou
		}
	}
	elseif($answerType == MATCHING)
	{
		for($i=1;$i <= $nbrOptions;$i++) {
			$option[$i]=trim($option[$i]);
			// checks if field is empty
			if(empty($option[$i])){
				$msgErr=$langFillLists;
				// clears options already recorded into the Answer object
				$objAnswer->cancel();
				break;
			} else {
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
				$weighting[$i]=abs($weighting[$i]);
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

		if(empty($msgErr)) {
			// checks if the question is used in several exercises
			if($exerciseId && !isset($modifyIn) && $objQuestion->selectNbrExercises() > 1) {
				$usedInSeveralExercises=1;
			} else {
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

if(isset($modifyAnswers)) {
	// construction of the Answer object
	$objAnswer=new Answer($questionId);
	$_SESSION['objAnswer'] = $objAnswer;

	if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
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
	elseif($answerType == FILL_IN_BLANKS) {
		if(!isset($submitAnswers) && !isset($buttonBack)) {
			if(!isset($setWeighting)) {
				$reponse=$objAnswer->selectAnswer(1);
				@list($reponse,$weighting)=explode('::',$reponse);
				$weighting=explode(',',$weighting);
				$temp=Array();

				// keys of the array go from 1 to N and not from 0 to N-1
				for($i=0;$i < sizeof($weighting);$i++) {
					$temp[$i+1]=$weighting[$i];
				}
				$weighting=$temp;
			}
			elseif(!isset($modifyIn))
			{
//				$weighting=unserialize(base64_decode($weighting));
				$weighting=unserialize($weighting);
			}
		}
	}
	elseif($answerType == MATCHING) {
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

	if(!isset($usedInSeveralExercises)) {
		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {

$tool_content .= <<<cData

    <form method="post" action="$_SERVER[PHP_SELF]?modifyAnswers=${modifyAnswers}">
    <input type="hidden" name="formSent" value="1">
    <input type="hidden" name="nbrAnswers" value="${nbrAnswers}">
cData;

	$tool_content .= "<table width=\"99%\" class=\"Question\"><thead><tr>
	<th class=\"left\" colspan=\"5\" height=\"25\">
	<b>".nl2br($questionName)."</b>&nbsp;&nbsp;
	</th></tr>
	<tr><td colspan=\"5\" ><b><u>$langQuestionAnswers</u>:</b><br />";
		
		if($answerType == UNIQUE_ANSWER) {
			$tool_content .= "<small>$langUniqueSelect</small>";
		}
		if($answerType == MULTIPLE_ANSWER) {
			$tool_content .= "<small>$langMultipleSelect</small>";
		}
		
	$tool_content .= "</td></tr></thead>";
	$tool_content .= "<tbody>";

	// if there is a picture, display this
	if($okPicture) {
		$tool_content .= "<tr>
		<td colspan='5' align=\"center\">"."<img src=\"".$picturePath."/quiz-".$questionId."\" border='0'></td>
		</tr>";
	}

	// if there is an error message
	if(!empty($msgErr))
	{
	$tool_content .= <<<cData
    <tr>
      <td colspan="5">
        <table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
        <tr><td>$msgErr</td></tr>
        </table>
      </td>
    </tr>
cData;
	}

	$tool_content .= <<<cData

  <tr>
    <td align="right" width="3%"><b>$langID</b></td>
    <td align="center" width="7%"><b>$langTrue</b></td>
    <td align="center" width="45%"><b>$langAnswer</b></td>
    <td align="center" width="45%"><b>$langComment</b></td>
    <td align="center" width="10%"><b>$langQuestionWeighting</b></td>
  </tr>
cData;

	for($i=1;$i <= $nbrAnswers;$i++) {
		$tool_content .="<tr><td class=\"right\">$i.</td>";
		if($answerType == UNIQUE_ANSWER) {
			$tool_content .= "<td align=\"center\" valign=\"top\">
			<input type=\"radio\" value=\"".$i."\" name=\"correct\" ";
			if(isset($correct) and $correct == $i) {
				$tool_content .= "checked=\"checked\"></td>";
			} else {
				$tool_content .= "></td>";
			}
		} else {
			$tool_content .= "<td align=\"center\" valign=\"top\">
			<input type=\"checkbox\" value=\"1\" name=\"correct[".$i."]\" ";
			if ((isset($correct[$i]))&&($correct[$i])) {
				$tool_content .= "checked=\"checked\"></td>";
			} else {
				$tool_content .= "></td>";
			}
		}
		
		$tool_content .= "<td align=\"center\">
		<textarea wrap=\"virtual\" rows=\"7\" cols=\"25\" "."name=\"reponse[".$i."]\" class=\"FormData_InputText\">".str_replace('{','&#123;',htmlspecialchars($reponse[$i]))."</textarea>
		</td>"."
		<td align=\"center\">
		<textarea wrap=\"virtual\" rows=\"7\" cols=\"25\" ". "name=\"comment[".$i."]\" class=\"FormData_InputText\">".str_replace('{','&#123;',htmlspecialchars($comment[$i]))."</textarea>
		</td>"."
		<td valign=\"top\" align=\"center\">
		<input class=\"FormData_InputText\" type=\"text\" name=\"weighting[".$i."]\" size=\"5\" value=\"";
		if (isset($weighting[$i])) {
			$tool_content .= $weighting[$i];
		} else {	
			$tool_content .= 0;
		}
		$tool_content .= "\"></td></tr>";
	}

$tool_content .= <<<cData

    <tr>
      <th class="left" colspan="2">&nbsp;</th>
      <td class="left"><b>$langAnswers :</b>&nbsp;
        <input type="submit" name="lessAnswers" value="${langLessAnswers}">&nbsp;
        <input type="submit" name="moreAnswers" value="${langMoreAnswers}">
      </td>
      <td align="center">
        <input type="submit" name="submitAnswers" value="${langCreate}">&nbsp;&nbsp;
        <input type="submit" name="cancelAnswers" value="${langCancel}">
      </td>
      <th class="left">&nbsp;</th>
    </tr>
    <tr>
      <th class="left" colspan="5">&nbsp;</th>
    </tr>
    </tbody>
    </table>
  </form>
cData;

		}
		elseif($answerType == FILL_IN_BLANKS)
		{

    $tool_content .= "
      <form name=\"formulaire\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."?modifyAnswers=".$modifyAnswers."\">\n";
if(!isset($setWeighting))
	$tempSW = "";
else
	$tempSW = $setWeighting;	


$tool_content .= <<<cData
      <input type="hidden" name="formSent" value="1">
      <input type="hidden" name="setWeighting" value="${tempSW}">
cData;

	if(!isset($setWeighting)) {
		$tool_content .= "<input type=\"hidden\" name=\"weighting\" value=\"";
		$tool_content .= "\">\n";

    $tool_content .= <<<cData
	
      <table class="FormData" width="99%">
      <tbody>
      <tr>
        <th class="left" width="220">$langQuestion:</th>
        <td><b>$questionName</b></td>
      </tr>
cData;

	if($okPicture) {
		$tool_content .= "<tr>
		<th colspan=\"2\"align=\"center\"><img src=\"".$picturePath."/quiz-".$questionId."\" border=\"0\"></th>
		</tr>";
	}
	
    $tool_content .= <<<cData
      <tr>
        <th class="left">$langQuestionAnswers:</th>
        <td>$langTypeTextBelow, $langAnd $langUseTagForBlank :<br/><br/>
            <textarea wrap="virtual" name="reponse" cols="70" rows="6" class="FormData_InputText">
cData;

  if(!isset($submitAnswers) && empty($reponse)) 
  	$tool_content .= $langDefaultTextInBlanks; 
  else 
  	$tool_content .= htmlspecialchars($reponse);

$tool_content .= <<<cData

            </textarea>
        </td>
      </tr>
cData;

		// if there is an error message
	if(!empty($msgErr)) {
		$tool_content .= "\n";
		$tool_content .= <<<cData

        <tr>
          <th>&nbsp;</th>
          <td>
          <table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
          <tr>
            <td>$msgErr</td>
          </tr>
          </table>
          </td>
        </tr>
cData;
		}



$tool_content .= <<<cData

        <tr>
          <th>&nbsp;</th>
          <td>
          <input type="submit" name="submitAnswers" value="${langNext} &gt;">
          &nbsp;&nbsp;<input type="submit" name="cancelAnswers" value="${langCancel}">
          </td>
        </tr>
        </tbody>
        </table>
cData;

	} else {

$tool_content .= "
      <input type=\"hidden\" name=\"blanks\" value=\"".htmlspecialchars(serialize($blanks))."\">";
$tool_content .= "
      <input type=\"hidden\" name=\"reponse\" value=\"".htmlspecialchars($reponse)."\">";
$tool_content .= <<<cData

cData;

		// if there is an error message
		if(!empty($msgErr))
		{

$tool_content .= <<<cData

      <tr>
        <td colspan="2">
          <table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
          <tr>
            <td>$msgErr</td>
          </tr>
          </table>
        </td>
      </tr>
cData;
		}

$tool_content .= <<<cData

      <table class="FormData" width="99%">
      <tbody>
      <tr>
        <th width="220">&nbsp;</th>
        <td><b>${langWeightingForEachBlank} :</b></td>
      </tr>
cData;

	foreach($blanks as $i=>$blank) {
		$tool_content .= "<tr><th class=\"right\">[".$blank."] :</th>"."
		<td><input type='text' name=\"weighting[".$i."]\" "."size='5' value=\"".$weighting[$i]."\" class='FormData_InputText'></td>
		</tr>";
	}

$tool_content .= <<<cData

      <tr>
        <th>&nbsp;</th>
        <td>
        <input type="submit" name="buttonBack" value="&lt; ${langBack}">&nbsp;&nbsp;
        <input type="submit" name="submitAnswers" value="${langCreate}">&nbsp;&nbsp;
        <input type="submit" name="cancelAnswers" value="${langCancel}">
        </td>
      </tr>
      </tbody>
      </table>
cData;

			}

$tool_content .= "</td></tr></thead></table></form>";

		} //END FILL_IN_BLANKS !!!
		elseif($answerType == MATCHING)
		{

$tool_content .= <<<cData



  
	<form method="post" action="$_SERVER[PHP_SELF]?modifyAnswers=${modifyAnswers}">
	<input type="hidden" name="formSent" value="1">
	<input type="hidden" name="nbrOptions" value="${nbrOptions}">
	<input type="hidden" name="nbrMatches" value="${nbrMatches}">
	
    <table width="99%" class="FormData">
    <tbody>
    <tr>
      <th width="220" class="left">$langQuestion :</th>
      <td colspan="3" class="left"><b>$questionName</b></td>
    </tr>	
cData;

	if($okPicture) {
		$tool_content .= "<tr>
		<td colspan='4' align='center'><img src='${picturePath}/quiz-${questionId}' border='0'></td></tr>";
	}

	// if there is an error message
	if(!empty($msgErr)) {
	$tool_content .= <<<cData
    <tr>
      <td colspan="4">
        <table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
        <tr>
          <td>$msgErr</td>
        </tr>
        </table>
      </td>
    </tr>
cData;
	}

	$listeOptions=Array();
	// creates an array with the option letters
	for($i=1,$j='A';$i <= $nbrOptions;$i++,$j++) {
		$listeOptions[$i]=$j;
	}

$tool_content .= <<<cData
    <tr>
      <th class="left">$langDefineOptions</th>
      <td>&nbsp;</td>
      <th align="right" colspan="2"><b>$langMakeCorrespond</b></th>
    </tr>
    <tr>
      <th class="right">$langColumnA:</th>
      <td align="left">$langMoreLessChoices: <input type="submit" name="moreMatches" value="+">&nbsp;<input type="submit" name="lessMatches" value="-">
      </td>
      <td align="center"><b>$langColumnB</b></td>
      <td align="center"><b>$langQuestionWeighting</b></td>
    </tr>
cData;
	for($j=1;$j <= $nbrMatches;$i++,$j++) {
			$tool_content .= "<tr>
			<th class=\"right\">".$j."</th>
			<td><input type=\"text\" name=\"match[".$i."]\" size=\"58\" value=\"";
	
		if(!isset($formSent) && !isset($match[$i])) 
			$tool_content .= ${"langDefaultMakeCorrespond$j"}; 
		else 
			$tool_content .= str_replace('{','&#123;',htmlspecialchars($match[$i]));
	
	$tool_content .= "\" class=\"auth_input\"></td>
	<td align=\"center\"><select name=\"sel[".$i."]\"  class=\"auth_input\">";
	
		foreach($listeOptions as $key=>$val) {
			$tool_content .= "<option value=\"".$key."\" ";
			if((!isset($submitAnswers) && !isset($sel[$i]) && $j == 2 && $val == 'B') || @$sel[$i] == $key) 
				$tool_content .= "selected=\"selected\"";
				$tool_content .= ">".$val."</option>";
		} // end foreach()
	
	$tool_content .= "</select></td>
	<td align=\"center\"><input type=\"text\" size=\"3\" ".
		"name=\"weighting[".$i."]\" value=\"";
		if(!isset($submitAnswers) && !isset($weighting[$i])) 
			$tool_content .= '5'; 
		else 
			$tool_content .= $weighting[$i];
		$tool_content .= "\"  class=\"auth_input\"></td></tr>";
	} // end for()

$tool_content .= <<<cData
    <tr>
      <th class="right">&nbsp;</th>
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
      <th class="right">$langColumnB:</th>
      <td colspan="3">$langMoreLessChoices: <input type="submit" name="moreOptions" value="+">&nbsp;<input type="submit" name="lessOptions" value="-">
      </td>
  </tr>
cData;

	foreach($listeOptions as $key=>$val) {
		$tool_content .= "<tr><th class=\"right\">".$val."</th>
		<td><input type=\"text\" ".
			"name=\"option[".$key."]\" size=\"58\" value=\"";
			
			if(!isset($formSent) && !isset($option[$key]))
				$tool_content .= ${"langDefaultMatchingOpt$val"}; 
			else 
				$tool_content .= str_replace('{','&#123;',htmlspecialchars($option[$key]));
				
			$tool_content .= "\" class=\"auth_input\"></td><td>&nbsp;</td><td>&nbsp;</td></tr>";
		} // end foreach()

$tool_content .= <<<cData
    <tr>
      <th>&nbsp;</th>
      <td colspan="3" align="left">
      <input type="submit" name="submitAnswers" value="${langCreate}">
      &nbsp;&nbsp;<input type="submit" name="cancelAnswers" value="${langCancel}">
      </td>
    </tr>
    </tbody>
    </table>
	
    </form>
cData;

		}
	}
}
?>
