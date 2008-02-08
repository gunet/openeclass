<?php
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
	addpoll.php
	@last update: 26-5-2006 by Dionysios Synodinos
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the poll tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights - to create polls.

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

$require_prof = TRUE;
$require_current_course = TRUE;
$langFiles = 'questionnaire';

$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';


// -------------- jscalendar -----------------
include('../../include/jscalendar/calendar.php');

if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

$u_date_start = strftime('%Y-%m-%d %H:%M:%S', strtotime('now -0 day'));
$u_date_end = strftime('%Y-%m-%d %H:%M:%S', strtotime('now +1 year'));

$start_cal_Poll = $jscalendar->make_input_field(
           array('showsTime'      => true,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d %H:%M:%S',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #fff; border: 1px dotted #000; text-align
: center',
                 'name'        => 'PollStart',
                 'value'       => $u_date_start));

$end_cal_Poll = $jscalendar->make_input_field(
           array('showsTime'      => true,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d %H:%M:%S',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #fff; border: 1px dotted #000; text-align: center',
                 'name'        => 'PollEnd',
                 'value'       => $u_date_end));


$nameTools = $langCreate;
$navigation[] = array("url"=>"questionnaire.php", "name"=> $langQuestionnaire);

$tool_content = "";

if (isset($_POST['PollCreate']))  {
 	  createMCPoll();
}

if (isset($_POST['MoreQuestions'])) 
		$questions++;

if (isset($_POST['MoreAnswers']))
		$answers++;

if(!isset($_REQUEST['UseCase'])) $_REQUEST['UseCase'] = "";

switch ($_REQUEST['UseCase']) {
case 1:
   // handle multi choice polls
   printMCQuestionForm();
   break;
case 2:
   // handle text input polls
   printTFQuestionForm();
   break;
default:
   // print new poll form
   printPollCreationForm();
}

// Get JS  ******************************************************
$head_content = <<<hContent
<script type="text/javascript">
<!-- Begin

function checkrequired(which, entry) {
var pass=true;
if (document.images) {
	for (i=0;i<which.length;i++) {
		var tempobj=which.elements[i];
		if (tempobj.name == entry) {
			if (tempobj.type=="text"&&tempobj.value=='') {
				pass=false;
				break;
		  }
	  }
	}
}
if (!pass) {
	alert("$langQQuestionNotGiven");
return false;
} else {
return true;
}
}
//  End -->
</script>
hContent;

if ($_REQUEST['UseCase'] ==1)
	draw($tool_content, 2, '', $head_content); 
else	
	draw($tool_content, 2, '', $local_head, '');


/*****************************************************************************
		Prints the new poll creation form
******************************************************************************/
function printPollCreationForm() {
	global $tool_content, $langName, $langPollStart, 
		$langPollEnd, $langType, $langPollMC, $langPollFillText, $langPollContinue, $langCreate, 
		$start_cal_Poll, $end_cal_Poll, $langCreatePoll;
	
	$CurrentDate = date("Y-m-d H:i:s");
	$tool_content .= <<<cData
    <form action="addpoll.php" id="poll" method="post">
    <table class='FormData'>
    <tbody>
    <tr>
      <th class='left' width='150'>&nbsp;</th>
      <td><b>$langCreatePoll</b></td>
    </tr>
    <tr>
      <th class='left'>$langName</th>
      <td><input type="text" size="50" name="PollName" class='FormData_InputText'></td>
    </tr>
    <tr>
      <th class='left'>$langPollStart</th>
      <td><!--<input type="text" size="17" name="PollStart" value="$CurrentDate">-->
      $start_cal_Poll
      </td>
    </tr>
    <tr>
      <th class='left'>$langPollEnd</th>
      <td><!--<input type="text" size="17" name="PollEnd">-->
       $end_cal_Poll
      </td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td>
      <input name="UseCase" type="hidden" value="1" />
      <input name="$langPollContinue" type="submit" value="$langPollContinue -&gt;">
      <input type="hidden" value="1" name="questions">
      <input type="hidden" value="2" name="answers">
      </td>
    </tr>
    </tbody>
    </table>
    </form>
cData;
}

