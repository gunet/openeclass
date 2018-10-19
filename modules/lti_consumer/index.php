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

$toolName = $langLtiConsumer;

// guest user not allowed
if (check_guest()) {
    $tool_content .= "<div class='alert alert-danger'>$langNoGuest</div>";
    draw($tool_content, 2);
}
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
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } else {
        if (isset($_GET['id'])) {
            $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));
        } else {
            $tool_content .= action_bar(array(
                array('title' => $langNewLTITool,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=1",
                      'icon' => 'fa-plus-circle',
                      'button-class' => 'btn-success',
                      'level' => 'primary-label',
                      'show' => 1)));
        }
    }
}

if (isset($_GET['add'])) {
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBBB);    
    new_lti_app();
}
elseif(isset($_POST['update_lti_app']))
{
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    add_update_lti_app($_POST['title'], $_POST['desc'], $_POST['lti_url'], $_POST['lti_key'], $_POST['lti_secret'], $_POST['status'],'true',getDirectReference($_GET['id']));
    Session::Messages($langLTIAppAddSuccessful, 'alert-success');
    redirect("index.php?course=$course_code");
}
elseif(isset($_GET['choice']))
{
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langBBB);
    switch($_GET['choice'])
    {
        case 'edit':
            edit_lti_app(getDirectReference($_GET['id']));
            break;
        case 'do_delete':
            delete_lti_app(getDirectReference($_GET['id']));
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
        add_update_lti_app($_POST['title'], $_POST['desc'], $_POST['lti_url'], $_POST['lti_key'], $_POST['lti_secret'], $_POST['status']);
    Session::Messages($langLTIAppAddSuccessful, 'alert-success');
    redirect("index.php?course=$course_code");        
}
else {
    lti_app_details();
}
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
