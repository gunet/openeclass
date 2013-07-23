<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
/**
 * @file index.php
 * @brief main exercise module script
 */

$TBL_EXERCISE_QUESTION = 'exercise_with_questions';
$TBL_EXERCISE = 'exercise';
$TBL_QUESTION = 'exercise_question';

require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';


$require_current_course = TRUE;

$require_help = TRUE;
$helpTopic = 'Exercise';
$guest_allowed = true;

include '../../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/search/exerciseindexer.class.php';
ModalBoxHelper::loadModalBox();
/**** The following is added for statistics purposes ***/
require_once 'include/action.php';
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
        load_js('tools.js');
        
	if (isset($_GET['exerciseId'])) {
		$exerciseId = $_GET['exerciseId'];
	}
	if(!empty($_GET['choice'])) {
		// construction of Exercise
		$objExerciseTmp = new Exercise();
		if($objExerciseTmp->read($exerciseId))
		{
                        $eidx = new ExerciseIndexer();
			switch($_GET['choice'])
			{
				case 'delete':	// deletes an exercise
					$objExerciseTmp->delete();
                                        $eidx->remove($exerciseId);
					break;
                                case 'purge':	// purge exercise results
					$objExerciseTmp->purge();
					break;
				case 'enable':  // enables an exercise
					$objExerciseTmp->enable();
					$objExerciseTmp->save();
                                        $eidx->store($exerciseId);
					break;
				case 'disable': // disables an exercise
					$objExerciseTmp->disable();
					$objExerciseTmp->save();
                                        $eidx->store($exerciseId);
					break;
			}
		}
		// destruction of Exercise
		unset($objExerciseTmp);
	}
	$sql = "SELECT id, title, description, type, active FROM `$TBL_EXERCISE` WHERE course_id = $course_id ORDER BY id LIMIT $from, $limitExPage";
	$result = db_query($sql);
	$qnum = db_query("SELECT COUNT(*) FROM `$TBL_EXERCISE` WHERE course_id = $course_id");
} else {
    // only for students
	$sql = "SELECT id, title, description, type, start_date, end_date, time_constraint, attempts_allowed ".
		"FROM `$TBL_EXERCISE` WHERE course_id = $course_id AND active = '1' ORDER BY id LIMIT $from, $limitExPage";
	$result = db_query($sql);
	$qnum = db_query("SELECT COUNT(*) FROM `$TBL_EXERCISE` WHERE course_id = $course_id AND active = 1");
}

list($num_of_ex) = mysql_fetch_array($qnum);
$nbrExercises = mysql_num_rows($result);

if($is_editor) {
	$tool_content .= "<div align='left' id='operations_container'>
        <ul id='opslist'>
	<li><a href='admin.php?course=$course_code&amp;NewExercise=Yes'>$langNewEx</a>&nbsp;|
			&nbsp;<a href='question_pool.php?course=$course_code'>$langQuestionPool</a></li>";
	$tool_content .= "</ul></div>";
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
			$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$prevpage'>&lt;&lt; $langPreviousPage</a>&nbsp;";
		}
		if ($nextpage < $maxpage) {
			$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;page=$nextpage'>$langNextPage &gt;&gt;</a>";
		}
	}

	$tool_content .= "<table width='100%' class='tbl_alt'><tr>";

	// shows the title bar only for the administrator
	if($is_editor) {
		$tool_content .= "
	      <th colspan='2'><div class='left'>$langExerciseName</div></th>
	      <th class='center'>$langResults</th>
	      <th width='85' class='center'>$langCommands&nbsp;</th>
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
				<td><a href='exercise_submit.php?course=$course_code&amp;exerciseId=${row['id']}'>".q($row['title'])."</a>$descr</td>";
			$eid = $row['id'];
			$NumOfResults = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record
                                                WHERE eid = '$eid'", $mysqlMainDb));

			if ($NumOfResults[0]) {
				$tool_content .= "<td align='center'><a href='results.php?course=$course_code&amp;exerciseId=".$row['id']."'>".
				$langExerciseScores1."</a> |
				<a href='csv.php?course=$course_code&amp;exerciseId=".$row['id']."' target=_blank>".$langExerciseScores3."</a></td>";
			} else {
				$tool_content .= "<td align='center'>	-&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;- </td>";
			}
			$langModify_temp = htmlspecialchars($langModify);
			$langConfirmYourChoice_temp = addslashes(htmlspecialchars($langConfirmYourChoice));
			$langDelete_temp = htmlspecialchars($langDelete);
			$tool_content .= "<td align = 'right'>
                                  <a href='admin.php?course=$course_code&amp;exerciseId=$row[id]'>
                                        <img src='$themeimg/edit.png' alt='$langModify_temp' title='$langModify_temp' />
                                  </a>
                                  <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=delete&amp;exerciseId=$row[id]' onClick=\"return confirmation('$langConfirmDelete');\">
                                        <img src='$themeimg/delete.png' alt='$langDelete_temp' title='$langDelete_temp' />
                                 </a>
                                  <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=purge&amp;exerciseId=$row[id]' onClick=\"return confirmation('$langConfirmPurgeExercises');\">
                                        <img src='$themeimg/clear.png' alt='".q($langPurgeExercises)."' title='".q($langPurgeExercises)."' />
                                  </a>";

			// if active
			if($row['active']) {
				if (isset($page)) {
					$tool_content .= "<a href=\"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=disable&amp;page=${page}&amp;exerciseId=".$row['id']."\">
					<img src='$themeimg/visible.png' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
				} else {
					$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=disable&amp;exerciseId=".$row['id']."'>
					<img src='$themeimg/visible.png' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
				}
			} else { // else if not active
				if (isset($page)) {
					$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=enable&amp;page=${page}&amp;exerciseId=".$row['id']."'>
					<img src='$themeimg/invisible.png' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
				} else {
					$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=enable&amp;exerciseId=".$row['id']."'>
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
                                        <td><a href='exercise_submit.php?course=$course_code&amp;exerciseId=".$row['id']."'>". q($row['title']) ."</a>";
                        } elseif ($currentDate <= $temp_StartDate) { // exercise has not yet started
                                $tool_content .= "<td width='16'><img src='$themeimg/arrow.png' alt='' /></td>
                                        <td class='invisible'>". q($row['title']) ."&nbsp;&nbsp;";
                        } else { // exercise has expired
                                $tool_content .= "<td width='16'>
                                <img src='$themeimg/arrow.png' alt='' />
                                </td><td>". q($row['title']) ."&nbsp;&nbsp;(<font color='red'>$m[expired]</font>)";
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