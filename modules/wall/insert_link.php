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

require_once 'include/course_settings.php';

function list_links($id = NULL) {
    global $course_id, $langNoCategory, $langDescription, $langChoice, $langNoLinksExist, $langLinks, $langSocialCategory, $langSelect;

    $ret_string = '';
    $result = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d", $course_id);
    if (count($result) == 0) {
        $ret_string .= "<div class='col-12 mt-3'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoLinksExist</span></div></div>";
    } else {
        $exist_link = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'link');
            foreach ($post_res as $exist_res) {
                $exist_link[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<div class='table-responsive'><table class='table-default'>" .
            "<thead><tr class='list-header'>" .
            "<th style='width:'>$langLinks</th>" .
            "<th>$langDescription</th>" .
            "<th width='10'>$langChoice</th>" .
            "</tr></thead>";
        $sql = Database::get()->queryArray("SELECT * FROM link_category WHERE course_id = ?d", $course_id);
        if (count($sql) > 0) {
            foreach ($sql as $catrow) {
                $ret_string .= "<tr>";
                $ret_string .= "<td><b>" . icon('fa-folder-open') . "&nbsp;" .
                    q($catrow->name) . "</b></td>";
                $ret_string .= "<td >" . standard_text_escape($catrow->description) . "</td>";
                $ret_string .= "<td><label aria-label='$langSelect' class='label-container'><input type='checkbox' name='catlink[]' value='$catrow->id' /><span class='checkmark'></span></label></td>";
                $ret_string .= "</tr>";
                $sql2 = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d AND category = ?d", $course_id, $catrow->id);
                foreach ($sql2 as $linkcatrow) {
                    $checked = '';
                    if (in_array($linkcatrow->id, $exist_link)) {
                        $checked = 'checked';
                    }
                    $ret_string .= "<tr>";
                    $ret_string .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;" . icon('fa-link') . "&nbsp;&nbsp;<a href='" . q($linkcatrow->url) . "' target='_blank' aria-label='(opens in a new tab)'>" .
                        q(($linkcatrow->title == '') ? $linkcatrow->url : $linkcatrow->title) . "</a></td>";
                    $ret_string .= "<td>" . standard_text_escape($linkcatrow->description) . "</td>";
                    $ret_string .= "<td><label aria-label='$langSelect' class='label-container'><input type='checkbox' $checked name='link[]' value='$linkcatrow->id' /><span class='checkmark'></span></label></td>";
                    $ret_string .= "</tr>";
                }
            }
        }
        $result = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d AND category = 0", $course_id);
        $linkinfo = array();
        foreach ($result as $row) {
            $linkinfo[] = array(
                'id' => $row->id,
                'url' => $row->url,
                'title' => ($row->title == '') ? $row->url : $row->title,
                'comment' => $row->description,
                'category' => $row->category);
        }
        if (count($linkinfo) > 0) {
            $ret_string .= "<tr>" .
                "<td colspan='3'><b>$langNoCategory</b></td>" .
                "</tr>";
            foreach ($linkinfo as $entry) {
                $checked = '';
                if (in_array($entry['id'], $exist_link)) {
                    $checked = 'checked';
                }
                $ret_string .= "<tr>" .
                    "<td>&nbsp;&nbsp;&nbsp;&nbsp;" . icon('fa-link') . "&nbsp;&nbsp;<a href='" . q($entry['url']) . "' target=_blank>" . q($entry['title']) . "</a></td>" .
                    "<td>" . standard_text_escape($entry['comment']) . "</td>" .
                    "<td><label aria-label='$langSelect' class='label-container'><input type='checkbox' $checked name='link[]' value='$entry[id]' /><span class='checkmark'></span></label></td>";
                "</tr>";
            }
        }
        if (setting_get(SETTING_COURSE_SOCIAL_BOOKMARKS_ENABLE, $course_id) == 1) {
            $result = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d AND category = -2", $course_id);
            $linkinfo = array();
            foreach ($result as $row) {
                $linkinfo[] = array(
                    'id' => $row->id,
                    'url' => $row->url,
                    'title' => ($row->title == '') ? $row->url : $row->title,
                    'comment' => $row->description,
                    'category' => $row->category);
            }
            if (count($linkinfo) > 0) {
                $ret_string .= "<tr>" .
                    "<td colspan='3'><b>$langSocialCategory</b></td>" .
                    "</tr>";
                foreach ($linkinfo as $entry) {
                    $checked = '';
                    if (in_array($entry['id'], $exist_link)) {
                        $checked = 'checked';
                    }
                    $ret_string .= "<tr>" .
                        "<td>&nbsp;&nbsp;&nbsp;&nbsp;" . icon('fa-link') . "&nbsp;&nbsp;<a href='" . q($entry['url']) . "' target=_blank>" . q($entry['title']) . "</a></td>" .
                        "<td>" . standard_text_escape($entry['comment']) . "</td>" .
                        "<td><label aria-label='$langSelect' class='label-container'><input type='checkbox' $checked name='link[]' value='$entry[id]' /><span class='checkmark'></span></label></td>";
                    "</tr>";
                }
            }
        }
        $ret_string .= "</table></div>";
    }
    return $ret_string;
}
