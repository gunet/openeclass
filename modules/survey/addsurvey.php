<?php
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
	addsurvey.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the survey tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights - to create surveys.

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

$require_current_course = TRUE;
$langFiles = 'survey';

$require_help = TRUE;
$helpTopic = 'Survey';

include '../../include/baseTheme.php';

$tool_content = "";


switch ($UseCase) {
case 1:
   // handle multi choice surveys
   printMCQuestionForm();
   break;
case 2:
   // handle text input surveys
   printTFQuestionForm();
   break;
default:
   // print new survey form
   printSurveyCreationForm();
}

draw($tool_content, 2); 

/*****************************************************************************
		Prints the new survey creation form
******************************************************************************/
function printSurveyCreationForm() {
	global $tool_content, $langSurveyName, $langSurveyStart, 
		$langSurveyEnd, $langSurveyType, $langSurveyMC, $langSurveyFillText, $langSurveyContinue;
	
	$CurrentDate = date("Y-m-d H:i:s");
	$CurrentDate = htmlspecialchars($CurrentDate);
	$tool_content .= <<<cData
	<form action="addsurvey.php" id="survey">
	<input type="hidden" value="0" name="MoreQuestions">
	<table>
		<tr><td>$langSurveyName</td><td colspan="2"><input type="text" size="50" name="SurveyName"></td></tr>
		<tr><td>$langSurveyStart</td><td colspan="2">
			<input type="text" size="17" name="SurveyStart" value="$CurrentDate">
		</td></tr>
		<tr><td>$langSurveyEnd</td><td colspan="2"><input type="text" size="17" name="SurveyEnd"></td></tr>
		<tr>
		  <td>$langSurveyType</td>
		  <td><label>
		    <input name="UseCase" type="radio" value="1" />
	      $langSurveyMC</label></td>
		  <td><label>
		    <input name="UseCase" type="radio" value="2" />
	      $langSurveyFillText</label></td>
		</tr>
		<tr><td colspan="3" align="right">
		  <input name="$langSurveyContinue" type="submit" value="$langSurveyContinue -&gt;"></td>
	</table>
	</form>
cData;
}

