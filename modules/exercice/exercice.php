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

// answer types
define('UNIQUE_ANSWER', 1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
$require_current_course = TRUE;

$require_help = TRUE;
$helpTopic = 'Exercise';
$guest_allowed = true;

include '../../include/baseTheme.php';
// support for math symbols
include('../../include/phpmathpublisher/mathpublisher.php');
/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_EXERCISE');

$tool_content = "";
$nameTools = $langExercices;

/*******************************/
/* Clears the exercise session */
/*******************************/
if (isset($_SESSION['objExercise']))  { unset($_SESSION['objExercise']); }
if (isset($_SESSION['objQuestion']))  { unset($_SESSION['objQuestion']); }
if (isset($_SESSION['objAnswer']))  { unset($_SESSION['objAnswer']); }
if (isset($_SESSION['questionList']))  { unset($_SESSION['questionList']); }
if (isset($_SESSION['exerciseResult']))  { unset($_SESSION['exerciseResult']); }

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';

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
if($is_adminOfCourse) {
	if(!empty($choice)) {
		// construction of Exercise
		$objExerciseTmp=new Exercise();
		if($objExerciseTmp->read($exerciseId))
		{
			switch($choice)
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
	$sql="SELECT id, titre, description, type, active FROM `$TBL_EXERCICES` ORDER BY id LIMIT $from, $limitExPage";
	$result = db_query($sql,$currentCourseID);
	$qnum = db_query("SELECT count(*) FROM `$TBL_EXERCICES`");
} else {
        // only for students
	$sql = "SELECT id, titre, description, type, StartDate, EndDate, TimeConstrain, AttemptsAllowed ".
		"FROM `$TBL_EXERCICES` WHERE active='1' ORDER BY id LIMIT $from, $limitExPage";
	$result = db_query($sql);
	$qnum = db_query("SELECT count(*) FROM `$TBL_EXERCICES` WHERE active = 1");
}

list($num_of_ex) = mysql_fetch_array($qnum);
$nbrExercises = mysql_num_rows($result);

if($is_adminOfCourse) {
	$tool_content .= "<div  align=\"left\" id=\"operations_container\"><ul id=\"opslist\">\n";

  $tool_content .= <<<cData
          <li><a href="admin.php?NewExercise=Yes">${langNewEx}</a>&nbsp;|&nbsp;<a href="question_pool.php">${langQuestionPool}</a></li>
cData;

$tool_content .= "</ul></div>";
} else  {
	$tool_content .= "";
}

$maxpage = 1 + intval($num_of_ex / $limitExPage);
if ($maxpage > 0) {
	$prevpage = $page - 1;
	$nextpage = $page + 1;
	if ($prevpage >= 0) {
 		$tool_content .= "<small><a href='$_SERVER[PHP_SELF]?page=$prevpage'>&lt;&lt; $langPreviousPage</a></small>&nbsp;";
	}
	if ($nextpage < $maxpage) { 
		$tool_content .= "<small><a href='$_SERVER[PHP_SELF]?page=$nextpage'>$langNextPage &gt;&gt;</a></small>";
	}
}

$tool_content .= <<<cData

      <table align="left" width="99%" class="ExerciseSum">
      <thead>
      <tr>
cData;

// shows the title bar only for the administrator
if($is_adminOfCourse) {
	$tool_content .= "<th style=\"border: 1px solid #edecdf;\" colspan=\"2\">
	<div align=\"left\">${langExerciseName}</div></th>
	<th width=\"65\" style=\"border: 1px solid #edecdf;\">${langResults}</th>
	<th width=\"65\" style=\"border: 1px solid #edecdf;\" class=\"right\">$langCommands&nbsp;</th>
	</tr>
	</thead>";
} else { // student view
	$tool_content .= "<th style=\"border: 1px solid #edecdf;\" class=\"left\" class=\"left\" colspan=\"2\">
	<div align=\"left\">$langExerciseName</div></th>
	<th style=\"border: 1px solid #edecdf;\">$langExerciseStart</th>
	<th style=\"border: 1px solid #edecdf;\">$langExerciseEnd</th>
	<th style=\"border: 1px solid #edecdf;\">$langExerciseConstrain</th>
	<th style=\"border: 1px solid #edecdf;\">$langExerciseAttemptsAllowed</th>
	</tr>
	</thead>";
}

if(!$nbrExercises) {
	$tool_content .= "
      <tr><td";
	if($is_adminOfCourse)
		$tool_content .= " colspan=\"4\"";
		$tool_content .= " class=\"empty\">${langNoEx}</td>
      </tr>";
}

$tool_content .= "<tbody>";
// while list exercises
$k = 0;
while($row = mysql_fetch_array($result)) {
	if ($k%2 == 0) {
		$tool_content .= "<tr>";
	} else {
		$tool_content .= "<tr class='odd'>";
	}
	// display math symbols (if any)
	$row['description'] = mathfilter($row['description'], 12, "../../courses/mathimg/");

	// prof only
        if($is_adminOfCourse) {
                if (!empty($row['description'])) {
                        $descr = "<br/><small>$row[description]</small>";
                } else {
                        $descr = '';
                }
		if(!$row['active']) {
			$tool_content .= "<td width=\"1\"><img style='padding-top:3px;' src='${urlServer}/template/classic/img/arrow_red.gif' alt='' /></td><td>
			<div class=\"invisible\">
			<a href=\"exercice_submit.php?exerciseId=${row['id']}\">".$row['titre']."</a>$descr</div></td>";
		} else {
			$tool_content .= "<td width=\"1\"><img style='padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' alt='' /></td><td>
			<a href=\"exercice_submit.php?exerciseId=${row['id']}\">".$row['titre']."</a>$descr</td>";
		}

		$eid = $row['id'];
		$NumOfResults = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record 
			WHERE eid='$eid'", $currentCourseID));

	if ($NumOfResults[0]) {
		$tool_content .= "<td align=\"center\"><nobr><a href=\"results.php?exerciseId=".$row['id']."\">".
		$langExerciseScores1."</a> | 
		<a href=\"csv.php?exerciseId=".$row['id']."\" target=_blank>".$langExerciseScores3."</a></nobr></td>";
	} else {
		$tool_content .= "<td align=\"center\">	-&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;- </td>";
	}
	$langModify_temp = htmlspecialchars($langModify);
	$langConfirmYourChoice_temp = addslashes(htmlspecialchars($langConfirmYourChoice));
	$langDelete_temp = htmlspecialchars($langDelete);
	$tool_content .= <<<cData

        <td align="right">
          <a href="admin.php?exerciseId=${row['id']}"><img src="../../template/classic/img/edit.gif" alt="${langModify_temp}" title="${langModify_temp}" /></a>
          <a href="$_SERVER[PHP_SELF]?choice=delete&amp;exerciseId=${row['id']}"  onclick="javascript:if(!confirm('${langConfirmYourChoice_temp}')) return false;"><img src="../../template/classic/img/delete.gif" alt="${langDelete_temp}" title="${langDelete_temp}" /></a>
cData;

	// if active
	if($row['active']) {
		if (isset($page)) {
			$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?choice=disable&amp;page=${page}&amp;exerciseId=".$row['id']."\">"."<img src='../../template/classic/img/visible.gif' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
		} else {
			$tool_content .= "
			<a href='$_SERVER[PHP_SELF]?choice=disable&amp;exerciseId=".$row['id']."'>"."<img src='../../template/classic/img/visible.gif' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
		}
	} else { // else if not active
		if (isset($page)) {
			$tool_content .= "
			<a href='$_SERVER[PHP_SELF]?choice=enable&amp;page=${page}&amp;exerciseId=".$row['id']."'>"."<img src='../../template/classic/img/invisible.gif' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
		} else {
			$tool_content .= "
			<a href='$_SERVER[PHP_SELF]?choice=enable&amp;exerciseId=".$row['id']."'>"."<img src='../../template/classic/img/invisible.gif' alt='$langVisible' title='$langVisible' /></a>&nbsp;";
		}
	}
	$tool_content .= "</td></tr>";
}
	// student only
else {
	$CurrentDate = date("Y-m-d");
	$temp_StartDate = mktime(0, 0, 0, substr($row['StartDate'], 5,2), substr($row['StartDate'], 8,2), substr($row['StartDate'], 0,4));
	$temp_EndDate = mktime(0, 0, 0, substr($row['EndDate'], 5,2),substr($row['EndDate'], 8,2),substr($row['EndDate'], 0,4));
	$CurrentDate = mktime(0, 0 , 0,substr($CurrentDate, 5,2), substr($CurrentDate, 8,2),substr($CurrentDate, 0,4));
	if (($CurrentDate >= $temp_StartDate) && ($CurrentDate <= $temp_EndDate)) {
		$tool_content .= "<td width=\"1\"><img style='padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' alt='' /></td>
		<td><a href=\"exercice_submit.php?exerciseId=".$row['id']."\">".$row['titre']."</a>";
	} else {
		$tool_content .= "<td width='1'>
			<img style='padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' alt='' />
			</td><td>".$row['titre']."&nbsp;&nbsp;(<font color=\"red\">$m[expired]</font>)";
	}
	$tool_content .= "<br/><small>$row[description]</small></td>
        <td align='center'><small>".nice_format($row['StartDate'])."</small></td>
        <td align='center'><small>".nice_format($row['EndDate'])."</small></td>";
	// how many attempts we have.
	$CurrentAttempt = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record
		WHERE eid='$row[id]' AND uid='$uid'", $currentCourseID));
	 if ($row['TimeConstrain'] > 0) {
		  $tool_content .= "<td align='center'>
		<small>$row[TimeConstrain] $langExerciseConstrainUnit</small></td>";
	} else {
		$tool_content .= "<td align='center'><small> - </small></td>";
	}
	if ($row['AttemptsAllowed'] > 0) {
		   $tool_content .= "<td align='center'><small>$CurrentAttempt[0]/$row[AttemptsAllowed]</small></td>";
	} else {
		 $tool_content .= "<td align='center'><small> - </small></td>";
	}
	  $tool_content .= "</tr>";
}
	// skips the last exercise, that is only used to know if we have or not to create a link "Next page"
	if ($k+1 == $limitExPage) {
		break;
	}
$k++;
}	// end while()

$tool_content .= "</tbody></table>";
add_units_navigation(TRUE);
draw($tool_content, 2, 'exercice');
?>
