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

require_once 'include/lib/mediaresource.factory.php';
require_once 'include/lib/multimediahelper.class.php';

/**
 * @brief list multimedia while inserting them in course unit
 * @global type $id
 * @global type $tool_content
 * @global type $themeimg
 * @global type $course_id
 * @global type $langTitle
 * @global type $langDescription
 * @global type $langDate
 * @global type $langChoice
 * @global type $langAddModulesButton
 * @global type $langNoVideo
 * @global type $course_code
 */
function list_videos() {
    global $id, $tool_content, $themeimg, $course_id,
    $langTitle, $langDescription, $langDate, $langChoice,
    $langAddModulesButton, $langNoVideo, $course_code;
    
            
    $count = 0;
    $video_found = FALSE;
    $cnt1 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM video WHERE course_id = ?d", $course_id)->cnt;
    $cnt2 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM videolink WHERE course_id = ?d", $course_id)->cnt;
    $count = $cnt1 + $cnt2;    
    if ($count > 0) {
        $video_found = TRUE;
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'><input type='hidden' name='id' value='$id' />";
                    $tool_content .= "<table class='table-default'>";
                    $tool_content .= "<tr class='list-header'>" .
                                     "<th width='200' class='text-left'>&nbsp;$langTitle</th>" .
                                     "<th class='text-left'>$langDescription</th>" .
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
                $tool_content .= "<td>&nbsp;".icon('fa-film')."&nbsp;&nbsp;" . $videolink . "</td>".
                                 "<td>" . q($row->description) . "</td>".
                                 "<td class='text-center'>" . nice_format($row->date, true, true) . "</td>" .
                                 "<td class='text-center'><input type='checkbox' name='video[]' value='$table:$row->id' /></td>" .
                                 "</tr>";                
            }
        }
        $sql = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY name", $course_id);
        if ($sql) {
            foreach ($sql as $videocat) {
                $tool_content .= "<tr>";
                $tool_content .= "<td>".icon('fa-folder-o')."&nbsp;&nbsp;" .
                                 q($videocat->name) . "</td>";
                $tool_content .= "<td colspan='2'>" . standard_text_escape($videocat->description) . "</td>";
                $tool_content .= "<td align='center'><input type='checkbox' name='videocatlink[]' value='$videocat->id' /></td>";
                $tool_content .= "</tr>";
                foreach (array('video', 'videolink') as $table) {
                    $sql2 = Database::get()->queryArray("SELECT * FROM $table WHERE category = ?d", $videocat->id);
                    foreach ($sql2 as $linkvideocat) {
                            $tool_content .= "<tr>";
                            $tool_content .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='$themeimg/links_on.png' />&nbsp;&nbsp;<a href='" . q($linkvideocat->url) . "' target='_blank'>" .
                                    q(($linkvideocat->title == '')? $linkvideocat->url: $linkvideocat->title) . "</a></td>";
                            $tool_content .= "<td>" . standard_text_escape($linkvideocat->description) . "</td>";
                            $tool_content .= "<td class='text-center'>" . nice_format($linkvideocat->date, true, true) . "</td>";
                            $tool_content .= "<td class='text-center'><input type='checkbox' name='video[]' value='$table:$linkvideocat->id' /></td>";
                            $tool_content .= "</tr>";	
                    }
                }
            }
        }
        $tool_content .= "</table><div class='text-right'><input class='btn btn-primary' type='submit' name='submit_video' value='".q($langAddModulesButton)."' />&nbsp;&nbsp;</div></form>";
    }
    if (!$video_found) {
        $tool_content .= "<div class='alert alert-warning'>$langNoVideo</div>";
    }                     
}
