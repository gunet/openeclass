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
    //triggerGame($course_id, $uid, ForumEvent::NEWPOST);
    triggerGame($course_id, $uid, ForumEvent::ACTIVITY);
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

    $subject = Database::get()->querySingle('SELECT title FROM forum_topic WHERE id = ?d', $topic)->title;
    notify_users($forum_id, $forum_name, $topic, $subject, $message, $time);

    $page = "modules/forum/viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id";
    $total_posts = get_total_posts($topic);
    if ($total_posts > POSTS_PER_PAGE) {
        $page .= '&start=' . (POSTS_PER_PAGE * intval(($total_posts - 1) / POSTS_PER_PAGE));
    }
    Session::Messages($langStored, 'alert-success');
    redirect_to_home_page($page);
} else {
    // Topic review
    $tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ));
    if (!isset($reply)) {
        $reply = '';
    }
    
    if (isset($_GET['parent_post'])) {
        $parent_post_text = Database::get()->querySingle("SELECT post_text FROM forum_post WHERE id = ?d", $parent_post)->post_text;
        $tool_content .= "<blockquote><p><h5>$parent_post_text</h5></p></blockquote>";
    }
    
    $tool_content .= "<div class='form-wrapper'>
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
