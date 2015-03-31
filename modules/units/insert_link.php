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
 * display available links (if any)
 * @global type $id
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langNoCategory
 * @global type $langCategorisedLinks
 * @global type $langComments
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoLinksExist
 * @global type $langLinks
 * @global type $course_code
 */
function list_links() {
    global $id, $course_id, $course_code, $tool_content,
    $langNoCategory, $langCategorisedLinks, $langDescr, $langAddModulesButton,
    $langChoice, $langNoLinksExist, $langLinks, $course_code;
    
    $result = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d", $course_id);
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langNoLinksExist</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>
				<input type='hidden' name='id' value='$id' />" .
                "<table class='table-default'>" .
                "<tr class='list-header'>" .
                "<th class='text-left' style='width:'>&nbsp;$langLinks</th>" .
                "<th class='text-left'>$langDescr</th>" .
                "<th width='10'>$langChoice</th>" .
                "</tr>";
        $sql = Database::get()->queryArray("SELECT * FROM link_category WHERE course_id = ?d", $course_id);
        if (count($sql) > 0) {
                        
            foreach ($sql as $catrow) {
                $tool_content .= "<tr>";
                $tool_content .= "<td><b>".icon('fa-folder-o')."&nbsp;".
                        q($catrow->name) . "</b></td>";
                $tool_content .= "<td >" . standard_text_escape($catrow->description) . "</td>";
                $tool_content .= "<td class='text-center'><input type='checkbox' name='catlink[]' value='$catrow->id' /></td>";
                $tool_content .= "</tr>";
                $sql2 = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d AND category = ?d", $course_id, $catrow->id);                
                foreach ($sql2 as $linkcatrow) {
                    $tool_content .= "<tr>";
                    $tool_content .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;".icon('fa-link')."&nbsp;&nbsp;<a href='" . q($linkcatrow->url) . "' target='_blank'>" .
                            q(($linkcatrow->title == '') ? $linkcatrow->url : $linkcatrow->title) . "</a></td>";
                    $tool_content .= "<td>" . standard_text_escape($linkcatrow->description) . "</td>";
                    $tool_content .= "<td class='text-center'><input type='checkbox' name='link[]' value='$linkcatrow->id' /></td>";
                    $tool_content .= "</tr>";
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
            $tool_content .= "<tr>" .
                    "<td colspan='3'><b>$langNoCategory</b></td>" .
                    "</tr>";
            foreach ($linkinfo as $entry) {
                $tool_content .= "<tr>" .
                        "<td>&nbsp;&nbsp;&nbsp;&nbsp;".icon('fa-link')."&nbsp;&nbsp;<a href='" . q($entry['url']) . "' target=_blank>" . q($entry['title']) . "</a></td>" .
                        "<td>" . standard_text_escape($entry['comment']) . "</td>" .
                        "<td class='text-center'><input type='checkbox' name='link[]' value='$entry[id]' /></td>";
                "</tr>";
            }
        }
        $tool_content .= "</table></form>";
        $tool_content .= "<div class='text-right'>" .
                "<input class='btn btn-primary' type='submit' name='submit_link' value='$langAddModulesButton' /></div>";
    }
}
