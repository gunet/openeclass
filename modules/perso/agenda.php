<?PHP
/*
*
*	File : class.Agenda.php
*
*	Agenda class
*
*	Base class responsible for all calendar events
*	Responsible for organising calendar events and return them to the main class.
*
*	@author Evelthon Prodromou <eprodromou@upnet.gr>
*
*	@access public
*
*	@version 1.0.1
*
*	@license http://opensource.org/licenses/gpl-license.php GNU General Public License
*
*/




function getUserAgenda($param, $type) {

	$uniqueDates = 7;

	global $mysqlMainDb, $uid, $dbname, $currentCourseID;

	$uid				= $param['uid'];
	$lesson_code		= $param['lesson_code'];
	$max_repeat_val		= $param['max_repeat_val'];
	//	$lesson_title		= $param['lesson_titles'];
	//	$lesson_code		= $param['lesson_code'];
	//	$lesson_professor	= $param['lesson_professor'];

	//	$usr_lst_login	= $param['usr_lst_login'];

	//	$usr_memory = $param['usr_memory'];

	for($i=0; $i < $max_repeat_val; $i++) {
		if($i < 1) {
			$tbl_lesson_codes = " lesson_code=" . "'$lesson_code[$i]'";
		} else {
			$tbl_lesson_codes .= " OR lesson_code=" . "'$lesson_code[$i]'";
		}
	}

	//		5.	select data from temp and sort by date


	$sql = "SELECT agenda.titre, agenda.contenu, agenda.day, agenda.hour, agenda.lasting, agenda.lesson_code,cours.intitule
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

	$mysql_query_result = db_query($sql, $mysqlMainDb);

//	$i = 0;
//	$agendaGroup = array();
	$agendaDateData = array();
//	$today = date("Y-m-d");
	$previousDate = "0000-00-00";
	while ($myAgenda = mysql_fetch_row($mysql_query_result)) {
//		$parsedAgenda = false;
		if ($myAgenda[2] != $previousDate) {
			@array_push($agendaDateData, $agendaData);
//			$parsedAgenda = true;
		}
		
		if ($myAgenda[2] == $previousDate) {
			array_push($agendaData, $myAgenda);
		} else {
			$agendaData = array();
			$previousDate = $myAgenda[2];
			array_push($agendaData, $myAgenda);
		}

//		echo $previousDate ." <br>";
	}


		array_push($agendaDateData, $agendaData);
	
//	dumpArray($agendaDateData);
	//		Constructing the array of data to be parsed back
	//		------------------------------------------------
	/*$agenda_values = array	(
	'0'	=> $i,	//number of agenda events
	'1'	=> $lesson_agenda	// agenda data
	);*/

	//	return $agenda_values;

}




?>