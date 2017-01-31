<?php

/* ========================================================================
 * Open eClass 3.6
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
 * ======================================================================== */


// New course default modules admin page

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/create_course/functions.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'modules.php', 'name' => $langModules);
$pageName = $langDefaultModules;

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_POST['module'])) {
        set_config('default_modules', serialize(array_keys($_POST['module'])));
    }
    Session::Messages($langWikiEditionSucceed, 'alert-success');
    redirect_to_home_page('modules/admin/modules_default.php');
} else {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => $urlAppend . 'modules/admin/modules.php',
              'icon' => 'fa-reply',
              'level' => 'primary-label')), false) .
        "<div class='alert alert-warning'>$langDefaultModulesHelp</div>
         <div class='form-wrapper'>
           <form class='form-horizontal' role='form' action='modules_default.php' method='post'>";

    $disabled = array();
    foreach (Database::get()->queryArray('SELECT module_id FROM module_disable') as $item) {
        $disabled[] = $item->module_id;
    }
    $default = default_modules();

    foreach ($modules as $mid => $minfo) {
        $checked = in_array($mid, $default)? ' checked': '';
        if (in_array($mid, $disabled)) {
            $entry_disabled = ' disabled';
            $not_visible = ' class="not_visible"';
        } else {
            $not_visible = $entry_disabled = '';
        }
        $icon = $minfo['image'];
        if (isset($theme_settings['icon_map'][$icon])) {
            $icon = $theme_settings['icon_map'][$icon];
        }
        $icon = icon($icon);
        $tool_content .= "
           <div class='form-group'>
             <div class='col-xs-12 checkbox'>
               <label$not_visible>
                 <input type='checkbox' name='module[$mid]' value='1'$checked$entry_disabled> " .
                    $icon . '&nbsp;' . q($minfo['title']) . "
               </label>
             </div>
           </div>";
    }
    $tool_content .= "
           <div class='form-group'>
             <div class='col-xs-12'>
               <input class='btn btn-primary' type='submit' name='submit' value='" . q($langSubmitChanges) . "'>
             </div>
           </div>
           ". generate_csrf_token_form_field() ."
         </form>
       </div>";
}

draw($tool_content, 3, null, $head_content);
