<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

/*
 * Perso Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component is the central controller of eclass personalised.
 * It controls personalisation and initialises several variables used by it.
 *
 * It is based on the diploma thesis of Evelthon Prodromou
 *
 */

if (!defined('INDEX_START')) {
	die("Action not allowed!");
}
define('MAIN', 0);

include 'redirector.php';
$status = array();
$sql = "SELECT cours.cours_id cours_id, cours.code code, cours.fake_code fake_code,
                        cours.intitule title, cours.titulaires profs, cours_user.statut statut
                FROM cours JOIN cours_user ON cours.cours_id = cours_user.cours_id
                WHERE cours_user.user_id = $uid        
                ORDER BY statut, cours.intitule, cours.titulaires";
$sql2 = "SELECT cours.cours_id cours_id, cours.code code, cours.fake_code fake_code,
                        cours.intitule title, cours.titulaires profs, cours_user.statut statut
                FROM cours JOIN cours_user ON cours.cours_id = cours_user.cours_id
                WHERE cours_user.user_id = $uid
                AND cours.visible != ".COURSE_INACTIVE."
                ORDER BY statut, cours.intitule, cours.titulaires";

if ($_SESSION['statut'] == 1) {
        $result2 = db_query($sql);
}
if ($_SESSION['statut'] == 5) {            
        $result2 = db_query($sql2);
}

if ($result2 and mysql_num_rows($result2) > 0) {
	while ($mycours = mysql_fetch_array($result2)) {
		$status[$mycours['code']] = $mycours['statut'];
	}
}
$_SESSION['status'] = $status;
$subsystem = MAIN;

include "lib/textLib.inc.php";
include "lib/fileDisplayLib.inc.php";
//include personalised component files (announcemets.php etc.) from /modules/perso
include "$webDir/modules/perso/lessons.php";
include "$webDir/modules/perso/assignments.php";
include "$webDir/modules/perso/announcements.php";
include "$webDir/modules/perso/documents.php";
include "$webDir/modules/perso/agenda.php";
include "$webDir/modules/perso/forumPosts.php";

$_user['persoLastLogin'] = last_login($uid);
$_user['lastLogin'] = str_replace('-', ' ', $_user['persoLastLogin']);

//	BEGIN Get user's lesson info]=====================================================
$user_lesson_info = getUserLessonInfo($uid, "html");
//	END Get user's lesson info]=====================================================

