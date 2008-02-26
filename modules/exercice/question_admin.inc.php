<?php // $Id$
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

// if the question we are modifying is used in several exercises
if(isset($usedInSeveralExercises))
{

@$tool_content .= <<<cData
	<h3>
		${questionName}
	</h3>	
		<form method="post" action="${PHP_SELF}?modifyQuestion=${modifyQuestion}&modifyAnswers=${modifyAnswers}">
		<table border="0">
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
	$tool_content .= "<input type=\"hidden\" name=\"correct\" value=\"". htmlspecialchars(serialize($correct))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"reponse\" value=\"". htmlspecialchars(serialize($reponse))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"comment\" value=\"". htmlspecialchars(serialize($comment))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"weighting\" value=\"". htmlspecialchars(serialize($weighting))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"nbrAnswers\" value=\"". $nbrAnswers."\">";
	}
	elseif($answerType == MATCHING) {

	$tool_content .= "<input type=\"hidden\" name=\"option\" value=\"".htmlspecialchars(serialize($option))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"match\" value=\"".htmlspecialchars(serialize($match)). "\">";
	$tool_content .= "<input type=\"hidden\" name=\"sel\" value=\"".htmlspecialchars(serialize($sel))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"weighting\" value=\"".htmlspecialchars(serialize($weighting)).	"\">";
	$tool_content .= "<input type=\"hidden\" name=\"nbrOptions\" value=\"".$nbrOptions."\">";
	$tool_content .= "<input type=\"hidden\" name=\"nbrMatches\" value=\"".$nbrMatches."\">";
	} else {
	$tool_content .= "<input type=\"hidden\" name=\"reponse\" value=\"".htmlspecialchars(serialize($reponse))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"comment\" value=\"".htmlspecialchars(serialize($comment))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"blanks\" value=\"".htmlspecialchars(serialize($blanks))."\">";
	$tool_content .= "<input type=\"hidden\" name=\"weighting\" value=\"".htmlspecialchars(serialize($weighting))."\">".
		"<input type=\"hidden\" name=\"setWeighting\" value=\"1\">";
		}
	} // end submit answers

	$tool_content .= <<<cData
		<input type="hidden" name="answerType" value="${answerType}">

    <table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
    <tr>
      <td>${langUsedInSeveralExercises} :</td>
    </tr>
    <tr>
      <td><input type="radio" name="modifyIn" value="allExercises" checked="checked">
      	${langModifyInAllExercises}</td>
    </tr>
    <tr>
      <td><input type="radio" name="modifyIn" value="thisExercise">
      	${langModifyInThisExercise}
      </td>
    </tr>
    <tr>
cData;
    
      $tool_content .= "<input type=\"submit\" name=\"";
      if (isset($submitQuestion))
      	$tool_content .= "submitQuestion \" ";
      else	
      	$tool_content .= "submitAnswers \" ";
     	
     	$tool_content .= "value=\"".$langOk."\"></td>&nbsp;&nbsp;<td align=\"center\">".
     		"<input type=\"submit\" name=\"buttonBack\" value=\"".$langCancel."\">";
     		
     	$tool_content .= <<<cData
				    </tr>
				    </table>
				  </td>
				</tr>
				</table>
				</form>
cData;

}
else
{
	// selects question informations
	$questionName=$objQuestion->selectTitle();
	$questionDescription=$objQuestion->selectDescription();

	// is picture set ?
	$okPicture=file_exists($picturePath.'/quiz-'.$questionId)?true:false;

	//$tool_content .= "<p><b>$questionName</b></p>";

	// show the picture of the question
	if($okPicture) {
		$tool_content .= "<center><img src='$picturePath/quiz-$questionId' border='0'></center>";
	}

	$tool_content .= "<blockquote>".nl2br($questionDescription)."</blockquote>";

	// doesn't show the edit link if we come from the question pool to pick a question for an exercise
	if(!isset($fromExercise)) {
			$tool_content .= "<a href=\"".$PHP_SELF."?modifyQuestion=".$questionId."\">
			<img src=\"../../template/classic/img/edit.gif\" border=\"0\" align=\"absmiddle\" title=\"".$langModify."\"></a>";
	}

$tool_content .= "<br>$questionName</td></tr>";
if($okPicture) {
        $tool_content .= "<tr><td valign=middle class=td_QuestionHeader align=center>
          <img src=$picturePath.'/quiz-'.$questionId;' border='0'>
          </td>
        </tr>";
	 }

     // Question Description
  if($questionDescription) {
					  $tool_content .= "<tr> <td valign=middle class=td_QuestionHeader align=left><b><i>$langDesCom:</i></b><br/>".nl2br($questionDescription)."</td></tr>";
        }

$tool_content .= "</table>";

$tool_content .= "<br><table align='center' width='96%'  border='0' cellspacing='0' cellpadding='0' style='border: 1px solid $table_border;'>
<tr>
   <td colspan='2' class='td_QuestionBody'>
   <b><u>$langQuestionAnswers</u>:</b>";

   // doesn't show the edit link if we come from the question pool to pick a question for an exercise
     if(!isset($fromExercise)) {
        $tool_content .= "<a href='$PHP_SELF?modifyAnswers=$questionId'><img src='../../images/edit.gif' border='0' align='absmiddle' title='$langModify'></a>";
}

$tool_content .= "<br/>";

      // shows answers of the question. 'true' means that we don't show the question, only answers
        if(!showQuestion($questionId,true)) {
			        $tool_content .= "<font color='red'>$langNoAnswer</font>";	 
        }
$tool_content .= "</td></tr></table><br>";

/*
$tool_content .= "<hr size=\"1\" noshade=\"noshade\">";

	// we are in an exercise
	if(isset($exerciseId))
	{

$tool_content .= <<<cData
	<a href="${PHP_SELF}">&lt;&lt; ${langGoBackToQuestionList}</a>
cData;

	}
	// we are not in an exercise, so we come from the question pool
	else
	{

$tool_content .= <<<cData
	<a href="question_pool.php?fromExercise=${fromExercise}">&lt;&lt; ${langGoBackToQuestionPool}</a>
cData;

	}

$tool_content .= <<<cData
	<br><br>
		
		<b>${langQuestionAnswers}</b>
		
		<br><br>
		
		<table border="0" align="center" cellpadding="2" cellspacing="2" width="95%">
	<form>
cData;

	// shows answers of the question. 'true' means that we don't show the question, only answers
	if(!showQuestion($questionId,true))
	{

$tool_content .= <<<cData
	<tr>
	  <td>${langNoAnswer}</td>
	</tr>
cData;

	}

$tool_content .= "</form></table><br>";

	// doesn't show the edit link if we come from the question pool to pick a question for an exercise
	if(!isset($fromExercise))
	{

$tool_content .= "<a href=\"".$PHP_SELF."?modifyAnswers=\"".$questionId.
	"\"><img src=\"../../template/classic/img/edit.gif\" border=\"0\" align=\"absmiddle\" alt=\"".$langModify."\"></a>";

	} */
}
?>
