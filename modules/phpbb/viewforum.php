<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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


$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
include '../../include/baseTheme.php';
if (!add_units_navigation(TRUE)) {
	$navigation[]= array ("url"=>"index.php", "name"=> $langForums);
}

$tool_content = "";
$paging = true;
$next = 0;

include_once("./config.php");
include("functions.php"); 

$forum = intval($_GET['forum']);

$tool_content .= "
    <div id=\"operations_container\">
      <ul id=\"opslist\">";

if ($is_adminOfCourse || $is_admin) {
	$tool_content .= "
        <li><a href='../forum_admin/forum_admin.php'>$langAdm</a></li>";
}
$tool_content .= "
        <li><a href='newtopic.php?forum=$forum'>$langNewTopic</a></li>
      </ul>
    </div>\n";

/*
* Retrieve and present data from course's forum
*/

$sql = "SELECT f.forum_type, f.forum_name
	FROM forums f
	WHERE forum_id = '$forum'";

$result = db_query($sql, $currentCourseID);
$myrow = mysql_fetch_array($result);
 
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

if(isset($_GET['topicnotify'])) { // modify topic notification
	if (isset($_GET['topic_id'])) {
		$topic_id = intval($_GET['topic_id']);
	}
	$rows = mysql_num_rows(db_query("SELECT * FROM forum_notify 
		WHERE user_id = $uid AND topic_id = $topic_id AND course_id = $cours_id", $mysqlMainDb));
	if ($rows > 0) {
		db_query("UPDATE forum_notify SET notify_sent = '$_GET[topicnotify]' 
			WHERE user_id = $uid AND topic_id = $topic_id AND course_id = $cours_id", $mysqlMainDb);
	} else {
		db_query("INSERT INTO forum_notify SET user_id = $uid,
		topic_id = $topic_id, notify_sent = 1, course_id = $cours_id", $mysqlMainDb);
	}
}

$sql = "SELECT t.*, p.post_time, p.nom AS nom1, p.prenom AS prenom1
        FROM topics t
        LEFT JOIN posts p ON t.topic_last_post_id = p.post_id
        WHERE t.forum_id = '$forum' 
        ORDER BY topic_time DESC LIMIT $first_topic, $topics_per_page";

$result = db_query($sql, $currentCourseID);

if (mysql_num_rows($result) > 0) { // topics found
// header
$tool_content .= "
     <table width='99%' class='tbl_border'>
     <tr>
       <th colspan='2'>&nbsp;$langSubject</th>
       <th width='100' class='center'>$langAnswers</th>
       <th width='100' class='center'>$langSender</th>
       <th width='100' class='center'>$langSeen</th>
       <th width='100' class='center'>$langLastMsg</th>
       <th width='20' class='center'>$langNotifyActions</th>
     </tr>";

        $i=0;
	while($myrow = mysql_fetch_array($result)) {
                if ($i%2==1) {
                   $tool_content .= "\n     <tr class=\"even\">";
                } else {
                   $tool_content .= "\n     <tr class=\"odd\">";
                }
		$replys = $myrow["topic_replies"];
		$last_post = $myrow["post_time"];
		$last_post_datetime = $myrow["post_time"];
		list($last_post_date, $last_post_time) = explode(' ', $last_post_datetime);
		list($year, $month, $day) = explode("-", $last_post_date);
		list($hour, $min) = explode(":", $last_post_time);
		$last_post_time = mktime($hour, $min, 0, $month, $day, $year);
		if (!isset($last_visit)) {
			$last_visit = 0;
		}
		if($replys >= $hot_threshold) {
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
		$start = '';
		$topiclink = "viewtopic.php?topic=" . $myrow["topic_id"] . "&amp;forum=$forum";
		if($replys+1 > $posts_per_page) {
			$pagination .= "\n<strong class='pagination'><span>\n<img src='$posticon_more' />";
			$pagenr = 1;
			$skippages = 0;
			for($x = 0; $x < $replys + 1; $x += $posts_per_page) {
				$lastpage = (($x + $posts_per_page) >= $replys + 1);
				if ($lastpage) {
					$start = "&amp;start=$x";
				} else {
					if ($x != 0) {
						$start = "&amp;start=$x";
					}
				}
				if($pagenr > 3 && $skippages != 1) {
					$pagination .= " ... ";
					$skippages = 1;
				}
				if ($skippages != 1 || $lastpage) {
					if ($x != -1) {
						$pagination .= "<a href=\"$topiclink$start\">$pagenr</a>";
						$pagination .= "<span class='page-sep'>,</span>";
					}
					$pagenr++;
				}
			}
			$pagination .= "&nbsp;</span></strong>";
		}
		$tool_content .= "\n       <td><a href='$topiclink'>$topic_title</a>$pagination</td>";
		$tool_content .= "\n       <td>$replys</td>";
		$tool_content .= "\n       <td class='center'>$myrow[prenom] $myrow[nom]</td>";
		$tool_content .= "\n       <td class='center'>$myrow[topic_views]</td>";
		$tool_content .= "\n       <td class='center'>$myrow[prenom1] $myrow[nom1]<br />$last_post</td>";
		list($topic_action_notify) = mysql_fetch_row(db_query("SELECT notify_sent FROM forum_notify 
			WHERE user_id = $uid AND topic_id = $myrow[topic_id] AND course_id = $cours_id", $mysqlMainDb));
		if (!isset($topic_action_notify)) {
			$topic_link_notify = FALSE;
			$topic_icon = '_off';
		} else {
			$topic_link_notify = toggle_link($topic_action_notify);
			$topic_icon = toggle_icon($topic_action_notify);
		}
		$tool_content .= "\n       <td class='center'>";
		if (isset($_GET['start']) and $_GET['start'] > 0) {
			$tool_content .= "<a href='$_SERVER[PHP_SELF]?forum=$forum&start=$_GET[start]&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow[topic_id]'><img src='../../template/classic/img/announcements$topic_icon.gif' title='$langNotify'></img></a>";
		} else {
			$tool_content .= "<a href='$_SERVER[PHP_SELF]?forum=$forum&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow[topic_id]'><img src='../../template/classic/img/announcements$topic_icon.gif' title='$langNotify'></img></a>";
		}
		$tool_content .= "</td>\n     </tr>";
                $i++;
	} // end of while
$tool_content .= "\n       </table>";
} else {
	$tool_content .= "\n  <p class='alert1'>$langNoTopics</p>\n";
}
$tool_content .= "</tbody></table>";
draw($tool_content, 2);
