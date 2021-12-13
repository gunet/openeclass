<?php
/**
 * Created by PhpStorm.
 * User: jexi
 * Date: 7/5/2019
 * Time: 3:41 μμ
 */

/**
 * @brief display available chats
 */
function list_chats() {
    global $id, $course_id, $tool_content, $urlServer,
           $langAddModulesButton, $langChoice, $langNoChatAvailable,
           $course_code, $langChat;

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
        $tool_content .= "<div class='alert alert-warning'>$langNoChatAvailable</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
            "<input type='hidden' name='id' value='$id'>" .
            "<table class='table-default'>" .
            "<tr class='list-header'>" .
            "<th style='width: 80px;'>$langChoice</th>" .
            "<th><div class='text-left'>&nbsp;$langChat</div></th>" .
            "</tr>";
        foreach ($chatinfo as $entry) {
            if ($entry['visible'] == 'inactive') {
                $vis = 'not_visible';
                $disabled = 'disabled';
            } else {
                $vis = '';
                $disabled = '';
            }
            if (!empty($entry['description'])) {
                $description_text = "<div style='margin-top: 10px;'>" .  $entry['description'] . "</div>";
            } else {
                $description_text = '';
            }
            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='chat[]' value='$entry[id]' $disabled></td>";
            $tool_content .= "<td>&nbsp;<a href='${urlServer}modules/chat/chat.php?course=$course_code&amp;conference_id=$entry[id]'>" . q($entry['name']) . "</a>$description_text</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='submit_chat' value='$langAddModulesButton'></div></form>";

    }
}
