<?php

/* ========================================================================
 * Open eClass 3.3
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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

if (empty($auth_data['auth_settings'])) {
    $auth_data['imaphost'] = '';
}

$tool_content .= "
    <div class='form-group'>
        <label for='imaphost' class='col-sm-2 control-label'>$langimaphost:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='imaphost' id='imaphost' type='text' value='" . q($auth_data['imaphost']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='imaport' class='col-sm-2 control-label'>$langimapport:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='imaport' id='imaport' type='text' value='143' disabled>
        </div>
    </div>" .
    eclass_auth_form($auth_data['auth_title'], $auth_data['auth_instructions']);

