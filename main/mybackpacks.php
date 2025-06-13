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

$require_login = true;
$require_help = true;
$helpTopic = 'Portfolio';

require_once '../include/baseTheme.php';
require_once  dirname(__DIR__) .  '/modules/main/services/BackpackConnectionService.php';
require_once  dirname(__DIR__) .  '/modules/admin/repositories/BackpackProviderRepository.php';

$toolName = $langPortfolio;
$pageName = $langMyBackpacks ?? 'My Backpacks';

// Initialize services
$backpackProviderRepo = new BackpackProviderRepository();
$backpackConnectionService = new BackpackConnectionService();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'connect':
                handleBackpackConnection($backpackProviderRepo, $backpackConnectionService);
                break;
            case 'disconnect':
                handleBackpackDisconnection($backpackConnectionService);
                break;
        }
    }
}

// Get user's current backpack connection
$userConnection = $backpackConnectionService->getUserConnection($uid);

// Get available backpack providers
$availableProviders = $backpackProviderRepo->findActive();

// Prepare data for view
$viewData = [
    'userConnection' => $userConnection,
    'availableProviders' => $availableProviders,
    'uid' => $uid,
    'urlAppend' => $urlAppend
];

view('main.mybackpacks', $viewData);

/**
 * Handle backpack connection
 */
function handleBackpackConnection(
    BackpackProviderRepository $providerRepo, 
    BackpackConnectionService $connectionService
): void {
    global $uid;
    
    $providerId = intval($_POST['provider_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$providerId) {
        Session::flash('message', trans('langBackpackProviderRequired'));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('main/mybackpacks.php');
        return;
    }
    
    $provider = $providerRepo->findById($providerId);
    if (!$provider || !$provider->isEnabled()) {
        Session::flash('message', trans('langBackpackProviderNotFound'));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('main/mybackpacks.php');
        return;
    }
    
    // Validate connection data
    $errors = $connectionService->validateConnectionData($provider, $email, $password);
    if (!empty($errors)) {
        Session::flash('message', implode('<br>', $errors));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('main/mybackpacks.php');
        return;
    }
    
    // Attempt connection
    $success = $connectionService->connectBackpack($uid, $providerId, $email, $password);
    
    if ($success) {
        Session::flash('message', trans('langBackpackConnectedSuccessfully'));
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message', trans('langBackpackConnectionFailed'));
        Session::flash('alert-class', 'alert-danger');
    }
    
    redirect_to_home_page('main/mybackpacks.php');
}

/**
 * Handle backpack disconnection
 */
function handleBackpackDisconnection(BackpackConnectionService $connectionService): void
{
    global $uid;
    
    $success = $connectionService->disconnectBackpack($uid);
    
    if ($success) {
        Session::flash('message', trans('langBackpackDisconnectedSuccessfully'));
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message', trans('langBackpackDisconnectionFailed'));
        Session::flash('alert-class', 'alert-danger');
    }
    
    redirect_to_home_page('main/mybackpacks.php');
}
