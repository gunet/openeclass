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
$casdata = $auth_data;

$cassettings = $casdata['auth_settings'];
$auth_instructions = $casdata['auth_instructions'];
$auth_title = $casdata['auth_title'];

if (!empty($cassettings)) {
    $cas = explode('|', $cassettings);
    $cas_host = str_replace('cas_host=', '', $cas[0]);
    $cas_port = str_replace('cas_port=', '', $cas[1]);
    if (empty($cas_port)) {
        $cas_port = 443;
    }
    $cas_context = str_replace('cas_context=', '', $cas[2]);
    $cas_cachain = str_replace('cas_cachain=', '', $cas[3]);
    $casusermailattr = str_replace('casusermailattr=', '', $cas[4]);
    $casuserfirstattr = str_replace('casuserfirstattr=', '', $cas[5]);
    $casuserlastattr = str_replace('casuserlastattr=', '', $cas[6]);
    $cas_altauth = intval(str_replace('cas_altauth=', '', $cas[7]));
    $cas_logout = str_replace('cas_logout=', '', $cas[8]);
    $cas_ssout = str_replace('cas_ssout=', '', $cas[9]);
} else {
    $cas_host = '';
    $cas_port = 443;
    $cas_context = '';
    $cas_logout = '';
    $cas_ssout = '';
    $cas_cachain = '';
    $casusermailattr = 'mail';
    // givenName is the default for LDAP not givename
    $casuserfirstattr = 'givenName';
    $casuserlastattr = 'sn';
    $cas_altauth = 0;
}
$cas_ssout_data = array();
$cas_ssout_data[0] = $m['no'];
$cas_ssout_data[1] = $m['yes'];


$cas_altauth_data = array();
$cas_altauth_data[0] = '-';
$cas_altauth_data[1] = 'eClass';
$cas_altauth_data[2] = 'POP3';
$cas_altauth_data[3] = 'IMAP';
$cas_altauth_data[4] = 'LDAP';
$cas_altauth_data[5] = 'External DB';

$tool_content .= "
    <div class='form-group'>
        <label for='cas_host' class='col-sm-2 control-label'>$langcas_host:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_host' id='cas_host' type='text' value='" . q($cas_host) . "'>
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
            <input class='form-control' name='cas_context' id='cas_context' type='text' value='" . q($cas_context) . "'>
        </div>
    </div> 
    <div class='form-group'>
        <label for='cas_logout' class='col-sm-2 control-label'>$langcas_logout:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_logout' id='cas_logout' type='text' value='" . q($cas_logout) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_logout' class='col-sm-2 control-label'>$langcas_ssout:</label>
        <div class='col-sm-10'>
            ". selection($cas_ssout_data, 'cas_ssout', $cas_ssout, 'class="form-control"') ."
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_cachain' class='col-sm-2 control-label'>$langcas_cachain:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='cas_cachain' id='cas_cachain' type='text' value='" . q($cas_cachain) . "'>
        </div>
    </div>  
    <div class='form-group'>
        <label for='casusermailattr' class='col-sm-2 control-label'>$langcasusermailattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casusermailattr' id='casusermailattr' type='text' value='" . q($casusermailattr) . "'>
        </div>
    </div>       
    <div class='form-group'>
        <label for='casuserfirstattr' class='col-sm-2 control-label'>$langcasuserfirstattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserfirstattr' id='casuserfirstattr' type='text' value='" . q($casuserfirstattr) . "'>
        </div>
    </div> 
    <div class='form-group'>
        <label for='casuserlastattr' class='col-sm-2 control-label'>$langcasuserlastattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='casuserlastattr' id='casuserlastattr' type='text' value='" . q($casuserlastattr) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='cas_altauth' class='col-sm-2 control-label'>$langcas_altauth:</label>
        <div class='col-sm-10'>
            ". selection($cas_altauth_data, 'cas_altauth', $cas_altauth, 'class="form-control"') ."
        </div>
    </div>
    <div class='form-group'>
        <label for='auth_title' class='col-sm-2 control-label'>$langAuthTitle:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='auth_title' id='auth_title' type='text' value='" . q($auth_title) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='auth_instructions' class='col-sm-2 control-label'>$langInstructionsAuth:</label>
        <div class='col-sm-10'>
            <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>" . q($auth_instructions) . "</textarea>
        </div>
    </div>";
