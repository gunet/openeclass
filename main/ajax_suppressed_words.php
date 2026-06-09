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

// Initialize system
require_once '../include/init.php';

// Security check: only authenticated users
if (!isset($_SESSION['uid'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Get the requested action (default is 'words')
$action = $_GET['action'] ?? 'words';

// Check if suppressed words are enabled
if (!get_config('suppressed_words_enabled')) {
    header('Content-Type: application/json; charset=utf-8');
    if ($action === 'version') {
        echo json_encode(0);
    } else {
        echo json_encode([]);
    }
    exit;
}

// Validate action to prevent any unexpected behavior
if (!in_array($action, ['words', 'version'])) {
    $action = 'words';
}

// Fetch data using the existing function
$data = get_suppressed_words_data($action);

// Set proper JSON header and output result
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit;
