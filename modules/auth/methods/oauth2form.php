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

if (!isset($auth_data)) {
    die;
}

if (!$auth_data) {
    $auth_data = [
        'apiBaseUrl' => '',
        'id' => '',
        'secret' => '',
        'authorizePath' => '/authorize',
        'accessTokenPath' => '/accessToken',
        'profileMethod' => 'profile',
        'casusermailattr' => '',
        'casusermailattr' => 'mail',
        'casuserfirstattr' => 'givenName',
        'casuserlastattr' => 'sn',
    ];
}

$tool_content .= "
    <div class='form-group'>
        <label for='apiBaseUrl' class='col-sm-2 control-label'>API Base URL:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='apiBaseUrl' id='apiBaseUrl' type='text' value='" . q($auth_data['apiBaseUrl']) . "' placeholder='https://sso.example.com/oauth2.0/'>
        </div>
    </div>
    <div class='form-group'>
        <label for='apiID' class='col-sm-2 control-label'>Application ID:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='apiID' id='apiID' type='text' value='" . q($auth_data['id']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='apiSecret' class='col-sm-2 control-label'>Application Secret:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='apiSecret' id='apiSecret' type='text' value='" . q($auth_data['secret']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='authorizePath' class='col-sm-2 control-label'>Authorize Path:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='authorizePath' id='authorizePath' type='text' value='" . q($auth_data['authorizePath']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='profileMethod' class='col-sm-2 control-label'>Profile Get Method:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='profileMethod' id='profileMethod' type='text' value='" . q($auth_data['profileMethod']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='accessTokenPath' class='col-sm-2 control-label'>Access Token Path:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='accessTokenPath' id='accessTokenPath' type='text' value='" . q($auth_data['accessTokenPath']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='casusermailattr' class='col-sm-2 control-label'>$langSSOMailAttr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casusermailattr' id='casusermailattr' type='text' value='" . q($auth_data['casusermailattr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='casuserfirstattr' class='col-sm-2 control-label'>$langSSOGivenNameAttr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserfirstattr' id='casuserfirstattr' type='text' value='" . q($auth_data['casuserfirstattr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='casuserlastattr' class='col-sm-2 control-label'>$langSSOSurnameAttr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserlastattr' id='casuserlastattr' type='text' value='" . q($auth_data['casuserlastattr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='casuserstudentid' class='col-sm-2 control-label'>$langSSOStudentIDAttr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserstudentid' id='casuserstudentid' type='text' value='" . q($auth_data['casuserstudentid']) . "'>
        </div>
    </div>" .
    eclass_auth_form($auth_data['auth_title'], $auth_data['auth_instructions']);
