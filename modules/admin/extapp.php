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

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'extconfig/externals.php';

$toolName = $langExtAppConfig;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
load_js('tools.js');
load_js('validation.js');
$available_themes = active_subdirs("$webDir/template", 'theme.html');

$data['appName'] = $appName = isset($_GET['edit'])? $_GET['edit']: null;
// Code to be executed with Ajax call when clicking the activate/deactivate button from External App list page
if (isset($_POST['state'])) {
    $appName = $_POST['appName'];
    if(showSecondFactorChallenge()!=""){
        $parts = explode(",",$appName);
        if(count($parts)>1){ 
            $appName = $parts[0];
            $_POST['sfaanswer'] = $parts[count($parts)-1];
        }else{
            $_POST['sfaanswer'] = '';
        }
        checkSecondFactorChallenge();
    }
    $newState = $_POST['state'] == 'fa-toggle-on' ? 0 : 1;    
    $appNameAjax = getDirectReference($appName);    
    
    if (($appNameAjax == 'openmeetings') and $newState == 1) {
        $app_bbb = ExtAppManager::getApp('bigbluebutton');        
        $app_bbb->setEnabled(!$newState); // disable bigbluebutton if openmeetings has been enabled      
    }
    
    if (($appNameAjax == 'bigbluebutton') and $newState == 1) {
        $app_bbb = ExtAppManager::getApp('openmeetings');
        $app_bbb->setEnabled(!$newState);  // disable openmeetings if bigbluebutton has been enabled          
    }
    
    ExtAppManager::getApp($appNameAjax)->setEnabled($newState);    
    echo $newState;    
    exit;
}

if ($appName) {
    $data['app'] = $app = ExtAppManager::getApp($appName);

    if (isset($_POST['submit'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        if ($_POST['submit'] == 'clear') {
            foreach ($app->getParams() as $param) {
                $param->setValue('');
                $param->persistValue();
            }
            Session::Messages($langFileUpdatedSuccess, 'alert-info');
        } else {
        $result = $app->storeParams();
        if ($result) {
            Session::Messages($result, 'alert-danger');
        } else {
            Session::Messages($langFileUpdatedSuccess, 'alert-success');
        }
    }
        redirect_to_home_page($app->getConfigUrl());
    }

    $navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
    $pageName = $langModify . ' ' . $app->getDisplayName();
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => 'extapp.php',
              'icon' => 'fa-reply',
              'level' => 'primary-label')));

    $view = "admin.other.extapps.config";
} else {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    $view = "admin.other.extapps.index";
}

$data['menuTypeID'] = 3;
view($view, $data);
