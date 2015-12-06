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

$shib_vars = array(
    'uname' => '',
    'email' => '',
    'cn' => '',
    'surname' => '',
    'givenname' => '',
    'studentid' => '');

$secureIndexPath = $webDir . '/secure/index.php';
if (is_readable($secureIndexPath)) {
    $shib_index = file_get_contents($secureIndexPath);
    while (preg_match('/\[[^]]*shib_(\w+)[^=]+=\s*@?([^;]+)\s*;/', $shib_index, $matches)) {
        $shib_vars[$matches[1]] = $matches[2];
        $shib_index = substr($shib_index, strlen($matches[0]));
    }
}
if (isset($shib_vars['shib_nom']) and !isset($shib_vars['shib_cn'])) {
    $shib_vars['shib_cn'] = $shib_vars['shib_nom'];
}

$r = Database::get()->querySingle("SELECT auth_settings, auth_instructions, auth_title FROM auth WHERE auth_id = 6");
$shibsettings = $r->auth_settings;
$auth_instructions = $r->auth_instructions;
$auth_title = $r->auth_title;
if ($shibsettings != 'shibboleth' and $shibsettings != '') {
    $shibseparator = $shibsettings;
    $checkedshib = 'checked';
} else {
    $checkedshib = $shibseparator = '';
}
$tool_content .= sprintf("<div class='alert alert-info'>$langExplainShib</div>",
    '<em>' . $secureIndexPath . '</em>') . "
    <div class='form-group'>
        <label for='dbfieldpass' class='col-sm-2 control-label'>$langShibEmail:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shib_email' id='shib_email' type='text' value='" . q($shib_vars['email']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='shibuname' class='col-sm-2 control-label'>$langShibUsername:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shib_uname' nid='shib_uname' type='text' value='" . q($shib_vars['uname']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='shibcn' class='col-sm-2 control-label'>$langShibCn:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shib_cn' id='shib_cn' type='text' value='" . q($shib_vars['cn']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='shibcn' class='col-sm-2 control-label'>$langShibSurname:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shib_surname' id='shib_surname' type='text' value='" . q($shib_vars['surname']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='shibcn' class='col-sm-2 control-label'>$langShibGivenname:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shib_givenname' id='shib_givenname' type='text' value='" . q($shib_vars['givenname']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='shibcn' class='col-sm-2 control-label'>$langShibStudentId:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shib_studentid' id='shib_studentid' type='text' value='" . q($shib_vars['studentid']) . "'>
        </div>
    </div>
    <div class='form-group form-inline'>
       <div class='col-sm-10 col-sm-offset-2'>
           <div class='checkbox'>
             <label>
                  <input type='checkbox' name='checkseparator' value='on' $checkedshib />&nbsp;$langCharSeparator&nbsp;
                  <input class='form-control' name='shibseparator' type='text' size='1' maxlength='2' value='" . q($shibseparator) . "' />
             </label>
           </div>
       </div>
    </div>" .
    eclass_auth_form($auth_title, $auth_instructions);
