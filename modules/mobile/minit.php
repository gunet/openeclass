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
require_once '../../vendor/autoload.php';

define('RESPONSE_OK', 'OK');
define('RESPONSE_FAILED', 'FAILED');
define('RESPONSE_EXPIRED', 'EXPIRED');
define('RESPONSE_NOTENABLED', 'NOTENABLED');
define('RESPONSE_USERNOTACTIVE', 'USERNOTACTIVE');
define('M_INIT', 1);
header('Content-Type: application/xml; charset=utf-8');

if (isset($require_noerrors) && $require_noerrors) {
    if (ini_get('display_errors')) {
        ini_set('display_errors', 0);
    }
}

if (isset($require_mlogin) && $require_mlogin) {

    if (!isset($_POST['token'])) {
        echo RESPONSE_FAILED;
        exit();
    } else {
        session_id($_POST['token']);
        session_start();
        $_SESSION['mobile'] = true;
    }

    if (!isset($_SESSION['uid'])) {
        echo RESPONSE_EXPIRED;
        session_regenerate_id();
        exit();
    }
}

// necessary before including init.php
if (isset($require_mcourse) && $require_mcourse) {
    if (!isset($_REQUEST['course'])) {
        echo RESPONSE_FAILED;
        exit();
    } else {
        $require_current_course = true;
        if (!isset($_GET['course'])) {
            $_GET['course'] = $_REQUEST['course'];
        }
    }
}
require_once '../../include/init.php';
require_once 'include/main_lib.php';
// reset the header because init also sets the header
header('Content-Type: application/xml; charset=utf-8');

if (isset($_REQUEST['profile'])) {
    $_SESSION['profile'] = $_REQUEST['profile'];
}

if (!get_config('enable_mobileapi')) {
    echo RESPONSE_NOTENABLED;
    exit();
}
