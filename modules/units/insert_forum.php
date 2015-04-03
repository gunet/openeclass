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
 * display available forums (if any)
 * @global type $id
 * @global type $tool_content
 * @global type $urlServer
 * @global type $course_id
 * @global type $langComments
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoForums
 * @global type $langForums
 * @global type $course_code
 */
function list_forums() {
    global $id, $tool_content, $urlServer, $course_id,
    $langComments, $langAddModulesButton, $langChoice, $langNoForums, $langForums, $course_code;

    $result = Database::get()->queryArray("SELECT * FROM forum WHERE course_id = ?d", $course_id);
    $foruminfo = array();
    foreach ($result as $row) {
        $foruminfo[] = array(
            'id' => $row->id,
            'name' => $row->name,
            'comment' => $row->desc,
            'topics' => $row->num_topics);
    }
    if (count($foruminfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoForums</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id' />" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th>$langForums</th>" .
                "<th>$langComments</th>" .
                "<th class='checkbox_cell text-center'>$langChoice</th>" .
                "</tr>";

        foreach ($foruminfo as $entry) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>
            <a href='${urlServer}modules/forum/viewforum.php?course=$course_code&amp;forum=$entry[id]'>" . q($entry['name']). "</a></td>";
            $tool_content .= "<td>" . q($entry['comment']) . "</td>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='forum[]' value='$entry[id]' /></td>";
            $tool_content .= "</tr>";
            $r = Database::get()->queryArray("SELECT * FROM forum_topic WHERE forum_id = ?d", $entry['id']);
            if (count($r) > 0) { // if forum topics found
                $topicinfo = array();
                foreach ($r as $topicrow) {
                    $topicinfo[] = array(
                        'topic_id' => $topicrow->id,
                        'topic_title' => $topicrow->title,
                        'topic_time' => $topicrow->topic_time);
                }
                foreach ($topicinfo as $topicentry) {
                    $tool_content .= "<tr>";
                    $tool_content .= "<td>&nbsp;".icon('fa-comments')."&nbsp;&nbsp;<a href='${urlServer}/modules/forum/viewtopic.php?course=$course_code&amp;topic=$topicentry[topic_id]&amp;forum=$entry[id]'>" . q($topicentry['topic_title']) . "</a></td>";
                    $tool_content .= "<td>&nbsp;</td>";
                    $tool_content .= "<td class='text-center'><input type='checkbox' name='forum[]'  value='$entry[id]:$topicentry[topic_id]' /></td>";
                    $tool_content .= "</tr>";
                }
            }
        }
        $tool_content .= 
                "</table>";
        $tool_content .= "<div class='text-right'>
                            <input class='btn btn-primary' type='submit' name='submit_forum' value='$langAddModulesButton' />
                        </div></form>";
        
    }
}
