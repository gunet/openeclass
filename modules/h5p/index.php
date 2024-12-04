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

$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'h5p';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'classes/H5PFactory.php';

$toolName = $langH5p;

$onlyEnabledWhere = ($is_editor) ? '' : " AND enabled = 1 ";
$content = Database::get()->queryArray("SELECT * FROM h5p_content WHERE course_id = ?d $onlyEnabledWhere ORDER BY id ASC", $course_id);

if ($is_editor) {
    $factory = new H5PFactory();
    $editorAjax = $factory->getH5PEditorAjax();
    $h5pcontenttypes = $editorAjax->getLatestLibraryVersions();

    // custom action bar
    $tool_content .= "
        <div class='action_bar'>
            <div class='col-sm-12 d-flex justify-content-end mt-3'>
                <div class='margin-top-thin margin-bottom-fat'>
                    <div class='btn-group'>";

    // Dropdown select for Creating H5P Content
    if (!empty($h5pcontenttypes)) {
        $tool_content .= "
            <select id='createpicker' class='selectpicker' title='$langCreate' data-style='submitAdminBtn' data-width='fit'>
                <optgroup label='$langH5pInteractiveContent'>";
        $counter = 0;
        foreach ($h5pcontenttypes as $h5pcontenttype) {
            if ($h5pcontenttype->enabled) {
                if ($counter == 0) {
                   $tool_content .= "<option selected></option>";
                }
                $typeTitle = $h5pcontenttype->title;
                $typeVal = $h5pcontenttype->machine_name . " " . $h5pcontenttype->major_version . "." . $h5pcontenttype->minor_version;
                $typeFolder = $h5pcontenttype->machine_name . "-" . $h5pcontenttype->major_version . "." . $h5pcontenttype->minor_version;
                $typeIconPath = $webDir . "/courses/h5p/libraries/" . $typeFolder . "/icon.svg";
                $typeIconUrl = (file_exists($typeIconPath))
                    ? $urlAppend . "courses/h5p/libraries/" . $typeFolder . "/icon.svg"  // expected icon
                    : $urlAppend . "resources/icons/h5p_library.svg"; // fallback icon
                $dataContent = "data-content=\"<img src='$typeIconUrl' alt='$typeTitle' width='24px' height='24px'>$typeTitle\"";
                $tool_content .= "<option $dataContent>$typeVal</option>\n";
            }
            $counter++;
        }

        $tool_content .= "
                </optgroup>
            </select>";

        // new button group for Create Dropdown
        $tool_content .= "</div><div class='btn-group'>";
    }

    // Import
    $tool_content .= "
        <a class='btn submitAdminBtn ms-2' href='upload.php?course=$course_code' data-bs-placement='bottom' data-bs-toggle='tooltip'  title='$langImport' aria-label='$langImport'>
            <span class='fa-solid fa-upload space-after-icon settings-icons'></span>
            <span class='TextBold hidden-xs ps-2'>$langImport</span>
        </a>";

    // end custom action bar
    $tool_content .= "
                    </div>
                </div>
            </div>
        </div>";

    // Control Flags
    if (isset($_GET['choice']) && isset($_GET['id'])) {
        switch($_GET['choice']) {
            case 'do_disable':
                Database::get()->querySingle("UPDATE h5p_content set enabled = 0 WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
                Session::flash('message',$langH5pSaveSuccess);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/h5p/index.php?course=$course_code");
                break;
            case 'do_enable':
                Database::get()->querySingle("UPDATE h5p_content set enabled = 1 WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
                Session::flash('message',$langH5pSaveSuccess);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/h5p/index.php?course=$course_code");
                break;
            case 'do_reuse_disable':
                Database::get()->querySingle("UPDATE h5p_content set reuse_enabled = 0 WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
                Session::flash('message',$langH5pSaveSuccess);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/h5p/index.php?course=$course_code");
                break;
            case 'do_reuse_enable':
                Database::get()->querySingle("UPDATE h5p_content set reuse_enabled = 1 WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
                Session::flash('message',$langH5pSaveSuccess);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/h5p/index.php?course=$course_code");
                break;
        }
    }
}

if ($content) {
   $tool_content .= "<div class='col-12 mt-4'><div class='table-responsive'><table class='table-default'>
        <thead>
            <tr class='list-header'>
                <th>$langH5pInteractiveContent</th>
                <th>$langTypeH5P</th>";
                if ($is_editor) {
                    $tool_content .= "
                        <th aria-label='$langSettingSelect'>
                        <span class='fa fa-gears'></span>
                    </th>";
                }
    $tool_content .= "
            </tr>
        </thead>
        <tbody>";

    foreach ($content as $item) {
        $q = Database::get()->querySingle("SELECT machine_name, title, major_version, minor_version
                                            FROM h5p_library WHERE id = ?s", $item->main_library_id);
        $h5p_content_type_title = $q->title;
        $typeFolder = $q->machine_name . "-" . $q->major_version . "." . $q->minor_version;
        $typeIconPath = $webDir . "/courses/h5p/libraries/" . $typeFolder . "/icon.svg";
        $typeIconUrl = (file_exists($typeIconPath))
            ? $urlAppend . "courses/h5p/libraries/" . $typeFolder . "/icon.svg"  // expected icon
            : $urlAppend . "resources/icons/h5p_library.svg"; // fallback icon

        $tool_content .= "<tr" . ($item->enabled ? '' : " class='not_visible'") . ">
                    <td>
                        <a href='view.php?course=$course_code&amp;id=$item->id'>$item->title</a>
                    </td>
                    <td>
                        <img src='$typeIconUrl' alt='$h5p_content_type_title' width='30px' height='30px'> <em>$h5p_content_type_title</em>
                    </td>";
        if ($is_editor) {
            $tool_content .= "<td class='text-end'>";
            $tool_content .= action_button([[
                'icon' => 'fa-edit',
                'title' => $langEditChange,
                'url' => "create.php?course=$course_code&amp;id=$item->id"
            ], [
                'icon' => $item->enabled ? 'fa-eye': 'fa-eye-slash',
                'title' => $item->enabled ? $langDeactivate : $langActivate,
                'url' => "index.php?course=$course_code&amp;id=$item->id&amp;choice=do_" . ($item->enabled ? 'disable' : 'enable')
            ], [
                'icon' => $item->reuse_enabled ? 'fa-bell': 'fa-bell-slash',
                'title' => $item->reuse_enabled ? $langReuseDeactivate : $langReuseActivate,
                'url' => "index.php?course=$course_code&amp;id=$item->id&amp;choice=do_reuse_" . ($item->reuse_enabled ? 'disable' : 'enable')
            ], [
                'icon' => 'fa-xmark',
                'title' => $langDelete,
                'url' => "delete.php?course=$course_code&amp;id=$item->id",
                'class' => 'delete',
                'confirm' => $langConfirmDelete
            ]], false);
            $tool_content .= "</td>";
        }
        $tool_content .= "</tr>";
    }
    $tool_content .= "</tbody></table></div></div>";
} else {
    $tool_content .= "<div class='col-sm-12 mt-3'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langH5pNoContent</span></div></div>";
}

// utilize bootstrap-select for Add/Create dropdown button
// override default bootstrap-select style because we want trully white color (alpha of 1 instead of default 0.5)
$head_content .= "
    <link rel='stylesheet' href='{$urlAppend}js/bootstrap-select/bootstrap-select.min.css'>
    <script type='text/javascript' src='{$urlAppend}js/bootstrap-select/bootstrap5-select.min.js'></script>
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
        .dropdown-menu.inner.show{
            max-height:400px;
        }
    </style>
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#createpicker').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                window.location.href = '{$urlAppend}modules/h5p/create.php?course=$course_code&library=' + $('#createpicker').val();
            });
        });
    </script>";

draw($tool_content, 2, null, $head_content);
