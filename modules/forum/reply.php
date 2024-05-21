<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018 Greek Universities Network - GUnet
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
$require_login = true;
$require_user_registration = true;
$require_help = true;
$helpTopic = 'forum';
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/forum/functions.php';
require_once 'modules/search/indexer.class.php';
require_once 'include/lib/fileUploadLib.inc.php';

$toolName = $langForums;
$pageName = $langReply;

if (isset($_GET['forum'])) {
    $forum = intval($_GET['forum']);
} else {
    header("Location: index.php?course=$course_code");
    exit();
}
if (isset($_GET['topic'])) {
    $topic = intval($_GET['topic']);
}

if (isset($_GET['unit'])) {
    $unit = intval($_GET['unit']);
}
$res_type = isset($_GET['res_type']);
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

if (!add_units_navigation(TRUE)) {
    if (!$res_type) {
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
        $navigation[] = array('url' => "viewforum.php?course=$course_code&amp;forum=$forum_id", 'name' => q($forum_name));
        $navigation[] = array('url' => "viewtopic.php?course=$course_code&amp;topic=$topic&amp;forum=$forum_id", 'name' => q($topic_title));
    } else {
        $navigation[] = array('url' => "../wall/index.php?course=$course_code", 'name' => $langWall);
        $navigation[] = array('url' => "../units/view.php?course=$course_code&amp;res_type=forum&amp;forum=$forum_id", 'name' => q($forum_name));
        $navigation[] = array('url' => "../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum_id", 'name' => q($topic_title));
    }
} else {
    $navigation[] = array('url' => "../units/view.php?course=$course_code&amp;res_type=forum&amp;forum=$forum_id&amp;unit=$unit", 'name' => q($forum_name));
    $navigation[] = array('url' => "../units/view.php?course=$course_code&amp;res_type=forum_topic&amp;topic=$topic&amp;forum=$forum_id&amp;unit=$unit", 'name' => q($topic_title));
}

if (!does_exists($forum, "forum") || !does_exists($topic, "topic") || !$parent_post_ok) {
    $tool_content .= $langErrorTopicSelect;
    draw($tool_content, 2, null, $head_content);
    exit();
}

if ($topic_locked == 1) {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langErrorTopicLocked</span></div></div>";
    draw($tool_content, 2, null, $head_content);
    exit();
}

if (isset($_POST['submit'])) {
    $message = $_POST['message'];
    $poster_ip = Log::get_client_ip();
    $parent_post = $_POST['parent_post'];
    if (trim($message) == '') {
        $tool_content .= "<div class='col-sm-12'>
                <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langEmptyMsg</span></div>
            </div>
            <div class='col-12 d-flex justify-content-center align-items center'>
                <a href='newtopic.php?course=$course_code&amp;forum=$forum_id' class='btn submitAdminBtn'>$langBack</a>
            </div>";
        draw($tool_content, 2, null, $head_content);
        exit();
    }
    $time = date("Y-m-d H:i:s");
    // upload attached file
    if (isset($_FILES['topic_file']) and is_uploaded_file($_FILES['topic_file']['tmp_name'])) { // upload comments file
        $topic_filename = $_FILES['topic_file']['name'];
        validateUploadedFile($topic_filename); // check file type
        $topic_filename = add_ext_on_mime($topic_filename);
        // File name used in file system and path field
        $safe_topic_filename = safe_filename(get_file_extension($topic_filename));
        if (!file_exists("$webDir/courses/$course_code/forum/")) {
            mkdir("$webDir/courses/$course_code/forum/", 0755);
        }
        if (move_uploaded_file($_FILES['topic_file']['tmp_name'], "$webDir/courses/$course_code/forum/$safe_topic_filename")) {
            @chmod("$webDir/courses/$course_code/forum/$safe_topic_filename", 0644);
            $topic_real_filename = $_FILES['topic_file']['name'];
            $topic_filepath = $safe_topic_filename;
        }
    } else {
        $topic_filepath = $topic_real_filename = '';
    }

    $this_post = Database::get()->query("INSERT INTO forum_post (topic_id, post_text, poster_id, post_time, poster_ip, parent_post_id, topic_filepath, topic_filename) VALUES (?d, ?s , ?d, ?t, ?s, ?d, ?s, ?s)"
                    , $topic, $message, $uid, $time, $poster_ip, $parent_post, $topic_filepath, $topic_real_filename)->lastInsertID;
    triggerForumGame($course_id, $uid, ForumEvent::NEWPOST);
    triggerTopicGame($course_id, $uid, ForumTopicEvent::NEWPOST, $topic);
    triggerForumAnalytics($course_id, $uid, ForumAnalyticsEvent::FORUMEVENT);
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

    if (isset($unit)) {
        $page = "modules/units/view.php?course=$course_code&res_type=forum_topic&topic=$topic&forum=$forum_id&unit=$unit";
    } else if ($res_type) {
        $page = "modules/units/view.php?course=$course_code&res_type=forum_topic&topic=$topic&forum=$forum_id";
    } else {
        $page = "modules/forum/viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id";
    }
    $total_posts = get_total_posts($topic);
    if ($total_posts > POSTS_PER_PAGE) {
        $page .= "&start=" . (POSTS_PER_PAGE * intval(($total_posts - 1) / POSTS_PER_PAGE));
    }
    Session::flash('message',$langStored);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page($page);
} else {
    if (isset($unit)) {
        $cancel_url = $back_url = "../units/view.php?course=$course_code&res_type=forum_topic&topic=$topic&forum=$forum_id&unit=$unit";
        $form_url = "../units/view.php?course=$course_code&amp;res_type=forum_topic_reply&amp;topic=$topic&forum=$forum_id&amp;unit=$unit";
    } else if ($res_type) {
        $cancel_url = $back_url = "../units/view.php?course=$course_code&res_type=forum_topic&topic=$topic&forum=$forum_id";
        $form_url = "../units/view.php?course=$course_code&amp;res_type=forum_topic_reply&amp;topic=$topic&forum=$forum_id";
    } else {
        $cancel_url = $back_url = "viewtopic.php?course=$course_code&topic=$topic&forum=$forum_id";
        $form_url = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&forum=$forum_id";
    }
    if (isset($_GET['parent_post'])) {
        $parent_post_text = Database::get()->querySingle("SELECT post_text FROM forum_post WHERE id = ?d", $parent_post)->post_text;
        $tool_content .= "<blockquote><h5>$parent_post_text</h5></blockquote>";
    }

    $reply = '';
    $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' action='$form_url' method='post' enctype='multipart/form-data'>
            <input type='hidden' name='parent_post' value='$parent_post'>
            <div class='form-group'>
              <label for='message' class='col-sm-6 control-label-notes'>$langBodyMessage</label>
              <div class='col-sm-12'>
                " . rich_text_editor('message', 15, 70, $reply) . "
              </div>
            </div>
            <div class='form-group mt-4'>
                <label for='topic_file' class='col-sm-6 control-label-notes'>$langAttachedFile</label>
                <div class='col-sm-12'>
                    <input type='file' name='topic_file' id='topic_file' size='35'>
                    " . fileSizeHidenInput() . "
                </div>
            </div>
            <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>                       
                    <input class='btn submitAdminBtn' type='submit' name='submit' value='$langSubmit'>                     
                    <a class='btn cancelAdminBtn' href='$cancel_url'>$langCancel</a>                    
                </div>
            </div>
	</form>
    </div></div>
    <div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
</div>";
}
draw($tool_content, 2, null, $head_content);
