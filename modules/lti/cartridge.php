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

require_once '../../include/baseTheme.php';
require_once 'modules/lti/classes/LtiEnrolHelper.php';
require_once 'modules/admin/extconfig/ltipublishapp.php';

// Require the tool id
$toolid = (isset($_GET['id'])) ? $_GET['id'] : NULL;
if ($toolid === NULL) {
    LtiEnrolHelper::draw_popup_error('Invalid Tool Id');
}

// Require the token
$token = (isset($_GET['token'])) ? $_GET['token'] : NULL;
if ($token === NULL) {
    LtiEnrolHelper::draw_popup_error('Invalid Token');
}

// Get the tool
$tool = LtiEnrolHelper::get_lti_tool($toolid);
if (!$tool) {
    LtiEnrolHelper::draw_popup_error('Invalid Tool');
}

// Verify the token
if (!LtiEnrolHelper::verify_cartridge_token($toolid, $token)) {
    LtiEnrolHelper::draw_popup_error('Incorrect Token');
}

// check if LTI Provider is enabled (global config) and available for the proper course
$ltipublishapp = ExtAppManager::getApp('ltipublish');
if (!$ltipublishapp->isEnabledForCourse($tool->course_id)) {
    LtiEnrolHelper::draw_popup_error('Tool is not enabled for course');
}

header('Content-Type: text/xml; charset=utf-8');
echo LtiEnrolHelper::create_cartridge($tool);
