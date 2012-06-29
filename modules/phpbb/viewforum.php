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


/*===========================================================================
phpbb/viewforum.php
* @version $Id$
@last update: 2006-07-23 by Artemios G. Voyiatzis
@authors list: Artemios G. Voyiatzis <bogart@upnet.gr>

based on Claroline version 1.7 licensed under GPL
copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

Claroline authors: Piraux Sebastien <pir@cerdecam.be>
Lederer Guillaume <led@cerdecam.be>

based on phpBB version 1.4.1 licensed under GPL
copyright (c) 2001, The phpBB Group
==============================================================================
@Description: This module implements a per course forum for supporting
discussions between teachers and students or group of students.
It is a heavily modified adaptation of phpBB for (initially) Claroline
and (later) eclass. In the future, a new forum should be developed.
Currently we use only a fraction of phpBB tables and functionality
(viewforum, viewtopic, post_reply, newtopic); the time cost is
enormous for both core phpBB code upgrades and migration from an
existing (phpBB-based) to a new eclass forum :-(

@Comments:

@todo:
==============================================================================
*/


$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
include '../../include/baseTheme.php';
include '../group/group_functions.php';

if (!add_units_navigation(TRUE)) {
	$navigation[]= array ("url"=>"index.php?course=$code_cours", "name"=> $langForums);
}

$paging = true;
$next = 0;

include_once("./config.php");
include("functions.php");

$local_head = '
<script type="text/javascript">
function confirmation()
{
    if (confirm("'.$langConfirmDelete.'"))
        {return true;}
    else
        {return false;}
}
</script>
';

$forum_id = intval($_GET['forum']);
$is_member = false;
$group_id = init_forum_group_info($forum_id);
if ($private_forum and !($is_member or $is_editor)) {
	$tool_content .= "<div class='caution'>$langPrivateForum</div>";
	draw($tool_content, 2);
	exit;
}
if (isset($_GET['empty'])) {// if we come from newtopic.php
	$tool_content .= "<p class='alert1'>$langEmptyNewTopic</p>";
}

if ($can_post) {
	$tool_content .= " 	        
	<div id='operations_container'> 	
	<ul id='opslist'>
	<li>
	<a href='newtopic.php?course=$code_cours&amp;forum=$forum_id'>$langNewTopic</a>
	</li>
	</ul> 	         
	</div>"; 	         
}
/*
* Retrieve and present data from course's forum
*/

$sql = "SELECT f.forum_type, f.forum_name
	FROM forums f
	WHERE forum_id = $forum_id";

$result = db_query($sql, $currentCourseID);
$myrow = mysql_fetch_array($result);
 
$forum_name = own_stripslashes($myrow["forum_name"]);
$nameTools = $forum_name;

$topic_count = mysql_fetch_row(db_query("SELECT COUNT(*) FROM topics WHERE forum_id = $forum_id"));
$total_topics = $topic_count[0];

if ($total_topics > $topics_per_page) { 
	$pages = intval($total_topics / $topics_per_page) + 1; // get total number of pages
}

if (isset($_GET['start'])) {
        $first_topic = intval($_GET['start']);
} else {
	$first_topic = 0;
}

if ($total_topics > $topics_per_page) { // navigation
	$base_url = "viewforum.php?course=$code_cours&amp;forum=$forum_id&amp;start="; 
	$tool_content .= "<table width='100%'><tr>";
	$tool_content .= "<td width='50%' align='left'><span class='row'><strong class='pagination'>
		<span class='pagination'>$langPages:&nbsp;";
	$current_page = $first_topic / $topics_per_page + 1; // current page 
	for ($x = 1; $x <= $pages; $x++) { // display navigation numbers
		if ($current_page == $x) {
			$tool_content .= "$x";
		} else { 
			$start = ($x-1)*$topics_per_page;
			$tool_content .= "<a href='$base_url&amp;start=$start'>$x</a>";
		}
	}
	$tool_content .= "</span></strong></span></td>";
	$tool_content .= "<td colspan='4' align='right'>";
	
	$next = $first_topic + $topics_per_page;
	$prev = $first_topic - $topics_per_page;
	if ($prev < 0) {
		$prev = 0;
	}
	
	if ($first_topic == 0) { // beginning
		$tool_content .= "<a href='$base_url$next'>$langNextPage</a>";
	} elseif ($first_topic + $topics_per_page < $total_topics) { 
		$tool_content .= "<a href='$base_url$prev'>$langPreviousPage</a>&nbsp|&nbsp;
		<a href='$base_url$next'>$langNextPage</a>";	
	} elseif ($start - $topics_per_page < $total_topics) { // end
		$tool_content .= "<a href='$base_url$prev'>$langPreviousPage</a>";
	} 
	$tool_content .= "</td></tr></table>";
}

