<?php
//perso.php

//Check for lessons that the user is a professor
$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
        	FROM cours, cours_user WHERE cours.code=cours_user.code_cours 
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

//include  block files (announcemets.php etc.) from /modules/perso
include("./modules/perso/lessons.php");
include("./modules/perso/assignments.php");
include("./modules/perso/announcements.php");
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
	$_user["persoLastLogin"] = date('Y-m-d');
	$_user["lastLogin"] = eregi_replace("-", " ", substr($_user["persoLastLogin"],0,10));
}

//dumpArray($_user);
//	END Get user's last login date]================================================

//	BEGIN user's status query]=====================================================

$user_status_query = db_query("SELECT statut FROM user WHERE user_id = '$uid'", $mysqlMainDb);
if ($row = mysql_fetch_row($user_status_query)) {
	$statut = $row[0];
}
//dumpArray($statut);
//	END user's status query]=======================================================


$user_lesson_info = getUserLessonInfo($uid, "html");


// BEGIN - Get user assignments
$param = array(	'uid'	=> $uid,
'max_repeat_val' 	=> $user_lesson_info[0][0], //max repeat val (num of lessons)
'lesson_titles'	=> $user_lesson_info[0][1],
'lesson_code'	=> $user_lesson_info[0][2],
'lesson_professor'	=> $user_lesson_info[0][3],
'lesson_statut'		=> $user_lesson_info[0][4]

);
//echo $user_lesson_info[0][0];
$user_assignments = getUserAssignments($param, "html");

//echo $user_assignments;
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
//dumpArray($user_lesson_info[0][5]);
$user_announcements = getUserAnnouncements($param, "html");

// END - Get user announcements


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
'usr_memory'		=> $user_lesson_info[0][7]//forums memory
);
$user_forumPosts = getUserForumPosts($param, "html");

//END - Get user forum posts

//$lesson_content = $user_lesson_info[1];
// ==  BEGIN create array with personalised content
$tool_content = array(
						'lessons_content' 	=> $user_lesson_info[1],
						'assigns_content' 	=> $user_assignments,
						'announce_content' 	=> $user_announcements
);
// == END create array with personalised content
//dumpArray($user_lesson_info);


?>