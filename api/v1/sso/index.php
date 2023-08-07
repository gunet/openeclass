<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

function api_method($access) {
    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }
    if (!isset($_GET['user_id'])) {
        Access::error(2, 'Required parameter user_id missing');
    }
    $user_id = $_GET['user_id'];
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
