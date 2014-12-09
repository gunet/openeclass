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

$imapdata = $auth_data;

if (!empty($imapdata)) {
    $imapsettings = $imapdata['auth_settings'];
    $auth_instructions = $imapdata['auth_instructions'];
    $imaphost = str_replace('imaphost=', '', $imapsettings);
} else {
    $imapsettings = $imapdata['auth_settings'];
    $auth_instructions = $imapdata['auth_instructions'];
    $imaphost = $imapsettings;
}

$imaphost = isset($_POST['imaphost']) ? $_POST['imaphost'] : $imaphost;
$auth_instructions = isset($_POST['auth_instructions']) ? $_POST['auth_instructions'] : $auth_instructions;

$tool_content .= "
    <div class='form-group'>
        <label for='imaphost' class='col-sm-2 control-label'>$langimaphost:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='imaphost' id='imaphost' type='text' value='" . q($imaphost) . "'>
        </div>
    </div>       
    <div class='form-group'>
        <label for='imaport' class='col-sm-2 control-label'>$langimapport:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='imaport' id='imaport' type='text' value='143' disabled>
        </div>
    </div> 
    <div class='form-group'>
        <label for='auth_instructions' class='col-sm-2 control-label'>$langInstructionsAuth:</label>
        <div class='col-sm-10'>
            <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>" . q($auth_instructions) . "</textarea>
        </div>
    </div>";
