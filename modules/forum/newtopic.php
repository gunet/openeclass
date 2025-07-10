<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_current_course = true;
$require_login = true;
$require_user_registration = true;
$require_help = false;
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/search/lucene/indexer.class.php';
require_once 'include/log.class.php';
require_once 'functions.php';
require_once 'include/lib/fileUploadLib.inc.php';

$toolName = $langForums;

if (isset($_GET['forum'])) {
    $forum = intval($_GET['forum']);
} else {
    header("Location: index.php?course=$course_code");
    exit();
}
if (isset($_GET['topic'])) {
    $topic = intval($_GET['topic']);
} else {
    $topic = '';
}

$unit = isset($_GET['unit'])? intval($_GET['unit']): null;
$res_type = isset($_GET['res_type']);

$myrow = Database::get()->querySingle("SELECT id, name FROM forum WHERE id = ?d AND course_id = ?d", $forum, $course_id);

$forum_name = $myrow->name;
$forum_id = $myrow->id;
$forumUrl = "viewforum.php?course=$course_code&amp;forum=$forum_id";
if ($unit) {
    $forumUrl = "view.php?course=$course_code&amp;res_type=forum&amp;forum=$forum_id&amp;unit=$unit";
} else if ($res_type) {
    $forumUrl = "view.php?course=$course_code&amp;res_type=forum&amp;forum=$forum_id";
}

$is_member = false;
$group_id = init_forum_group_info($forum_id);

$pageName = $langNewTopic;
if (!add_units_navigation(TRUE)) {
    if (!$res_type) {
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
    } else {
        $navigation[] = array('url' => "../wall/index.php?course=$course_code", 'name' => $langWall);
    }
}
$navigation[] = array('url' => $forumUrl, 'name' => $forum_name);

if (!does_exists($forum_id, "forum")) {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langErrorPost</span></div></div>";
    draw($tool_content, 2);
    exit;
}

if (isset($_POST['submit'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    if (empty($message) or empty($subject)) {
        header("Location: viewforum.php?course=$course_code&forum=$forum_id&empty=true");
        exit;
    }
    $message = purify($message);
    $poster_ip = Log::get_client_ip();
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


    $topic_id = Database::get()->query("INSERT INTO forum_topic (title, poster_id, forum_id, topic_time) VALUES (?s, ?d, ?d, ?t)"
                    , $subject, $uid, $forum_id, $time)->lastInsertID;
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_FORUMTOPIC, $topic_id);

    $post_id = Database::get()->query("INSERT INTO forum_post (topic_id, post_text, poster_id, post_time, poster_ip, topic_filepath, topic_filename) VALUES (?d, ?s, ?d, ?t, ?s, ?s, ?s)"
                    , $topic_id, $message, $uid, $time, $poster_ip, $topic_filepath, $topic_real_filename)->lastInsertID;
    triggerForumGame($course_id, $uid, ForumEvent::NEWPOST);
    triggerTopicGame($course_id, $uid, ForumTopicEvent::NEWPOST, $topic_id);
    triggerForumAnalytics($course_id, $uid, ForumAnalyticsEvent::FORUMEVENT);
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_FORUMPOST, $post_id);

    $forum_user_stats = Database::get()->querySingle("SELECT COUNT(*) as c FROM forum_post
                        INNER JOIN forum_topic ON forum_post.topic_id = forum_topic.id
                        INNER JOIN forum ON forum.id = forum_topic.forum_id
                        WHERE forum_post.poster_id = ?d AND forum.course_id = ?d", $uid, $course_id);
    Database::get()->query("DELETE FROM forum_user_stats WHERE user_id = ?d AND course_id = ?d", $uid, $course_id);
    Database::get()->query("INSERT INTO forum_user_stats (user_id, num_posts, course_id) VALUES (?d,?d,?d)", $uid, $forum_user_stats->c, $course_id);

    Database::get()->query("UPDATE forum_topic
                    SET last_post_id = ?d
                WHERE id = ?d
                AND forum_id = ?d", $post_id, $topic_id, $forum_id);

    Database::get()->query("UPDATE forum
                    SET num_topics = num_topics+1,
                    num_posts = num_posts+1,
                    last_post_id = ?d
		WHERE id = ?d", $post_id, $forum_id);

    $topic = $topic_id;
    $total_forum = get_total_topics($forum_id);
    // subtract 1 because we want the number of replies, not the number of posts.
    $total_topic = get_total_posts($topic) - 1;

    notify_users($forum_id, $forum_name, $topic_id, $subject, $message, $time);

    Session::flash('message',$langTopicStored);
    Session::flash('alert-class', 'alert-success');
    $redirectUrl = $unit?
        "modules/units/view.php?course=$course_code&res_type=forum&forum=$forum_id&unit=$unit":
        "modules/forum/viewforum.php?course=$course_code&forum=$forum_id";
    redirect_to_home_page($redirectUrl);
} else {
    $action = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic&forum=$forum_id";
    if ($unit) {
        $action .= "&amp;unit=$unit&amp;res_type=forum_new_topic";
    } else if ($res_type) {
        $action .= "&amp;res_type=forum_new_topic";
    }
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' action='$action' method='post' enctype='multipart/form-data'>
        <fieldset>
            <legend class='mb-0' aria-label='$langForm'></legend>
            <div class='form-group'>
              <label for='subject' class='col-sm-12 control-label-notes'>$langSubject <span class='asterisk Accent-200-cl'>(*)</span></label>
              <div class='col-sm-12'>
                <input type='text' name='subject' id='subject' class='form-control' maxlength='100'>
              </div>
            </div>
            <div class='form-group mt-4'>
              <label for='message' class='col-sm-12 control-label-notes'>$langBodyMessage</label>
              <div class='col-sm-12'>
                " . rich_text_editor('message', 14, 50, '') . "
              </div>
            </div>
            <div class='form-group mt-4'>
                <label for='topic_file' class='col-sm-12 control-label-notes'>$langAttachedFile</label>
                <div class='col-sm-12'>
                    <input type='file' name='topic_file' id='topic_file' size='35'>
                    " . fileSizeHidenInput() . "
                </div>
            </div>
            <div class='form-group mt-5'>
              <div class='col-12 d-flex justify-content-end align-items-center gap-2'>                                   
                    <input class='btn submitAdminBtn' type='submit' name='submit' value='$langSubmit'>                                    
                    <a class='btn cancelAdminBtn' href='viewforum.php?course=$course_code&forum=$forum_id'>$langCancel</a>                                    
              </div>
            </div>
	</fieldset>
	</form>
    </div></div>
    <div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
</div>";
}
draw($tool_content, 2, null, $head_content);
