<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

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

$tool_content .= action_bar(array(
    array(
        'title' => $langBack,
        'level' => 'primary-label',
        'icon' => 'fa-reply',
        'url' => "index.php?course=$course_code"
    )
));

    if (isset($_GET['addcategory'])) {
        if (isset($_POST['categoryname'])) {
            $categoryname = $_POST['categoryname'];
        }
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langGroups);
        $tool_content .= "<div class = 'form-wrapper'>";
        $tool_content .= "<form class = 'form-horizontal' role='form' method='post' action='index.php?course=$course_code&amp;addcategory=1'>";

        $form_name = $form_description = '';
        $form_legend = $langCategoryAdd;

        $tool_content .= "<fieldset>
                        <div class='form-group".(Session::getError('categoryname') ? " has-error" : "")."'>
                            <label for='CatName' class='col-sm-2 control-label'>$langCategoryName:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='categoryname' size='53' placeholder='$langCategoryName'>
                                <span class='help-block'>".Session::getError('categoryname')."</span>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='CatDesc' class='col-sm-2 control-label'>$langDescription:</label>
                            <div class='col-sm-10'>
                                <textarea class='form-control' rows='5' name='description'></textarea>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>
                                <input type='submit' class='btn btn-primary' name='submitCategory' value='$form_legend' />
                                <a href='index.php?course=$course_code' class='btn btn-default'>$langCancel</a>
                            </div>
                        </div>
                        </fieldset>
                     ". generate_csrf_token_form_field() ."
                    </form>
                </div>";
    } elseif (isset($_GET['editcategory'])) {
        $id = $_GET['id'];        
        category_form_defaults($id);
        $myrow = Database::get()->querySingle("SELECT name,description  FROM group_category WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $form_legend = $langCategoryMod;	
        $tool_content .= "<div class = 'form-wrapper'>";
        $tool_content .= "<form class = 'form-horizontal' role='form' method='post' action='index.php?course=$course_code&amp;editcategory=1'>";
        $tool_content .= "<fieldset>
            <div class='form-group".(Session::getError('categoryname') ? " has-error" : "")."'>
                <label for='CatName' class='col-sm-2 control-label'>$langCategoryName:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='categoryname' size='53' placeholder='$langCategoryName' $form_name>
                                                    <span class='help-block'>".Session::getError('categoryname')."</span>
                </div>
            </div>
            <div class='form-group'>
                <label for='CatDesc' class='col-sm-2 control-label'>$langDescription:</label>
                <div class='col-sm-10'>
                    <textarea class='form-control' rows='5' name='description'>$form_description</textarea>
                </div>
            </div>
            <input type='hidden' name='id' value='" . getIndirectReference($id) . "' />
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input type='submit' class='btn btn-primary' name='submitCategory' value='$form_legend' />
                    <a href='index.php?course=$course_code' class='btn btn-default'>$langCancel</a>
                </div>
            </div>
            </fieldset>
         ". generate_csrf_token_form_field() ."
        </form>
        </div>";
    }

draw($tool_content, 2);