<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

function list_chats($id = NULL) {
    global $course_id, $urlServer, $langChoice, $langNoChatAvailable, $langDescription, $langChat, $langSelect;

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
        $ret_string .= "<div class='col-12 mt-3'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoChatAvailable</span></div></div>";
    } else {
        $exist_chat = array();

        if (!is_null($id)) { //find existing resources (edit case)
            $post_res = Database::get()->queryArray("SELECT * FROM wall_post_resources WHERE post_id = ?d AND type = ?s", $id, 'chat');
            foreach ($post_res as $exist_res) {
                $exist_chat[] = $exist_res->res_id;
            }
        }

        $ret_string .= "<div class='table-responsive'><table class='table-default'>" .
            "<tr class='list-header'>" .
            "<thead><th><div align='left'>$langChat</div></th>" .
            "<th><div align='left'>$langDescription</div></th>" .
            "<th width='80'>$langChoice</th>" .
            "</tr></thead>";
        foreach ($chatinfo as $entry) {
            $checked = '';
            if (in_array($entry['id'], $exist_chat)) {
                $checked = 'checked';
            }

            $ret_string .= "<tr>";
            $ret_string .= "<td>&nbsp;".icon('fa fa-exchange')."&nbsp;&nbsp;<a href='{$urlServer}modules/chat/chat.php?conference_id=$entry[id]'>".q($entry['name'])."</a></td>";
            $ret_string .= "<td>".$entry['description']."</td>";
            $ret_string .= "<td><label aria-label='$langSelect' class='label-container'><input type='checkbox' $checked name='chat[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
            $ret_string .= "</tr>";
        }
        $ret_string .= "</table></div>";
    }
    return $ret_string;
}
