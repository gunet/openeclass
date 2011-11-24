<?php
/* ========================================================================
 * Open eClass 2.4
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

/*
 * Open eClass 2.x standard stuff
 */
$require_current_course = TRUE;
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

$sql = "SELECT f.forum_type, f.forum_name, f.forum_access, t.topic_title 
            FROM forums f, topics t 
            WHERE f.forum_id = $forum
            AND t.topic_id = $topic 
            AND t.forum_id = f.forum_id
            AND f.course_id = $cours_id
            AND t.course_id = $cours_id";	
$result = db_query($sql);
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


if (!does_exists($forum, "forum") || !does_exists($topic, "topic")) {
	$tool_content .= $langErrorTopicSelect;
	draw($tool_content, 2, null, $head_content);
	exit();
}

if (isset($_POST['submit'])) {
	$message = $_POST['message'];
	$quote = $_POST['quote'];
        $poster_ip = $_SERVER['REMOTE_ADDR'];
	if (trim($message) == '') {
                $tool_content .= "
                <p class='alert1'>$langEmptyMsg</p>
                <p class='back'>&laquo; $langClick <a href='newtopic.php?course=$code_cours&amp;forum=$forum_id'>$langHere</a> $langReturnTopic</p>";
                draw($tool_content, 2, null, $head_content);
		exit();
	}
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
	$message = format_message($message);
	$time = date("Y-m-d H:i");
	$nom = addslashes($_SESSION['nom']);
	$prenom = addslashes($_SESSION['prenom']);

	//to prevent [addsig] from getting in the way, let's put the sig insert down here.
	if (isset($sig) && $sig) {
		$message .= "\n[addsig]";
	}
	$sql = "INSERT INTO posts (topic_id, forum_id, post_text, poster_id, post_time, poster_ip, course_id)
			VALUES ($topic, $forum, ".autoquote($message) ." , $uid, '$time', '$poster_ip', $cours_id)";
	$result = db_query($sql);
	$this_post = mysql_insert_id();
	$sql = "UPDATE topics SET topic_replies = topic_replies+1, 
                    topic_last_post_id = $this_post, 
                    topic_time = '$time' 
		WHERE topic_id = $topic
                    AND course_id = $cours_id";
	$result = db_query($sql);
	$sql = "UPDATE forums SET forum_posts = forum_posts+1, 
                    forum_last_post_id = $this_post 
		WHERE forum_id = $forum
                    AND course_id = $cours_id";
	$result = db_query($sql);
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
	$cat_name = category_name($category_id);
	$sql = db_query("SELECT DISTINCT user_id FROM forum_notify 
			WHERE (topic_id = $topic OR forum_id = $forum OR cat_id = $category_id) 
			AND notify_sent = 1 AND course_id = $cours_id AND user_id != $uid", $mysqlMainDb);
	$c = course_code_to_title($currentCourseID);
        $name = uid_to_name($uid);
	$forum_message = "-------- $langBodyMessage ($langSender: $name )\n$message--------";
	$plain_forum_message = html2text($forum_message);
	$body_topic_notify = "$langBodyTopicNotify $langInForum '$topic_title' $langOfForum '$forum_name' $langInCat '$cat_name' $langTo $langCourseS '$c'  <br /><br />$forum_message <br /><br />$gunet<br /><a href='{$urlServer}$currentCourseID'>{$urlServer}$currentCourseID</a>";
	$plain_body_topic_notify = "$langBodyTopicNotify $langInForum '$topic_title' $langOfForum '$forum_name' $langInCat '$cat_name' $langTo $langCourseS '$c' \n\n$plain_forum_message \n\n$gunet\n<a href='{$urlServer}$currentCourseID'>{$urlServer}$currentCourseID</a>";
	while ($r = mysql_fetch_array($sql)) {
                if (get_user_email_notification($r['user_id'], $cours_id)) {
                        $emailaddr = uid_to_email($r['user_id']);
                        send_mail_multipart('', '', '', $emailaddr, $subject_notify, $plain_body_topic_notify, $body_topic_notify, $charset);
                }
	}
	// end of notification
	
	$total_posts = get_total_posts($topic, "topic");
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
		<form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>
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
			<input type='SUBMIT' name='logging_in' value='$langEnter'>
			</td></tr></table>
			</td></tr></table>
		</fieldset>
		</form>";
		draw($tool_content, 2, null, $head_content);
		exit();
	} 	
	// Topic review
	$tool_content .= "
        <div id='operations_container'>
            <ul id='opslist'>
              <li><a href='viewtopic.php?course=$code_cours&amp;topic=$topic&amp;forum=$forum' target='_blank'>$langTopicReview</a></li>
            </ul>
        </div>";

	$tool_content .= "<form action='$_SERVER[PHP_SELF]?course=$code_cours&amp;topic=$topic&forum=$forum' method='post'>
	<fieldset>
        <legend>$langTopicAnswer: $topic_title</legend>
	<table class='tbl' width='100%'>
        <tr>
        <td>$langBodyMessage:";
	if (isset($quote) && $quote) {
		$sql = "SELECT post_text, post_time
                            FROM posts 
			WHERE post_id = '$post' 
                            AND course_id = $cours_id";
		if ($r = db_query($sql)) {
			$m = mysql_fetch_array($r);
			$text = $m["post_text"];
			$text = str_replace("<BR>", "\n", $text);
			$text = stripslashes($text);
			$text = bbdecode($text);
			$text = undo_make_clickable($text);
			$text = str_replace("[addsig]", "", $text);
			eval("\$reply = \"$langQuoteMsg\";");
		} else {
			$tool_content .= $langErrorConnectForumDatabase;
			draw($tool_content, 2, null, $head_content);
			exit();
		}
	}
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
	    <input type='submit' name='submit' value='$langSubmit'>&nbsp;
	    <input type='submit' name='cancel' value='$langCancelPost'>
 	  </td>
	</tr>
	</table>
        </fieldset>
	</form>";
}
draw($tool_content, 2, null, $head_content);
