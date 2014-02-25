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



/**
 * Personalised ForumPosts Component, eClass Personalised
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id: forumPosts.php,v 1.34 2011/09/28 13:47:01 jexi Exp $
 * @package eClass Personalised
 *
 * @abstract This component populates the Forum Posts block on the user's personalised
 * interface. It is based on the diploma thesis of Evelthon Prodromou.
 *
 */

/**
 * Function getUserForumPosts
 *
 * Populates an array with data regarding the user's personalised forum posts
 *
 * @param array $param
 * @param string $type (data, html)
 * @return array
 */
function getUserForumPosts($param, $type) {
    global $uid;

    $uid = $param['uid'];
    $lesson_code = $param['lesson_code'];
    $max_repeat_val = $param['max_repeat_val'];
    $lesson_title = $param['lesson_titles'];
    $lesson_code = $param['lesson_code'];
    $lesson_professor = $param['lesson_professor'];

    $last_month = strftime('%Y %m %d', strtotime('now -1 month'));

    $forumPosts = array();

    for ($i = 0; $i < $max_repeat_val; $i++) {
        $forum_query_new = createForumQueries($last_month, $lesson_code[$i]);
        $mysql_query_result = db_query($forum_query_new);

        if ($num_rows = mysql_num_rows($mysql_query_result) > 0) {
            $forumData = array();
            $forumSubData = array();
            $forumContent = array();
            array_push($forumData, $lesson_title[$i]);
            array_push($forumData, $lesson_code[$i]);
        }

        while ($myForumPosts = mysql_fetch_row($mysql_query_result)) {
            if ($myForumPosts) {
                array_push($forumContent, $myForumPosts);
            }
        }
        if ($num_rows > 0) {
            array_push($forumSubData, $forumContent);
            array_push($forumData, $forumSubData);
            array_push($forumPosts, $forumData);
        }
    }

    if ($type == "html") {
        return forumHtmlInterface($forumPosts);
    } elseif ($type == "data") {
        return $forumPosts;
    }
}

/**
 * Function forumHtmlInterface
 *
 * Generates html content for the Forum Posts block of eClass personalised.
 *
 * @param array $data
 * @return string HTML content for the documents block
 * @see function getUserForumPosts()
 */
function forumHtmlInterface($data) {
    global $langNoPosts, $langMore, $urlServer;

    $content = "";
    $numOfLessons = count($data);
    if ($numOfLessons > 0) {
        $content .= "<table width='100%'>";
        for ($i = 0; $i < $numOfLessons; $i++) {
            $content .= "<tr><td class='sub_title1'>" . $data[$i][0] . "</td></tr>";
            $iterator = count($data[$i][2][0]);
            for ($j = 0; $j < $iterator; $j++) {
                $url = $urlServer . "modules/forum/viewtopic.php?course=" . $data[$i][1] . "&amp;topic=" . $data[$i][2][0][$j][2] . "&amp;forum=" . $data[$i][2][0][$j][0] . "&amp;s=" . $data[$i][2][0][$j][4];
                $content .= "<tr><td><ul class='custom_list'><li><a href='$url'>
				<b>" . q($data[$i][2][0][$j][3]) . " (" . nice_format(date("Y-m-d", strtotime($data[$i][2][0][$j][5]))) . ")</b>
                                </a><div class='smaller grey'><b>" . q(uid_to_name($data[$i][2][0][$j][6])) .
                        "</b></div><div class='smaller'>" .
                        standard_text_escape(ellipsize_html($data[$i][2][0][$j][7], 150, "<b>&nbsp;...<a href='$url'>[$langMore]</a></b>")) .
                        "</div></li></ul></td></tr>";
            }
        }
        $content .= "</table>";
    } else {
        $content .= "<p class='alert1'>$langNoPosts</p>";
    }
    return $content;
}

/**
 * Function createForumQueries
 *
 * Creates needed queries used by getUserForumPosts()
 *
 * @param string $dateVar
 * @return string SQL query
 */
function createForumQueries($dateVar, $code) {

    $course_id = course_code_to_id($code);

    $forum_query = 'SELECT forum.id,
                               forum.name,
                               forum_topic.id,
                               forum_topic.title,
                               forum_topic.num_replies,
                               forum_post.post_time,
                               forum_post.poster_id,
                               forum_post.post_text
                        FROM forum, forum_topic, forum_post, course_module
                        WHERE CONCAT(forum_topic.title, forum_post.post_text) != \'\'
                               AND forum.id = forum_topic.forum_id
                               AND forum_post.topic_id = forum_topic.id
                               AND forum.course_id = ' . $course_id . '
                               AND DATE_FORMAT(forum_post.post_time, \'%Y %m %d\') >= "' . $dateVar . '"
                               AND course_module.visible = 1
                               AND course_module.module_id = ' . MODULE_ID_FORUM . '
                               AND course_module.course_id = ' . $course_id . '
                        ORDER BY forum_post.post_time LIMIT 15';

    return $forum_query;
}
