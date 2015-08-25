<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014 Greek Universities Network - GUnet
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
require_once 'modules/forum/config.php';
require_once 'modules/forum/functions.php';
require_once 'modules/search/indexer.class.php';

$toolName = $langForums;
if (isset($_GET['forum'])) {
    $forum = intval($_GET['forum']);
} else {
    header("Location: index.php?course=$course_code");
    exit();
}
if (isset($_GET['topic'])) {
    $topic = intval($_GET['topic']);
}
$parent_post_ok = true;
if (isset($_GET['parent_post'])) {
    $parent_post = intval($_GET['parent_post']);
    $result = Database::get()->querySingle("SELECT * FROM forum_post WHERE topic_id = ?d AND id = ?d", $topic, $parent_post);
    if (!$result) {
        $parent_post_ok = false; //user has altered get param from url
    }
} else {
    $parent_post = 0;
}

$myrow = Database::get()->querySingle("SELECT f.name, t.title, t.locked
            FROM forum f, forum_topic t
            WHERE f.id = $forum
            AND t.id = $topic
            AND t.forum_id = f.id
            AND f.course_id = ?d", $course_id);

$forum_name = $myrow->name;
$topic_title = $myrow->title;
$topic_locked = $myrow->locked;
$forum_id = $forum;

$is_member = false;
$group_id = init_forum_group_info($forum_id);

$pageName = $langReply;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
$navigation[] = array('url' => "viewforum.php?course=$course_code&amp;forum=$forum_id", 'name' => q($forum_name));
$navigation[] = array('url' => "viewtopic.php?course=$course_code&amp;topic=$topic&amp;forum=$forum_id", 'name' => q($topic_title));

if (!does_exists($forum, "forum") || !does_exists($topic, "topic") || !$parent_post_ok) {
    $tool_content .= $langErrorTopicSelect;
    draw($tool_content, 2, null, $head_content);
    exit();
}

if ($topic_locked == 1) {
    $tool_content .= "<div class='alert alert-warning'>$langErrorTopicLocked</div>";
    draw($tool_content, 2, null, $head_content);
    exit();
}

if (isset($_POST['submit'])) {
    $message = $_POST['message'];
    $poster_ip = $_SERVER['REMOTE_ADDR'];
    $parent_post = $_POST['parent_post'];
    if (trim($message) == '') {
        $tool_content .= "
                <div class='alert alert-warning'>$langEmptyMsg</div>
                <p class='back'>&laquo; $langClick <a href='newtopic.php?course=$course_code&amp;forum=$forum_id'>$langHere</a> $langReturnTopic</p>";
        draw($tool_content, 2, null, $head_content);
        exit();
    }


    $time = date("Y-m-d H:i:s");
    $surname = addslashes($_SESSION['surname']);
    $givenname = addslashes($_SESSION['givenname']);

    $this_post = Database::get()->query("INSERT INTO forum_post (topic_id, post_text, poster_id, post_time, poster_ip, parent_post_id) VALUES (?d, ?s , ?d, ?t, ?s, ?d)"
                    , $topic, $message, $uid, $time, $poster_ip, $parent_post)->lastInsertID;
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_FORUMPOST, $this_post);
    $forum_user_stats = Database::get()->querySingle("SELECT COUNT(*) as c FROM forum_post 
                        INNER JOIN forum_topic ON forum_post.topic_id = forum_topic.id
                        INNER JOIN forum ON forum.id = forum_topic.forum_id
                        WHERE forum_post.poster_id = ?d AND forum.course_id = ?d", $uid, $course_id);
    Database::get()->query("DELETE FROM forum_user_stats WHERE user_id = ?d AND course_id = ?d", $uid, $course_id);
    Database::get()->query("INSERT INTO forum_user_stats (user_id, num_posts, course_id) VALUES (?d,?d,?d)", $uid, $forum_user_stats->c, $course_id);
    Database::get()->query("UPDATE forum_topic SET topic_time = ?t,
                    num_replies = num_replies+1,
                    last_post_id = ?d
		WHERE id = ?d AND forum_id = ?d", $time, $this_post, $topic, $forum_id);
    $result = Database::get()->query("UPDATE forum SET num_posts = num_posts+1,
                    last_post_id = ?d
		WHERE id = ?d
                    AND course_id = ?d", $this_post, $forum_id, $course_id);
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
    $sql = Database::get()->queryArray("SELECT DISTINCT user_id FROM forum_notify
			WHERE (topic_id = ?d OR forum_id = ?d OR cat_id = ?d)
			AND notify_sent = 1 AND course_id = ?d AND user_id != ?d"
            , $topic, $forum_id, $category_id, $course_id, $uid);
    $c = course_code_to_title($course_code);
    $name = uid_to_name($uid);
    $forum_message = "-------- $langBodyMessage ($langSender: $name )\n$message--------";
    $plain_forum_message = q(html2text($forum_message));
    $body_topic_notify = "<br>$langBodyTopicNotify $langInForum '" . q($topic_title) . "' $langOfForum '" . q($forum_name) . "' 
                                $langInCat '" . q($cat_name) . "' $langTo $langCourseS '$c'  <br />
                                <br />" . $forum_message . "<br /><br />$gunet<br />
                                <a href='{$urlServer}$course_code'>{$urlServer}$course_code</a>";
    $plain_body_topic_notify = "$langBodyTopicNotify $langInForum '" . q($topic_title) . "' $langOfForum " . q($forum_name) . "' $langInCat '" . q($cat_name) . "' $langTo $langCourseS '$c' \n\n$plain_forum_message \n\n$gunet\n<a href='{$urlServer}$course_code'>{$urlServer}$course_code</a>";
    $linkhere = "&nbsp;<a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
    $unsubscribe = "<br /><br />$langNote: " . sprintf($langLinkUnsubscribe, course_id_to_title($course_id));
    $plain_body_topic_notify .= $unsubscribe . $linkhere;
    $body_topic_notify .= $unsubscribe . $linkhere;
    foreach ($sql as $r) {
        if (get_user_email_notification($r->user_id, $course_id)) {
            $emailaddr = uid_to_email($r->user_id);
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
    $_SESSION['message'] = "<div class='alert alert-success'>$langStored</div>";
    header("Location: {$urlServer}modules/forum/viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id" . $page);
    exit;
} else {
    // Topic review
    $tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ));
    if (!isset($reply)) {
        $reply = "";
    }
    $tool_content .= "
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&forum=$forum_id' method='post'>
            <input type='hidden' name='parent_post' value='$parent_post'>
            <fieldset>
            <div class='form-group'>
              <label for='message' class='col-sm-2 control-label'>$langBodyMessage:</label>
              <div class='col-sm-10'>
                " . rich_text_editor('message', 15, 70, $reply) . "
              </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
                    <a class='btn btn-default' href='viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id'>$langCancel</a>
                </div>
            </div>              
        </fieldset>
	</form>
    </div>";
}
draw($tool_content, 2, null, $head_content);
