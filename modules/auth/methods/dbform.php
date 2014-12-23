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


$dbdata = $auth_data;

if (!empty($dbdata)) {
    $dbsettings = $dbdata['auth_settings'];
    $auth_instructions = $dbdata['auth_instructions'];
    $edb = empty($dbsettings) ? '' : explode('|', $dbsettings);
    if (!empty($edb)) {
        $dbhost = str_replace('dbhost=', '', $edb[0]);
        $dbname = str_replace('dbname=', '', $edb[1]);
        $dbuser = str_replace('dbuser=', '', $edb[2]);
        $dbpass = str_replace('dbpass=', '', $edb[3]);
        $dbtable = str_replace('dbtable=', '', $edb[4]);
        $dbfielduser = str_replace('dbfielduser=', '', $edb[5]);
        $dbfieldpass = str_replace('dbfieldpass=', '', $edb[6]);
        $dbpassencr = str_replace('dbpassencr=', '', $edb[7]);
    } else {
        $dbhost = '';
        $dbname = '';
        $dbuser = '';
        $dbpass = '';
        $dbtable = '';
        $dbfielduser = '';
        $dbfieldpass = '';
        $dbpassencr = '';
    }
} else {
    $dbsettings = $dbdata['auth_settings'];
    $auth_instructions = $dbdata['auth_instructions'];
    $dbhost = $dbsettings;
}

$tool_content .= "
    <div class='form-group'>
        <label for='dbhost' class='col-sm-2 control-label'>$langdbhost:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbhost' id='dbhost' type='text' value='" . q($dbhost) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbname' class='col-sm-2 control-label'>$langdbname:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbname' id='dbname' type='text' value='" . q($dbname) . "'>
        </div>
    </div>      
    <div class='form-group'>
        <label for='dbuser' class='col-sm-2 control-label'>$langdbuser:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbuser' id='dbuser' type='text' value='" . q($dbuser) . "' autocomplete='off'>        
        </div>
    </div>  
    <div class='form-group'>
        <label for='dbpass' class='col-sm-2 control-label'>$langdbpass:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbpass' id='dbpass' type='password' value='" . q($dbpass) . "' autocomplete='off'>        
        </div>
    </div>  
    <div class='form-group'>
        <label for='dbtable' class='col-sm-2 control-label'>$langdbtable:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbtable' id='dbtable' type='text' value='" . q($dbtable) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbfielduser' class='col-sm-2 control-label'>$langdbfielduser:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbfielduser' id='dbfielduser' type='text' value='" . q($dbfielduser) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbfieldpass' class='col-sm-2 control-label'>$langdbfieldpass:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='dbfieldpass' id='dbfieldpass' type='text' value='" . q($dbfieldpass) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='dbpassencr' class='col-sm-2 control-label'>$langdbpassencr:</label>
        <div class='col-sm-10'>
            <select class='form-control' name='dbpassencr' id='dbpassencr'>
                    <option value='none'>Plain Text</option>
                    <option value='md5'>MD5</option>
                    <option value='ehasher'>Eclass Hasher</option>
            </select>
        </div>
    </div>    
    <div class='form-group'>
        <label for='auth_instructions' class='col-sm-2 control-label'>$langInstructionsAuth:</label>
        <div class='col-sm-10'>
            <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>" . q($auth_instructions) . "</textarea>
        </div>
    </div>";

if($dbpassencr != ""){
    $search = "$dbpassencr'";
    $replace = "$dbpassencr' Selected";
    $tool_content = str_replace($search, $replace, $tool_content);
}
