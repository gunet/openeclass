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
	$lesson_id		= $param['lesson_id'];
	$lesson_code		= $param['lesson_code'];
	$max_repeat_val		= $param['max_repeat_val'];
	$lesson_title		= $param['lesson_titles'];
	$lesson_code		= $param['lesson_code'];
	$lesson_professor	= $param['lesson_professor'];

	$usr_lst_login	= $param['usr_lst_login'];

	$usr_memory = $param['usr_memory'];

        // Generate SQL code for all queries
        // ----------------------------------------
        // We have 2 SQL cases. The scripts tries to return all new announcements
        // the user had since his last login. If the returned rows are less than 1
        // it gets the last announcements the user had.
        // -----------------------------------------------------------------------

	$announce_query_new = createQueries(array(
                'lesson_id' => $lesson_id,
                'lesson_code' => $lesson_code,
                'max_repeat_val' => $max_repeat_val,
                'date' => $usr_lst_login));
	$announce_query_memo = createQueries(array(
                'lesson_id' => $lesson_id,
                'lesson_code' => $lesson_code,
                'max_repeat_val' => $max_repeat_val,
                'date' => $usr_memory));

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
		$sqlNowDate = str_replace(' ', '-', $usr_lst_login);
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
	global $urlAppend, $langNoAnnouncementsExist, $langMore, $dateFormatLong;
	$announceExist = false;
	$assign_content= '<div class="datacontainer"><ul>';

	$max_repeat_val = count($data);
	for ($i=0; $i <$max_repeat_val; $i++) {
		$iterator =  count($data[$i][2]);
		if ($iterator > 0) {
			$announceExist = true;
			$assign_content .= "\n          <li class='category'>".$data[$i][0]."</li>";
			$url = $urlAppend . "/modules/announcements/announcements.php?course=" .$data[$i][1]."&amp;an_id=";
			for ($j=0; $j < $iterator; $j++) {
				$an_id = $data[$i][2][$j][3];
				$assign_content .= "\n<li><a class='square_bullet2' href='$url$an_id'>" .
                                           "<strong class='title_pos'>" . $data[$i][2][$j][0] .
                                           autoCloseTags($data[$i][2][$j][0]) .
                                           " <span class='announce_date'> (" .
                                           claro_format_locale_date($dateFormatLong, strtotime($data[$i][2][$j][2])) .
                                           ")</span></strong></a>".
						standard_text_escape(
	                                           ellipsize($data[$i][2][$j][1], 250, "<strong>&nbsp;...<a href='$url$an_id'>[$langMore]</a></strong>")) .
					   "</li>";
			}
		}
	}

	$assign_content .= "
        </ul>
      </div> ";

	if (!$announceExist) {
		$assign_content = "<p class='alert1'>$langNoAnnouncementsExist</p>";
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

	$lesson_id = $queryParam['lesson_id'];
	$lesson_code = $queryParam['lesson_code'];
	$max_repeat_val = $queryParam['max_repeat_val'];
	$date = $queryParam['date'];

	for ($i=0;$i<$max_repeat_val;$i++) {
		if(is_array($date)){
			$dateVar = $date[$i];
		} else {
			$dateVar = $date;
		}

		$announce_query[$i] = "SELECT title, contenu, temps, annonces.id
                        FROM `$mysqlMainDb`.annonces, `$lesson_code[$i]`.accueil
                        WHERE cours_id = $lesson_id[$i]
				AND visibility = 'v'
                                AND DATE_FORMAT(temps,'%Y %m %d / %H %i') >='$dateVar'
                                AND `$lesson_code[$i]`.accueil.visible = 1
                                AND `$lesson_code[$i]`.accueil.id = 7
                        ORDER BY temps DESC";
	}
	return $announce_query;
}
