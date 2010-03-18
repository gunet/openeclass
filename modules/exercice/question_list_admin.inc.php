<?php
/*========================================================================
*   Open eClass 2.3
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

 // $Id$
/**
 * This script allows to manage the question list
 * It is included from the script admin.php
 */

// moves a question up in the list
if(isset($moveUp)) {
	$objExercise->moveUp($moveUp);
	$objExercise->save();
}

// moves a question down in the list
if(isset($moveDown)) {
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


  $tool_content .= <<<cData
    <table width="99%" class="FormData">
    <thead>
    <tr>
      <th class="left" width="220">$langQuestionList :</th>
      <td><div class="right">
      <a href="$_SERVER[PHP_SELF]?newQuestion=yes">${langNewQu}</a> | <a href="question_pool.php?fromExercise=${exerciseId}">${langGetExistingQuestion}</a>
      </div></td>
    </tr>
    </thead>
    </table>
cData;

  $tool_content .= <<<cData
  
    <table width="99%" class="Question">
    <tbody>
cData;

if($nbrQuestions) {
	$questionList=$objExercise->selectQuestionList();
	$i=1;

	foreach($questionList as $id) {
		$objQuestionTmp=new Question();
		$objQuestionTmp->read($id);

	$tool_content .= "<tr><td align=\"right\" width=\"1\">".$i.".</td>
	<td> ".$objQuestionTmp->selectTitle()."<br/>
	<small class=\"invisible\">".$aType[$objQuestionTmp->selectType()-1]."</small></td>
	<td class=\"right\" width=\"50\"><a href=\"".$_SERVER['PHP_SELF']."?editQuestion=".$id."\">"."<img src='../../template/classic/img/edit.gif' border='0' align='absmiddle' title='$langModify'></a>"." <a href=\"".$_SERVER['PHP_SELF']."?deleteQuestion=".$id."\" "."onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langConfirmYourChoice))."')) return false;\">"."<img src='../../template/classic/img/delete.gif' border='0' align='absmiddle' title='$langDelete'></a></td>
	<td width='20'>";
	
		if($i != 1) {
			$tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?moveUp=".$id."\">
   			<img src=\"../../template/classic/img/up.gif\" border=\"0\" align=\"absmiddle\" title=\"".$langUp."\"></a> ";
		}
		$tool_content .= "</td><td width='20'>";
		if($i != $nbrQuestions)	{
			$tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?moveDown=".$id."\">
			<img src=\"../../template/classic/img/down.gif\" border=\"0\" align=\"absmiddle\" title=\"".$langDown."\"></a> ";
		}
		$tool_content .= "</td></tr>";
		$i++;
		unset($objQuestionTmp);
	}
}

if(!isset($i)) {
$tool_content .= <<<cData
    <tr>
      <td class="alert1">${langNoQuestion}</td>
    </tr>
cData;
}

$tool_content .= "</tbody></table>";
?>
