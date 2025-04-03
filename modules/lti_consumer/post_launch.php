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


$require_current_course = TRUE;
$require_login = TRUE;
require_once '../../include/baseTheme.php';
require_once 'lti-functions.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_LTI_CONSUMER);
/* * *********************************** */

// guest user not allowed
if (check_guest()) {
    die();
}

$resource_link_id = getDirectReference($_GET['id']);
$lti_app = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $resource_link_id);
if (!$lti_app) {
    die();
}

echo create_join_button_for_ltitool($lti_app);
echo create_js_for_immediatelaunch($lti_app);