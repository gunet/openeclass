<?php
/* ========================================================================
 * Open eClass 2.6
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
require_once '../../include/baseTheme.php';
require_once 'functions.php';

/**** The following is added for statistics purposes ***/
require_once '../../include/action.php';
$action = new action();
$action->record('MODULE_ID_QUESTIONNAIRE');
/**************************************/

$nameTools = $langQuestionnaire;

load_js('tools.js');

if ($is_editor) {
    if (isset($_GET['pid']) and is_numeric($_GET['pid'])) {
        $pid = intval($_GET['pid']);
    } else {
        $pid = 0;
    }

    if (isset($_GET['visibility'])) {
        // activate / dectivate polls
        switch ($_GET['visibility']) {
            case 'activate':
                $sql = "UPDATE poll SET active = 1 WHERE pid = $pid";
                $result = db_query($sql, $currentCourseID);
                $tool_content .= "<p class='success'>" . q($langPollActivated) . "</p>";
                break;
            case 'deactivate':
                $sql = "UPDATE poll SET active = 0 WHERE pid = $pid";
                $result = db_query($sql, $currentCourseID);
                $tool_content .= "<p class='success'>" . q($langPollDeactivated) . "</p>";
                break;
        }
    } elseif (isset($_GET['delete']) and $_GET['delete'] == 'yes')  {
        // delete polls
        db_query("DELETE FROM poll_question_answer WHERE pqid IN
                        (SELECT pqid FROM poll_question WHERE pid = $pid)");
        db_query("DELETE FROM poll WHERE pid= $pid ");
        db_query("DELETE FROM poll_question WHERE pid = $pid");
        db_query("DELETE FROM poll_answer_record WHERE pid = $pid");
        $tool_content .= "<p class='success'>".q($langPollDeleted)."</p>";
    } elseif (isset($_GET['purge']) and $_GET['purge'] == 'yes')  {
        // purge poll results
        db_query("DELETE FROM poll_answer_record WHERE pid = $pid");
        $tool_content .= "<p class='success'>".q($langPollPurged)."</p>";
    } elseif (isset($_GET['clone']) and $_GET['clone'] == 'yes') {
        $poll = db_query_get_single_row("SELECT * FROM poll WHERE pid = $pid");
        $questions = db_query("SELECT * FROM poll_question WHERE pid = $pid");
        $poll['name'] .= " ($langCopy2)";
	db_query("INSERT INTO poll
                            SET creator_id = $poll[creator_id],
                                course_id = $poll[course_id],
                                name = " . quote($poll['name']) . ",
                                creation_date = " . quote($poll['creation_date']) .",
                                start_date = " . quote($poll['start_date']) .",
                                end_date = " . quote($poll['end_date']) .",
                                description = " . quote($poll['description']) .",
                                end_message = " . quote($poll['end_message']) .",
                                anonymized = $poll[anonymized],    
                                active = 1");
	$new_pid = mysql_insert_id();
        while ($question = mysql_fetch_array($questions)) {
            db_query("INSERT INTO poll_question
                                       SET pid = $new_pid,
                                           question_text = " . quote($question['question_text']) .",
                                           qtype = $question[qtype]");
            $new_pqid = mysql_insert_id();
            $answers = db_query("SELECT * FROM poll_question_answer WHERE pqid = $question[pqid]");
            while ($answer = mysql_fetch_array($answers)) {
                db_query("INSERT INTO poll_question_answer
                                        SET pqid = $new_pqid,
                                            answer_text = " . quote($answer['answer_text']));
            }
        }        
    }

    $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
            <li><a href='addpoll.php?course=$code_cours'>$langCreatePoll</a></li>
          </ul>
        </div>";
}

printPolls();
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);


/***************************************************************************************************
 * printPolls()
 ****************************************************************************************************/
function printPolls() {
        global $tool_content, $currentCourse, $code_cours, $langCreatePoll,
               $langPollsActive, $langTitle, $langPollCreator, $langPollCreation,
               $langPollStart, $langPollEnd, $langPollNone, $is_editor,
               $mysqlMainDb, $langEdit, $langDelete, $langActions,
               $langDeactivate, $langPollsInactive, $langPollHasEnded, $langActivate, 
               $langParticipate, $langVisible, $user_id, $langHasParticipated,
               $langHasNotParticipated, $uid, $langConfirmDelete, $langPurgeExercises,
               $langConfirmPurgeExercises, $langAnswers, $langSee, $langCreateDuplicate;

        $poll_check = 0;
        $result = db_query("SHOW TABLES FROM `$currentCourse`", $currentCourse);
        while ($row = mysql_fetch_row($result)) {
                if ($row[0] == 'poll') {
                        $result = db_query("SELECT * FROM poll", $currentCourse);
                        $num_rows = mysql_num_rows($result);
                        if ($num_rows > 0)
                                ++$poll_check;
                }
        }
        if (!$poll_check) {
                $tool_content .= "\n    <p class='alert1'>".$langPollNone . "</p><br>";
        } else {
                // Print active polls
                $tool_content .= "
		      <table align='left' width='100%' class='tbl_alt'>
		      <tr>
			<th colspan='2'><div align='left'>&nbsp;$langTitle</div></th>
			<th class='center'>$langPollStart</th>
			<th class='center'>$langPollEnd</th>";
		
                if ($is_editor) {
                    $tool_content .= "<th class='center' width='16'>$langAnswers</th>
                                      <th class='center' width='125'>$langActions</th>";
                } else {
                    $tool_content .= "<th class='center'>$langParticipate</th>";
                }
                $tool_content .= "</tr>";
                $active_polls = db_query("SELECT pid, name, active, start_date, end_date FROM poll ORDER BY end_date DESC", $currentCourse);
                $index_aa = 1;
                $k =0;
                while ($thepoll = mysql_fetch_array($active_polls)) {
                        $visibility = $thepoll["active"];
		
                        if (($visibility) or ($is_editor)) {
                                if ($visibility) {
                                        if ($k%2 == 0) {
                                                $visibility_css = 'class="even"';
                                        } else {
                                                $visibility_css = 'class="odd"';
                                        }
                                        $visibility_gif = 'visible';
                                        $visibility_func = 'deactivate';
                                        $arrow_png = 'arrow';
                                        $k++;
                                } else {
                                        $visibility_css = 'class="invisible"';
                                        $visibility_gif = 'invisible';
                                        $visibility_func = 'activate';
                                        $arrow_png = 'arrow';
                                        $k++;
                                }
                                if ($k%2 == 0) {
                                        $tool_content .= "<tr $visibility_css>";
                                } else {
                                        $tool_content .= "<tr $visibility_css>";
                                }			
                                $temp_CurrentDate = date('Y-m-d H:i');
                                $temp_StartDate = $thepoll['start_date'];
                                $temp_EndDate = $thepoll['end_date'];
                                $temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
                                $temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
                                $temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));
                                $pid = $thepoll['pid'];
                                $countAnswers = db_query_get_single_value("SELECT COUNT(DISTINCT(user_id)) FROM poll_answer_record WHERE pid = $pid", $currentCourse);
                                // check if user has participated
                                $has_participated = db_query_get_single_value("SELECT COUNT(*) FROM poll_answer_record
                                                        WHERE user_id = $uid AND pid = $pid");
                                // check if poll has ended
                                if ($temp_CurrentDate >= $temp_StartDate and $temp_CurrentDate < $temp_EndDate) {
                                        $poll_ended = 0;
                                } else {
                                        $poll_ended = 1;
                                }
                                if ($is_editor) {
                                        $tool_content .= "
                        <td width='16'>" . icon($arrow_png) . "</td>
                        <td><a href='pollresults.php?course=$code_cours&amp;pid=$pid'>$thepoll[name]</a>";
                                } else {
                                        $tool_content .= "
                        <td>" . icon('arrow') . "</td>
                        <td>";
                                        if (($has_participated[0] == 0) and $poll_ended == 0) {
                                            $tool_content .= "<a href='pollparticipate.php?course=$code_cours&amp;UseCase=1&pid=$pid'>" .
                                                q($thepoll['name']) . "</a>";
                                        } else {
                                            $tool_content .= q($thepoll['name']);
                                        }
                                }
                                $tool_content .= "</td>
                        <td class='center'>".nice_format(date("Y-m-d H:i", strtotime($thepoll["start_date"])), true)."</td>";
                                $tool_content .= "
                        <td class='center'>".nice_format(date("Y-m-d H:i", strtotime($thepoll["end_date"])), true)."</td>";
                                if ($is_editor)  {
                                    $tool_content .= "
                                        <td class='center'>$countAnswers</td>
                                        <td class='center'>" .
                                            icon('search', $langSee, "pollparticipate.php?course=$code_cours&amp;UseCase=1&pid=$pid") .
                                            "&nbsp;" . 
                                            icon('edit', $langEdit, "addpoll.php?course=$code_cours&amp;edit=yes&amp;pid=$pid") .
                                            "&nbsp;" . 
                                            icon('clear', $langPurgeExercises,
                                                "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;purge=yes&amp;pid=$pid",
                                                "onClick=\"return confirmation('" . js_escape($langConfirmPurgeExercises) . "');\"") .
                                            "&nbsp;" .
                                            icon('delete', $langDelete,
                                                "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;delete=yes&amp;pid=$pid",
                                                "onClick=\"return confirmation('" . js_escape($langConfirmDelete) . "');\"") .
                                            "&nbsp;" .
                                            icon('duplicate', $langCreateDuplicate,
                                                "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;clone=yes&amp;pid={$pid}") .
                                            "&nbsp;" .               
                                            icon($visibility_gif, $langVisible,
                                                "$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;visibility=$visibility_func&amp;pid={$pid}") .                                          
                                            "</td></tr>";
                                } else {
                                        $tool_content .= "
                        <td class='center'>";
                                        if (($has_participated[0] == 0) and ($poll_ended == 0)) {
                                                $tool_content .= "$langHasNotParticipated";
                                        } else {
                                                if ($poll_ended == 1) {
                                                        $tool_content .= $langPollHasEnded;
                                                } else {
                                                        $tool_content .= $langHasParticipated;
                                                }
                                        }
                                        $tool_content .= "</td></tr>";
                                }
                        }
                        $index_aa ++;
                }
                $tool_content .= "</table>";
        }
}
