<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * @file forumPosts.php
 * @brief get latest 10 forum posts from users courses
 */

/**
 * @brief display forum posts from users courses
 * @global type $langNoPosts
 * @global type $langMore
 * @global type $urlServer
 * @param type $param
 * @param type $type
 * @return string
 */
function getUserForumPosts($param) {
    
    global $langNoPosts, $langMore, $urlServer;
        
    $lesson_id = $param['lesson_id'];   
    $last_month = strftime('%Y %m %d', strtotime('now -1 month'));
    
    $found = false;
    $forum_content = '<table width="100%">';
    foreach ($lesson_id as $lid) {
        $q = Database::get()->queryArray("SELECT forum.id AS forumid,
                                                 forum.name,
                                                 forum_topic.id AS topicid,
                                                 forum_topic.title,                                                 
                                                 forum_post.post_time,
                                                 forum_post.poster_id,
                                                 forum_post.post_text
                                         FROM forum, forum_topic, forum_post, course_module
                                         WHERE CONCAT(forum_topic.title, forum_post.post_text) != ''
                                                 AND forum.id = forum_topic.forum_id
                                                 AND forum_post.topic_id = forum_topic.id
                                                 AND forum.course_id = ?d
                                                 AND DATE_FORMAT(forum_post.post_time, '%Y %m %d') >= ?t
                                                 AND course_module.visible = 1
                                                 AND course_module.module_id = " . MODULE_ID_FORUM . "
                                                 AND course_module.course_id = ?d
                                         ORDER BY forum_post.post_time LIMIT 10", $lid, $last_month, $lid);
        if ($q) {
            $found = true;
            $forum_content .= "<tr><td class='sub_title1'>" . q(ellipsize(course_id_to_title($lid), 70)) . "</td></tr>";
            foreach ($q as $data) {
                $url = $urlServer . "modules/forum/viewtopic.php?course=" . course_id_to_code($lid) . "&amp;topic=" . $data->topicid . "&amp;forum=" . $data->forumid;
                $forum_content .= "<tr><td><ul class='custom_list'><li><a href='$url'>
				<b>" . q($data->title) . " (" . nice_format(date("Y-m-d", strtotime($data->post_time))) . ")</b>
                                </a><div class='smaller grey'><b>" . q(uid_to_name($data->poster_id)) .
                        "</b></div><div class='smaller'>" .
                        standard_text_escape(ellipsize_html($data->post_text, 150, "<b>&nbsp;...<a href='$url'>[$langMore]</a></b>")) .
                        "</div></li></ul></td></tr>";
            }
        }
    }
    $forum_content .= "</table>";
  
    if ($found) {
        return $forum_content; 
    } else {
        return "<p class='alert1'>$langNoPosts</p>";
    }
    
}