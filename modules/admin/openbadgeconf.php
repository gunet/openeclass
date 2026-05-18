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

require_once '../../include/baseTheme.php';
require_once 'controllers/BackpackProviderController.php';

$require_admin = true;
$require_help = true;
$helpTopic = 'external_tools';
$helpSubTopic = 'open_badges';

$toolName = $langBackpackExternalProvider;
$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];
$navigation[] = ['url' => 'extapp.php', 'name' => $langExtAppConfig];

load_js('tools.js');
load_js('validation.js');

try {
    $controller = new BackpackProviderController();
    $controller->handleRequest();
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log('OpenBadges configuration error: ' . $e->getMessage());
    
    $tool_content .= "<div class='alert alert-danger'>
        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
        <span>An error occurred while processing your request. Please try again later.</span>
    </div>";
    
    draw($tool_content, 3, null);
}
