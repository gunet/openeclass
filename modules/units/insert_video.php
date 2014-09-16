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
    global $id, $tool_content, $themeimg, $course_id,
    $langTitle, $langDescr, $langDate, $langChoice, $langCatVideoDirectory,
    $langAddModulesButton, $langNoVideo, $course_code;
    
            
    $count = 0;
    $video_found = FALSE;
    $cnt1 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM video WHERE course_id = ?d", $course_id)->cnt;
    $cnt2 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM videolink WHERE course_id = ?d", $course_id)->cnt;
    $count = $cnt1 + $cnt2;
    $numLine = 0;
    if ($count > 0) {
        $video_found = TRUE;
        $tool_content .= " <form action='insert.php?course=$course_code' method='post'><input type='hidden' name='id' value='$id' />";
                    $tool_content .= "<table class='tbl_alt' width='99%'>";
                    $tool_content .= "<tr>" .
                                     "<th width='200'><div align='left'>&nbsp;$langTitle</div></th>" .
                                     "<th><div align='left'>$langDescr</div></th>" .
                                     "<th width='100'>$langDate</th>" .
                                     "<th width='80'>$langChoice</th>" .
                                     "</tr>";
        foreach (array('video', 'videolink') as $table) {
            $result = Database::get()->queryArray("SELECT * FROM $table WHERE (category IS NULL OR category = 0) AND course_id = ?d", $course_id);
            foreach ($result as $row) {
                $row->course_id = $course_id;
                if ($table == 'video') {
                    $vObj = MediaResourceFactory::initFromVideo($row);
                    $videolink = MultimediaHelper::chooseMediaAhref($vObj);
                } else {
                    $vObj = MediaResourceFactory::initFromVideoLink($row);
                    $videolink = MultimediaHelper::chooseMedialinkAhref($vObj);
                }
                if ($numLine%2 == 0) {
                    $tool_content .= "<tr class='even'>";
                } else {
                    $tool_content .= "<tr class='odd'>";
                }
                $tool_content .= "<td>&nbsp;<img src='$themeimg/videos_on.png' />&nbsp;&nbsp;" . $videolink . "</td>".
                                 "<td>" . q($row->description) . "</td>".
                                 "<td class='center'>" . nice_format($row->date, true, true) . "</td>" .
                                 "<td class='center'><input type='checkbox' name='video[]' value='$table:$row->id' /></td>" .
                                 "</tr>";
                $numLine++;
            }
        }
        $sql = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY name", $course_id);
        if ($sql) {
            $tool_content .= "<tr class='odd'><td colspan='3' class='bold'>&nbsp;$langCatVideoDirectory</td></tr>";
            foreach ($sql as $videocat) {
                $tool_content .= "<tr class='even'>";
                $tool_content .= "<td><img src='$themeimg/folder_open.png' />&nbsp;&nbsp;" .
                                 q($videocat->name) . "</td>";
                $tool_content .= "<td colspan='2'>" . standard_text_escape($videocat->description) . "</td>";
                $tool_content .= "<td align='center'><input type='checkbox' name='videocatlink[]' value='$videocat->id' /></td>";
                $tool_content .= "</tr>";
                foreach (array('video', 'videolink') as $table) {
                    $sql2 = Database::get()->queryArray("SELECT * FROM $table WHERE category = ?d", $videocat->id);
                    foreach ($sql2 as $linkvideocat) {
                            $tool_content .= "<tr class='even'>";
                            $tool_content .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='$themeimg/links_on.png' />&nbsp;&nbsp;<a href='" . q($linkvideocat->url) . "' target='_blank'>" .
                                    q(($linkvideocat->title == '')? $linkvideocat->url: $linkvideocat->title) . "</a></td>";
                            $tool_content .= "<td>" . standard_text_escape($linkvideocat->description) . "</td>";
                            $tool_content .= "<td class='center'>" . nice_format($linkvideocat->date, true, true) . "</td>";
                            $tool_content .= "<td align='center'><input type='checkbox' name='video[]' value='$table:$linkvideocat->id' /></td>";
                            $tool_content .= "</tr>";	
                    }
                }
            }
        }
        $tool_content .= "<tr><th colspan='4'><div align='right'><input type='submit' name='submit_video' value='".q($langAddModulesButton)."' />&nbsp;&nbsp;</div></th></tr></table></form>";
    }
    if (!$video_found) {
        $tool_content .= "<p class='alert1'>$langNoVideo</p>";
    }                     
}
