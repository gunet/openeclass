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
	questionnaire.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================
        @Description: Main script for the questionnaire tool
==============================================================================
*/

$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';
include '../../include/baseTheme.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_QUESTIONNAIRE');
/**************************************/

$nameTools = $langQuestionnaire;
$tool_content = "";

$head_content = '
<script>
function confirmation ()
{
    if (confirm("'.$langConfirmDelete.'"))
        {return true;}
    else
        {return false;}
}
</script>
';

if (isset($visibility)) {
		switch ($visibility) {

// activate / dectivate surveys
/*
		case 'sactivate':
		$sql = "UPDATE survey SET active='1' WHERE sid='".mysql_real_escape_string($_GET['sid'])."'";
		$result = db_query($sql,$currentCourseID);
		$GLOBALS["tool_content"] .= $GLOBALS["langSurveyActivated"];
			break;
		case 'sdeactivate':
			$sql = "UPDATE survey SET active='0' WHERE sid='".mysql_real_escape_string($_GET['sid'])."'";
				$result = db_query($sql,$currentCourseID);
				$GLOBALS["tool_content"] .= $GLOBALS["langSurveyDeactivated"];
			break;
*/
// activate / dectivate polls
		case 'activate':
			$sql = "UPDATE poll SET active='1' WHERE pid='".mysql_real_escape_string($_GET['pid'])."'";
			$result = db_query($sql,$currentCourseID);
			$GLOBALS["tool_content"] .= "".$GLOBALS["langPollActivated"]."<br>";
			break;
		case 'deactivate':
			$sql = "UPDATE poll SET active='0' WHERE pid='".mysql_real_escape_string($_GET['pid'])."'";
			$result = db_query($sql, $currentCourseID);
			$GLOBALS["tool_content"] .= "".$GLOBALS["langPollDeactivated"]."<br>";
			break;
		}
}


