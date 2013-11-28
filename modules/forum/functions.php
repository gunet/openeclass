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

function get_total_topics($forum_id) {

    $sql = db_query("SELECT COUNT(*) AS total FROM forum_topic
                        WHERE forum_id = $forum_id");
    $myrow = mysql_fetch_array($sql);

    return $myrow["total"];
}

/*
 * Return the total number of posts in forum or topic
 */

function get_total_posts($id) {

    $sql = db_query("SELECT COUNT(*) AS total FROM forum_post
                WHERE topic_id = $id");
    $myrow = mysql_fetch_array($sql);

    return $myrow["total"];
}

/*
 * Return the most recent post in a forum
 */

function get_last_post($topic_id) {

    $sql = "SELECT post_time FROM forum_post
                WHERE topic_id = $topic_id                
                ORDER BY post_time DESC LIMIT 1";

    $val = db_query_get_single_value($sql);

    return $val;
}

/*
 * Checks if a forum or a topic exists in the database. Used to prevent
 * users from simply editing the URL to post to a non-existant forum or topic
 */

function does_exists($id, $type) {

    global $course_id;
    switch ($type) {
        case 'forum':
            $sql = "SELECT id FROM forum
                                WHERE id = $id
                                AND course_id = $course_id";
            break;
        case 'topic':
            $sql = "SELECT id FROM forum_topic
                                WHERE id = $id";
            break;
    }
    if (!$result = db_query($sql))
        return(0);
    if (!$myrow = mysql_fetch_array($result))
        return(0);
    return(1);
}

/*
 * Check if this is the first post in a topic. Used in editpost.php
 */

function is_first_post($topic_id, $post_id) {

    $sql = "SELECT id FROM forum_post
                WHERE topic_id = $topic_id
                ORDER BY id LIMIT 1";
    if (!$r = db_query($sql)) {
        return(0);
    }
    if (!$m = mysql_fetch_array($r)) {
        return(0);
    }
    if ($m["id"] == $post_id) {
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

    if ($r = mysql_fetch_row(db_query("SELECT cat_id FROM forum
                    WHERE id = $id
                    AND course_id = $course_id"))) {
        return $r[0];
    } else {
        return FALSE;
    }
}

// returns a category name from a category id
function category_name($id) {

    global $course_id;

    if ($r = mysql_fetch_row(db_query("SELECT cat_title FROM forum_category
                    WHERE id = $id
                    AND course_id = $course_id"))) {
        return $r[0];
    } else {
        return FALSE;
    }
}

function init_forum_group_info($forum_id) {
    global $course_id, $group_id, $can_post, $is_member, $is_editor;

    $q = db_query("SELECT id FROM `group`
			WHERE course_id = $course_id AND forum_id = $forum_id");
    if ($q and mysql_num_rows($q) > 0) {
        list($group_id) = mysql_fetch_row($q);
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

    global $pagination, $posts_per_page, $topiclink;

    $start = $pagenr * $posts_per_page;
    $pagenr++;
    $pagination .= "<a href='$topiclink&amp;start=$start'>$pagenr</a>" .
            (($pagenr < $total_reply_pages) ? "<span class='page-sep'>,&nbsp;</span>" : '');
}
