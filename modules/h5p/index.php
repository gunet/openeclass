<?php
/*
 * ========================================================================
 * Open eClass 3.11 - E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
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
 *
 * For a full list of contributors, see "credits.txt".
 */

$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'classes/H5PFactory.php';

$toolName = $langH5p;

$content = Database::get()->queryArray("SELECT * FROM h5p_content WHERE course_id = ?d ORDER BY id ASC", $course_id);

if ($is_editor) {
    $factory = new H5PFactory();
    $editorAjax = $factory->getH5PEditorAjax();
    $h5pcontenttypes = $editorAjax->getLatestLibraryVersions();

    // custom action bar
    $tool_content .= "
        <div class='row action_bar'>
            <div class='col-sm-12 clearfix'>
                <div class='margin-top-thin margin-bottom-fat pull-right'>
                    <div class='btn-group'>";

    // Dropdown select for Creating H5P Content
    if (!empty($h5pcontenttypes)) {
        $tool_content .= "
            <select id='createpicker' class='selectpicker' title='$langCreate' data-style='btn-primary' data-width='fit'>
                <optgroup label='$langH5pInteractiveContent'>";

        foreach ($h5pcontenttypes as $h5pcontenttype) {
            if ($h5pcontenttype->enabled) {
                $typeTitle = $h5pcontenttype->title;
                $typeVal = $h5pcontenttype->machine_name . " " . $h5pcontenttype->major_version . "." . $h5pcontenttype->minor_version;
                $typeFolder = $h5pcontenttype->machine_name . "-" . $h5pcontenttype->major_version . "." . $h5pcontenttype->minor_version;
                $typeIconPath = $webDir . "/courses/h5p/libraries/" . $typeFolder . "/icon.svg";
                $typeIconUrl = (file_exists($typeIconPath))
                    ? $urlAppend . "courses/h5p/libraries/" . $typeFolder . "/icon.svg"  // expected icon
                    : $urlAppend . "js/h5p-core/images/h5p_library.svg"; // fallback icon
                $dataContent = "data-content=\"<img src='$typeIconUrl' alt='$typeTitle' width='24px' height='24px'>$typeTitle\"";
                $tool_content .= "<option $dataContent>$typeVal</option>\n";
            }
        }

        $tool_content .= "
                </optgroup>
            </select>";

        // new button group for Create Dropdown
        $tool_content .= "</div><div class='btn-group'>";
    }

    // Update
    $tool_content .= "
        <a class='btn btn-success' href='update.php?course=$course_code' data-placement='bottom' data-toggle='tooltip'  title='$langMaj'>
            <span class='fa fa-refresh space-after-icon'></span>
            <span class='hidden-xs'>$langMaj</span>
        </a>";

    // Import
    $tool_content .= "
        <a class='btn btn-success' href='upload.php?course=$course_code' data-placement='bottom' data-toggle='tooltip'  title='$langImport'>
            <span class='fa fa-upload space-after-icon'></span>
            <span class='hidden-xs'>$langImport</span>
        </a>";

    // end custom action bar
    $tool_content .= "
                    </div>
                </div>
            </div>
        </div>";
}

if ($content) {
    $tool_content .= "<table class='table-default'>
        <thead>
            <tr class='list-header''>
                <th class='text-left'>$langH5pInteractiveContent</th>               
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
            $tool_content .= action_button([[
                'icon' => 'fa-edit',
                'title' => $langEditChange,
                'url' => "create.php?course=$course_code&amp;id=$item->id"
            ], [
                'icon' => 'fa-times',
                'title' => $langDelete,
                'url' => "delete.php?course=$course_code&amp;id=$item->id",
                'class' => 'delete',
                'confirm' => $langConfirmDelete
            ]], false);
        }
        $tool_content .= "</td></tr>";
    }
    $tool_content .= "</tbody></table>";
} else {
    $tool_content .= "<div class='alert alert-warning'>$langH5pNoContent</div>";
}

// utilize bootstrap-select for Add/Create dropdown button
// override default bootstrap-select style because we want trully white color (alpha of 1 instead of default 0.5)
$head_content .= "
    <link rel='stylesheet' href='${urlAppend}js/bootstrap-select/bootstrap-select.min.css'>
    <script type='text/javascript' src='${urlAppend}js/bootstrap-select/bootstrap-select.min.js'></script>
    <style>
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-secondary,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-success,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-danger,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-info,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-dark,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-secondary:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-success:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-danger:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-info:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-dark:hover,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-secondary:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-success:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-danger:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-info:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-dark:focus,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-primary:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-secondary:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-success:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-danger:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-info:active,
        .bootstrap-select > .dropdown-toggle.bs-placeholder.btn-dark:active {
          color: rgba(255, 255, 255, 1);
        }
    </style>
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#createpicker').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                window.location.href = '${urlAppend}modules/h5p/create.php?course=${course_code}&library=' + $('#createpicker').val();
            });
        });
    </script>";

draw($tool_content, 2, null, $head_content);
