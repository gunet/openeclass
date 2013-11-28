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


$pop3data = $auth_data;

if (!empty($pop3data)) {
    $pop3settings = $pop3data['auth_settings'];
    $auth_instructions = $pop3data['auth_instructions'];
    $pop3host = str_replace("pop3host=", "", $pop3settings);
} else {
    $pop3settings = $pop3data['auth_settings'];
    $auth_instructions = $pop3data['auth_instructions'];
    $pop3host = $pop3settings;
}

$pop3host = isset($_POST['pop3host']) ? $_POST['pop3host'] : $pop3host;
$auth_instructions = isset($_POST['auth_instructions']) ? $_POST['auth_instructions'] : $auth_instructions;

$tool_content .= "
    <tr>
      <th class='left'>$langpop3host:</th>
      <td><input class='FormData_InputText' name='pop3host' type='text' size='30' value='" . q($pop3host) . "' /></td>
    </tr>
    <tr>
      <th class='left'>$langpop3port:</th>
      <td>110</td>
    </tr>
    <tr>
      <th class='left'>$langInstructionsAuth:</th>
      <td><textarea class='FormData_InputText' name='auth_instructions' cols='30' rows='10'>" . q($auth_instructions) . "</textarea></td>
    </tr>
";
