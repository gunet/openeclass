<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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


// Disable modules admin page

$require_admin = true;
require_once '../../include/baseTheme.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$pageName = $langDisableModules;

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    Database::get()->query('DELETE FROM module_disable');
    $arr = array();
    if (isset($_POST['moduleDisable'])) {
        foreach ($_POST['moduleDisable'] as $key => &$value){
          $arr[getDirectReference($key)] = $value;
        }
        $optArray = implode(', ', array_fill(0, count($_POST['moduleDisable']), '(?d)'));
        Database::get()->query('INSERT INTO module_disable (module_id) VALUES ' . $optArray, array_keys($arr));
    }
    Session::Messages($langWikiEditionSucceed, 'alert-success');
    redirect_to_home_page('modules/admin/modules.php');
} else {
    $data['disabled'] = [];
    foreach (Database::get()->queryArray('SELECT module_id FROM module_disable') as $item) {
        $data['disabled'][] = $item->module_id;
    }
    $data['action_bar'] = action_bar(
                        [
                            [
                                'title' => $langBack,
                                'url' => $urlAppend . 'modules/admin/index.php',
                                'icon' => 'fa-reply',
                                'level' => 'primary-label'
                            ]
                        ], false);

    $alwaysEnabledModules = array(MODULE_ID_AGENDA, MODULE_ID_DOCS, MODULE_ID_ANNOUNCE, MODULE_ID_DROPBOX, MODULE_ID_DESCRIPTION);
    foreach ($alwaysEnabledModules as $alwaysEnabledModule) {
        unset($modules[$alwaysEnabledModule]);
    }
    $data['modules'] = $modules;
}
$data['menuTypeID'] = 3;
view('admin.other.modules', $data);
