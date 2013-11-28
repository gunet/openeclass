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

$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'For';
require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/search/forumtopicindexer.class.php';
require_once 'modules/search/forumpostindexer.class.php';

$idx = new Indexer();
$ftdx = new ForumTopicIndexer($idx);
$fpdx = new ForumPostIndexer($idx);

if (!add_units_navigation(true)) {
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langForums);
}

require_once 'config.php';
require_once 'functions.php';

if ($is_editor) {
    load_js('tools.js');
}

$paging = true;
$next = 0;
$forum_id = intval($_GET['forum']);
$is_member = false;
$group_id = init_forum_group_info($forum_id);

$result = db_query("SELECT id, name FROM forum WHERE id = $forum_id AND course_id = $course_id");
$myrow = mysql_fetch_array($result);

$forum_name = $myrow['name'];
$forum_id = $myrow['id'];

$nameTools = $forum_name;

if (isset($_GET['empty'])) { // if we come from newtopic.php
    $tool_content .= "<p class='alert1'>$langEmptyNewTopic</p>";
}

if ($can_post) {
    $tool_content .= "
	<div id='operations_container'>
	<ul id='opslist'>
	<li>
	<a href='newtopic.php?course=$course_code&amp;forum=$forum_id'>$langNewTopic</a>
	</li>
	</ul>
	</div>";
}

/*
 * Retrieve and present data from course's forum
 */

