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


$require_admin = TRUE;
require_once '../../include/baseTheme.php';

// Main body
$activate = isset($_GET['activate']) ? $_GET['activate'] : ''; //variable of declaring the activation update
// update process for all the inactive records/users
if ((!empty($activate)) && ($activate == 1)) {
    // update
    $countinactive = Database::get()->query("UPDATE user SET expires_at = ".DBHelper::timeAfter(15552000) . " WHERE expires_at<= CURRENT_DATE()")->affectedRows;
    if ($countinactive > 0) {
        Session::flash('message',"$langRealised $countinactive $langChanges");
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message',$langNoChanges);
        Session::flash('alert-class', 'alert-warning');
    }
}
redirect_to_home_page('modules/admin/listusers.php');
