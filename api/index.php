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

/*
 * @file api/index.php
 *
 * @abstract Return list of active API levels
 *
 */

require_once '../include/init.php';

header('Content-Type: application/json');
if (!get_config('ext_apitoken_enabled')) {
    echo json_encode([
            'errorcode' => 999,
            'errormessage' => 'The Open eClass API is disabled',
        ], JSON_UNESCAPED_UNICODE);
} else {
    $api_levels = [
        'v1' => $urlServer . 'api/v1/',
    ];
    echo json_encode($api_levels, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
