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

require_once '../../include/baseTheme.php';
require_once 'api/v1/access.class.php';
require_once 'include/log.class.php';

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
    Access::error(0, 'No error');
}
