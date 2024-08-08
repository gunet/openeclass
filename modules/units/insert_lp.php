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
 * @brief display list of available learning paths (if any)
 */
function list_lps() {
    global $id, $course_id, $tool_content, $urlServer,
    $langAddModulesButton, $langChoice, $langNoLearningPath,
    $langLearningPaths, $course_code, $langSelect;

    $result = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE course_id = ?d ORDER BY name", $course_id);
    $lpinfo = array();
    foreach ($result as $row) {
        $lpinfo[] = array(
            'id' => $row->learnPath_id,
            'name' => $row->name,
            'comment' => $row->comment,
            'visible' => $row->visible,
            'rank' => $row->rank);
    }
    if (count($lpinfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoLearningPath</span></div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id'>" .
                "<div class='table-responsive'><table class='table-default'>" .
                "<thead><tr class='list-header'>" .
                "<th>$langChoice</th>" .
                "<th>$langLearningPaths</th>" .
                "</tr></thead>";
        foreach ($lpinfo as $entry) {
            if ($entry['visible'] == 0) {
                $vis = 'not_visible';
                $disabled = 'disabled';
            } else {
                $vis = '';
                $disabled = '';
            }
            $m_id = Database::get()->querySingle("SELECT module_id FROM lp_rel_learnPath_module WHERE learnPath_id = ?d
                                                    AND `rank` = (SELECT MIN(`rank`) FROM lp_rel_learnPath_module WHERE learnPath_id = ?d)",
                                                $entry['id'], $entry['id']);
            if (($m_id) and $m_id->module_id > 0) {
                if (!empty($entry['comment'])) {
                    $comment_text = "<div>" . $entry['comment'] . "</div>";
                } else {
                    $comment_text = '';
                }
                $tool_content .= "<tr class='$vis'>";
                $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='lp[]' value='$entry[id]' $disabled><span class='checkmark'></span></label></td>";
                $tool_content .= "<td><a href='{$urlServer}modules/learnPath/viewer.php?course=$course_code&amp;path_id=$entry[id]&amp;module_id=$m_id->module_id'>" . q($entry['name']) . "</a>"
                 . $comment_text . "</td>";
                $tool_content .= "</tr>";
            }
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='d-flex justify-content-start mt-4'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='submit_lp' value='$langAddModulesButton'></div></form>";
    }
}
