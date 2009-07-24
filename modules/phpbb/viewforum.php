<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

/*
* GUNET eclass 2.0 standard stuff
*/
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
include '../../include/baseTheme.php';
if (!add_units_navigation(TRUE)) {
	$navigation[]= array ("url"=>"index.php", "name"=> $l_forums);
}

$tool_content = "";
$paging = true;
$next = 0;
/*
* Tool-specific includes
*/
include_once("./config.php");
include("functions.php"); // application logic for phpBB

/******************************************************************************
* Actual code starts here
*****************************************************************************/

/*
* First, some decoration
*/

	$tool_content .= "
    <div id=\"operations_container\">
      <ul id=\"opslist\">";
if ( $is_adminOfCourse || $is_admin ) {
	$tool_content .= "
        <li><a href=\"../forum_admin/forum_admin.php\"><a href=\"../forum_admin/forum_admin.php\">$l_adminpanel</a></li>";
}
	$tool_content .= "
        <li><a href=\"newtopic.php?forum=$forum\">$langNewTopic</a></li>
      </ul>
    </div>
    <br />
	";

/*
* Retrieve and present data from course's forum
*/

$forum = intval($_GET['forum']);

$sql = "SELECT f.forum_type, f.forum_name
	FROM forums f
	WHERE forum_id = '$forum'";
if (!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= $langErrorConnectForumDatabase;
	draw($tool_content, 2);
	exit();
}
if (!$myrow = mysql_fetch_array($result)) {
	$tool_content .= $langErrorForumSelect;
	draw($tool_content, 2);
	exit();
}
$forum_name = own_stripslashes($myrow["forum_name"]);
$nameTools = $forum_name;

$topic_count = mysql_fetch_row(db_query("SELECT COUNT(*) FROM topics WHERE forum_id = '$forum'"));
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
	$base_url = "viewforum.php?forum=$forum&amp;start="; 
	$tool_content .= "<table width='99%'><tr>";
	$tool_content .= "<td width='50%' align='left'><span class='row'><strong class='pagination'>
		<span class='pagination'>$langPages:&nbsp;";
	$current_page = $first_topic / $topics_per_page + 1; // current page 
	for ($x = 1; $x <= $pages; $x++) { // display navigation numbers
		if ($current_page == $x) {
			$tool_content .= "$x";
		} else { 
			$start = ($x-1)*$topics_per_page;
			$tool_content .= "<a href='$base_url$start'>$x</a>";	
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
		$tool_content .= "<a href='$base_url$next'>$l_nextpage</a>";
	} elseif ($first_topic + $topics_per_page < $total_topics) { 
		$tool_content .= "<a href='$base_url$prev'>$l_prevpage</a>&nbsp|&nbsp;
		<a href='$base_url$next'>$l_nextpage</a>";	
	} elseif ($start - $topics_per_page < $total_topics) { // end
		$tool_content .= "<a href='$base_url$prev'>$l_prevpage</a>";
	} 
	$tool_content .= "</td></tr></table>";
}

$tool_content .= <<<cData

    <table width="99%" class="ForumSum">
    <thead>
    <tr>
      <td class="ForumHead" colspan="2">&nbsp;$l_topic</td>
      <td class="ForumHead" width="100">$l_replies</td>
      <td class="ForumHead" width="100">$l_poster</th>
      <td class="ForumHead" width="100">$langSeen</td>
      <td class="ForumHead" width="100">$langLastMsg</td>
    </tr>
    </thead>
    <tbody>
cData;


$sql = "SELECT t.*, u.username, u2.username as last_poster, p.post_time FROM topics t
        LEFT JOIN users u ON t.topic_poster = u.user_id 
        LEFT JOIN posts p ON t.topic_last_post_id = p.post_id
        LEFT JOIN users u2 ON p.poster_id = u2.user_id
        WHERE t.forum_id = '$forum' 
        ORDER BY topic_time DESC LIMIT $first_topic, $topics_per_page";

if(!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= "</table>";
	$tool_content .= $langErrorTopicsQueryDatabase;
	draw($tool_content, 2, 'phpbb');
	exit();
}


if (mysql_num_rows($result) > 0) { // topics found
	while($myrow = mysql_fetch_array($result)) {
		$tool_content .= "<tr>";
		$replys = $myrow["topic_replies"];
		$last_post = $myrow["post_time"];
		$last_post_datetime = $myrow["post_time"];

		list($last_post_date, $last_post_time) = split(" ", $last_post_datetime);
		list($year, $month, $day) = explode("-", $last_post_date);
		list($hour, $min) = explode(":", $last_post_time);
		$last_post_time = mktime($hour, $min, 0, $month, $day, $year);
		if (!isset($last_visit)) {
			$last_visit = 0;
		}
		if($replys >= $hot_threshold) {
			if ($last_post_time < $last_visit )
				$image = $hot_folder_image;
			else
				$image = $hot_newposts_image;
		} else {
			if ( $last_post_time < $last_visit ) {
				$image = $folder_image;
			} else {
				$image = $newposts_image;
			}
			if ($myrow["topic_status"] == 1) {
				$image = $locked_image;
			}
		}
		$tool_content .= "<td width=\"1\"><img src=\"$image\"></td>";
		$topic_title = own_stripslashes($myrow["topic_title"]);
		$pagination = '';
		$start = '';
		$topiclink = "viewtopic.php?topic=" . $myrow["topic_id"] . "&forum=$forum";
		if($replys+1 > $posts_per_page) {
			$pagination .= "\n          <strong class=\"pagination\"><span>\n
			            <img src=".$posticon_more.">";
			$pagenr = 1;
			$skippages = 0;
			for($x = 0; $x < $replys + 1; $x += $posts_per_page) {
				$lastpage = (($x + $posts_per_page) >= $replys + 1);
				if ($lastpage) {
					$start = "&start=$x&$replys";
				} else {
					if ($x != 0) {
						$start = "&start=$x";
					}
					$start .= "&" . ($x + $posts_per_page - 1);
				}
				if($pagenr > 3 && $skippages != 1) {
					$pagination .= " ... ";
					$skippages = 1;
				}
				if ($skippages != 1 || $lastpage) {
					if ($x != -1) {
						$pagination .= "<a href=\"$topiclink$start\">$pagenr</a>";
						$pagination .= "<span class=\"page-sep\">,</span>";
					}
					$pagenr++;
				}
			}
			$pagination .= "&nbsp;\n          </span></strong>\n      ";
		}
		$topiclink .= "&$replys";
		$tool_content .= "\n<TD><a href=\"$topiclink\">$topic_title</a>$pagination</TD>\n";
		$tool_content .= "<TD class=\"Forum_leftside\">$replys</TD>\n";
		$tool_content .= "<TD class='Forum_leftside1'>" . $myrow["prenom"] . " " . $myrow["nom"] . "</TD>\n";
		$tool_content .= "<TD class=\"Forum_leftside\">" . $myrow["topic_views"] . "</TD>\n";
		$tool_content .= "<TD class=\"Forum_leftside1\">$last_post</TD></TR>\n";
	} // end of while
} else {
	$tool_content .= "\n      <td colspan=6>$l_notopics</td></tr>\n";
}
$tool_content .= "</tbody></table>";

draw($tool_content, 2, 'phpbb');