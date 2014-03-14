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
    <tr>
      <th class='left'>$langdbhost:</th>
      <td><input class='FormData_InputText' name='dbhost' type='text' size='30' value='" . q($dbhost) . "' /></td>
    </tr>
    <tr>
      <th class='left'>$langdbname:</th>
      <td><input class='FormData_InputText' name='dbname' type='text' size='30' value='" . q($dbname) . "' /></td>
    </tr>
    <tr>
      <th class='left'>$langdbuser:</th>
    <td><input class='FormData_InputText' name='dbuser' type='text' size='30' value='" . q($dbuser) . "' autocomplete='off' /></td>
    </tr>
    <tr>
      <th class='left'>$langdbpass:</th>
      <td><input class='FormData_InputText' name='dbpass' type='password' size='30' value='" . q($dbpass) . "' autocomplete='off' /></td>
    </tr>
    <tr>
      <th class='left'>$langdbtable:</th>
      <td><input class='FormData_InputText' name='dbtable' type='text' size='30' value='" . q($dbtable) . "' /></td>
    </tr>
    <tr>
      <th class='left'>$langdbfielduser:</th>
      <td><input class='FormData_InputText' name='dbfielduser' type='text' size='30' value='" . q($dbfielduser) . "' /></td>
    </tr>
    <tr>
      <th class='left'>$langdbfieldpass:</th>
    <td><input class='FormData_InputText' name='dbfieldpass' type='text' size='30' value='" . q($dbfieldpass) . "' /></td>
    </tr>
    <tr>
      <th class='left'>$langdbpassencr:</th>
    	<td><select name='dbpassencr'>
    		<option value='none'>Plain Text</option>
			<option value='md5'>MD5</option>
			<option value='ehasher'>Eclass Hasher</option>
    	</select></td>
    </tr>
    <tr>
      <th class='left'>$langInstructionsAuth:</th>
      <td><textarea class='FormData_InputText' name='auth_instructions' cols='30' rows='10'>" . q($auth_instructions) . "</textarea></td>
    </tr>
";

if($dbpassencr != ""){
    $search = "$dbpassencr'";
    $replace = "$dbpassencr' Selected";
    $tool_content = str_replace($search, $replace, $tool_content);
}
