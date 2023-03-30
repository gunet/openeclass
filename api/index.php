<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
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
