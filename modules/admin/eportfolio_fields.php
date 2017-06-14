<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

define('EPF_TEXTBOX', 1);
define('EPF_TEXTAREA', 2);
define('EPF_DATE', 3);
define('EPF_MENU', 4);
define('EPF_LINK', 5);

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langEPFAdmin;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_GET['add_cat'])) { //add a new category form
    load_js('validation.js');
    
    $pageName = $langCategoryAdd;
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "eportfolio_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='catForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='catname' class='col-sm-2 control-label'>$langName</label>
                      <div class='col-sm-10'><input id='catname' type='text' name='cat_name'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'>".showSecondFactorChallenge()."<input class='btn btn-primary' type='submit' name='submit_cat' value='$langAdd'></div>";
    $tool_content .= "</fieldset>". generate_csrf_token_form_field() ."</form></div>";
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
    Session::Messages($langEPFCatDelSuccess, 'alert-success');
    redirect_to_home_page("modules/admin/eportfolio_fields.php");
} elseif (isset($_GET['edit_cat'])) { //edit category form
    $catid = intval(getDirectReference($_GET['edit_cat']));
    $cat_name = Database::get()->querySingle("SELECT name FROM eportfolio_fields_category WHERE id = ?d", $catid)->name;
    
    load_js('validation.js');
    
    $pageName = $langCategoryMod;
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "eportfolio_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='catForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<input type='hidden' name='cat_id' value='" . getIndirectReference($catid) . "'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='catname' class='col-sm-2 control-label'>$langName</label>
                      <div class='col-sm-10'><input id='catname' type='text' name='cat_name' value='$cat_name'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'>".showSecondFactorChallenge()."<input class='btn btn-primary' type='submit' name='submit_cat' value='$langAdd'></div>";
    $tool_content .= "</fieldset>". generate_csrf_token_form_field() ."</form></div>";
    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("catForm");
            chkValidator.addValidation("catname","req","' . $langCPFCategoryNameAlert . '");
    //]]></script>';
} elseif (isset($_GET['add_field'])) { //add new field form (first step)
    $catid = intval(getDirectReference($_GET['add_field']));
    
    $pageName = $langAddField;
    
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "eportfolio_fields.php",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    
    $field_types = array(EPF_TEXTBOX => $langCPFText, EPF_TEXTAREA => $langCPFTextarea, EPF_DATE => $langCPFDate, EPF_MENU => $langCPFMenu, EPF_LINK =>$langCPFLink );
    
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='fieldForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<input type='hidden' name='catid' value='" . getIndirectReference($catid) . "'>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='datatype' class='col-sm-2 control-label'>$langCPFFieldDatatype</label>
                      <div class='col-sm-10'>".selection($field_types, 'datatype', 1, 'class="form-control"')."</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='add_field_proceed_step2' value='$langNext'></div>";
    $tool_content .= "</fieldset>". generate_csrf_token_form_field() ."</form></div>";
    
} elseif (isset($_POST['add_field_proceed_step2'])) { //add new field form 2nd step
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $catid = intval(getDirectReference($_POST['catid']));
    
    load_js('validation.js');
    
    $pageName = $langAddField;
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "eportfolio_fields.php?add_field=" . getIndirectReference($catid),
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    
    $yes_no = array(0 => $langNo, 1 => $langYes);
        
    $datatype = intval($_POST['datatype']);
    
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='fieldForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<input type='hidden' name='catid' value='" . getIndirectReference($catid) . "'>";
    $tool_content .= "<input type='hidden' name='datatype' value='$datatype'>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='name' class='col-sm-2 control-label'>$langName</label>
                      <div class='col-sm-10'><input id='name' type='text' name='field_name'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='shortname' class='col-sm-2 control-label'>$langCPFShortName <small>($langEPFUniqueShortname)</small></label>
                      <div class='col-sm-10'><input id='shortname' type='text' name='field_shortname'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'><label for='fielddescr' class='col-sm-2 control-label'>$langCPFFieldDescr</label>
                      <div class='col-sm-10'>".rich_text_editor('fielddescr', 8, 20, '')."</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='required' class='col-sm-2 control-label'>$langCPFFieldRequired</label>
                      <div class='col-sm-10'>".selection($yes_no, 'required', 0, 'class="form-control"')."</div>";
    $tool_content .= "</div>";
    if ($datatype == EPF_MENU) {
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='options' class='col-sm-2 control-label'>$langCPFMenuOptions <small>($langCPFMenuOptionsExplan)</small></label>
                          <div class='col-sm-10'><textarea name='options' rows='8' cols='20'></textarea></div>";
        $tool_content .= "</div>";
    }
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'>".showSecondFactorChallenge()."<input class='btn btn-primary' type='submit' name='submit_field' value='$langAdd'></div>";
    $tool_content .= "</fieldset>". generate_csrf_token_form_field() ."</form></div>";
    
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
            Session::Messages($langEPFFieldEditSuccess, 'alert-success');
            redirect_to_home_page("modules/admin/eportfolio_fields.php");
        } else {
            Session::Messages($langEPFEditUniqueShortnameError, 'alert-danger');
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
            Session::Messages($langEPFFieldAddSuccess, 'alert-success');
            redirect_to_home_page("modules/admin/eportfolio_fields.php");
        } else { //shortname is not unique, abort
            Session::Messages($langCPFCreateUniqueShortnameError, 'alert-danger');
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
    Session::Messages($langEPFFieldDelSuccess, 'alert-success');
    redirect_to_home_page("modules/admin/eportfolio_fields.php");
} elseif (isset($_GET['edit_field'])) { //save edited field
    $pageName = $langCPFFieldEdit;
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "eportfolio_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    
    $fieldid = intval(getDirectReference($_GET['edit_field']));
    $result = Database::get()->querySingle("SELECT * FROM eportfolio_fields WHERE id = ?d", $fieldid);
    if (count($result) != 0) {
        
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
        
        $field_types = array(EPF_TEXTBOX => $langCPFText, EPF_TEXTAREA => $langCPFTextarea, EPF_DATE => $langCPFDate, EPF_MENU => $langCPFMenu, EPF_LINK =>$langCPFLink);        
        $yes_no = array(0 => $langNo, 1 => $langYes);
        
        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='fieldForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<fieldset>";
        $tool_content .= "<input type='hidden' name='field_id' value='" . getIndirectReference($fieldid) . "'>";
        $tool_content .= "<input type='hidden' name='datatype' value='$datatype'>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='name' class='col-sm-2 control-label'>$langName</label>
                          <div class='col-sm-10'><input id='name' type='text' name='field_name' value='$name'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='shortname' class='col-sm-2 control-label'>$langCPFShortName <small>($langEPFUniqueShortname)</small></label>
                          <div class='col-sm-10'><input id='shortname' type='text' name='field_shortname' value='$shortname'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'><label for='fielddescr' class='col-sm-2 control-label'>$langCPFFieldDescr</label>
                          <div class='col-sm-10'>".rich_text_editor('fielddescr', 8, 20, $description)."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='datatype' class='col-sm-2 control-label'>$langCPFFieldDatatype</label>
                          <div class='col-sm-10'>".selection($field_types, 'datatype_disabled', $datatype, 'class="form-control" disabled')."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='required' class='col-sm-2 control-label'>$langCPFFieldRequired</label>
                          <div class='col-sm-10'>".selection($yes_no, 'required', $required, 'class="form-control"')."</div>";
        $tool_content .= "</div>";
        if ($datatype == EPF_MENU) {
            $tool_content .= "<div class='form-group'>";
            $tool_content .= "<label for='options' class='col-sm-2 control-label'>$langCPFMenuOptions <small>($langCPFMenuOptionsExplan - $langCPFMenuOptionsChangeExplan)</small></label>
                              <div class='col-sm-10'><textarea name='options' rows='8' cols='20'>$textarea_val</textarea></div>";
            $tool_content .= "</div>";
        }
        $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit_field' value='$langSave'></div>";
        $tool_content .= "</fieldset>". generate_csrf_token_form_field() ."</form></div>";
        
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
        Session::Messages($langEPFCatModSuccess, 'alert-success');
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
        Session::Messages($langEPFCatAddedSuccess, 'alert-success');
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
    
    $tool_content .= action_bar(array(
        array('title' => $langCategoryAdd,
              'url' => "eportfolio_fields.php?add_cat",
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        array('title' => $langBack,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));

    $result = Database::get()->queryArray("SELECT * FROM eportfolio_fields_category ORDER BY sortorder DESC");
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langEPFNoCats</div>";
    } else {
        $form_data_array = array(); //array used to build the sortorder form
        
        $tool_content .= "<div id='multi'>"; //container for sorting
        foreach ($result as $res) {
            $form_data_array[$res->id] = array();
            
            $head_content .= "<style>
                                .tile__name {
                                    cursor: move;
                                }
                                .tile__list {
                                    cursor: move;
                                }
                              </style>";
            $tool_content .= "<div id='cat_".getIndirectReference($res->id)."' class='table-responsive tile' style='margin-bottom:30px;'><table class='table-default'>";
            $tool_content .= "<caption class='tile__name'><strong>$langCategory :</strong> $res->name<div class='pull-right'>";
            
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
                        'icon' => 'fa-times',
                        'class' => 'delete',
                        'confirm' => $langEPFConfirmCatDelete
                )
            );
            
            $tool_content .= action_button($dyntools)."</div></caption>";
            
            $tool_content .= "<thead><tr class='list-header'>
                <td>$langName</td>
                <td>$langCPFShortName</td>
                <td>$langDescription</td>
                <td>$langCPFFieldDatatype</td>
                <td>$langCPFFieldRequired</td>
                <td>" . icon('fa-gears') . "</td>
		        </tr></thead>";
            
            $q = Database::get()->queryArray("SELECT * FROM eportfolio_fields WHERE categoryid = ?d ORDER BY sortorder DESC", $res->id);
            if (count($q) == 0) {
                $tool_content .= "<tbody class='tile__list'>";
                $tool_content .= "<tr class='ignore-item'><td colspan='9' class='text-center'><span class='not_visible'>".$langCPFNoFieldinCat."</td></tr>";
                $tool_content .= "</tbody>";
            } else {
                
                $field_types = array(EPF_TEXTBOX => $langCPFText, EPF_TEXTAREA => $langCPFTextarea, EPF_DATE => $langCPFDate, EPF_MENU => $langCPFMenu, EPF_LINK =>$langCPFLink);                
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
                              'icon' => 'fa-times',
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

draw($tool_content, 3, null, $head_content);
