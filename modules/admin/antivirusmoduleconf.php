<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/antivirusapp.php';
$nameTools = $langAntivirus;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

$available_themes = active_subdirs("$webDir/template", 'theme.html');

// Scan the connectors directory and locate the appropriate classes
$data['connectorClasses'] = $connectorClasses = AntivirusApp::getAntivirusServices();

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    set_config('antivirus_connector', $_POST['formconnector']);
    foreach($connectorClasses as $curConnectorClass) {
        $connector = new $curConnectorClass();
        foreach($connector->getConfigFields() as $curField => $curLabel) {
            set_config($curField, preg_replace('/[^A-Za-z0-9-.\/]/', '', $_POST['form'.$curField]));
        }
    }

    //Session::Messages($langAntivirusUpdated, 'alert-success');
    Session::flash('message',$langAntivirusUpdated); 
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/antivirusmoduleconf.php');
}
$toolName = $langBasicCfgSetting;
$data['connectorOptions'] = array_map(function($connectorClass) {
    $connector = new $connectorClass();
    $selected = q(get_config('antivirus_connector')) == $connectorClass ? " selected='selected'" : '';
    return "<option value='$connectorClass'$selected>".$connector->getName()."</option>";
}, $connectorClasses);

$head_content .= "
    <script type='text/javascript'>
    function update_connector_config_visibility() {
        $('tr.connector-config').hide();
        $('tr.connector-config input').removeAttr('required');
        $('tr.connector-'+$('select[name=\"formconnector\"]').val()).show();
        $('tr.connector-'+$('select[name=\"formconnector\"]').val()+' input').attr('required', 'required');
    }
    $(document).ready(function() {
        $('select[name=\"formconnector\"]').change(function() {
            update_connector_config_visibility();
        });
        update_connector_config_visibility();
    });
    </script>";


// Display link to index.php
$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "extapp.php",
        'icon' => 'fa-reply',
        'level' => 'primary')));

$data['menuTypeID'] = 3;
view ('admin.other.extapps.antivirus_config', $data);
