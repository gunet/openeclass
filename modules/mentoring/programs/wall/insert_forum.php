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
    global $urlServer, $mentoring_program_id, $langComments, $langChoice, $langNoForums, $langForums, $mentoring_program_code, $program_group_id;

    $ret_string = '';
    // select topics from forums (not from group forums)
    $foruminfo = Database::get()->queryArray("SELECT * FROM mentoring_forum WHERE mentoring_program_id = ?d
                                                    AND cat_id IN (SELECT id FROM mentoring_forum_category WHERE cat_order >= 0)
                                                    AND id = ?d", $mentoring_program_id,$program_group_id);
    if (!$foruminfo) {
        $ret_string .= "<div class='col-12 mt-3'><div class='alert alert-warning rounded-2'>$langNoForums</div></div>";
    } else {
        $exist_forum = array();
        $exist_topic = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM mentoring_wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'mentoring_forum');
            foreach ($post_res as $exist_res) {
                $exist_forum[] = $exist_res->res_id;
            }
            $post_res = Database::get()->queryArray("SELECT * FROM mentoring_wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'mentoring_topic');
            foreach ($post_res as $exist_res) {
                $exist_topic[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<form action='insert.php?program=$mentoring_program_code' method='post'>" .
                "<input type='hidden' name='id' value='$id' />" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th>$langForums</th>" .
                "<th>$langComments</th>" .
                "<th style='width:20px;' class='text-center'>$langChoice</th>" .
                "</tr>";

        foreach ($foruminfo as $entry) {
            $fchecked = '';
            if (in_array($entry->id, $exist_forum)) {
                $fchecked = 'checked';
            }

            $ret_string .= "<tr>
                <td><a href='{$urlServer}modules/mentoring/programs/group/forum_group.php?forum_group_id=".getInDirectReference($program_group_id)."'><b>" . q($entry->name) . "</b></a></td>
                <td>" . ($entry->desc? q($entry->desc): '&nbsp;') . "</td>
                <td class='text-center'><label class='label-container'><input type='checkbox' $fchecked name='forum[]' value='{$entry->id}'><span class='checkmark'></span></label></td>
              </tr>";

            $r = Database::get()->queryArray("SELECT * FROM mentoring_forum_topic WHERE forum_id = ?d", $entry->id);
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
                    $ret_string .= "<td>&nbsp;".icon('fa-comments')."&nbsp;&nbsp;<a href='{$urlServer}modules/mentoring/programs/group/viewtopic.php?topic_id=".getInDirectReference($topicentry[topic_id])."&amp;forum_id=".getInDirectReference($entry->id)."&amp;group_id=".getInDirectReference($program_group_id)."'>" . q($topicentry['topic_title']) . "</a></td>";
                    $ret_string .= "<td>&nbsp;</td>";
                    $ret_string .= "<td class='text-center'><label class='label-container'><input type='checkbox' $tchecked name='forum[]' value='{$entry->id}:$topicentry[topic_id]'><span class='checkmark'></span></label></td>";
                    $ret_string .= "</tr>";
                }
            }
        }
        $ret_string .= "</table></div>";
    }
    return $ret_string;
}
