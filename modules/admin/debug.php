<?php

/*
 * ========================================================================
 * Open eClass 3.0 - E-learning and Course Management System
 * ========================================================================
  Copyright(c) 2003-2013  Greek Universities Network - GUnet
  A full copyright notice can be read in "/info/copyright.txt".

  Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
  Yannis Exidaridis <jexi@noc.uoa.gr>
  Alexandros Diamantidis <adia@noc.uoa.gr>

  For a full list of contributors, see "credits.txt".
 */

class Debug {

    private static $level = Debug::ERROR;
    private static $default_level = Debug::ERROR;
    private static $log_location = null;

    const LOW = 0;
    const INFO = 10;
    const ERROR = 20;
    const CRITICAL = 30;
    const ALWAYS = 100;

    public static function setLevel($level) {
        Debug::$level = abs(intval($level));
    }

    public static function setDefaultLevel($level) {
        Debug::$default_level = abs(intval($level));
    }

    public static function message($message, $level = null, $backtrace_file = null, $backtrace_line = 'unknown') {
        if (is_null($level))
            $level = Debug::$default_level;
        if ($level >= Debug::$level) {
            echo "<p><br>";
            if (!is_null($backtrace_file)) {
                echo "In file <b>" . $backtrace_file . "</b> on line <b>" . $backtrace_line . "</b>";
            }
            echo "<br> $message </p>";
        }
        if (!is_null(Debug::$log_location))
            file_put_contents(Debug::$log_location, $message . "\n", FILE_APPEND | LOCK_EX);
    }

}
