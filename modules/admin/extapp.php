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

$require_admin = true;
$require_help = true;

if (isset($_GET['edit'])) {
    $helpTopic = 'external_tools';
    switch ($_GET['edit']) {
        case 'onedrive':
                $helpSubTopic = 'one_drive';
            break;
        case 'dropbox':
                $helpSubTopic = 'drop_box';
            break;
        case 'owncloud':
                $helpSubTopic = 'own_cloud';
            break;
        case 'webdav':
                $helpSubTopic = 'web_dav';
            break;
        case 'analytics':
                $helpSubTopic = 'analytics';
            break;
        case 'ftp':
                $helpSubTopic = 'ftp';
            break;
        default:
                $helpSubTopic = 'external_tools';
            break;
    }
} else {
    $helpTopic = 'system_settings';
    $helpSubTopic = 'external_tools';
}

require_once '../../include/baseTheme.php';
require_once 'extconfig/externals.php';

$toolName = $langAdmin;
$pageName = $langExtAppConfig;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
load_js('tools.js');
load_js('validation.js');

$data['appName'] = $appName = $_GET['edit'] ?? null;
// Code to be executed with Ajax call when clicking the activate/deactivate button from External App list page
if (isset($_POST['state'])) {
    $appName = $_POST['appName'];
    if (showSecondFactorChallenge()) {
        $parts = explode(',' ,$appName);
        if (count($parts) > 1){
            $appName = $parts[0];
            $_POST['sfaanswer'] = $parts[count($parts)-1];
        } else {
            $_POST['sfaanswer'] = '';
        }
        checkSecondFactorChallenge();
    }
    $newState = $_POST['state'] == 'fa-toggle-on' ? 0 : 1;
    $appNameAjax = getDirectReference($appName);

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
            Session::flash('message',$langFileUpdatedSuccess);
            Session::flash('alert-class', 'alert-info');
        } else {
            $result = $app->storeParams();
        }
        if ($result) {
            Session::flash('message',$result);
            Session::flash('alert-class', 'alert-danger');
        } else {
            Session::flash('message',$langFileUpdatedSuccess);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page($app->getConfigUrl());
    }

    $navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);
    $pageName = $langModify . ' ' . $app->getDisplayName();

    $view = "admin.other.extapps.config";
} else {
    $view = "admin.other.extapps.index";
}

view($view, $data);
