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
define('UNIQUE_ANSWER', 1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);
define('TRUE_FALSE', 5);

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
$require_current_course = TRUE;

$require_help = TRUE;
$helpTopic = 'Exercise';
$guest_allowed = true;

include '../../include/baseTheme.php';

require_once '../video/video_functions.php';
load_modal_box();

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record(MODULE_ID_EXERCISE);

$nameTools = $langExercices;

/*******************************/
/* Clears the exercise session */
/*******************************/
if (isset($_SESSION['objExercise']))    { unset($_SESSION['objExercise']); }
if (isset($_SESSION['objQuestion']))    { unset($_SESSION['objQuestion']); }
if (isset($_SESSION['objAnswer']))      { unset($_SESSION['objAnswer']); }
if (isset($_SESSION['questionList']))   { unset($_SESSION['questionList']); }
if (isset($_SESSION['exerciseResult'])) { unset($_SESSION['exerciseResult']); }

$TBL_EXERCISE_QUESTION = 'exercise_question';
$TBL_EXERCISE = 'exercise';
$TBL_QUESTION = 'question';

// maximum number of exercises on a same page
$limitExPage = 15;
if (isset($_GET['page'])) {
	$page = intval($_GET['page']);
} else {
	$page = 0;
}
// selects $limitExPage exercises at the same time
$from = $page * $limitExPage;

// only for administrator
if($is_editor) {
	// delete confirmation
	$head_content .= '
	<script type="text/javascript">
	function confirmation ()
	{
	    if (confirm("'.$langConfirmDelete.'"))
		{return true;}
	    else
		{return false;}
	}
	</script>';

	if (isset($_GET['exerciseId'])) {
		$exerciseId = $_GET['exerciseId'];
	}
	if(!empty($_GET['choice'])) {
		// construction of Exercise
		$objExerciseTmp = new Exercise();
		if($objExerciseTmp->read($exerciseId))
		{
			switch($_GET['choice'])
			{
				case 'delete':	// deletes an exercise
					$objExerciseTmp->delete();
					break;
				case 'enable':  // enables an exercise
					$objExerciseTmp->enable();
					$objExerciseTmp->save();
					break;
				case 'disable': // disables an exercise
					$objExerciseTmp->disable();
					$objExerciseTmp->save();
					break;
			}
		}
		// destruction of Exercise
		unset($objExerciseTmp);
	}
	$sql = "SELECT id, title, description, type, active FROM `$TBL_EXERCISE` WHERE course_id = $cours_id ORDER BY id LIMIT $from, $limitExPage";
	$result = db_query($sql, $mysqlMainDb);
	$qnum = db_query("SELECT count(*) FROM `$TBL_EXERCISE` WHERE course_id = $cours_id");
} else {
    // only for students
	$sql = "SELECT id, title, description, type, start_date, end_date, time_constraint, attempts_allowed ".
		"FROM `$TBL_EXERCISE` WHERE course_id = $cours_id AND active = '1' ORDER BY id LIMIT $from, $limitExPage";
	$result = db_query($sql, $mysqlMainDb);
	$qnum = db_query("SELECT count(*) FROM `$TBL_EXERCISE` WHERE course_id = $cours_id AND active = 1", $mysqlMainDb);
}

list($num_of_ex) = mysql_fetch_array($qnum);
$nbrExercises = mysql_num_rows($result);

if($is_editor) {
	$tool_content .= "
    <div align=\"left\" id=\"operations_container\">
      <ul id=\"opslist\">
	<li><a href='admin.php?course=$code_cours&amp;NewExercise=Yes'>$langNewEx</a>&nbsp;|
			&nbsp;<a href='question_pool.php?course=$code_cours'>$langQuestionPool</a></li>";
	$tool_content .= "
      </ul>
    </div>";
} else  {
	$tool_content .= "";
}

