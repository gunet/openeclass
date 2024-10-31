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

class Debug {

    public static $level = Debug::ERROR;
    private static $default_level = Debug::ERROR;
    private static $log_location = null;
    private static $output;

    const LOW = 0;
    const INFO = 10;
    const WARNING = 20;
    const ERROR = 30;
    const CRITICAL = 40;
    const ALWAYS = 100;

    public static function setLevel($level) {
        Debug::$level = abs(intval($level));
    }

    public static function setDefaultLevel($level) {
        Debug::$default_level = abs(intval($level));
    }

    /**
     * Set the current output module for Debug
     * @param type $newoutput Type of: function ($message, $level)
     */
    public static function setOutput($newoutput) {
        Debug::$output = $newoutput;
    }

    public static function message($message, $level = null, $backtrace_file = null, $backtrace_line = 'unknown') {
        if (is_null($level))
            $level = Debug::$default_level;
        if ($level >= Debug::$level) {
            if (is_null($backtrace_file)) {
                $backtrace_entry = debug_backtrace();
                $backtrace_file = $backtrace_entry[1]['file'];
                $backtrace_line = $backtrace_entry[1]['line'];
            }
            $full_message = "<p>In file <b>" . $backtrace_file . "</b> on line <b>" . $backtrace_line . "</b> : " . "<i>$message</i></p>";
            if (is_null(Debug::$output)) {
                error_log($full_message);
                if (ini_get('display_errors')) {
                    echo $full_message;
                }
            } else {
                $curoutput = Debug::$output;
                $curoutput($full_message, $level);
            }
        }
        if (!is_null(Debug::$log_location))
            file_put_contents(Debug::$log_location, $message . "\n", FILE_APPEND | LOCK_EX);
    }

}
