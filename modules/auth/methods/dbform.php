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
    $auth_data['dbhost'] = '';
    $auth_data['dbname'] = '';
    $auth_data['dbuser'] = '';
    $auth_data['dbpass'] = '';
    $auth_data['dbtable'] = '';
    $auth_data['dbfielduser'] = '';
    $auth_data['dbfieldpass'] = '';
    $auth_data['dbpassencr'] = 'none';
}

$dbpassencr_data = array(
    'none' => 'Plain Text',
    'md5' => 'MD5',
    'ehasher' => 'Eclass Hasher');

$tool_content .= "
    <div class='form-group'>
        <label for='dbhost' class='col-sm-2 control-label'>$langdbhost:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbhost' id='dbhost' type='text' value='" . q($auth_data['dbhost']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbname' class='col-sm-2 control-label'>$langdbname:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbname' id='dbname' type='text' value='" . q($auth_data['dbname']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbuser' class='col-sm-2 control-label'>$langdbuser:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbuser' id='dbuser' type='text' value='" . q($auth_data['dbuser']) . "' autocomplete='off'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbpass' class='col-sm-2 control-label'>$langdbpass:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbpass' id='dbpass' type='password' value='" . q($auth_data['dbpass']) . "' autocomplete='off'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbtable' class='col-sm-2 control-label'>$langdbtable:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbtable' id='dbtable' type='text' value='" . q($auth_data['dbtable']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbfielduser' class='col-sm-2 control-label'>$langdbfielduser:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbfielduser' id='dbfielduser' type='text' value='" . q($auth_data['dbfielduser']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbfieldpass' class='col-sm-2 control-label'>$langdbfieldpass:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbfieldpass' id='dbfieldpass' type='text' value='" . q($auth_data['dbfieldpass']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbpassencr' class='col-sm-2 control-label'>$langdbpassencr:</label>
        <div class='col-sm-10'>" . selection($dbpassencr_data, 'dbpassencr', $auth_data['dbpassencr']) . "
        </div>
    </div>" .
    eclass_auth_form($auth_data['auth_title'], $auth_data['auth_instructions']);

