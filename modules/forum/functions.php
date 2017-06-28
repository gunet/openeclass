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
 * Return the total number of topics in a forum
 */

define('POSTS_PER_PAGE', 20);
define('TOPICS_PER_PAGE', 10);
define('HOT_THRESHOLD', 20);
define('PAGINATION_CONTEXT', 3);

require_once 'modules/progress/ForumTopicEvent.php';

function get_total_topics($forum_id) {
    return Database::get()->querySingle("SELECT COUNT(*) AS total FROM forum_topic WHERE forum_id = ?d", $forum_id)->total;
}

/*
 * Return the total number of posts in forum or topic
 */

function get_total_posts($id) {
    return Database::get()->querySingle("SELECT COUNT(*) AS total FROM forum_post WHERE topic_id = ?d", $id)->total;
}

/*
 * Return the most recent post in a forum
 */

function get_last_post($topic_id) {
    return Database::get()->querySingle("SELECT post_time FROM forum_post
                WHERE topic_id = ?d                
                ORDER BY post_time DESC LIMIT 1", $topic_id)->post_time;
}

/*
 * Checks if a forum or a topic exists in the database. Used to prevent
 * users from simply editing the URL to post to a non-existant forum or topic
 */

function does_exists($id, $type) {

    global $course_id;
    switch ($type) {
        case 'forum':
            $sql = Database::get()->querySingle("SELECT id FROM forum
                                WHERE id = ?d
                                AND course_id = ?d", $id, $course_id);
            break;
        case 'topic':
            $sql = Database::get()->querySingle("SELECT id FROM forum_topic
                                WHERE id = ?d", $id);
            break;
    }
    if (!$sql)
        return 0;
    else
        return 1;
}

/*
 * Check if this is the first post in a topic. Used in editpost.php
 */

function is_first_post($topic_id, $post_id) {

    $sql = Database::get()->querySingle("SELECT id FROM forum_post
                WHERE topic_id = ?d
                ORDER BY id LIMIT 1", $topic_id);
    if (!$sql) {
        return(0);
    }
    if ($sql->id == $post_id) {
        return(1);
    } else {
        return(0);
    }
}

// display notification status of link
function toggle_link($notify) {

    if ($notify == TRUE) {
        return FALSE;
    } elseif ($notify == FALSE) {
        return TRUE;
    }
}

// display notification status of link and icon
function toggle_icon($notify) {

    if ($notify == TRUE) {
        return '_on';
    } elseif ($notify == FALSE) {
        return '_off';
    }
}

// returns a category id from a forum id
function forum_category($id) {

    global $course_id;

    $r = Database::get()->querySingle("SELECT cat_id FROM forum
                    WHERE id = ?d
                    AND course_id = ?d", $id, $course_id);
    if ($r) {
        return $r->cat_id;
    } else {
        return FALSE;
    }
}

// returns a category name from a category id
function category_name($id) {

    global $course_id;

    $r = Database::get()->querySingle("SELECT cat_title FROM forum_category
                    WHERE id = ?d
                    AND course_id = ?d", $id, $course_id);
    if ($r) {
        return $r->cat_title;
    } else {
        return FALSE;
    }
}

function init_forum_group_info($forum_id) {
    global $course_id, $group_id, $can_post, $is_member, $is_editor;

    $q = Database::get()->querySingle("SELECT id FROM `group`
			WHERE course_id = ?d AND forum_id = ?d", $course_id, $forum_id);
    if ($q) {
        $group_id = $q->id;
        initialize_group_info($group_id);
    } else {
        $group_id = false;
    }
    if (!$group_id or $is_member or $is_editor) {
        $can_post = true;
    } else {
        $can_post = false;
    }
    return $group_id;
}

function add_topic_link($pagenr, $total_reply_pages) {

    global $pagination, $topiclink;

    $start = $pagenr * POSTS_PER_PAGE;
    $pagenr++;
    $pagination .= "<a href='$topiclink&amp;start=$start'>$pagenr</a>" .
            (($pagenr < $total_reply_pages) ? "<span class='page-sep'>,&nbsp;</span>" : '');
}

/**
 * @brief Send an e-mail notification for new messages to subscribed users
 * @global type $logo
 * @global type $langNewForumNotify
 * @global type $course_code 
 * @global type $course_id
 * @global type $langForumFrom
 * @global type $uid
 * @global type $langBodyForumNotify
 * @global type $langInForums
 * @global type $urlServer
 * @global type $langdate
 * @global type $langSender
 * @global type $langCourse
 * @global type $langCategory
 * @global type $langForum
 * @global type $langSubject
 * @global type $langNote
 * @global type $langLinkUnsubscribe
 * @global type $langHere
 * @global type $charset
 * @global type $langMailBody
 * @global type $langMailSubject
 * @param type $forum_id
 * @param type $forum_name
 * @param type $topic_id
 * @param type $subject
 * @param type $message
 * @param type $topic_date
 */
function notify_users($forum_id, $forum_name, $topic_id, $subject, $message, $topic_date) {
    global $logo, $langNewForumNotify, $course_code, $course_id, $langForumFrom,
        $uid, $langBodyForumNotify, $langInForums, $urlServer, $langdate, $langSender,
        $langCourse, $langCategory, $langForum, $langSubject, $langNote,
        $langLinkUnsubscribe, $langHere, $charset, $langMailBody, $langMailSubject;

    $subject_notify = "$logo - $langNewForumNotify";
    $category_id = forum_category($forum_id);
    $cat_name = category_name($category_id);    
    $name = uid_to_name($uid);
    $title = course_id_to_title($course_id);

    $header_html_topic_notify = "<!-- Header Section -->
    <div id='mail-header'>
        <br>
        <div>
            <div id='header-title'>$langBodyForumNotify <a href='{$urlServer}courses/$course_code'>".q($title)."</a>.</div>
            <ul id='forum-category'>
                <li><span><b>$langCategory:</b></span> <span>" . q($cat_name) . "</span></li>
                <li><span><b>$langForum:</b></span> <span><a href='{$urlServer}modules/forum/viewforum.php?course=$course_code&amp;forum=$forum_id'>" . q($forum_name) . "</a></span></li>
                <li><span><b>$langForumFrom :</b></span> <span>$name</span></li>
                <li><span><b>$langdate:</b></span> <span> $topic_date </span></li>
            </ul>
        </div>
    </div>";
    
    $body_html_topic_notify = "<!-- Body Section -->
    <div id='mail-body'>
        <br>
        <div><b>$langMailSubject</b> <span class='left-space'><a href='{$urlServer}modules/forum/viewforum.php?course=$course_code&amp;forum=$forum_id&amp;topic=$topic_id'>" . q($subject) . "</a></span></div><br>
        <div><b>$langMailBody</b></div>
        <div id='mail-body-inner'>
            $message
        </div>
    </div>";

    $footer_html_topic_notify = "<!-- Footer Section -->
    <div id='mail-footer'>
        <br>
        <div>
            <small>" . sprintf($langLinkUnsubscribe, q($title)) ." <a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a></small>
        </div>
    </div>";

    $html_topic_notify = $header_html_topic_notify.$body_html_topic_notify.$footer_html_topic_notify;

    $plain_message = html2text($html_topic_notify);
    $plain_topic_notify = "$langBodyForumNotify $langInForums\n" .
       "$langSender: $name\n" .
       "$langCourse: $title\n    {$urlServer}courses/$course_code/\n" .
       "$langCategory: $cat_name\n" .
       "$langForum: $forum_name\n    {$urlServer}modules/forum/viewforum.php?course=$course_code&forum=$forum_id\n" . 
       "$langSubject: $subject\n    {$urlServer}modules/forum/viewforum.php?course=$course_code&forum=$forum_id&topic=$topic_id\n" . 
       "--------------------------------------------\n$plain_message\n" .
       "--------------------------------------------\n" .
       "$langNote: " . canonicalize_whitespace(str_replace('<br />', "\n", sprintf($langLinkUnsubscribe, q($title)))) .
       " $langHere:\n${urlServer}main/profile/emailunsubscribe.php?cid=$course_id\n";

       if (setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS)) { // first lookup for course setting
           $users = Database::get()->queryArray("SELECT cu.user_id FROM course_user cu
                                                    JOIN user u ON cu.user_id=u.id
                                                WHERE cu.course_id = ?d
                                                AND u.email <> ''
                                                AND u.email IS NOT NULL", $course_id);
       } else { // if it's not set lookup user setting           
            $users = Database::get()->queryArray("SELECT DISTINCT user_id FROM forum_notify
			WHERE (forum_id = ?d OR cat_id = ?d)
			AND notify_sent = 1 AND course_id = ?d AND user_id != ?d", $forum_id, $category_id, $course_id, $uid);
       }       
    $email = array();
    foreach ($users as $user) {
        if (get_user_email_notification($user->user_id, $course_id)) {
            $useremail = uid_to_email($user->user_id);
            if (Swift_Validate::email($useremail)) { // if email is valid
                $email[] = $useremail;
            }
        }
    }    
    send_mail_multipart('', '', '', $email, $subject_notify, $plain_topic_notify, $html_topic_notify);
}


function triggerGame($courseId, $uid, $eventName, $topicId) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = ForumTopicEvent::ACTIVITY;
    $eventData->module = MODULE_ID_FORUM;
    $eventData->resource = intval($topicId);

    ForumTopicEvent::trigger($eventName, $eventData);
}