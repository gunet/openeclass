<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

$catId = intval($_GET['category']);
if ($catId <= 0) {
    redirect_to_home_page('modules/admin/index.php');
}

$toolName = $langCourseCategoryValues;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'coursecategory.php', 'name' => $langCourseCategoryActions);

if (isset($_GET['action'])) {
    $navigation[] = array('url' => $_SERVER['SCRIPT_NAME'] . '?category=' . $catId, 'name' => $langCourseCategoryValues);
    switch ($_GET['action']) {
        case 'add':
            $pageName = $langCourseCategoryValueAdd;
            break;
        case 'delete':
            $pageName = $langCourseCategoryValueDel;
            break;
        case 'edit':
            $pageName = $langCourseCategoryValueEdit;
            break;
    }
}

// handle current lang missing from active langs
if (!in_array($language, $session->active_ui_languages)) {
    array_unshift($session->active_ui_languages, $language);
}

// link to add a new category value
if (!isset($_REQUEST['action'])) {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
                'url' => $_SERVER['SCRIPT_NAME'] . "?category=" . $catId,
                'icon' => 'fa-reply',
                'level' => 'primary'),
            array('title' => $langAdd,
                'url' => $_SERVER['SCRIPT_NAME'] . "?category=" . $catId . "&amp;action=add",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success')
        ));
} else {
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => $_SERVER['SCRIPT_NAME'] . "?category=" . $catId,
                'icon' => 'fa-reply',
                'level' => 'primary')));
}

