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

$require_admin = true;
$require_help = true;
$helpTopic = ' users_administration';
$helpSubTopic = 'user_profile_fields';
require_once '../../include/baseTheme.php';
require 'modules/admin/custom_profile_fields_functions.php';

 if (isset($_POST['submit_field'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $name = $_POST['field_name'];
    $shortname = $_POST['field_shortname'];
    $description = $_POST['fielddescr'];
    $datatype = intval($_POST['datatype']);
    if (isset($_POST['required'])) {
        $required = intval($_POST['required']);
    } else {
        $required = 0;
    }
    if ($datatype == CPF_MENU && isset($_POST['options'])) {
        $data = explode(PHP_EOL, $_POST['options']);
        $data = serialize($data);
    } else {
        $data = '';
    }
    $registration = intval($_POST['registration']);
    $visibility = intval($_POST['visibility']);

    if (isset($_POST['field_id'])) { //save edited field
        $fieldid = intval($_POST['field_id']);

        //check for unique shortname
        $is_unique = true;
        $old_shortname = Database::get()->querySingle("SELECT shortname FROM custom_profile_fields WHERE id = ?d", $fieldid)->shortname;
        if ($shortname != $old_shortname) {
            $count = Database::get()->querySingle("SELECT COUNT(*) AS c FROM custom_profile_fields WHERE shortname = ?s", $shortname)->c;
            if ($count != 0) {
                $is_unique = false;
            }
        }

        if ($is_unique) {
            Database::get()->query("UPDATE custom_profile_fields SET name = ?s,
                                    shortname = ?s,
                                    description = ?s,
                                    datatype = ?d,
                                    required = ?d,
                                    visibility = ?d,
                                    user_type = 10,
                                    registration = ?d,
                                    data = ?s
                                    WHERE id = ?d", $name, $shortname, $description, $datatype, $required, $visibility, $registration, $data, $fieldid);
            Session::flash('message',$langCPFFieldEditSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/admin/custom_profile_fields.php");
        } else {
            Session::flash('message',$langCPFEditUniqueShortnameError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/admin/custom_profile_fields.php");
        }
    } else { //save new field

        //check for unique shortname
        $count = Database::get()->querySingle("SELECT COUNT(*) AS c FROM custom_profile_fields WHERE shortname = ?s", $shortname)->c;
        if ($count == 0) { //shortname is unique, proceed

            $catid = intval($_POST['catid']);

            $result = Database::get()->querySingle("SELECT MIN(sortorder) AS m FROM custom_profile_fields WHERE categoryid = ?d", $catid);
            if (!is_null($result->m)) {
                $sortorder = $result->m - 1;
            } else {
                $sortorder = 0;
            }

            Database::get()->query("INSERT INTO custom_profile_fields (shortname, name, description, datatype, categoryid, sortorder, required, visibility, user_type, registration, data)
                                    VALUES (?s, ?s, ?s, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s)", $shortname, $name, $description, $datatype, $catid, $sortorder, $required, $visibility, 10, $registration, $data);
            Session::flash('message',$langCPFFieldAddSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/admin/custom_profile_fields.php");
        } else { //shortname is not unique, abort
            Session::flash('message',$langCPFCreateUniqueShortnameError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/admin/custom_profile_fields.php");
        }
    }
} elseif (isset($_GET['del_field'])) { //delete fields
    $fieldid = intval($_GET['del_field']);
    //delete fields profile data
    Database::get()->query("DELETE custom_profile_fields_data FROM custom_profile_fields_data INNER JOIN custom_profile_fields
                            ON custom_profile_fields_data.field_id = custom_profile_fields.id
                            WHERE custom_profile_fields.id = ?d", $fieldid);
    Database::get()->query("DELETE custom_profile_fields_data_pending FROM custom_profile_fields_data_pending INNER JOIN custom_profile_fields
                            ON custom_profile_fields_data_pending.field_id = custom_profile_fields.id
                            WHERE custom_profile_fields.id = ?d", $fieldid);
    //delete field
    Database::get()->query("DELETE FROM custom_profile_fields WHERE id = ?d", $fieldid);
    Session::flash('message',$langCPFFieldDelSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/custom_profile_fields.php");
} elseif (isset($_GET['del_cat'])) { //delete category
    $catid = intval(getDirectReference($_GET['del_cat']));
    //delete fields profile data
    Database::get()->query("DELETE custom_profile_fields_data FROM custom_profile_fields_data INNER JOIN custom_profile_fields
                            ON custom_profile_fields_data.field_id = custom_profile_fields.id INNER JOIN custom_profile_fields_category
                            ON custom_profile_fields.categoryid = custom_profile_fields_category.id 
                            WHERE custom_profile_fields_category.id = ?d", $catid);
    Database::get()->query("DELETE custom_profile_fields_data_pending FROM custom_profile_fields_data_pending INNER JOIN custom_profile_fields
                            ON custom_profile_fields_data_pending.field_id = custom_profile_fields.id INNER JOIN custom_profile_fields_category
                            ON custom_profile_fields.categoryid = custom_profile_fields_category.id
                            WHERE custom_profile_fields_category.id = ?d", $catid);
    //delete fields
    Database::get()->query("DELETE custom_profile_fields FROM custom_profile_fields INNER JOIN custom_profile_fields_category
                            ON custom_profile_fields.categoryid = custom_profile_fields_category.id 
                            WHERE custom_profile_fields_category.id = ?d", $catid);
    //delete category
    Database::get()->query("DELETE FROM custom_profile_fields_category WHERE id = ?d", $catid);
    Session::flash('message',$langCPFCatDelSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/custom_profile_fields.php");

} elseif (isset($_POST['submit_cat'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    if (isset($_POST['cat_id'])) { //save edited category
        $catid = intval(getDirectReference($_POST['cat_id']));
        Database::get()->query("UPDATE custom_profile_fields_category SET name = ?s WHERE id = ?d", $_POST['cat_name'], $catid);
        Session::flash('message',$langCPFCatModSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/admin/custom_profile_fields.php");
    } else { //save new category
        //add category as last in sort order
        $result = Database::get()->querySingle("SELECT MIN(sortorder) AS m FROM custom_profile_fields_category");
        if (!is_null($result->m)) {
            $sortorder = $result->m - 1;
        } else {
            $sortorder = 0;
        }
        Database::get()->query("INSERT INTO custom_profile_fields_category (name, sortorder) VALUES (?s, ?d)", $_POST['cat_name'], $sortorder);
        Session::flash('message',$langCPFCatAddedSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/admin/custom_profile_fields.php");
    }
} elseif (isset($_POST['cats'])) { //save sort order
    $cats_counter = count($_POST['cats']);
    $fields_counter = count($_POST['fields']);

    foreach ($_POST['cats'] as $cat) {
        $cat_id = substr($cat, 4);
        Database::get()->query("UPDATE custom_profile_fields_category SET sortorder = ?d WHERE id = ?d", $cats_counter, $cat_id);
        $cats_counter--;
    }

    foreach ($_POST['fields'] as $field) {
        $field_id = substr($field, 6);
        Database::get()->query("UPDATE custom_profile_fields SET sortorder = ?d, categoryid=?d WHERE id = ?d", $fields_counter, substr($_POST['fields_cat'][$field], 4), $field_id);
        $fields_counter--;
    }

    exit;
}

$toolName = $langAdmin;
$pageName = $langCPFAdminSideMenuLink;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_GET['add_cat']) || isset($_GET['edit_cat'])) { //add a new category form

    $navigation[] = array('url' => 'custom_profile_fields.php', 'name' => $langCPFAdminSideMenuLink);
    $pageName = $langCategoryAdd;

    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => "custom_profile_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary')));

    load_js('validation.js');
    $data['catid'] = '';
    $data['cat_name'] = '';
    if (isset($_GET['edit_cat'])) {
        $data['catid'] = intval(getDirectReference($_GET['edit_cat']));
        $data['cat_name'] = Database::get()->querySingle("SELECT name FROM custom_profile_fields_category WHERE id = ?d", $data['catid'])->name;
    }

    $view = 'admin.users.custom_profile_fields.createCategory';
} elseif (isset($_GET['add_field'])) { //add new field form (first step)
    $data['catid'] = intval(getDirectReference($_GET['add_field']));
    $pageName = $langAddField;

    $data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                  'url' => "custom_profile_fields.php",
                  'icon' => 'fa-reply',
                  'level' => 'primary')));

    $data['field_types'] = [
        CPF_TEXTBOX => $langCPFText,
        CPF_TEXTAREA => $langCPFTextarea,
        CPF_DATE => $langCPFDate,
        CPF_MENU => $langCPFMenu,
        CPF_LINK => $langLink
    ];


    $view = 'admin.users.custom_profile_fields.createStep1';
} elseif (isset($_POST['add_field_proceed_step2'])) { //add new field form 2nd step
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $data['catid'] = intval(getDirectReference($_POST['catid']));

    load_js('validation.js');

    $pageName = $langAddField;
    $navigation[] = array('url' => 'custom_profile_fields.php', 'name' => $langCPFAdminSideMenuLink);

    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => "custom_profile_fields.php?add_field=" . getIndirectReference($data['catid']),
              'icon' => 'fa-reply',
              'level' => 'primary')));

    $data['yes_no'] = array(0 => $langNo, 1 => $langYes);
    $data['visibility'] = array(CPF_VIS_PROF => $langProfOnly, CPF_VIS_ALL => $langToAllUsers);

    $data['datatype'] = intval($_POST['datatype']);
    $data['fielddescr_rich_text'] = rich_text_editor('fielddescr', 8, 20, '');

    $view = 'admin.users.custom_profile_fields.createStep2';

} elseif (isset($_GET['edit_field'])) { //save edited field
    $pageName = $langCPFFieldEdit;
    $navigation[] = array('url' => 'custom_profile_fields.php', 'name' => $langCPFAdminSideMenuLink);

    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => "custom_profile_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary')));

    $data['fieldid'] = intval(getDirectReference($_GET['edit_field']));

    $result = Database::get()->querySingle("SELECT * FROM custom_profile_fields WHERE id = ?d", $data['fieldid']);
    if ($result) {

        $data['name'] = $name = q($result->name);
        $data['shortname'] = $shortname = q($result->shortname);
        $description = standard_text_escape($result->description);
        $data['datatype'] = $datatype = $result->datatype;
        $data['required'] = $result->required;
        $data['vis'] = $vis = $result->visibility;
        $data['registration'] = $registration = $result->registration;
        $custom_profile_fields_data = $result->data;

        if ($data['datatype'] == CPF_MENU) {
            $custom_profile_fields_data = unserialize($custom_profile_fields_data);
            $data['textarea_val'] = '';
            foreach ($custom_profile_fields_data as $line) {
                $data['textarea_val'] .= $line."\n";
            }
            $data['textarea_val'] = substr($data['textarea_val'], 0, strlen($data['textarea_val'])-1);
        }

        load_js('validation.js');
        $data['fielddescr_rich_text'] =  rich_text_editor('fielddescr', 8, 20, standard_text_escape($result->description));

        $data['field_types'] = $field_types = array(CPF_TEXTBOX => $langCPFText, CPF_TEXTAREA => $langCPFTextarea, CPF_DATE => $langCPFDate, CPF_MENU => $langCPFMenu, CPF_LINK =>$langLink);
        $data['yes_no'] = array(0 => $langNo, 1 => $langYes);
        $data['visibility'] = array(CPF_VIS_PROF => $langProfOnly, CPF_VIS_ALL => $langToAllUsers);
    }

    $view = 'admin.users.custom_profile_fields.createStep2';

} else { //show categories and fields list
    load_js('sortable');
    $head_content .= "<style>
                        .tile__name {
                            cursor: move;
                        }
                        .tile__list {
                            cursor: move;
                        }
                      </style>";
    $data['action_bar'] = action_bar(array(
        array('title' => $langCategoryAdd,
              'url' => "custom_profile_fields.php?add_cat",
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success')
        ));

    $data['field_types'] = array(CPF_TEXTBOX => $langCPFText, CPF_TEXTAREA => $langCPFTextarea, CPF_DATE => $langCPFDate, CPF_MENU => $langCPFMenu, CPF_LINK => $langLink);
    $data['yes_no'] = array(0 => $langNo, 1 => $langYes);
    $data['visibility'] = array(CPF_VIS_PROF => $langProfOnly, CPF_VIS_ALL => $langToAllUsers);

    $data['result'] = $result = Database::get()->queryArray("SELECT * FROM custom_profile_fields_category ORDER BY sortorder DESC");

    if ($result) {
        $data['form_data_array'] = array(); //array used to build the sortorder form

        foreach ($result as $res) {
            $data['form_data_array'][$res->id] = array();
            $q = Database::get()->queryArray("SELECT * FROM custom_profile_fields WHERE categoryid = ?d ORDER BY sortorder DESC", $res->id);
            if ($q) {
                foreach ($q as $f) {
                    $data['form_data_array'][$res->id] = $q;
                }
            }
        }
    }
  $view = 'admin.users.custom_profile_fields.index';
}

view ($view, $data);
