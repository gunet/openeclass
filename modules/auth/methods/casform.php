<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
    $tool_content .= "<p align='center'><font color='red'><strong>$langCASNotWork.</strong></font></p>";
}
$casdata = $auth_data;

$cassettings = $casdata['auth_settings'];
$auth_instructions = $casdata['auth_instructions'];

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

$tool_content .= "
    <tr>
      <th class='left'>$langcas_host:</th>
      <td><input class='FormData_InputText' name='cas_host' type='text' size='30' value='" . q($cas_host) . "'></td>
    </tr>
    <tr>
      <th class='left'>$langcas_port:</th>
      <td><input class='FormData_InputText' name='cas_port' type='text' size='30' value='" . q($cas_port) . "'></td>
    </tr>
    <tr>
      <th class='left'>$langcas_context:</th>
      <td><input class='FormData_InputText' name='cas_context' type='text' size='30' value='" . q($cas_context) . "'></td>
    </tr>
    <tr>
      <th class='left'>$langcas_logout:</th>
      <td><input class='FormData_InputText' name='cas_logout' type='text' size='30' value='" . q($cas_logout) . "'></td>
    </tr>
    <tr>
      <th class='left'>$langcas_ssout:</th>
      <td>";
$cas_ssout_data = array();
$cas_ssout_data[0] = $m['no'];
$cas_ssout_data[1] = $m['yes'];
$tool_content .= selection($cas_ssout_data, 'cas_ssout', $cas_ssout);
$tool_content .= "    </td>
    </tr>
    <tr>
      <th class='left'>$langcas_cachain:</th>
      <td><input class='FormData_InputText' name='cas_cachain' type='text' size='40' value='" . q($cas_cachain) . "'></td>
    </tr>
    <tr>
      <th class='left'>$langcasusermailattr:</th>
      <td><input class='FormData_InputText' name='casusermailattr' type='text' size='30' value='" . q($casusermailattr) . "'></td>
    </tr>
    <tr>
      <th class='left'>$langcasuserfirstattr:</th>
      <td><input class='FormData_InputText' name='casuserfirstattr' type='text' size='30' value='" . q($casuserfirstattr) . "'></td>
    </tr>
    <tr>
      <th class='left'>$langcasuserlastattr:</th>
      <td><input class='FormData_InputText' name='casuserlastattr' type='text' size='30' value='" . q($casuserlastattr) . "'></td>
    </tr>
    <tr>
      <th class='left'>$langcas_altauth:</th>
      <td>";

$cas_altauth_data = array();
$cas_altauth_data[0] = '-';
$cas_altauth_data[1] = 'eClass';
$cas_altauth_data[2] = 'POP3';
$cas_altauth_data[3] = 'IMAP';
$cas_altauth_data[4] = 'LDAP';
$cas_altauth_data[5] = 'External DB';
$tool_content .= selection($cas_altauth_data, 'cas_altauth', $cas_altauth);
$tool_content .= "    </td>
    </tr>
    <tr>
      <th class='left'>$langInstructionsAuth:</th>
      <td><textarea class='FormData_InputText' name='auth_instructions' cols='30' rows='10'>" . q($auth_instructions) . "</textarea></td>
    </tr>";
