<?php
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
 
==============================================================================
*/

$require_prof = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';
include('../../include/jscalendar/calendar.php');
$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

$u_date_start = strftime('%Y-%m-%d %H:%M:%S', strtotime('now -0 day'));
$u_date_end = strftime('%Y-%m-%d %H:%M:%S', strtotime('now +1 year'));

$start_cal_Survey = $jscalendar->make_input_field(
           array('showsTime'      => true,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d %H:%M:%S',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'SurveyStart',
                 'value'       => $u_date_start));

$end_cal_Survey = $jscalendar->make_input_field(
           array('showsTime'      => true,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d %H:%M:%S',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'SurveyEnd',
                 'value'       => $u_date_end));

$nameTools = $langCreate;
$navigation[] = array("url"=>"questionnaire.php", "name"=> $langQuestionnaire);

$tool_content = "";

if(!isset($_REQUEST['UseCase'])) $_REQUEST['UseCase'] = "";

switch ($_REQUEST['UseCase']) {
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

function addEvent(SelectedQuestion) {
	
	var CurrentQuestion = new Array(6);

	var question1= new Array(6);
	question1[0]="Σε αυτή την ενότητα, η προσπάθια μου επικεντρώθηκε σε θέματα που με ενδιέφεραν.";
	question1[1]="Σχεδόν ποτέ.";
	question1[2]="Σπάνια.";
	question1[3]="Μερικές φορές.";
	question1[4]="Συχνά.";
	question1[5]="Σχεδόν πάντα.";
	
	var question2= new Array(6);
	question2[0]="Σε αυτή την ενότητα, αυτά που μαθαίνω έχουν να κάνουν με το επάγγελμά μου.";
	question2[1]="Σχεδόν ποτέ.";                                                                   
	question2[2]="Σπάνια.";                                                                        
	question2[3]="Μερικές φορές.";                                                                 
	question2[4]="Συχνά.";                                                                         
	question2[5]="Σχεδόν πάντα.";    
	
	var question3= new Array(6);
	question3[0]="Σε αυτή την ενότητα, ασκώ κριτική σκέψη.";
	question3[1]="Σχεδόν ποτέ.";                                                                   
	question3[2]="Σπάνια.";                                                                        
	question3[3]="Μερικές φορές.";                                                                 
	question3[4]="Συχνά.";                                                                         
	question3[5]="Σχεδόν πάντα.";     
	
	var question4= new Array(6);
	question4[0]="Σε αυτή την ενότητα, συνεργάζομαι με τους συμφοιτητές μου.";
	question4[1]="Σχεδόν ποτέ.";                                                                   
	question4[2]="Σπάνια.";                                                                        
	question4[3]="Μερικές φορές.";                                                                 
	question4[4]="Συχνά.";                                                                         
	question4[5]="Σχεδόν πάντα.";    
	
	var question5= new Array(6);
	question5[0]="Σε αυτή την ενότητα, η διδασκαλία κρίνεται ικανοποιητική.";
	question5[1]="Σχεδόν ποτέ.";                                                                   
	question5[2]="Σπάνια.";                                                                        
	question5[3]="Μερικές φορές.";                                                                 
	question5[4]="Συχνά.";                                                                         
	question5[5]="Σχεδόν πάντα.";                                                         
	
	var question6= new Array(6);
	question6[0]="Σε αυτή την ενότητα, υπάρχει σωστή επικοινωνία με τον διδάσκοντα.";
	question6[1]="Σχεδόν ποτέ.";                                                                   
	question6[2]="Σπάνια.";                                                                        
	question6[3]="Μερικές φορές.";                                                                 
	question6[4]="Συχνά.";                                                                         
	question6[5]="Σχεδόν πάντα."; 
	
	var question7= new Array(6);
	question7[0]="Προσπαθώ να βρίσκω λάθη στο σκεπτικό του συνομιλητή μου.";
	question7[1]="ΟΧΙ!";                                                                   
	question7[2]="ίσως όχι.";                                                                        
	question7[3]="Ούτε συμφωνώ, ούτε διαφωνώ.";                                                                 
	question7[4]="Ίσως ναι.";                                                                         
	question7[5]="ΝΑΙ!";
	question7[5]="";  
	
	var question8= new Array(6);
	question8[0]="Όταν συζητώ μπαίνω στην θέση του συνομιλητή μου.";
	question8[1]="ΟΧΙ!";                                                                   
	question8[2]="ίσως όχι.";                                                                        
	question8[3]="Ούτε συμφωνώ, ούτε διαφωνώ.";                                                                 
	question8[4]="Ίσως ναι.";                                                                         
	question8[5]="ΝΑΙ!"; 
	question8[5]="";
	
	var question9= new Array(6);
	question9[0]="Μένω αντικειμενικός κατά την ανάλυση καταστάσεων.";
	question9[1]="ΟΧΙ!";                                                                   
	question9[2]="ίσως όχι.";                                                                        
	question9[3]="Ούτε συμφωνώ, ούτε διαφωνώ.";                                                                 
	question9[4]="Ίσως ναι.";                                                                         
	question9[5]="ΝΑΙ!"; 
	question9[5]=""; 
	
	var question10= new Array(6);
	question10[0]="Μου αρέσει να παίρνω τον ρόλο του συνήγορου του διαβόλου.";
	question10[1]="ΟΧΙ!";                                                                   
	question10[2]="ίσως όχι.";                                                                        
	question10[3]="Ούτε συμφωνώ, ούτε διαφωνώ.";                                                                 
	question10[4]="Ίσως ναι.";                                                                         
	question10[5]="ΝΑΙ!"; 
	question10[5]="";
	
	var PollForm = document.getElementById('survey');
	
	var NewQuestion = document.getElementById('NewQuestion');
	
	switch(SelectedQuestion) {
      case 1:   CurrentQuestion = question1; break
      case 2:   CurrentQuestion = question2; break
      case 3:   CurrentQuestion = question3; break
      case 4:   CurrentQuestion = question4; break
      case 5:   CurrentQuestion = question5; break
      case 6:   CurrentQuestion = question6; break
      case 7:   CurrentQuestion = question7; break
      case 8:   CurrentQuestion = question8; break
      case 9:   CurrentQuestion = question9; break
      case 10:   CurrentQuestion = question10; break
      default:  alert("JS error");         
   }
	
	NewQuestion.value = CurrentQuestion[0];
	
	var NewAnswer; 
	var OldAnswer;
	for(var i=1; i < CurrentQuestion.length; i++ ){
		if (CurrentQuestion[i] != "") {
			if (i<3) {
				NewAnswer = document.getElementById('NewAnswer'+i);
				NewAnswer.value = CurrentQuestion[i];
			} else {
				
				NewAnswer = document.createElement('input');
				NewAnswer.value = CurrentQuestion[i];
				NewAnswer.setAttribute('id','NewAnswer'+i);
				NewAnswer.setAttribute('type','text');
				NewAnswer.setAttribute('size','70');
				NewAnswer.setAttribute('name','answerx.'+i);
				
				TheTABLE = document.getElementById('QuestionTable');
				
				NewTR = document.createElement('tr');
				LabelTD = document.createElement('td');
				LabelP = document.createElement('p');
				LabelP.value = "Απάντηση";
				LabelTD.appendChild(LabelP);
				NewTD = document.createElement('td');
				NewTD.appendChild(NewAnswer);
				NewTR.appendChild(LabelTD);
				NewTR.appendChild(NewTD);
				OldTR = document.getElementById('NextLine');
				OldTR.parentNode.insertBefore(NewTR,OldTR);
			}
		}
	}
}

function removeEvent() {
	var PollForm = document.getElementById('survey');
	var SelectElement = document.getElementById('QuestionSelector');
	try {
		PollForm.removeChild(SelectElement);
	} catch (err) {
		alert("ERROR: "+err);
	}
}
</script>
hContent;

if ($_REQUEST['UseCase'] ==1)
	draw($tool_content, 2, 'survey', $head_content); 
else	
	draw($tool_content, 2, '', $local_head, '');

/*****************************************************************************
		Prints the new survey creation form
******************************************************************************/
function printSurveyCreationForm() {
	global $tool_content, $langTitle, $langPollStart, 
		$langPollEnd, $langType, $langSurveyMC, $langSurveyFillText, 
		$langCreate, $langSurveyContinue,  $start_cal_Survey, $end_cal_Survey;
	
	$CurrentDate = date("Y-m-d H:i:s");
	$CurrentDate = htmlspecialchars($CurrentDate);
	$tool_content .= <<<cData
	<form action="addsurvey.php" id="survey" method="post">
	<input type="hidden" value="0" name="MoreQuestions">
	<table><thead></thead>
		<tr><td>$langTitle</td><td colspan="2"><input type="text" size="50" name="SurveyName"></td></tr>
		<tr><td>$langPollStart</td><td colspan="2">
			$start_cal_Survey
		</td></tr>
		<tr><td>$langPollEnd</td><td colspan="2">$end_cal_Survey</td></tr>
		<!--<tr>
		  <td>$langType</td>
		  <td><label>
		    <input name="UseCase" type="radio" value="1" />
	      $langSurveyMC</label></td>
		  <td><label>
		    <input name="UseCase" type="radio" value="2" />
	      $langSurveyFillText</label></td>
		</tr>-->
		<input name="UseCase" type="hidden" value="1" />
		<tr><td colspan="3" align="right">
      <input name="$langSurveyContinue" type="submit" value="$langSurveyContinue -&gt;"></td></tr>
	</table>
	</form>
cData;
}

/*****************************************************************************
		Prints new multiple choice question and 2 answers
******************************************************************************/
function printMCQuestionForm() {

	global $tool_content, $langTitle, $langSurveyStart, $langSurveyEnd, 
		$langType, $langSurveyMC, $langSurveyFillText, 
		$langQuestion, $langCreate, $langSurveyMoreQuestions, 
		$langSurveyCreated, $MoreQuestions, $langAnswer, 
		$langSurveyMoreAnswers, $langSurveyInfo,
		$langQuestion1, $langQuestion2, $langQuestion3, $langQuestion4, $langQuestion5, $langQuestion6,
		$langQuestion7, $langQuestion8,$langQuestion9, $langQuestion10;
		
		if(isset($_POST['SurveyName'])) $SurveyName = htmlspecialchars($_POST['SurveyName']);
		if(isset($_POST['SurveyEnd'])) $SurveyEnd = htmlspecialchars($_POST['SurveyEnd']);
		if(isset($_POST['SurveyStart'])) $SurveyStart = htmlspecialchars($_POST['SurveyStart']);
		
//	if ($MoreQuestions == 2) { // Create survey ******************************************************
	if ($MoreQuestions == $langCreate) { // Create survey
		createMCSurvey();
	} elseif(count($_POST)<7) { // Just entered MC survey creation dialiog ****************************
		$tool_content .= <<<cData
		<table><thead></thead>
	<tr><td colspan=2>$langSurveyInfo</td></tr></table>
	<form action="addsurvey.php" id="survey" method="post" name="SurveyForm" onSubmit="return checkrequired(this, 'question1')">
	<input type="hidden" value="1" name="UseCase">
	<table id="QuestionTable">
	<tr><td>$langTitle</td><td colspan="2"><input type="text" size="50" name="SurveyName" value="$SurveyName"></td></tr>
	<tr><td>$langSurveyStart</td><td colspan="2"><input type="text" size="20" name="SurveyStart" value="$SurveyStart"></td></tr>
	<tr><td>$langSurveyEnd</td><td colspan="2"><input type="text" size="20" name="SurveyEnd" value="$SurveyEnd"></td></tr>
	<tr><td colspan=3>
	<SELECT NAME="questionx" onChange="addEvent(this.selectedIndex);this.parentNode.removeChild(this);" id="QuestionSelector">
				<OPTION>$langSurveyInfo</option>
				<OPTION VALUE="question1">$langQuestion1[0]</option>
        <OPTION VALUE="question2">$langQuestion2[0]</option>
        <OPTION VALUE="question3">$langQuestion3[0]</option>
        <OPTION VALUE="question4">$langQuestion4[0]</option>
        <OPTION VALUE="question5">$langQuestion5[0]</option>
        <OPTION VALUE="question6">$langQuestion6[0]</option>
        <OPTION VALUE="question7">$langQuestion7[0]</option>
        <OPTION VALUE="question8">$langQuestion8[0]</option>
        <OPTION VALUE="question9">$langQuestion9[0]</option>
        <OPTION VALUE="question10">$langQuestion10[0]</option>
				</SELECT>
			</td></tr>
			<tr><td>$langQuestion</td><td><input type="text" name="question1" size="70" id="NewQuestion"></td></tr> 
			<tr><td>$langAnswer 1</td><td><input type="text" name="answer1.1" size="70" id="NewAnswer1"></td></tr>
			<tr><td>$langAnswer 2</td><td><input type="text" name="answer1.2" size="70" id="NewAnswer2"></td></tr>
			<tr id="NextLine">
			  <td>
			    <input name="MoreQuestions" type="submit" value="$langSurveyMoreAnswers" /></td>
			  <td>
			    <input name="MoreQuestions" type="submit" value="$langSurveyMoreQuestions" /></td>
		    <td>
					<input name="MoreQuestions" type="submit" value="$langCreate"></td>
			</tr>
		</table>
		<input type="hidden" value="1" name="NumOfQuestions">
		</form>
cData;
	} elseif ($MoreQuestions == $langSurveyMoreAnswers) {  // Print more answers 
		$NumOfQuestions = $_POST['NumOfQuestions'];
		
		$tool_content .= <<<cData
		<form action="addsurvey.php" id="survey" method="post">
		<input type="hidden" value="1" name="UseCase">
		<table>
			<tr><td>$langTitle</td><td colspan="2"><input type="text" size="50" name="SurveyName" value="$SurveyName"></td></tr>
			<tr><td>$langSurveyStart</td><td colspan="2"><input type="text" size="10" name="SurveyStart" value="$SurveyStart"></td></tr>
			<tr><td>$langSurveyEnd</td><td colspan="2"><input type="text" size="10" name="SurveyEnd" value="$SurveyEnd"></td></tr>
			
cData;

		printAllQA();
		$tool_content .= <<<cData
					<tr><td>$langAnswer</td><td colspan="2"><input type="text" size="10" name="answer" value=""></td></tr>
						<tr>
			  <td>
			    <input name="MoreQuestions" type="submit" value="$langSurveyMoreAnswers" />
		     </td>
			  <td>
			    <input name="MoreQuestions" type="submit" value="$langSurveyMoreQuestions" />
		     </td>
		    <td>
			    <input name="MoreQuestions" type="submit" value="$langCreate" />
		     </td>
			</tr>
		</table>
		<input type="hidden" value="{$NumOfQuestions}" name="NumOfQuestions">
		</form>
cData;
	} else {  // Print more questions ******************************************************
		$NumOfQuestions = $_POST['NumOfQuestions'];
		++$NumOfQuestions;
		
		$tool_content .= <<<cData
		<form action="addsurvey.php" id="survey" method="post" name="SurveyForm"  onSubmit="return checkrequired(this, 'questionx')">
		<input type="hidden" value="1" name="UseCase">
		<table>
		<tr><td>$langTitle</td><td colspan="2">
				<input type="text" size="50" name="SurveyName" value="$SurveyName"></td></tr>
		<tr><td>$langSurveyStart</td><td colspan="2">
					<input type="text" size="20" name="SurveyStart" value="$SurveyStart"></td></tr>
		<tr><td>$langSurveyEnd</td><td colspan="2">
					<input type="text" size="20" name="SurveyEnd" value="$SurveyEnd"></td></tr>
			
cData;
		
		printAllQA();
		
		$tool_content .= <<<cData
		<tr><td colspan=3><hr></td></tr>
			<tr><td colspan=3>
				<SELECT NAME="questionx" onChange="addEvent(this.selectedIndex);this.parentNode.removeChild(this);" id="QuestionSelector">
				<OPTION>$langSurveyInfo</option>
				<OPTION VALUE="question1">$langQuestion1[0]</option>
				<OPTION VALUE="question2">$langQuestion2[0]</option>
				<OPTION VALUE="question3">$langQuestion3[0]</option>
				<OPTION VALUE="question4">$langQuestion4[0]</option>
				<OPTION VALUE="question5">$langQuestion5[0]</option>
				<OPTION VALUE="question6">$langQuestion6[0]</option>
				<OPTION VALUE="question7">$langQuestion7[0]</option>
				<OPTION VALUE="question8">$langQuestion8[0]</option>
				<OPTION VALUE="question9">$langQuestion9[0]</option>
				<OPTION VALUE="question10">$langQuestion10[0]</option>
				</SELECT>
			</td></tr>
cData;
		
		$tool_content .= "<tr> <td>" . 
				$langQuestion . "	</td><td><input type='text' name='questionx' size='70' id='NewQuestion'></td></tr>".
				"<tr><td>$langAnswer 1</td><td><input type='text' name='answerx.1' size='70' id='NewAnswer1'></td></tr>".
				"<tr><td>$langAnswer 2</td><td><input type='text' name='answerx.2' size='70' id='NewAnswer2'></td></tr>";
			
		$tool_content .= <<<cData
				<tr id="NextLine"><td colspan=3><hr></td></tr>
				<tr>
			  <td>
			    <input name="MoreQuestions" type="submit" value="$langSurveyMoreAnswers" />
		     </td>
			  <td>
			    <input name="MoreQuestions" type="submit" value="$langSurveyMoreQuestions" />
		    </td>
		    <td>
			    <input name="MoreQuestions" type="submit" value="$langCreate" />
		     </td>
			</tr>
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
	global $tool_content, $langTitle, $langSurveyStart, $langSurveyEnd, 
		$langType, $langSurveyMC, $langSurveyFillText, 
		$langQuestion, $langCreate, $langSurveyMoreQuestions, 
		$langSurveyCreated, $MoreQuestions;
		
		if(isset($_POST['SurveyName'])) $SurveyName = htmlspecialchars($_POST['SurveyName']);
		if(isset($_POST['SurveyEnd'])) $SurveyEnd = htmlspecialchars($_POST['SurveyEnd']);
		if(isset($_POST['SurveyStart'])) $SurveyStart = htmlspecialchars($_POST['SurveyStart']);
		
//	if ($MoreQuestions == 2) {
	if ($MoreQuestions == $langCreate) {
		createTFSurvey();
	} else {
		$tool_content .= <<<cData
		<form action="addsurvey.php" id="survey" method="post">
		<input type="hidden" value="2" name="UseCase">
		<table>
			<tr><td>$langTitle</td><td colspan="2"><input type="text" size="50" name="SurveyName" value="$SurveyName"></td></tr>
			<tr><td>$langSurveyStart</td><td colspan="2"><input type="text" size="20" name="SurveyStart" value="$SurveyStart"></td></tr>
			<tr><td>$langSurveyEnd</td><td colspan="2"><input type="text" size="20" name="SurveyEnd" value="$SurveyEnd"></td></tr>
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
			  <td>
			    <input name="MoreQuestions" type="submit" value="$langSurveyMoreQuestions" />
		      </td>
			  <td>
			    <input name="MoreQuestions" type="submit" value="$langCreate" />
		      </td>
			</tr>
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
				mysql_real_escape_string($sid). "','".
				$GLOBALS['uid']. "','".
				$GLOBALS['currentCourseID']. "','".
				mysql_real_escape_string($SurveyName) 	. "','".
				mysql_real_escape_string($CreationDate) . "','".
				mysql_real_escape_string($StartDate) 		. "','".
				mysql_real_escape_string($EndDate) 			. "','".
				mysql_real_escape_string($SurveyType) 	. "','".
				mysql_real_escape_string($SurveyActive) ."')");
		} elseif (($counter > 4)&&($counter <= count($_POST)-2)) {
			$QuestionText = $$key;
			$sqid = "";
			$pattern = "1234567890";
			for($i=0;$i<12;$i++)
				$sqid .= $pattern{rand(0,9)};
			mysql_select_db($GLOBALS['currentCourseID']);
			$result2 = db_query("INSERT INTO survey_question VALUES ('".
				$sqid. "','".
				mysql_real_escape_string($sid). "','".
				mysql_real_escape_string($QuestionText) ."')");
		}
	}	  
    
	$GLOBALS["tool_content"] .= $GLOBALS["langSurveyCreated"];
}
function createMCSurvey() {
	
	global $tool_content, $langQuestion, $langAnswer;

	// insert into survey as above
	
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
				mysql_select_db($GLOBALS['currentCourseID']);
				$result3 = db_query("INSERT INTO survey VALUES ('".
					mysql_real_escape_string($sid). "','".
					$GLOBALS['uid']. "','".
					$GLOBALS['currentCourseID']. "','".
					mysql_real_escape_string($SurveyName) 	. "','".
					mysql_real_escape_string($CreationDate) . "','".
					mysql_real_escape_string($StartDate) 		. "','".
					mysql_real_escape_string($EndDate) 			. "','".
					mysql_real_escape_string($SurveyType) 	. "','".
					mysql_real_escape_string($SurveyActive) ."')");
			}	
			if (($counter >= 5) && ($counter <= (count($_POST)-2) )) { // question or anwser
				if (substr($key, 0, 8) == "question") { //question
					$QuestionText = $$key;
					$sqid = "";
					$pattern = "1234567890";
					for($i=0;$i<12;$i++)
						$sqid .= $pattern{rand(0,9)};
					mysql_select_db($GLOBALS['currentCourseID']);
					$result4 = db_query("INSERT INTO survey_question VALUES ('".
					$sqid . "','".
					mysql_real_escape_string($sid) . "','".
					mysql_real_escape_string($QuestionText) ."')");

				} else { //answer
					if ($$key != '') {
						$AnwserText = $$key;	
						mysql_select_db($GLOBALS['currentCourseID']);
						$result5 = db_query("INSERT INTO survey_question_answer VALUES ('0','".
							$sqid. "','".
							mysql_real_escape_string($AnwserText) ."')");
					}
				}
			}
	}
	$GLOBALS["tool_content"] .= $GLOBALS["langSurveyCreated"];
}
function printAllQA() {

	global $tool_content, $langQuestion, $langAnswer;

		$counter = 0;
		$CurrentQuestion = 0;
		$CurrentAnswer = 0;
		foreach (array_keys($_POST) as $key) {
			$$key = $_POST[$key];
			++$counter;
			if (($counter >= 5)&&($counter <= (count($_POST)-3) )) { // question or anwser
				if (substr($key, 0, 8) == "question") { //question
					++$CurrentQuestion;
					$tool_content .= "<tr><td colspan=3><hr></td></tr> <tr><td>" . $langQuestion . 
						" </td><td><input type='text' size='70' name='question{$CurrentQuestion}' value='".
						$$key."'></td></tr>\n";
				} else { //answer
					if ($$key != '') {
						++$CurrentAnswer;
						$tool_content .= " <tr><td>" . $langAnswer . 
						" </td><td><input type='text' size='70' name='answer{$CurrentQuestion}.{$CurrentAnswer}' ".
						"value='{$$key}'></td></tr>\n";
					}
				}
			}
	 }
}
?>
