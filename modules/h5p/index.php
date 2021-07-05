<?php

$require_current_course = true;

require_once '../../include/baseTheme.php';
$toolName = "H5P";

$content = Database::get()->queryArray("SELECT * FROM h5p_content WHERE course_id = ?d ORDER BY id ASC", $course_id);

if ($is_editor) {
    $tool_content .= action_bar([
        ['title' => $langImport,
            'url' => "upload.php?course=" . $course_code,
            'icon' => 'fa-upload',
            'level' => 'primary-label']
    ], false);
}

if ($content) {
    $tool_content .= "<table class='table-default'>
        <thead>
        	<tr class='list-header''>
        		<th class='text-left'>H5P</th>
                <th class='text-center' style='width:109px;'>
                	<span class='fa fa-gears'></span>
                </th>
            </tr>
        </thead>
        <tbody>";

    foreach ($content as $item) {
        $tool_content .= "<tr>
					<td>
						<a href='view.php?course=$course_code&amp;id=$item->id'>$item->title</a>
					</td>
					<td class='text-center'>";

    if ($is_editor) {
        $tool_content .= action_button([
            ['icon' => 'fa-times',
                'title' => $langDelete,
                'url' => "delete.php?course=$course_code&amp;id=$item->id",
                'class' => 'delete',
                'confirm' => $langConfirmDelete
            ]
        ], false);
    }
    $tool_content .= "</td></tr>";
    }
    $tool_content .= "</tbody></table>";
} else {
    $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχει περιεχόμενο H5P</div>";
}

draw($tool_content, 2);
