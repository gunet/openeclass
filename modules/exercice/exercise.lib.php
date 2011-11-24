<?php
// $Id$
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


function showQuestion($questionId, $onlyAnswers = false) {
	global $tool_content, $picturePath, $webDir;
	global $langNoAnswer, $langColumnA, $langColumnB, $langMakeCorrespond;

        // construction of the Question object
	$objQuestionTmp=new Question();
	// reads question informations
	if(!$objQuestionTmp->read($questionId)) {
		// question not found
		return false;
	}
	$answerType=$objQuestionTmp->selectType();

	if(!$onlyAnswers) {
		$questionName=$objQuestionTmp->selectTitle();
		$questionDescription=$objQuestionTmp->selectDescription();	
		$questionDescription_temp = standard_text_escape($questionDescription);
		$tool_content .= "
                  <tr class='even'>
                    <td colspan='2'>
		<b>$questionName</b><br />
		$questionDescription_temp
                </td>
              </tr>";
		if(file_exists($picturePath.'/quiz-'.$questionId)) {
			$tool_content .= "
                  <tr class='even'>
                    <td class='center' colspan='2'><img src='".${'picturePath'}."/quiz-".${'questionId'}."'></td>
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
		$tool_content .= "
                  <tr class='even'>
                    <td colspan='2'>
                      <table class='tbl_border' width='100%'>
                      <tr>
                        <th width='200'>$langColumnA</th>
                        <th width='130'>$langMakeCorrespond</th>
                        <th width='200'>$langColumnB</th>
                      </tr>
                      </table>
                    </td>
                  </tr>";
	}

	for($answerId=1;$answerId <= $nbrAnswers;$answerId++) {
		$answer = $objAnswerTmp->selectAnswer($answerId);
		$answer = mathfilter($answer, 12, '../../courses/mathimg/');
		$answerCorrect = $objAnswerTmp->isCorrect($answerId);
		if($answerType == FILL_IN_BLANKS) {
			// splits text and weightings that are joined with the character '::'
			list($answer) = explode('::',$answer);
			// replaces [blank] by an input field
                        $answer = preg_replace('/\[[^]]+\]/',
					       '<input type="text" name="choice['.$questionId.'][]" size="10" />',
					       standard_text_escape(($answer)));
		}
		// unique answer
		if($answerType == UNIQUE_ANSWER) {
			$tool_content .= "
			<tr class='even'>
			  <td class='center' width='1'>
			    <input type='radio' name='choice[${questionId}]' value='${answerId}' />
			  </td>
			  <td>${answer}</td>
			</tr>";
		}
		// multiple answers
		elseif($answerType == MULTIPLE_ANSWER) {
			$tool_content .= "
			<tr class='even'>
			  <td width='1' align='center'>
			    <input type='checkbox' name='choice[${questionId}][${answerId}]' value='1' />
			  </td>
			  <td>${answer}</td>
			</tr>";
		}
		// fill in blanks
		elseif($answerType == FILL_IN_BLANKS) {
			$tool_content .= "
			<tr class='even'>
			  <td colspan='2'>${answer}</td>
			</tr>";
		}
		// matching
		elseif($answerType == MATCHING) { 
			if(!$answerCorrect) {
				// options (A, B, C, ...) that will be put into the list-box
				$Select[$answerId]['Lettre']=$cpt1++;
				// answers that will be shown at the right side
				$Select[$answerId]['Reponse']=$answer;
			}
			else
			{
				$tool_content .= "
				<tr class='even'>
				  <td colspan='2'>
				    <table class='tbl'>
				    <tr>
				      <td width='200'><b>${cpt2}.</b> ${answer}</td>
				      <td width='130'><div align='center'>
				       <select name='choice[${questionId}][${answerId}]'>
					 <option value='0'>--</option>";

				// fills the list-box
				 foreach($Select as $key=>$val) {
					 $tool_content .= "
					<option value=\"${key}\">${val['Lettre']}</option>";
				 }
				 $tool_content .= "
				</select></div>
			       </td>
			       <td width='200'>";
				 if(isset($Select[$cpt2]))
				       $tool_content .= '<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse'];
				 else
				       $tool_content .= '&nbsp;';

				$tool_content .= "
                                        </td>
                                      </tr>
                                      </table>
                                    </td>
                                  </tr>";
				$cpt2++;
				// if the left side of the "matching" has been completely shown
				if($answerId == $nbrAnswers) {
					// if it remains answers to shown at the right side
					while(isset($Select[$cpt2])) 	{
						$tool_content .= "
                                              <tr class='even'>
                                                <td colspan='2'>
                                                  <table>
                                                  <tr>
                                                    <td width='60%' colspan='2'>&nbsp;</td>
                                                    <td width='40%' align='right' valign='top'>".
                                                      "<b>".$Select[$cpt2]['Lettre'].".</b> ".$Select[$cpt2]['Reponse']."</td>
                                                  </tr>
                                                  </table>
                                                </td>
                                              </tr>";
						$cpt2++;
					}	// end while()
				}  // end if()
			}
                               // $tool_content .= " </table>";
		}
		elseif($answerType == TRUE_FALSE) {
			$tool_content .= "
                          <tr class='even'>
                            <td width='1' align='center'>
                              <input type='radio' name='choice[${questionId}]' value='${answerId}' />
                            </td>
                            <td>$answer</td>
                          </tr>";
		}
	}	// end for()

	if(!$nbrAnswers) {
		$tool_content .= "
                  <tr>
                    <td colspan='2'><p class='caution'>$langNoAnswer</td>
                  </tr>";
	}
	// destruction of the Answer object
	unset($objAnswerTmp);
	// destruction of the Question object
	unset($objQuestionTmp);
	return $nbrAnswers;
}