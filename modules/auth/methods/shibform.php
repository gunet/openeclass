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


if ($f = @fopen("${webDir}secure/index.php", "r")) {
    while (!feof($f)) {
        $buffer = fgets($f, 4096);
        if (strpos($buffer, 'shib_email')) {
            $shibemail = strstr($buffer, '=');
            $shibemail = trim(substr($shibemail, 1, -2));
        }
        if (strpos($buffer, 'shib_uname')) {
            $shibuname = strstr($buffer, '=');
            $shibuname = trim(substr($shibuname, 1, -2));
        }
        if (strpos($buffer, 'shib_nom')) {
            $shibcn = strstr($buffer, '=');
            $shibcn = trim(substr($shibcn, 1, -2));
        }
    }
    fclose($f);
}

$r = Database::get()->querySingle("SELECT auth_settings, auth_instructions FROM auth WHERE auth_id = 6");
$shibsettings = $r->auth_settings;
$auth_instructions = $r->auth_instructions;
if ($shibsettings != 'shibboleth' and $shibsettings != '') {
    $shibseparator = $shibsettings;
    $checkedshib = 'checked';
} else {
    $checkedshib = $shibseparator = '';
}
$tool_content .= sprintf("<tr><td colspan='2'><div class='info'>$langExplainShib</div></td></tr>", $webDir);
$tool_content .= "
  <tr>
    <th class='left'>$langShibEmail:</th>
    <td><input class='FormData_InputText' name='shibemail' type='text' size='30' value='" . q($shibemail) . "' /></td>
  </tr>
  <tr><th class='left'>$langShibUsername:</th>
    <td><input class='FormData_InputText' name='shibuname' type='text' size='30' value='" . q($shibuname) . "' /></td>
  </tr>
  <tr>
    <th class='left' rowspan='2'>$langShibCn:</th>
    <td><input class='FormData_InputText' name='shibcn' type='text' size='30' value='" . q($shibcn) . "' /></td>
  </tr>
  <tr>
    <td bgcolor='#F8F8F8'><input type='checkbox' name='checkseparator' value='on' $checkedshib />&nbsp;$langCharSeparator&nbsp;
      <input class='FormData_InputText' name='shibseparator' type='text' size='1' maxlength='2' value='" . q($shibseparator) . "' /></td>
  </tr>
  <tr>
    <th class='left'>$langInstructionsAuth:</th>
    <td><textarea class='FormData_InputText' name='auth_instructions' cols='30' rows='10'>" . q($auth_instructions) . "</textarea></td>
  </tr>
";
