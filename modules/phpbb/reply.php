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
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
include '../group/group_functions.php';
include_once("./config.php");
include("functions.php");

if (isset($_GET['forum'])) {
	$forum = intval($_GET['forum']);
}
if (isset($_GET['topic'])) {
	$topic = intval($_GET['topic']);
}

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

$is_member = false;
$group_id = init_forum_group_info($forum_id);
if ($private_forum and !$is_member and !$is_editor) {
	$tool_content .= "<div class='caution'>$langPrivateForum</div>";
	draw($tool_content, 2);
	exit;
}

$nameTools = $langReply;
$navigation[]= array ("url"=>"index.php?course=$code_cours", "name"=> $langForums);
$navigation[]= array ("url"=>"viewforum.php?course=$code_cours&amp;forum=$forum", "name"=> $forum_name);
$navigation[]= array ("url"=>"viewtopic.php?course=$code_cours&amp;topic=$topic&amp;forum=$forum", "name"=> $topic_title);


if (!does_exists($forum, $currentCourseID, "forum") || !does_exists($topic, $currentCourseID, "topic")) {
	$tool_content .= $langErrorTopicSelect;
	draw($tool_content, 2, null, $head_content);
	exit();
}

if (isset($_POST['submit'])) {
	$message = $_POST['message'];
	$quote = $_POST['quote'];
	if (trim($message) == '') {
                $tool_content .= "
                <p class='alert1'>$langEmptyMsg</p>
                <p class='back'>&laquo; $langClick <a href='newtopic.php?course=$code_cours&amp;forum=$forum_id'>$langHere</a> $langReturnTopic</p>";
                draw($tool_content, 2, null, $head_content);
		exit();
	}
	// XXX: Do we need this code ?
	if ( $uid == -1 ) {
		if ($forum_access == 3 && $user_level < 2) {
			$tool_content .= $langNoPost;
                        draw($tool_content, 2, null, $head_content);
			exit();
		}
	}
	// Check that, if this is a private forum, the current user can post here.
	if ($forum_type == 1) {
		if (!check_priv_forum_auth($uid, $forum, TRUE, $currentCourseID)) {
			$tool_content .= "$langPrivateForum $langNoPost";
			draw($tool_content, 2, null, $head_content);
			exit();
		}
	}
	$poster_ip = $_SERVER['REMOTE_ADDR'];
	$is_html_disabled = false;
	if ((isset($allow_html) && $allow_html == 0) || isset($html)) {
		$message = htmlspecialchars($message);
		$is_html_disabled = true;
		if (isset($quote) && $quote) {
			// If it's been edited more than once, there might be old "edited by" strings with
			// escaped HTML code in them. We want to fix this up right here:
			$message = preg_replace("#&lt;font\ size\=-1&gt;\[\ $langEditedBy(.*?)\ \]&lt;/font&gt;#si", '[ ' . $langEditedBy . '\1 ]', $message);
		}
	}
	if ((isset($allow_bbcode) && $allow_bbcode == 1) && !isset($bbcode)) {
		$message = bbencode($message, $is_html_disabled);
	}
	$message = purify(format_message($message));
	$time = date("Y-m-d H:i");
	$nom = addslashes($_SESSION['nom']);
	$prenom = addslashes($_SESSION['prenom']);

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
		draw($tool_content, 2, null, $head_content);
		exit();
	}
	
	// --------------------------------
	// notify users 
	// --------------------------------
	$subject_notify = "$logo - $langSubjectNotify";
	$category_id = forum_category($forum);
	$cat_name = q(category_name($category_id));
	$sql = db_query("SELECT DISTINCT user_id FROM forum_notify 
			WHERE (topic_id = $topic OR forum_id = $forum OR cat_id = $category_id) 
			AND notify_sent = 1 AND course_id = $cours_id AND user_id != $uid", $mysqlMainDb);
	$c = course_code_to_title($currentCourseID);
        $linkhere = "${urlServer}main/emailunsubscribe.php?cid=$cours_id";
        $unsubscribe = sprintf($langLinkUnsubscribe, $intitule);

        $body_topic_notify = "<br>$langBodyTopicNotify $langInForum '" . q($topic_title) .
                "' $langInCat '". q($cat_name) . "' $langTo $langCourseS '" .
                "<a href='{$urlServer}courses/$currentCourseID'>" . q($intitule) . "</a>" .
                "' <hr>". "<b>$langSender:</b> " . q("$prenom $nom") .
                "<br><b>$langBodyMessage:</b><br>" .  "\n" . $message . "<hr>$gunet<br>$langNote:" .
                $unsubscribe . " <a href='$linkhere'>$langHere</a>\n";

        $plain_body_topic_notify = "$langBodyTopicNotify $langInForum '$topic_title' " .
                "$langOfForum '$forum_name' $langInCat '$cat_name' $langTo $langCourseS '$c'" .
                "------ $langBodyMessage ($langSender:$prenom $nom) ------\n" .
                html2text($message) .
                "\n\n$gunet\n{$urlServer}courses/$currentCourseID\n" .
                $unsubscribe . "\n" . $linkhere . "\n";

	while ($r = mysql_fetch_array($sql)) {
                if (get_user_email_notification($r['user_id'], $cours_id)) {
                        $emailaddr = uid_to_email($r['user_id']);
                        send_mail_multipart('', '', '', $emailaddr, $subject_notify, $plain_body_topic_notify, $body_topic_notify, $charset);
                }
	}
	// end of notification
	
	$total_posts = get_total_posts($topic, $currentCourseID, "topic");
	if ($total_posts > $posts_per_page) { 
		$page = '&start=' . ($posts_per_page * intval(($total_posts - 1) / $posts_per_page));
	} else {
		$page = '';
	}
	$_SESSION['message'] = "<p class='success'>$langStored</p>";
	header("Location: {$urlServer}modules/phpbb/viewtopic.php?course=$code_cours&topic=$topic&forum=$forum" . $page);
	exit;
} elseif (isset($_POST['cancel'])) {
	header("Location: viewtopic.php?course=$code_cours&topic=$topic&forum=$forum");	
} else {
	// Private forum logic here.
	if (($forum_type == 1) && !$user_logged_in && !$logging_in) {
		$tool_content .= "
		<form action='$_SERVER[SCRIPT_NAME]?course=$code_cours' method='post'>
		<fieldset>
		   <legend></legend>     
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
			<input type='submit' name='logging_in' value='".q($langEnter)."'>
			</td></tr></table>
			</td></tr></table>
		</fieldset>
		</form>";
		draw($tool_content, 2, null, $head_content);
		exit();
	} else {
		if ($forum_type == 1) {
			// To get here, we have a logged-in user. So, check whether that user is allowed to view
			// this private forum.
			if (!check_priv_forum_auth($uid, $forum, TRUE, $currentCourseID)) {
				$tool_content .= "$langPrivateForum $langNoPost";
				draw($tool_content, 2, null, $head_content);
				exit();
			}
		}
	}	
	// Topic review
	$tool_content .= "
    <div id=\"operations_container\">
	<ul id=\"opslist\">
          <li><a href=\"viewtopic.php?course=$code_cours&amp;topic=$topic&amp;forum=$forum\" target=\"_blank\">$langTopicReview</a></li>
	</ul>
    </div>";

	$tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&forum=$forum' method='post'>
	<fieldset>
        <legend>$langTopicAnswer: ".q($topic_title)."</legend>
	<table class='tbl' width='100%'>
        <tr>
        <td>$langBodyMessage:";	
	if (!isset($reply)) {
		$reply = "";
	}
	if (!isset($quote)) {
		$quote = "";
	}
	$tool_content .= "</td>
        </tr>
	<tr>
          <td>".rich_text_editor('message', 15, 70, $reply, "")."</td>
        </tr>
	<tr>
	  <td class='right'>
	    <input type='hidden' name='quote' value='$quote'>
	    <input type='submit' name='submit' value='".q($langSubmit)."'>&nbsp;
	    <input type='submit' name='cancel' value='".q($langCancelPost)."'>
 	  </td>
	</tr>
	</table>
        </fieldset>
	</form>";
}
draw($tool_content, 2, null, $head_content);
