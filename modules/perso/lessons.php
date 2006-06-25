<?PHP
//session_start();

/*
*
*	File : lessons.php
*
*	Lessons View
*
*	This class return all the lessons the user is subscribed to
*	along with additional information regarding each lesson's
*	code, name and professor
*
*	@author Evelthon Prodromou <eprodromou@upnet.gr>
*
*	@access public
*
*	@version 1.0.1
*
*/


//if type is 'data' it returns an array with all lesson data
//if type is 'html' it creates the interface html populated with data and
//returnes it to the calling function
function  getUserLessonInfo($uid, $type) {
	//	?$userID=$uid;
	global $mysqlMainDb;
	
//	TODO: add the new fields for memory in the db
	
	$user_courses = "SELECT
								cours.code , cours.fake_code , 
	                                           cours.intitule , cours.titulaires ,
	                                           cours.languageCourse ,
	                                           cours_user.statut,
	                                           user.perso, 
	                                           user.announce_flag,
	                                           user.doc_flag,
	                                           user.forum_flag

	                                   FROM    cours, cours_user, user
	                                  
	                                  WHERE cours.code = cours_user.code_cours
	                                  AND   cours_user.user_id = '".$uid."' 
	                                  AND   user.user_id = '".$uid."'
	                                  ";


	$mysql_query_result = db_query($user_courses, $mysqlMainDb);

	$repeat_val = 0;

	$lesson_titles = array();
//	$userStatus = 0;//flat to check what links should be shown in the personalised interface
	//getting user's lesson titles
	while ($mycourses = mysql_fetch_row($mysql_query_result)) {

		$lesson_titles[$repeat_val] 	= $mycourses[2]; //lesson titles
		$lesson_code[$repeat_val]		= $mycourses[0]; //lesson code used in tables
		$lesson_professor[$repeat_val]	= $mycourses[3]; //lesson professor
		$lesson_statut[$repeat_val]		= $mycourses[5];//statut (user|prof)
		/*$lesson_announce_f[$repeat_val]	= eregi_replace("-", " ", $mycourses[7]);//announcements flag
		$lesson_doc_f[$repeat_val]		= eregi_replace("-", " ", $mycourses[8]);//documents flag
		$lesson_forum_f[$repeat_val]	= eregi_replace("-", " ", $mycourses[9]);//forum flag*/

		$repeat_val++;
	}
	
	$memory = "SELECT
				user.announce_flag, user.doc_flag, user.forum_flag
				FROM user
				WHERE user.user_id = '".$uid."'
				";
	$memory_result = db_query($memory, $mysqlMainDb);
	
	while ($my_memory_result = mysql_fetch_row($memory_result)) {
//		dumpArray($my_memory_result);
		$lesson_announce_f = eregi_replace("-", " ", $my_memory_result[0]);
		$lesson_doc_f = eregi_replace("-", " ", $my_memory_result[1]);
		$lesson_forum_f = eregi_replace("-", " ", $my_memory_result[2]);
	}
		

	
	$max_repeat_val = $repeat_val;

	$ret_val[0] = $max_repeat_val;
	$ret_val[1] = $lesson_titles;
	@$ret_val[2]	= $lesson_code;
	@$ret_val[3] = $lesson_professor;
	@$ret_val[4] = $lesson_statut;
	$ret_val[5] = $lesson_announce_f;
	$ret_val[6] = $lesson_doc_f;
	$ret_val[7] = $lesson_forum_f;
	
//dumpArray($ret_val);
	//check what sort of data should be returned
	if($type == "html") {
		return array($ret_val,htmlInterface($ret_val));
//		return htmlInterface($ret_val);
	} elseif ($type == "data") {
		return $ret_val;
	}

}


function htmlInterface($data) {
	global $statut, $is_admin, $urlServer, $langCourseCreate, $langOtherCourses;
	global $langNotEnrolledToLessons, $langCreateLesson, $langEnroll;
	$lesson_content = "";
	if ($data[0] > 0) {
	$lesson_content .= <<<lCont

      		<div id="datacontainer">

				<ul id="datalist">
lCont;

	for ($i=0; $i<$data[0]; $i++){
/*		if ($data[4][$i] == 1) {
			$prof_css = "class=\"statut\"";
		} else {
			$prof_css = "";
		}*/

		$lesson_content .= "
	<li>
	<a class=\"square_bullet\" href=\"courses/".$data[2][$i]."\">
	
	<div class=\"title_pos\">".$data[2][$i]." - ".$data[1][$i]."</div>
	<div class=\"content_pos\">".$data[3][$i]."</div>
	</a>
	</li>
	";
	}



	$lesson_content .= "
	</ul>
			</div> 
		<br>";
	} else {
		$lesson_content .= "
		<p>$langNotEnrolledToLessons</p>
		";
		if ($statut == 1) {
			$lesson_content .= "
		<p>$langCreateLesson</p>
		";
		}
		$lesson_content .= "
		<p>$langEnroll</p>
		";
	}
	$lesson_content .= "
		<a class=\"enroll_icon\" href=".$urlServer."modules/auth/courses.php>$langOtherCourses</a>
	   		";

if ($statut == 1) {
	$lesson_content .= "
	 | <a class=\"create_lesson\" href=".$urlServer."modules/create_course/create_course.php>$langCourseCreate</a>
	";
}
	
	return $lesson_content;
}

?>