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
 */
function list_forums() {
    global $id, $tool_content, $urlServer, $course_id,
           $langAddModulesButton, $langChoice, $langNoForums, $langForums, $course_code, $langSelect;

    // select topics from forums (not from group forums)
    $foruminfo = Database::get()->queryArray("SELECT * FROM forum WHERE course_id = ?d
                                                    AND cat_id IN (SELECT id FROM forum_category WHERE cat_order >= 0)", $course_id);
    if (!$foruminfo) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoForums</span></div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id' />" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<thead><tr class='list-header'>" .
                "<th>$langChoice</th>" .
                "<th>$langForums</th>" .
                "</tr></thead>";

        foreach ($foruminfo as $entry) {
            if (!empty($entry->desc)) {
                $description_text = "<div>" .  q($entry->desc) . "</div>";
            } else {
                $description_text = '';
            }
            $tool_content .= "<tr>
                <td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='forum[]' value='{$entry->id}'><span class='checkmark'></span></label></td>
                <td><a href='{$urlServer}modules/forum/viewforum.php?course=$course_code&amp;forum={$entry->id}'>" . $entry->name . "</a>$description_text</td>
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
                    $tool_content .= "<tr>";
                    $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='forum[]'  value='{$entry->id}:$topicentry[topic_id]'><span class='checkmark'></span></label></td>";
                    $tool_content .= "<td>".icon('fa-comments')."&nbsp;&nbsp;<a href='{$urlServer}modules/forum/viewtopic.php?course=$course_code&amp;topic=$topicentry[topic_id]&amp;forum={$entry->id}'>" . q($topicentry['topic_title']) . "</a></td>";
                    $tool_content .= "</tr>";
                }
            }
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='d-flex justify-content-start mt-4'>
                            <input class='btn submitAdminBtn' type='submit' name='submit_forum' value='$langAddModulesButton' />
                        </div></form>";
    }
}
