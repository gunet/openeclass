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
 * @global type $id
 * @global type $course_id
 * @global type $tool_content
 * @global type $urlServer
 * @global type $langComments
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoLearningPath
 * @global type $langLearningPaths
 * @global type $course_code
 * @global type $themeimg
 */
function list_lps() {
    global $id, $course_id, $tool_content, $urlServer, $langComments,
    $langAddModulesButton, $langChoice, $langNoLearningPath,
    $langLearningPaths, $course_code, $themeimg;


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
        $tool_content .= "<p class='alert1'>$langNoLearningPath</p>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
                "<input type='hidden' name='id' value='$id'>" .
                "<table width='99%' class='tbl_alt'>" .
                "<tr>" .
                "<th><div align='left'>&nbsp;$langLearningPaths</div></th>" .
                "<th><div align='left'>$langComments</div></th>" .
                "<th width='80'>$langChoice</th>" .
                "</tr>";
        $i = 0;
        foreach ($lpinfo as $entry) {
            if ($entry['visible'] == 0) {
                $vis = 'invisible';
            } else {
                if ($i % 2 == 0) {
                    $vis = 'even';
                } else {
                    $vis = 'odd';
                }
            }
            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td>&nbsp;<img src='$themeimg/lp_on.png' />&nbsp;&nbsp;<a href='${urlServer}/modules/learnPath/learningPath.php?course=$course_code&amp;path_id=$entry[id]'>" . q($entry[name]) . "</a></td>";
            $tool_content .= "<td>" . q($entry[comment]) . "</td>";
            $tool_content .= "<td align='center'><input type='checkbox' name='lp[]' value='$entry[id]'></td>";
            $tool_content .= "</tr>";
            $i++;
        }
        $tool_content .= "<tr>" .
                "<th colspan='3'><div align='right'>";
        $tool_content .= "<input type='submit' name='submit_lp' value='$langAddModulesButton'></div></th>";
        $tool_content .= "</tr></table></form>\n";
    }
}
