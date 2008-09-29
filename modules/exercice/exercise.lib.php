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

function showQuestion($questionId, $onlyAnswers=false)
{
	global $tool_content, $picturePath, $webDir, $langNoAnswer, $langColumnA, $langColumnB, $langMakeCorrespond;
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

	$questionDescription_temp = nl2br(make_clickable($questionDescription));
	$tool_content .= <<<cData
      <tr>
        <td colspan="2">
        <b>${questionName}</b>
        <br/>
        <small>${questionDescription_temp}</small>
        </td>
      </tr>
cData;

		if(file_exists($picturePath.'/quiz-'.$questionId)) {
			$tool_content .= "
      <tr>
        <td align=\"center\" colspan=\"2\"><center><img src=\"".${'picturePath'}."/quiz-".${'questionId'}."\" border=\"0\"></center></td>
      </tr>";
		}
	}  // end if(!$onlyAnswers)

	// construction of the Answer object
	$objAnswerTmp=new Answer($questionId);

	$nbrAnswers=$objAnswerTmp->selectNbrAnswers();

	// only used for the answer type "Matching"
	if($answerType == MATCHING) {
		$cpt1='A';
		$cpt2=1;
		$Select=array();
		$tool_content .= <<<cData
      <tr>
        <td colspan="2">
        <table width="100%">
        <thead>
        <tr>
          <td width="44%" class="left"><u><b>$langColumnA</b></u></td>
          <td width="12%"><div align="center"><b>$langMakeCorrespond</b></div></td>
          <td width="44%" class="left"><u><b>$langColumnB</b></u></td>
        </tr>
        </thead>
        </table>
        </td>
      </tr>
cData;
	}

	for($answerId=1;$answerId <= $nbrAnswers;$answerId++)
	{
		$answer=$objAnswerTmp->selectAnswer($answerId);
		$answerCorrect=$objAnswerTmp->isCorrect($answerId);
		// latex support
		$answer=latex_content($answer);
		if($answerType == FILL_IN_BLANKS) {
			// splits text and weightings that are joined with the character '::'
			list($answer)=explode('::',$answer);
			// replaces [blank] by an input field
			$answer=ereg_replace('\[[^]]+\]','<input type="text" name="choice['.$questionId.'][]" size="10">',nl2br($answer));
		}

		// unique answer
		if($answerType == UNIQUE_ANSWER)
		{
	$tool_content .= <<<cData

      <tr>
        <td width="1%" align="center"><input type="radio" name="choice[${questionId}]" value="${answerId}"></td>
        <td width="99%">${answer}</td>
      </tr>
cData;
		}
		// multiple answers
		elseif($answerType == MULTIPLE_ANSWER)
		{
	$tool_content .= <<<cData

      <tr>
        <td width="1%" align="center"><input type="checkbox" name="choice[${questionId}][${answerId}]" value="1"></td>
        <td width="99%">${answer}</td>
      </tr>
cData;
		}
		// fill in blanks
		elseif($answerType == FILL_IN_BLANKS) {
			$tool_content .= "
      <tr>
        <td colspan=\"2\">${answer}</td>
      </tr>";
		}
		// matching
		else
		{
			if(!$answerCorrect) {
				// options (A, B, C, ...) that will be put into the list-box
				$Select[$answerId]['Lettre']=$cpt1++;
				// answers that will be shown at the right side
				$Select[$answerId]['Reponse']=$answer;
			}
			else
			{
				$tool_content .= <<<cData

      <tr>
        <td colspan="2">
        <table width="100%">
        <thead>
        <tr>
          <td width="44%"><b>${cpt2}.</b> ${answer}</td>
          <td width="12%"><div align="center">
            <select name="choice[${questionId}][${answerId}]">
            <option value="0">--</option>
cData;

	       // fills the list-box
           foreach($Select as $key=>$val)
	       {
			$tool_content .= "
            <option value=\"${key}\">${val['Lettre']}</option>";
		   }// end foreach()

		  $tool_content .= "
            </select></div>
          </td>
          <td width=\"44%\">";

		  if(isset($Select[$cpt2]))
		  	$tool_content .= '<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse'];
		  else
		  	$tool_content .= '&nbsp;';

		  $tool_content .=	"
          </td>
        </tr>
        </thead>
        </table>
        </td>
      </tr>";
			$cpt2++;

				// if the left side of the "matching" has been completely shown
				if($answerId == $nbrAnswers) {
					// if it remains answers to shown at the right side
					while(isset($Select[$cpt2])) 	{
		$tool_content .= "
      <tr>
        <td colspan=\"2\">".
			"<table>".
			"<tr><td width=\"60%\" colspan=\"2\">&nbsp;</td><td width=\"40%\" align=\"right\" valign=\"top\">".
			"<b>".$Select[$cpt2]['Lettre'].".</b> ".$Select[$cpt2]['Reponse']."</td></tr></table>
        </td>
      </tr>";
						$cpt2++;
					}	// end while()
				}  // end if()
			}
		}
	}	// end for()

	if(!$nbrAnswers) {
		$tool_content .= "
      <tr>
        <td colspan=\"2\"><font color='red'>$langNoAnswer</font></td>
      </tr>";
	}

	// destruction of the Answer object
	unset($objAnswerTmp);
	// destruction of the Question object
	unset($objQuestionTmp);

	return $nbrAnswers;
}
?>
