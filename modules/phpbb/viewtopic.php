<?php
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
$tool_content = "";

include_once("./config.php");
include("functions.php"); // application logic for phpBB

// support for math symbols
include('../../include/phpmathpublisher/mathpublisher.php');

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
 
if (isset($_GET['all'])) {
        $paging = false;
} else {
        $paging = true;
}

$sql = "SELECT f.forum_type, f.forum_name
	FROM forums f, topics t 
	WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";
if (!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= $langErrorConnectForumDatabase;
	draw($tool_content, 2);
	exit();
}
if (!$myrow = mysql_fetch_array($result)) {
	$tool_content .= $langErrorTopicSelect;
	draw($tool_content, 2);
	exit();
}
$forum_name = own_stripslashes($myrow["forum_name"]);

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
	$navigation[]= array ("url"=>"index.php", "name"=> $langForums);
	$navigation[]= array ("url"=>"viewforum.php?forum=$forum", "name"=> $forum_name);
}
$nameTools = $topic_subject;

	$tool_content .= "<div id='operations_container'>
	<ul id='opslist'>
	<li><a href='newtopic.php?forum=$forum'>$langNewTopic</a></li>
	<li>";
	if($lock_state != 1) {
		$tool_content .= "<a href='reply.php?topic=$topic&amp;forum=$forum'>$langAnswer</a>";
	} else {
		$tool_content .= "<img src='$reply_locked_image' alt='' />";
	}				
	$tool_content .= "</li></ul></div>";

if ($paging and $total > $posts_per_page ) {
	$times = 1;
	$tool_content .= "<table WIDTH='99%'><thead>
	<tr><td WIDTH='50%' align='left'>
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
			$tool_content .= "\n<a href=\"$_SERVER[PHP_SELF]?mode=viewtopic&amp;topic=$topic&amp;forum=$forum&amp;start=$x\">$times</a>";
		}
		$times++;
	}

	$tool_content .= "</span></strong></span></td>
	<td align=\"right\">
	<span class='pages'>$langGoToPage: &nbsp;&nbsp;";
	if ( isset($start) && $start > 0 ) {
		$tool_content .= "\n       <a href=\"$_SERVER[PHP_SELF]?topic=$topic&amp;forum=$forum&amp;start=$last_page\">$langPreviousPage</a>&nbsp;|";
	} else {
		$start = 0;
	}	
	if (($start + $posts_per_page) < $total) {
		$next_page = $start + $posts_per_page;
		$tool_content .= "\n       <a href=\"$_SERVER[PHP_SELF]?topic=$topic&amp;forum=$forum&amp;start=$next_page\">$langNextPage</a>&nbsp;|";
	}
	$tool_content .= "&nbsp;<a href=\"$_SERVER[PHP_SELF]?topic=$topic&amp;forum=$forum&amp;all=true\">$langAllOfThem</a></span>
	</td>
	</tr>
	</thead>
	</table>";
} else {
	$tool_content .= "<table WIDTH=\"99%\"><thead>
	<tr>
	<td WIDTH=\"60%\" align=\"left\">
	<span class='row'><strong class='pagination'>&nbsp;</strong></span></td>
	<td align=\"right\">";
	if ($total > $posts_per_page) {	
		$tool_content .= "<span class='pages'>
		&nbsp;<a href=\"$_SERVER[PHP_SELF]?topic=$topic&amp;forum=$forum&amp;start=0\">$langPages</a>
		</span>";
	}
	$tool_content .= "</td></tr></thead></table>";
}

$tool_content .= <<<cData

    <table WIDTH="99%" class="ForumSum">
    <thead>
    <tr>
      <td class="ForumHead" width="150">$langAuthor</td>
      <td class="ForumHead" colspan="2">$langMessage</td>
    </tr>
    </thead>
    <tbody>
cData;