$topic_count = mysql_fetch_row(db_query("SELECT num_topics FROM forum
                WHERE id = $forum_id
                AND course_id = $course_id"));
$total_topics = $topic_count[0];

if ($total_topics > $topics_per_page) {
    $pages = intval($total_topics / $topics_per_page) + 1; // get total number of pages
}

if (isset($_GET['start'])) {
    $first_topic = intval($_GET['start']);
} else {
    $first_topic = 0;
}

if ($total_topics > $topics_per_page) { // navigation
    $base_url = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;start=";
    $tool_content .= "<table width='100%'><tr>";
    $tool_content .= "<td width='50%' align='left'><span class='row'><strong class='pagination'>
		<span class='pagination'>$langPages:&nbsp;";
    $current_page = $first_topic / $topics_per_page + 1; // current page
    for ($x = 1; $x <= $pages; $x++) { // display navigation numbers
        if ($current_page == $x) {
            $tool_content .= "$x";
        } else {
            $start = ($x - 1) * $topics_per_page;
            $tool_content .= "<a href='$base_url&amp;start=$start'>$x</a>";
        }
    }
    $tool_content .= "</span></strong></span></td>";
    $tool_content .= "<td colspan='4' align='right'>";

    $next = $first_topic + $topics_per_page;
    $prev = $first_topic - $topics_per_page;
    if ($prev < 0) {
        $prev = 0;
    }

    if ($first_topic == 0) { // beginning
        $tool_content .= "<a href='$base_url$next'>$langNextPage</a>";
    } elseif ($first_topic + $topics_per_page < $total_topics) {
        $tool_content .= "<a href='$base_url$prev'>$langPreviousPage</a>&nbsp|&nbsp;
		<a href='$base_url$next'>$langNextPage</a>";
    } elseif ($start - $topics_per_page < $total_topics) { // end
        $tool_content .= "<a href='$base_url$prev'>$langPreviousPage</a>";
    }
    $tool_content .= "</td></tr></table>";
}

// delete topic
if (($is_editor) and isset($_GET['topicdel'])) {
    if (isset($_GET['topic_id'])) {
        $topic_id = intval($_GET['topic_id']);
    }
    $sql = db_query("SELECT id FROM forum_post WHERE topic_id = $topic_id");
    $number_of_posts = get_total_posts($topic_id);
    while ($r = mysql_fetch_array($sql)) {
        db_query("DELETE FROM forum_post WHERE id = $r[id]");
    }
    $fpdx->removeByTopic($topic_id);
    $number_of_topics = get_total_topics($forum_id);
    $num_topics = $number_of_topics - 1;
    if ($number_of_topics < 0) {
        $num_topics = 0;
    }
    db_query("DELETE FROM forum_topic WHERE id = $topic_id AND forum_id = $forum_id");
    $ftdx->remove($topic_id);
    db_query("UPDATE forum SET num_topics = $num_topics,
                                num_posts = num_posts-$number_of_posts
                            WHERE id = $forum_id
                                AND course_id = $course_id");
    db_query("DELETE FROM forum_notify WHERE topic_id = $topic_id AND course_id = $course_id");
}

// modify topic notification
if (isset($_GET['topicnotify'])) {
    if (isset($_GET['topic_id'])) {
        $topic_id = intval($_GET['topic_id']);
    }
    $rows = mysql_num_rows(db_query("SELECT * FROM forum_notify
		WHERE user_id = $uid AND topic_id = $topic_id AND course_id = $course_id"));
    if ($rows > 0) {
        db_query("UPDATE forum_notify SET notify_sent = " . intval($_GET['topicnotify']) . "
			WHERE user_id = $uid AND topic_id = $topic_id AND course_id = $course_id");
    } else {
        db_query("INSERT INTO forum_notify SET user_id = $uid,
		topic_id = $topic_id, notify_sent = 1, course_id = $course_id");
    }
}

$sql = "SELECT t.*, p.post_time, p.poster_id AS poster_id
        FROM forum_topic t
        LEFT JOIN forum_post p ON t.last_post_id = p.id
        WHERE t.forum_id = $forum_id
        ORDER BY topic_time DESC LIMIT $first_topic, $topics_per_page";

$result = db_query($sql);

if (mysql_num_rows($result) > 0) { // topics found
    $tool_content .= "
	<table width='100%' class='tbl_alt'>
	<tr>
	  <th colspan='2'>&nbsp;$langSubject</th>
	  <th width='70' class='center'>$langAnswers</th>
	  <th width='150' class='center'>$langSender</th>
	  <th width='80' class='center'>$langSeen</th>
	  <th width='200' class='center'>$langLastMsg</th>
	  <th width='70' class='center'>$langActions</th>
	</tr>";
    $i = 0;
    while ($myrow = mysql_fetch_array($result)) {
        if ($i % 2 == 1) {
            $tool_content .= "<tr class='odd'>";
        } else {
            $tool_content .= "<tr class='even'>";
        }
        $replies = $myrow['num_replies'];
        $topic_id = $myrow['id'];
        $last_post_datetime = $myrow['post_time'];
        list($last_post_date, $last_post_time) = explode(' ', $last_post_datetime);
        list($year, $month, $day) = explode("-", $last_post_date);
        list($hour, $min) = explode(":", $last_post_time);
        $last_post_time = mktime($hour, $min, 0, $month, $day, $year);
        if (!isset($last_visit)) {
            $last_visit = 0;
        }
        if ($replies >= $hot_threshold) {
            if ($last_post_time < $last_visit)
                $image = $hot_folder_image;
            else
                $image = $hot_newposts_image;
        } else {
            if ($last_post_time < $last_visit) {
                $image = $folder_image;
            } else {
                $image = $newposts_image;
            }
        }
        $tool_content .= "<td width='1'><img src='$image' /></td>";
        $topic_title = $myrow['title'];
        $pagination = '';
        $topiclink = "viewtopic.php?course=$course_code&amp;topic=$topic_id&amp;forum=$forum_id";
        if ($replies > $posts_per_page) {
            $total_reply_pages = ceil($replies / $posts_per_page);
            $pagination .= "<strong class='pagination'><span>\n<img src='$posticon_more' />";
            add_topic_link(0, $total_reply_pages);
            if ($total_reply_pages > PAGINATION_CONTEXT + 1) {
                $pagination .= "&nbsp;...&nbsp;";
            }
            for ($p = max(1, $total_reply_pages - PAGINATION_CONTEXT); $p < $total_reply_pages; $p++) {
                add_topic_link($p, $total_reply_pages);
            }
            $pagination .= "&nbsp;</span></strong>";
        }
        $tool_content .= "<td><a href='$topiclink'><b>" . q($topic_title) . "</b></a>$pagination</td>";
        $tool_content .= "<td class='center'>$replies</td>";
        $tool_content .= "<td class='center'>" . q(uid_to_name($myrow['poster_id'])) . "</td>";
        $tool_content .= "<td class='center'>$myrow[num_views]</td>";
        $tool_content .= "<td class='center'>" . q(uid_to_name($myrow['poster_id'])) . "<br />$last_post_datetime</td>";
        list($topic_action_notify) = mysql_fetch_row(db_query("SELECT notify_sent FROM forum_notify
			WHERE user_id = $uid AND topic_id = $myrow[id] AND course_id = $course_id", $mysqlMainDb));
        if (!isset($topic_action_notify)) {
            $topic_link_notify = FALSE;
            $topic_icon = '_off';
        } else {
            $topic_link_notify = toggle_link($topic_action_notify);
            $topic_icon = toggle_icon($topic_action_notify);
        }
        $tool_content .= "<td class='center'>";
        if ($is_editor) {
            $tool_content .= "
			<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;topic_id=$myrow[id]&amp;topicdel=yes' onClick=\"return confirmation('$langConfirmDelete');\">
			<img src='$themeimg/delete.png' title='$langDelete' alt='$langDelete' />
			</a>";
        }
        if (isset($_GET['start']) and $_GET['start'] > 0) {
            $tool_content .= "
			<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;start=$_GET[start]&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow[id]'>
			<img src='$themeimg/email$topic_icon.png' title='$langNotify' />
			</a>";
        } else {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forum=$forum_id&amp;topicnotify=$topic_link_notify&amp;topic_id=$myrow[id]'>
			<img src='$themeimg/email$topic_icon.png' title='$langNotify' />
			</a>";
        }
        $tool_content .= "</td>\n</tr>";
        $i++;
    } // end of while
    $tool_content .= "</table>";
} else {
    $tool_content .= "<div class='alert1'>$langNoTopics</div>";
}
draw($tool_content, 2, null, $head_content);
