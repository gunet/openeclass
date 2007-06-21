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


$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\">";

$tool_content .= <<<cData
		<li><a href="${PHP_SELF}?newQuestion=yes">${langNewQu}</a> | <a href="question_pool.php?fromExercise=${exerciseId}">
		${langGetExistingQuestion}</a></li>
</ul></div>
	<br><br>
	<b>${langQuestionList}</b>
	
<table border="0" align="center" cellpadding="2" cellspacing="2" width="95%">
cData;

if($nbrQuestions)
{
	$questionList=$objExercise->selectQuestionList();

	$i=1;

	foreach($questionList as $id) {

		$objQuestionTmp=new Question();
		$objQuestionTmp->read($id);

$tool_content .= "<tr><td>".$i." ".$objQuestionTmp->selectTitle()."<br><small>".
	$aType[$objQuestionTmp->selectType()-1]."</small></td>";
	
$tool_content .= "</tr><tr><td><a href=\"".$_SERVER['PHP_SELF']."?editQuestion=".$id."\">".
	"<img src=\"../../template/classic/img/edit.gif\" border=\"0\" align=\"absmiddle\" alt=\"".$langModify."\"></a>".
	" <a href=\"".$_SERVER['PHP_SELF']."?deleteQuestion=".$id."\" ".
	"onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langConfirmYourChoice))."')) return false;\">".
	"<img src=\"../../template/classic/img/delete.gif\" border=\"0\" align=\"absmiddle\" alt=\"".$langDelete."\"></a>";

		if($i != 1) {

	$tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?moveUp=".$id.
		"\"><img src=\"../../template/classic/img/up.gif\" border=\"0\" align=\"absmiddle\" alt=\"".$langMoveUp."\"></a>";

		}

		if($i != $nbrQuestions)		{

		$tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?moveDown=".$id.
		"\"><img src=\"../../template/classic/img/down.gif\" border=\"0\" align=\"absmiddle\" alt=\"".$langMoveDown."\"></a>";
		}

  $tool_content .= "</td></tr>";
		$i++;
		unset($objQuestionTmp);
	}
}

if(!isset($i)) {
$tool_content .= <<<cData
	<tr><td>${langNoQuestion}</td>
	</tr>
cData;
}

$tool_content .= "</table>";
?>