// delete topic
if (($is_editor) and isset($_GET['topicdel'])) {
	if (isset($_GET['topic_id'])) {
		$topic_id = intval($_GET['topic_id']);
	}
	$number_of_posts = get_total_posts($topic_id, $currentCourseID, "topic");
	$sql = db_query("SELECT posts_text.post_id AS post_id FROM posts_text, posts
		WHERE posts.post_id = posts_text.post_id
		AND posts.topic_id = $topic_id", $currentCourseID);
	while ($r = mysql_fetch_array($sql)) {
		db_query("DELETE FROM posts_text WHERE post_id = $r[post_id]");
	}
	db_query("DELETE FROM posts WHERE topic_id = $topic_id", $currentCourseID);
	db_query("DELETE FROM topics WHERE topic_id = $topic_id", $currentCourseID);
	db_query("UPDATE forums SET forum_topics = forum_topics-1
		 WHERE forum_id = $forum_id", $currentCourseID);
	db_query("UPDATE forums SET forum_posts = forum_posts-$number_of_posts
		 WHERE forum_id = $forum_id", $currentCourseID);
}


// modify topic notification
if(isset($_GET['topicnotify'])) { 
	if (isset($_GET['topic_id'])) {
		$topic_id = intval($_GET['topic_id']);
	}
	$rows = mysql_num_rows(db_query("SELECT * FROM forum_notify 
		WHERE user_id = $uid AND topic_id = $topic_id AND course_id = $cours_id", $mysqlMainDb));
	if ($rows > 0) {
		db_query("UPDATE forum_notify SET notify_sent = " . intval($_GET['topicnotify']) . " 
			WHERE user_id = $uid AND topic_id = $topic_id AND course_id = $cours_id", $mysqlMainDb);
	} else {
		db_query("INSERT INTO forum_notify SET user_id = $uid,
		topic_id = $topic_id, notify_sent = 1, course_id = $cours_id", $mysqlMainDb);
	}
}

$sql = "SELECT t.*, p.post_time, p.nom AS nom1, p.prenom AS prenom1
        FROM topics t
        LEFT JOIN posts p ON t.topic_last_post_id = p.post_id
        WHERE t.forum_id = $forum_id 
        ORDER BY topic_time DESC LIMIT $first_topic, $topics_per_page";

$result = db_query($sql, $currentCourseID);



if (mysql_num_rows($result) > 0) { // topics found
	// header
	$tool_content .= "
	<table width='100%' class='tbl_alt'>
	<tr>
	  <th colspan='2'>&nbsp;$langSubject</th>
	  <th width='70' class='center'>$langAnswers</th>
	  <th width='150' class='center'>$langSender</th>
	  <th width='80' class='center'>$langSeen</th>
	  <th width='200' class='center'>$langLastMsg</th>
	  <th width='70' class='center'>$langActions</th>
	</tr>";
        $i=0;
	while($myrow = mysql_fetch_array($result)) {
                if ($i%2==1) {
                   $tool_content .= "\n     <tr class=\"odd\">";
                } else {
                   $tool_content .= "\n     <tr class=\"even\">";
                }
		$replies = 1 + $myrow['topic_replies'];
                $last_post = $myrow['post_time'];
                $topic_id = $myrow['topic_id'];
		$last_post_datetime = $myrow["post_time"];
		list($last_post_date, $last_post_time) = explode(' ', $last_post_datetime);
		list($year, $month, $day) = explode("-", $last_post_date);
		list($hour, $min) = explode(":", $last_post_time);
		$last_post_time = mktime($hour, $min, 0, $month, $day, $year);
		if (!isset($last_visit)) {
			$last_visit = 0;
		}
		if ($replies >= $hot_threshold) {
			if ($last_post_time < $last_visit)
				$image = $hot_folder_image;
			else
				$image = $hot_newposts_image;
		} else {
			if ($last_post_time < $last_visit) {
				$image = $folder_image;
			} else {
				$image = $newposts_image;
			}
			if ($myrow["topic_status"] == 1) {
				$image = $locked_image;
			}
		}
		$tool_content .= "\n       <td width='1'><img src='$image' /></td>";
		$topic_title = own_stripslashes($myrow["topic_title"]);
                $pagination = '';
                $topiclink = "viewtopic.php?course=$code_cours&amp;topic=$topic_id&amp;forum=$forum_id";
		if ($replies > $posts_per_page) {
                        $total_reply_pages = ceil($replies / $posts_per_page);
			$pagination .= "\n<strong class='pagination'><span>\n<img src='$posticon_more' />";
                        add_topic_link(0, $total_reply_pages);
                        if ($total_reply_pages > PAGINATION_CONTEXT + 1) {
                                $pagination .= "&nbsp;...&nbsp;";
                        }
			for ($p = max(1, $total_reply_pages - PAGINATION_CONTEXT); $p < $total_reply_pages; $p++) {
                                add_topic_link($p, $total_reply_pages);
			}
			$pagination .= "&nbsp;</span></strong>";
		}
		$tool_content .= "\n<td><a href='$topiclink'><b>$topic_title</b></a>$pagination</td>";
		$tool_content .= "\n<td class='center'>$replies</td>";
		$tool_content .= "\n<td class='center'>". q($myrow['prenom']) ." ". q($myrow['nom']) ."</td>";
		$tool_content .= "\n<td class='center'>$myrow[topic_views]</td>";
		$tool_content .= "\n<td class='center'>". q($myrow['prenom1']) ." ". q($myrow['nom1']) ."<br />$last_post</td>";
		list($topic_action_notify) = mysql_fetch_row(db_query("SELECT notify_sent FROM forum_notify 
			WHERE user_id = $uid AND topic_id = $myrow[topic_id] AND course_id = $cours_id", $mysqlMainDb));
		if (!isset($topic_action_notify)) {
			$topic_link_notify = FALSE;
			$topic_icon = '_off';
		} else {
			$topic_link_notify = toggle_link($topic_action_notify);
			$topic_icon = toggle_icon($topic_action_notify);
		}
		$tool_content .= "\n<td class='center'>";
		if ($is_editor) {
			$tool_content .= "
			<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;forum=$forum_id&amp;topic_id=$myrow[topic_id]&amp;topicdel=yes' onClick='return confirmation()'>
			<img src='$themeimg/delete.png' title='$langDelete' alt='$langDelete' />
			</a>";
		}
		if (isset($_GET['start']) and $_GET['start'] > 0) {
			$tool_content .= "
			<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;forum=$forum_id&amp;start=$_GET[start]&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow[topic_id]'>
			<img src='$themeimg/email$topic_icon.png' title='$langNotify' />
			</a>";
		} else {
			$tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;forum=$forum_id&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow[topic_id]'>
			<img src='$themeimg/email$topic_icon.png' title='$langNotify' />
			</a>";
		}
		$tool_content .= "</td>\n</tr>";
                $i++;
	} // end of while
	$tool_content .= "</table>";
} else {
	$tool_content .= "<div class='alert1'>$langNoTopics</div>";
}
draw($tool_content, 2, null, $local_head);


function add_topic_link($pagenr, $total_reply_pages) {
        global $pagination, $posts_per_page, $topiclink;
        $start = $pagenr * $posts_per_page;
        $pagenr++;
        $pagination .= "<a href='$topiclink&amp;start=$start'>$pagenr</a>" .
                       (($pagenr < $total_reply_pages)? "<span class='page-sep'>,&nbsp;</span>": '');
}