// delete polls
if (isset($delete) and $delete='yes')  {
	$pid = intval($_GET['pid']);
	db_query("DELETE FROM poll_question_answer WHERE pqid IN
		(SELECT pqid FROM poll_question WHERE pid=$pid)");
	db_query("DELETE FROM poll WHERE pid=$pid");
	db_query("DELETE FROM poll_question WHERE pid='$pid'");
	db_query("DELETE FROM poll_answer_record WHERE pid='$pid'");
        $GLOBALS["tool_content"] .= "".$GLOBALS["langPollDeleted"]."";
	draw($tool_content, 2, 'questionnaire', $head_content);
	exit();
}

    if ($is_adminOfCourse) {
	$tool_content .= "
    <div id=\"operations_container\">
      <ul id=\"opslist\">
        <li><a href='addpoll.php'>$langCreatePoll</a></li>
      </ul>
    </div>
    ";
    }

/*
// delete surveys
if (isset($sdelete) and $sdelete='yes') {
				db_query("DELETE FROM survey WHERE sid=".mysql_real_escape_string($_GET['sid']));
        $sd = mysql_fetch_array(db_query("SELECT aid FROM survey_answer
                                        WHERE sid=".mysql_real_escape_string($_GET['sid'])));
        db_query("DELETE FROM survey_answer_record WHERE aid='$sd[aid]'");
        db_query("DELETE FROM survey_answer WHERE sid=".mysql_real_escape_string($_GET['sid']));
        $sd = mysql_fetch_array(db_query("SELECT sqid FROM survey_question
                                        WHERE sid=".mysql_real_escape_string($_GET['sid'])));
        db_query("DELETE FROM survey_question_answer WHERE sqid='$sd[sqid]'");
        db_query("DELETE FROM survey_question WHERE sid=".mysql_real_escape_string($_GET['sid']));

       $GLOBALS["tool_content"] .= "<p>".$GLOBALS["langSurveyDeleted"]."</p>";
       draw($tool_content, 2, 'questionnaire', $head_content);
			 exit();
}
*/
//$tool_content .= "<p><b>$langNamesSurvey</b></p><br>";
//printSurveys();


printPolls();
add_units_navigation(TRUE);
draw($tool_content, 2, 'questionnaire', $head_content);

 /***************************************************************************************************
 * printSurveys()
 ****************************************************************************************************/

/* Apenergopoihsame ta Surveys
	function printSurveys() {
 		global $tool_content, $currentCourse, $langSurveyNone,
		$langYes, $langCreateSurvey, $langTitle, $langSurveyCreator,
		$langSurveyStart, $langSurveyEnd, $langType, $langCreate,
		$langSurveyOperations, $is_adminOfCourse, $langSurveysActive, $mysqlMainDb, $langActions,
		$langSurveyMC, $langEdit, $langDelete, $langActivate, $langDeactivate, $langSurveysInactive, $langParticipate,
 			$langHasParticipated, $uid;

		$survey_check = 0;
		$result = mysql_list_tables($currentCourse);
		while ($row = mysql_fetch_row($result)) {
			if ($row[0] == 'survey') {
		 		$result = db_query("select * from survey", $currentCourse);
				$num_rows = mysql_num_rows($result);
				if ($num_rows > 0)
		 			++$survey_check;
			}
		}
		if (!$survey_check) {
			$tool_content .= "<p class='alert1'>".$langSurveyNone . "</p><br>";
			if ($is_adminOfCourse)
				$tool_content .= '<a href="addsurvey.php?UseCase=0">'.$langCreateSurvey.'</a><br><br>  ';
			}
		else {
			if ($is_adminOfCourse)
				$tool_content .= '<b><a href="addsurvey.php?UseCase=0">'.$langCreateSurvey.'</a></b><br><br>  ';

			// Print active surveys
			$tool_content .= <<<cData
				<table border="0" width="99%"><thead><tr>
				<th>$langTitle</th>
				<th>$langSurveyCreator</th>
				<th>$langCreate</th>
				<th>$langSurveyStart</th>
				<th>$langSurveyEnd</th>
				<th>$langType</th>
cData;

				if ($is_adminOfCourse) {
					$tool_content .= "<th colspan='2'>$langActions</th>";
				} else {
					$tool_content .= "<th>$langParticipate</th>";
				}
				$tool_content .= "</tr></thead><tbody>";

			$active_surveys = db_query("select * from survey", $currentCourse);

			while ($theSurvey = mysql_fetch_array($active_surveys)) {

				$visibility = $theSurvey["active"];

				if (($visibility)||($is_adminOfCourse)) {

					if ($visibility) {
						$visibility_css = " ";
						$visibility_gif = "invisible";
						$visibility_func = "sdeactivate";
					} else {
						$visibility_css = " class=\"invisible\"";
						$visibility_gif = "visible";
						$visibility_func = "sactivate";
					}

					$creator_id = $theSurvey["creator_id"];
					$survey_creator = db_query("SELECT nom,prenom FROM user
									WHERE user_id='$creator_id'", $mysqlMainDb);
					$theCreator = mysql_fetch_array($survey_creator);

					$sid = $theSurvey["sid"];
					$answers = db_query("SELECT * FROM survey_answer WHERE sid='$sid'", $currentCourse);
					$countAnswers = mysql_num_rows($answers);

					if ($is_adminOfCourse) {
						$tool_content .= "\n<tr><td><a href=\"surveyresults.php?sid=".
						$sid ."&type=" . $theSurvey["type"]."\">" . $theSurvey["name"] .
						"</a></td>";
					} else {
						$tool_content .= "<tr><td>" . $theSurvey["name"] . "</td>";
					}

					$tool_content .= "<td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["creation_date"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["start_date"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["end_date"] . "</td>";

					if ($theSurvey["type"] == 1) {
						$tool_content .= "<td>" . $langSurveyMC . "</td>";
					} else {
						$tool_content .= "<td>" . $langSurveyFillText . "</td>";
					}
					if ($is_adminOfCourse)   {
						$tool_content .= "<td align=center>".
						"<a href='$_SERVER[PHP_SELF]?sdelete=yes&sid={$sid}' onClick='return confirmation();'>
						<img src='../../template/classic/img/delete.gif' border='0' title='$langDelete'></a>&nbsp;".
						"<a href='$_SERVER[PHP_SELF]?visibility=$visibility_func&sid={$sid}'>
						<img src='../../template/classic/img/".$visibility_gif.".gif' border='0'></a>  ".
							"</td></tr>\n";
					} else {
							$thesid = $theSurvey["sid"];
							$has_participated = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM survey_answer
										WHERE creator_id='$uid' AND sid='$thesid'"));
						if ($has_participated[0] == 0) {
							$tool_content .= "<td align='center'><a href='surveyparticipate.php?UseCase=1&sid=". $sid ."'>";
							$tool_content .= $langYes;
							$tool_content .= "</a></td></tr>";
						} else {
							$tool_content .= "<td>".$langHasParticipated."</td></tr>";
						}
					}
				}
			}
		$tool_content .= "</table><br>";
		}
	}
*/


 /***************************************************************************************************
 * printPolls()
 ****************************************************************************************************/
function printPolls() {
global $tool_content, $currentCourse, $langCreatePoll, $langPollsActive,
	$langTitle, $langPollCreator, $langPollCreation, $langPollStart,
	$langPollEnd, $langPollNone, $is_adminOfCourse,
	$mysqlMainDb, $langEdit, $langDelete, $langActions,
	$langDeactivate, $langPollsInactive, $langPollHasEnded, $langActivate, $langParticipate, $langVisible,
	$user_id, $langHasParticipated, $langHasNotParticipated, $uid, $urlServer;

$poll_check = 0;
$result = db_query("SHOW TABLES FROM $currentCourse", $currentCourse);
while ($row = mysql_fetch_row($result)) {
		if ($row[0] == 'poll') {
	 		$result = db_query("SELECT * FROM poll", $currentCourse);
			$num_rows = mysql_num_rows($result);
			if ($num_rows > 0)
	 			++$poll_check;
		}
}
if (!$poll_check) {
	$tool_content .= "<p class='alert1'>".$langPollNone . "</p><br>";
} else {
	// Print active polls
		$tool_content .= <<<cData

      <table align="left" width="99%" class="Questionnaire">
      <tbody>
      <tr>
        <th class='left' colspan="2" width='35%'>&nbsp;$langTitle</th>
        <th width='25%' class='left'>$langPollCreator</th>
        <th width='10%'>$langPollCreation</th>
        <th width='10%'>$langPollStart</th>
        <th width='10%'>$langPollEnd</th>
cData;

	if ($is_adminOfCourse) {
 		$tool_content .= "\n        <th width=\"10%\">$langActions</th>";
	} else {
		$tool_content .= "\n        <th>$langParticipate</th>";
	}
	$tool_content .= "\n      </tr>";
	$active_polls = db_query("SELECT * FROM poll", $currentCourse);
	$index_aa = 1;
	$k =0;
		while ($thepoll = mysql_fetch_array($active_polls)) {
			$visibility = $thepoll["active"];


		if (($visibility) or ($is_adminOfCourse)) {
			if ($visibility) {
				$visibility_css = "";
				$visibility_gif = "visible";
				$visibility_func = "deactivate";
				$arrow_gif = "arrow_red";
				$k++;
			} else {
				$visibility_css = " class=\"invisible\"";
				$visibility_gif = "invisible";
				$visibility_func = "activate";
				$arrow_gif = "arrow_grey";
				$k++;
			}
			if ($k%2 == 0) {
	           		$tool_content .= "\n      <tr $visibility_css>";
	        	} else {
	           		$tool_content .= "\n      <tr class=\"odd\">";
            		}			

			$temp_CurrentDate = date("Y-m-d");
			$temp_StartDate = $thepoll["start_date"];
			$temp_EndDate = $thepoll["end_date"];
			$temp_StartDate = mktime(0, 0, 0, substr($temp_StartDate, 5,2), substr($temp_StartDate, 8,2),substr($temp_StartDate, 0,4));
			$temp_EndDate = mktime(0, 0, 0, substr($temp_EndDate, 5,2), substr($temp_EndDate, 8,2), substr($temp_EndDate, 0,4));
			$temp_CurrentDate = mktime(0, 0 , 0,substr($temp_CurrentDate, 5,2), substr($temp_CurrentDate, 8,2),substr($temp_CurrentDate, 0,4));

			$creator_id = $thepoll["creator_id"];
			$theCreator = uid_to_name($creator_id);
			$pid = $thepoll["pid"];
			$answers = db_query("SELECT * FROM poll_answer_record WHERE pid='$pid'", $currentCourse);
			$countAnswers = mysql_num_rows($answers);
			$thepid = $thepoll["pid"];
			// check if user has participated
			$has_participated = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM poll_answer_record
					WHERE user_id='$uid' AND pid='$thepid'"));
			// check if poll has ended
			if (($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {
				$poll_ended = 0;
			} else {
				$poll_ended = 1;
			}
			if ($is_adminOfCourse) {
				$tool_content .= "\n        <td width=\"1%\"><img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/$arrow_gif.gif' title='bullet'></td>
        <td><div align=\"left\"><a href='pollresults.php?pid=$pid'>$thepoll[name]</a>";
			} else {
				$tool_content .= "\n        <td width=\"1%\"><img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>
        <td><div align=\"left\">";
				if (($has_participated[0] == 0) and $poll_ended == 0) {
					$tool_content .= "<a href='pollparticipate.php?UseCase=1&pid=$pid'>$thepoll[name]</a>";
				} else {
				$tool_content .= "$thepoll[name]";
				}
			}
			$tool_content .= "</div></td>
        <td>$theCreator</td>";
			$tool_content .= "\n        <td align='center'>".nice_format(date("Y-m-d", strtotime($thepoll["creation_date"])))."</td>";
			$tool_content .= "\n        <td align='center'>".nice_format(date("Y-m-d", strtotime($thepoll["start_date"])))."</td>";
			$tool_content .= "\n        <td align='center'>".nice_format(date("Y-m-d", strtotime($thepoll["end_date"])))."</td>";
			if ($is_adminOfCourse)  {
				$tool_content .= "\n        <td align='center'>
				<a href='addpoll.php?edit=yes&pid=$pid'>
				<img src='../../template/classic/img/edit.gif' title='$langEdit' border='0'></img></a>&nbsp;
				<a href='$_SERVER[PHP_SELF]?delete=yes&pid=$pid' onClick='return confirmation();'>
				<img src='../../template/classic/img/delete.gif' title='$langDelete' border='0'></img></a>&nbsp;
				<a href='$_SERVER[PHP_SELF]?visibility=$visibility_func&pid={$pid}'>
				<img src='../../template/classic/img/".$visibility_gif.".gif' border='0' title=\"".$langVisible."\"></img></a></td>\n      </tr>";
			} else {
				$tool_content .= "\n      <td align='center'>";
				if (($has_participated[0] == 0) and ($poll_ended == 0)) {
					$tool_content .= "$langHasNotParticipated";
				} else {
					if ($poll_ended == 1) {
						$tool_content .= $langPollHasEnded;
					} else {
						$tool_content .= $langHasParticipated;
					}
				}
				$tool_content .= "</td>\n      </tr>";
			}
		}
		$index_aa ++;
		}
		$tool_content .= "\n      </tbody>\n      </table>";
	}
}
?>