/*****************************************************************************
		Prints new multiple choice question and 2 answers
******************************************************************************/
function printMCQuestionForm() {
		global $tool_content, $langName, $langPollStart, $langPollEnd, 
		$langType, $langPollMC, $langPollFillText, $langPollContinue, 
		$langQuestion, $langCreate, $langPollMoreQuestions, 
		$langPollCreated, $MoreQuestions, $langAnswer, 
	  $langPollMoreAnswers, $questions, $answers;
		
		if(isset($_POST['PollName'])) $PollName = htmlspecialchars($_POST['PollName']);
		if(isset($_POST['PollEnd'])) $PollEnd = htmlspecialchars($_POST['PollEnd']);
		if(isset($_POST['PollStart'])) $PollStart = htmlspecialchars($_POST['PollStart']);
		
		$tool_content .= "<form action='$_SERVER[PHP_SELF]' id='poll' method='post'>
    <input type='hidden' value='1' name='UseCase'>
    <table>
      <tr><th>$langName</th><td colspan='2'><input type='text' size='50' name='PollName' value='$PollName'></td></tr>
      <tr><th>$langPollStart</th><td colspan='2'><input type='text' size='17' name='PollStart' value='$PollStart'></td></tr>
      <tr><th>$langPollEnd</th><td colspan='2'><input type='text' size='17' name='PollEnd' value='$PollEnd'></td></tr>";

			for ($i=1; $i<=$questions; $i++) {
				$tool_content .= "<tr><td>$langQuestion #".$i."</td>
												<td><input type='text' name='question".$i."' size='50'></td></tr>";
					for ($j=$i; $j<=$answers; $j++) {
				    $tool_content .= "
								<tr><td>$langAnswer #".$j."</td><td><input type='text' name='answer".$j.".1' size='50'></td></tr>";
							}
				}
      $tool_content .= "<tr>
	      <td><input type='submit' name='MoreAnswers' value='$langPollMoreAnswers'></td>
        <td><input type='submit' name='MoreQuestions' value='$langPollMoreQuestions'></td>
        <td><input type='submit' name='PollCreate' value='$langCreate'></td>
      </tr>
    </table>
    <input type='hidden' value='1' name='NumOfQuestions'>
    <input type='hidden' value='$questions' name='questions'>
    <input type='hidden' value='$answers' name='answers'>
    </form>";
}

/*****************************************************************************
		Prints new text fill question
******************************************************************************/
function printTFQuestionForm() {
	global $tool_content, $langName, $langPollStart, $langPollEnd, 
		$langType, $langPollMC, $langPollFillText, $langPollContinue, 
		$langQuestion, $langCreate, $langPollMoreQuestions, 
		$langPollCreated, $MoreQuestions;
		
		if(isset($_POST['PollName'])) $PollName = htmlspecialchars($_POST['PollName']);
		if(isset($_POST['PollEnd'])) $PollEnd = htmlspecialchars($_POST['PollEnd']);
		if(isset($_POST['PollStart'])) $PollStart = htmlspecialchars($_POST['PollStart']);
		
	if ($MoreQuestions == 2) {
		createTFPoll();
	} else {
		$tool_content .= <<<cData
		<form action="addpoll.php" id="poll" method="post">
		<input type="hidden" value="2" name="UseCase">
		<table>
			<tr><td>$langName</td><td colspan="2"><input type="text" size="50" name="PollName" value="$PollName"></td></tr>
			<tr><td>$langPollStart</td><td colspan="2"><input type="text" size="10" name="PollStart" value="$PollStart"></td></tr>
			<tr><td>$langPollEnd</td><td colspan="2"><input type="text" size="10" name="PollEnd" value="$PollEnd"></td></tr>
cData;
		$counter = 0;
		foreach (array_keys($_POST) as $key) {
			++$counter;
		  $$key = $_POST[$key];
		  if (($counter > 4 )&($counter < count($_POST)-1)) {
				$tool_content .= "<tr><td>$langQuestion</td><td><input type='text' name='question{$counter}' value='${$key}'></td></tr>"; 
			}
		}
			
		$tool_content .= <<<cData
			<tr><td>$langQuestion</td><td><input type='text' name='question'></td></tr>
			<tr>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="1" />
		      $langPollMoreQuestions</label></td>
			  <td><label>
			    <input name="MoreQuestions" type="radio" value="2" checked/>
		      $langPollCreate</label></td>
			</tr>
			<tr><td colspan="2" align="right">
			  <input name="$langPollContinue" type="submit" value="$langPollContinue -&gt;"></td>
		</table>
		</form>
cData;
	}
}

