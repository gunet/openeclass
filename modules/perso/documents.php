<?PHP

/*
*
*	File : documents.php
*
*	documents personalised component
*
*	In charge for collecting data regarding documents for all
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
function getUserDocuments($param = null, $type) {

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
	$docs_query_new 	= createDocsQueries($queryParamNew);
	$docs_query_memo 	= createDocsQueries($queryParamMemo);

//	print_a($docs_query_new);
	//		We have 2 SQL cases. The scripts tries to return all the new Announcement
	//		the user had since his last login. If the returned items are less than 1
	//		it gets the last announcements the user saw.
	//		--------------------------------------------------------------------------

	$docsGroup = array();
	$docsSubGroup = array();
	$getNewDocs = false;
	for ($i=0;$i<$max_repeat_val;$i++) { //each iteration refers to one lesson

		$mysql_query_result = db_query($docs_query_new[$i], $lesson_code[$i]);

		if ($num_rows = mysql_num_rows($mysql_query_result) > 0) {
			$getNewDocs = true;

			$docsLessonData = array();
			$docsData = array();

			array_push($docsLessonData, $lesson_title[$i]);
			array_push($docsLessonData, $lesson_code[$i]);

			//update the corresponding field in cours_user and set
			//the field's value to the last LOGIN date of the user
			//set a flag so that it only updates the date once! :)

			//PROSOXH!! to update na ginetai afou bgei apo to for!!1
			//alliws 8a to kanei se ka8e ma8hma pou exei nees anakoinwseis!! (axreiasto!)
//			echo "data exists";
		}

		while ($myDocuments = mysql_fetch_row($mysql_query_result)) {

			if ($myDocuments){
				array_push($docsData,$myDocuments);
			}
		}
//				print_a($docsData);
		if ($num_rows > 0) {
			array_push($docsLessonData, $docsData);
			array_push($docsSubGroup, $docsLessonData);
		}

	}



	if ($getNewDocs) {
		array_push($docsGroup, $docsSubGroup);
		$sqlNowDate = eregi_replace(" ", "-",$usr_lst_login);
		$sql = "UPDATE `user` SET `doc_flag` = '$sqlNowDate' WHERE `user_id` = $uid ";
		db_query($sql, $mysqlMainDb);
//		echo $sql;
		//update announcemenets memory
		//call announceHtmlInterface()
	} elseif (!$getNewDocs) {
		
		//if there are no new announcements, get the last announcements the user had
		//so that we always have something to display
		for ($i=0; $i < $max_repeat_val; $i++){
			$mysql_query_result = db_query($docs_query_memo[$i], $lesson_code[$i]);
			
			if (mysql_num_rows($mysql_query_result) > 0) {
				$docsLessonData = array();
				$docsData = array();
				array_push($docsLessonData, $lesson_title[$i]);
				array_push($docsLessonData, $lesson_code[$i]);
				//auto yphrxe k sto announcements. Giati yparxei ?
//				$mysql_query_result = db_query($announce_query_memo[$i]);

				while ($myDocuments = mysql_fetch_row($mysql_query_result)) {
					array_push($docsData,$myDocuments);
				}

				array_push($docsLessonData, $docsData);
				array_push($docsSubGroup, $docsLessonData);
			}
		}
		array_push($docsGroup, $docsSubGroup);
	}


	//	array_push($announceGroup, $announceSubGroup);

//			print_a($docsSubGroup); //<<<---- auto einai to swsto array!!!

	if($type == "html") {
		return docsHtmlInterface($docsSubGroup);
	} elseif ($type == "data") {
		return $docsSubGroup;
	}

}

function docsHtmlInterface($data) {
	global $urlServer, $langNoDocsExist ;
	
	$docsExist = false;
	$content= <<<aCont
	<div id="datacontainer">

				<ul id="datalist">
aCont;
	$max_repeat_val = count($data);
	for ($i=0; $i <$max_repeat_val; $i++) {
		$iterator =  count($data[$i][2]);
		if ($iterator > 0) {
			$docsExist = true;
			$content .= "
		<li class=\"category\">".$data[$i][0]."</li>
		";
			$url = $_SERVER['PHP_SELF'] . "?perso=6&c=" .$data[$i][1];
			for ($j=0; $j < $iterator; $j++){

				$content .= "
		<li><a class=\"square_bullet\" href=\"$url\"><div class=\"content_pos\">".$data[$i][2][$j][2]." ( ".$data[$i][2][$j][3].")</div></a>
			
		</li>
		";
			}

			if ($i+1 <$max_repeat_val) $content .= "<br>";
		}
	}

	$content .= "
	</ul>
			</div> 
";

	if (!$docsExist) {
		$content = "<p>$langNoDocsExist</p>";
	}
//	echo $content;
	return $content;

}

function createDocsQueries($queryParam){
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

		$docs_query[$i] = "SELECT path, filename, title, date_modified
								FROM document
								WHERE visibility = 'v'
								AND DATE_FORMAT(date_modified,'%Y %m %d') >='" .$dateVar."'
								ORDER BY date_modified DESC
									";
//				echo $docs_query[$i] . "<br>";
	}

	return $docs_query;
}



?>