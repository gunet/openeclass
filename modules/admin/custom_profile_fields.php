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

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langCPFAdmin;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_GET['add_cat'])) { //add a new category form
    load_js('validation.js');
    
    $pageName = $langCategoryAdd;
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "custom_profile_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='catForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='catname' class='col-sm-2 control-label'>$langName</label>
                      <div class='col-sm-10'><input id='catname' type='text' name='cat_name'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit_cat' value='$langAdd'></div>";
    $tool_content .= "</fieldset></form></div>";
    $tool_content .='<script language="javaScript" type="text/javascript">
                    //<![CDATA[
                        var chkValidator  = new Validator("catForm");
                        chkValidator.addValidation("catname","req","' . $langCPFCategoryNameAlert . '");
                    //]]></script>';
} elseif (isset($_GET['del_cat'])) { //delete category
    $catid = intval($_GET['del_cat']);
    //delete fields profile data
    Database::get()->query("DELETE custom_profile_fields_data FROM custom_profile_fields_data INNER JOIN custom_profile_fields
                            ON custom_profile_fields_data.field_id = custom_profile_fields.id INNER JOIN custom_profile_fields_category
                            ON custom_profile_fields.categoryid = custom_profile_fields_category.id 
                            WHERE custom_profile_fields_category.id = ?d", $catid);
    //delete fields
    Database::get()->query("DELETE custom_profile_fields FROM custom_profile_fields INNER JOIN custom_profile_fields_category
                            ON custom_profile_fields.categoryid = custom_profile_fields_category.id 
                            WHERE custom_profile_fields_category.id = ?d", $catid);
    //delete category
    Database::get()->query("DELETE FROM custom_profile_fields_category WHERE id = ?d", $catid);
    Session::Messages($langCPFCatDelSuccess, 'alert-success');
    redirect_to_home_page("modules/admin/custom_profile_fields.php");
} elseif (isset($_GET['edit_cat'])) { //edit category form
    $catid = intval($_GET['edit_cat']);
    $cat_name = Database::get()->querySingle("SELECT name FROM custom_profile_fields_category WHERE id = ?d", $catid)->name;
    
    load_js('validation.js');
    
    $pageName = $langCategoryMod;
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "custom_profile_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='catForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<input type='hidden' name='cat_id' value='$catid'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='catname' class='col-sm-2 control-label'>$langName</label>
                      <div class='col-sm-10'><input id='catname' type='text' name='cat_name' value='$cat_name'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit_cat' value='$langAdd'></div>";
    $tool_content .= "</fieldset></form></div>";
    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("catForm");
            chkValidator.addValidation("catname","req","' . $langCPFCategoryNameAlert . '");
    //]]></script>';
} elseif (isset($_GET['add_field'])) { //add new field form
    $catid = intval($_GET['add_field']);
    
    load_js('validation.js');
    
    $pageName = $langAddField;
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "custom_profile_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    
    $field_types = array(1 => $langCPFText, 2 => $langCPFTextarea, 3 => $langCPFDate, 4 => $langCPFMenu, 5 =>$langCPFLink );
    $yes_no = array(0 => $m['no'], 1 => $m['yes']);
    $visibility = array(1 => $langProfOnly, 10 => $langToAllUsers);
    $user_type = array(1 => $langsTeachers, 5 => $langStudents, 10 => $langAll);
    
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='fieldForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<input type='hidden' name='catid' value='$catid'>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='name' class='col-sm-2 control-label'>$langName</label>
                      <div class='col-sm-10'><input id='name' type='text' name='field_name'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='shortname' class='col-sm-2 control-label'>$langCPFShortName</label>
                      <div class='col-sm-10'><input id='shortname' type='text' name='field_shortname'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'><label for='fielddescr' class='col-sm-2 control-label'>$langCPFFieldDescr</label>
                      <div class='col-sm-10'>".rich_text_editor('fielddescr', 8, 20, '')."</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='datatype' class='col-sm-2 control-label'>$langCPFFieldDatatype</label>
                      <div class='col-sm-10'>".selection($field_types, 'datatype', 1, 'class="form-control"')."</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='required' class='col-sm-2 control-label'>$langCPFFieldRequired</label>
                      <div class='col-sm-10'>".selection($yes_no, 'required', 0, 'class="form-control"')."</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='registration' class='col-sm-2 control-label'>$langCPFFieldRegistration</label>
                      <div class='col-sm-10'>".selection($yes_no, 'registration', 0, 'class="form-control"')."</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='user_type' class='col-sm-2 control-label'>$langCPFFieldUserType</label>
                      <div class='col-sm-10'>".selection($user_type, 'user_type', 10, 'class="form-control"')."</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='visibility' class='col-sm-2 control-label'>$langCPFFieldVisibility</label>
                      <div class='col-sm-10'>".selection($visibility, 'visibility', 10, 'class="form-control"')."</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit_field' value='$langAdd'></div>";
    $tool_content .= "</fieldset></form></div>";
    
    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("fieldForm");
            chkValidator.addValidation("field_name","req","' . $langCPFFieldNameAlert . '");
            chkValidator.addValidation("field_shortname","req","' . $langCPFFieldShortNameAlert . '");
    //]]></script>';
                
} elseif (isset($_POST['submit_field'])) {
    
    $name = $_POST['field_name'];
    $shortname = $_POST['field_shortname'];
    $description = $_POST['fielddescr'];
    $datatype = intval($_POST['datatype']);
    $required = intval($_POST['required']);
    $registration = intval($_POST['registration']);
    $user_type = intval($_POST['user_type']);
    $visibility = intval($_POST['visibility']);
    
    if (isset($_POST['field_id'])) { //save edited field
        $fieldid = intval($_POST['field_id']);
        
        Database::get()->query("UPDATE custom_profile_fields SET name = ?s,
                                shortname = ?s,
                                description = ?s,
                                datatype = ?d,
                                required = ?d,
                                visibility = ?d,
                                user_type = ?d,
                                registration = ?d
                                WHERE id = ?d", $name, $shortname, $description, $datatype, $required, $visibility, $user_type, $registration, $fieldid);
        Session::Messages($langCPFFieldEditSuccess, 'alert-success');
        redirect_to_home_page("modules/admin/custom_profile_fields.php");
    } else { //save new field
        $catid = intval($_POST['catid']);
        
        $result = Database::get()->querySingle("SELECT MIN(sortorder) AS m FROM custom_profile_fields WHERE categoryid = ?d", $catid);
        if (!is_null($result->m)) {
            $sortorder = $result->m - 1;
        } else {
            $sortorder = 0;
        }
        
        Database::get()->query("INSERT INTO custom_profile_fields (shortname, name, description, datatype, categoryid, sortorder, required, visibility, user_type, registration) 
                                VALUES (?s, ?s, ?s, ?d, ?d, ?d, ?d, ?d, ?d, ?d)", $shortname, $name, $description, $datatype, $catid, $sortorder, $required, $visibility, $user_type, $registration);
        Session::Messages($langCPFFieldAddSuccess, 'alert-success');
        redirect_to_home_page("modules/admin/custom_profile_fields.php");
    }
} elseif (isset($_GET['del_field'])) { //delete fields
    $fieldid = intval($_GET['del_field']);
    //delete fields profile data
    Database::get()->query("DELETE custom_profile_fields_data FROM custom_profile_fields_data INNER JOIN custom_profile_fields
                            ON custom_profile_fields_data.field_id = custom_profile_fields.id
                            WHERE custom_profile_fields.id = ?d", $fieldid);
    //delete field
    Database::get()->query("DELETE FROM custom_profile_fields WHERE id = ?d", $fieldid);
    Session::Messages($langCPFFieldDelSuccess, 'alert-success');
    redirect_to_home_page("modules/admin/custom_profile_fields.php");
} elseif (isset($_GET['edit_field'])) { //save edited field
    $pageName = $langCPFFieldEdit;
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "custom_profile_fields.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    
    $fieldid = intval($_GET['edit_field']);
    $result = Database::get()->querySingle("SELECT * FROM custom_profile_fields WHERE id = ?d", $fieldid);
    if (count($result) != 0) {
        
        $name = q($result->name);
        $shortname = q($result->shortname);
        $description = standard_text_escape($result->description);
        $datatype = $result->datatype;
        $required = $result->required;
        $vis = $result->visibility;
        $utype = $result->user_type;
        $registration = $result->registration;
        
        load_js('validation.js');
        
        $field_types = array(1 => $langCPFText, 2 => $langCPFTextarea, 3 => $langCPFDate, 4 => $langCPFMenu, 5 =>$langCPFLink );
        $yes_no = array(0 => $m['no'], 1 => $m['yes']);
        $visibility = array(1 => $langProfOnly, 10 => $langToAllUsers);
        $user_type = array(1 => $langsTeachers, 5 => $langStudents, 10 => $langAll);
        
        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='fieldForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<fieldset>";
        $tool_content .= "<input type='hidden' name='field_id' value='$fieldid'>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='name' class='col-sm-2 control-label'>$langName</label>
                          <div class='col-sm-10'><input id='name' type='text' name='field_name' value='$name'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='shortname' class='col-sm-2 control-label'>$langCPFShortName</label>
                          <div class='col-sm-10'><input id='shortname' type='text' name='field_shortname' value='$shortname'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'><label for='fielddescr' class='col-sm-2 control-label'>$langCPFFieldDescr</label>
                          <div class='col-sm-10'>".rich_text_editor('fielddescr', 8, 20, $description)."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='datatype' class='col-sm-2 control-label'>$langCPFFieldDatatype</label>
                          <div class='col-sm-10'>".selection($field_types, 'datatype', $datatype, 'class="form-control"')."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='required' class='col-sm-2 control-label'>$langCPFFieldRequired</label>
                          <div class='col-sm-10'>".selection($yes_no, 'required', $required, 'class="form-control"')."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='registration' class='col-sm-2 control-label'>$langCPFFieldRegistration</label>
                          <div class='col-sm-10'>".selection($yes_no, 'registration', $registration, 'class="form-control"')."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='user_type' class='col-sm-2 control-label'>$langCPFFieldUserType</label>
                          <div class='col-sm-10'>".selection($user_type, 'user_type', $utype, 'class="form-control"')."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='visibility' class='col-sm-2 control-label'>$langCPFFieldVisibility</label>
                          <div class='col-sm-10'>".selection($visibility, 'visibility', $vis, 'class="form-control"')."</div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit_field' value='$langSave'></div>";
        $tool_content .= "</fieldset></form></div>";
        
        $tool_content .='<script language="javaScript" type="text/javascript">
                        //<![CDATA[
                            var chkValidator  = new Validator("fieldForm");
                            chkValidator.addValidation("field_name","req","' . $langCPFFieldNameAlert . '");
                            chkValidator.addValidation("field_shortname","req","' . $langCPFFieldShortNameAlert . '");
                        //]]></script>';
    } else {
        
    }
} elseif (isset($_POST['submit_cat'])) {
    if (isset($_POST['cat_id'])) { //save edited category
        $catid = intval($_POST['cat_id']);
        Database::get()->query("UPDATE custom_profile_fields_category SET name = ?s WHERE id = ?d", $_POST['cat_name'], $catid);
        Session::Messages($langCPFCatModSuccess, 'alert-success');
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
        Session::Messages($langCPFCatAddedSuccess, 'alert-success');
        redirect_to_home_page("modules/admin/custom_profile_fields.php");
    }
} else { //show categories and fields list
    load_js('sortable');
    
    $tool_content .= action_bar(array(
        array('title' => $langCategoryAdd,
              'url' => "custom_profile_fields.php?add_cat",
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        array('title' => $langBack,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));

    $result = Database::get()->queryArray("SELECT * FROM custom_profile_fields_category");
    if (count($result) == 0) {
        $tool_content .= "<div class='alert alert-warning'>$langCPFNoCats</div>";
    } else {
        $tool_content .= "<div>"; //container for sorting
        foreach ($result as $res) {
            $tool_content .= "<div class='table-responsive' style='margin-bottom:30px;'><table class='table-default'>";
            $tool_content .= "<caption><strong>$langCategory :</strong> $res->name<div class='pull-right'>";
            
            $dyntools = array(
                array(
                        'title' => $langCPFNewField,
                        'url' => "$_SERVER[SCRIPT_NAME]?add_field=$res->id",
                        'icon' => 'fa-plus-circle',
                        'level' => 'primary'
                ),
                array('title' => $langModify,
                        'url' => "$_SERVER[SCRIPT_NAME]?edit_cat=$res->id",
                        'icon' => 'fa-edit',
                        'level' => 'primary'
                ),
                array('title' => $langDelete,
                        'url' => "$_SERVER[SCRIPT_NAME]?del_cat=$res->id",
                        'icon' => 'fa-times',
                        'class' => 'delete',
                        'confirm' => $langCPFConfirmCatDelete
                )
            );
            
            $tool_content .= action_button($dyntools)."</div></caption>";
            
            $tool_content .= "<tr class='list-header'>
                <td>$langName</td>
                <td>$langCPFShortName</td>
                <td>$langCPFFieldDescr</td>
                <td>$langCPFFieldDatatype</td>
                <td>$langCPFFieldRequired</td>
                <td>$langCPFFieldRegistration</td>
                <td>$langCPFFieldUserType</td>
                <td>$langCPFFieldVisibility</td>
                <td>" . icon('fa-gears') . "</td>
		        </tr>";
            
            $q = Database::get()->queryArray("SELECT * FROM custom_profile_fields WHERE categoryid = ?d", $res->id);
            if (count($q) == 0) {
                $tool_content .= "<tbody>";
                $tool_content .= "<tr><td colspan='9' class='text-center'><span class='not_visible'>".$langCPFNoFieldinCat."</td></tr>";
                $tool_content .= "</tbody>";
            } else {
                
                $field_types = array(1 => $langCPFText, 2 => $langCPFTextarea, 3 => $langCPFDate, 4 => $langCPFMenu, 5 =>$langCPFLink );
                $yes_no = array(0 => $m['no'], 1 => $m['yes']);
                $visibility = array(1 => $langProfOnly, 10 => $langToAllUsers);
                $user_type = array(1 => $langsTeachers, 5 => $langStudents, 10 => $langAll);
                
                $tool_content .= "<tbody>";
                foreach ($q as $f) {
                    
                    $field_dyntools = array(
                        array('title' => $langModify,
                              'url' => "$_SERVER[SCRIPT_NAME]?edit_field=$f->id",
                              'icon' => 'fa-edit',
                        ),
                        array('title' => $langDelete,
                              'url' => "$_SERVER[SCRIPT_NAME]?del_field=$f->id",
                              'icon' => 'fa-times',
                              'class' => 'delete',
                              'confirm' => $langCPFConfirmFieldDelete
                        )
                    );
                    
                    $tool_content .= "<tr>";
                    $tool_content .= "<td>".q($f->name)."</td>";
                    $tool_content .= "<td>".q($f->shortname)."</td>";
                    $tool_content .= "<td>".standard_text_escape($f->description)."</td>";
                    $tool_content .= "<td>".$field_types[$f->datatype]."</td>";
                    $tool_content .= "<td>".$yes_no[$f->required]."</td>";
                    $tool_content .= "<td>".$yes_no[$f->registration]."</td>";
                    $tool_content .= "<td>".$user_type[$f->user_type]."</td>";
                    $tool_content .= "<td>".$visibility[$f->visibility]."</td>";
                    $tool_content .= "<td>".action_button($field_dyntools)."</td>";
                    $tool_content .= "</tr>";
                }
                $tool_content .= "</tbody>";
            }
            
            $tool_content .= "</table></div>";
        }
        $tool_content .= "</div>";
            
    }
    
}

draw($tool_content, 3, null, $head_content);
