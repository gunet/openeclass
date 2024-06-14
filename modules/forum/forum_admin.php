<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

/**
 * @file forum_admin.php
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'forum';
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/course_settings.php';
require_once 'functions.php';
require_once 'modules/search/indexer.class.php';

$forum_id = isset($_REQUEST['forum_id']) ? intval($_REQUEST['forum_id']) : '';
$cat_id = isset($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : '';

$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
    var pass=true;
    if (document.images) {
        for (i=0;i<which.length;i++) {
            var tempobj=which.elements[i];
            if (tempobj.name == entry) {
                if (tempobj.type=="text"&&tempobj.value=='') {
                    pass=false;
                    break;
                }
            }
        }
    }
    if (!pass) {
        alert("$langEmptyCat");
        return false;
    } else {
        return true;
    }
}
</script>
hContent;
$toolName = $langForums;
$pageName = $langCategoryAdd;
if (isset($_GET['forumcatedit'])) {
    $pageName = $langModCatName;
}
if (isset($_GET['forumcatdel'])) {
    $pageName = $langCatForumAdmin;
}
if (isset($_GET['forumgo'])) {
    $pageName = $langAdd;
}
if (isset($_GET['forumgoedit'])) {
    $pageName = $langChangeForum;
}
if (isset($_GET['forumgodel'])) {
    $pageName = $langDelete;
}
if (isset($_GET['forumtopicedit'])) {
    $pageName = $langChangeTopicForum;
}
if (isset($_GET['settings'])) {
    $pageName = $langConfig;
}

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
// forum go
if (isset($_GET['forumgo'])) {
    $ctg = category_name($cat_id);
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumgoadd=yes&amp;cat_id=$cat_id' method='post' onsubmit=\"return checkrequired(this,'forum_name');\">
        <fieldset>
            <div class='form-group'>
                <label for='cat_title' class='col-sm-6 control-label-notes'>$langCategory</label>
                <div class='col-sm-12'>
                    <input type='text' class='form-control' id='cat_title' value='$ctg' disabled>
                </div>
            </div>
            <div class='form-group mt-4'>
                <label for='forum_name' class='col-sm-6 control-label-notes'>$langForName</label>
                <div class='col-sm-12'>
                    <input type='text' class='form-control' name='forum_name' id='forum_name'>
                </div>
            </div>
            <div class='form-group mt-4'>
                <label for='forum_desc' class='col-sm-6 control-label-notes'>$langDescription</label>
                <div class='col-sm-12'>
                    <textarea class='form-control' name='forum_desc' id='forum_desc' rows='3'></textarea>
                </div>
            </div>
            <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                    <input class='btn submitAdminBtn' type='submit' value='$langAdd'>
                    <a href='index.php?course=$course_code' class='btn cancelAdminBtn'>$langCancel</a>
                </div>
            </div>
        </fieldset>
        </form></div></div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
    </div>";
}
// forum go edit
elseif (isset($_GET['forumgoedit'])) {
    $result = Database::get()->querySingle("SELECT id, name, `desc`, cat_id
                                        FROM forum
                                        WHERE id = ?d
                                        AND course_id = ?d", $forum_id, $course_id);
    $forum_id = $result->id;
    $forum_name = $result->name;
    $forum_desc = $result->desc;
    $cat_id_1 = $result->cat_id;
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
                <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumgosave=yes&amp;cat_id=$cat_id' method='post' onsubmit=\"return checkrequired(this,'forum_name');\">
                <input type='hidden' name='forum_id' value='$forum_id'>
                <fieldset>
                <div class='form-group'>
                    <label for='forum_name' class='col-sm-6 control-label-notes'>$langForName</label>
                    <div class='col-sm-12'>
                        <input name='forum_name' type='text' class='form-control' id='forum_name' value='" . q($forum_name) . "'>
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='forum_desc' class='col-sm-6 control-label-notes'>$langDescription</label>
                    <div class='col-sm-12'>
                        <textarea name='forum_desc' id='forum_desc' class='form-control' cols='47' rows='3'>" . q($forum_desc) . "</textarea>
                    </div>
                </div>
                <div class='form-group mt-4'>";
    $result = Database::get()->querySingle("SELECT COUNT(*) as c FROM `group` WHERE `forum_id` = ?d", $forum_id);
    if ($result->c == 0) {//group forums cannot change category
        $tool_content .= "
                    <label for='cat_id' class='col-sm-6 control-label-notes'>$langChangeCat</label>
                    <div class='col-sm-12'>
                    <select name='cat_id' id='cat_id' class='form-select'>";
        $result = Database::get()->queryArray("SELECT `id`, `cat_title` FROM `forum_category` WHERE `course_id` = ?d AND `cat_order` <> ?d", $course_id, -1);
        //cat_order <> -1: temp solution to exclude group categories and avoid triple join
        foreach ($result as $result_row) {
            $cat_id = $result_row->id;
            $cat_title = $result_row->cat_title;
            if ($cat_id == $cat_id_1) {
                $tool_content .= "<option value='$cat_id' selected>$cat_title</option>";
            } else {
                $tool_content .= "<option value='$cat_id'>$cat_title</option>";
            }
        }
        $tool_content .= "</select></div>";
    }
    $tool_content .= "
       </div>
        <div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                <input class='btn submitAdminBtn' type='submit' value='$langModify'>
                <a class='btn cancelAdminBtn' href='index.php?course=$course_code'>$langCancel</a>
            </div>
        </div>
        </fieldset>
        </form></div></div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
    </div>";
}

// edit forum category
elseif (isset($_GET['forumcatedit'])) {
    $result = Database::get()->querySingle("SELECT id, cat_title FROM forum_category
                                WHERE id = ?d
                                AND course_id = ?d", $cat_id, $course_id);
    $cat_id = $result->id;
    $cat_title = $result->cat_title;
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumcatsave=yes' method='post' onsubmit=\"return checkrequired(this,'cat_title');\">
        <input type='hidden' name='cat_id' value='$cat_id'>
        <fieldset>
        <div class='form-group'>
            <label for='cat_title' class='col-sm-6 control-label-notes'>$langCategory</label>
            <div class='col-sm-12'>
                <input name='cat_title' type='text' class='form-control' id='cat_title' placeholder='$langCategory' value='$cat_title'>
            </div>
        </div>
        <div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                <input class='btn submitAdminBtn' type='submit' value='$langModify'>
                <a class='btn cancelAdminBtn' href='index.php?course=$course_code'>$langCancel</a>
            </div>
        </div>
        </fieldset>
        </form></div></div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
    </div>";
}

// Save forum category
elseif (isset($_GET['forumcatsave'])) {

    Database::get()->query("UPDATE forum_category SET cat_title = ?s
                                        WHERE id = ?d AND course_id = ?d", $_POST['cat_title'], $cat_id, $course_id);
    Session::flash('message',$langNameCatMod);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/forum/index.php?course=$course_code");
}
// Save forum
elseif (isset($_GET['forumgosave'])) {
    Database::get()->query("UPDATE forum SET name = ?s,
                                   `desc` = ?s,
                                   cat_id = ?d
                                WHERE id = ?d
                                AND course_id = ?d"
            , $_POST['forum_name'], purify($_POST['forum_desc']), $cat_id, $forum_id, $course_id);
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_FORUM, $forum_id);
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langForumDataChanged</span></div></div>";
}

// Add category to forums
elseif (isset($_GET['forumcatadd'])) {
    Database::get()->query("INSERT INTO forum_category
                        SET cat_title = ?s,
                        course_id = ?d", $_POST['categories'], $course_id);
    Session::flash('message',$langCatAdded);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/forum/index.php?course=$course_code");
}

// forum go add
elseif (isset($_GET['forumgoadd'])) {
    $ctg = category_name($cat_id);
    $title = course_id_to_title($course_id);
    $forid = Database::get()->query("INSERT INTO forum (name, `desc`, cat_id, course_id)
                                VALUES (?s, ?s, ?d, ?d)"
            , $_POST['forum_name'], $_POST['forum_desc'], $cat_id, $course_id)->lastInsertID;
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_FORUM, $forid);
    // --------------------------------
    // notify users
    // --------------------------------
    $subject_notify = "$logo - $langCatNotify";
    $body_topic_notify = "$langBodyCatNotify $langInCat '$ctg' \n\n$gunet";

    if (setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS)) { // first lookup for course setting
           $sql = Database::get()->queryArray("SELECT cu.user_id FROM course_user cu
                                                    JOIN user u ON cu.user_id=u.id
                                                WHERE cu.course_id = ?d
                                                AND u.email <> ''
                                                AND u.email IS NOT NULL", $course_id);
       } else { // if it's not set lookup user setting
            $sql = Database::get()->queryArray("SELECT DISTINCT user_id FROM forum_notify
                                WHERE cat_id = ?d AND
                                        notify_sent = 1 AND
                                        course_id = ?d AND
                                        user_id <> ?d"
            , $cat_id, $course_id, $uid);
       }
    foreach ($sql as $r) {
        $emailaddr = uid_to_email($r->user_id);
        if (valid_email($emailaddr) and get_user_email_notification($r->user_id, $course_id)) {
            $linkhere = "&nbsp;<a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
            $unsubscribe = "<br /><br />$langNote: " . sprintf($langLinkUnsubscribe, $title);
            $body_topic_notify .= $unsubscribe . $linkhere;


            $header_html_topic_notify = "<!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <div id='header-title'>$subject_notify</div>
                </div>
            </div>";

            $body_html_topic_notify = "<!-- Body Section -->
            <div id='mail-body'>
                <br>
                <div><b>$langMailBody</b></div>
                <div id='mail-body-inner'>
                    $langBodyCatNotify $langInCat '$ctg' $gunet.
                </div>
            </div>";

            $footer_html_topic_notify = "<!-- Footer Section -->
            <div id='mail-footer'>
                <br>
                <div>
                    <small>" . sprintf($langLinkUnsubscribe, q($title)) ." <a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a></small>
                </div>
            </div>";

            $html_topic_notify = $header_html_topic_notify.$body_html_topic_notify.$footer_html_topic_notify;

            $plain_message = html2text($html_topic_notify);

            send_mail_multipart('', '', '', $emailaddr, $subject_notify, $plain_message, $html_topic_notify);
        }
    }
    // end of notification
    Session::flash('message',$langForumCategoryAdded);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/forum/index.php?course=$course_code");
}

// delete forum category
elseif (isset($_GET['forumcatdel'])) {
    $result = Database::get()->queryArray("SELECT id FROM forum WHERE cat_id = ?d AND course_id = ?d", $cat_id, $course_id);
    foreach ($result as $result_row) {
        $forum_id = $result_row->id;
        $result2 = Database::get()->queryArray("SELECT id FROM forum_topic WHERE forum_id = ?d", $forum_id);
        foreach ($result2 as $result_row2) {
            $topic_id = $result_row2->id;
            $post_authors = Database::get()->queryArray("SELECT DISTINCT poster_id FROM forum_post WHERE topic_id = ?d", $topic_id);
            //delete forum posts ratings first
            Database::get()->query("DELETE rating FROM rating INNER JOIN forum_post on rating.rid = forum_post.id
                                    WHERE rating.rtype = ?s AND forum_post.topic_id = ?d", 'forum_post', $topic_id);
            Database::get()->query("DELETE rating_cache FROM rating_cache INNER JOIN forum_post on rating_cache.rid = forum_post.id
                                    WHERE rating_cache.rtype = ?s AND forum_post.topic_id = ?d", 'forum_post', $topic_id);
            //delete abuse reports for forum posts belonging to this topic
            $res = Database::get()->queryArray("SELECT abuse_report.*, forum_post.post_text FROM abuse_report INNER JOIN forum_post ON abuse_report.rid = forum_post.id
                                    WHERE abuse_report.rtype = ?s AND forum_post.topic_id = ?d", 'forum_post', $topic_id);
            foreach ($res as $r) {
                Log::record($r->course_id, MODULE_ID_ABUSE_REPORT, LOG_DELETE,
                    array('id' => $r->id,
                          'user_id' => $r->user_id,
                          'reason' => $r->reason,
                          'message' => $r->message,
                          'rtype' => 'forum_post',
                          'rid' => $r->rid,
                          'rcontent' => $r->post_text,
                          'status' => $r->status
                ));
            }
            Database::get()->query("DELETE abuse_report FROM abuse_report INNER JOIN forum_post ON abuse_report.rid = forum_post.id
                                    WHERE abuse_report.rtype = ?s AND forum_post.topic_id = ?d", 'forum_post', $topic_id);
            Database::get()->query("DELETE FROM forum_post WHERE topic_id = ?d", $topic_id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVEBYTOPIC, Indexer::RESOURCE_FORUMPOST, $topic_id);

            foreach ($post_authors as $author) {
                $forum_user_stats = Database::get()->querySingle("SELECT COUNT(*) as c FROM forum_post
                        INNER JOIN forum_topic ON forum_post.topic_id = forum_topic.id
                        INNER JOIN forum ON forum.id = forum_topic.forum_id
                        WHERE forum_post.poster_id = ?d AND forum.course_id = ?d", $author, $course_id);
                Database::get()->query("DELETE FROM forum_user_stats WHERE user_id = ?d AND course_id = ?d", $author, $course_id);
                if ($forum_user_stats->c != 0) {
                    Database::get()->query("INSERT INTO forum_user_stats (user_id, num_posts, course_id) VALUES (?d,?d,?d)", $author, $forum_user_stats->c, $course_id);
                }
            }
        }
        Database::get()->query("DELETE FROM forum_topic WHERE forum_id = ?d", $forum_id);
        Indexer::queueAsync(Indexer::REQUEST_REMOVEBYFORUM, Indexer::RESOURCE_FORUMTOPIC, $forum_id);
        Database::get()->query("DELETE FROM forum_notify WHERE forum_id = ?d AND course_id = ?d", $forum_id, $course_id);
        Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_FORUM, $forum_id);
    }
    Database::get()->query("DELETE FROM forum WHERE cat_id = ?d AND course_id = ?d", $cat_id, $course_id);
    Database::get()->query("DELETE FROM forum_notify WHERE cat_id = ?d AND course_id = ?d", $cat_id, $course_id);
    Database::get()->query("DELETE FROM forum_category WHERE id = ?d AND course_id = ?d", $cat_id, $course_id);

    Session::flash('message',$langCatForumDelete);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/forum/index.php?course=$course_code");
}

// delete forum
elseif (isset($_GET['forumgodel'])) {
    $result = Database::get()->queryArray("SELECT id FROM forum WHERE id = ?d AND course_id = ?d", $forum_id, $course_id);
    foreach ($result as $result_row) {
        $forum_id = $result_row->id;
        $result2 = Database::get()->queryArray("SELECT id FROM forum_topic WHERE forum_id = ?d", $forum_id);
        foreach ($result2 as $result_row2) {
            $topic_id = $result_row2->id;
            $post_authors = Database::get()->queryArray("SELECT DISTINCT poster_id FROM forum_post WHERE topic_id = ?d", $topic_id);
            //delete abuse reports of posts belonging to the topic
            $res = Database::get()->queryArray("SELECT abuse_report.*, forum_post.post_text FROM abuse_report INNER JOIN forum_post ON abuse_report.rid = forum_post.id
                                    WHERE abuse_report.rtype = ?s AND forum_post.topic_id = ?d", 'forum_post', $topic_id);
            foreach ($res as $r) {
                Log::record($r->course_id, MODULE_ID_ABUSE_REPORT, LOG_DELETE,
                    array('id' => $r->id,
                        'user_id' => $r->user_id,
                        'reason' => $r->reason,
                        'message' => $r->message,
                        'rtype' => 'forum_post',
                        'rid' => $r->rid,
                        'rcontent' => $r->post_text,
                        'status' => $r->status
                ));
            }
            Database::get()->query("DELETE abuse_report FROM abuse_report INNER JOIN forum_post ON abuse_report.rid = forum_post.id
                                    WHERE abuse_report.rtype = ?s AND forum_post.topic_id = ?d", 'forum_post', $topic_id);
            //delete forum posts ratings first
            Database::get()->query("DELETE rating FROM rating INNER JOIN forum_post on rating.rid = forum_post.id
                                    WHERE rating.rtype = ?s AND forum_post.topic_id = ?d", 'forum_post', $topic_id);
            Database::get()->query("DELETE rating_cache FROM rating_cache INNER JOIN forum_post on rating_cache.rid = forum_post.id
                                    WHERE rating_cache.rtype = ?s AND forum_post.topic_id = ?d", 'forum_post', $topic_id);
            Database::get()->query("DELETE FROM forum_post WHERE topic_id = ?d", $topic_id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVEBYTOPIC, Indexer::RESOURCE_FORUMPOST, $topic_id);

            foreach ($post_authors as $author) {
                $forum_user_stats = Database::get()->querySingle("SELECT COUNT(*) as c FROM forum_post
                        INNER JOIN forum_topic ON forum_post.topic_id = forum_topic.id
                        INNER JOIN forum ON forum.id = forum_topic.forum_id
                        WHERE forum_post.poster_id = ?d AND forum.course_id = ?d", $author, $course_id);
                Database::get()->query("DELETE FROM forum_user_stats WHERE user_id = ?d AND course_id = ?d", $author, $course_id);
                if ($forum_user_stats->c != 0) {
                    Database::get()->query("INSERT INTO forum_user_stats (user_id, num_posts, course_id) VALUES (?d,?d,?d)", $author, $forum_user_stats->c, $course_id);
                }
            }
        }
    }
    Database::get()->query("DELETE FROM forum_topic WHERE forum_id = ?d", $forum_id);
    Indexer::queueAsync(Indexer::REQUEST_REMOVEBYFORUM, Indexer::RESOURCE_FORUMTOPIC, $forum_id);
    Database::get()->query("DELETE FROM forum_notify WHERE forum_id = ?d AND course_id = ?d", $forum_id, $course_id);
    Database::get()->query("DELETE FROM forum WHERE id = ?d AND course_id = ?d", $forum_id, $course_id);
    Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_FORUM, $forum_id);
    Database::get()->query("UPDATE `group` SET forum_id = 0
                    WHERE forum_id = ?d
                    AND course_id = ?d", $forum_id, $course_id);

    Session::flash('message',$langForumDelete);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/forum/index.php?course=$course_code");
} elseif (isset($_GET['forumtopicedit'])) {
   $topic_id = intval($_GET['topic_id']);

   $result = Database::get()->querySingle("SELECT `forum_id` FROM `forum_topic` as ft, `forum` as f
           WHERE ft.`id` = ?d AND ft.`forum_id` = f.`id` AND `f`.course_id = ?d ", $topic_id, $course_id);
   if ($result) {
       $current_forum_id = $result->forum_id;

       $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
       <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
       <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumtopicsave=yes&amp;topic_id=$topic_id' method='post'>
       <fieldset>
       <div class='form-group'>
        <div class='col-sm-12'>
        <select name='forum_id' class='form-select'>";
        $result = Database::get()->queryArray("SELECT f.`id` as `forum_id`, f.`name` as `forum_name`, fc.`cat_title` as `cat_title` FROM `forum` AS `f`, `forum_category` AS `fc` WHERE f.`course_id` = ?d AND f.`cat_id` = fc.`id`", $course_id);
        foreach ($result as $result_row) {
           $forum_id = $result_row->forum_id;
           $forum_name = $result_row->forum_name;
           $cat_title = $result_row->cat_title;
           if ($forum_id == $current_forum_id) {
               $tool_content .= "<option value='$forum_id' selected>" . q($forum_name) . " (" . q($cat_title) . ")</option>";
           } else {
               $tool_content .= "<option value='$forum_id'>" . q($forum_name) . " (" . q($cat_title) . ")</option>";
           }
       }
       $tool_content .= "</select></div>
       </div>
       <div class='form-group mt-4'>
            <div class='col-12 d-flex justify-content-end align-items-center>
                <input class='btn submitAdminBtn' type='submit' value='$langModify'>
            </div>
        </div>
       </fieldset>
       </form></div></div>
       <div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
    </div>";
   }
} elseif (isset($_GET['forumtopicsave'])) {
    $topic_id = intval($_GET['topic_id']);
    $new_forum = intval($_POST['forum_id']);

    if (does_exists($topic_id, 'topic')) {//topic belongs to the course and new forum is not a group forum

        $result = Database::get()->querySingle("SELECT `forum_id`, `num_replies`, `last_post_id`  FROM `forum_topic` WHERE `id` = ?d", $topic_id);
        $current_forum_id = $result->forum_id;
        $num_replies = $result->num_replies;
        $last_post_id = $result->last_post_id;

        if ($current_forum_id != $new_forum) {
            Database::get()->query("UPDATE `forum_topic` SET `forum_id` = ?d WHERE `id` = ?d", $new_forum, $topic_id);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_FORUMTOPIC, $topic_id);

            $result = Database::get()->querySingle("SELECT `last_post_id`, MAX(`topic_time`) FROM `forum_topic` WHERE `forum_id`=?d",$new_forum);
            $last_post_id = $result->last_post_id;

            Database::get()->query("UPDATE `forum` SET `num_topics` = `num_topics`+1, `num_posts` = `num_posts`+?d, `last_post_id` = ?d
                    WHERE id = ?d",$num_replies+1, $last_post_id, $new_forum);

            $result = Database::get()->querySingle("SELECT `last_post_id`, MAX(`topic_time`) FROM `forum_topic` WHERE `forum_id`=?d",$current_forum_id);
            if ($result) {
                $last_post_id = $result->last_post_id;
            } else {
                $last_post_id = 0;
            }

            Database::get()->query("UPDATE `forum` SET `num_topics` = `num_topics`-1, `num_posts` = `num_posts`-?d, `last_post_id` = ?d
                    WHERE id = ?d",$num_replies+1,$last_post_id, $current_forum_id);
        }//if user selected the current forum do nothing

       $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langTopicDataChanged</span></div></div>";
    }

} elseif (isset($_GET['settings'])) {
    if (isset($_POST['submitSettings'])) {
        setting_set(SETTING_FORUM_RATING_ENABLE, $_POST['r_radio'], $course_id);
        $message = "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langRegDone</span></div></div>";
    }

    if (isset($message) && $message) {
        $tool_content .= $message . "<br/>";
        unset($message);
    }

    if (setting_get(SETTING_FORUM_RATING_ENABLE, $course_id) == 1) {
        $checkDis = "";
        $checkEn = "checked ";
    } else {
        $checkDis = "checked ";
        $checkEn = "";
    }

    $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;settings=yes' method='post'>
        <fieldset>
        <div class='form-group'>
            <label class='col-sm-12 control-label-notes mb-2'>$langForumPostRating</label>
            <div class='col-sm-9'>
                <div class='radio mb-2'>
                    <label><input type='radio' value='1' name='r_radio' $checkEn/>$langRatingEn</label>
                </div>
                <div class='radio'>
                    <label><input type='radio' value='0' name='r_radio' $checkDis/>$langRatingDis</label>
                </div>
            </div>
        </div>
        <div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-start align-items-center'>
                <input class='btn submitAdminBtn' type='submit' name='submitSettings' value='$langSubmit'>
            </div>
        </div>
        </fieldset>
        </form>
        </div></div>
        <div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
    </div>";
} else {
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumcatadd=yes' method='post' onsubmit=\"return checkrequired(this,'categories');\">
        <fieldset>
        <div class='form-group'>
            <label for='categories' class='col-sm-6 control-label-notes'>$langCategory</label>
            <div class='col-sm-12'>
              <input name='categories' type='text' class='form-control' id='categories' placeholder='$langCategory'>
            </div>
        </div>
        <div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                <input class='btn submitAdminBtn' type='submit' value='$langAdd'>
                <a class='btn cancelAdminBtn' href='index.php?course=$course_code'>$langCancel</a>
            </div>
        </div>
        </fieldset>
        </form></div></div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
    </div>
    </div>";
}
draw($tool_content, 2, null, $head_content);