//if user is registered to at least one lesson
if ($user_lesson_info[0][0] > 0) {
	// BEGIN - Get user assignments
	$param = array(	'uid'	=> $uid,
	'max_repeat_val' 	=> $user_lesson_info[0][0], //max repeat val (num of lessons)
	'lesson_titles'	=> $user_lesson_info[0][1],
	'lesson_code'	=> $user_lesson_info[0][2],
	'lesson_professor'	=> $user_lesson_info[0][3],
	'lesson_statut'		=> $user_lesson_info[0][4],
	'lesson_id'             => $user_lesson_info[0][8]
	);
	$user_assignments = getUserAssignments($param, "html");
	//END - Get user assignments

	// BEGIN - Get user announcements
	$param = array(	'uid'	=> $uid,
	'max_repeat_val' 	=> $user_lesson_info[0][0], //max repeat val (num of lessons)
	'lesson_titles'	=> $user_lesson_info[0][1],
	'lesson_code'	=> $user_lesson_info[0][2],
	'lesson_professor'	=> $user_lesson_info[0][3],
	'lesson_statut'		=> $user_lesson_info[0][4],
	'usr_lst_login'		=> $_user["lastLogin"],
	'usr_memory'		=> $user_lesson_info[0][5],
        'lesson_id'             => $user_lesson_info[0][8]
	);

	$user_announcements = getUserAnnouncements($param, 'html');
	// END - Get user announcements

	// BEGIN - Get user documents

	$param = array(	'uid'	=> $uid,
	'max_repeat_val' 	=> $user_lesson_info[0][0], //max repeat val (num of lessons)
	'lesson_titles'	=> $user_lesson_info[0][1],
	'lesson_code'	=> $user_lesson_info[0][2],
	'lesson_professor'	=> $user_lesson_info[0][3],
	'lesson_statut'		=> $user_lesson_info[0][4],
	'usr_lst_login'		=> $_user["lastLogin"],
	'usr_memory'		=> $user_lesson_info[0][6]
	);

	$user_documents = getUserDocuments($param, "html");

	// END - Get user documents

	//BEGIN - Get user agenda
	$param = array(	'uid'	=> $uid,
	'max_repeat_val' 	=> $user_lesson_info[0][0], //max repeat val (num of lessons)
	'lesson_titles'	=> $user_lesson_info[0][1],
	'lesson_code'	=> $user_lesson_info[0][2],
	'lesson_professor'	=> $user_lesson_info[0][3],
	'lesson_statut'		=> $user_lesson_info[0][4],
	'usr_lst_login'		=> $_user["lastLogin"]
	);
	$user_agenda = getUserAgenda($param, "html");

	//END - Get user agenda

	//BEGIN - Get user forum posts
	$param = array(	'uid'	=> $uid,
	'max_repeat_val' 	=> $user_lesson_info[0][0], //max repeat val (num of lessons)
	'lesson_titles'	=> $user_lesson_info[0][1],
	'lesson_code'	=> $user_lesson_info[0][2],
	'lesson_professor'	=> $user_lesson_info[0][3],
	'lesson_statut'		=> $user_lesson_info[0][4],
	'usr_lst_login'		=> $_user["lastLogin"],
	'usr_memory'		=> $user_lesson_info[0][7]//forum memory
	);
	$user_forumPosts = getUserForumPosts($param, "html");
	//END - Get user forum posts

} else {
	//show a "-" in all blocks if the user is not enrolled to any lessons
	// (except of the lessons block which is handled before)
	$user_assignments = "<p>-</p>";
	$user_announcements = "<p>-</p>";
	$user_documents = "<p>-</p>";
	$user_agenda = "<p>-</p>";
	$user_forumPosts = "<p>-</p>";
}

// ==  BEGIN create array with personalised content
$perso_tool_content = array(
'lessons_content' 	=> $user_lesson_info[1],
'assigns_content' 	=> $user_assignments,
'announce_content' 	=> $user_announcements,
'docs_content'		=> $user_documents,
'agenda_content' 	=> $user_agenda,
'forum_content' 	=> $user_forumPosts
);

// == END create array with personalised content


/*
 * Function autoCloseTags
 *
 * It is used by the announcements and agenda personalised components. These
 * tools offer the ability to the professor to add content by using a WYSIWYG editor.
 * Thus, the professor can add several HTML tags to the content.
 *
 * The personalised logic limits this content to an X number of characters. This can
 * cause several tags to be in a not-closed state.
 *
 * This function makes sure ALL tags are closed so that no errors are presented to
 * the personalised interface
 *
 * @param string $string HTML code parsed by the personalised components
 * @return string HTML code with all html tag elements closed properly
 */
function autoCloseTags($string) {

	$donotclose=array('br','img','input'); //Tags that are not to be closed

	//prepare vars and arrays
	$tagstoclose='';
	$tags=array();

	//put all opened tags into an array
	preg_match_all("/<(([A-Z]|[a-z]).*)(( )|(>))/isU",$string,$result);

	$openedtags=$result[1];

	//this is just done so that the order of the closed tags in the end will be better
	$openedtags=array_reverse($openedtags);

	//put all closed tags into an array
	preg_match_all("/<\/(([A-Z]|[a-z]).*)(( )|(>))/isU",$string,$result2);
	$closedtags=$result2[1];

	//look up which tags still have to be closed and put them in an array
	for ($i=0;$i<count($openedtags);$i++) {
		if (in_array($openedtags[$i],$closedtags)) { unset($closedtags[array_search($openedtags[$i],$closedtags)]); }
		else array_push($tags, $openedtags[$i]);
	}
	//prepare the close-tags for output
	for($x=0;$x<count($tags);$x++) {
		$add=strtolower(trim($tags[$x]));

		if(!in_array($add,$donotclose)) $tagstoclose.='</'.$add.'>';
	}
	return $tagstoclose;
}
