<?PHP

/*
*
*	File : class.Announcements.php
*
*	Announcements Class
*
*	The class in charge for collecting data regarding announcements for all
*	the lessons a user is subscribed to.
*
*	@author Evelthon Prodromou <eprodromou@upnet.gr>
*
*	@access public
*
*	@version 1.0.1
*
*/

/*ALTER TABLE `user` ADD `perso` ENUM( 'yes', 'no' ) DEFAULT 'no' NOT NULL ,
ADD `announce_flag` DATE NOT NULL ,
ADD `doc_flag` DATE NOT NULL ,
ADD `forum_flag` DATE NOT NULL ;*/
function getUserAnnouncements($param = null, $type) {

	global $mysqlMainDb, $uid, $dbname, $currentCourseID;

	$uid				= $param['uid'];
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
	'date'				=> $usr_lst_login
	);

	$queryParamMemo = array(
	'lesson_code'		=> $lesson_code,
	'max_repeat_val'	=> $max_repeat_val,
	'date'				=> $usr_memory
	);
	//echo $max_repeat_val;
	//	dumpArray($usr_memory);
	$announce_query_new 	= createQueries($queryParamNew);
	$announce_query_memo 	= createQueries($queryParamMemo);

	//	dumpArray($announce_querys_new);
	//		We have 2 SQL cases. The scripts tries to return all the new Announcement
	//		the user had since his last login. If the returned items are less than 1
	//		it gets the last announcements the user saw.
	//		--------------------------------------------------------------------------

	$announceGroup = array();
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

			//update the corresponding field in cours_user and set
			//the field's value to the last LOGIN date of the user
			//set a flag so that it only updates the date once! :)

			//PROSOXH!! to update na ginetai afou bgei apo to for!!1
			//alliws 8a to kanei se ka8e ma8hma pou exei nees anakoinwseis!! (axreiasto!)
		}

		while ($myAnnouncements = mysql_fetch_row($mysql_query_result)) {

			if ($myAnnouncements){
				/*if(strlen($myAnnouncements[0]) > 150) {
				$myAnnouncements[0] = substr($myAnnouncements[0], 0, 150);
				$myAnnouncements[0] .= " ...[Περισσότερα]";
				}*/
				$myAnnouncements[0] = strip_tags($myAnnouncements[0]);
				array_push($announceData,$myAnnouncements);

				/*				if (strlen($this->announce_data[5][$i][$rep_val]['content']) > 150)
				{
				$this->announce_data[5][$i][$rep_val]['content'] = substr($this->announce_data[5][$i][$rep_val]['content'], 0, 150);
				$this->announce_data[5][$i][$rep_val]['content'] .= "...  ";

				$t->set_var('ANNOUNCE_CONTENT_MORE_TXT', $this->langBlocksMoreTxt);
				}*/
			}
		}
		//		dumpArray($announceData);
		if ($num_rows > 0) {
			array_push($announceLessonData, $announceData);
			array_push($announceSubGroup, $announceLessonData);
		}

	}



	if ($getNewAnnounce) {
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
					$myAnnouncements[0] = strip_tags($myAnnouncements[0]);
					array_push($announceData,$myAnnouncements);
				}

				array_push($announceLessonData, $announceData);
				array_push($announceSubGroup, $announceLessonData);
			}
		}
		array_push($announceGroup, $announceSubGroup);
	}


	//	array_push($announceGroup, $announceSubGroup);

	//		print_a($announceSubGroup); //<<<---- auto einai to swsto array!!!
	//	dumpArray($announceGroup);
	//	print_a($announceSubGroup);
	//	return  $announcements_values;
	if($type == "html") {
		return announceHtmlInterface($announceSubGroup, $max_repeat_val);
	} elseif ($type == "data") {
		return $announceSubGroup;
	}

}

function announceHtmlInterface($data, $max_repeat_val) {
	global $urlServer,$langNoAnnouncementsExist, $langMore;
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
			$assign_content .= "
		<li class=\"category\">".$data[$i][0]."</li>
		";
			$url = $_SERVER['PHP_SELF'] . "?perso=2&c=" .$data[$i][1];
			for ($j=0; $j < $iterator; $j++){
				if(strlen($data[$i][2][$j][0]) > 150) {
					$data[$i][2][$j][0] = substr($data[$i][2][$j][0], 0, 150);
					$data[$i][2][$j][0] .= " <span class=\"announce_date\">$langMore</span>
					";
				}
				$assign_content .= "
		<li><a class=\"square_bullet\" href=\"$url\">
		<div class=\"content_pos\"><span class=\"announce_date\">".$data[$i][2][$j][1]." : </span>".$data[$i][2][$j][0]."</div></a>
			
		</li>
		";
			}

			if ($i+1 <$max_repeat_val) $assign_content .= "<br>";
		}
	}

	$assign_content .= "
	</ul>
			</div> 
";

	if (!$announceExist) {
		$assign_content = "<p>$langNoAnnouncementsExist</p>";
	}
	return $assign_content;

}

function createQueries($queryParam){
	global $mysqlMainDb, $maxValue;

	$lesson_code = $queryParam['lesson_code'];
	$max_repeat_val = $queryParam['max_repeat_val'];
	$date = $queryParam['date'];
	//	echo $max_repeat_val;
	for ($i=0;$i<$max_repeat_val;$i++) {

		if(is_array($date)){
			$dateVar = $date[$i];
		} else {
			$dateVar = $date;
		}

		$announce_query[$i] = "SELECT contenu, temps FROM " .$mysqlMainDb." . annonces
									WHERE code_cours='" . $lesson_code[$i] . "'
									AND DATE_FORMAT(temps,'%Y %m %d') >='" .$dateVar."'
									ORDER BY temps DESC
									";
		//		echo $announce_query[$i] . "<br>";
	}

	return $announce_query;
}



?>