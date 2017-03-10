<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */


if (!method_exists('phpCAS', 'setDebug')) {
    $tool_content .= "<div class='alert alert-danger'>$langCASNotWork</div>";
}

if (!empty($auth_data['auth_settings'])) {
    $cas_port = $auth_data['cas_port'];
    if (empty($cas_port)) {
        $cas_port = 443;
    }
} else {
    $auth_data['cas_host'] = $auth_data['cas_context'] =
    $auth_data['cas_logout'] = $auth_data['cas_ssout'] =
    $auth_data['cas_cachain'] = $auth_data['casusermailattr'] =
    $auth_data['casusermailattr'] = '';
    $cas_port = 443;
    $auth_data['casusermailattr'] = 'mail';
    $auth_data['casuserfirstattr'] = 'givenName';
    $auth_data['casuserlastattr'] = 'sn';
    $auth_data['cas_altauth'] = 0;
    $auth_data['cas_altauth_use'] = 'mobile';
}

$cas_ssout_data = array(0 => $m['no'], 1 => $m['yes']);

$cas_altauth_data = array(
    0 => '-',
    1 => 'eClass',
    2 => 'POP3',
    3 => 'IMAP',
    4 => 'LDAP',
    5 => 'External DB');

$cas_altauth_use_data = array('mobile' => $langcas_altauth_use_mobile, 'all' => $langcas_altauth_use_all);
if (!isset($auth_data['cas_altauth_use'])) {
    $auth_data['cas_altauth_use'] = 'mobile';
}

$tool_content .= "
    <div class='form-group'>
        <label for='cas_host' class='col-sm-2 control-label'>$langcas_host:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_host' id='cas_host' type='text' value='" . q($auth_data['cas_host']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_port' class='col-sm-2 control-label'>$langcas_port:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_port' id='cas_port' type='text' value='" . q($cas_port) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_context' class='col-sm-2 control-label'>$langcas_context:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_context' id='cas_context' type='text' value='" . q($auth_data['cas_context']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_logout' class='col-sm-2 control-label'>$langcas_logout:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_logout' id='cas_logout' type='text' value='" . q($auth_data['cas_logout']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_logout' class='col-sm-2 control-label'>$langcas_ssout:</label>
        <div class='col-sm-10'>
            ". selection($cas_ssout_data, 'cas_ssout', $auth_data['cas_ssout'], 'class="form-control"') ."
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_cachain' class='col-sm-2 control-label'>$langcas_cachain:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_cachain' id='cas_cachain' type='text' value='" . q($auth_data['cas_cachain']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='casusermailattr' class='col-sm-2 control-label'>$langcasusermailattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casusermailattr' id='casusermailattr' type='text' value='" . q($auth_data['casusermailattr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='casuserfirstattr' class='col-sm-2 control-label'>$langcasuserfirstattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserfirstattr' id='casuserfirstattr' type='text' value='" . q($auth_data['casuserfirstattr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='casuserlastattr' class='col-sm-2 control-label'>$langcasuserlastattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserlastattr' id='casuserlastattr' type='text' value='" . q($auth_data['casuserlastattr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='casuserstudentid' class='col-sm-2 control-label'>$langcasuserstudentid:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserstudentid' id='casuserstudentid' type='text' value='" . q($auth_data['casuserstudentid']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_altauth' class='col-sm-2 control-label'>$langcas_altauth:</label>
        <div class='col-sm-10'>
            ". selection($cas_altauth_data, 'cas_altauth', $auth_data['cas_altauth'], 'class="form-control"') ."
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_altauth_use' class='col-sm-2 control-label'>$langcas_altauth_use:</label>
        <div class='col-sm-10'>
            ". selection($cas_altauth_use_data, 'cas_altauth_use', $auth_data['cas_altauth_use'], 'class="form-control"') ."
        </div>
    </div>" .
    eclass_auth_form($auth_data['auth_title'], $auth_data['auth_instructions']);
