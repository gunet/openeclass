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



if (!defined('COURSE_OPEN')) {
    require_once '../../include/baseTheme.php';
}
require_once 'api/v1/access.class.php';
require_once 'include/log.class.php';

if (!get_config('ext_apitoken_enabled')) {
    Access::error(999, 'The Open eClass API is disabled');
}

$token = Access::getToken();

if ($token) {
    $access = Access::fromToken($token);

    if (!$access->isValid) {
        Access::error(1, 'The token provided was not valid');
    }
} else {
    $access = new Access();
}

if (function_exists('api_method')) {
    api_method($access);
} else {
    $base = $urlServer . 'api/v1/';
    $api_methods = [
        [
            'url' => $base . 'categories/',
            'method' => 'GET',
            'auth_required' => false,
            'description' => 'Get platform categories',
        ],
        [
            'url' => $base . 'categories/?id={N}',
            'method' => 'GET',
            'auth_required' => false,
            'description' => 'Get info about category with id {N}',
        ],
        [
            'url' => $base . 'courses/',
            'method' => 'GET',
            'auth_required' => false,
            'description' => 'Get platform courses',
        ],
        [
            'url' => $base . 'users/?id={N}',
            'method' => 'GET',
            'auth_required' => false,
            'description' => 'Get info of user with id {N}',
        ],
        [
            'url' => $base . 'users/?username={S}',
            'method' => 'GET',
            'auth_required' => false,
            'description' => 'Get info of user with username {S}',
        ],
        [
            'url' => $base . 'registration/?course_id={S}',
            'method' => 'GET',
            'auth_required' => false,
            'description' => 'Get registration info',
        ],
    ];
    header('Content-Type: application/json');
    echo json_encode($api_methods, JSON_UNESCAPED_UNICODE);
}


