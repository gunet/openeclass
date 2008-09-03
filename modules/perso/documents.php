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
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/


/*

 * Personalised Documents Component, eClass Personalised

 *

 * @author Evelthon Prodromou <eprodromou@upnet.gr>

 * @version $Id$

 * @package eClass Personalised

 *

 * @abstract This component populates the documents block on the user's personalised

 * interface. It is based on the diploma thesis of Evelthon Prodromou.

 *

 */



/*

 * Function getUserDocuments

 *

 * Populates an array with data regarding the user's personalised documents

 *

 * @param array $param

 * @param  string $type (data, html)

 * @return array

 */

function getUserDocuments($param = null, $type) {



	global $mysqlMainDb, $uid, $dbname, $currentCourseID;



	$uid	= $param['uid'];

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



	$docs_query_new 	= createDocsQueries($queryParamNew);

	$docs_query_memo 	= createDocsQueries($queryParamMemo);



	//		We have 2 SQL cases. The scripts tries to return all the new documents

	//		the user had since his last login. If the returned items are less than 1

	//		it gets the last documents the user had by using the docs_flag field

	//		(table user, main database).

	//		--------------------------------------------------------------------------



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


		}



		while ($myDocuments = mysql_fetch_row($mysql_query_result)) {

			if ($myDocuments){

				$myDocuments[0] = strrev(substr(strstr(strrev($myDocuments[0]),"/"), 1));

				array_push($docsData,$myDocuments);

			}

		}



		if ($num_rows > 0) {

			array_push($docsLessonData, $docsData);

			array_push($docsSubGroup, $docsLessonData);

		}


	}



	if ($getNewDocs) {

		$docsGroup = array();

		array_push($docsGroup, $docsSubGroup);

		$sqlNowDate = eregi_replace(" ", "-",$usr_lst_login);

		$sql = "UPDATE `user` SET `doc_flag` = '$sqlNowDate' WHERE `user_id` = $uid ";

		db_query($sql, $mysqlMainDb);



	} elseif (!$getNewDocs) {



		//if there are no new documents, get the last documents the user had

		//so that we always have something to display

		for ($i=0; $i < $max_repeat_val; $i++){

			$mysql_query_result = db_query($docs_query_memo[$i], $lesson_code[$i]);



			if (mysql_num_rows($mysql_query_result) > 0) {

				$docsLessonData = array();

				$docsData = array();

				array_push($docsLessonData, $lesson_title[$i]);

				array_push($docsLessonData, $lesson_code[$i]);



				while ($myDocuments = mysql_fetch_row($mysql_query_result)) {

					$myDocuments[0] = strrev(substr(strstr(strrev($myDocuments[0]),"/"), 1));

					array_push($docsData,$myDocuments);

				}

				array_push($docsLessonData, $docsData);

				array_push($docsSubGroup, $docsLessonData);

			}

		}

	}



	if($type == "html") {

		return docsHtmlInterface($docsSubGroup);

	} elseif ($type == "data") {

		return $docsSubGroup;

	}



}



/**

 * Function docsHtmlInterface

 *

 * Generates html content for the documents block of eClass personalised.

 *

 * @param array $data

 * @return string HTML content for the documents block

 * @see function getUserDocuments()

 */

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

            <li class=\"category\">".$data[$i][0]."</li>";

			for ($j=0; $j < $iterator; $j++) {

				$url = $_SERVER['PHP_SELF'] . "?perso=6&c=" .$data[$i][1]."&p=".$data[$i][2][$j][0];

				$content .= "
            <li><a class=\"square_bullet2\" href=\"$url\"><p class=\"content_pos\">".nice_format(date("Y-m-d", strtotime($data[$i][2][$j][3])))." : ".$data[$i][2][$j][1]."</p></a></li>";
			}

			if ($i+1 <$max_repeat_val) $content .= "<br>";

		}

	}

	$content .= "
        </ul>
    </div> ";

	if (!$docsExist) {

		$content = "<p>$langNoDocsExist</p>";

	}

	return $content;

}


/**

 * Function createDocsQueries

 *

 * Creates needed queries used by getUserDocuments

 *

 * @param array $queryParam

 * @return array sql query

 * @see function getUserDocuments()

 */

function createDocsQueries($queryParam){

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



		$docs_query[$i] = "SELECT path, filename, title, date_modified

			FROM document, accueil WHERE visibility = 'v'

			AND DATE_FORMAT(date_modified,'%Y %m %d') >='" .$dateVar."'

			AND accueil.visible =1

			AND accueil.id =3

			ORDER BY date_modified DESC";

	}

	return $docs_query;

}



?>

