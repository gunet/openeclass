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
require_once 'include/lib/modalboxhelper.class.php';

function list_videos($id = NULL) {
    global $themeimg, $course_id, $langTitle, $langDescription, $langDate, 
    $langChoice, $langCatVideoDirectory, $langNoVideo, $course_code;
    
    $ret_string = '';
    $count = 0;
    $video_found = FALSE;
    $cnt1 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM video WHERE course_id = ?d AND visible = ?d", $course_id, 1)->cnt;
    $cnt2 = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM videolink WHERE course_id = ?d AND visible = ?d", $course_id, 1)->cnt;
    $count = $cnt1 + $cnt2;    
    
    if ($count > 0) {
        $exist_video = array();
        $exist_videolink = array();
        
        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND (type = ?s OR type = ?s)", $id, 'video', 'videolink');
            foreach ($post_res as $exist_res) {
                if ($exist_res->type == 'video') {
                    $exist_video[] = $exist_res->res_id;
                } elseif ($exist_res->type == 'videolink') {
                    $exist_videolink[] = $exist_res->res_id;
                }
            }
        }
        
        $video_found = TRUE;
        $ret_string .= "<table class='table-default'>";
        $ret_string .= "<tr class='list-header'>" .
                         "<th width='200' class='text-left'>&nbsp;$langTitle</th>" .
                         "<th class='text-left'>$langDescription</th>" .
                         "<th width='100'>$langDate</th>" .
                         "<th width='80'>$langChoice</th>" .
                         "</tr>";
        foreach (array('video', 'videolink') as $table) {
            $result = Database::get()->queryArray("SELECT * FROM $table WHERE (category IS NULL OR category = 0) AND course_id = ?d AND visible = ?d", $course_id, 1);
            foreach ($result as $row) {
                $checked = '';
                $row->course_id = $course_id;
                if ($table == 'video') {
                    $vObj = MediaResourceFactory::initFromVideo($row);
                    $videolink = MultimediaHelper::chooseMediaAhref($vObj);
                    if (in_array($row->id, $exist_video)) {
                        $checked = 'checked';
                    }
                } else {
                    $vObj = MediaResourceFactory::initFromVideoLink($row);
                    $videolink = MultimediaHelper::chooseMedialinkAhref($vObj);
                    if (in_array($row->id, $exist_videolink)) {
                        $checked = 'checked';
                    }
                }                
                $ret_string .= "<td>&nbsp;".icon('fa-film')."&nbsp;&nbsp;" . $videolink . "</td>".
                                 "<td>" . q($row->description) . "</td>".
                                 "<td class='text-center'>" . nice_format($row->date, true, true) . "</td>" .
                                 "<td class='text-center'><input type='checkbox' $checked name='video[]' value='$table:$row->id' /></td>" .
                                 "</tr>";                
            }
        }
        $sql = Database::get()->queryArray("SELECT * FROM video_category WHERE course_id = ?d ORDER BY name", $course_id);
        if ($sql) {
            foreach ($sql as $videocat) {
                $ret_string .= "<tr>";
                $ret_string .= "<td>".icon('fa-folder-o')."&nbsp;&nbsp;" .
                                 q($videocat->name) . "</td>";
                $ret_string .= "<td colspan='3'>" . standard_text_escape($videocat->description) . "</td>";
                $ret_string .= "</tr>";
                foreach (array('video', 'videolink') as $table) {
                    $sql2 = Database::get()->queryArray("SELECT * FROM $table WHERE category = ?d AND visible = ?d", $videocat->id, 1);
                    foreach ($sql2 as $linkvideocat) {
                            $checked = '';
                            if ($table == 'video') {
                                if (in_array($linkvideocat->id, $exist_video)) {
                                    $checked = 'checked';
                                }
                            } else {
                                if (in_array($linkvideocat->id, $exist_videolink)) {
                                    $checked = 'checked';
                                }
                            }
                            $ret_string .= "<tr>";
                            $ret_string .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;<img src='$themeimg/links_on.png' />&nbsp;&nbsp;<a href='" . q($linkvideocat->url) . "' target='_blank'>" .
                                    q(($linkvideocat->title == '')? $linkvideocat->url: $linkvideocat->title) . "</a></td>";
                            $ret_string .= "<td>" . standard_text_escape($linkvideocat->description) . "</td>";
                            $ret_string .= "<td class='text-center'>" . nice_format($linkvideocat->date, true, true) . "</td>";
                            $ret_string .= "<td class='text-center'><input type='checkbox' $checked name='video[]' value='$table:$linkvideocat->id' /></td>";
                            $ret_string .= "</tr>";	
                    }
                }
            }
        }
        $ret_string .= "</table>";
    }
    if (!$video_found) {
        $ret_string .= "<div class='alert alert-warning'>$langNoVideo</div>";
    }
    return $ret_string;                     
}
