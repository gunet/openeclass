<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * Open eClass 3.x standard stuff
 */
$require_current_course = true;
$require_help = true;
$helpTopic = 'For';
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/group/group_functions.php';
require_once 'config.php';
require_once 'functions.php';
require_once 'modules/search/forumpostindexer.class.php';

$fpdx = new ForumPostIndexer();

if (isset($_GET['forum'])) {
    $forum = intval($_GET['forum']);
}
if (isset($_GET['topic'])) {
    $topic = intval($_GET['topic']);
}

$sql = "SELECT f.name, t.title
            FROM forum f, forum_topic t
            WHERE f.id = $forum
            AND t.id = $topic
            AND t.forum_id = f.id
            AND f.course_id = $course_id";
$result = db_query($sql);
$myrow = mysql_fetch_array($result);

$forum_name = $myrow["name"];
$topic_title = $myrow["title"];
$forum_id = $forum;

$is_member = false;
$group_id = init_forum_group_info($forum_id);

$nameTools = $langReply;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
$navigation[] = array('url' => "viewforum.php?course=$course_code&amp;forum=$forum_id", 'name' => $forum_name);
$navigation[] = array('url' => "viewtopic.php?course=$course_code&amp;topic=$topic&amp;forum=$forum_id", 'name' => $topic_title);

if (!does_exists($forum, "forum") || !does_exists($topic, "topic")) {
    $tool_content .= $langErrorTopicSelect;
    draw($tool_content, 2, null, $head_content);
    exit();
}

if (isset($_POST['submit'])) {
    $message = $_POST['message'];
    $poster_ip = $_SERVER['REMOTE_ADDR'];
    if (trim($message) == '') {
        $tool_content .= "
                <p class='alert1'>$langEmptyMsg</p>
                <p class='back'>&laquo; $langClick <a href='newtopic.php?course=$course_code&amp;forum=$forum_id'>$langHere</a> $langReturnTopic</p>";
        draw($tool_content, 2, null, $head_content);
        exit();
    }


    $time = date("Y-m-d H:i");
    $surname = addslashes($_SESSION['surname']);
    $givenname = addslashes($_SESSION['givenname']);

    $sql = "INSERT INTO forum_post (topic_id, post_text, poster_id, post_time, poster_ip)
			VALUES ($topic, " . autoquote($message) . " , $uid, '$time', '$poster_ip')";
    $result = db_query($sql);
    $this_post = mysql_insert_id();
    $fpdx->store($this_post);
    $sql = "UPDATE forum_topic SET topic_time = '$time',
                    num_replies = num_replies+1,
                    last_post_id = $this_post
		WHERE id = $topic AND forum_id = $forum_id";
    $result = db_query($sql);
    $sql = "UPDATE forum SET num_posts = num_posts+1,
                    last_post_id = $this_post
		WHERE id = $forum_id
                    AND course_id = $course_id";
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
    $category_id = forum_category($forum_id);
    $cat_name = category_name($category_id);
    $sql = db_query("SELECT DISTINCT user_id FROM forum_notify
			WHERE (topic_id = $topic OR forum_id = $forum_id OR cat_id = $category_id)
			AND notify_sent = 1 AND course_id = $course_id AND user_id != $uid", $mysqlMainDb);
    $c = course_code_to_title($course_code);
    $name = uid_to_name($uid);
    $forum_message = "-------- $langBodyMessage ($langSender: $name )\n$message--------";
    $plain_forum_message = html2text($forum_message);
    $body_topic_notify = "<br>$langBodyTopicNotify $langInForum '" . q($topic_title) . "' $langOfForum '" . q($forum_name) . "' 
                                $langInCat '" . q($cat_name) . "' $langTo $langCourseS '$c'  <br />
                                <br />" . q($forum_message) . "<br /><br />$gunet<br />
                                <a href='{$urlServer}$course_code'>{$urlServer}$course_code</a>";
    $plain_body_topic_notify = "$langBodyTopicNotify $langInForum '$topic_title' $langOfForum '$forum_name' $langInCat '$cat_name' $langTo $langCourseS '$c' \n\n$plain_forum_message \n\n$gunet\n<a href='{$urlServer}$course_code'>{$urlServer}$course_code</a>";
    $linkhere = "&nbsp;<a href='${urlServer}modules/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
    $unsubscribe = "<br /><br />" . sprintf($langLinkUnsubscribe, $title);
    $plain_body_topic_notify .= $unsubscribe . $linkhere;
    $body_topic_notify .= $unsubscribe . $linkhere;
    while ($r = mysql_fetch_array($sql)) {
        if (get_user_email_notification($r['user_id'], $course_id)) {
            $emailaddr = uid_to_email($r['user_id']);
            send_mail_multipart('', '', '', $emailaddr, $subject_notify, $plain_body_topic_notify, $body_topic_notify, $charset);
        }
    }
    // end of notification

    $total_posts = get_total_posts($topic);
    if ($total_posts > $posts_per_page) {
        $page = '&start=' . ($posts_per_page * intval(($total_posts - 1) / $posts_per_page));
    } else {
        $page = '';
    }
    $_SESSION['message'] = "<p class='success'>$langStored</p>";
    header("Location: {$urlServer}modules/forum/viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id" . $page);
    exit;
} elseif (isset($_POST['cancel'])) {
    header("Location: viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id");
} else {
    // Topic review
    $tool_content .= "
        <div id='operations_container'>
            <ul id='opslist'>
              <li><a href='viewtopic.php?course=$course_code&amp;topic=$topic&amp;forum=$forum_id' target='_blank'>$langTopicReview</a></li>
            </ul>
        </div>";

    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&forum=$forum_id' method='post'>
	<fieldset>
        <legend>$langTopicAnswer: " . q($topic_title) . "</legend>
	<table class='tbl' width='100%'>
        <tr>
        <td>$langBodyMessage:";
    if (!isset($reply)) {
        $reply = "";
    }
    $tool_content .= "</td>
        </tr>
	<tr>
          <td>" . rich_text_editor('message', 15, 70, $reply, "") . "</td>
        </tr>
	<tr>
	  <td class='right'>	 
	    <input type='submit' name='submit' value='$langSubmit'>&nbsp;
	    <input type='submit' name='cancel' value='$langCancelPost'>
 	  </td>
	</tr>
	</table>
        </fieldset>
	</form>";
}
draw($tool_content, 2, null, $head_content);
