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

define('DAILY', 0);
define('WEEKLY', 1);
define('MONTHLY',2);
define('TOTAL', 3);


abstract class PeriodType {

    public static function periodType() {
        global $langDailyCount, $langWeeklyCount, $langMonthlyCount, $langTotalCount;

        return array(
            DAILY => array('title' => $langDailyCount),
            WEEKLY => array('title' => $langWeeklyCount),
            MONTHLY => array('title' => $langMonthlyCount),
            TOTAL => array('title' => $langTotalCount)
        );
    }
}

