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


$require_current_course = TRUE;
$require_login = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/lti_consumer/lti-functions.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_ASSIGN);
/* * *********************************** */

// guest user not allowed
if (check_guest()) {
    die();
}

$resource_link_id = intval($_GET['id']);
$assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $resource_link_id);
$lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $assignment->lti_template);
if (!$lti) {
    die();
}

echo create_join_button(
    $lti->lti_provider_url,
    $lti->lti_provider_key,
    $lti->lti_provider_secret,
    $assignment->id,
    "assignment",
    $assignment->title,
    $assignment->description,
    $assignment->launchcontainer,
    '',
    $assignment
);

echo '<script type="text/javascript">
//<![CDATA[
document.ltiLaunchForm.submit();
//]]>
</script>';