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

require_once 'include/baseTheme.php';
define('DLOCK', $webDir . '/courses/cron.lock');
session_write_close();

lock();
ignore_user_abort(true);
imgOut();
custom_flush();
cronjob();
unlock();


function cronjob() {
    $file = '/tmp/koko.txt';
    file_put_contents($file, "run1: ". date('G:i:s') ."\n", FILE_APPEND);
    sleep(7);
    file_put_contents($file, "run2: ". date('G:i:s') ."\n", FILE_APPEND);
}

function imgOut() {
    $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
    header('Content-Type: image/gif');
    header('Content-Length: ' . strlen($img));
    header('Connection: Close');
    echo $img;
}

function lock() {
    if (file_exists(DLOCK)) {
        imgOut();
        exit();
    }
    mkdir(DLOCK);
}

function unlock() {
    if (file_exists(DLOCK))
        rmdir (DLOCK);
}

function custom_flush() {
    echo(str_repeat(' ', 256));
    // check that buffer is actually set before flushing
    if (ob_get_length()){           
        @ob_flush();
        @flush();
        @ob_end_flush();
    }   
    @ob_start();
}
