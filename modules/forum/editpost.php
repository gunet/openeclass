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
require_once 'include/lib/fileDisplayLib.inc.php';

load_js('tools.js');

$head_content .= "
        <script type='text/javascript'>
            $(document).ready(function() {
                $('#filedelete').click(function(e) {
                    var link = $(this).attr('href');                    
                    e.preventDefault();                   

                    bootbox.confirm({ 
                        closeButton: false,
                        title: '<div class=\'icon-modal-default\'><i class=\'fa-regular fa-trash-can fa-xl Accent-200-cl\'></i></div><h3 class=\'modal-title-default text-center mb-0\'>".js_escape($langConfirmDelete)."</h3>',
                        message: '<p class=\'text-center\'>".js_escape($langConfirmDelete)."</p>',
                        buttons: {
                            cancel: {
                                label: '".js_escape($langCancel)."',
                                className: 'cancelAdminBtn position-center'
                            },
                            confirm: {
                                label: '".js_escape($langDelete)."',
                                className: 'deleteAdminBtn position-center',
                            }
                        },
                        callback: function (result) {
                            if(result) {
                                document.location.href = link;     
                            }
                        }
                    });

                });
            });
        </script>";

if (isset($_REQUEST['forum'])) {
    $forum_id = intval($_REQUEST['forum']);
} else {
    header("Location: index.php?course=$course_code");
    exit();
}
if (isset($_REQUEST['topic'])) {
    $topic_id = intval($_REQUEST['topic']);
}

// delete post attachment
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $fp = Database::get()->querySingle("SELECT topic_filepath FROM forum_post WHERE id = ?d", $id);
    unlink("$webDir/courses/$course_code/forum/$fp->topic_filepath");
    Database::get()->query("UPDATE forum_post SET topic_filepath = '', topic_filename = '' WHERE id = ?d", $id);
    Session::flash('message',$langForumAttachmentDeleted);
    Session::flash('alert-class', 'alert-success');
    header("Location: {$urlServer}modules/forum/viewtopic.php?course=$course_code&topic=$topic_id&forum=$forum_id");
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

    $myrow = Database::get()->querySingle("SELECT p.id, p.post_text, p.post_time, p.topic_filepath, p.topic_filename, t.title
                        FROM forum_post p, forum_topic t
                        WHERE p.id = ?d
                        AND p.topic_id = t.id", $post_id);
    $message = str_replace('{', '&#123;', $myrow->post_text);
    list($day, $time) = explode(' ', $myrow->post_time);
    $first_post = is_first_post($topic_id, $post_id);
    $subject_field = $attached_file_content = '';
    if ($first_post) {
        $subject_field .= "
            <div class='form-group'>
                <label for='title' class='col-sm-6 control-label-notes'>$langSubject</label>
                <div class='col-sm-12'>
                    <input type='text' name='subject' size='53' maxlength='100' value='" . q($myrow->title) . "'  class='form-control'>
                </div>
            </div>";
    }

    if (!empty($myrow->topic_filename)) {
        $actual_filename = $webDir . "/courses/" . $course_code . "/forum/" . $myrow->topic_filepath;
        $attached_file_content =
            "<div class='form-group mt-4'>
                <label class='col-sm-6 control-label-notes'>$langAttachedFile</label>
                <div class='col-sm-12'>
                    " .q($myrow->topic_filename) ." (" . format_file_size(filesize($actual_filename)) . ") <a id='filedelete' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;topic=$topic_id&amp;forum=$forum_id&amp;delete=$myrow->id'>
                        <span class='fa-solid fa-xmark fa-fw text-danger' data-original-title='$langDeleteAttachment' title='' data-toggle='tooltip'></span>
                    </a>
                </div>                        
            </div>";
    }


    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
            <form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
                <input type='hidden' name='post_id' value='$post_id'>
                <input type='hidden' name='topic' value='$topic_id'>
                <input type='hidden' name='forum' value='$forum_id'>            
                $subject_field
                $attached_file_content
                <div class='form-group mt-4'>
                    <label for='title' class='col-sm-6 control-label-notes'>$langBodyMessage</label>
                    <div class='col-sm-12'>
                        " . rich_text_editor('message', 10, 50, $message) . "
                    </div>
                </div>
                <div class='form-group mt-5'>
                    <div class='col-12 d-flex justify-content-end align-items center gap-2'>
                        <input class='btn submitAdminBtn' type='submit' name='submit' value='$langSubmit'>
                        <a class='btn cancelAdminBtn' href='viewtopic.php?course=$course_code&topic=$topic_id&forum=$forum_id'>$langCancel</a>
                    </div>
                </div>
            </form>
        </div></div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
    </div>";
}
draw($tool_content, 2, null, $head_content);
