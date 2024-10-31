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

class CronUtil {

    public static $dlockpath = '/courses/cron.lock';

    public static function imgOut() {
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
        header('Content-Type: image/gif');
        header('Content-Length: ' . strlen($img));
        header('Connection: Close');
        echo $img;
        Debug::message('cron image out', Debug::INFO);
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
        Debug::message('cron forked', Debug::INFO);
    }

    public static function lock() {
        global $webDir;
        $lock = $webDir . self::$dlockpath;

        if (file_exists($lock)) {
            self::imgOut();
            Debug::message('cron lock already exists, exiting. If you think this is an error, please manually rmdir /courses/cron.lock/ directory.', Debug::WARNING);
            exit();
        }
        mkdir($lock);
        Debug::message('cron lock', Debug::INFO);
    }

    public static function unlock() {
        global $webDir;
        $lock = $webDir . self::$dlockpath;

        if (file_exists($lock)) {
            rmdir($lock);
        }
        Debug::message('cron unlock', Debug::INFO);
    }

}
