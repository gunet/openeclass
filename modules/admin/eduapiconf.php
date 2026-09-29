<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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
require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/eduapiapp.php';

$app = ExtAppManager::getApp('eduapi');
$toolName = $langConfig . ' ' . $app->getDisplayName();
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);


if (isset($_POST['submit'])) {
  if (!isset($_POST['token']) || !validate_csrf_token($_POST['token']))
    csrf_token_error();

  if ($_POST['submit'] == 'clear') {
    // Clear all configuration
    set_config('eduapi_base_url', '');
    set_config('eduapi_token_url', '');
    set_config('eduapi_client_id', '');
    set_config('eduapi_client_secret', '');
    $app->setEnabled(false);

    Session::flash('message', $langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-info');
  } else {
    // Save configuration
    set_config('eduapi_base_url', $_POST['eduapi_base_url']);
    set_config('eduapi_token_url', $_POST['eduapi_token_url']);
    set_config('eduapi_client_id', $_POST['eduapi_client_id']);
    set_config('eduapi_client_secret', $_POST['eduapi_client_secret']);

    // Handle enabled checkbox
    if (isset($_POST['enabled'])) {
      $app->setEnabled(true);
    } else {
      $app->setEnabled(false);
    }

    Session::flash('message', $langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
  }
  redirect_to_home_page('modules/admin/eduapiconf.php');
}

view('admin.eduapi.conf', [
    'base_url' => get_config('eduapi_base_url'),
    'token_url' => get_config('eduapi_token_url'),
    'client_id' => get_config('eduapi_client_id'),
    'client_secret' => get_config('eduapi_client_secret'),
    'enabled' => $app->isEnabled(),
    'form_image' => get_form_image(),
]);
