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

/*

 * Personalised Announcements Component, eClass Personalised

 *

 * @author Evelthon Prodromou <eprodromou@upnet.gr>

 * @version $Id$

 * @package eClass Personalised

 *

 * @abstract This component populates the announcements block on the user's personalised

 * interface. It is based on the diploma thesis of Evelthon Prodromou.

 *

 */



/*

 * Function getUserAnnouncements

 *

 * Populates an array with data regarding the user's personalised announcements

 *

 * @param array $param

 * @param string $type (data, html)

 * @return array

 */

function getUserAnnouncements($param = null, $type) {



	global $mysqlMainDb, $uid, $dbname, $currentCourseID;



	$uid			= $param['uid'];

	$lesson_code		= $param['lesson_code'];

	$max_repeat_val		= $param['max_repeat_val'];

	$lesson_title		= $param['lesson_titles'];

	$lesson_code		= $param['lesson_code'];

	$lesson_professor	= $param['lesson_professor'];



	$usr_lst_login	= $param['usr_lst_login'];



	$usr_memory = $param['usr_memory'];



	//		Generate SQL code for all queries

	//		----------------------------------------

	$queryParamNew = array(

	'lesson_code'		=> $lesson_code,

	'max_repeat_val'	=> $max_repeat_val,

	'date'		=> $usr_lst_login

	);



	$queryParamMemo = array(

	'lesson_code'		=> $lesson_code,

	'max_repeat_val'	=> $max_repeat_val,

	'date'		=> $usr_memory

	);



	$announce_query_new 	= createQueries($queryParamNew);

	$announce_query_memo 	= createQueries($queryParamMemo);



	//		We have 2 SQL cases. The scripts tries to return all new announcements

	//		the user had since his last login. If the returned rows are less than 1

	//		it gets the last announcements the user had.

	//		-----------------------------------------------------------------------



	$announceSubGroup = array();

	$getNewAnnounce = false;

	for ($i=0;$i<$max_repeat_val;$i++) { //each iteration refers to one lesson



		$mysql_query_result = db_query($announce_query_new[$i]);



		if ($num_rows = mysql_num_rows($mysql_query_result) > 0) {

			$getNewAnnounce = true;

			$announceLessonData = array();

			$announceData = array();



			array_push($announceLessonData, $lesson_title[$i]);

			array_push($announceLessonData, $lesson_code[$i]);

		}



		while ($myAnnouncements = mysql_fetch_row($mysql_query_result)) {

			if ($myAnnouncements){

				$myAnnouncements[0] = strip_tags($myAnnouncements[0], '<b><i><u><ol><ul><li><br>');

				array_push($announceData,$myAnnouncements);

			}

		}



		if ($num_rows > 0) {

			array_push($announceLessonData, $announceData);

			array_push($announceSubGroup, $announceLessonData);

		}

	}





	if ($getNewAnnounce) {

		$announceGroup = array();

		array_push($announceGroup, $announceSubGroup);

		$sqlNowDate = eregi_replace(" ", "-",$usr_lst_login);

		$sql = "UPDATE `user` SET `announce_flag` = '$sqlNowDate' WHERE `user_id` = $uid ";

		db_query($sql, $mysqlMainDb);



	} elseif (!$getNewAnnounce) {

		//if there are no new announcements, get the last announcements the user had

		//so that we always have something to display

		for ($i=0; $i < $max_repeat_val; $i++){

			$mysql_query_result = db_query($announce_query_memo[$i]);

			if (mysql_num_rows($mysql_query_result) > 0) {

				$announceLessonData = array();

				$announceData = array();

				array_push($announceLessonData, $lesson_title[$i]);

				array_push($announceLessonData, $lesson_code[$i]);



				$mysql_query_result = db_query($announce_query_memo[$i]);



				while ($myAnnouncements = mysql_fetch_row($mysql_query_result)) {

					$myAnnouncements[0] = strip_tags($myAnnouncements[0], '<b><i><u><ol><ul><li><br>');

					array_push($announceData,$myAnnouncements);

				}

				array_push($announceLessonData, $announceData);

				array_push($announceSubGroup, $announceLessonData);

			}

		}

	}



	if($type == "html") {

		return announceHtmlInterface($announceSubGroup);

	} elseif ($type == "data") {

		return $announceSubGroup;

	}



}



/**

 * Function announceHtmlInterface

 *

 * Generates html content for the announcements block of eClass personalised.

 *

 * @param array $data

 * @return string HTML content for the documents block

 * @see function getUserAnnouncements()

 */

function announceHtmlInterface($data) {

	global $urlServer, $langNoAnnouncementsExist, $langMore;

	$announceExist = false;

	$assign_content= <<<aCont

      <div id="datacontainer">
        <ul id="datalist">
aCont;

	$max_repeat_val = count($data);
	for ($i=0; $i <$max_repeat_val; $i++) {
		$iterator =  count($data[$i][2]);
		if ($iterator > 0) {
			$announceExist = true;
			$assign_content .= "\n          <li class=\"category\">".$data[$i][0]."</li>";
			$url = $_SERVER['PHP_SELF'] . "?perso=2&c=" .$data[$i][1];
			for ($j=0; $j < $iterator; $j++){
				if(strlen($data[$i][2][$j][1]) > 150) {
					$data[$i][2][$j][1] = substr($data[$i][2][$j][1], 0, 150);
					$data[$i][2][$j][1] .= "... <a href=\"$url\">[$langMore]</a>";
				}
				if(strlen($data[$i][2][$j][0]) > 50) {
					$data[$i][2][$j][0] = substr($data[$i][2][$j][0], 0, 50);
					$data[$i][2][$j][0] .= "...";
				}

			$assign_content .= "\n          <li><a class=\"square_bullet2\" href=\"$url\"><strong class=\"title_pos\">".$data[$i][2][$j][0].autoCloseTags($data[$i][2][$j][0])." <span class=\"announce_date\"> (".nice_format($data[$i][2][$j][2]).")</span></strong></a><p class=\"content_pos\">".$data[$i][2][$j][1].autoCloseTags($data[$i][2][$j][1])."</p></li>";
			}
			//if ($i+1 <$max_repeat_val) $assign_content .= "<br>";
		}
	}

	$assign_content .= "
        </ul>
      </div> ";

	if (!$announceExist) {
		$assign_content = "<p>$langNoAnnouncementsExist</p>";
	}
	return $assign_content;

}



/**

 * Function createQueries

 *

 * Creates needed queries used by getUserAnnouncements

 *

 * @param array $queryParam

 * @return array sql query

 * @see function getUserAnnouncements()

 */

function createQueries($queryParam){

	global $mysqlMainDb, $maxValue;

	$lesson_code = $queryParam['lesson_code'];
	$max_repeat_val = $queryParam['max_repeat_val'];
	$date = $queryParam['date'];


	for ($i=0;$i<$max_repeat_val;$i++) {

		if(is_array($date)){

			$dateVar = $date[$i];

		} else {

			$dateVar = $date;

		}



		$announce_query[$i] = "SELECT title, contenu, temps

		FROM " .$mysqlMainDb." . annonces, ".$lesson_code[$i].".accueil

		WHERE code_cours='" . $lesson_code[$i] . "'

		AND DATE_FORMAT(temps,'%Y %m %d') >='" .$dateVar."'

		AND ".$lesson_code[$i].".accueil.visible =1

		AND ".$lesson_code[$i].".accueil.id =7

		ORDER BY temps DESC";
	}

	return $announce_query;

}



?>

