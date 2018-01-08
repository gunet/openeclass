<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
        $default = array_map(getDirectReference, array_keys($_POST['module']));
        set_config('default_modules', serialize($default));
    }
    Session::Messages($langWikiEditionSucceed, 'alert-success');
    redirect_to_home_page('modules/admin/modules_default.php');
} else {
    $data['disabled'] = [];
    foreach (Database::get()->queryArray('SELECT module_id FROM module_disable') as $item) {
        $data['disabled'][] = $item->module_id;
    }
    $data['modules'] = $modules;
    $data['default'] = default_modules();

    $data['action_bar'] = action_bar(
        [
            [ 'title' => $langBack,
              'url' => $urlAppend . 'modules/admin/modules.php',
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);
    $data['menuTypeID'] = 3;
    view('admin.other.modules_default', $data);
}