// Display all available course category values
if (!isset($_GET['action'])) {
    $values = Database::get()->queryArray("SELECT * FROM category_value WHERE category_id = ?d ORDER BY ordering, id", $catId);
    if (count($values) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>" . $langNoResult . "</span></div></div>";
    } else {
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th>$langAllCourseCategoryValues</th><th class='text-end' aria-label='$langSettingSelect'>".icon('fa-gears', $langActions)."</th></tr></thead>";
        foreach ($values as $value) {
            $name = Hierarchy::unserializeLangField($value->name);
            $visibility = $value->active ? '' : ' class=not_visible';
            $tool_content .= "<tr><td$visibility>" . $name . "</td><td class='option-btn-cell text-end'>";
            $tool_content .= action_button(array(
                array(
                    'title' => $langEditChange,
                    'icon' => 'fa-edit',
                    'url' => "coursecategoryvalues.php?category=" . $catId . "&amp;action=edit&amp;id=" . $value->id
                ),
                array(
                    'title' => $langDelete,
                    'icon' => 'fa-xmark',
                    'url' => "coursecategoryvalues.php?category=" . $catId . "&amp;action=delete&amp;id=" . $value->id
                )));
                $tool_content .= "</td><tr>";
        }
        $tool_content .= "</table></div>";
    }
}
// Add a new course category value
elseif (isset($_GET['action']) && $_GET['action'] == 'add') {
    if (isset($_POST['add'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }

        list($names, $name, $ordering, $active) = prepareDataFromPost();

        if (empty($names)) {
            //Session::Messages($langEmptyNodeName, 'alert alert-danger');
            Session::flash('message',$langEmptyNodeName); 
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/coursecategoryvalues.php?category=' . $catId . '&action=add');
        } else {
            // OK Create the new course category value
            $q = "INSERT INTO category_value (category_id, name, ordering, active) VALUES (?d, ?s, ?d, ?d)";
            Database::get()->query($q, $catId, $name, $ordering, $active);
            //Session::Messages($langAddSuccess, 'alert-success');
            Session::flash('message',$langAddSuccess); 
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('modules/admin/coursecategoryvalues.php?category=' . $catId);
        }
    } else {
        // Display form for new course category value information
        $tool_content .= displayForm();
    }
}
// Delete course category value
elseif (isset($_GET['action']) and $_GET['action'] == 'delete') {
    $id = intval($_GET['id']);

    // locate the category we want to delete
    $value = Database::get()->querySingle("SELECT * from category_value WHERE id = ?d", $id);

    if ($value !== false) {
        // The category value can be deleted
        Database::get()->query("DELETE FROM course_category WHERE category_value_id = ?d", $id);
        Database::get()->query("DELETE FROM category_value WHERE id = ?d", $id);
        //Session::Messages($langCourseCategoryValueErase, 'alert-success');
        Session::flash('message',$langCourseCategoryValueErase); 
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/admin/coursecategoryvalues.php?category=' . $catId);
    }
}
// Edit a course category value
elseif (isset($_GET['action']) and $_GET['action'] == 'edit') {
    $id = intval($_REQUEST['id']);

    if (isset($_POST['edit'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) { csrf_token_error(); }

        list($names, $name, $ordering, $active) = prepareDataFromPost();

        if (empty($names)) {
            //Session::Messages($langEmptyNodeName, 'alert-danger');
            Session::flash('message',$langEmptyNodeName); 
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/coursecategoryvalues.php?category=' . $catId . '&action=edit&id=' . $id);
        } else {
            // OK Update the course category value
            $q = "UPDATE category_value SET name = ?s, ordering = ?d, active = ?d WHERE id = ?d";
            Database::get()->query($q, $name, $ordering, $active, $id);
            //Session::Messages($langEditCourseCategoryValueSuccess, 'alert-success');
            Session::flash('message',$langEditCourseCategoryValueSuccess); 
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('modules/admin/coursecategoryvalues.php?category=' . $catId);
        }
    } else {
        // Display form for edit course category information
        $myval = Database::get()->querySingle("SELECT name, ordering, active FROM category_value WHERE id = ?d", $id);
        $tool_content .= displayForm($id, $myval->name, $myval->ordering, $myval->active);
    }
}

draw($tool_content, 3, null, $head_content);


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
    $active = (isset($_POST['active'])) ? 1 : 0;

    return array($names, $name, $ordering, $active);
}

function displayForm($id = null, $name = null, $ordering = null, $active = null) {
    global $catId, $session, $langNameOfLang, $urlAppend, $langImgFormsDes, $langSettingSelect, $langForm;

    $html = '';
    $action = ($id == null) ? 'add' : 'edit';
    $actionValue = ($id == null) ? $GLOBALS['langAdd'] : $GLOBALS['langAcceptChanges'];

    $html .= "
    <div class='row'>
        
    
    <div class='col-lg-6 col-12'><div class='form-wrapper form-edit border-0 px-0'>
        <form role='form' class='form-horizontal' method='post' action='" . $_SERVER['SCRIPT_NAME'] . "?category=" . $catId . "&amp;action=" . $action . "'>
        <fieldset><legend class='mb-0' aria-label='$langForm'></legend>";

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
        $html .= "<div class='form-group mb-4'><label for='name_".q($langcode)."' class='col-sm-12 control-label-notes'>" . $GLOBALS['langName'] . $langSuffix . "</label>";
        $tdpre = ($i >= 0) ? "<div class='col-sm-12'>" : '';
        $placeholder = $GLOBALS['langCourseCategoryValue2'] . $langSuffix;
        $html .= $tdpre . "<input id='name_".q($langcode)."' class='form-control' type='text' name='name-" . q($langcode) . "' " . $nameValue . " placeholder='$placeholder'></div></div>";
        $i++;
    }

    // ordering input
    $orderingValue = ($id != null) ? "value='" . $ordering . "'" : '';
    $html .= "
    <div class='form-group mt-4'>
        <label for='ordering_id' class='col-sm-12 control-label-notes'>" . $GLOBALS['langCourseCategoryValueOrdering'] . ":</label>
        <div class='col-sm-12'>
            <input id='ordering_id' class='form-control' type='text' name='ordering' " . $orderingValue . " placeholder='". $GLOBALS['langCourseCategoryValueOrdering2'] . "'>
        </div>
    </div>";

    // checkboxes
    $checked = " checked='checked' ";
    $check_active = $checked;
    if ($id != null) {
        $check_active = ($active == 1) ? $checked : '';
    }

    $html .= "
    <div class='form-group mt-4'>
        <label class='col-sm-12 control-label-notes'>" . $GLOBALS['langChatActive'] . "</label>
        <div class='col-sm-12'>
            <div class='checkbox'>
                <label class='label-container' aria-label='$langSettingSelect'>
                    <input type='checkbox' name='active' value='1' " . $check_active . ">
                    <span class='checkmark'></span>
                    " . $GLOBALS['langCourseCategoryValueActive2'] . "
                </label>
            </div>
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
                            'href' =>  $_SERVER['SCRIPT_NAME'] . "?category=" . $catId
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
