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
 * @brief display list of available videos (if any)
 */
function list_videos() {
    global $id, $tool_content, $course_id,
            $langVideo, $langDate, $langChoice, $langAddModulesButton,
            $langNoVideo, $course_code, $langSelect, $langOpenNewTab;

    $video_found = FALSE;
    $cnt1 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM video WHERE course_id = ?d", $course_id)->cnt;
    $cnt2 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM videolink WHERE course_id = ?d", $course_id)->cnt;
    $count = $cnt1 + $cnt2;
    if ($count > 0) {
        $video_found = TRUE;
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'><input type='hidden' name='id' value='$id' />";
                    $tool_content .= "<div class='table-responsive'><table class='table-default'>";
                    $tool_content .= "<tr class='list-header'>" .
                                     "<thead><th>$langChoice</th>" .
                                     "<th>$langVideo</th>" .
                                     "<th>$langDate</th>" .
                                     "</tr></thead>";
        foreach (array('video', 'videolink') as $table) {
            $result = Database::get()->queryArray("SELECT * FROM $table WHERE (category IS NULL OR category = 0) AND course_id = ?d ORDER BY date DESC", $course_id);
            foreach ($result as $row) {
                $row->course_id = $course_id;
                if ($table == 'video') {
                    $vObj = MediaResourceFactory::initFromVideo($row);
                    $videolink = MultimediaHelper::chooseMediaAhref($vObj);
                } else {
                    $vObj = MediaResourceFactory::initFromVideoLink($row);
                    $videolink = MultimediaHelper::chooseMedialinkAhref($vObj);
                }
                if (!empty($row->description)) {
                    $description_text = "<div>" .  q($row->description). "</div>";
                } else {
                    $description_text = '';
                }
                $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='video[]' value='$table:$row->id'><span class='checkmark'></span></label></td>" .
                                 "<td>".icon('fa-film')."&nbsp;&nbsp;" . $videolink . $description_text . "</td>" .
                                 "<td>" . format_locale_date(strtotime($row->date), 'short', false) . "</td>" .
                                 "</tr>";
            }
        }
        $sql = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY name", $course_id);
        if ($sql) {
            foreach ($sql as $videocat) {
                $tool_content .= "<tr>";
                $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='videocatlink[]' value='$videocat->id' /><span class='checkmark'></span></label></td>";
                $tool_content .= "<td>".icon('fa-folder-open')."&nbsp;&nbsp;<strong>" . q($videocat->name) . "</strong>";
                if (!empty($videocat->description)) {
                    $videocat_description_text = "<div>" .  q($videocat->description). "</div>";
                } else {
                    $videocat_description_text = '';
                }
                $tool_content .= $videocat_description_text . "</td>";
                $tool_content .= "</tr>";
                foreach (array('video', 'videolink') as $table) {
                    $sql2 = Database::get()->queryArray("SELECT * FROM $table WHERE category = ?d ORDER BY date DESC", $videocat->id);
                    foreach ($sql2 as $linkvideocat) {
                        if (!empty($linkvideocat->description)) {
                            $linkvideocat_description_text = "<div>" .  q($linkvideocat->description). "</div>";
                        } else {
                            $linkvideocat_description_text = '';
                        }
                        $tool_content .= "<tr>";
                        $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='video[]' value='$table:$linkvideocat->id' /><span class='checkmark'></span></label></td>";
                        $tool_content .= "<td>" . icon('fa-link') . "&nbsp;&nbsp;<a href='" . q($linkvideocat->url) . "' target='_blank' aria-label='$langOpenNewTab'>" .
                                q(($linkvideocat->title == '')? $linkvideocat->url: $linkvideocat->title) . "</a>";
                        $tool_content .= $linkvideocat_description_text . "</td>";
                        $tool_content .= "<td>" . format_locale_date(strtotime($linkvideocat->date), 'short', false) . "</td>";
                        $tool_content .= "</tr>";
                    }
                }
            }
        }
        $tool_content .= "</table></div><div class='d-flex justify-content-start mt-4'><input class='btn submitAdminBtn' type='submit' name='submit_video' value='".q($langAddModulesButton)."' />&nbsp;&nbsp;</div></form>";
    }
    if (!$video_found) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoVideo</span></div></div>";
    }
}
