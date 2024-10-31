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

    const periodType = array(
        DAILY => array('title' => 'Ημερήσιος Υπολογισμός'),
        WEEKLY => array('title' => 'Εβδομαδιαίος Υπολογισμός'),
        MONTHLY => array('title' => 'Μηνιαίος Υπολογισμός'),
        TOTAL => array('title' => 'Συνολικός Υπολογισμός')
    );
}