$topic = intval($_GET['topic']);
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
	draw($tool_content, 2, 'phpbb');
	exit();
}
$myrow = mysql_fetch_array($result);
$count = 0;
do {
	if(!($count % 2))
		$row_color = 'topic_row1';
	else 
		$row_color = 'topic_row2';
	$tool_content .= "<tr>";
	$tool_content .= "<td class=\"$row_color\"><b>" . $myrow["prenom"] . " " . $myrow["nom"] . "</b></td>";
	$message = own_stripslashes($myrow["post_text"]);
	// support for math symbols
	$message = mathfilter($message, 12, "../../courses/mathimg/");

	if ($count == 0) {
		$postTitle = "$langPostTitle: <b>$topic_subject</b>";
	} else {
		$postTitle = "";
	}

	$tool_content .= "<td class=\"$row_color\">
	<div class='post_massage'>
	<img src='$posticon' alt='' />
	<em>$langSent: " . $myrow["post_time"] . "</em>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$postTitle
	</div>
	<br />$message<br /><br />
	</td>
	<td class='$row_color' width='40'><div align='right'>";
	if ($is_adminOfCourse) { // course admin
		$tool_content .= "<a href=\"editpost.php?post_id=".$myrow["post_id"]."&amp;topic=$topic&amp;forum=$forum\"><img src='../../template/classic/img/edit.gif' title='$langModify' alt='$langModify' /></a>";
		$tool_content .= "&nbsp;<a href='editpost.php?post_id=".$myrow["post_id"]."&amp;topic=$topic&amp;forum=$forum&amp;delete=on&amp;submit=yes' onClick='return confirmation()'><img src='../../template/classic/img/delete.gif' title='$langDelete' alt='$langDelete' /></a>";
	}
	$tool_content .= "</div></td></tr>";
	$count++;
} while($myrow = mysql_fetch_array($result));

$sql = "UPDATE topics SET topic_views = topic_views + 1 WHERE topic_id = '$topic'";
db_query($sql, $currentCourseID);

$tool_content .= "</tbody></table>";

if ($paging and $total > $posts_per_page) {
	$times = 1;
	$tool_content .= <<<cData

    <table WIDTH="99%">
    <thead>
    <tr>
    <td WIDTH="50%" align=\"right\">
      <span class='row'><strong class='pagination'>
       <span>
cData;
	$last_page = $start - $posts_per_page;
	$tool_content .= "$langPages: ";

	for($x = 0; $x < $total; $x += $posts_per_page) {
		if($times != 1) {
			$tool_content .= "\n       <span class=\"page-sep\">,</span>";
		}
		if($start && ($start == $x)) {
			$tool_content .= "" .  $times;
		} else if($start == 0 && $x == 0) {
			$tool_content .= "1";
		} else {
			$tool_content .= "\n<a href=\"$_SERVER[PHP_SELF]?mode=viewtopic&amp;topic=$topic&amp;forum=$forum&amp;start=$x\">$times</a>";
		}
		$times++;
	}
	$tool_content .= "</span></strong></span></td>
	<td><span class='pages'>$langGoToPage: &nbsp;&nbsp;";
	if (isset($start) && $start > 0) {
		$tool_content .= "\n       <a href=\"$_SERVER[PHP_SELF]?topic=$topic&amp;forum=$forum&amp;start=$last_page\">$langPreviousPage</a>&nbsp;|";
	} else {
		$start = 0;
	}	
	if (($start + $posts_per_page) < $total) {
		$next_page = $start + $posts_per_page;
		$tool_content .= "\n<a href=\"$_SERVER[PHP_SELF]?topic=$topic&amp;forum=$forum&amp;start=$next_page\">$langNextPage</a>&nbsp;|";
	}
	$tool_content .= "&nbsp;<a href=\"$_SERVER[PHP_SELF]?topic=$topic&amp;forum=$forum&amp;all=true\">$langAllOfThem</a>
	</span>
	</td></tr></thead></table>";
} else {
	$tool_content .= "<table width=\"99%\"><thead>
	<tr>
	<td width=\"60%\" align=\"left\">
	<span class='row'><strong class='pagination'>&nbsp;</strong>
	</span></td>
	<td align=\"right\">
	<span class='pages'>";
	if ($total > $posts_per_page) {	
		$tool_content .= "&nbsp;<a href=\"$_SERVER[PHP_SELF]?topic=$topic&amp;forum=$forum&amp;start=0\">$langPages</a>";
        } else {
                $tool_content .= '&nbsp;';
        }
	$tool_content .= "</span></td></tr></thead></table>";
}

draw($tool_content, 2, 'phpbb', $local_head);