function createTFPoll() {
	$counter = 0;
	foreach (array_keys($_POST) as $key) {
		++$counter;
		$$key = $_POST[$key];
		
		if ($counter == 2) {
			$PollName = $$key;
		} elseif ($counter == 3) {
			$CreationDate = date("Y-m-d H:i:s");
			$StartDate = $$key;
		} elseif ($counter == 4) {
			$EndDate = $$key;
			$PollType = 2;
			$PollActive = 1;
			$pid = date("YmdHms"); 
			mysql_select_db($GLOBALS['currentCourseID']);
			$result1 = db_query("INSERT INTO poll VALUES ('".
				mysql_real_escape_string($pid). "','".
				$GLOBALS['uid']. "','".
				$GLOBALS['currentCourseID']. "','".
				mysql_real_escape_string($PollName) 	. "','".
				mysql_real_escape_string($CreationDate) . "','".
				mysql_real_escape_string($StartDate) 		. "','".
				mysql_real_escape_string($EndDate) 			. "','".
				mysql_real_escape_string($PollType) 	. "','".
				mysql_real_escape_string($PollActive) ."')");
				//$GLOBALS["tool_content"] .= $result1;
		} elseif (($counter > 4)&&($counter <= count($_POST)-2)) {
			$QuestionText = $$key;
			mysql_select_db($GLOBALS['currentCourseID']);
			$result2 = db_query("INSERT INTO poll_question VALUES ('0','".
				mysql_real_escape_string($pid). "','".
				mysql_real_escape_string($QuestionText) ."')");
		}
	}	  
    
	$GLOBALS["tool_content"] .= $GLOBALS["langPollCreated"];
}

function createMCPoll() {
	
	global $tool_content, $langQuestion, $langAnswer ;

		$counter = 0;
		$CurrentQuestion = 0;
		$CurrentAnswer = 0;
		foreach (array_keys($_POST) as $key) {
			$$key = $_POST[$key];
			++$counter;
			
			// Populate poll table first
			if ($counter == 2) {
				$PollName = $$key;
			} elseif ($counter == 3) {
				$CreationDate = date("Y-m-d H:i:s");
				$StartDate = $$key;
			} elseif ($counter == 4) {
				$EndDate = $$key;
				$PollType = 1;
				$PollActive = 1;
				$pid = date("YmdHms"); 
				mysql_select_db($GLOBALS['currentCourseID']);
				$result3 = db_query("INSERT INTO poll VALUES ('".
					mysql_real_escape_string($pid). "','".
					$GLOBALS['uid']. "','".
					$GLOBALS['currentCourseID']. "','".
					mysql_real_escape_string($PollName) 	. "','".
					mysql_real_escape_string($CreationDate) . "','".
					mysql_real_escape_string($StartDate) 		. "','".
					mysql_real_escape_string($EndDate) 			. "','".
					mysql_real_escape_string($PollType) 	. "','".
					mysql_real_escape_string($PollActive) ."')");
			}	
			if (($counter >= 5)&&($counter <= (count($_POST)-4) )) { // question or anwser
				if (substr($key, 0, 8) == "question") { //question
					$QuestionText = $$key;
					$sqid = "";
					$pattern = "1234567890";
					for($i=0;$i<12;$i++)
						$sqid .= $pattern{rand(0,9)};
					mysql_select_db($GLOBALS['currentCourseID']);
					$result4 = db_query("INSERT INTO poll_question VALUES ('".
					$sqid . "','".
					mysql_real_escape_string($pid) . "','".
					mysql_real_escape_string($QuestionText) ."')");

				} else { //answer
					if ($$key != '') {
						$AnswerText = $$key;	
						mysql_select_db($GLOBALS['currentCourseID']);
						$result5 = db_query("INSERT INTO poll_question_answer VALUES ('0','".
							$sqid. "','".
							mysql_real_escape_string($AnswerText) ."')");
					}
				}
			}
	 }
	$GLOBALS["tool_content"] .= $GLOBALS["langPollCreated"];
}
function printAllQA() {

	global $tool_content,$langQuestion,$langAnswer;

		$counter = 0;
		$CurrentQuestion = 0;
		$CurrentAnswer = 0;
		foreach (array_keys($_POST) as $key) {
			$$key = $_POST[$key];
			++$counter;
			if (($counter >= 5)&&($counter <= (count($_POST)-3) )) { // question or anwser
				if (substr($key, 0, 8) == "question") { //question
					++$CurrentQuestion;
					$tool_content .= "<tr><td colspan=3><hr></td></tr> 
								<tr><td>" . $langQuestion . 
							" </td><td><input size='50' type='text' name='question{$CurrentQuestion}' value='".$$key."'>
								</td></tr>\n";
				} else { //answer
					if ($$key != '') {
						++$CurrentAnswer;
						$tool_content .= " <tr><td>" . $langAnswer . 
						" </td><td><input size='50' type='text' name='answer{$CurrentQuestion}.{$CurrentAnswer}' ".
						"value='{$$key}'></td></tr>\n";
						}
				}
		}
	}
}
?>
