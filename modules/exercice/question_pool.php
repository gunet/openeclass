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

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

$require_current_course = TRUE;
$langFiles='exercice';

//include('../../include/init.php');

include '../../include/baseTheme.php';

$tool_content = "";

$nameTools=$langQuestionPool;
$navigation[]=array("url" => "exercice.php","name" => $langExercices);

//begin_page($nameTools);

$is_allowedToEdit=$is_adminOfCourse;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

// maximum number of questions on a same page
$limitQuestPage=50;

if($is_allowedToEdit)
{
	// deletes a question from the data base and all exercises
	if(isset($delete))
	{
		// construction of the Question object
		$objQuestionTmp=new Question();

		// if the question exists
		if($objQuestionTmp->read($delete))
		{
			// deletes the question from all exercises
			$objQuestionTmp->delete();
		}

		// destruction of the Question object
		unset($objQuestionTmp);
	}
	// gets an existing question and copies it into a new exercise
	elseif(isset($recup) && isset($fromExercise))
	//elseif(isset($recup) && $fromExercise) - why was it like that?!?
	{
		// construction of the Question object
		$objQuestionTmp=new Question();

		// if the question exists
		if($objQuestionTmp->read($recup))
		{
			//$tool_content .= "sssssssss".$fromExercise;
			
			// vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
					if(!is_object(@$objExercise)) {
						// construction of the Exercise object
						$objExercise=new Exercise();
			
						// creation of a new exercise if wrong or not specified exercise ID
						if(isset($exerciseId)) {
							$objExercise->read($exerciseId);
						}
					
						// saves the object into the session
						session_register('objExercise');
				}			
				$fromExercise=$objExercise->selectId();	
			// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
			// adds the exercise ID represented by $fromExercise into the list of exercises for the current question
			$objQuestionTmp->addToList($fromExercise);
		}

		// destruction of the Question object
		unset($objQuestionTmp);


		//////////////// - 20061106
		if(!is_object(@$objExercise)) {
			// construction of the Exercise object
			$objExercise=new Exercise();

			// creation of a new exercise if wrong or not specified exercise ID
			if(isset($exerciseId)) {
				$objExercise->read($exerciseId);
			}
		
			// saves the object into the session
			session_register('objExercise');
			
			
		}			
		$exerciseId=$objExercise->selectId();			
		//$tool_content .= "qqqqqq".$exerciseId."qqqqq";
		////////////////
		
		// adds the question ID represented by $recup into the list of questions for the current exercise
		
		$objExercise->addToList($recup);

		header("Location: admin.php?editQuestion=$recup");
		exit();
	}
}


// if admin of course
if($is_allowedToEdit)
{

if (isset($fromExercise)) {
	$temp_fromExercise = $fromExercise;
} else {
	$temp_fromExercise = "";
	//$fromExercise = 0;
}

$tool_content .= <<<cData
	<form method="get" action="${PHP_SELF}">
	<!--<input type="hidden" name="fromExercise" value="$temp_fromExercise">-->
	<table border="0" align="center" cellpadding="2" cellspacing="2" width="95%">
	<tr>
cData;
	
	$tool_content .= "<td colspan=\"";
	if (isset($fromExercise))
		$tool_content .= "2";
	else
		$tool_content .= "3";
		
	$tool_content .= "\" align=\"right\">";
  
	$tool_content .= $langFilter." : <select name=\"exerciseId\">".
		"<option value=\"0\">-- ".$langAllExercises." --</option>".
		"<option value=\"-1\" ";
		
	if(isset($exerciseId) && $exerciseId == -1) 
		$tool_content .= "selected=\"selected\""; 
	$tool_content .= ">-- ".$langOrphanQuestions." --</option>";
	
	if (isset($fromExercise)) {
		mysql_select_db($currentCourseID);
		$sql="SELECT id,titre FROM `$TBL_EXERCICES` WHERE id<>'$fromExercise' ORDER BY id";
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
	} else {
		mysql_select_db($currentCourseID);
		$sql="SELECT id,titre FROM `$TBL_EXERCICES` ORDER BY id";
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
	}
	
	// shows a list-box allowing to filter questions
	while($row=mysql_fetch_array($result))
	{

	$tool_content .= "<option value=\"".$row['id']."\"";
	
	if(isset($exerciseId) && $exerciseId == $row['id']) 
		$tool_content .= "selected=\"selected\"";
	$tool_content .= ">".$row['titre']."</option>";

	}
	
$tool_content .= <<<cData
    </select> <input type="submit" value="${langOk}">
  </td>
</tr>
cData;

	@$from=$page*$limitQuestPage;
	
	//mysql_select_db($currentCourseID);
	
	// if we have selected an exercise in the list-box 'Filter'
	if(isset($exerciseId) && $exerciseId > 0)
	{
		$sql="SELECT id,question,type FROM `$TBL_EXERCICE_QUESTION`,`$TBL_QUESTIONS` WHERE question_id=id AND exercice_id='$exerciseId' ORDER BY q_position LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
	}
	// if we have selected the option 'Orphan questions' in the list-box 'Filter'
	elseif(isset($exerciseId) && $exerciseId == -1)
	{
		$sql="SELECT id,question,type FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` ON question_id=id WHERE exercice_id IS NULL ORDER BY question LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
	}
	// if we have not selected any option in the list-box 'Filter'
	else
	{		
		@$sql="SELECT id,question,type FROM `$TBL_QUESTIONS` LEFT JOIN `$TBL_EXERCICE_QUESTION` ON question_id=id WHERE exercice_id IS NULL OR exercice_id<>'$fromExercise' GROUP BY id ORDER BY question LIMIT $from,".($limitQuestPage+1);
		$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);

		// forces the value to 0
		$exerciseId=0;
	}

	$nbrQuestions=mysql_num_rows($result);

