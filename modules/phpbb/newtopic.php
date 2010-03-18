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
        phpbb/newtopic.php
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
 * Open eClass 2.x standard stuff
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = FALSE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
$tool_content = "";
$lang_editor = langname_to_code($language);
$head_content = <<<hContent
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>
hContent;


include_once("./config.php");
include("functions.php"); // application logic for phpBB

/******************************************************************************
 * Actual code starts here
 *****************************************************************************/

$sql = "SELECT forum_name, forum_access, forum_type FROM forums
	WHERE (forum_id = '$forum')";
if (!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= $langErrorDataForum;
	draw($tool_content, 2, 'phpbb', $head_content);
	exit;
}
$myrow = mysql_fetch_array($result);
$forum_name = $myrow["forum_name"];
$forum_access = $myrow["forum_access"];
$forum_type = $myrow["forum_type"];
$forum_id = $forum;

$nameTools = $langNewTopic;
$navigation[]= array ("url"=>"index.php", "name"=> $langForums);
$navigation[]= array ("url"=>"viewforum.php?forum=$forum_id", "name"=> $forum_name);

if (!does_exists($forum, $currentCourseID, "forum")) {
	//XXX: Error message in specified language
	$tool_content .= $langErrorPost;
}

if (isset($submit) && $submit) {
	$subject = strip_tags($subject);
	if (trim($message) == '' || trim($subject) == '') {
		$tool_content .= $langEmptyMsg;
		draw($tool_content, 2, 'phpbb', $head_content);
		exit;
	}
	if (!isset($username)) {
		$username = $langAnonymous;
	}
	
	if($forum_access == 3 && $user_level < 2) {
		$tool_content .= $langNoPost;
		draw($tool_content, 2, 'phpbb', $head_content);
		exit;
	}
	// Either valid user/pass, or valid session. continue with post.. but first:
	// Check that, if this is a private forum, the current user can post here.
	if ($forum_type == 1) {
		if (!check_priv_forum_auth($uid, $forum, TRUE, $currentCourseID)) {
			$tool_content .= "$langPrivateForum $langNoPost";
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
	}
	$is_html_disabled = false;
	if ((isset($allow_html) && $allow_html == 0) || isset($html)) {
		$message = htmlspecialchars($message);
		$is_html_disabled = true;
	}
	if ((isset($allow_bbcode) && $allow_bbcode == 1) && !($_POST['bbcode'])) {
		$message = bbencode($message, $is_html_disabled);
	}
	$message = format_message($message);
	$subject = strip_tags($subject);
	$poster_ip = $REMOTE_ADDR;
	$time = date("Y-m-d H:i");
	$nom = addslashes($nom);
	$prenom = addslashes($prenom);

	if (isset($sig) && $sig) {
		$message .= "\n[addsig]";
	}
	$sql = "INSERT INTO topics (topic_title, topic_poster, forum_id, topic_time, topic_notify, nom, prenom)
			VALUES (" . autoquote($subject) . ", '$uid', '$forum', '$time', 1, '$nom', '$prenom')";
	$result = db_query($sql, $currentCourseID);

	$topic_id = mysql_insert_id();
	$sql = "INSERT INTO posts (topic_id, forum_id, poster_id, post_time, poster_ip, nom, prenom)
			VALUES ('$topic_id', '$forum', '$uid', '$time', '$poster_ip', '$nom', '$prenom')";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= $langErrorEnterPost;
		draw($tool_content, 2, 'phpbb', $head_content);
		exit();
	} else {
		$post_id = mysql_insert_id();
		if ($post_id) {
			$sql = "INSERT INTO posts_text (post_id, post_text)
					VALUES ($post_id, " . autoquote($message) . ")";
			$result = db_query($sql, $currentCourseID);
			$sql = "UPDATE topics
				SET topic_last_post_id = $post_id
				WHERE topic_id = '$topic_id'";
			$result = db_query($sql, $currentCourseID);
		}
	}
	$sql = "UPDATE forums
		SET forum_posts = forum_posts+1, forum_topics = forum_topics+1, forum_last_post_id = $post_id
		WHERE forum_id = '$forum'";
	$result = db_query($sql, $currentCourseID);
	
	$topic = $topic_id;
	$total_forum = get_total_topics($forum, $currentCourseID);
	$total_topic = get_total_posts($topic, $currentCourseID, "topic")-1;  
	// Subtract 1 because we want the nr of replies, not the nr of posts.
	$forward = 1;

	// --------------------------------
	// notify users 
	// --------------------------------
	$subject_notify = "$logo - $langNewForumNotify";
	$category_id = forum_category($forum);
	$cat_name = category_name($category_id);
	$sql = db_query("SELECT DISTINCT user_id FROM forum_notify 
			WHERE (forum_id = $forum OR cat_id = $category_id) 
			AND notify_sent = 1 AND course_id = $cours_id", $mysqlMainDb);
	$c = course_code_to_title($currentCourseID);
	$body_topic_notify = "$langCourse: '$c'\n\n$langBodyForumNotify $langInForums '$forum_name' $langInCat '$cat_name' \n\n$gunet";
	while ($r = mysql_fetch_array($sql)) {
		$emailaddr = uid_to_email($r['user_id']);
		send_mail('', '', '', $emailaddr, $subject_notify, $body_topic_notify, $charset);
	}
	// end of notification
	
	$tool_content .= "<table width='99%'><tbody>
	<tr><td class='success'>
	<p><b>$langStored</b></p>
	<p>$langClick <a href='viewtopic.php?topic=$topic_id&amp;forum=$forum&amp;$total_topic'>$langHere</a>$langViewMsg</p>
	<p>$langClick <a href='viewforum.php?forum=$forum_id&amp;total_forum'>$langHere</a> $langReturnTopic</p>
	</td>
	</tr>
	</tbody></table>"; 
} else {
	if (!$uid AND !$fakeUid) {
		$tool_content .= "<center><br /><br />
		$langLoginBeforePost1
		<br />
		$langLoginBeforePost2
		<a href='../../index.php'>$langLoginBeforePost3.</a>
		</center>";
		draw($tool_content, 2, 'phpbb', $head_content);
		exit();
	}
	$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
	<table class='FormData' width='99%'>
	<tbody>
	<tr>
	<th width='220'>&nbsp;</th>
	<td><b>$langTopicData</b></td>
	</tr>
	<tr>
	<th class='left'>$langSubject:</th>
	<td><input type='text' name='subject' size='53' maxlength='100' class='FormData_InputText' /></td>
	</tr>
	<tr>
	<th class='left'>$langBodyMessage:</th>
	<td>
	<table class='xinha_editor'>
	<tr>
	<td><textarea id='xinha' name='message' rows='14' cols='50' class='FormData_InputText'></textarea></td>
	</tr></table>
	</td>
	</tr>
	<tr>
	<th>&nbsp;</th>
	<td><input type='hidden' name='forum' value='$forum' />
	<input type='submit' name='submit' value='$langSubmit' />&nbsp;
	<input type='submit' name='cancel' value='$langCancelPost' />
	</td></tr>
	</tbody>
	</table>
	<br/>
	</form>";
}
draw($tool_content, 2, 'phpbb', $head_content);
?>
