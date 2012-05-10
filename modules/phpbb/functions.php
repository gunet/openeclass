<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * Returns the total number of topics in a form
 */
function get_total_topics($forum_id) {
	
        global $langError;
        
	$sql = "SELECT COUNT(*) AS total FROM forum_topics
                        WHERE forum_id = $forum_id";
	if(!$result = db_query($sql))
		return($langError);
	if(!$myrow = mysql_fetch_array($result))
		return($langError);
	
	return($myrow["total"]);
}

/*
 * Returns the total number of posts in forum or topic
 */ 
function get_total_posts($id, $type) {
      
    switch($type) {
        case 'forum':
          $sql = "SELECT COUNT(*) AS total FROM forum_posts 
                    WHERE forum_id = $id";
          break;
        case 'topic':
          $sql = "SELECT COUNT(*) AS total FROM forum_posts
                    WHERE topic_id = $id";
          break;
    }
    if(!$result = db_query($sql)) {
        return("error!");
    }
    if(!$myrow = mysql_fetch_array($result)) {
        return("0");
    }
   return($myrow["total"]);
}

/*
 * Returns the most recent post in a forum
 */
function get_last_post($topic_id, $forum_id) {
   
    global $langError, $langNoPosts;
    
     $sql = "SELECT post_time FROM forum_posts
                WHERE topic_id = $topic_id
                AND forum_id = $forum_id
                ORDER BY post_time DESC LIMIT 1";
    
    if(!$result = db_query($sql)) {
        return($langError);
    }
    if(!$myrow = mysql_fetch_array($result)) {
        return($langNoPosts);
    }
    
    $val = $myrow["post_time"];
    return($val);
}


/*
 * Checks if a forum or a topic exists in the database. Used to prevent
 * users from simply editing the URL to post to a non-existant forum or topic
 */
function does_exists($id, $type) {
        
        global $cours_id;
	switch($type) {
		case 'forum':
			$sql = "SELECT id FROM forum
                                WHERE id = $id 
                                AND course_id = $cours_id";
		break;
		case 'topic':
			$sql = "SELECT id FROM forum_topics 
                                WHERE id = $id";
		break;
	}
	if(!$result = db_query($sql))
		return(0);
	if(!$myrow = mysql_fetch_array($result)) 
		return(0);
	return(1);
}

/*
 * Check if this is the first post in a topic. Used in editpost.php
 */

function is_first_post($topic_id, $post_id) {
        
    
    $sql = "SELECT id FROM forum_posts 
                WHERE topic_id = $topic_id
                ORDER BY id LIMIT 1";
    if(!$r = db_query($sql)) {
        return(0);
    }
    if(!$m = mysql_fetch_array($r)) {
        return(0);
    }
    if($m["id"] == $post_id) {
        return(1);
    } else {
        return(0);
    }
}



function sync($id, $type) {
        
   global $cours_id;
   
   switch($type) {
   	case 'forum':
   		$sql = "SELECT MAX(id) AS last_post FROM forum_posts
                            WHERE forum_id = $id";
   		$result = db_query($sql);
   		if($row = mysql_fetch_array($result)) {
   			$last_post = $row["last_post"];
   		}
   		$sql = "SELECT COUNT(id) AS total FROM forum_posts
                                WHERE forum_id = $id";
   		$result = db_query($sql);
   		if($row = mysql_fetch_array($result)) {
   			$total_posts = $row["total"];
   		}
   		$sql = "SELECT COUNT(id) AS total FROM forum_topics
                            WHERE forum_id = $id";
   		$result = db_query($sql);
   		if($row = mysql_fetch_array($result)) {
   			$total_topics = $row["total"];
   		}
   		$sql = "UPDATE forum
			SET num_topics = $total_topics,
                            num_posts = $total_posts,
                            last_post_id = $last_post
                        WHERE id = $id
                        AND course_id = $cours_id";
   		$result = db_query($sql);
   	break;

   	case 'topic':
   		$sql = "SELECT MAX(id) AS last_post FROM forum_posts
                            WHERE topic_id = $id";
		$result = db_query($sql);
   		if($row = mysql_fetch_array($result)) {
   			$last_post = $row["last_post"];
   		}
   		$sql = "SELECT COUNT(id) AS total FROM forum_posts
                            WHERE topic_id = $id";
   		$result = db_query($sql);
   		if($row = mysql_fetch_array($result)) {
   			$total_posts = $row["total"];
   		}
   		$total_posts -= 1;
   		$sql = "UPDATE forum_topics SET num_replies = $total_posts,
                            last_post_id = $last_post
			WHERE forum_id = $id";
   		$result = db_query($sql);
   	break;
   }
   return;
}

// display notification status of link
function toggle_link($notify) {
	
	if ($notify == TRUE) {
		return FALSE;
	} elseif ($notify == FALSE) {
		return TRUE;
	}
}

// display notification status of link and icon
function toggle_icon($notify) {	
	
	if ($notify == TRUE) {
		return '_on';
	} elseif ($notify == FALSE) {
		return '_off';
	}
}

// returns a category id from a forum id
function forum_category($id) {
	
	global $cours_id;
	
	if ($r = mysql_fetch_row(db_query("SELECT cat_id FROM forum
                    WHERE id = $id 
                    AND course_id = $cours_id"))) {
		return $r[0];
	} else {
		return FALSE;
	}
}

// returns a category name from a category id
function category_name($id) {
	
	global $cours_id;
	
	if ($r = mysql_fetch_row(db_query("SELECT cat_title FROM forum_categories
                    WHERE id = $id
                    AND course_id = $cours_id"))) {
		return $r[0];
	} else {
		return FALSE;
	}
}


function init_forum_group_info($forum_id)
{
	global $cours_id, $group_id, $can_post, $is_member, $is_editor;

	$q = db_query("SELECT id FROM `group`
			WHERE course_id = $cours_id AND forum_id = $forum_id");
	if ($q and mysql_num_rows($q) > 0) {
		list($group_id) = mysql_fetch_row($q);
		initialize_group_info($group_id);
	} else {
		$group_id = false;
	}
	if (!$group_id or $is_member or $is_editor) {
		$can_post = true;
	} else {
		$can_post = false;
	}
	return $group_id;
}


function add_topic_link($pagenr, $total_reply_pages) {
        
        global $pagination, $posts_per_page, $topiclink;
        
        $start = $pagenr * $posts_per_page;
        $pagenr++;
        $pagination .= "<a href='$topiclink&amp;start=$start'>$pagenr</a>" .
                       (($pagenr < $total_reply_pages)? "<span class='page-sep'>,&nbsp;</span>": '');
}