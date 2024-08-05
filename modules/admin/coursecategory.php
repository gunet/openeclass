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
            $pageName = $langCategoryAdd;
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

// link to add a new course category
if (!isset($_REQUEST['action'])) {
    $tool_content .= action_bar(array(
            array('title' => $langAdd,
                'url' => "$_SERVER[SCRIPT_NAME]?action=add",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success')
        ));
}

// Display all available course categories
if (!isset($_GET['action'])) {
    $categories = Database::get()->queryArray("SELECT * FROM category ORDER BY ordering, id");
    if (count($categories) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>" . $langNoResult . "</span></div></div>";
    } else {
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th>$langAllCourseCategories</th><th class='text-end' aria-label='$langSettingSelect'>".icon('fa-gears', $langActions)."</th></tr></thead>";
        foreach ($categories as $category) {
            $name = Hierarchy::unserializeLangField($category->name);
            $visibility = $category->active ? '' : ' class=not_visible';
            $tool_content .= "<tr><td$visibility>" . $name . "</td><td class='option-btn-cell text-end'>";
            $tool_content .= action_button(array(
                array(
                    'title' => $langEditChange,
                    'icon' => 'fa-edit',
                    'url' => "coursecategory.php?action=edit&amp;id=" . $category->id
                ),
                array(
                    'title' => $langEditCourseCategoryValues,
                    'icon' => 'fa-list',
                    'url' => "coursecategoryvalues.php?category=" . $category->id
                ),
                array(
                    'title' => $langDelete,
                    'icon' => 'fa-xmark',
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

        list($names, $name, $ordering, $multiple, $searchable, $active) = prepareDataFromPost();

        if (empty($names)) {
            //Session::Messages($langEmptyNodeName, 'alert alert-danger');
            Session::flash('message',$langEmptyNodeName);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/coursecategory.php?action=add');
        } else {
            // OK Create the new course category
            $q = "INSERT INTO category (name, ordering, multiple, searchable, active) VALUES (?s, ?d, ?d, ?d, ?d)";
            Database::get()->query($q, $name, $ordering, $multiple, $searchable, $active);
            //Session::Messages($langAddSuccess, 'alert alert-success');
            Session::flash('message',$langAddSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('modules/admin/coursecategory.php');
        }
    } else {
        // Display form for new course category information
        $tool_content .= displayForm();
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
            //Session::Messages("$langCourseCategoryProErase<br>$langCourseCategoryNoErase", 'alert-danger');
            Session::flash('message',"$langCourseCategoryProErase<br>$langCourseCategoryNoErase");
            Session::flash('alert-class', 'alert-danger');
        } else {
            // The category can be deleted
            Database::get()->query("DELETE FROM category WHERE id = ?d", $id);
            //Session::Messages($langCourseCategoryErase, 'alert alert-success');
            Session::flash('message',$langCourseCategoryErase);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page('modules/admin/coursecategory.php');
    }
}
// Edit a course category
elseif (isset($_GET['action']) and $_GET['action'] == 'edit') {
    $id = intval($_REQUEST['id']);

    if (isset($_POST['edit'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }

        list($names, $name, $ordering, $multiple, $searchable, $active) = prepareDataFromPost();

        if (empty($names)) {
            //Session::Messages($langEmptyNodeName, 'alert alert-danger');
            Session::flash('message',$langEmptyNodeName);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/coursecategory.php?action=edit&id=' . $id);
        } else {
            // OK Update the course category
            $q = "UPDATE category SET name = ?s, ordering = ?d, multiple = ?d, searchable = ?d, active = ?d WHERE id = ?d";
            Database::get()->query($q, $name, $ordering, $multiple, $searchable, $active, $id);
            //Session::Messages($langEditCourseCategorySuccess, 'alert alert-success');
            Session::flash('message',$langEditCourseCategorySuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('modules/admin/coursecategory.php');
        }
    } else {
        // Display form for edit course category information
        $mycat = Database::get()->querySingle("SELECT name, ordering, multiple, searchable, active FROM category WHERE id = ?d", $id);
        $tool_content .= displayForm($id, $mycat->name, $mycat->ordering, $mycat->multiple, $mycat->searchable, $mycat->active);
    }
}

draw($tool_content, null, null, $head_content);


function prepareDataFromPost() {
    global $session;

    $names = array();
    foreach ($session->active_ui_languages as $key => $langcode) {
        $n = (isset($_POST['name-' . $langcode])) ? $_POST['name-' . $langcode] : null;
        if (!empty($n)) {
            $names[$langcode] = $n;
        }
    }
    $name = serialize($names);

    $ordering = (isset($_POST['ordering'])) ? intval($_POST['ordering']) : 0;
    $multiple = (isset($_POST['multiple'])) ? 1 : 0;
    $searchable = (isset($_POST['searchable'])) ? 1 : 0;
    $active = (isset($_POST['active'])) ? 1 : 0;

    return array($names, $name, $ordering, $multiple, $searchable, $active);
}

function displayForm($id = null, $name = null, $ordering = null, $multiple = null, $searchable = null, $active = null) {
    global $session, $langNameOfLang, $urlAppend, $langImgFormsDes, $langSettingSelect;

    $html = '';
    $action = ($id == null) ? 'add' : 'edit';
    $actionValue = ($id == null) ? $GLOBALS['langAdd'] : $GLOBALS['langAcceptChanges'];

    $html .= "
    <div class='row'>
       
    <div class='col-lg-6 col-12'><div class='form-wrapper form-edit border-0 px-0'>
        <form role='form' class='form-horizontal' method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?action=" . $action . "'>
        <fieldset>";

    // name multi-lang field
    $is_serialized = false;
    if ($id != null) {
        $names = @unserialize($name);
        if ($names !== false) {
            $is_serialized = true;
        }
    }

    $i = 0;
    foreach ($session->active_ui_languages as $key => $langcode) {
        $nameValue = '';
        if ($id != null) {
            $n = ($is_serialized && isset($names[$langcode])) ? $names[$langcode] : '';
            if (!$is_serialized && $key == 0) {
                $n = $name;
            }
            $nameValue = "value='" . q($n) . "'";
        }

        $langSuffix = " (" . $langNameOfLang[langcode_to_name($langcode)] . ")";
        $html .= "<div class='form-group mb-4'><label for='code_id' class='col-sm-12 control-label-notes'>" . $GLOBALS['langName'] . $langSuffix . "</label>";
        $tdpre = ($i >= 0) ? "<div class='col-sm-12'>" : '';
        $placeholder = $GLOBALS['langCourseCategory2'] . $langSuffix;
        $html .= $tdpre . "<input id='code_id' class='form-control' type='text' name='name-" . q($langcode) . "' " . $nameValue . " placeholder='$placeholder'></div></div>";
        $i++;
    }

    // ordering input
    $orderingValue = ($id != null) ? "value='" . $ordering . "'" : '';
    $html .= "
    <div class='form-group mt-4'>
        <label for='ordering_id' class='col-sm-12 control-label-notes'>" . $GLOBALS['langReorder'] . "</label>
        <div class='col-sm-12'>
            <input id='ordering_id' class='form-control' type='text' name='ordering' " . $orderingValue . " placeholder='". $GLOBALS['langCourseCategoryOrdering2'] . "'>
        </div>
    </div>";

    // checkboxes
    $checked = " checked='checked' ";
    $check_multiple = $check_searchable = $check_active = $checked;
    if ($id != null) {
        $check_multiple = ($multiple == 1) ? $checked : '';
        $check_searchable = ($searchable == 1) ? $checked : '';
        $check_active = ($active == 1) ? $checked : '';
    }

    $html .= "
    <div class='form-group mt-4'>
        <label class='col-sm-12 control-label-notes'>" . $GLOBALS['langCourseCategoryMultiple'] . "</label>
        <div class='col-sm-12'>
            <label class='label-container' aria-label='$langSettingSelect'>
                <input type='checkbox' name='multiple' value='1' " . $check_multiple . ">
                <span class='checkmark'></span>
                " . $GLOBALS['langCourseCategoryMultiple2'] . "
            </label>
        </div>
    </div>
    <div class='form-group mt-4'>
        <label class='col-sm-12 control-label-notes'>" . $GLOBALS['langCourseCategorySearchable'] . "</label>
        <div class='col-sm-12'>
            <label class='label-container' aria-label='$langSettingSelect'>
                <input type='checkbox' name='searchable' value='1' " . $check_searchable . ">
                <span class='checkmark'></span>
                " . $GLOBALS['langCourseCategorySearchable2'] . "
            </label>
        </div>
    </div>
    <div class='form-group mt-4'>
        <label class='col-sm-12 control-label-notes'>" . $GLOBALS['langChatActive'] . "</label>
        <div class='col-sm-12'>
            <label class='label-container' aria-label='$langSettingSelect'>
                <input type='checkbox' name='active' value='1' " . $check_active . ">
                <span class='checkmark'></span>
                " . $GLOBALS['langCourseCategoryActive2'] . "
            </label>
        </div>
    </div>";

    if ($id != null) {
        $html .= "<input type='hidden' name='id' value='" . $id . "' />";
    }

    $html .= "
    <div class='form-group mt-5 d-flex justify-content-end align-items-center'>
       
           "
                . form_buttons(array(
                    array(
                        'class' => 'submitAdminBtn',
                        'text' => $GLOBALS['langSave'],
                        'name' => $action,
                        'value'=> $actionValue
                    ),
                    array(
                        'class' => 'cancelAdminBtn ms-1',
                        'href' => $_SERVER['SCRIPT_NAME']
                    )
                )) .
           "
        
    </div>
    </fieldset>
    ". generate_csrf_token_form_field() ."
    </form>
    </div></div>
    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div></div>";

    return $html;
}
