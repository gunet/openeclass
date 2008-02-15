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
	$tool_content .= "<div id=\"topic_title_id\">" . $thePoll["name"] . "</div><br><p>";
	$tool_content .= "$langPollCreateDate: <b>" . $thePoll["creation_date"] . "</b><br><br>";
	$tool_content .= $langPollStarted . " <b>" . $thePoll["start_date"] . "</b> ";
	$tool_content .= $langPollEnded. " <b>" . $thePoll["end_date"] . "</b><br><br>";

	$questions = db_query("SELECT * FROM poll_question WHERE pid=$pid");
	while ($theQuestion = mysql_fetch_array($questions)) {
		$tool_content .= "<h3>$theQuestion[question_text]</h3>";
		if ($theQuestion['qtype'] == 'multiple') {
			$answers = db_query("SELECT COUNT(aid) AS count, poll_question_answer.answer_text AS answer 
					FROM poll_answer_record LEFT JOIN poll_question_answer 
					ON poll_answer_record.aid = poll_question_answer.pqaid 
					WHERE qid = $theQuestion[pqid] GROUP BY aid", $currentCourseID);
			$answer_counts = array();
			$answer_text = array();
			$answer_total = 0;
			while ($theAnswer = mysql_fetch_array($answers)) {
				$answer_counts[] = $theAnswer['count'];
				$answer_total += $theAnswer['count'];
				$answer_text[] = $theAnswer['answer'];
			}
			$chart = new PieChart(600, 300);
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
				$tool_content .= "<dt>" . uid_to_name($theAnswer['user_id']) . ":</dt> <dd>$theAnswer[answer_text]</dd>"; 
			}
			$tool_content .= '</dl>';
		}
	}
	$tool_content .= "$langPollTotalAnswers : <b>$answer_total</b><br>";

// display page
draw($tool_content, 2); 
?>
