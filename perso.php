<?php
/*===========================================================================
*              GUnet eClass 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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

include("redirector.php");
//Check for lessons that the user is a professor
$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s FROM cours, cours_user WHERE cours.code=cours_user.code_cours 
	AND cours_user.user_id='".$uid."' AND cours_user.statut='1'");
if (mysql_num_rows($result2) > 0) {
	$i=0;
	while ($mycours = mysql_fetch_array($result2)) {
		$dbname = $mycours["k"];
		$status[$dbname] = $mycours["s"];
		$i++;
	}       // while
} // if

session_register('status');
// end of check

//include personalised component files (announcemets.php etc.) from /modules/perso
include("./modules/perso/lessons.php");
include("./modules/perso/assignments.php");
include("./modules/perso/announcements.php");
include("./modules/perso/documents.php");
include("./modules/perso/agenda.php");
include("./modules/perso/forumPosts.php");

//	BEGIN Get user's last login date]==============================================

$last_login_query = 	"SELECT  `id_user` ,  `when` ,  `action`
			FROM  $mysqlMainDb.loginout
			WHERE  `action`  =  'LOGIN' AND  `id_user`  = $uid
			ORDER BY  `when`  DESC 
			LIMIT 1,1 ";

$login_date_result 	= db_query($last_login_query, $mysqlMainDb);


if (mysql_num_rows($login_date_result)) {
	$login_date_fetch	= mysql_fetch_row($login_date_result);
	$_user["persoLastLogin"] = substr($login_date_fetch[1],0,10);
	$_user["lastLogin"] = eregi_replace("-", " ", substr($login_date_fetch[1],0,10));
} else {
	//The else serves the exceptional case when the user logs in to
	// the platform for the first time
	$_user["persoLastLogin"] = date('Y-m-d');
	$_user["lastLogin"] = eregi_replace("-", " ", substr($_user["persoLastLogin"],0,10));
}
//	END Get user's last login date]================================================


//	BEGIN user's status query]=====================================================
$user_status_query = db_query("SELECT statut FROM user WHERE user_id = '$uid'", $mysqlMainDb);
if ($row = mysql_fetch_row($user_status_query)) {
	$statut = $row[0];
}
//	END user's status query]=======================================================


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
	'lesson_statut'		=> $user_lesson_info[0][4]

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
	'usr_memory'		=> $user_lesson_info[0][5]
	);

	$user_announcements = getUserAnnouncements($param, "html");
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
$tool_content = array(
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

?>
