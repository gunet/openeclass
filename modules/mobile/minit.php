<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


define('RESPONSE_OK', 'OK');
define('RESPONSE_FAILED', 'FAILED');
define('RESPONSE_EXPIRED', 'EXPIRED');
define('M_INIT', 1);


if (isset($require_noerrors) && $require_noerrors) {
    if (ini_get('display_errors'))
        ini_set('display_errors', 0);
}

if (isset($require_mlogin) && $require_mlogin) {
    
    if (!isset($_REQUEST['token'])) {
        echo RESPONSE_FAILED;
        exit();
    } else {
        session_id($_REQUEST['token']);
        session_start();
        $_SESSION['mobile'] = true;
    }

    if (!isset($_SESSION['uid'])) {
        echo RESPONSE_EXPIRED;
        exit();
    }
}

// necessary before including init.php
if (isset($require_mcourse) && $require_mcourse) {
    if (!isset($_REQUEST['course'])) {
        echo RESPONSE_FAILED;
        exit();
    } else
        $require_current_course = true;
}
require_once ('../../include/init.php');


if (isset($_REQUEST['profile']))
    $_SESSION['profile'] = $_REQUEST['profile'];

