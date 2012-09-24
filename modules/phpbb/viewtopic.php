<?php
/* ========================================================================
 * Open eClass 2.6
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
        phpbb/viewtopic.php
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
include_once "config.php";
include "functions.php"; 
require_once '../video/video_functions.php';

load_modal_box();
$head_content .= '
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
 
if (isset($_GET['all'])) {
        $paging = false;
} else {
        $paging = true;
}

if (isset($_GET['forum'])) {
	$forum = intval($_GET['forum']);
}
if (isset($_GET['topic'])) {
	$topic = intval($_GET['topic']);
}
$sql = "SELECT f.forum_type, f.forum_name
	FROM forums f, topics t 
	WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";
	
$result = db_query($sql, $currentCourseID);
	
if (!$myrow = mysql_fetch_array($result)) {
        $tool_content .= "<p class='alert1'>$langErrorTopicSelect</p>";
	draw($tool_content, 2);
	exit();
}
$forum_name = own_stripslashes($myrow["forum_name"]);

if (isset($_GET['delete']) && $is_editor) {
	$post_id = intval($_GET['post_id']);
	$last_post_in_thread = get_last_post($topic, $currentCourseID, "time_fix");
	
	$result = db_query("SELECT post_time FROM posts WHERE post_id = '$post_id'", $currentCourseID);
	$myrow = mysql_fetch_array($result);
	$this_post_time = $myrow["post_time"];
	list($day, $time) = explode(' ', $this_post_time);
		
	db_query("DELETE FROM posts WHERE post_id = $post_id", $currentCourseID);
	db_query("DELETE FROM posts_text WHERE post_id = $post_id", $currentCourseID);
	db_query("UPDATE forums SET forum_posts = forum_posts-1 WHERE forum_id=$forum");
	if ($last_post_in_thread == $this_post_time) {
		$topic_time_fixed = $last_post_in_thread;
		$sql = "UPDATE topics
			SET topic_time = '$topic_time_fixed'
			WHERE topic_id = '$topic'";
		if (!$r = db_query($sql, $currentCourseID)) {
			$tool_content .= $langPostRemoved;
			draw($tool_content, 2, null, $head_content);
			exit();
		}
	}
	$total = get_total_posts($topic, $currentCourseID, "topic");
	if (get_total_posts($topic, $currentCourseID, "topic") == 0) {
		db_query("DELETE FROM topics WHERE topic_id = '$topic'", $currentCourseID);
		db_query("UPDATE forums SET forum_topics = forum_topics-1 WHERE forum_id= $forum");
		header("Location: viewforum.php?course=$code_cours&forum=$forum");
	}
	sync($currentCourseID, $forum, 'forum');
	sync($currentCourseID, $topic, 'topic');	
	$tool_content .= "<p class='success'>$langDeletedMessage</p>";
}

$sql = "SELECT topic_title, topic_status
	FROM topics 
	WHERE topic_id = '$topic'";

$total = get_total_posts($topic, $currentCourseID, "topic");

if ($paging and $total > $posts_per_page) {
	$times = 0;
	for ($x = 0; $x < $total; $x += $posts_per_page) {
	     $times++;
	}
	$pages = $times;
}

$result = db_query($sql, $currentCourseID);
$myrow = mysql_fetch_array($result);
$topic_subject = own_stripslashes($myrow["topic_title"]);
$lock_state = $myrow["topic_status"];

if (!add_units_navigation(TRUE)) {
	$navigation[]= array ("url"=>"index.php?course=$code_cours", "name"=> $langForums);
	$navigation[]= array ("url"=>"viewforum.php?course=$code_cours&amp;forum=$forum", "name"=> $forum_name);
}
$nameTools = $topic_subject;

if (isset($_SESSION['message'])) {
	$tool_content .= $_SESSION['message'];
	unset($_SESSION['message']);
}

$tool_content .= "<div id='operations_container'> 	
	<ul id='opslist'>
	<li><a href='reply.php?course=$code_cours&amp;topic=$topic&amp;forum=$forum'>$langReply";

$tool_content .= "</a></li></ul></div>";
	
if ($paging and $total > $posts_per_page ) {
	$times = 1;
	$tool_content .= "
        <table width='100%' class='tbl'>
	<tr>
          <td width='50%' align='left'>
	  <span class='row'><strong class='pagination'>
	  <span>";

	if (isset($_GET['start'])) {
		$start = intval($_GET['start']);
	} else {
		$start = 0;
	}

	$last_page = $start - $posts_per_page;
	$tool_content .= "$langPages: ";

	for($x = 0; $x < $total; $x += $posts_per_page) {
		if($times != 1) {
			$tool_content .= "\n<span class=\"page-sep\">,</span>";
		}
		if($start && ($start == $x)) {
			$tool_content .= "" .  $times;
		} else if($start == 0 && $x == 0) {
			$tool_content .= "1";
		} else {
			$tool_content .= "\n<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;start=$x\">$times</a>";
		}
		$times++;
	}

	$tool_content .= "</span></strong></span></td>
	<td align=\"right\">
	<span class='pages'>";
	if (isset($start) && $start > 0 ) {
		$tool_content .= "\n<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;start=$last_page\">$langPreviousPage</a>&nbsp;|";
	} else {
		$start = 0;
	}	
	if (($start + $posts_per_page) < $total) {
		$next_page = $start + $posts_per_page;
		$tool_content .= "\n<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;start=$next_page\">$langNextPage</a>&nbsp;|";
	}
	$tool_content .= "&nbsp;<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;all=true\">$langAllOfThem</a></span>
	</td>
	</tr>
	</table>";
} else {
	$tool_content .= "
        <table width=\"100%\" class='tbl'>
	<tr>
	<td WIDTH=\"60%\" align=\"left\">
	<span class='row'><strong class='pagination'>&nbsp;</strong></span></td>
	<td align=\"right\">";
	if ($total > $posts_per_page) {	
		$tool_content .= "<span class='pages'>
		&nbsp;<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;start=0\">$langPages</a>
		</span>";
	}
	$tool_content .= "</td></tr></table>";
}

$tool_content .= "<table width='100%' class='tbl_alt'>
    <tr>
      <th width='220'>$langAuthor</th>
      <th>$langMessage</th>
      <th width='60'>$langActions</th>
    </tr>";

if (isset($_GET['all'])) {
    $sql = "SELECT p.*, pt.post_text FROM posts p, posts_text pt 
		WHERE topic_id = '$topic' 
		AND p.post_id = pt.post_id
		ORDER BY post_id";
} elseif (isset($_GET['start'])) {
	$start = intval($_GET['start']);
	$sql = "SELECT p.*, pt.post_text FROM posts p, posts_text pt 
		WHERE topic_id = '$topic' 
		AND p.post_id = pt.post_id
		ORDER BY post_id LIMIT $start, $posts_per_page";
} else {
	$sql = "SELECT p.*, pt.post_text FROM posts p, posts_text pt
		WHERE topic_id = '$topic'
		AND p.post_id = pt.post_id
		ORDER BY post_id LIMIT $posts_per_page";
}
if (!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= "$langErrorConnectPostDatabase. $sql";
	draw($tool_content, 2);
	exit();
}
$myrow = mysql_fetch_array($result);
$count = 0;
do {
	if ($count%2 == 1) {
		$tool_content .= "\n<tr class='odd'>";
	} else {
		$tool_content .= "\n<tr class='even'>";
	}
	$tool_content .= "\n<td valign='top'>".display_user($myrow['poster_id'])."</td>";
	$message = own_stripslashes($myrow["post_text"]);
	// support for math symbols
	$message = mathfilter($message, 12, "../../courses/mathimg/");
	if ($count == 0) {
		$postTitle = "<b>$langPostTitle: </b>".q($topic_subject);
	} else {
		$postTitle = "";
	}

	$tool_content .= "\n<td>
	  <div>
	    
	    <b>$langSent: </b>" . $myrow["post_time"] . "<br>$postTitle
	  </div>
	  <br />$message<br />
	</td>
	<td width='40' valign='top'>";
	if ($is_editor) { // course admin
		$tool_content .= "<a href=\"editpost.php?course=$code_cours&amp;post_id=".$myrow["post_id"]."&amp;topic=$topic&amp;forum=$forum\">
		<img src='$themeimg/edit.png' title='$langModify' alt='$langModify' /></a>";
		$tool_content .= "&nbsp;&nbsp;<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;post_id=".$myrow["post_id"]."&amp;topic=$topic&amp;forum=$forum&amp;delete=on' onClick='return confirmation()'><img src='$themeimg/delete.png' title='$langDelete' alt='$langDelete' /></a>";
	}
	$tool_content .= "</td>\n</tr>";
	$count++;
} while($myrow = mysql_fetch_array($result));

$sql = "UPDATE topics SET topic_views = topic_views + 1 WHERE topic_id = '$topic'";
db_query($sql, $currentCourseID);

$tool_content .= "\n    </table>";

if ($paging and $total > $posts_per_page) {
	$times = 1;
	$tool_content .= "<table width='100%' class='tbl'>
	<tr>
	<td width='50%'>
	<span class='row'><strong class='pagination'><span>";
	
	$last_page = $start - $posts_per_page;
	$tool_content .= "$langPages: ";

	for($x = 0; $x < $total; $x += $posts_per_page) {
		if($times != 1) {
			$tool_content .= "\n<span class=\"page-sep\">,</span>";
		}
		if($start && ($start == $x)) {
			$tool_content .= "" .  $times;
		} else if($start == 0 && $x == 0) {
			$tool_content .= "1";
		} else {
			$tool_content .= "\n<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;start=$x\">$times</a>";
		}
		$times++;
	}
	$tool_content .= "</span></strong></span></td>
	<td><span class='pages'>";
	if (isset($start) && $start > 0) {
		$tool_content .= "\n<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;start=$last_page\">$langPreviousPage</a>&nbsp;|";
	} else {
		$start = 0;
	}	
	if (($start + $posts_per_page) < $total) {
		$next_page = $start + $posts_per_page;
		$tool_content .= "\n<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;start=$next_page\">$langNextPage</a>&nbsp;|";
	}
	$tool_content .= "&nbsp;<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;all=true\">$langAllOfThem</a>
	</span>
	</td></tr></table>";
} else {
	$tool_content .= "<table width=\"100%\" class=\"tbl\">
	<tr>
	<td width=\"60%\" align=\"left\">
	<span class='row'><strong class='pagination'>&nbsp;</strong>
	</span></td>
	<td align=\"right\">
	<span class='pages'>";
	if ($total > $posts_per_page) {	
		$tool_content .= "&nbsp;<a href=\"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&amp;forum=$forum&amp;start=0\">$langPages</a>";
        } else {
                $tool_content .= '&nbsp;';
        }
	$tool_content .= "</span></td></tr></table>";
}
draw($tool_content, 2, null, $head_content);
