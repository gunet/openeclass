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
include '../group/group_functions.php';

include_once("./config.php");
include("functions.php");

/******************************************************************************
 * Actual code starts here
 *****************************************************************************/
if (isset($_GET['forum'])) {
	$forum = intval($_GET['forum']);
}
if (isset($_GET['topic'])) {
	$topic = intval($_GET['topic']);
} else {
	$topic = '';
}

$sql = "SELECT forum_name, forum_access, forum_type FROM forums
	WHERE (forum_id = '$forum')";
if (!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= $langErrorDataForum;
	draw($tool_content, 2, null, $head_content);
	exit;
}
$myrow = mysql_fetch_array($result);
$forum_name = $myrow["forum_name"];
$forum_access = $myrow["forum_access"];
$forum_type = $myrow["forum_type"];
$forum_id = $forum;

$is_member = false;
$group_id = init_forum_group_info($forum_id);
if ($private_forum and !($is_member or $is_editor)) {
	$tool_content .= "<div class='caution'>$langPrivateForum</div>";
	draw($tool_content, 2);
	exit;
}

$nameTools = $langNewTopic;
$navigation[]= array('url' => "index.php?course=$code_cours", 'name' => $langForums);
$navigation[]= array('url' => "viewforum.php?course=$code_cours&amp;forum=$forum_id", 'name' => $forum_name);

if (!does_exists($forum, $currentCourseID, "forum")) {
	$tool_content .= "<div class='caution'>$langErrorPost</div>";
	draw($tool_content, 2);
	exit;
}

if (isset($_POST['submit'])) {
	$subject = trim($_POST['subject']);
	$message = trim($_POST['message']);
	if (empty($message) or empty($subject)) {
		header("Location: viewforum.php?course=$code_cours&forum=$forum&empty=true");
		exit;
	}

	// Check that, if this is a private forum, the current user can post here.
	if (!$can_post) {
		$tool_content .= "<div class='caution'>$langPrivateForum $langNoPost</div>";
		draw($tool_content, 2);
		exit();

	}
	$is_html_disabled = false;
	if ((isset($allow_html) && $allow_html == 0) || isset($html)) {
		$message = htmlspecialchars($message);
		$is_html_disabled = true;
	}
	if ((isset($allow_bbcode) && $allow_bbcode == 1) && !($_POST['bbcode'])) {
		$message = bbencode($message, $is_html_disabled);
	}
	$message = purify(format_message($message));
	$poster_ip = $_SERVER['REMOTE_ADDR'];
	$time = date("Y-m-d H:i");
	$nom = addslashes($_SESSION['nom']);
	$prenom = addslashes($_SESSION['prenom']);

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
		draw($tool_content, 2, null, $head_content);
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
	// subtract 1 because we want the number of replies, not the number of posts.
	$forward = 1;

	// --------------------------------
	// notify users
	// --------------------------------
	$subject_notify = "$logo - $langNewForumNotify";
	$category_id = forum_category($forum);
	$cat_name = category_name($category_id);
	$sql = db_query("SELECT DISTINCT user_id FROM forum_notify
			WHERE (forum_id = $forum OR cat_id = $category_id)
			AND notify_sent = 1 AND course_id = $cours_id AND user_id != $uid", $mysqlMainDb);
	$c = course_code_to_title($currentCourseID);
        $linkhere = "${urlServer}main/emailunsubscribe.php?cid=$cours_id";
        $unsubscribe = sprintf($langLinkUnsubscribe, $intitule);

        $body_topic_notify = "<br>$langBodyForumNotify $langInForums '" . q($forum_name) .
                "' $langInCat '". q($cat_name) . "' $langTo $langCourseS '" .
                "<a href='{$urlServer}courses/$currentCourseID'>" . q($intitule) . "</a>" .
                "' <hr>". "<b>$langSender:</b> " . q("$prenom $nom") .
                "<br><b>$langBodyMessage:</b><br>" .  "\n" . $message . "<hr>$gunet<br>$langNote:" .
                $unsubscribe . " <a href='$linkhere'>$langHere</a>\n";

        $plain_body_topic_notify = "$langBodyForumNotify $langInForums '$forum_name' $langInCat " .
                "'$cat_name' $langTo $langCourseS '$c'\n\n" .
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

	$tool_content .= "<p class='success'>$langStored</p>
		<p class='back'>&laquo; <a href='viewtopic.php?course=$code_cours&amp;topic=$topic_id&amp;forum=$forum&amp;$total_topic'>$langReturnMessages</a></p>
		<p class='back'>&laquo; <a href='viewforum.php?course=$code_cours&amp;forum=$forum_id'>$langReturnTopic</a></p>";
} elseif (isset($_POST['cancel'])) {
	header("Location: viewforum.php?course=$code_cours&forum=$forum");
	exit;
} else {
	$tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;topic=$topic&forum=$forum' method='post'>
        <fieldset>
          <legend>$langTopicData</legend>
	  <table class='tbl' width='100%'>
	  <tr>
	    <th>$langSubject:</th>
	    <td><input type='text' name='subject' size='53' maxlength='100' /></td>
	  </tr>
	  <tr>
            <th valign='top'>$langBodyMessage:</th>
            <td>".  rich_text_editor('message', 14, 50, '', '') ."</td>
          </tr>
	  <tr>
            <th>&nbsp;</th>
	    <td class='right'>
	       <input class='Login' type='submit' name='submit' value='".q($langSubmit)."' />&nbsp;
	       <input class='Login' type='submit' name='cancel' value='".q($langCancelPost)."' />
	    </td>
          </tr>
	  </table>
	</fieldset>
	</form>";
}

draw($tool_content, 2, null, $head_content);
