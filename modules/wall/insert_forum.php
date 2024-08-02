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

function list_forums($id = NULL) {
    global $urlServer, $course_id, $langComments, $langChoice, $langNoForums, $langForums, $course_code, $langSettingSelect, $langSelect;

    $ret_string = '';
    // select topics from forums (not from group forums)
    $foruminfo = Database::get()->queryArray("SELECT * FROM forum WHERE course_id = ?d
                                                    AND cat_id IN (SELECT id FROM forum_category WHERE cat_order >= 0)", $course_id);
    if (!$foruminfo) {
        $ret_string .= "<div class='col-12 mt-3'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoForums</span></div></div>";
    } else {
        $exist_forum = array();
        $exist_topic = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'forum');
            foreach ($post_res as $exist_res) {
                $exist_forum[] = $exist_res->res_id;
            }
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'topic');
            foreach ($post_res as $exist_res) {
                $exist_topic[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id' />" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<thead><tr class='list-header'>" .
                "<th>$langForums</th>" .
                "<th>$langComments</th>" .
                "<th aria-label='$langSettingSelect'></th>" .
                "</tr></thead>";

        foreach ($foruminfo as $entry) {
            $fchecked = '';
            if (in_array($entry->id, $exist_forum)) {
                $fchecked = 'checked';
            }

            $ret_string .= "<tr>
                <td><a href='{$urlServer}modules/forum/viewforum.php?course=$course_code&amp;forum={$entry->id}'><b>" . q($entry->name) . "</b></a></td>
                <td>" . ($entry->desc? q($entry->desc): '&nbsp;') . "</td>
                <td><label aria-label='$langSelect' class='label-container'><input type='checkbox' $fchecked name='forum[]' value='{$entry->id}'><span class='checkmark'></span></label></td>
              </tr>";

            $r = Database::get()->queryArray("SELECT * FROM forum_topic WHERE forum_id = ?d", $entry->id);
            if (count($r) > 0) { // if forum topics found
                $topicinfo = array();
                foreach ($r as $topicrow) {
                    $topicinfo[] = array(
                        'topic_id' => $topicrow->id,
                        'topic_title' => $topicrow->title,
                        'topic_time' => $topicrow->topic_time);
                }
                foreach ($topicinfo as $topicentry) {
                    $tchecked = '';
                    if (in_array($topicentry['topic_id'], $exist_topic)) {
                        $tchecked = 'checked';
                    }

                    $ret_string .= "<tr>";
                    $ret_string .= "<td>&nbsp;".icon('fa-comments')."&nbsp;&nbsp;<a href='{$urlServer}modules/forum/viewtopic.php?course=$course_code&amp;topic=$topicentry[topic_id]&amp;forum={$entry->id}'>" . q($topicentry['topic_title']) . "</a></td>";
                    $ret_string .= "<td>&nbsp;</td>";
                    $ret_string .= "<td><label aria-label='$langSelect' class='label-container'><input type='checkbox' $tchecked name='forum[]' value='{$entry->id}:$topicentry[topic_id]'><span class='checkmark'></span></label></td>";
                    $ret_string .= "</tr>";
                }
            }
        }
        $ret_string .= "</table></div>";
    }
    return $ret_string;
}