/*****************************************************************************
		Prints new multiple choice question and 2 answers
******************************************************************************/
function printMCQuestionForm() {
		global $tool_content, $langSurveyName, $langSurveyStart, $langSurveyEnd, 
		$langSurveyType, $langSurveyMC, $langSurveyFillText, $langSurveyContinue, 
		$langSurveyQuestion, $langSurveyCreate, $langSurveyMoreQuestions, $SurveyName, 
		$SurveyStart, $SurveyEnd, $langSurveyCreated, $MoreQuestions, $langSurveyAnswer, 
		$langSurveyMoreAnswers, $UseCase;
		
	if ($MoreQuestions == 2) { // Create survey ******************************************************
		createMCSurvey();
	} elseif(count($_POST)<5) { // Just entered MC survey cretion dialiog ****************************
		$tool_content .= <<<cData
		<form action="addsurvey.php" id="survey" method="post">
		<input type="hidden" value="1" name="UseCase">
		<table>
			<tr><td>$langSurveyName</td><td colspan="2"><input type="text" size="50" name="SurveyName" value="$SurveyName"></td></tr>
			<tr><td>$langSurveyStart</td><td colspan="2"><input type="text" size="10" name="SurveyStart" value="$SurveyStart"></td></tr>
			<tr><td>$langSurveyEnd</td><td colspan="2"><input type="text" size="10" name="SurveyEnd" value="$SurveyEnd"></td></tr>
			<tr><td>$langSurveyQuestion</td><td><input type="text" name="question1" size="50"></td></tr> 
			<tr><td>$langSurveyAnswer #1</td><td><input type="text" name="answer1.1" size="50"></td></tr>
			<tr><td>$langSurveyAnswer #2</td><td><input type="text" name="answer2.1" size="50"></td></tr>
			<tr>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="1" />
		      $langSurveyMoreAnswers</label></td>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="3" />
		      $langSurveyMoreQuestions</label></td>
		    <td><label>
			    <input name="MoreQuestions" type="radio" value="2" />
		      $langSurveyCreate</label></td>
			</tr>
			<tr><td colspan="2" align="right">
			  <input name="$langSurveyContinue" type="submit" value="$langSurveyContinue -&gt;"></td>
		</table>
		<input type="hidden" value="1" name="NumOfQuestions">
		</form>
cData;
	} elseif ($MoreQuestions == 1) {  // Print more answers ***************************************************
		$NumOfQuestions = $_POST['NumOfQuestions'];
		
		$tool_content .= <<<cData
		<form action="addsurvey.php" id="survey" method="post">
		<input type="hidden" value="1" name="UseCase">
		<table>
			<tr><td>$langSurveyName</td><td colspan="2"><input type="text" size="50" name="SurveyName" value="$SurveyName"></td></tr>
			<tr><td>$langSurveyStart</td><td colspan="2"><input type="text" size="10" name="SurveyStart" value="$SurveyStart"></td></tr>
			<tr><td>$langSurveyEnd</td><td colspan="2"><input type="text" size="10" name="SurveyEnd" value="$SurveyEnd"></td></tr>
			
cData;

		$tool_content .= "\n<!-- BEGIN printAllQA() -->\n\n";
		printAllQA();
		$tool_content .= "\n\n<!-- END printAllQA() -->\n\n";
		
			
		$tool_content .= <<<cData
					<tr><td>$langSurveyAnswer</td><td colspan="2"><input type="text" size="10" name="answer" value=""></td></tr>
						<tr>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="1" />
		      $langSurveyMoreAnswers</label></td>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="3" />
		      $langSurveyMoreQuestions</label></td>
		    <td><label>
			    <input name="MoreQuestions" type="radio" value="2" />
		      $langSurveyCreate</label></td>
			</tr>
			<tr><td colspan="2" align="right">
			  <input name="$langSurveyContinue" type="submit" value="$langSurveyContinue -&gt;"></td>
		</table>
		<input type="hidden" value="{$NumOfQuestions}" name="NumOfQuestions">
		</form>
cData;
	} else {  // Print more questions ******************************************************
		$NumOfQuestions = $_POST['NumOfQuestions'];
		++$NumOfQuestions;
		
		$tool_content .= <<<cData
		<form action="addsurvey.php" id="survey" method="post">
		<input type="hidden" value="1" name="UseCase">
		<table>
		<tr><td>$langSurveyName</td><td colspan="2"><input type="text" size="50" name="SurveyName" value="$SurveyName"></td></tr>
			<tr><td>$langSurveyStart</td><td colspan="2"><input type="text" size="10" name="SurveyStart" value="$SurveyStart"></td></tr>
			<tr><td>$langSurveyEnd</td><td colspan="2"><input type="text" size="10" name="SurveyEnd" value="$SurveyEnd"></td></tr>
			
cData;
		
		$tool_content .= "\n<!-- BEGIN printAllQA() -->\n\n";
		printAllQA();
		$tool_content .= "\n\n<!-- END printAllQA() -->\n\n";
		
		$tool_content .= "<tr><td colspan=3><hr></td></tr> <tr> <td>" . 
//			$langSurveyQuestion . "	</td><td><input type='text' name='question" .
//			($answer_num + 1) . "'></td></tr>";
				$langSurveyQuestion . "	</td><td><input type='text' name='questionx'></td></tr>".
				"<tr><td>$langSurveyAnswer #1</td><td><input type='text' name='answerx1' size='50'></td></tr>".
				"<tr><td>$langSurveyAnswer #2</td><td><input type='text' name='answerx2' size='50'></td></tr>";
			
		$tool_content .= <<<cData
				<tr><td colspan=3><hr></td></tr>
				<tr>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="1" />
		      $langSurveyMoreAnswers</label></td>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="3" />
		      $langSurveyMoreQuestions</label></td>
		    <td><label>
			    <input name="MoreQuestions" type="radio" value="2" checked="checked" />
		      $langSurveyCreate</label></td>
			</tr>
			<tr><td colspan="2" align="right">
			  <input name="$langSurveyContinue" type="submit" value="$langSurveyContinue -&gt;"></td>
		</table>
		<input type="hidden" value="{$NumOfQuestions}" name="NumOfQuestions">
		</form>
cData;
	}
}



