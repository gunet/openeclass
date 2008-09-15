<?PHP
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/


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

function getUserAgenda($param, $type) {



	//number of unique dates to collect data for

	$uniqueDates = 5;

	global $mysqlMainDb, $uid, $dbname, $currentCourseID;


	$uid			= $param['uid'];

	$lesson_code		= $param['lesson_code'];

	$max_repeat_val		= $param['max_repeat_val'];



	for($i=0; $i < $max_repeat_val; $i++) {

		if($i < 1) {

			$tbl_lesson_codes = " lesson_code=" . "'$lesson_code[$i]'";

		} else {

			$tbl_lesson_codes .= " OR lesson_code=" . "'$lesson_code[$i]'";

		}

	}



	//mysql version 4.x query

	$sql_4 = "SELECT agenda.titre, agenda.contenu, agenda.day, agenda.hour, agenda.lasting, agenda.lesson_code,cours.intitule

			FROM 

			(	SELECT day

				FROM agenda 

				WHERE ($tbl_lesson_codes)

				GROUP BY day

				HAVING (TO_DAYS(day) - TO_DAYS(NOW())) >= '0'

				ORDER BY day DESC

				LIMIT $uniqueDates

			) AS A, cours

			JOIN agenda ON agenda.day = A.day

			WHERE agenda.lesson_code = cours.code

			ORDER by day, hour

			";
	

	//mysql version 5.x query

	$sql_5= "SELECT agenda.titre, agenda.contenu, agenda.day, 
			DATE_FORMAT(agenda.hour, '%H:%i'), 
			agenda.lasting, agenda.lesson_code,cours.intitule

			FROM 

			((	SELECT day

				FROM agenda 

				WHERE ($tbl_lesson_codes)

				GROUP BY day

				HAVING (TO_DAYS(day) - TO_DAYS(NOW())) >= '0'

				ORDER BY day DESC

				LIMIT $uniqueDates

			) AS A, cours)

			JOIN agenda ON agenda.day = A.day

			WHERE agenda.lesson_code = cours.code

			ORDER by day, hour

			";



	$ver = mysql_get_server_info();

	

	if (version_compare("5.0", $ver) <= 0)

	$sql = $sql_5;//mysql 4 compatible query

	elseif (version_compare("4.1", $ver) <= 0)

	$sql = $sql_4;//mysql 5 compatible query

	

	$mysql_query_result = db_query($sql, $mysqlMainDb);



	//	$agendaGroup = array();

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

function agendaHtmlInterface($data) {

	global $langNoEventsExist, $langUnknown, $langDuration, $langMore, $l_ondate, $langHours, $langHour;

	$numOfDays = count($data);

	if ($numOfDays > 0) {

		$agenda_content= <<<agCont

	<div id="datacontainer">



	<ul id="datalist">

agCont;
		for ($i=0; $i <$numOfDays; $i++) {

			$agenda_content .= "<li class=\"category\">".nice_format($data[$i][0][2])."</li>";

			$iterator =  count($data[$i]);

			for ($j=0; $j < $iterator; $j++){

				$url = $_SERVER['PHP_SELF'] . "?perso=4&c=" . $data[$i][$j][5];

				if (strlen($data[$i][$j][4]) == 0) {
					$data[$i][$j][4] = "$langUnknown";
				}
				elseif ($data[$i][$j][4] == 1) {
					$data[$i][$j][4] = $data[$i][$j][4]." $langHour";
				}
				else {
					$data[$i][$j][4] = $data[$i][$j][4]." $langHours";
				}


				if(strlen($data[$i][$j][1]) > 150) {
					$data[$i][$j][1] = substr($data[$i][$j][1], 0, 150);
					$data[$i][$j][1] .= " <strong class=\"announce_date\">$langMore</strong>";
				}

				$agenda_content .= "<li><a class=\"square_bullet\" href=\"$url\">
				<p class=\"title_pos\">
				<span class=\"announce_date\">".$data[$i][$j][0]."</span></p>
				<strong class=\"title_pos\">"
				.$data[$i][$j][6]."&nbsp;".$l_ondate."&nbsp;".$data[$i][$j][3]." ($langDuration: ".$data[$i][$j][4].")</strong>
				</a>
				<p class=\"content_pos\">".$data[$i][$j][1].autoCloseTags($data[$i][$j][1])."</p>
				</li>";
			}

			if ($i+1 <$numOfDays) $agenda_content .= "<br>";
		}
		$agenda_content .= "</ul></div> ";

	} else {

		$agenda_content = "<p>$langNoEventsExist</p>";

	}


	return $agenda_content;

}

?>

