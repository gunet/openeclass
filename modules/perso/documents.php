<?PHP

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
function getUserDocuments($param)
{
	global $mysqlMainDb, $uid;

        $lesson_code = $param['lesson_code'];
        $max_repeat_val = $param['max_repeat_val'];
        $lesson_title = $param['lesson_titles'];
        $usr_lst_login = $param['usr_lst_login'];
	$usr_memory = $param['usr_memory'];

	// We have 2 SQL cases. The scripts tries to return all the new documents
	// the user had since his last login. If the returned items are less than 1
	// it gets the last documents the user had by using the docs_flag field
	// (table user, main database).

	$docs_query_new = docsHtmlInterface($usr_lst_login);
	$docs_query_memo = docsHtmlInterface($usr_memory);

	$docsSubGroup = array();
	$getNewDocs = false;

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
}


/**
 * Function docsHtmlInterface
 *
 * Generates html content for the documents block of eClass personalised.
 *
 * @param $date
 * @return string HTML content for the documents block
 * @see function getUserDocuments()
 */
function docsHtmlInterface($date)
{
	global $urlServer, $langNoDocsExist, $uid;
        global $mysqlMainDb, $maxValue;

        $q = db_query("SELECT path, filename, title, date_modified
                       FROM document, cours_user, accueil
                       WHERE visibility = 'v' AND
                             date_modified >= '$date' AND
                             accueil.visible = 1 AND
                             accueil.id = 3
                       ORDER BY date_modified DESC");

	$docsExist = false;
	$content= <<<aCont
    <div class="datacontainer">
      <ul class="datalist">
aCont;

	$max_repeat_val = count($data);
	for ($i=0; $i <$max_repeat_val; $i++) {
		$iterator =  count($data[$i][2]);
		if ($iterator > 0) {
			$docsExist = true;
			$content .= "\n          <li class=\"category\">".$data[$i][0]."</li>";
			for ($j=0; $j < $iterator; $j++) {
				$url = $urlServer . "index.php?perso=6&amp;c=" .$data[$i][1]."&amp;p=".$data[$i][2][$j][0];
				$content .= "\n          <li><a class=\"square_bullet2\" href=\"$url\"><strong class=\"title_pos\">".$data[$i][2][$j][1]." - (".nice_format(date("Y-m-d", strtotime($data[$i][2][$j][3]))).")</strong></a></li>";
			}
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


