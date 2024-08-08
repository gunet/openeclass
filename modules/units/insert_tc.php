<?php
/**
 * Created by PhpStorm.
 * User: jexi
 * Date: 7/5/2019
 * Time: 3:41 μμ
 */

/**
 * @brief display available teleconferences
 */
function list_tcs() {
    global $id, $course_id, $tool_content,
           $langAddModulesButton, $langChoice, $langNoBBBSesssions,
           $course_code, $langBBB, $langSelect;

    $result = Database::get()->queryArray("SELECT * FROM tc_session WHERE course_id = ?d ORDER BY title", $course_id);
    $tcinfo = array();
    foreach ($result as $row) {
        $tcinfo[] = array(
            'id' => $row->id,
            'name' => $row->title,
            'description' => $row->description,
            'visible' => $row->active);
    }
    if (count($tcinfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoBBBSesssions</span></div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
            "<input type='hidden' name='id' value='$id'>" .
            "<div class='table-responsive'><table class='table-default'>" .
            "<thead><tr class='list-header'>" .
            "<th>$langChoice</th>" .
            "<th>$langBBB</th>" .
            "</tr></thead>";
        foreach ($tcinfo as $entry) {
            if ($entry['visible'] == 0) {
                $vis = 'not_visible';
                $disabled = 'disabled';
            } else {
                $vis = '';
                $disabled = '';
            }
            if (!empty($entry['description'])) {
                $description_text = "<div class='text-muted'>" . $entry['description'] . "</div>";
            } else {
                $description_text = '';
            }
            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='tc[]' value='$entry[id]' $disabled><span class='checkmark'></span></label></td>";
            $tool_content .= "<td>" . q($entry['name']) . "</a>$description_text</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='d-flex justify-content-start mt-4'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='submit_tc' value='$langAddModulesButton'></div></form>";
    }
}
