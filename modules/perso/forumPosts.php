<?PHP

/*
*
*	File : forumPosts.php
*
*	Forum Posts
*
*	The component responsible for all posts in lessons the user is
*	subscribed to.
*
*	@author Evelthon Prodromou <eprodromou@upnet.gr>
*
*	@access public
*
*	@version 1.0.1
*
*
*/



function getUserForumPosts($param, $type)
{
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
	/*	$queryParamNew = array(
	'lesson_code'		=> $lesson_code,
	'max_repeat_val'	=> $max_repeat_val,
	'date'				=> $usr_lst_login
	);

	$queryParamMemo = array(
	'lesson_code'		=> $lesson_code,
	'max_repeat_val'	=> $max_repeat_val,
	'date'				=> $usr_memory
	);*/

	//	dumpArray($usr_memory);
	$forum_query_new 	= createForumQueries($usr_lst_login);
	$forum_query_memo 	= createForumQueries($usr_memory);


	$forumPosts = array();
	$getNewPosts = false;
	for ($i=0;$i<$max_repeat_val;$i++) {

		//		array_push($forumPostsData, $lesson_title[$i]);
		//		array_push($forumPostsData, $lesson_code[$i]);

		$mysql_query_result = db_query($forum_query_new, $lesson_code[$i]);

		if ($num_rows = mysql_num_rows($mysql_query_result) > 0) {
			$getNewPosts = true;

			//			$announceLessonData = array();
			$forumData = array();
			$forumSubData = array();
			$forumContent = array();

			array_push($forumData, $lesson_title[$i]);
			array_push($forumData, $lesson_code[$i]);

			//update the corresponding field in cours_user and set
			//the field's value to the last LOGIN date of the user
			//set a flag so that it only updates the date once! :)

			//PROSOXH!! to update na ginetai afou bgei apo to for!!1
			//alliws 8a to kanei se ka8e ma8hma pou exei nees anakoinwseis!! (axreiasto!)
		}

		//		$forum_post_counter = 0;

		while ($myForumPosts = mysql_fetch_row($mysql_query_result)) {
			if ($myForumPosts){
				array_push($forumContent, $myForumPosts);
			}
			/*			$lesson_posts[$i][$forum_post_counter]['forum_name']	= $myForumPosts[1];
			$lesson_posts[$i][$forum_post_counter]['topic_title']	= $myForumPosts[3];
			$lesson_posts[$i][$forum_post_counter]['name']			= $myForumPosts[11];
			$lesson_posts[$i][$forum_post_counter]['surname']		= $myForumPosts[10];
			$lesson_posts[$i][$forum_post_counter]['post_time']		= $myForumPosts[9];

			$post_text = strip_tags($myForumPosts[13]);

			$lesson_posts[$i][$forum_post_counter]['post_text']		= $post_text;

			$lesson_posts[$i][$forum_post_counter]['topic_id']		= $myForumPosts[2];
			$lesson_posts[$i][$forum_post_counter]['forum_id']		= $myForumPosts[0];
			$lesson_posts[$i][$forum_post_counter]['sub_id']		= $myForumPosts[5];

			$forum_post_counter++;*/
		}
		//		dumpArray($forumContent);
		//		$forum_posts_per_lesson[$i] = $forum_post_counter;
		//		$lesson_posts[$i]['lesson_title']						= $lesson_titles[$i];
		//		$lesson_posts[$i]['number_of_posts']					= $forum_posts_per_lesson[$i];
		if ($num_rows > 0) {
//			echo "lala";
			array_push($forumSubData, $forumContent);
			array_push($forumData, $forumSubData);
			array_push($forumPosts, $forumData);
		}

	}
	//fix apo dw k katw (ta ekana c/p apo announcements)
	if ($getNewPosts) {
		//		array_push($announceGroup, $announceSubGroup);
		$sqlNowDate = eregi_replace(" ", "-",$usr_lst_login);
		$sql = "UPDATE `user` SET `forum_flag` = '$sqlNowDate' WHERE `user_id` = $uid ";
		db_query($sql, $mysqlMainDb);
		//		echo $sql;
		//update announcemenets memory
		//call announceHtmlInterface("new")
	} elseif (!$getNewPosts) {
		//if there are no new announcements, get the last announcements the user had
		//so that we always have something to display
		for ($i=0; $i < $max_repeat_val; $i++){
			$mysql_query_result = db_query($forum_query_memo, $lesson_code[$i]);
			if (mysql_num_rows($mysql_query_result) >0) {
				$forumData = array();
				$forumSubData = array();
				$forumContent = array();

				array_push($forumData, $lesson_title[$i]);
				array_push($forumData, $lesson_code[$i]);

				while ($myForumPosts = mysql_fetch_row($mysql_query_result)) {
					array_push($forumContent, $myForumPosts);
				}

				array_push($forumSubData, $forumContent);
				array_push($forumData, $forumSubData);
				array_push($forumPosts, $forumData);
			}
		}

	}

//		dumpArray($forumPosts);
//		print_a($forumPosts);

	if($type == "html") {
		return forumHtmlInterface($forumPosts);
	} elseif ($type == "data") {
		return $forumPosts;
	}

}


function forumHtmlInterface($data) {
	$content= <<<fCont

	 <div id="datacontainer">

				<ul id="datalist">
fCont;
	$numOfLessons = count($data);
	for ($i=0; $i <$numOfLessons; $i++) {
		$content .= "
		<li class=\"category\">".$data[$i][0]."
		</li>";
		$iterator =  count($data[$i][2][0]);
		for ($j=0; $j < $iterator; $j++){
			$url = $_SERVER['PHP_SELF']."?perso=5&c=".$data[$i][1]."&t=".$data[$i][2][0][$j][2]."&f=".$data[$i][2][0][$j][0]."&s=".$data[$i][2][0][$j][4];
			$content .= "
		<li><a class=\"square_bullet\" href=\"$url\"><div class=\"title_pos\">".$data[$i][2][0][$j][3]." (".$data[$i][2][0][$j][5].")</div>
			<div class=\"content_pos\">".$data[$i][2][0][$j][8]."</div>
			<div class=\"content_pos\">Apostoleas: ".$data[$i][2][0][$j][6]." ".$data[$i][2][0][$j][7]."</div>
		</a></li>
		";
		}

		if ($i+1 <$numOfLessons) $content .= "<br>";
	}

	$content .= "</ul>
			</div> 
";


	return $content;
}

function createForumQueries($dateVar){

	$forum_query = 'SELECT	forums.forum_id,
									forums.forum_name,
			
									topics.topic_id,
									topics.topic_title,
									topics.topic_replies,

									posts.post_time,
									posts.nom,
									posts.prenom,

									posts_text.post_text

						FROM    	forums,
									topics,
									posts,
									posts_text
			
						WHERE 	
						CONCAT(topics.topic_title, posts_text.post_text) != \'\'
						
						AND		forums.forum_id = topics.forum_id
						AND		posts.forum_id 	= forums.forum_id
						AND		posts.post_id	= posts_text.post_id	
						AND 	posts.topic_id	= topics.topic_id	

						AND	DATE_FORMAT(posts.post_time, \'%Y %m %d\') >= "'.$dateVar.'"
			
						ORDER BY posts.post_time ';
	//		echo $forum_query . "<br>";


	return $forum_query;
}



?>