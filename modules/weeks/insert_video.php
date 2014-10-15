<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/multimediahelper.class.php';

function list_videos() {
    global $id, $tool_content, $urlServer,
    $langTitle, $langDescr, $langDate, $langChoice,
    $langAddModulesButton, $langNoVideo, $course_code,
    $themeimg, $course_id, $mysqlMainDb;

    $table_started = false;
    $count = 0;
    foreach (array('video', 'videolink') as $table) {
        $result = Database::get()->queryArray("SELECT * FROM $table WHERE course_id = $course_id");
        $count += count($result);
        $numLine = 0;
        foreach ($result as $row) {
            if (!$table_started) {
                $tool_content .= "<form action='insert.php?course=$course_code' method='post'><input type='hidden' name='id' value='$id' />";
                $tool_content .= "<table class='tbl_alt' width='99%'>";
                $tool_content .= "<tr>" .
                        "<th><div align='left'>&nbsp;$langTitle</div></th>" .
                        "<th><div align='left'>$langDescr</div></th>" .
                        "<th width='100'>$langDate</th>" .
                        "<th width='80'>$langChoice</th>" .
                        "</tr>";
                $table_started = true;
            }

            if ($table == 'video') {
                $vObj = MediaResourceFactory::initFromVideo($row);
                $videolink = MultimediaHelper::chooseMediaAhref($vObj);
            } else {
                $vObj = MediaResourceFactory::initFromVideoLink($row);
                $videolink = MultimediaHelper::chooseMedialinkAhref($vObj);
            }

            if ($numLine % 2 == 0) {
                $tool_content .= "<tr class='even'>";
            } else {
                $tool_content .= "<tr class='odd'>";
            }

            $tool_content .= "<td>&nbsp;<img src='$themeimg/videos_on.png' />&nbsp;&nbsp;" . $videolink . "</td>" .
                    "<td>" . htmlspecialchars($row->description) . "</td>" .
                    "<td class='center'>" . nice_format($row->date, true, true) . "</td>" .
                    "<td class='center'><input type='checkbox' name='video[]' value='$table:$row->id /></td>" .
                    "</tr>";
            $numLine++;
        }
    }
    if ($count > 0) {
        $tool_content .= "<tr><th colspan='4'><div align='right'><input type='submit' name='submit_video' value='$langAddModulesButton' />&nbsp;&nbsp;</div></th></tr></table></form>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoVideo</div>";
    }
}
