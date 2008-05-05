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
phpbb/index.php
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
$require_login = TRUE;
$require_current_course = TRUE;
$require_help = FALSE;
include '../../include/baseTheme.php';
$nameTools = $l_forums;
$tool_content = "";

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_FORUM');
/**************************************/

/*
* Tool-specific includes
*/
include_once("./config.php");

/******************************************************************************
* Actual code starts here
*****************************************************************************/
/*
* First, some decoration
*/

if ($is_adminOfCourse || $is_admin) {
	$tool_content .= "
    <div id=\"operations_container\">
      <ul id=\"opslist\">
        <li><a href=\"../forum_admin/forum_admin.php\">$l_adminpanel</a></li>
      </ul>
    </div>
    <br />
	";
}
$tool_content .= <<<cData

    <table width="99%" class="ForumSum">
    <thead>
    <tr>
      <td colspan="2" class="forum_category">$l_forums</td>
      <td class="forum_category">$l_topics</td>
      <td class="forum_category">$l_posts</td>
      <td class="forum_category">$l_lastpost</td>
    </tr>
    </thead>
cData;

/*
* Populate data with forum categories
*/
$sql = "SELECT c.* FROM catagories c, forums f
	 WHERE f.cat_id=c.cat_id
	 GROUP BY c.cat_id, c.cat_title, c.cat_order
	 ORDER BY c.cat_id DESC";

if ( !$result = db_query($sql, $currentCourseID)) {
	$tool_content .= "
    </table>";
	$tool_content .= "$langUnableGetCategories<br>$sql";
	draw($tool_content, 2);
	exit();
}

