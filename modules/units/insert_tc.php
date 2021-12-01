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
           $langDescription, $course_code, $langBBB;

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
        $tool_content .= "<div class='alert alert-warning'>$langNoBBBSesssions</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
            "<input type='hidden' name='id' value='$id'>" .
            "<table class='table-default'>" .
            "<tr class='list-header'>" .
            "<th width='80'>$langChoice</th>" .
            "<th><div align='left'>&nbsp;$langBBB</div></th>" .
            "<th><div align='left'>$langDescription</div></th>" .
            "</tr>";
        foreach ($tcinfo as $entry) {
            if ($entry['visible'] == 0) {
                $vis = 'not_visible';
                $disabled = 'disabled';
            } else {
                $vis = '';
                $disabled = '';
            }

            $tool_content .= "<tr class='$vis'>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='tc[]' value='$entry[id]' $disabled></td>";
            $tool_content .= "<td>&nbsp;" . icon('fa fa-exchange') . "&nbsp;&nbsp;" . q($entry['name']) . "</a></td>";
            $tool_content .= "<td>" . $entry['description'] . "</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='submit_tc' value='$langAddModulesButton'></div></form>";

    }
}