/*****************************************************************************
		Prints new text fill question
******************************************************************************/
function printTFQuestionForm() {
	global $tool_content, $langSurveyName, $langSurveyStart, $langSurveyEnd, 
		$langSurveyType, $langSurveyMC, $langSurveyFillText, $langSurveyContinue, 
		$langSurveyQuestion, $langSurveyCreate, $langSurveyMoreQuestions, $SurveyName, 
		$SurveyStart, $SurveyEnd, $langSurveyCreated, $MoreQuestions;
	if ($MoreQuestions == 2) {
		createTFSurvey();
	} else {
		$tool_content .= <<<cData
		<form action="addsurvey.php" id="survey" method="post">
		<input type="hidden" value="2" name="UseCase">
		<table>
			<tr><td>$langSurveyName</td><td colspan="2"><input type="text" size="50" name="SurveyName" value="$SurveyName"></td></tr>
			<tr><td>$langSurveyStart</td><td colspan="2"><input type="text" size="10" name="SurveyStart" value="$SurveyStart"></td></tr>
			<tr><td>$langSurveyEnd</td><td colspan="2"><input type="text" size="10" name="SurveyEnd" value="$SurveyEnd"></td></tr>
cData;
		$counter = 0;
		foreach (array_keys($_POST) as $key) {
			++$counter;
		  $$key = $_POST[$key];
		  if (($counter > 4 )&($counter < count($_POST)-1)) {
				$tool_content .= "<tr><td>$langSurveyQuestion</td><td><input type='text' name='question{$counter}' value='${$key}'></td></tr>"; 
			}
			//$tool_content .= $$key ."|". $key ."<br>"; 
		}
			
		$tool_content .= <<<cData
			<tr><td>$langSurveyQuestion</td><td><input type='text' name='question'></td></tr>
			<tr>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="1" />
		      $langSurveyMoreQuestions</label></td>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="2" checked="checked" />
		      $langSurveyCreate</label></td>
			</tr>
			<tr><td colspan="2" align="right">
			  <input name="$langSurveyContinue" type="submit" value="$langSurveyContinue -&gt;"></td>
		</table>
		</form>
cData;
	}
}

