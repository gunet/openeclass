<?php // $Id$
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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

/*===========================================================================
	work.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the work tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights

 	The user can : - navigate through files and directories.
                       - upload a file
                       - delete, copy a file or a directory
                       - edit properties & content (name, comments, 
			 html content)

 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
   	2) Define the directory to display
  	3) Read files and directories from the directory defined in part 2
  	4) Display all of that on an HTML page
 
  	@TODO: eliminate code duplication between document/document.php, scormdocument.php
==============================================================================
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

$tool_content .= "<h3>$questionName</h3>";

$tool_content .= "<form enctype=\"multipart/form-data\" method=\"post\" action=\"".$PHP_SELF.
	"?modifyQuestion=".@$modifyQuestion."&newQuestion=".@$newQuestion."\"><table border=\"0\" cellpadding=\"5\">";

	if($okPicture)
	{

$tool_content .= "<tr><td colspan=\"2\" align=\"center\"><img src=\"".$picturePath.
	"/quiz-".$questionId."\" border=\"0\"></td></tr>";

	}

	// if there is an error message
	if(!empty($msgErr))
	{

$tool_content .= <<<cData
	<tr>
	  <td colspan="2">
		<table border="0" cellpadding="3" align="center" width="400" bgcolor="#FFCC00">
		<tr>
		  <td>${msgErr}</td>
		</tr>
		</table>
	  </td>
	</tr>
cData;
	}

$tool_content .= "<tr><td>".$langQuestion." :</td><td><input type=\"text\" name=\"questionName\"" .
	"size=\"50\" maxlength=\"200\" value=\"".htmlspecialchars($questionName)."\" style=\"width:400px;\"></td>";

$tool_content .= <<<cData
	</tr>
	<tr>
	  <td valign="top">${langQuestionDescription} :</td>
cData;

  $tool_content .= "<td><textarea wrap=\"virtual\" name=\"questionDescription\" cols=\"50\" rows=\"4\" ".
  	"style=\"width:400px;\">".htmlspecialchars($questionDescription)."</textarea></td></tr><tr><td>";

if ($okPicture) 
	$tool_content .= "$langReplacePicture";
else 
	$tool_content .= "$langAddPicture";	

$tool_content .= " :</td> <td><input type=\"file\" name=\"imageUpload\" size=\"30\" style=\"width:390px;\">";
	if ($okPicture)
	{

	$tool_content .= "<br><input type=\"checkbox\" name=\"deletePicture\" value=\"1\" ";
	
	if(isset($deletePicture)) 
		$tool_content .= 'checked="checked"'; 
	$tool_content .= "> ".$langDeletePicture;

	}

  $tool_content .= <<<cData
		  </td>
		</tr>
		<tr>
		  <td valign="top">${langAnswerType} :</td>
cData;


  $tool_content .= "<td><input type=\"radio\" name=\"answerType\" value=\"1\" "; 
  	if ($answerType <= 1) 
  		$tool_content .= 'checked="checked"'; 
  	$tool_content .= "> ".$langUniqueSelect."<br>";
	$tool_content .= "<input type=\"radio\" name=\"answerType\" value=\"2\" ";
		if ($answerType == 2) 
			$tool_content .= 'checked="checked"';
		$tool_content .= "> ".$langMultipleSelect."<br>";
	$tool_content .= "<input type=\"radio\" name=\"answerType\" value=\"4\" ";
		if ($answerType >= 4) 
			$tool_content .= 'checked="checked"';
		$tool_content .= "> ".$langMatching."<br>";
	$tool_content .= "<input type=\"radio\" name=\"answerType\" value=\"3\" ";
		if ($answerType == 3) 
			$tool_content .= 'checked="checked"';
		$tool_content .= "> ".$langFillBlanks;
$tool_content .= <<<cData

	  </td>
	</tr>
	<tr>
	  <td colspan="2" align="center">
		<input type="submit" name="submitQuestion" value="${langOk}">
		&nbsp;&nbsp;<input type="submit" name="cancelQuestion" value="${langCancel}">
	  </td>
	</tr>
	</table>
	</form>
cData;

}
?>
