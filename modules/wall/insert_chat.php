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

function list_chats($id = NULL) {
    global $course_id, $urlServer, $langChoice, $langNoChatAvailable, $langDescription, $langChat;

    $ret_string = '';
    $result = Database::get()->queryArray("SELECT * FROM conference WHERE course_id = ?d ORDER BY conf_title", $course_id);
    $chatinfo = array();
    foreach ($result as $row) {
        $chatinfo[] = array(
            'id' => $row->conf_id,
            'name' => $row->conf_title,
            'description' => $row->conf_description,
            'visible' => $row->status);
    }
    if (count($chatinfo) == 0) {
        $ret_string .= "<div class='alert alert-warning'>$langNoChatAvailable</div>";
    } else {
        $exist_chat = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'chat');
            foreach ($post_res as $exist_res) {
                $exist_chat[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<table class='table-default'>" .
            "<tr class='list-header'>" .
            "<th><div align='left'>&nbsp;$langChat</div></th>" .
            "<th><div align='left'>$langDescription</div></th>" .
            "<th width='80'>$langChoice</th>" .
            "</tr>";
        foreach ($chatinfo as $entry) {
            $checked = '';
            if (in_array($entry['id'], $exist_chat)) {
                $checked = 'checked';
            }

            $ret_string .= "<tr>";
            $ret_string .= "<td>&nbsp;".icon('fa fa-exchange')."&nbsp;&nbsp;<a href='{$urlServer}modules/chat/chat.php?conference_id=$entry[id]'>".q($entry['name'])."</a></td>";
            $ret_string .= "<td>".$entry['description']."</td>";
            $ret_string .= "<td class='text-center'><input type='checkbox' $checked name='chat[]' value='$entry[id]'></td>";
            $ret_string .= "</tr>";
        }
        $ret_string .= "</table>";
    }
    return $ret_string;
}
