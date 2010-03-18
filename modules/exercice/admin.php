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

// disable notices due to some problems
//error_reporting('E_ALL ^ E_NOTICE');

// answer types
define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('exercise.lib.php');

$require_current_course = TRUE;
include '../../include/baseTheme.php';

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

include '../../include/jscalendar/calendar.php';

if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

$local_head .= "
<script language=\"JavaScript\">
function validate() {
	if (document.forms[0].intitule.value==\"\") {
   		alert(\"$langAlertTitle\"); 
   		return false;
 	}
 	if (document.forms[0].titulaires.value==\"\") {
   		alert(\"$langAlertAdmin\"); 
   		return false;
 	}
 	return true;
}
</script>
";

$tool_content = "";
$nameTools = $langExercices;
$navigation[]= array ("url"=>"exercice.php", "name"=> $langExercices);
// picture path
$picturePath='../../courses/'.$currentCourseID.'/image';
// the 4 types of answers
$aType=array($langUniqueSelect,$langMultipleSelect,$langFillBlanks,$langMatching);

// tables used in the exercise tool
$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

if(!$is_adminOfCourse) {
	$tool_content .= $langNotAllowed;
	draw($tool_content, 2, 'exercice', $local_head, '');
	exit();
}

/****************************/
/*  stripslashes POST data  */
/****************************/
if($REQUEST_METHOD == 'POST') {
	foreach($_POST as $key=>$val) {
		if(is_string($val)) {
			$_POST[$key]=stripslashes($val);
		} elseif(is_array($val)) {
			foreach($val as $key2=>$val2) {
				$_POST[$key][$key2]=stripslashes($val2);
			}
		}
		$GLOBALS[$key]=$_POST[$key];
	}
}

// intializes the Exercise object
if(@(!is_object($objExercise))) {
	// construction of the Exercise object
	$objExercise=new Exercise();
	// creation of a new exercise if wrong or not specified exercise ID
	if(isset($exerciseId)) {
		$objExercise->read($exerciseId);
	}
	// saves the object into the session
	$_SESSION['objExercise'] = $objExercise; 
}

// doesn't select the exercise ID if we come from the question pool
if(!isset($fromExercise)) {
	// gets the right exercise ID, and if 0 creates a new exercise
	if(!$exerciseId=$objExercise->selectId()) {
		$modifyExercise='yes';
	}
}

$nbrQuestions=$objExercise->selectNbrQuestions();

// intializes the Question object
if(isset($editQuestion) || isset($newQuestion) || isset($modifyQuestion) || isset($modifyAnswers)) {
	if(isset($editQuestion) || isset($newQuestion)) {
		// construction of the Question object
		$objQuestion=new Question();
		// saves the object into the session
		$_SESSION['objQuestion'] = $objQuestion;
		// reads question data
		if(isset($editQuestion)) {
			// question not found
			if(!$objQuestion->read($editQuestion)) {
				$tool_content .= $langQuestionNotFound;
				draw($tool_content, 2, 'exercice', $local_head, '');
				exit();
			}
		}
	}
	// checks if the object exists
	if(is_object($objQuestion)) {
		// gets the question ID
		$questionId=$objQuestion->selectId();
	} else { // question not found
		$tool_content .= $langQuestionNotFound;
		draw($tool_content, 2, 'exercice', $local_head, '');
		exit();
	}
}

// if cancelling an exercise
if(isset($cancelExercise)) {
	// existing exercise
	if($exerciseId) {
		unset($modifyExercise);
	}
	// new exercise
	else {
		// goes back to the exercise list
		header('Location: exercice.php');
		exit();
	}
}

// if cancelling question creation/modification
if(isset($cancelQuestion)) {
	// if we are creating a new question from the question pool
	if(!$exerciseId && !$questionId) {
		// goes back to the question pool
		header('Location: question_pool.php');
		exit();
	} else {
		// goes back to the question viewing
		$editQuestion=$modifyQuestion;
		unset($newQuestion,$modifyQuestion);
	}
}

// if cancelling answer creation/modification
if(isset($cancelAnswers)) {
	// goes back to the question viewing
	$editQuestion=$modifyAnswers;
	unset($modifyAnswers);
}

// modifies the query string that is used in the link of tool name
if(isset($editQuestion) || isset($modifyQuestion) || isset($modifyAnswers)) {
	$nameTools=$langQuestionManagement;
	$navigation[]= array ("url" => "admin.php?exerciseId=$exerciseId", "name" => $langExerciseManagement);
	@$QUERY_STRING=$questionId?'editQuestion='.$questionId.'&fromExercise='.$fromExercise:'newQuestion=yes';
} elseif(isset($newQuestion)) {
	$nameTools=$langNewQu;
	$navigation[]= array ("url" => "admin.php?exerciseId=$exerciseId", "name" => $langExerciseManagement);
	@$QUERY_STRING=$questionId?'editQuestion='.$questionId.'&fromExercise='.$fromExercise:'newQuestion=yes';
} elseif(isset($NewExercise)) {
	$nameTools=$langNewEx;
	$QUERY_STRING='';
} elseif(isset($modifyExercise)) {
	$nameTools=$langInfoExercise;
	$navigation[]= array ("url" => "admin.php?exerciseId=$exerciseId", "name" => $langExerciseManagement);
	$QUERY_STRING='';
} else {
	$nameTools=$langExerciseManagement;
	$QUERY_STRING='';
}

// if the question is duplicated, disable the link of tool name
if(isset($modifyIn) and $modifyIn == 'thisExercise') {
	if ($buttonBack) {
		$modifyIn='allExercises';
	} else {
		$noPHP_SELF=true;
	}
}

if(isset($newQuestion) || isset($modifyQuestion)) {
	// statement management
	include('statement_admin.inc.php');
}

if(isset($modifyAnswers)) {
	// answer management
	include('answer_admin.inc.php');
}

if(isset($editQuestion) || isset($usedInSeveralExercises)) {
	// question management
	include('question_admin.inc.php');
}

if(!isset($newQuestion) && !isset($modifyQuestion) && !isset($editQuestion) && !isset($modifyAnswers)) {
	// exercise management
	include('exercise_admin.inc.php');

	if(!isset($modifyExercise)) {
		// question list management
		include('question_list_admin.inc.php');
	}
}
draw($tool_content, 2, 'exercice', $local_head, '');

// -----------------------------------------------
// function for displaying jscalendar
// -----------------------------------------------
function jscal_html($name, $u_date) {
	
	global $jscalendar;
	if (!$u_date) {
		$u_date = strftime('%Y-%m-%d', strtotime('now -0 day'));
	}

	$cal = $jscalendar->make_input_field(
 	   array('showsTime' => false,
                 'showOthers' => true,
                 'ifFormat' => '%Y-%m-%d'),
       array('style' => 'width: 15em; color: #840; background-color: #fff; border: 1px dotted #000; text-align: center',
                 'name'  => $name,
                 'value' => $u_date));
	
	return $cal;
}
?>
