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

function list_assignments() {
    global $id, $tool_content, $langTitle, $langChoice, $m,
    $langAddModulesButton, $langNoAssign, $langActive, $langInactive,
    $langVisible, $course_id, $course_code, $themeimg;

    $result = db_query("SELECT * FROM assignment WHERE course_id = $course_id ORDER BY active, title");
    if (mysql_num_rows($result) == 0) {
        $tool_content .= "\n  <p class='alert1'>$langNoAssign</p>";
    } else {
        $tool_content .= "\n  <form action='insert.php?course=$course_code' method='post'>" .
                "\n  <input type='hidden' name='id' value='$id' />\n" .
                "\n    <table width='99%' class='tbl_alt'>" .
                "\n    <tr>" .
                "\n      <th class='left'>&nbsp;$langTitle</th>" .
                "\n      <th width='110'>$langVisible</th>" .
                "\n      <th width='120'>$m[deadline]</th>" .
                "\n      <th width='80'>$langChoice</th>" .
                "\n    </tr>\n";
        $i = 0;
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $visible = $row['active'] ?
                    "<img title='$langActive' src='$themeimg/visible.png' />" :
                    "<img title='$langInactive' src='$themeimg/invisible.png' />";
            $description = empty($row['description']) ? '' :
                    "<div>$row[description]</div>";
            if ($i % 2) {
                $rowClass = "class='odd'";
            } else {
                $rowClass = "class='even'";
            }

            $tool_content .= "\n    <tr $rowClass>" .
                    "\n      <td>&laquo; $row[title]$description</td>" .
                    "\n      <td class='center'>$visible</td>" .
                    "\n      <td class='center'>$row[submission_date]</td>" .
                    "\n      <td class='center'><input name='work[]' value='$row[id]' type='checkbox' /></td>" .
                    "\n    </tr>";
            $i++;
        }
        $tool_content .= "\n    <tr>" .
                "\n        <th colspan='4'><div align='right'>" .
                "<input type='submit' name='submit_work' value='$langAddModulesButton' />" .
                "</div></th>" .
                "\n    </tr>" .
                "\n    </table>" .
                "\n  </form>";
    }
}
