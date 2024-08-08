<?php

/**
 * @brief display available h5p
 */
function list_h5p() {
    global $id, $course_id, $tool_content, $urlServer, $webDir, $urlAppend,
           $langAddModulesButton, $langChoice, $langH5pNoContent,
           $course_code, $langH5p, $langSelect;

    $result = Database::get()->queryArray("SELECT * FROM h5p_content WHERE course_id = ?d ORDER BY title", $course_id);
    $h5pinfo = array();
    foreach ($result as $row) {
        $h5pinfo[] = array(
            'id' => $row->id,
            'title' => $row->title,
            'main_library_id' => $row->main_library_id);
    }
    if (count($h5pinfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langH5pNoContent</span></div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
            "<input type='hidden' name='id' value='$id'>" .
            "<div class='table-responsive'><table class='table-default'>" .
            "<thead><tr class='list-header'>" .
            "<th>$langChoice</th>" .
            "<th>$langH5p</th>" .
            "</tr></thead>";
        foreach ($h5pinfo as $entry) {
            $q = Database::get()->querySingle("SELECT machine_name, title, major_version, minor_version
                                            FROM h5p_library WHERE id = ?s", $entry['main_library_id']);
            $h5p_content_type_title = $q->title;
            $typeFolder = $q->machine_name . "-" . $q->major_version . "." . $q->minor_version;
            $typeIconPath = $webDir . "/courses/h5p/libraries/" . $typeFolder . "/icon.svg";
            $typeIcon = (file_exists($typeIconPath))
                ? $urlAppend . "courses/h5p/libraries/" . $typeFolder . "/icon.svg"  // expected icon
                : $urlAppend . "js/h5p-core/images/h5p_library.svg"; // fallback icon
            $tool_content .= "<tr>";
            $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='h5p[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
            $tool_content .= "<td><img src='$typeIcon' width='30px' height='30px' title='$h5p_content_type_title' alt='$h5p_content_type_title'>&nbsp;&nbsp;<a href='{$urlServer}modules/h5p/view.php?id=$entry[id]&amp;course=$course_code'>" . q($entry['title']) . "</a></td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='d-flex justify-content-start mt-4'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='submit_h5p' value='$langAddModulesButton'>";
        $tool_content .= "</div>";
        $tool_content .= "</form>";
    }
}
