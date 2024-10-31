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
$require_course_admin = true;
$require_login = true;

include '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';

$toolName = $langCourseCategoryActions;
add_units_navigation(TRUE);
load_js('tools.js');

$categories = Database::get()->queryArray("SELECT * FROM category WHERE active = 1 ORDER BY ordering, id");

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }

    // delete all category values
    Database::get()->query("DELETE FROM course_category WHERE course_id = ?d", $course_id);

    $catIndex = 1;
    foreach ($categories as $category) {

        // form parameters
        $inputName = "cat" . $catIndex;
        if ($category->multiple) {
            $selectActive = $inputName . "Active";
            if (isset($_POST[$selectActive])) {
                foreach ($_POST[$selectActive] as $mid_ref) {
                    $mid = getDirectReference($mid_ref);
                    Database::get()->query("INSERT INTO course_category (course_id, category_value_id) VALUES (?d, ?d)", $course_id, $mid);
                }
            }
        } else {
            if (isset($_POST[$inputName])) {
                $mid = getDirectReference($_POST[$inputName]);
                Database::get()->query("INSERT INTO course_category (course_id, category_value_id) VALUES (?d, ?d)", $course_id, $mid);
            }
        }
        $catIndex++;
    }

    Session::flash('message',$langRegDone);
    Session::flash('alert-class', 'alert-success');
}

$tool_content .= "
<div class='col-sm-12'>
<div class='card panelCard card-default px-lg-4 py-lg-3 mt-3'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
        <h3>" . $langCourseCategoryActions . "</h3>

    </div>
    <div class='card-body'>
    <form name='courseCategories' action='" . $_SERVER['SCRIPT_NAME'] . "?course=" . $course_code . "' method='post' enctype='multipart/form-data'>
        <div class='table-responsive mt-0'>
            <table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th width='45%'>" . $langInactiveCourseCategories . "</th>
                    <th width='10%'>" . $langMove . "</th>
                    <th width='45%'>" . $langActiveCourseCategories . "</th>
                </tr></thead>";

$submitOnClick = '';
$catIndex = 1;
foreach ($categories as $category) {
    $name = Hierarchy::unserializeLangField($category->name);
    $tool_content .= "<tr><th width='45%' colspan='3'>" . $name . "</th></tr>";

    // form parameters
    $inputName = "cat" . $catIndex;

    if ($category->multiple) {

        // form parameters
        $selectInactive = $inputName . "Inactive[]";
        $selectActive = $inputName . "Active[]";
        $idInactive = $inputName . "_inactive_box";
        $idActive = $inputName . "_active_box";
        $submitOnClick .= "selectAll('" . $idActive. "', true); ";

        // inactive category values
        $inactiveOpts = '';
        $inactiveVals = Database::get()->queryArray("SELECT * 
          FROM category_value 
          WHERE category_id = ?d 
          AND active = 1 
          AND id NOT IN (SELECT category_value_id FROM course_category WHERE course_id = ?d) 
          ORDER BY ordering, id", $category->id, $course_id);
        foreach ($inactiveVals as $inactiveVal) {
            $inactiveValName = Hierarchy::unserializeLangField($inactiveVal->name);
            $mid = getIndirectReference($inactiveVal->id);
            $inactiveOpts .= "<option value='" . $mid . "'>" . q($inactiveValName) . "</option>";
        }

        // active category values
        $activeOpts = '';
        $activeVals = Database::get()->queryArray("SELECT * 
          FROM category_value 
          WHERE category_id = ?d 
          AND active = 1 
          AND id IN (SELECT category_value_id FROM course_category WHERE course_id = ?d) 
          ORDER BY ordering, id", $category->id, $course_id);
        foreach ($activeVals as $activeVal) {
            $activeValName = Hierarchy::unserializeLangField($activeVal->name);
            $mid = getIndirectReference($activeVal->id);
            $activeOpts .= "<option value='" . $mid . "'>" . q($activeValName) . "</option>";
        }

        $tool_content .= "
                <tr>
                    <td>
                        <select aria-label='$langInactiveCourseCategories' class='form-select h-100 rounded-0' name='" . $selectInactive . "' id='" . $idInactive . "' size='17' multiple>" . $inactiveOpts . "</select>
                    </td>
                    <td>
                        <button type='button' aria-label='$langMove' class='btn submitAdminBtn m-auto d-block' onClick=\"move('" . $idInactive . "','" . $idActive . "')\"><span class='fa fa-arrow-right'></span></button><br><br>
                        <button type='button' aria-label='$langMove' class='btn submitAdminBtn m-auto d-block' onClick=\"move('" . $idActive . "','" . $idInactive . "')\"><span class='fa fa-arrow-left'></span></button>
                    </td>
                    <td>
                        <select aria-label='$langActiveCourseCategories' class='form-select h-100 rounded-0' name='" . $selectActive . "' id='" . $idActive . "' size='17' multiple>" . $activeOpts . "</select>
                    </td>
                </tr>";

    } else {
        $allVals = Database::get()->queryArray("SELECT * FROM category_value WHERE category_id = ?d AND active = 1 ORDER BY ordering, id", $category->id);
        $curVal = Database::get()->querySingle("SELECT * 
          FROM category_value 
          WHERE category_id = ?d 
          AND active = 1 
          AND id IN (SELECT category_value_id FROM course_category WHERE course_id = ?d) 
          ORDER BY ordering, id LIMIT 1", $category->id, $course_id);

        $tool_content .= "<tr><td colspan='3'><div class='col-sm-10'>";

        foreach($allVals as $val) {
            $valName = Hierarchy::unserializeLangField($val->name);
            $mid = getIndirectReference($val->id);
            $checked = '';
            if ($curVal && $curVal->id == $val->id) {
                $checked = " checked='checked' ";
            }

            $tool_content .= "
                        <div class='radio'>
                            <label>
                                <input type='radio' name='" . $inputName . "' value='" . $mid . "' id='" . $mid . "' " . $checked . ">" . $valName . "
                            </label>
                        </div>";
        }

        $tool_content .= "</div></td></tr>";
    }

    $catIndex++;
}

$tool_content .= "
                <tr>
                    <td colspan='3'>
                        <input type='submit' class='btn submitAdminBtn m-auto d-block' value='$langSubmit' name='submit' onClick=\"" . $submitOnClick . "\" />
                    </td>
                </tr>
            </table>
        </div>" . generate_csrf_token_form_field() . "
    </form></div>
</div></div>";

draw($tool_content, 2, null, $head_content);
