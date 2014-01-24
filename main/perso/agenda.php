<?php
/* ========================================================================
 * Open eClass 2.8
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
	$lesson_code		= $param['lesson_code'];
	$max_repeat_val		= $param['max_repeat_val'];
	$tbl_lesson_codes = array();
	
	for($i=0; $i < $max_repeat_val; $i++) {
		array_push($tbl_lesson_codes, $lesson_code[$i]);
	}
	array_walk($tbl_lesson_codes, 'wrap_each');
	$tbl_lesson_codes = implode(",", $tbl_lesson_codes);

	//mysql version 4.x query
	$sql_4 = "SELECT agenda.titre, agenda.contenu, agenda.day, agenda.hour, agenda.lasting, 
			agenda.lesson_code, cours.intitule
		FROM agenda, cours WHERE agenda.lesson_code IN ($tbl_lesson_codes)
		AND agenda.lesson_code = cours.code
		GROUP BY day
		HAVING (TO_DAYS(day) - TO_DAYS(NOW())) >= '0'
		ORDER BY day, hour DESC
		LIMIT $uniqueDates";

	//mysql version 5.x query
	$sql_5 = "SELECT agenda.titre, agenda.contenu, agenda.day, DATE_FORMAT(agenda.hour, '%H:%i'), 
		agenda.lasting, agenda.lesson_code, cours.intitule
		FROM agenda, cours WHERE agenda.lesson_code IN ($tbl_lesson_codes) 
		AND agenda.lesson_code = cours.code
		GROUP BY day
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
				$url = $urlServer . "modules/agenda/agenda.php?c=" . $data[$i][$j][5];
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
                                $data[$i][$j][1] = ellipsize(q(strip_tags($data[$i][$j][1])), 150, "... <a href=\"$url\">[$langMore]</a>");
                                $data[$i][$j][6] = ellipsize($data[$i][$j][6], 60);
				$agenda_content .= "<tr><td><ul class='custom_list'><li><a href=\"$url\"><b>".q($data[$i][$j][0])."</b></a><br /><b>".q($data[$i][$j][6])."</b><div class='smaller'>".$langExerciseStart.": <b>".$data[$i][$j][3]."</b> | $langDuration: <b>".$data[$i][$j][4]."</b><br />".$data[$i][$j][1]."</div></li></ul></td></tr>";
			}
		}
		$agenda_content .= "</table>";
	} else {
		$agenda_content = "<p class='alert1'>$langNoEventsExist</p>";
	}
	return $agenda_content;
}
