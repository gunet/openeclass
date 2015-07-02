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

$secureIndexPath = $webDir . '/secure/index.php';
if ($f = @fopen($secureIndexPath, 'r')) {
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
$tool_content .= sprintf("<div class='alert alert-info'>$langExplainShib</div>", '<em>' . $secureIndexPath . '</em>');
$tool_content .= "
    <div class='form-group'>
        <label for='dbfieldpass' class='col-sm-2 control-label'>$langShibEmail:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shibemail' id='shibemail' type='text' value='" . q($shibemail) . "'>
        </div>
    </div>    
    <div class='form-group'>
        <label for='shibuname' class='col-sm-2 control-label'>$langShibUsername:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shibuname' nid='shibuname' type='text' value='" . q($shibuname) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='shibcn' class='col-sm-2 control-label'>$langShibCn:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='shibcn' id='shibcn' type='text' value='" . q($shibcn) . "'>
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
    </div>   
    <div class='form-group'>
        <label for='auth_instructions' class='col-sm-2 control-label'>$langInstructionsAuth:</label>
        <div class='col-sm-10'>
            <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>" . q($auth_instructions) . "</textarea>
        </div>
    </div>";