function createTFSurvey() {
	$counter = 0;
	foreach (array_keys($_POST) as $key) {
		++$counter;
		$$key = $_POST[$key];
		
		if ($counter == 2) {
			$SurveyName = $$key;
		} elseif ($counter == 3) {
			$CreationDate = date("Y-m-d H:i:s");
			$StartDate = $$key;
		} elseif ($counter == 4) {
			$EndDate = $$key;
			$SurveyType = 2;
			$SurveyActive = 1;
			$sid = date("YmdHms"); 
			mysql_select_db($GLOBALS['currentCourseID']);
			$result1 = db_query("INSERT INTO survey VALUES ('".
				$sid. "','".
				$GLOBALS['uid']. "','".
				$GLOBALS['currentCourseID']. "','".
				$SurveyName 	. "','".
				$CreationDate . "','".
				$StartDate 		. "','".
				$EndDate 			. "','".
				$SurveyType 	. "','".
				$SurveyActive ."')");
				//$GLOBALS["tool_content"] .= $result1;
		} elseif (($counter > 4)&&($counter <= count($_POST)-2)) {
			$QuestionText = $$key;
			///////////
			$sqid = "";
			$pattern = "1234567890";
			for($i=0;$i<12;$i++)
				$sqid .= $pattern{rand(0,9)};
			///////////
			mysql_select_db($GLOBALS['currentCourseID']);
			$result2 = db_query("INSERT INTO survey_question VALUES ('".
				$sqid. "','".
				$sid. "','".
				$QuestionText ."')");
		}
	}	  
    
	$GLOBALS["tool_content"] .= $GLOBALS["langSurveyCreated"];
}
function createMCSurvey() {
	
	global $tool_content,$langSurveyQuestion,$langSurveyAnswer ;

	// insert into survey as above //////////////////////////////////////////////////////////////
	

		$counter = 0;
		$CurrentQuestion = 0;
		$CurrentAnswer = 0;
		foreach (array_keys($_POST) as $key) {
			$$key = $_POST[$key];
			++$counter;
			
			// Populate survey table first
			if ($counter == 2) {
				$SurveyName = $$key;
			} elseif ($counter == 3) {
				$CreationDate = date("Y-m-d H:m:s");
				$StartDate = $$key;
			} elseif ($counter == 4) {
				$EndDate = $$key;
				$SurveyType = 1;
				$SurveyActive = 1;
				$sid = date("YmdHms"); 
				//$tool_content .= "<br>About to create SURVEY entry<br>";
				mysql_select_db($GLOBALS['currentCourseID']);
				$result3 = db_query("INSERT INTO survey VALUES ('".
					$sid. "','".
					$GLOBALS['uid']. "','".
					$GLOBALS['currentCourseID']. "','".
					$SurveyName 	. "','".
					$CreationDate . "','".
					$StartDate 		. "','".
					$EndDate 			. "','".
					$SurveyType 	. "','".
					$SurveyActive ."')");
			}	
			if (($counter >= 5)&&($counter <= (count($_POST)-3) )) { // question or anwser
				//$tool_content .= "<br>Began iterating QAs";
				if (substr($key, 0, 8) == "question") { //question
					//$tool_content .= "<br>Is Q";
					// insert into survey_question //////////////////////////////////////////////////////////////
					$QuestionText = $$key;
					$sqid = "";
					$pattern = "1234567890";
					for($i=0;$i<12;$i++)
						$sqid .= $pattern{rand(0,9)};
					//$tool_content .= "<br>sqid " . $sqid ."<br>";
					//$tool_content .= "<br>About to create SURVEY_QUESTION entry<br>";
					mysql_select_db($GLOBALS['currentCourseID']);
					$result4 = db_query("INSERT INTO survey_question VALUES ('".
					$sqid . "','".
					$sid . "','".
					$QuestionText ."')");

				} else { //answer
					// insert into survey_question_answer //////////////////////////////////////////////////////////////
					//$tool_content .= "<br>Is A";
					if ($$key != '') {
						$AnwserText = $$key;	
						//$tool_content .= "<br>About to create SURVEY_QUESTION_ANSWER entry<br>";
						mysql_select_db($GLOBALS['currentCourseID']);
						$result5 = db_query("INSERT INTO survey_question_answer VALUES ('0','".
							$sqid. "','".
							$AnwserText ."')");
					}
				}
			}
	}
	$GLOBALS["tool_content"] .= $GLOBALS["langSurveyCreated"];
}
function printAllQA() {
	global $tool_content,$langSurveyQuestion,$langSurveyAnswer ;

		$counter = 0;
		$CurrentQuestion = 0;
		$CurrentAnswer = 0;
		foreach (array_keys($_POST) as $key) {
			$$key = $_POST[$key];
			//$tool_content .= "$key = " . $key . " | $$key = " . $$key . "<br>\n\n"; 
			++$counter;
			if (($counter >= 5)&&($counter <= (count($_POST)-3) )) { // question or anwser
				if (substr($key, 0, 8) == "question") { //question
					++$CurrentQuestion;
					$tool_content .= "<tr><td colspan=3><hr></td></tr> <tr><td>" . $langSurveyQuestion . 
						" </td><td><input type='text' name='question{$CurrentQuestion}' value='".
						$$key."'></td></tr>\n";
				} else { //answer
					if ($$key != '') {
						++$CurrentAnswer;
						$tool_content .= " <tr><td>" . $langSurveyAnswer . 
						" </td><td><input type='text' name='answer{$CurrentQuestion}.{$CurrentAnswer}' ".
						"value='{$$key}'></td></tr>\n";
					}
					
				}
				
				
			}
			
		}
}
?>
