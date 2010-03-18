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
        phpbb/reply.php
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
==============================================================================
*/

/*
 * Open eClass 2.x standard stuff
 */
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
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
include("functions.php");

if (isset($post_id) && $post_id) {
	// We have a post id, so include that in the checks..
	$sql  = "SELECT f.forum_type, f.forum_name, f.forum_access, t.topic_title ";
	$sql .= "FROM forums f, topics t, posts p ";
	$sql .= "WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic)";
	$sql .= " AND (p.post_id = $post_id) AND (t.forum_id = f.forum_id)";
	$sql .= " AND (p.forum_id = f.forum_id) AND (p.topic_id = t.topic_id)";
} else {
	// No post id, just check forum and topic.
	$sql = "SELECT f.forum_type, f.forum_name, f.forum_access, t.topic_title ";
	$sql .= "FROM forums f, topics t ";
	$sql .= "WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";	
}

$result = db_query($sql, $currentCourseID);
$myrow = mysql_fetch_array($result);

$forum_name = $myrow["forum_name"];
$forum_access = $myrow["forum_access"];
$forum_type = $myrow["forum_type"];
$topic_title = $myrow["topic_title"];
$forum_id = $forum;

$nameTools = $langReply;
$navigation[]= array ("url"=>"index.php", "name"=> $langForums);
$navigation[]= array ("url"=>"viewforum.php?forum=$forum", "name"=> $forum_name);
$navigation[]= array ("url"=>"viewtopic.php?&topic=$topic&forum=$forum", "name"=> $topic_title);


if (!does_exists($forum, $currentCourseID, "forum") || !does_exists($topic, $currentCourseID, "topic")) {
	$tool_content .= $langErrorTopicSelect;
	draw($tool_content, 2, 'phpbb', $head_content);
	exit();
}

