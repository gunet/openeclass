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


$require_current_course = TRUE;
$require_login = TRUE;
$require_help = FALSE;
$require_editor = TRUE;
require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'modules/search/indexer.class.php';

if (isset($_REQUEST['forum'])) {
    $forum_id = intval($_REQUEST['forum']);
} else {
    header("Location: index.php?course=$course_code");
    exit();
}
if (isset($_REQUEST['topic'])) {
    $topic_id = intval($_REQUEST['topic']);
}
if (isset($_REQUEST['post_id'])) {
    $post_id = intval($_REQUEST['post_id']);
}
if (isset($_POST['submit'])) {
    $message = $_POST['message'];

    $result = Database::get()->query("UPDATE forum_post SET post_text = ?s
                        WHERE id = ?d", purify($message), $post_id);
    if (!$result) {
        $tool_content .= $langUnableUpdatePost;
        draw($tool_content, 2, null, $head_content);
        exit();
    }
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_FORUMPOST, $post_id);

    if (isset($_POST['subject'])) {
        $subject = $_POST['subject'];
        $result = Database::get()->query("UPDATE forum_topic
                                SET title = ?s
                        WHERE id = ?d", trim($subject), $topic_id);
        if (!$result) {
            $tool_content .= $langUnableUpdateTopic;
            draw($tool_content, 2, null, $head_content);
            exit();
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_FORUMTOPIC, $topic_id);
    }
    header("Location: {$urlServer}modules/forum/viewtopic.php?course=$course_code&topic=$topic_id&forum=$forum_id");
    exit;
} else {
    $myrow = Database::get()->querySingle("SELECT f.name, t.title
                        FROM forum f, forum_topic t
                        WHERE f.id = ?d
                                AND t.id = ?d
                                AND t.forum_id = f.id", $forum_id, $topic_id);

    if (!$myrow) {
        $tool_content .= $langTopicInformation;
        draw($tool_content, 2, null, $head_content);
        exit();
    }

    $pageName = $langReplyEdit;
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
    $navigation[] = array('url' => "viewforum.php?course=$course_code&amp;forum=$forum_id", 'name' => q($myrow->name));
    $navigation[] = array('url' => "viewtopic.php?course=$course_code&amp;topic=$topic_id&amp;forum=$forum_id", 'name' => q($myrow->title));

    $tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "viewtopic.php?course=$course_code&topic=$topic_id&forum=$forum_id",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));

    $myrow = Database::get()->querySingle("SELECT p.post_text, p.post_time, t.title
                        FROM forum_post p, forum_topic t
                        WHERE p.id = ?d
                        AND p.topic_id = t.id", $post_id);
    $message = str_replace('{', '&#123;', $myrow->post_text);
    list($day, $time) = explode(' ', $myrow->post_time);
    $first_post = is_first_post($topic_id, $post_id);
    $subject_field = '';
    if ($first_post) {
        $subject_field .= "
                    <div class='form-group'>
                        <label for='title' class='col-sm-2 control-label'>$langSubject:</label>
                        <div class='col-sm-10'>
                            <input type='text' name='subject' size='53' maxlength='100' value='" . q($myrow->title) . "'  class='form-control'>
                        </div>
                    </div>";            
    }    
    $tool_content .= "
        <div class='form-wrapper'>
            <form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
            <input type='hidden' name='post_id' value='$post_id'>
            <input type='hidden' name='topic' value='$topic_id'>
            <input type='hidden' name='forum' value='$forum_id'>
            <fieldset>
                $subject_field
                <div class='form-group'>
                    <label for='title' class='col-sm-2 control-label'>$langBodyMessage:</label>
                    <div class='col-sm-10'>
                        " . rich_text_editor('message', 10, 50, $message) . "
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
                        <a class='btn btn-default' href='viewtopic.php?course=$course_code&topic=$topic_id&forum=$forum_id'>$langCancel</a>
                    </div>
                </div>                               
            </fieldset>
            </form>
        </div>";
}
draw($tool_content, 2, null, $head_content);
