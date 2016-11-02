<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
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
 * ======================================================================== 
 */

$require_admin = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';


$toolName = $langCourseCategoryActions;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_GET['action'])) {
    $navigation[] = array('url' => $_SERVER['SCRIPT_NAME'], 'name' => $langCourseCategoryActions);
    switch ($_GET['action']) {
        case 'add':
            $pageName = $langCourseCategoryAdd;
            break;
        case 'delete':
            $pageName = $langCourseCategoryDel;
            break;
        case 'edit':
            $pageName = $langCourseCategoryEdit;
            break;
    }
}

// handle current lang missing from active langs
if (!in_array($language, $session->active_ui_languages)) {
    array_unshift($session->active_ui_languages, $language);
}

// link to add a new node
if (!isset($_REQUEST['action'])) {
    $tool_content .= action_bar(array(
            array('title' => $langAdd,
                'url' => "$_SERVER[SCRIPT_NAME]?action=add",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
        array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
} else {
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
}

// Display all available course categories
if (!isset($_GET['action'])) {
    $categories = Database::get()->queryArray("SELECT * FROM category ORDER BY id");
    if (count($categories) == 0) {
        $tool_content .= "<div class='alert alert-warning'>" . $langNoResult . "</div>";
    } else {
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<th>$langAllCourseCategories</th><th class='text-right'>".icon('fa-gears', $langActions)."</th>";
        foreach ($categories as $category) {
            $name = Hierarchy::unserializeLangField($category->name);
            $visibility = $category->active ? '' : ' class=not_visible';
            $tool_content .= "<tr><td$visibility>" . $name . "</td><td class='option-btn-cell'>";
            $tool_content .= action_button(array(
                array(
                    'title' => $langEditChange,
                    'icon' => 'fa-edit',
                    'url' => "coursecategory.php?action=edit&amp;id=" . $category->id
                ),
                array(
                    'title' => $langDelete,
                    'icon' => 'fa-times',
                    'url' => "coursecategory.php?action=delete&amp;id=" . $category->id
                )));
                $tool_content .= "</td><tr>";
        }
        $tool_content .= "</table></div>";
    }
}
// Add a new course category
elseif (isset($_GET['action']) && $_GET['action'] == 'add') {
    if (isset($_POST['add'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }
        
        $names = array();
        foreach ($session->active_ui_languages as $key => $langcode) {
            $n = (isset($_POST['name-' . $langcode])) ? $_POST['name-' . $langcode] : null;
            if (!empty($n)) {
                $names[$langcode] = $n;
            }
        }
        $name = serialize($names);

        $multiple = (isset($_POST['multiple'])) ? 1 : 0;
        $searchable = (isset($_POST['searchable'])) ? 1 : 0;
        $active = (isset($_POST['active'])) ? 1 : 0;

        if (empty($names)) {
            $tool_content .= "<div class='alert alert-danger'>" . $langEmptyCourseCategoryName . "</div><br>";
            $tool_content .= action_bar(array(
                array('title' => $langReturnToAddCourseCategory,
                    'url' => $_SERVER['SCRIPT_NAME'] . "?action=add",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));
        } else {
            // OK Create the new course category
            $q = "INSERT INTO category (name, multiple, searchable, active) VALUES (?s, ?d, ?d, ?d)";
            Database::get()->query($q, $name, $multiple, $searchable, $active);
            $tool_content .= "<div class='alert alert-success'>" . $langAddSuccess . "</div>";
        }
    } else {
        // Display form for new course category information
        $tool_content .= "<div class='form-wrapper'>
            <form role='form' class='form-horizontal' method=\"post\" action=\"" . $_SERVER['SCRIPT_NAME'] . "?action=add\">
            <fieldset>";

        // name multi-lang field
        $i = 0;
        foreach ($session->active_ui_languages as $key => $langcode) {
            $tool_content .= "<div class='form-group'><label class='col-sm-3 control-label'>$langName:</label>";
            $tdpre = ($i >= 0) ? "<div class='col-sm-9'>" : '';
            $placeholder = "$langCourseCategory2 (" . $langNameOfLang[langcode_to_name($langcode)] . ")";
            $tool_content .= $tdpre . "<input class='form-control' type='text' name='name-" . $langcode . "' placeholder='$placeholder'></div></div>";
            $i++;
        }

        $tool_content .= "
        <div class='form-group'>
            <label class='col-sm-3 control-label'>$langCourseCategoryMultiple</label>
            <div class='col-sm-9'>
                <input class='form-control' type='checkbox' name='multiple' value='1' checked='checked'>
                <span class='help-block'><small>$langCourseCategoryMultiple2</small></span>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>$langCourseCategorySearchable</label>
            <div class='col-sm-9'>
                <input class='form-control' type='checkbox' name='searchable' value='1' checked='checked'>
                <span class='help-block'><small>$langCourseCategorySearchable2</small></span>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>$langCourseCategoryActive</label>
            <div class='col-sm-9'>
                <input class='form-control' type='checkbox' name='active' value='1' checked='checked'>
                <span class='help-block'><small>$langCourseCategoryActive2</small></span>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-9 col-sm-offset-3'>" . form_buttons(array(
                array(
                    'text' => $langSave,
                    'name' => 'add',
                    'value'=> $langAdd
                ),
                array(
                    'href' => $_SERVER['SCRIPT_NAME']
                )
            )) . "
            </div>
        </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."
        </form>
        </div>";
    }
}
// Delete course category
elseif (isset($_GET['action']) and $_GET['action'] == 'delete') {
    $id = intval($_GET['id']);

    // locate the category we want to delete
    $category = Database::get()->querySingle("SELECT * from category WHERE id = ?d", $id);

    if ($category !== false) {
        // locate any category values belonging to this category
        $c = 0;
        $c += Database::get()->querySingle("SELECT COUNT(*) AS count FROM category_value WHERE category_id = ?d", $id)->count;

        if ($c > 0) {
            // The category cannot be deleted
            Session::Messages("$langCourseCategoryProErase<br>$langCourseCategoryNoErase", 'alert-danger');
        } else {
            // The category can be deleted
            Database::get()->query("DELETE FROM category WHERE id = ?d", $id);
            Session::Messages($langCourseCategoryErase, 'alert alert-success');
        }
        redirect_to_home_page('modules/admin/coursecategory.php');
    }
}
// Edit a course category
elseif (isset($_GET['action']) and $_GET['action'] == 'edit') {
    $id = intval($_REQUEST['id']);
}

draw($tool_content, 3, null, $head_content);