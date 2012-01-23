<?php 
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */



// answer types
define('UNIQUE_ANSWER',   1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS',  3);
define('MATCHING',        4);
define('TRUE_FALSE',      5);

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('exercise.lib.php');

$require_current_course = TRUE;
include '../../include/baseTheme.php';
include '../../include/jscalendar/calendar.php';

if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$head_content = $jscalendar->get_load_files_code();

$nameTools = $langExercices;
$navigation[] = array ('url' => "exercice.php?course=$code_cours", 'name' => $langExercices);

// picture path
$picturePath='../../courses/'.$currentCourseID.'/image';

// the 4 types of answers
$aType = array($langUniqueSelect, $langMultipleSelect, $langFillBlanks, $langMatching, $langTrueFalse);

// tables used in the exercise tool
$TBL_EXERCISE_QUESTION = 'exercise_question';
$TBL_EXERCISE = 'exercise';
$TBL_QUESTION = 'question';
$TBL_ANSWER = 'answer';

if(!$is_editor) {
	$tool_content .= $langNotAllowed;
	draw($tool_content, 2, null, $head_content);
	exit();
}

/****************************/
/*  stripslashes POST data  */
/****************************/
if($_SERVER['REQUEST_METHOD'] == 'POST') {
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

if (isset($_GET['exerciseId'])) {
    $exerciseId = $_GET['exerciseId'];
}
if (isset($_GET['newQuestion'])) {
    $newQuestion = $_GET['newQuestion'];
}
if (isset($_SESSION['objExercise'])) {
    $objExercise = $_SESSION['objExercise'];
}
if (isset($_SESSION['objAnswer'])) {
    $objAnswer = $_SESSION['objAnswer'];
}
// intializes the Exercise object
if(@(!is_object($objExercise))) {
	// construction of the Exercise object
	$objExercise = new Exercise();
	// creation of a new exercise if wrong or not specified exercise ID
	if(isset($exerciseId)) {
	    $objExercise->read($exerciseId);
	}
	// saves the object into the session
	//$_SESSION['objExercise'][$exerciseId] = $objExercise;
	$_SESSION['objExercise'] = $objExercise;
}

// doesn't select the exercise ID if we come from the question pool
if (!isset($fromExercise)) {
	// gets the right exercise ID, and if 0 creates a new exercise
	if (!$exerciseId = $objExercise->selectId()) {
		$modifyExercise='yes';
	}
}

$nbrQuestions = $objExercise->selectNbrQuestions();

// intializes the Question object
if (isset($_GET['editQuestion']) || isset($_GET['newQuestion'])) {
    // construction of the Question object
    $objQuestion=new Question();
    // saves the object into the session
    $_SESSION['objQuestion'][$exerciseId] = $objQuestion;
    // reads question data
    if (isset($_GET['editQuestion'])) {
	    // question not found
	    if(!$objQuestion->read($_GET['editQuestion'])) {
		    $tool_content .= $langQuestionNotFound;
		    draw($tool_content, 2, null, $head_content);
		    exit();
	    }
    }
}
    
if (isset($_SESSION['objQuestion'][$exerciseId])) {
    $objQuestion = $_SESSION['objQuestion'][$exerciseId];
}    
    
if (isset($_GET['modifyQuestion']) || isset($_GET['modifyAnswers'])) {
	// checks if the object exists
	if(is_object($objQuestion)) {
		// gets the question ID
		$questionId=$objQuestion->selectId();
	} else { // question not found
		$tool_content .= $langQuestionNotFound;
		draw($tool_content, 2, null, $head_content);
		exit();
	}
}

// if cancelling an exercise
if(isset($_POST['cancelExercise'])) {
	// existing exercise
	if($exerciseId) {
		unset($_GET['modifyExercise']);
	} else {
		// goes back to the exercise list
		header('Location: exercice.php?course='.$code_cours);
		exit();
	}
}

// if cancelling question creation/modification
if(isset($_POST['cancelQuestion'])) {
	// if we are creating a new question from the question pool
	if(!$exerciseId && !$questionId) {
		// goes back to the question pool
		header('Location: question_pool.php?course='.$code_cours);
		exit();
	} else {
		// goes back to the question viewing
		$editQuestion = $_GET['modifyQuestion'];
		unset($_GET['newQuestion'], $_GET['modifyQuestion']);
	}
}

// if cancelling answer creation/modification
if(isset($_POST['cancelAnswers'])) {
	// goes back to the question viewing
	$editQuestion = $_GET['modifyAnswers'];
	unset($_GET['modifyAnswers']);
}

// modifies the query string that is used in the link of tool name
if(isset($_GET['editQuestion']) || isset($_GET['modifyQuestion']) || isset($_GET['modifyAnswers'])) {
	$nameTools = $langQuestionManagement;
	$navigation[] = array('url' => "admin.php?course=$code_cours&amp;exerciseId=$exerciseId", 'name' => $langExerciseManagement);
	@$QUERY_STRING = $questionId?'editQuestion='.$questionId.'&fromExercise='.$fromExercise:'newQuestion=yes';
} elseif(isset($_GET['newQuestion'])) {
	$nameTools = $langNewQu;
	$navigation[] = array('url' => "admin.php?course=$code_cours&amp;exerciseId=$exerciseId", 'name' => $langExerciseManagement);
	@$QUERY_STRING = $questionId?'editQuestion='.$questionId.'&fromExercise='.$fromExercise:'newQuestion=yes';
} elseif(isset($_GET['NewExercise'])) {
	$nameTools = $langNewEx;
	$QUERY_STRING = '';
} elseif(isset($_GET['modifyExercise'])) {
	$nameTools = $langInfoExercise;
	$navigation[] = array('url' => "admin.php?course=$code_cours&amp;exerciseId=$exerciseId", 'name' => $langExerciseManagement);
	$QUERY_STRING = '';
} else {
	$nameTools = $langExerciseManagement;
	$QUERY_STRING = '';
}


// --------- Various Actions ---------------------------

// if the question is duplicated, disable the link of tool name
if (isset($_POST['modifyIn']) and $_POST['modifyIn'] == 'thisExercise') {
	if (isset($_POST['buttonBack'])) {
		$modifyIn = 'allExercises';
	} else {
		$noPHP_SELF = true;
	}
}
   
if(isset($_GET['newQuestion']) || isset($_GET['modifyQuestion'])) {
	// statement management
	include('statement_admin.inc.php');
}
if(isset($_GET['modifyAnswers'])) {
	// answer management
	include('answer_admin.inc.php');
}

if(isset($_GET['editQuestion']) || isset($usedInSeveralExercises)) {
	// question management
	include('question_admin.inc.php');
}

if(!isset($_GET['newQuestion']) && !isset($_GET['modifyQuestion']) &&
	    !isset($_GET['editQuestion']) && !isset($_GET['modifyAnswers'])) {
	    // exercise management
	include('exercise_admin.inc.php');
	if(!isset($_GET['modifyExercise']) and !isset($_GET['NewExercise'])) {
	    // question list management
	    include('question_list_admin.inc.php');
	}
}
draw($tool_content, 2, null, $head_content);

// -----------------------------------------------
// function for displaying jscalendar
// -----------------------------------------------
function jscal_html($name, $u_date) {
	
	global $jscalendar;
	if (!$u_date) {
		$u_date = strftime('%Y-%m-%d %H:%M', strtotime('now -0 day'));
	}

	$cal = $jscalendar->make_input_field(
 	   array('showsTime' => true,
                 'showOthers' => true,
                 'ifFormat' => '%Y-%m-%d %H:%M'),
       array('style' => 'width: 15em; color: #840; background-color: #fff; border: 1px dotted #000; text-align: center',
                 'name'  => $name,
                 'value' => $u_date));
	
	return $cal;
}
?>
