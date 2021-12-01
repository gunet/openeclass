<?php

/**
 * @brief display available h5p
 */
function list_h5p() {
    global $id, $course_id, $tool_content, $urlServer,
           $langAddModulesButton, $langChoice, $langH5pNoContent,
           $course_code, $langH5p;

    $result = Database::get()->queryArray("SELECT * FROM h5p_content WHERE course_id = ?d ORDER BY title", $course_id);
    $h5pinfo = array();
    foreach ($result as $row) {
        $h5pinfo[] = array(
            'id' => $row->id,
            'title' => $row->title);
    }
    if (count($h5pinfo) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langH5pNoContent</div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
            "<input type='hidden' name='id' value='$id'>" .
            "<table class='table-default'>" .
            "<tr class='list-header'>" .
            "<th width='80'>$langChoice</th>" .
            "<th><div class='text-left'>&nbsp;$langH5p</div></th>" .
            "</tr>";
        foreach ($h5pinfo as $entry) {
            $tool_content .= "<tr>";
            $tool_content .= "<td class='text-center'><input type='checkbox' name='h5p[]' value='$entry[id]'></td>";
            $tool_content .= "<td>&nbsp;" . icon('fa fa-tablet') . "&nbsp;&nbsp;<a href='${urlServer}modules/h5p/view.php?id=$entry[id]&amp;course=$course_code'>" . q($entry['title']) . "</a></td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>";
        $tool_content .= "<div class='text-right'>";
        $tool_content .= "<input class='btn btn-primary' type='submit' name='submit_h5p' value='$langAddModulesButton'></div></form>";

    }
}