$tool_content .= <<<cData
	<tr>
	  <td colspan="
cData;


if (isset($fromExercise))
	$tool_content .= "2";
else
	$tool_content .= "3";

$tool_content .= "\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"95%\"><tr><td>";

	if(isset($fromExercise))
	{

		$tool_content .= "<a href=\"admin.php\">&lt;&lt; ".$langGoBackToEx."</a>";

	}
	else
	{

		$tool_content .= "<a href=\"admin.php?newQuestion=yes\">".$langNewQu."</a>";

	}

	  $tool_content .= "</td><td align=\"right\">";

	if(isset($page))
	{

	$tool_content .= "<small><a href=\"".$PHP_SELF.
		"?exerciseId=".$exerciseId.
		"&fromExercise=".$fromExercise.
		"&page=".($page-1)."\">&lt;&lt; ".$langPreviousPage."</a></small> |";

	}
	elseif($nbrQuestions > $limitQuestPage)
	{

	$tool_content .= "<small>&lt;&lt; $langPreviousPage |</small>";

	}

	if($nbrQuestions > $limitQuestPage)
	{

	$tool_content .= "<small><a href=\"".$PHP_SELF.
	"?exerciseId=".$exerciseId.
	"&fromExercise=".$fromExercise.
	"&page=".($page+1)."\">".$langNextPage.
	" &gt;&gt;</a></small>";

	}
	elseif(isset($page))
	{

	$tool_content .= "<small>$langNextPage &gt;&gt;</small>";

	}

	 $tool_content .= <<<cData
	 	</td>
			</tr>
			</table>
		  </td>
		</tr>
		<tr bgcolor="#E6E6E6">
cData;

	if(isset($fromExercise))
	{

	$tool_content .= <<<cData
	  <td width="80%" align="center">${langQuestion}</td>
	  <td width="20%" align="center">${langReuse}</td>
cData;

	}
	else
	{

  $tool_content .= <<<cData
	<td width="60%" align="center">${langQuestion}</td>
	<td width="20%" align="center">${langModify}</td>
	<td width="20%" align="center">${langDelete}</td>
cData;
	}

$tool_content .= "</tr>";

	$i=1;

	while($row=mysql_fetch_array($result))
	{
		// if we come from the exercise administration to get a question, doesn't show the question already used by that exercise
		if(!isset($fromExercise) || !is_object(@$objExercise) || !$objExercise->isInList($row['id']))
		{

//$tool_content .= "<tr><td><a href=\"admin.php?editQuestion=".$row['id'].
//	"&fromExercise=".$fromExercise."\">".$row['question']."</a></td><td align=\"center\">";

			if(!isset($fromExercise)) {
				//$tool_content .= "<a href=\"admin.php?editQuestion=".$row['id']."\"><img src=\"../../template/classic/img/edit.gif\" border=\"0\" alt=\"".$langModify."\"></a>";
				
				$tool_content .= "<tr><td><a href=\"admin.php?editQuestion=".$row['id'].
					"&fromExercise=\"\">".$row['question']."</a></td><td align=\"center\"><a href=\"admin.php?editQuestion=".$row['id']."\"><img src=\"../../template/classic/img/edit.gif\" border=\"0\" alt=\"".$langModify."\"></a>";
			} else {
				$tool_content .= "<tr><td><a href=\"admin.php?editQuestion=".$row['id'].
					"&fromExercise=".$fromExercise."\">".$row['question']."</a></td><td align=\"center\">";
				
				$tool_content .= "<a href=\"".$PHP_SELF."?recup=".$row['id'].
					"&fromExercise=".$fromExercise."\"><img src=\"../../template/classic/img/enroll.gif\" border=\"0\" alt=\"".$langReuse."\"></a>";
			}

  $tool_content .= "</td>";

			if(!isset($fromExercise))
			{

  $tool_content .= "<td align=\"center\"><a href=\"".$PHP_SELF."?exerciseId=".$exerciseId."&delete=".$row['id']."\"". 
		" onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langConfirmYourChoice)).
		"')) return false;\"><img src=\"../../template/classic/img/delete.gif\" border=\"0\" alt=\"".$langDelete."\"></a></td>";

			}

$tool_content .= "</tr>";

			// skips the last question, that is only used to know if we have or not to create a link "Next page"
			if($i == $limitQuestPage)
			{
				break;
			}

			$i++;
		}
	}

	if(!$nbrQuestions)
	{

$tool_content .= "<tr><td colspan=\"";

if (isset($fromExercise)&&($fromExercise))
	$tool_content .= "2";
else
	$tool_content .= "3";	
	
	$tool_content .= "\">".$langNoQuestion."</td></tr>";

	}

$tool_content .= <<<cData

</table>
</form>
cData;

}
// if not admin of course
else
{
	$tool_content .= $langNotAllowed;
}

draw($tool_content, 2);
?>