if (isset($submit) && $submit) {
	if (trim($message) == '') {
		$tool_content .= $langEmptyMsg;
		draw($tool_content, 2, 'phpbb', $head_content);
		exit();
	}
	if (isset($user_level) && $user_level == -1) {
		$tool_content .= $luserremoved;
		draw($tool_content, 2, 'phpbb', $head_content);
		exit();
	}
	if ($forum_access == 3 && $user_level < 2) {
		$tool_content .= $langNoPost;
		draw($tool_content, 2, 'phpbb', $head_content);
		exit();
	}
	// XXX: Do we need this code ?
	if ( $uid == -1 ) {
		if ($forum_access == 3 && $user_level < 2) {
			$tool_content .= $langNoPost;
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
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
	$poster_ip = $REMOTE_ADDR;
	$is_html_disabled = false;
	if ( (isset($allow_html) && $allow_html == 0) || isset($html)) {
		$message = htmlspecialchars($message);
		$is_html_disabled = true;
		if (isset($quote) && $quote) {
			$edit_by = get_syslang_string($sys_lang, "l_editedby");
			// If it's been edited more than once, there might be old "edited by" strings with
			// escaped HTML code in them. We want to fix this up right here:
			$message = preg_replace("#&lt;font\ size\=-1&gt;\[\ $edit_by(.*?)\ \]&lt;/font&gt;#si", '[ ' . $edit_by . '\1 ]', $message);
		}
	}
	if ( (isset($allow_bbcode) && $allow_bbcode == 1) && !isset($bbcode)) {
		$message = bbencode($message, $is_html_disabled);
	}
	$message = format_message($message);
	$time = date("Y-m-d H:i");
	$nom = addslashes($nom);
	$prenom = addslashes($prenom);

	//to prevent [addsig] from getting in the way, let's put the sig insert down here.
	if (isset($sig) && $sig) {
		$message .= "\n[addsig]";
	}
	$sql = "INSERT INTO posts (topic_id, forum_id, poster_id, post_time, poster_ip, nom, prenom)
			VALUES ('$topic', '$forum', '$uid','$time', '$poster_ip', '$nom', '$prenom')";
	$result = db_query($sql, $currentCourseID);
	$this_post = mysql_insert_id();
	if ($this_post) {
		$sql = "INSERT INTO posts_text (post_id, post_text) VALUES ($this_post, " .
                        autoquote($message) . ")";
		$result = db_query($sql, $currentCourseID); 
	}
	$sql = "UPDATE topics SET topic_replies = topic_replies+1, topic_last_post_id = $this_post, topic_time = '$time' 
		WHERE topic_id = '$topic'";
	$result = db_query($sql, $currentCourseID);
	$sql = "UPDATE forums SET forum_posts = forum_posts+1, forum_last_post_id = '$this_post' 
		WHERE forum_id = '$forum'";
	$result = db_query($sql, $currentCourseID);
	if (!$result) {
		$tool_content .= $langErrorUpadatePostCount;
		draw($tool_content, 2, 'phpbb', $head_content);
		exit();
	}
	
	// --------------------------------
	// notify users 
	// --------------------------------
	$subject_notify = "$logo - $langSubjectNotify";
	$category_id = forum_category($forum);
	$cat_name = category_name($category_id);
	$sql = db_query("SELECT DISTINCT user_id FROM forum_notify 
			WHERE (topic_id = $topic OR forum_id = $forum OR cat_id = $category_id) 
			AND notify_sent = 1 AND course_id = $cours_id", $mysqlMainDb);
	$c = course_code_to_title($currentCourseID);
	$body_topic_notify = "$langCourse: '$c'\n\n$langBodyTopicNotify $langInForum '$topic_title' $langOfForum '$forum_name' $langInCat '$cat_name' \n\n$gunet";
	while ($r = mysql_fetch_array($sql)) {
		$emailaddr = uid_to_email($r['user_id']);
		send_mail('', '', '', $emailaddr, $subject_notify, $body_topic_notify, $charset);
	}
	// end of notification
	 
	$total_forum = get_total_topics($forum, $currentCourseID);
	$total_topic = get_total_posts($topic, $currentCourseID, "topic")-1;
	// Subtract 1 because we want the nr of replies, not the nr of posts.
	$forward = 1;
	$tool_content .= "<div id=\"operations_container\">
	<ul id=\"opslist\">
	<li><a href=\"viewtopic.php?topic=$topic&forum=$forum&$total_topic\">$langViewMessage</a></li>
	<li><a href=\"viewforum.php?forum=$forum&$total_forum\">$langReturnTopic</a></li>
	</ul></div><br />";
	
	$tool_content .= "<table width=\"99%\"><tbody><tr>
	<td class=\"success\">$langStored</td>
	</tr></tbody></table>";
} else {
	// Private forum logic here.
	if (($forum_type == 1) && !$user_logged_in && !$logging_in) {
		$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
		<table align='left' width='99%'>
		<tr><td>
		<table width='100%'><tr><td>$langPrivateNotice</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
		<td>
		<input type='hidden' name='forum' value='$forum'>
		<input type='hidden' name='topic' value='$topic'>
		<input type='hidden' name='post' value='$post'>
		<input type='hidden' name='quote' value='$quote'>
		<input type='SUBMIT' name='logging_in' value='$langEnter'>
		</td></tr></table>
		</td></tr></table></form>";
		draw($tool_content, 2, 'phpbb', $head_content);
		exit();
	} else {
		if (!$uid AND !$fakeUid) {
			$tool_content .= "<center><br><br>$langLoginBeforePost1<br>";
			$tool_content .= "$langLoginBeforePost2<a href=../../index.php>$langLoginBeforePost3</a></center>";
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
		if ($forum_type == 1) {
			// To get here, we have a logged-in user. So, check whether that user is allowed to view
			// this private forum.
			if (!check_priv_forum_auth($uid, $forum, TRUE, $currentCourseID)) {
				$tool_content .= "$langPrivateForum $langNoPost";
				draw($tool_content, 2, 'phpbb', $head_content);
				exit();
			}
			// Ok, looks like we're good.
		}
	}	
	// Topic review
	$tool_content .= "<div id=\"operations_container\">
	<ul id=\"opslist\">
	<li><a href=\"viewtopic.php?topic=$topic&forum=$forum\" target=\"_blank\">$langTopicReview</a></li>
	</ul></div><br />";
	$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
	<table class=\"FormData\" width=\"99%\">
	<tbody>
	<tr>
	<th width=\"220\">&nbsp;</th>
	<td><b>$langTopicAnswer</b>: $topic_title</td>
	</tr>
	<tr>
        <th class=\"left\">$langBodyMessage:";
	if (isset($quote) && $quote) {
		$sql = "SELECT pt.post_text, p.post_time, u.username 
			FROM posts p, posts_text pt 
			WHERE p.post_id = '$post' AND pt.post_id = p.post_id";
		if ($r = db_query($sql, $currentCourseID)) {
			$m = mysql_fetch_array($r);
			$text = $m["post_text"];
			$text = str_replace("<BR>", "\n", $text);
			$text = stripslashes($text);
			$text = bbdecode($text);
			$text = undo_make_clickable($text);
			$text = str_replace("[addsig]", "", $text);
			$syslang_quotemsg = get_syslang_string($sys_lang, "langQuoteMsg");
			eval("\$reply = \"$syslang_quotemsg\";");
		} else {
			$tool_content .= $langErrorConnectForumDatabase;
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
	}
	if (!isset($reply)) {
		$reply = "";
	}
	if (!isset($quote)) {
		$quote = "";
	}
	$tool_content .= "</th><td valign='top'>
	<table class='xinha_editor'>
	<tr>
	<td>
	<textarea id='xinha' name='message' rows='15' cols='70' class='auth_input'>$reply</textarea></td>
	</tr></table></td>
	</tr>
	<tr>
	<th>&nbsp;</th>
	<td>
	<input type='hidden' name='forum' value='$forum'>
	<input type='hidden' name='topic' value='$topic'>
	<input type='hidden' name='quote' value='$quote'>
	<input type='submit' name='submit' value='$langSubmit'>&nbsp;
	<input type='submit' name='cancel' value='$langCancelPost'>
	</td>
	</tr>
	</tbody></table>
	</form><br/>";
}

draw($tool_content, 2, 'phpbb', $head_content);

