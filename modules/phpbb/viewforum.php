<?php
/*=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
A full copyright notice can be read in "/info/copyright.txt".

Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
Yannis Exidaridis <jexi@noc.uoa.gr>
Alexandros Diamantidis <adia@noc.uoa.gr>

For a full list of contributors, see "credits.txt".

This program is a free software under the terms of the GNU
(General Public License) as published by the Free Software
Foundation. See the GNU License for more details.
The full license can be read in "license.txt".

Contact address: GUnet Asynchronous Teleteaching Group,
Network Operations Center, University of Athens,
Panepistimiopolis Ilissia, 15784, Athens, Greece
eMail: eclassadmin@gunet.gr
==============================================================================*/

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
$require_help = FALSE;
include '../../include/baseTheme.php';
$navigation[]= array ("url"=>"index.php", "name"=> $l_forums);

$tool_content = "";

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
if ( $is_adminOfCourse || $is_admin ) {
	$tool_content .= "<a href=\"../forum_admin/forum_admin.php\">$l_adminpanel</a></P>&nbsp;";
}

$tool_content .= "<P><a href=\"newtopic.php?forum=$forum\">$langNewTopic</a></P>";

/*
* Retrieve and present data from course's forum
*/

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

$tool_content .= <<<cData
	<TABLE WIDTH="99%">
	<thead><TR><th></th><th>&nbsp;$l_topic</th><th>$l_replies</th>
	<th>$l_poster</th><th>$langSeen</th>
	<th>$langLastMsg</th></TR></thead>
	<tbody>
cData;

if ( isset($start) ) {
	if (!$start) {
		$start = 0;
	}
} else {
	$start = 0;
}

if (!isset($topics_per_page)) {
	$topics_per_page = 50;
}

$sql = "SELECT t.*, u.username, u2.username as last_poster, p.post_time FROM topics t
        LEFT JOIN users u ON t.topic_poster = u.user_id 
        LEFT JOIN posts p ON t.topic_last_post_id = p.post_id
        LEFT JOIN users u2 ON p.poster_id = u2.user_id
        WHERE t.forum_id = '$forum' 
        ORDER BY topic_time DESC LIMIT $start, $topics_per_page";

if(!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= <<<cData

</TABLE>
cData;
	$tool_content .= $langErrorTopicsQueryDatabase;
	draw($tool_content, 2);
	exit();
}

$topics_start = $start;

if ($myrow = mysql_fetch_array($result)) {
	do {
		$tool_content .= "<TR>\n";
		$replys = $myrow["topic_replies"];
		$last_post = $myrow["post_time"];
		$last_post_datetime = $myrow["post_time"];

		list($last_post_date, $last_post_time) = split(" ", $last_post_datetime);
		list($year, $month, $day) = explode("-", $last_post_date);
		list($hour, $min) = explode(":", $last_post_time);
		$last_post_time = mktime($hour, $min, 0, $month, $day, $year);
		if ( !isset($last_visit) ) {
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
		$tool_content .= "<TD><IMG SRC=\"$image\"></TD>\n";
		$topic_title = own_stripslashes($myrow["topic_title"]);
		$pagination = '';
		$start = '';
		$topiclink = "viewtopic.php?topic=" . $myrow["topic_id"] . "&forum=$forum";
		if($replys+1 > $posts_per_page) {
			$pagination .= '&nbsp;&nbsp;&nbsp;(<img src=\"' . $posticon . '\">' . $l_gotopage;
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
					$pagination .= ", ... ";
					$skippages = 1;
				}
				if ($skippages != 1 || $lastpage) {
					if ($x != 0) {
						$pagination .= ", ";
						$pagination .= "<a href=\"$topiclink$start\">$pagenr</a>";
					}
					$pagenr++;
				}
				$pagination .= ")";
			}
		}
		$topiclink .= "&$replys";
		$tool_content .= "<TD><a href=\"$topiclink\">$topic_title</a>$pagination</TD>\n";
		$tool_content .= "<TD>$replys</TD>\n";
		$tool_content .= "<TD>" . $myrow["prenom"] . " " . $myrow["nom"] . "</TD>\n";
		$tool_content .= "<TD>" . $myrow["topic_views"] . "</TD>\n";
		$tool_content .= "<TD>$last_post</TD></TR>\n";
	} while($myrow = mysql_fetch_array($result));
} else {
	$tool_content .= "<td colspan=6>$l_notopics</td></tr>\n";
}
$tool_content .= "</tbody></table>";
draw($tool_content, 2);
?>
