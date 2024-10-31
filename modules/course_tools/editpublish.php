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

$require_current_course = true;
$require_course_admin = true;

require_once '../../include/baseTheme.php';
require_once 'publish-functions.php';
require_once 'modules/admin/extconfig/ltipublishapp.php';
require_once 'modules/lti/classes/LtiEnrolHelper.php';

// check if LTI Provider is enabled (global config) and available for the current course
$ltipublishapp = ExtAppManager::getApp('ltipublish');
if (!$ltipublishapp->isEnabledForCurrentCourse()) {
    $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>Tool is not enabled for course</span></div>";
    draw($tool_content, 2);
    exit;
}

$toolName = $langLtiPublishTool;
$pageName = $langNewLTITool;
if (isset($_GET['choice'])) {
    $pageName = $langModify;
    if ($_GET['choice'] == 'show') {
        $pageName = $langViewShow;
    }
}

load_js('validation.js');

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php?course=$course_code",
        'icon' => 'fa-reply',
        'level' => 'primary')));

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langToolManagement);

if (isset($_POST['new_publish_ltiapp'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    add_publish_ltiapp($_POST['title'], $_POST['desc'], $_POST['lti_key'], $_POST['lti_secret'], $_POST['status']);
    Session::flash('message',$langPUBLTIAppAddSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect("index.php?course=$course_code");
} else if (isset($_POST['update_publish_ltiapp'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    update_publish_ltiapp(getDirectReference($_POST['id']), $_POST['title'], $_POST['desc'], $_POST['lti_key'], $_POST['lti_secret'], $_POST['status']);
    Session::flash('message',$langPUBLTIAppAddSuccessful);
    Session::flash('alert-class', 'alert-success');
    redirect("index.php?course=$course_code");
} else if (isset($_GET['choice'])) {
    switch($_GET['choice']) {
        case 'edit':
            edit_publish_ltiapp(getDirectReference($_GET['id']));
            break;
        case 'do_delete':
            delete_publish_ltiapp(getDirectReference($_GET['id']));
            Session::flash('message',$langLTIAppDeleteSuccessful);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/course_tools/index.php?course=$course_code");
            break;
        case 'do_disable':
            disable_publish_ltiapp(getDirectReference($_GET['id']));
            break;
        case 'do_enable':
            enable_publish_ltiapp(getDirectReference($_GET['id']));
            break;
        case 'show':
            show_publish_ltiapp(getDirectReference($_GET['id']));
            break;
        default:
            break;
    }
} else {
    new_publish_ltiapp();
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