$total_categories = mysql_num_rows($result);
if ( $total_categories ) {
	if ( isset($viewcat) ) {
		if ( !$viewcat ) {
			$viewcat = -1;
		}
	} else {
		$viewcat = -1;
	}
	while ( $cat_row = mysql_fetch_array($result) ) {
		$categories[] = $cat_row;
	}
	$limit_forums = "";
	if ( $viewcat != -1 ) {
		$limit_forums = "WHERE f.cat_id = $viewcat";
	}
	$sql = "SELECT f.*, u.username, u.user_id, p.post_time, p.nom, p.prenom, p.topic_id
		FROM forums f LEFT JOIN posts p ON p.post_id = f.forum_last_post_id
		LEFT JOIN users u ON u.user_id = p.poster_id
		$limit_forums ORDER BY f.cat_id, f.forum_id";
	if ( !$f_res = db_query($sql, $currentCourseID) ) {
		$tool_content .= <<<cData

    </table>
cData;
		$tool_content .= "Error getting forum data.";
		draw($tool_content, 2, 'phpbb');
		exit();
	}
		$tool_content .= "
    <tbody>";
	 
	while ( $forum_data = mysql_fetch_array($f_res) ) {
		$forum_row[] = $forum_data;
	}
	for( $i=0; $i < $total_categories; $i++) {
		if ( $viewcat != -1 ) {
			if ( $categories[$i][cat_id] != $viewcat ) {
				$title = stripslashes($categories[$i][cat_title]);
				$tool_content .= "
    <tr class=\"Forum\">
      <td colspan=\"5\" class=\"left\">&nbsp;$title</td>
    </tr>";
				continue;
			}
		}
		$title = stripslashes($categories[$i]["cat_title"]);
		$catNum = $categories[$i]["cat_id"];
		$tool_content .= "
    <tr>
      <td colspan=\"5\" class=\"Forum\">&nbsp;$title</td>
    </tr>";
		@reset($forum_row);
		for ( $x=0; $x < count($forum_row); $x++) {
			unset($last_post);
			if ( $forum_row[$x]["cat_id"] == $categories[$i]["cat_id"] ) {
				if ( $forum_row[$x]["post_time"] ) {
					$last_post = $forum_row[$x]["post_time"];
					$last_post_datetime = $forum_row[$x]["post_time"];
					list($last_post_date, $last_post_time) = split(" ", $last_post_datetime);
					list($year, $month, $day) = explode("-", $last_post_date);
					list($hour, $min) = explode(":", $last_post_time);
					$last_post_time = mktime($hour, $min, 0, $month, $day, $year);
					$human_last_post_time = date("d/m/Y -  H:i", $last_post_time);
				}
				if (empty($last_post) ) {
					$last_post = $l_noposts;
				}
				$tool_content .= "
    <tr>";
				if ( !isset($last_visit) ) {
					$last_visit = 0;
				}
				if(@$last_post_time > $last_visit && $last_post != $l_noposts) {
					$tool_content .= "
      <td width=\"1\" class=\"left\"><IMG SRC=\"$newposts_image\"></td>";
				} else {
					$tool_content .= "
      <td width=2% class=\"center\"><IMG SRC=\"$folder_image\"></td>";
				}
				$name = stripslashes($forum_row[$x]["forum_name"]);
				$last_post_nom = $forum_row[$x]["nom"];
				$last_post_prenom = $forum_row[$x]["prenom"];
				$last_post_topic_id = $forum_row[$x]["topic_id"];
				$total_posts = $forum_row[$x]["forum_posts"];
				$total_topics = $forum_row[$x]["forum_topics"];
				$desc = stripslashes($forum_row[$x]["forum_desc"]);
				$tool_content .= "
      <td>";
				$forum=$forum_row[$x]["forum_id"];
				if ( $is_adminOfCourse==1 ) { // TUTOR VIEW
					$sqlTutor = db_query("SELECT id FROM student_group
						WHERE forumId='$forum' AND tutor='$uid'", $currentCourseID );
					$countTutor = mysql_num_rows($sqlTutor);
					if ( $countTutor == 0 ) {
						$tool_content .= "<a href=\"viewforum.php?forum=" . $forum_row[$x]["forum_id"] . "&$total_posts\">$name</a>";
					} else {
						$tool_content .= "<a href=\"viewforum.php?forum=" . $forum_row[$x]["forum_id"] . "&$total_posts\">$name</a>&nbsp;($langOneMyGroups)";
					}
				} elseif ( $status[$dbname] == 1 OR $status[$dbname] == 2 ) { // ADMIN VIEW
					$tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a>";
				} elseif ( $catNum == 1 ) { // STUDENT VIEW
					if ( @$forum == @$myGroupForum ) {
						$tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a>&nbsp;&nbsp;($langMyGroup)";
					} else {
						if(@$privProp==1) {
							$tool_content .= "$name";
						} else {
							$tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a>";
						}
					}
				} else { // OTHER FORUMS
					$tool_content .= "<a href=\"viewforum.php?forum=".$forum_row[$x]["forum_id"]."&$total_posts\">$name</a> ";
				}
				$tool_content .= "<br>$desc";
				$tool_content .= "</td>";
				$tool_content .= "
      <td width=\"65\" class=\"Forum_leftside\">$total_topics</td>";
				$tool_content .= "
      <td width=\"65\" class=\"Forum_leftside\">$total_posts</td>";
				$tool_content .= "
      <td width=\"200\" class=\"Forum_post\">";
	  			//if (isset($last_post_prenom) && isset($last_post_nom)) {
				if ($total_topics>0 && $total_posts>0) {
				$tool_content .= "$last_post_prenom $last_post_nom 
             <a set=\"yes\" href=\"viewtopic.php?topic=$last_post_topic_id&forum=$forum\"><IMG border=\"0\" SRC=\"$icon_topic_latest\"></a><br />$human_last_post_time</td>";	  			    
	  			} else {
				$tool_content .= "<font color=\"#CAC3B5\">$l_noposts</font></td>";				
				}

				$tool_content .= "
    </tr>";
			}
		}
	}
}

/*
* Closing decoration and actual drawing
*/
$tool_content .= <<<cData

    </tbody>
    </table>

cData;
draw($tool_content, 2, 'phpbb');
?>
