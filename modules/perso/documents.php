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
        $usr_lst_login = $param['usr_lst_login'];
	$usr_memory = $param['usr_memory'];

        // Try to return all the new documents the user had since his last login.
        // If no items are returned, get the last documents the user had by using
        // the docs_flag field.

        $new_docs = docsHtmlInterface($usr_lst_login);
        if (empty($new_docs)) {
		// if there are no new documents, get the last documents the user had
		// so that we always have something to display
        	$new_docs = docsHtmlInterface($usr_memory);
        } else {
		$sqlNowDate = str_replace(' ', '-', $usr_lst_login);
                db_query("UPDATE `user` SET `doc_flag` = '$sqlNowDate' WHERE `user_id` = $uid");
        }

        return $new_docs;
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
	global $urlServer, $langNoDocsExist, $uid, $currentCourseID, $cours_id;
        global $mysqlMainDb, $maxValue;

        $q = db_query("SELECT path, course_id, code, filename, title, date_modified, intitule
                       FROM document, cours_user, cours
                       WHERE document.course_id = cours_user.cours_id AND
                             cours_user.user_id = $uid AND
                             cours.cours_id = cours_user.cours_id AND
                             group_id IS NULL AND
                             visibility = 'v' AND
                             date_modified >= '$date' AND
			     format <> '.dir'
                       ORDER BY course_id, date_modified DESC", $mysqlMainDb);

        $last_course_id = null;
        if ($q and mysql_num_rows($q) > 0) {
                $content = '<div class="datacontainer"><ul class="datalist">';
                while ($row = mysql_fetch_array($q)) {
                        if ($last_course_id != $row['course_id']) {
                                $content .= "\n<li class='category'>" . q($row['intitule']) . "</li>";
				$currentCourseID = $row['code'];
				$cours_id = $row['course_id'];
                        }
                        $last_course_id = $row['course_id'];
			$url = file_url($row['path']);
                        $content .= "\n<li><a class='square_bullet2' href='$url'><strong class='title_pos'>" .
                                    q($row['filename']) . ' - (' .
                                    nice_format(date('Y-m-d', strtotime($row['date_modified']))) .
                                    ")</strong></a></li>";
		}
		unset($currentCourseID);
                $content .= "\n</ul></div>\n";
                return $content;
	} else {
		return "\n<p>$langNoDocsExist</p>\n";
	}
}
