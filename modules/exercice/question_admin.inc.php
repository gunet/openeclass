<? // $Id$
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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


// if the question we are modifying is used in several exercises
if(isset($usedInSeveralExercises)) {
$tool_content .= <<<cData

    <h3>${questionName}</h3>
    <form method="post" action="$_SERVER[PHP_SELF]?modifyQuestion=${modifyQuestion}&modifyAnswers=${modifyAnswers}">
    <table width="99%">
    <tr>
	<td>
cData;

	// submit question
	if(isset($submitQuestion)) {
		$tool_content .= "<input type=\"hidden\" name=\"questionName\" value=\"".htmlspecialchars($questionName)."\">";
		$tool_content .= "<input type=\"hidden\" name=\"questionDescription\""."value=\"".htmlspecialchars($questionDescription)."\">";
		$tool_content .= "<input type='hidden' name='imageUpload_size' value='$imageUpload_size'>
		<input type='hidden' name='deletePicture' value='$deletePicture'>";
	}
	// submit answers
	else {
		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER) {
			$tool_content .= "
			<input type=\"hidden\" name=\"correct\" value=\"". htmlspecialchars(serialize($correct))."\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"reponse\" value=\"". htmlspecialchars(serialize($reponse))."\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"comment\" value=\"". htmlspecialchars(serialize($comment))."\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"weighting\" value=\"". htmlspecialchars(serialize($weighting))."\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"nbrAnswers\" value=\"". $nbrAnswers."\">";
		} elseif($answerType == MATCHING) {
			$tool_content .= "
			<input type=\"hidden\" name=\"option\" value=\"".htmlspecialchars(serialize($option))."\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"match\" value=\"".htmlspecialchars(serialize($match)). "\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"sel\" value=\"".htmlspecialchars(serialize($sel))."\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"weighting\" value=\"".htmlspecialchars(serialize($weighting)).	"\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"nbrOptions\" value=\"".$nbrOptions."\">";
			$tool_content .= "
			<input type=\"hidden\" name=\"nbrMatches\" value=\"".$nbrMatches."\">";
		} else {
			$tool_content .= "<input type=\"hidden\" name=\"reponse\" value=\"".htmlspecialchars(serialize($reponse))."\">";
			$tool_content .= "<input type=\"hidden\" name=\"comment\" value=\"".htmlspecialchars(serialize($comment))."\">";
			$tool_content .= "<input type=\"hidden\" name=\"blanks\" value=\"".htmlspecialchars(serialize($blanks))."\">";
			$tool_content .= "<input type=\"hidden\" name=\"weighting\" value=\"".htmlspecialchars(serialize($weighting))."\">"."
			<input type=\"hidden\" name=\"setWeighting\" value=\"1\">";
		}
	} // end submit answers

	$tool_content .= "<input type='hidden' name='answerType' value='${answerType}'>
	<table width='99%'><tr>
	<td>${langUsedInSeveralExercises} :</td>
	</tr>
	<tr>
	<td><input type='radio' name='modifyIn' value='allExercises'>
	$langModifyInAllExercises</td>
	</tr>
	<tr><td><input type='radio' name='modifyIn' value='thisExercise' checked='checked'>$langModifyInThisExercise</td>
	</tr><tr><td>";

	$tool_content .= "<input type=\"submit\" name=\"";
	if (isset($submitQuestion))
		$tool_content .= "submitQuestion \" ";
	else	
		$tool_content .= "submitAnswers \" ";
	$tool_content .= "value='$langOk'>&nbsp;&nbsp;
		<input type='submit' name='buttonBack' value='$langCancel'>";
	
     	$tool_content .= <<<cData

    </td></tr>
    </table>
      </td>
    </tr>
    </table>
    </form>
cData;

} else {
	// selects question informations
	$questionName=$objQuestion->selectTitle();
	$questionDescription=$objQuestion->selectDescription();
	// is picture set ?
	$okPicture=file_exists($picturePath.'/quiz-'.$questionId)?true:false;
	$tool_content .= "<table width='99%' class='Question'><thead>
	<tr><th class='left' colspan='2' height=\"25\">
	<b>".nl2br($questionName)."</b>&nbsp;&nbsp;";
	// doesn't show the edit link if we come from the question pool to pick a question for an exercise
	if(!isset($fromExercise)) {
		$tool_content .= "
		<a href=\"".$_SERVER['PHP_SELF']."?modifyQuestion=".$questionId."\">
		<img src='../../template/classic/img/edit.gif' border='0' align='absmiddle' title='$langModify'></a>";
	}

	$questionDescription = mathfilter($questionDescription, 12, "../../courses/mathimg/");
	$tool_content .= "<br/><i>$questionDescription</i>";
	// show the picture of the question
	if($okPicture) {
		$tool_content .= "<br/><center><img src='$picturePath/quiz-$questionId' border='0'></center><br/>";
	}

	$tool_content .= "</th></tr>";	
	$tool_content .= "<tr><td colspan='2'><b><u>$langQuestionAnswers</u>:</b>";

	// doesn't show the edit link if we come from the question pool to pick a question for an exercise
	if(!isset($fromExercise)) {
		$tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?modifyAnswers=$questionId'>
		<img src='../../template/classic/img/edit.gif' border='0' align='absmiddle' title='$langModify'></a>";
	}
        $tool_content .= "<br/></td></tr>";
	$tool_content .= "</td></tr></thead></table><br/>";
	$tool_content .= "<div class='center'><a href='admin.php?exerciseId=$exerciseId'>$langBackExerciseManagement</a></div>";
}
?>
