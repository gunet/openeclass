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

/**
 * @file group_creation.php
 * @brief create users group
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'groups';
$helpSubTopic = 'settings';
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'group_functions.php';
$toolName = $langGroups;
$pageName = $langCategoryAdd;

if (isset($_GET['addcategory'])) {
    if (isset($_POST['categoryname'])) {
        $categoryname = $_POST['categoryname'];
    }
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langGroups);
    $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class = 'form-wrapper form-edit rounded'>";
    $tool_content .= "<form class = 'form-horizontal' role='form' method='post' action='index.php?course=$course_code&amp;addcategory=1'>";

    $form_name = $form_description = '';

    $tool_content .= "<fieldset>
                    <legend class='mb-0' aria-label='$langForm'></legend>
                    <div class='form-group".(Session::getError('categoryname') ? " has-error" : "")."'>
                        <label for='CatName' class='col-sm-12 control-label-notes'>$langCategoryName <span class='asterisk Accent-200-cl'>(*)</span></label>
                        <div class='col-sm-12'>
                            <input id='CatName' class='form-control' type='text' name='categoryname' size='53' placeholder='$langCategoryName'>
                            <span class='help-block Accent-200-cl'>".Session::getError('categoryname')."</span>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='CatDesc' class='col-sm-12 control-label-notes'>$langDescription</label>
                        <div class='col-sm-12'>
                            <textarea id='CatDesc' class='form-control' rows='5' name='description'></textarea>
                        </div>
                    </div>
                    <div class='form-group mt-5'>
                        <div class='col-12 d-flex justify-content-end align-items-center'>
                            <input type='submit' class='btn submitAdminBtn' name='submitCategory' value='$langSubmit' />
                            <a href='index.php?course=$course_code' class='btn cancelAdminBtn ms-2'>$langCancel</a>
                        </div>
                    </div>
                    </fieldset>
                 ". generate_csrf_token_form_field() ."
                </form>
            </div></div><div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>
        </div>";
} elseif (isset($_GET['editcategory'])) {
    $id = $_GET['id'];
    category_form_defaults($id);
    $myrow = Database::get()->querySingle("SELECT name,description  FROM group_category WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'><div class = 'form-wrapper form-edit rounded'>";
    $tool_content .= "<form class = 'form-horizontal' role='form' method='post' action='index.php?course=$course_code&amp;editcategory=1'>";
    $tool_content .= "<fieldset>
    <legend class='mb-0' aria-label='$langForm'></legend>
        <div class='form-group".(Session::getError('categoryname') ? " has-error" : "")."'>
            <label for='CatName' class='col-sm-12 control-label-notes'>$langCategoryName <span class='asterisk Accent-200-cl'>(*)</span></label>
            <div class='col-sm-12'>
                <input id='CatName' class='form-control' type='text' name='categoryname' size='53' placeholder='$langCategoryName' $form_name>
                                                <span class='help-block Accent-200-cl'>".Session::getError('categoryname')."</span>
            </div>
        </div>
        <div class='form-group mt-4'>
            <label for='CatDesc' class='col-sm-6 control-label-notes'>$langDescription</label>
            <div class='col-sm-12'>
                <textarea id='CatDesc' class='form-control' rows='5' name='description'>$form_description</textarea>
            </div>
        </div>
        <input type='hidden' name='id' value='" . getIndirectReference($id) . "' />
        <div class='form-group mt-5'>
            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                <input type='submit' class='btn submitAdminBtn' name='submitCategory' value='$langSubmit' />
                <a href='index.php?course=$course_code' class='btn cancelAdminBtn'>$langCancel</a>
            </div>
        </div>
        </fieldset>
     ". generate_csrf_token_form_field() ."
    </form>
    </div></div><div class='d-none d-lg-block'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div>
</div>";
}

draw($tool_content, 2);
