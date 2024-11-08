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

function api_method($access) {
    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }
    if (!isset($_REQUEST['user_id'])) {
        Access::error(2, 'Required parameter user_id missing');
    }
    $user_id = $_REQUEST['user_id'];
    $user = Database::get()->querySingle('SELECT id, username, expires_at > NOW() FROM user
        WHERE id = ?d', $user_id);
    if (!$user) {
        Access::error(3, "User with id '$user_id' not found");
    }
    $login_url = $GLOBALS['urlServer'] . 'modules/auth/sso.php?user=' . urlencode($user->username) .
        '&token=' . token_generate("login user={$user->username}", true);
    header('Content-Type: application/json');
    echo json_encode(['url' => $login_url], JSON_UNESCAPED_UNICODE);
    exit();
}

chdir('..');
require_once 'apiCall.php';
