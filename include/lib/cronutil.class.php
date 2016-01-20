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

class CronUtil {

    public static $dlockpath = '/courses/cron.lock';

    public static function imgOut() {
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
        header('Content-Type: image/gif');
        header('Content-Length: ' . strlen($img));
        header('Connection: Close');
        echo $img;
        error_log("cron image out");
    }

    public static function flush() {
        echo(str_repeat(' ', 256));
        // check that buffer is actually set before flushing
        if (ob_get_length()) {
            @ob_flush();
            @flush();
            @ob_end_flush();
        }
        @ob_start();
        error_log("cron forked");
    }

    public static function lock() {
        global $webDir;
        $lock = $webDir . self::$dlockpath;

        if (file_exists($lock)) {
            self::imgOut();
            error_log("cron lock already exists, exiting. If you think this is an error, please manually rmdir /courses/cron.lock/ directory.");
            exit();
        }
        mkdir($lock);
        error_log("cron lock");
    }

    public static function unlock() {
        global $webDir;
        $lock = $webDir . self::$dlockpath;

        if (file_exists($lock)) {
            rmdir($lock);
        }
        error_log("cron unlock");
    }

}
