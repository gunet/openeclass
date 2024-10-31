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

const LTI_TYPE = 'generic';

$require_current_course = true;
$require_course_admin = true;
$require_help = TRUE;
$helpTopic = 'lti_consumer';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'lti-functions.php';

require_once 'include/lib/modalboxhelper.class.php';
ModalBoxHelper::loadModalBox();

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_LTI_CONSUMER);
/* * *********************************** */

$toolName = $langToolManagement;

load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('validation.js');

load_js('select2');

if ($is_editor) {
    if (isset($_GET['add']) or isset($_GET['choice'])) {
        if (isset($_GET['add'])) {
            $pageName = $langNewLTITool;
        } elseif ((isset($_GET['choice'])) and $_GET['choice'] == 'edit') {
            $pageName = $langModify;
        }
    } else {
        if (isset($_GET['id'])) {
            $action_bar = action_bar(array(
                array('title' => $langBack,
                      'url' => "../course_tools/index.php?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary')));
        } else {
            $action_bar = action_bar(array(
                array('title' => $langNewLTITool,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=1",
                      'icon' => 'fa-plus-circle',
                      'button-class' => 'btn-success',
                      'level' => 'primary-label',
                      'show' => 1)));
        }
        $tool_content .= $action_bar;
    }
}

if (isset($_GET['add'])) {
    $navigation[] = array('url' => "../course_tools/index.php?course=$course_code", 'name' => $langToolManagement);
    new_lti_app(false, $course_code);
}
elseif(isset($_POST['update_lti_app']))
{
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    add_update_lti_app($_POST['title'], $_POST['desc'], $_POST['lti_url'], $_POST['lti_key'], $_POST['lti_secret'],
        $_POST['lti_launchcontainer'], $_POST['status'], $_POST['lti_courses'],null, false,
        true, getDirectReference($_GET['id']), LTI_TYPE);
        Session::flash('message',$langLTIAppAddSuccessful);
        Session::flash('alert-class', 'alert-success');
    redirect("../course_tools/index.php?course=$course_code");
}
elseif(isset($_GET['choice']))
{
    $navigation[] = array('url' => "../course_tools/index.php?course=$course_code", 'name' => $langToolManagement);
    switch($_GET['choice'])
    {
        case 'edit':
            edit_lti_app(getDirectReference($_GET['id']));
            break;
        case 'do_delete':
            delete_lti_app(getDirectReference($_GET['id']));
            Session::flash('message',$langBBBDeleteSuccessful);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/course_tools/index.php?course=$course_code");
            break;
        case 'do_disable':
            disable_lti_app(getDirectReference($_GET['id']));
            break;
        case 'do_enable':
            enable_lti_app(getDirectReference($_GET['id']));
            break;
        case 'do_join':
            break;
    }

} elseif(isset($_POST['new_lti_app'])) { // new lti app
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    add_update_lti_app($_POST['title'], $_POST['desc'], $_POST['lti_url'], $_POST['lti_key'], $_POST['lti_secret'],
                       $_POST['lti_launchcontainer'], $_POST['status'], $_POST['lti_courses'], LTI_TYPE, $course_id, false,
                       false, null);
    Session::flash('message',$langLTIAppAddSuccessful);
    Session::flash('alert-class', 'alert-success');

    redirect("../course_tools/index.php?course=$course_code");
}
else {
    lti_app_details();
}
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
