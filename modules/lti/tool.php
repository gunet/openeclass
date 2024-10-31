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
require_once 'modules/auth/auth.inc.php';
require_once 'modules/lti/classes/LtiEnrolHelper.php';
require_once 'modules/lti/classes/LtiEnrolDataConnector.php';
require_once 'modules/lti/classes/LtiToolProvider.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/ToolConsumer.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/ToolProvider.php';
require_once 'modules/lti/ltiprovider/src/OAuth/OAuthDataStore.php';
require_once 'modules/lti/ltiprovider/src/OAuth/OAuthServer.php';
require_once 'modules/lti/ltiprovider/src/OAuth/OAuthSignatureMethod.php';
require_once 'modules/lti/ltiprovider/src/OAuth/OAuthSignatureMethod_HMAC_SHA1.php';
require_once 'modules/lti/ltiprovider/src/OAuth/OAuthRequest.php';
require_once 'modules/lti/ltiprovider/src/OAuth/OAuthUtil.php';
require_once 'modules/lti/ltiprovider/src/OAuth/OAuthConsumer.php';
require_once 'modules/lti/ltiprovider/src/OAuth/OAuthToken.php';
require_once 'modules/lti/ltiprovider/src/ToolProvider/OAuthDataStore.php';
require_once 'modules/admin/extconfig/ltipublishapp.php';

use IMSGlobal\LTI\ToolProvider\ToolConsumer;
use IMSGlobal\LTI\ToolProvider\ToolProvider;

// require the tool id
$toolid = (isset($_GET['id'])) ? $_GET['id'] : NULL;
if ($toolid === NULL) {
    LtiEnrolHelper::draw_popup_error('Invalid Tool Id');
}

// Get the tool.
$tool = LtiEnrolHelper::get_lti_tool($toolid);
if (!$tool) {
    LtiEnrolHelper::draw_popup_error('Invalid Tool');
}

// check if LTI Provider is enabled (global config) and available for the proper course
$ltipublishapp = ExtAppManager::getApp('ltipublish');
if (!$ltipublishapp->isEnabledForCourse($tool->course_id)) {
    LtiEnrolHelper::draw_popup_error('Tool is not enabled for course');
}

// require the consumer key
$consumerkey = (isset($_POST['oauth_consumer_key'])) ? $_POST['oauth_consumer_key'] : NULL;
if ($consumerkey === NULL) {
    LtiEnrolHelper::draw_popup_error('Invalid Consumer');
}

// LTI version
$ltiversion = (isset($_POST['lti_version'])) ? $_POST['lti_version'] : NULL;

// Only accept valid launch requests
$messagetype = (isset($_POST['lti_message_type'])) ? $_POST['lti_message_type'] : NULL;
if ($messagetype === NULL || $messagetype != "basic-lti-launch-request") {
    LtiEnrolHelper::draw_popup_error('Invalid Request');
}

// Initialise tool provider.
$toolprovider = new LtiToolProvider($toolid);

// Special handling for LTIv1 launch requests.
if ($ltiversion === ToolProvider::LTI_VERSION1) {
    // Consumer details
    $consumername = (isset($_POST['tool_consumer_instance_name'])) ? $_POST['tool_consumer_instance_name'] : '';
    $consumerguid = (isset($_POST['tool_consumer_instance_guid'])) ? $_POST['tool_consumer_instance_guid'] : NULL;
    $consumerversion = (isset($_POST['tool_consumer_info_version'])) ? $_POST['tool_consumer_info_version'] : NULL;

    $dataconnector = new LtiEnrolDataConnector();
    $consumer = new ToolConsumer($consumerkey, $dataconnector);
    // Check if the consumer has already been registered to the enrol_lti_lti2_consumer table. Register if necessary.
    $consumer->ltiVersion = ToolProvider::LTI_VERSION1;
    // For LTIv1, set the tool secret as the consumer secret.
    $consumer->secret = $tool->lti_provider_secret;
    $consumer->name = $consumername;
    $consumer->consumerName = $consumer->name;
    $consumer->consumerGuid = $consumerguid;
    $consumer->consumerVersion = $consumerversion;
    $consumer->enabled = true;
    $consumer->protected = true;
    $consumer->save();

    // Set consumer to tool provider.
    $toolprovider->consumer = $consumer;
}

// Handle the request.
$toolprovider->handleRequest();

draw_popup();