if(!$nbrExercises) {
    $tool_content .= "<p class='alert1'>$langNoEx</p>";
} else {
	$maxpage = 1 + intval($num_of_ex / $limitExPage);
	if ($maxpage > 0) {
		$prevpage = $page - 1;
		$nextpage = $page + 1;
		if ($prevpage >= 0) {
			$tool_content .= "<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;page=$prevpage'>&lt;&lt; $langPreviousPage</a>&nbsp;";
		}
		if ($nextpage < $maxpage) { 
			$tool_content .= "<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;page=$nextpage'>$langNextPage &gt;&gt;</a>";
		}
	}

	$tool_content .= "
	    <table width='100%' class='tbl_alt'>
	    <tr>";
	
	// shows the title bar only for the administrator
	if($is_editor) {
		$tool_content .= "
	      <th colspan='2'><div class='left'>$langExerciseName</div></th>
	      <th width='65'>${langResults}</th>
	      <th width='65' class='right'>$langCommands&nbsp;</th>
	    </tr>";
	} else { // student view
		$tool_content .= "
	      <th colspan='2'>$langExerciseName</th>
	      <th width='110' class='center'>$langExerciseStart / $langExerciseEnd</th>
	      <th width='70' class='center'>$langExerciseConstrain</th>
	      <th width='70' class='center'>$langExerciseAttemptsAllowed</th>
              <th width='70' class='center'>$langResults</th>
	    </tr>";
	}
	$tool_content .= "<tbody>";
	// while list exercises
	$k = 0;
	while($row = mysql_fetch_array($result)) {
		if($is_editor) {
			if(!$row['active']) {
				$tool_content .= "<tr class='invisible'>";
			} else {
				if ($k%2 == 0) {
					$tool_content .= "<tr class='even'>";
				} else {
					$tool_content .= "<tr class='odd'>";
				}
			}
		} else {
			if ($k%2 == 0) {
				$tool_content .= "<tr class='even'>";
			} else {
				$tool_content .= "<tr class='odd'>";
			}
		}
		
		$row['description'] = standard_text_escape($row['description']);
	
		// prof only
		if($is_editor) {
			if (!empty($row['description'])) {
				$descr = "<br/>$row[description]";
			} else {
				$descr = '';
			}
			$tool_content .= "<td width='16'>
				<img src='$themeimg/arrow.png' alt='' /></td>
				<td><a href=\"exercise_submit.php?course=$code_cours&amp;exerciseId=${row['id']}\">".q($row['title'])."</a>$descr</td>";
			$eid = $row['id'];
			$NumOfResults = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record 
                                                WHERE eid = '$eid'", $mysqlMainDb));
	
			if ($NumOfResults[0]) {
				$tool_content .= "<td align='center'><a href=\"results.php?course=$code_cours&amp;exerciseId=".$row['id']."\">".
				$langExerciseScores1."</a> | 
				<a href=\"csv.php?course=$code_cours&amp;exerciseId=".$row['id']."\" target=_blank>".$langExerciseScores3."</a></td>";
			} else {
				$tool_content .= "<td align='center'>	-&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;- </td>";
			}
			$langModify_temp = htmlspecialchars($langModify);
			$langConfirmYourChoice_temp = addslashes(htmlspecialchars($langConfirmYourChoice));
			$langDelete_temp = htmlspecialchars($langDelete);
			$tool_content .= "<td align = 'right'>
			  <a href='admin.php?course=$code_cours&amp;exerciseId=$row[id]'><img src='$themeimg/edit.png' alt='$langModify_temp' title='$langModify_temp' />
			  </a>
				<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;choice=delete&amp;exerciseId=$row[id]' onClick='return confirmation();'>          
			  <img src='$themeimg/delete.png' alt='$langDelete_temp' title='$langDelete_temp' />
			  </a>";
		
			// if active
			if($row['active']) {
				if (isset($page)) {
					$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?course=$code_cours&amp;choice=disable&amp;page=${page}&amp;exerciseId=".$row['id']."\">
					<img src='$themeimg/visible.png' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
				} else {
					$tool_content .= "<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;choice=disable&amp;exerciseId=".$row['id']."'>
					<img src='$themeimg/visible.png' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
				}
			} else { // else if not active
				if (isset($page)) {
					$tool_content .= "<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;choice=enable&amp;page=${page}&amp;exerciseId=".$row['id']."'>
					<img src='$themeimg/invisible.png' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
				} else {
					$tool_content .= "<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;choice=enable&amp;exerciseId=".$row['id']."'>
					<img src='$themeimg/invisible.png' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
				}
			}
			$tool_content .= "</td></tr>";
		}
		// student only
		else {
                        $currentDate = date("Y-m-d H:i");
                        $temp_StartDate = mktime(substr($row['start_date'], 11, 2), substr($row['start_date'], 14, 2), 0, substr($row['start_date'], 5,2), substr($row['start_date'], 8,2), substr($row['start_date'], 0,4));
                        $temp_EndDate   = mktime(substr($row['end_date'], 11, 2), substr($row['end_date'], 14, 2), 0, substr($row['end_date'], 5,2),   substr($row['end_date'], 8,2),   substr($row['end_date'], 0,4));
                        $currentDate    = mktime(substr($currentDate, 11, 2), substr($currentDate, 14, 2), 0, substr($currentDate, 5,2),       substr($currentDate, 8,2),       substr($currentDate, 0,4));
                        if (($currentDate >= $temp_StartDate) && ($currentDate <= $temp_EndDate)) {
                                $tool_content .= "<td width='16'><img src='$themeimg/arrow.png' alt='' /></td>
                                        <td><a href='exercise_submit.php?course=$code_cours&amp;exerciseId=".$row['id']."'>".$row['title']."</a>";
                        } elseif ($currentDate <= $temp_StartDate) { // exercise has not yet started
                                $tool_content .= "<td width='16'><img src='$themeimg/arrow.png' alt='' /></td>
                                        <td class='invisible'>".$row['title']."&nbsp;&nbsp;";
                        } else { // exercise has expired
                                $tool_content .= "<td width='16'>
                                <img src='$themeimg/arrow.png' alt='' />
                                </td><td>".$row['title']."&nbsp;&nbsp;(<font color='red'>$m[expired]</font>)";
                        }
                        $tool_content .= "<br />$row[description]</td><td class='smaller' align='center'>
                                ".nice_format(date("Y-m-d H:i", strtotime($row['start_date'])), true)." / 
                                ".nice_format(date("Y-m-d H:i", strtotime($row['end_date'])), true)."</td>";
                        // how many attempts we have.
                        $currentAttempt = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record
                                                                      WHERE eid = '$row[id]' AND uid = '$uid'", $mysqlMainDb));
                        if ($row['time_constraint'] > 0) {
                                $tool_content .= "<td align='center'>
                                $row[time_constraint] $langExerciseConstrainUnit</td>";
                        } else {
                                $tool_content .= "<td align='center'> - </td>";
                        }
                        if ($row['attempts_allowed'] > 0) {
                                $tool_content .= "<td align='center'>$currentAttempt[0]/$row[attempts_allowed]</td>";
                        } else {
                                $tool_content .= "<td align='center'> - </td>";
                        }
                        // user last exercise score
                        $r = mysql_fetch_array(db_query("SELECT total_score, total_weighting 
                                        FROM exercise_user_record WHERE uid=$uid 
                                        AND eid=$row[id] 
                                        ORDER BY eurid DESC LIMIT 1", $mysqlMainDb));
                        if (empty($r['total_score'])) {
                                $tool_content .= "<td align='center'>&dash;</td>";
                        } else {
                                $tool_content .= "<td align='center'>$r[total_score]/$r[total_weighting]</td>";
                        }
                        $tool_content .= "</tr>";
		}
		// skips the last exercise, that is only used to know if we have or not to create a link "Next page"
		if ($k+1 == $limitExPage) {
			break;
		}
		$k++;
	}	// end while()
	$tool_content .= "</table>";
}
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);

