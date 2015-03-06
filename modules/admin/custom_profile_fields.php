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

$toolName = $langCustomProfileFieldsAdmin;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_GET['add_cat'])) {
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
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'><input class='btn btn-primary' type='submit' name='submit' value='$langAdd'></div>";
    $tool_content .= "</fieldset></form></div>";
} elseif (isset($_POST['submit'])) {
        
} else {
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

}

draw($tool_content, 3, null, $head_content);
