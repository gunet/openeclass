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
 * Personalised Documents Component, eClass Personalised
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * @package eClass Personalised
 *
 * @abstract This component populates the agenda block on the user's personalised
 * interface. It is based on the diploma thesis of Evelthon Prodromou.
 *
 */

/**
 * Function getUserAgenda
 *
 * Populates an array with data regarding the user's personalised agenda.
 *
 * @param array $param
 * @param string $type (data, html)
 * @return array
 */
function getUserAgenda($param, $type)
{
        global $mysqlMainDb, $uid;
        
	//number of unique dates to collect data for
	$uniqueDates = 5;	        
	$uid			= $param['uid'];
	$lesson_code    = $param['lesson_code'];
	$course_id      = $param['lesson_id'];
        
	$max_repeat_val = $param['max_repeat_val'];
	$tbl_course_ids = array();
	
	// exclude courses with disabled agenda modules
	for($i=0; $i < $max_repeat_val; $i++) {               
	    $row = mysql_fetch_array(db_query("SELECT visible FROM course_module WHERE 
                                                      module_id = ".MODULE_ID_AGENDA." AND
                                                      course_id = ".$course_id[$i]));
	    if ($row['visible'] == 1) {
		    array_push($tbl_course_ids, $course_id[$i]);
            }
	}
	array_walk($tbl_course_ids, 'wrap_each');
	$tbl_course_ids = implode(",", $tbl_course_ids);

	//mysql version 4.x query
	$sql_4 = "SELECT agenda.title, agenda.content, agenda.day, agenda.hour, agenda.lasting, course.code, course.title
		FROM agenda, course WHERE agenda.course_id IN ($tbl_course_ids)
		AND agenda.course_id = course.id
		AND agenda.visible = 1
		HAVING (TO_DAYS(day) - TO_DAYS(NOW())) >= '0'
		ORDER BY day, hour DESC
		LIMIT $uniqueDates";

	//mysql version 5.x query
	$sql_5 = "SELECT agenda.title, agenda.content, agenda.day, DATE_FORMAT(agenda.hour, '%H:%i'), 
		agenda.lasting, course.code, course.title
		FROM agenda, course WHERE agenda.course_id IN ($tbl_course_ids) 
		AND agenda.course_id = course.id
		AND agenda.visible = 1
		HAVING (TO_DAYS(day) - TO_DAYS(NOW())) >= '0'
		ORDER BY day, hour DESC
		LIMIT $uniqueDates"; 

	$ver = mysql_get_server_info();

	if (version_compare("5.0", $ver) <= 0){
		$sql = $sql_5;//mysql 4 compatible query
	}
	elseif (version_compare("4.1", $ver) <= 0) {
		$sql = $sql_4;//mysql 5 compatible query
	}

	$mysql_query_result = db_query($sql, $mysqlMainDb);
	$agendaDateData = array();
	$previousDate = "0000-00-00";
	$firstRun = true;
	while ($myAgenda = mysql_fetch_row($mysql_query_result)) {
		//allow certain html tags that do not cause errors in the
		//personalised interface
		$myAgenda[1] = strip_tags($myAgenda[1], '<b><i><u><ol><ul><li><br>');
		if ($myAgenda[2] != $previousDate ) {
			if (!$firstRun) {
				@array_push($agendaDateData, $agendaData);
			}
		}

		if ($firstRun) $firstRun = false;

		if ($myAgenda[2] == $previousDate) {
			array_push($agendaData, $myAgenda);
		} else {
			$agendaData = array();
			$previousDate = $myAgenda[2];
			array_push($agendaData, $myAgenda);
		}
	}

	if (!$firstRun) {
		array_push($agendaDateData, $agendaData);
	}

	if($type == "html") {
		return agendaHtmlInterface($agendaDateData);
	} elseif ($type == "data") {
		return $agendaDateData;
	}
}


/*
 * Function agendaHtmlInterface
 *
 * @param array $data
 * @return string HTML content for the documents block
 * @see function getUserAgenda()
 */
function agendaHtmlInterface($data)
{
	global $langNoEventsExist, $langUnknown, $langDuration, $langMore, $langHours, $langHour;
	global $langExerciseStart, $urlServer, $dateFormatLong;

	$numOfDays = count($data);
	if ($numOfDays > 0) {
		$agenda_content= "<table width='100%'>";      
		for ($i=0; $i <$numOfDays; $i++) {
			$agenda_content .= "<tr><td class='sub_title1'>".claro_format_locale_date($dateFormatLong, strtotime($data[$i][0][2]))."</td></tr>";
			$iterator =  count($data[$i]);
			for ($j=0; $j < $iterator; $j++){
				$url = $urlServer . "index.php?perso=4&amp;c=" . $data[$i][$j][5];
				if (strlen($data[$i][$j][4]) == 0) {
					$data[$i][$j][4] = "$langUnknown";
				}
				elseif ($data[$i][$j][4] == 1) {
					$data[$i][$j][4] = $data[$i][$j][4]." $langHour";
				}
				else {
					$data[$i][$j][4] = $data[$i][$j][4]." $langHours";
				}
                                $data[$i][$j][0] = ellipsize($data[$i][$j][0], 80);
                                $data[$i][$j][1] = ellipsize($data[$i][$j][1], 150, "... <a href=\"$url\">[$langMore]</a>");
                                $data[$i][$j][6] = ellipsize($data[$i][$j][6], 60);
				$agenda_content .= "<tr><td><ul class='custom_list'><li><a href=\"$url\"><b>".q($data[$i][$j][0])."</b></a><br /><b>".q($data[$i][$j][6])."</b><div class='smaller'>".$langExerciseStart.":<b>".$data[$i][$j][3]."</b> | $langDuration:<b>".$data[$i][$j][4]."</b><br />".standard_text_escape($data[$i][$j][1])."</div></li></ul></td></tr>";
			}
		}
		$agenda_content .= "</table>";
	} else {
		$agenda_content = "<p class='alert1'>$langNoEventsExist</p>";
	}
	return $agenda_content;
}
