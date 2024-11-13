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

define('EPF_TEXTBOX', 1);
define('EPF_TEXTAREA', 2);
define('EPF_DATE', 3);
define('EPF_MENU', 4);
define('EPF_LINK', 5);

$require_admin = true;
$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'user_e_portfolio_fields';
require_once '../../include/baseTheme.php';

$toolName = $langAdmin;
$pageName = $langEPFAdmin;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_GET['add_cat'])) { //add a new category form
    load_js('validation.js');

    $pageName = $langCategoryAdd;
    $navigation[] = array('url' => 'eportfolio_fields.php', 'name' => $langEPFAdminSideMenuLink);

    $tool_content .= "
            <div class='row'>
                <div class='col-lg-6 col-12 mt-3'>
                    <div class='form-wrapper form-edit border-0 px-0'>
                        <form class='form-horizontal' role='form' name='catForm' action='$_SERVER[SCRIPT_NAME]' method='post'>
                        <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='form-group'>
                            <label for='catname' class='col-sm-12 control-label-notes'>$langName <span class='asterisk Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'><input id='catname' class='form-control' type='text' name='cat_name' placeholder='$langName...'></div>
                        </div>
                        <div class='row p-2'>
                            <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>".showSecondFactorChallenge()."<input class='btn submitAdminBtn' type='submit' name='submit_cat' value='$langAdd'></div>
                        </fieldset>". generate_csrf_token_form_field() ."</form>
                    </div>
                </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div></div>";
    $tool_content .='<script language="javaScript" type="text/javascript">
                    //<![CDATA[
                        var chkValidator  = new Validator("catForm");
                        chkValidator.addValidation("catname","req","' . $langCPFCategoryNameAlert . '");
                    //]]></script>';
} elseif (isset($_GET['del_cat'])) { //delete category
    $catid = intval(getDirectReference($_GET['del_cat']));
    //delete fields profile data
    Database::get()->query("DELETE eportfolio_fields_data FROM eportfolio_fields_data INNER JOIN eportfolio_fields
                            ON eportfolio_fields_data.field_id = eportfolio_fields.id INNER JOIN eportfolio_fields_category
                            ON eportfolio_fields.categoryid = eportfolio_fields_category.id 
                            WHERE eportfolio_fields_category.id = ?d", $catid);
    //delete fields
    Database::get()->query("DELETE eportfolio_fields FROM eportfolio_fields INNER JOIN eportfolio_fields_category
                            ON eportfolio_fields.categoryid = eportfolio_fields_category.id 
                            WHERE eportfolio_fields_category.id = ?d", $catid);
    //delete category
    Database::get()->query("DELETE FROM eportfolio_fields_category WHERE id = ?d", $catid);
    Session::flash('message',$langEPFCatDelSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/eportfolio_fields.php");
} elseif (isset($_GET['edit_cat'])) { //edit category form
    $catid = intval(getDirectReference($_GET['edit_cat']));
    $cat_name = Database::get()->querySingle("SELECT name FROM eportfolio_fields_category WHERE id = ?d", $catid)->name;

    load_js('validation.js');

    $pageName = $langCategoryMod;
    $navigation[] = array('url' => 'eportfolio_fields.php', 'name' => $langEPFAdminSideMenuLink);

    $tool_content .= "
            <div class='row'>
                <div class='col-lg-6 col-12 mt-3'>
                  <div class='form-wrapper form-edit border-0 px-0'>
                    <form class='form-horizontal' role='form' name='catForm' action='$_SERVER[SCRIPT_NAME]' method='post'>
                    <input type='hidden' name='cat_id' value='" . getIndirectReference($catid) . "'>
                    <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                        <div class='form-group'>
                        <label for='catname' class='col-sm-12 control-label-notes'>$langName <span class='asterisk Accent-200-cl'>(*)</span></label>
                              <div class='col-sm-12'><input id='catname' class='form-control' type='text' name='cat_name' value='$cat_name'></div>
                        </div>
                    <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>".showSecondFactorChallenge()."<input class='btn submitAdminBtn' type='submit' name='submit_cat' value='$langAdd'></div>
                    </fieldset>". generate_csrf_token_form_field() ."</form></div></div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                    </div>
                </div>";
            $tool_content .='<script language="javaScript" type="text/javascript">
                //<![CDATA[
                    var chkValidator  = new Validator("catForm");
                    chkValidator.addValidation("catname","req","' . $langCPFCategoryNameAlert . '");
            //]]></script>';
} elseif (isset($_GET['add_field'])) { //add new field form (first step)
    $catid = intval(getDirectReference($_GET['add_field']));
    $navigation[] = array('url' => 'eportfolio_fields.php', 'name' => $langEPFAdminSideMenuLink);
    $pageName = $langAddField;

    $field_types = array(EPF_TEXTBOX => $langCPFText, EPF_TEXTAREA => $langCPFTextarea, EPF_DATE => $langCPFDate, EPF_MENU => $langCPFMenu, EPF_LINK =>$langLink );

    $tool_content .= "
        <div class='row'>    
            <div class='col-lg-6 col-12 mt-3'>
              <div class='form-wrapper form-edit border-0 px-0'>
                <form class='form-horizontal' role='form' name='fieldForm' action='$_SERVER[SCRIPT_NAME]' method='post'>
                <fieldset><legend class='mb-0' aria-label='$langForm'></legend>
                <input type='hidden' name='catid' value='" . getIndirectReference($catid) . "'>
                <div class='form-group'>
                <label for='datatype' class='col-sm-12 control-label-notes'>$langCPFFieldDatatype <span class='asterisk Accent-200-cl'>(*)</span></label>
                <div class='col-sm-12'>".selection($field_types, 'datatype', 1, 'class="form-control" id="datatype"')."</div>
                </div>
                <div class='col-12 mt-5 d-flex justify-content-end align-items-center'><input class='btn submitAdminBtn' type='submit' name='add_field_proceed_step2' value='$langNext'></div>
                </fieldset>". generate_csrf_token_form_field() ."</form></div></div>
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                </div>
            </div>";

} elseif (isset($_POST['add_field_proceed_step2'])) { //add new field form 2nd step
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $catid = intval(getDirectReference($_POST['catid']));

    load_js('validation.js');
    $navigation[] = array('url' => 'eportfolio_fields.php', 'name' => $langEPFAdminSideMenuLink);
    $pageName = $langAddField;

    $yes_no = array(0 => $langNo, 1 => $langYes);

    $datatype = intval($_POST['datatype']);

    $tool_content .= "
    <div class='row'>
    
    <div class='col-lg-6 col-12 mt-3'>
                      <div class='form-wrapper form-edit border-0 px-0'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='fieldForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset><legend class='mb-0' aria-label='$langForm'></legend>";
    $tool_content .= "<input type='hidden' name='catid' value='" . getIndirectReference($catid) . "'>";
    $tool_content .= "<input type='hidden' name='datatype' value='$datatype'>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='name' class='col-sm-12 control-label-notes'>$langName <span class='asterisk Accent-200-cl'>(*)</span></label>
                      <div class='col-sm-12'><input id='name' class='form-control' type='text' name='field_name'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group mt-4'>";
    $tool_content .= "<label for='shortname' class='col-sm-12 control-label-notes'>$langCPFShortName <small>($langEPFUniqueShortname)</small> <span class='asterisk Accent-200-cl'>(*)</span></label>
                      <div class='col-sm-12'><input id='shortname' class='form-control' type='text' name='field_shortname'></div>";
    $tool_content .= "</div>";

    $tool_content .= "<div class='form-group mt-4'><label for='fielddescr' class='col-sm-12 control-label-notes'>$langDescription</label>
                      <div class='col-sm-12'>".rich_text_editor('fielddescr', 8, 20, '')."</div>";

    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group mt-4'>";
    $tool_content .= "<label for='required' class='col-sm-12 control-label-notes'>$langCPFFieldRequired</label>
                      <div class='col-sm-12'>".selection($yes_no, 'required', 0, 'class="form-control"')."</div>";
    $tool_content .= "</div>";
    if ($datatype == EPF_MENU) {
        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='options' class='col-sm-12 control-label-notes'>$langCPFMenuOptions <small>($langCPFMenuOptionsExplan)</small></label>
                          <div class='col-sm-12'><textarea name='options' rows='8' class='w-100'></textarea></div>";
        $tool_content .= "</div>";
    }
    $tool_content .= "<div class='col-12 mt-5 d-flex justify-content-end align-items-center'>".showSecondFactorChallenge()."<input class='btn submitAdminBtn' type='submit' name='submit_field' value='$langAdd'></div>";
    $tool_content .= "</fieldset>". generate_csrf_token_form_field() ."</form></div></div>
    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                    </div></div>";

    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("fieldForm");
            chkValidator.addValidation("field_name","req","' . $langEPFFieldNameAlert . '");
            chkValidator.addValidation("field_shortname","req","' . $langEPFFieldShortNameAlert . '");
        ';
    if ($datatype == EPF_MENU) {
        $tool_content .= 'chkValidator.addValidation("options","req","' . $langCPFMenuOptionsAlert . '");';
    }
    $tool_content .= '//]]></script>';

} elseif (isset($_POST['submit_field'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $name = $_POST['field_name'];
    $shortname = $_POST['field_shortname'];
    $description = $_POST['fielddescr'];
    $datatype = intval($_POST['datatype']);
    if (isset($_POST['required'])) {
        $required = intval($_POST['required']);
    } else {
        $required = 0;
    }
    if ($datatype == EPF_MENU && isset($_POST['options'])) {
        $data = explode(PHP_EOL, $_POST['options']);
        $data = serialize($data);
    } else {
        $data = '';
    }

    if (isset($_POST['field_id'])) { //save edited field
        $fieldid = intval(getDirectReference($_POST['field_id']));

        //check for unique shortname
        $is_unique = true;
        $old_shortname = Database::get()->querySingle("SELECT shortname FROM eportfolio_fields WHERE id = ?d", $fieldid)->shortname;
        if ($shortname != $old_shortname) {
            $count = Database::get()->querySingle("SELECT COUNT(*) AS c FROM eportfolio_fields WHERE shortname = ?s", $shortname)->c;
            if ($count != 0) {
                $is_unique = false;
            }
        }

        if ($is_unique) {
            Database::get()->query("UPDATE eportfolio_fields SET name = ?s,
                                    shortname = ?s,
                                    description = ?s,
                                    datatype = ?d,
                                    required = ?d,
                                    data = ?s
                                    WHERE id = ?d", $name, $shortname, $description, $datatype, $required, $data, $fieldid);
            Session::flash('message',$langEPFFieldEditSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/admin/eportfolio_fields.php");
        } else {
            Session::flash('message',$langEPFEditUniqueShortnameError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/admin/eportfolio_fields.php");
        }
    } else { //save new field

        //check for unique shortname
        $count = Database::get()->querySingle("SELECT COUNT(*) AS c FROM eportfolio_fields WHERE shortname = ?s", $shortname)->c;
        if ($count == 0) { //shortname is unique, proceed

            $catid = intval(getDirectReference($_POST['catid']));

            $result = Database::get()->querySingle("SELECT MIN(sortorder) AS m FROM eportfolio_fields WHERE categoryid = ?d", $catid);
            if (!is_null($result->m)) {
                $sortorder = $result->m - 1;
            } else {
                $sortorder = 0;
            }

            Database::get()->query("INSERT INTO eportfolio_fields (shortname, name, description, datatype, categoryid, sortorder, required, data) 
                                    VALUES (?s, ?s, ?s, ?d, ?d, ?d, ?d, ?s)", $shortname, $name, $description, $datatype, $catid, $sortorder, $required, $data);
            Session::flash('message',$langEPFFieldAddSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/admin/eportfolio_fields.php");
        } else { //shortname is not unique, abort
            Session::flash('message',$langCPFCreateUniqueShortnameError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/admin/eportfolio_fields.php");
        }
    }
} elseif (isset($_GET['del_field'])) { //delete fields
    $fieldid = intval(getDirectReference($_GET['del_field']));
    //delete fields profile data
    Database::get()->query("DELETE eportfolio_fields_data FROM eportfolio_fields_data INNER JOIN eportfolio_fields
                            ON eportfolio_fields_data.field_id = eportfolio_fields.id
                            WHERE eportfolio_fields.id = ?d", $fieldid);
    //delete field
    Database::get()->query("DELETE FROM eportfolio_fields WHERE id = ?d", $fieldid);
    Session::flash('message',$langEPFFieldDelSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/eportfolio_fields.php");
} elseif (isset($_GET['edit_field'])) { //save edited field
    $pageName = $langCPFFieldEdit;
    $navigation[] = array('url' => 'eportfolio_fields.php', 'name' => $langEPFAdminSideMenuLink);

    $fieldid = intval(getDirectReference($_GET['edit_field']));
    $result = Database::get()->querySingle("SELECT * FROM eportfolio_fields WHERE id = ?d", $fieldid);

    if ($result) {
        $name = q($result->name);
        $shortname = q($result->shortname);
        $description = standard_text_escape($result->description);
        $datatype = $result->datatype;
        $required = $result->required;
        $data = $result->data;

        if ($datatype == EPF_MENU) {
            $data = unserialize($data);
            $textarea_val = '';
            foreach ($data as $line) {
                $textarea_val .= $line."\n";
            }
            $textarea_val = substr($textarea_val, 0, strlen($textarea_val)-1);
        }

        load_js('validation.js');

        $field_types = array(EPF_TEXTBOX => $langCPFText, EPF_TEXTAREA => $langCPFTextarea, EPF_DATE => $langCPFDate, EPF_MENU => $langCPFMenu, EPF_LINK => $langLink);
        $yes_no = array(0 => $langNo, 1 => $langYes);

        $tool_content .= "
        <div class='row'>
        
        <div class='col-lg-6 col-12 mt-3'>
                          <div class='form-wrapper form-edit border-0 px-0'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='fieldForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<fieldset><legend class='mb-0' aria-label='$langForm'></legend>";
        $tool_content .= "<input type='hidden' name='field_id' value='" . getIndirectReference($fieldid) . "'>";
        $tool_content .= "<input type='hidden' name='datatype' value='$datatype'>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='name' class='col-sm-12 control-label-notes'>$langName <span class='asterisk Accent-200-cl'>(*)</span></label>
                          <div class='col-sm-12'><input id='name' class='form-control' type='text' name='field_name' value='$name'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='shortname' class='col-sm-12 control-label-notes'>$langCPFShortName <small>($langEPFUniqueShortname)</small> <span class='asterisk Accent-200-cl'>(*)</span></label>
                          <div class='col-sm-12'><input id='shortname' class='form-control' type='text' name='field_shortname' value='$shortname'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group mt-4'><label for='fielddescr' class='col-sm-12 control-label-notes'>$langDescription</label>
                          <div class='col-sm-12'>".rich_text_editor('fielddescr', 8, 20, $description)."</div>";

        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='datatype' class='col-sm-12 control-label-notes'>$langCPFFieldDatatype</label>
                          <div class='col-sm-12'>".selection($field_types, 'datatype_disabled', $datatype, 'class="form-control" id="datatype" disabled')."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group mt-4'>";
        $tool_content .= "<label for='required' class='col-sm-12 control-label-notes'>$langCPFFieldRequired</label>
                          <div class='col-sm-12'>".selection($yes_no, 'required', $required, 'class="form-control" id="required"')."</div>";
        $tool_content .= "</div>";
        if ($datatype == EPF_MENU) {
            $tool_content .= "<div class='form-group mt-4'>";
            $tool_content .= "<label for='options' class='col-sm-12 control-label-notes'>$langCPFMenuOptions <small>($langCPFMenuOptionsExplan - $langCPFMenuOptionsChangeExplan)</small></label>
                              <div class='col-sm-12'><textarea name='options' rows='8' class='w-100' id='options'>$textarea_val</textarea></div>";
            $tool_content .= "</div>";
        }
        $tool_content .= "<div class='col-12 mt-5 d-flex justify-content-end align-items-center'><input class='btn submitAdminBtn' type='submit' name='submit_field' value='$langSave'></div>";
        $tool_content .= "</fieldset>". generate_csrf_token_form_field() ."</form></div></div>
        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
                    </div></div>";

        $tool_content .='<script language="javaScript" type="text/javascript">
                        //<![CDATA[
                            var chkValidator  = new Validator("fieldForm");
                            chkValidator.addValidation("field_name","req","' . $langEPFFieldNameAlert . '");
                            chkValidator.addValidation("field_shortname","req","' . $langEPFFieldShortNameAlert . '");
                        ';
        if ($datatype == EPF_MENU) {
            $tool_content .= 'chkValidator.addValidation("options","req","' . $langCPFMenuOptionsAlert . '");';
        }
        $tool_content .= '//]]></script>';
    } else {

    }
} elseif (isset($_POST['submit_cat'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    if (isset($_POST['cat_id'])) { //save edited category
        $catid = intval(getDirectReference($_POST['cat_id']));
        Database::get()->query("UPDATE eportfolio_fields_category SET name = ?s WHERE id = ?d", $_POST['cat_name'], $catid);
        Session::flash('message',$langEPFCatModSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/admin/eportfolio_fields.php");
    } else { //save new category
        //add category as last in sort order
        $result = Database::get()->querySingle("SELECT MIN(sortorder) AS m FROM eportfolio_fields_category");
        if (!is_null($result->m)) {
            $sortorder = $result->m - 1;
        } else {
            $sortorder = 0;
        }
        Database::get()->query("INSERT INTO eportfolio_fields_category (name, sortorder) VALUES (?s, ?d)", $_POST['cat_name'], $sortorder);
        Session::flash('message',$langEPFCatAddedSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/admin/eportfolio_fields.php");
    }
} elseif (isset($_POST['cats'])) { //save sort order
    $cats_counter = count($_POST['cats']);
    $fields_counter = count($_POST['fields']);

    foreach ($_POST['cats'] as $cat) {
        $cat_id = getDirectReference(substr($cat, 4));
        Database::get()->query("UPDATE eportfolio_fields_category SET sortorder = ?d WHERE id = ?d", $cats_counter, $cat_id);
        $cats_counter--;
    }

    foreach ($_POST['fields'] as $field) {
        $field_id = getDirectReference(substr($field, 6));
        Database::get()->query("UPDATE eportfolio_fields SET sortorder = ?d, categoryid=?d WHERE id = ?d", $fields_counter, getDirectReference(substr($_POST['fields_cat'][$field], 4)), $field_id);
        $fields_counter--;
    }

    exit;
} else { //show categories and fields list
    load_js('sortable');

    $action_bar = action_bar(array(
        array('title' => $langCategoryAdd,
              'url' => "eportfolio_fields.php?add_cat",
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        ));

    $tool_content .= $action_bar;

    $result = Database::get()->queryArray("SELECT * FROM eportfolio_fields_category ORDER BY sortorder DESC");
    if (count($result) == 0) {
        $tool_content .= "
        <div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langEPFNoCats</span></div></div>";
    } else {
        $form_data_array = array(); //array used to build the sortorder form

        $head_content .= "
            <style>
                .tile__name { cursor: move; }
                .tile__list { cursor: move; }
            </style>";
        $tool_content .= "<div id='multi'>"; //container for sorting
        foreach ($result as $res) {
            $form_data_array[$res->id] = array();

            $tool_content .= "<div id='cat_".getIndirectReference($res->id)."' class='table-responsive tile' style='margin-bottom:30px;'><table class='table-default'>";
            $tool_content .= "<caption class='tile__name ps-1 pe-1'><strong>$langCategory :</strong> " . q($res->name) . "<div class='float-end'>";

            $dyntools = array(
                array(
                        'title' => $langEPFNewField,
                        'url' => "$_SERVER[SCRIPT_NAME]?add_field=" . getIndirectReference($res->id),
                        'icon' => 'fa-plus-circle',
                        'level' => 'primary'
                ),
                array('title' => $langModify,
                        'url' => "$_SERVER[SCRIPT_NAME]?edit_cat=" . getIndirectReference($res->id),
                        'icon' => 'fa-edit',
                        'level' => 'primary'
                ),
                array('title' => $langDelete,
                        'url' => "$_SERVER[SCRIPT_NAME]?del_cat=" . getIndirectReference($res->id),
                        'icon' => 'fa-xmark',
                        'class' => 'delete',
                        'confirm' => $langEPFConfirmCatDelete
                )
            );

            $tool_content .= action_button($dyntools)."</div></caption>";

            $tool_content .= "<thead><tr class='list-header'>
                <td class='bg-header-table TextBold'>$langName</td>
                <td class='bg-header-table TextBold'>$langCPFShortName</td>
                <td class='bg-header-table TextBold'>$langDescription</td>
                <td class='bg-header-table TextBold'>$langCPFFieldDatatype</td>
                <td class='bg-header-table TextBold'>$langCPFFieldRequired</td>
                <td class='bg-header-table TextBold' aria-label='$langSettingSelect'>" . icon('fa-gears') . "</td>
		        </tr></thead>";

            $q = Database::get()->queryArray("SELECT * FROM eportfolio_fields WHERE categoryid = ?d ORDER BY sortorder DESC", $res->id);
            if (count($q) == 0) {
                $tool_content .= "<tbody class='tile__list'>";
                $tool_content .= "<tr class='ignore-item'><td colspan='9'><span class='not_visible'>".$langCPFNoFieldinCat."</td></tr>";
                $tool_content .= "</tbody>";
            } else {

                $field_types = array(EPF_TEXTBOX => $langCPFText, EPF_TEXTAREA => $langCPFTextarea, EPF_DATE => $langCPFDate, EPF_MENU => $langCPFMenu, EPF_LINK =>$langLink);
                $yes_no = array(0 => $langNo, 1 => $langYes);

                $tool_content .= "<tbody class='tile__list'>";
                foreach ($q as $f) {
                    $form_data_array[getIndirectReference($res->id)][] = getIndirectReference($f->id);
                    $field_dyntools = array(
                        array('title' => $langModify,
                              'url' => "$_SERVER[SCRIPT_NAME]?edit_field=" . getIndirectReference($f->id),
                              'icon' => 'fa-edit',
                        ),
                        array('title' => $langDelete,
                              'url' => "$_SERVER[SCRIPT_NAME]?del_field=" . getIndirectReference($f->id),
                              'icon' => 'fa-xmark',
                              'class' => 'delete',
                              'confirm' => $langEPFConfirmFieldDelete
                        )
                    );

                    $tool_content .= "<tr id='field_" . getIndirectReference($f->id) . "'>";
                    $tool_content .= "<td>".q($f->name)."</td>";
                    $tool_content .= "<td>".q($f->shortname)."</td>";
                    $tool_content .= "<td>".standard_text_escape($f->description)."</td>";
                    $tool_content .= "<td>".$field_types[$f->datatype]."</td>";
                    $tool_content .= "<td>".$yes_no[$f->required]."</td>";
                    $tool_content .= "<td>".action_button($field_dyntools)."</td>";
                    $tool_content .= "</tr>";
                }
                $tool_content .= "</tbody>";
            }

            $tool_content .= "</table></div>";
        }
        $tool_content .= "</div>";
        $tool_content .= "<form name='sortOrderForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= generate_csrf_token_form_field() ."</form>";
        $tool_content .= "<script src='custom_profile_fields.js'></script>";
    }
}

draw($tool_content, null, null, $head_content);
