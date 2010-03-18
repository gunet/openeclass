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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';
require_once '../../include/libchart/libchart.php';

$nameTools = $langPollCharts;
$navigation[] = array("url"=>"questionnaire.php", "name"=> $langQuestionnaire);

$tool_content = "";
$total_answers = 0;
$questions = array();

if(!isset($_GET['pid']) || !is_numeric($_GET['pid'])) die();
	$pid = intval($_GET['pid']);
	$current_poll = db_query("SELECT * FROM poll WHERE pid='$pid' ORDER BY pid", $currentCourse);
	$thePoll = mysql_fetch_array($current_poll);


	$tool_content .= "
    <table width=\"99%\" class='FormData'>
    <tbody>
    <tr>
      <th class=\"left\" width=\"220\">&nbsp;</th>
      <td><b>$langSurvey</b></td>
    </tr>
    <tr>
      <th class='left'>$langTitle:</th>
      <td>" . $thePoll["name"] . "</td>
    </tr>
    <tr>
      <th class='left'>$langPollCreation:</th>
      <td>".nice_format(date("Y-m-d", strtotime($thePoll["creation_date"])))."</td>
    </tr>
    <tr>
      <th class='left'>$langPollStart:</th>
      <td>".nice_format(date("Y-m-d", strtotime($thePoll["start_date"])))."</td>
    </tr>
    <tr>
      <th class='left'>$langPollEnd:</th>
      <td>".nice_format(date("Y-m-d", strtotime($thePoll["end_date"])))."</td>
    </tr>
    </tbody>
    </table>
    <br />";


	//$tool_content .= "<div id=\"topic_title_id\">" . $thePoll["name"] . "</div><br><p>";
	//$tool_content .= "$langPollCreateDate: <b>" . $thePoll["creation_date"] . "</b><br><br>";
	//$tool_content .= $langPollStarted . " <b>" . $thePoll["start_date"] . "</b> ";
	//$tool_content .= $langPollEnded. " <b>" . $thePoll["end_date"] . "</b><br><br>";

	$tool_content .= "
    <table width=\"99%\" class='FormData'>
    <tbody>
    <tr>
      <th class='left' width=\"220\">&nbsp;</th>
      <td><b>$langAnswers</b></td>
    </tr>";

	$questions = db_query("SELECT * FROM poll_question WHERE pid=$pid");
	while ($theQuestion = mysql_fetch_array($questions)) {
	$tool_content .= "
    <tr>
      <th class='left' width=\"220\">$langQuestion</th>
      <td>$theQuestion[question_text]</td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td>";

		if ($theQuestion['qtype'] == 'multiple') {
			$answers = db_query("SELECT COUNT(aid) AS count, aid, poll_question_answer.answer_text AS answer
				FROM poll_answer_record LEFT JOIN poll_question_answer
				ON poll_answer_record.aid = poll_question_answer.pqaid
				WHERE qid = $theQuestion[pqid] GROUP BY aid", $currentCourseID);
			$answer_counts = array();
			$answer_text = array();
			$answer_total = 0;
			while ($theAnswer = mysql_fetch_array($answers)) {
				$answer_counts[] = $theAnswer['count'];
				$answer_total += $theAnswer['count'];
				if ($theAnswer['aid'] < 0) {
					$answer_text[] = $langPollUnknown;
				} else {
					$answer_text[] = $theAnswer['answer'];
				}
			}
			$chart = new PieChart(500, 300);
			$chart->setMargin(5);
			$chart->setTitle('');
			foreach ($answer_counts as $i => $count) {
				$percentage = 100 * ($count / $answer_total);
				$label = sprintf("$answer_text[$i] (%2.1f%%)", $percentage);
				$chart->addPoint(new Point($label, $percentage));
			}

			$chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';
			$chart->render($webDir.$chart_path);
			$tool_content .= '<img src="'.$urlServer.$chart_path.'" /><br>';
		} else {
			$answers = db_query("SELECT answer_text, user_id FROM poll_answer_record
					WHERE qid = $theQuestion[pqid]", $currentCourseID);
			$tool_content .= '<dl>';
			while ($theAnswer = mysql_fetch_array($answers)) {
				$tool_content .= "<dt><u>$langUser</u>: <dd>" . uid_to_name($theAnswer['user_id']) . "</dd></dt> <dt><u>$langAnswer</u>: <dd>$theAnswer[answer_text]</dd></dt>";
			}
			$tool_content .= '</dl>';
		}
			$tool_content .= "
	  </td>
	</tr>
	<tr>
      <td colspan=\"2\">&nbsp;</td>
    </tr>
    ";
	}
		$tool_content .= "
    <tr>
      <th class='left'>$langPollTotalAnswers:</th>
      <td><b>$answer_total</b></td>
    </tr>
    </tbody>
    </table>
    <br />";


// display page
draw($tool_content, 2, 'questionnaire');
?>
