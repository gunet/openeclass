<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

abstract class DBHelper {

    private static $helper_impl;

    private static function impl() {
        if (!DBHelper::$helper_impl) {
            switch (DB_TYPE) {
                case "MYSQL":
                default :
                    DBHelper::$helper_impl = new _DBHelper_MYSQL();
            }
        }
        return DBHelper::$helper_impl;
    }

    /**
     * @deprecated It should be private.
     * @param type $unixDate The date in numeric format. If it is missing (or null) defaults to "now".
     * @return timestamp The timestamp in SQL wrapper
     */
    public static function intToDate($unixDate = null) {
        if (is_null($unixDate))
            $unixDate = time();
        return DBHelper::impl()->intToDateImpl(intval($unixDate));
    }

    /**
     * 
     * @param int $secondsOffset Offset by current time in seconds
     * @return timestamp The timestamp in SQL wrapper
     */
    public static function timeAfter($secondsOffset = null) {
        if (is_null($secondsOffset))
            $secondsOffset = 0;
        return DBHelper::impl()->intToDateImpl(time() + intval($secondsOffset));
    }

    abstract protected function intToDateImpl($unixdate);
}

/**
 * Private object to implement the various helper commands.
 * All method parameters should be already checked.
 */
class _DBHelper_MYSQL extends DBHelper {

    protected function intToDateImpl($unixdate) {
        return "FROM_UNIXTIME(" . $unixdate . ")";
    }

}
