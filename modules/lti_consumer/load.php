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
$require_help = TRUE;
$helpTopic = 'lti_consumer';

require_once '../../include/baseTheme.php';
require_once 'lti-functions.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_LTI_CONSUMER);
/* * *********************************** */

$toolName = $langLtiConsumer;

// guest user not allowed
if (check_guest()) {
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langNoGuest</span></div></div>";
    draw($tool_content, 2);
    exit;
}

$resource_link_id = getDirectReference($_GET['id']);
$lti_app = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $resource_link_id);
if (!$lti_app) {
    redirect_to_home_page("courses/" . $course_code);
}

$head_content .= create_js_for_docreadylaunch($lti_app);

$pageName = q($lti_app->title);
$tool_content .= action_bar(array(
    array(
        'title' => $langBack,
        'url' => $urlServer . "courses/" . $course_code,
        'icon' => 'fa-reply',
        'level' => 'primary'
    )
));

$tool_content .= create_join_button_for_ltitool($lti_app);

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
