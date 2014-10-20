<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';

$nameTools = $langBBBConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

load_js('datatables');
load_js('tools.js');
load_js('validation.js');
    
$head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */
        $(document).ready(
            function() {
                $('#bbb_servers').dataTable(
                    {
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": "./listbbbservers.php",
                'oLanguage': {                       
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '$langNoResult',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                       'sInfoFiltered': '',
                       'sInfoPostFix':  '',
                       'sSearch':       '$langSearch',
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
                     
                }
            );    
        }
    );
/* ]]> */
</script>
EOF;

$available_themes = active_subdirs("$webDir/template", 'theme.html');

$bbb_server = isset($_GET['edit_server']) ? intval($_GET['edit_server']) : '';

global $langΒΒΒServerAlertHostname,$langΒΒΒServerAlertIP,$langΒΒΒServerAlertKey,$langΒΒΒServerAlertAPIUrl;
global $langΒΒΒServerAlertMaxRooms,$langΒΒΒServerAlertMaxUsers,$langΒΒΒServerAlertOrder;

if (isset($_GET['add_server']))
{
    $tool_content .= "<form name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= '<fieldset><legend>';
    $tool_content .=  $langAddBBBServer;
    $tool_content .='</legend>
    <table width="100%" align="left" class="tbl">';
    //$tool_content .= '<tr><th class="left" width="100"><b>Server id:</b></th>
    //<td class="smaller"><input class="FormData_InputText" type="text" name="id_form" />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>'.$langHost.':</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="hostname_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>IP:</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="ip_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>'.$langPresharedKey.':</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="key_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>API URL:</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="api_url_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>'.$langMaxRooms.':</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="max_rooms_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>'.$langMaxUsers.':</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="max_users_form" />&nbsp;(*)</td></tr>';
    $tool_content .= "<tr><th class='left' width='100'><b>$langBBBEnableRecordings</b></th>
            <td><input type='radio' id='recorings_off' name='enable_recordings' checked='true' value='false' />
            <label for='recorings_off'>" . $m['no'] . "</label><br />
            <input type='radio' id='recorings_on' name='enable_recordings' value='true' />
            <label for='recorings_on'>" . $m['yes'] . "</label></td>
        </th>";
    $tool_content .= "<tr><th class='left' width='100'><b>$langActivate</b></th>
            <td><input type='radio' id='enabled_false' name='enabled' checked='false' value='false' />
            <label for='enabled_false'>" . $m['no'] . "</label><br />
            <input type='radio' id='enabled_true' name='enabled' checked='true' value='true' />
            <label for='enabled_true'>" . $m['yes'] . "</label></td>
        </th>";
    $tool_content .= '<tr><th class="left" width="100"><b>'.$langBBBServerOrder.':</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="weight" /></td></tr>';
    
    $tool_content .= '</table><div align="right"><input class="btn btn-primary" type="submit" name="submit" value="'.$langAddModify.'"></div>';

    $tool_content .= '</fieldset></form>';
    
    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("serverForm");
            chkValidator.addValidation("hostname_form","req","'.$langΒΒΒServerAlertHostname.'");
            chkValidator.addValidation("ip_form","req","'.$langΒΒΒServerAlertIP.'");
            chkValidator.addValidation("ip_form","regexp=^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$","'.$langΒΒΒServerAlertIP.'");
            chkValidator.addValidation("key_form","req","'.$langΒΒΒServerAlertKey.'");
            chkValidator.addValidation("api_url_form","req","'.$langΒΒΒServerAlertAPIUrl.'");
            chkValidator.addValidation("max_rooms_form","req","'.$langΒΒΒServerAlertMaxRooms.'");
            chkValidator.addValidation("max_rooms_form","numeric","'.$langΒΒΒServerAlertMaxRooms.'");
            chkValidator.addValidation("max_users_form","req","'.$langΒΒΒServerAlertMaxUsers.'");
            chkValidator.addValidation("max_users_form","numeric","'.$langΒΒΒServerAlertMaxUsers.'");
            chkValidator.addValidation("weight","req","'.$langΒΒΒServerAlertOrder.'");
            chkValidator.addValidation("weight","numeric","'.$langΒΒΒServerAlertOrder.'");
        //]]></script>';
}
else if (isset($_GET['delete_server']))
{
    $id = $_GET['delete_server'] ;
    Database::get()->querySingle("DELETE FROM bbb_servers WHERE id=?d",$id);
    // Display result message
    $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";
    // Display link to go back to index.php
    $tool_content .= "<div class='alert alert-success'><a href='bbbmoduleconf.php'>$langBack</a></div>";
}
// Save new config.php
else if (isset($_POST['submit'])) {

    $hostname = $_POST['hostname_form'];
    $ip = $_POST['ip_form'];
    $key = $_POST['key_form'];
    $api_url = $_POST['api_url_form'];
    $max_rooms = $_POST['max_rooms_form'];
    $max_users = $_POST['max_users_form'];
    $enable_recordings =  $_POST['enable_recordings'] ;
    $enabled =  $_POST['enabled'] ;
    $weight = $_POST['weight'];

    if(isset($_POST['id_form'])) {
        $id = $_POST['id_form'] ;
        Database::get()->querySingle("UPDATE bbb_servers SET hostname = ?s,
                ip = ?s,
                server_key = ?s,
                api_url = ?s,
                max_rooms =?s,
                max_users =?s,
                enable_recordings =?s,
                enabled = ?s,
                weight = ?d
                WHERE id =?d",$hostname,$ip,$key,$api_url,$max_rooms,$max_users,$enable_recordings,$enabled,$weight,$id);
    }
    else
    {
        Database::get()->querySingle("INSERT INTO bbb_servers (hostname,ip,server_key,api_url,max_rooms,max_users,enable_recordings,enabled,weight) VALUES
        (?s,?s,?s,?s,?s,?s,?s,?s,?d)",$hostname,$ip,$key,$api_url,$max_rooms,$max_users,$enable_recordings,$enabled,$weight);
        
    }
    
    #register_posted_variables($config_vars, 'all', 'intval');
    //$_SESSION['theme'] = $theme = $available_themes[$theme];

    // Display result message
    $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";
    // Display link to go back to index.php
    $tool_content .= "<p class='pull-right'><a href='bbbmoduleconf.php'>$langBack</a></p>";
} // end of if($submit)
// Display config.php edit form
else {
    $tool_content .= "<form name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";

    $tool_content .= '<fieldset><legend>'.$langAvailableBBBServers.'</legend>
    <div id="operations_container">
    <ul id="opslist">
    <li>
    <a href="?add_server">'.$langAddBBBServer .'</a>
    </li>
    </ul>
    </div>
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="bbb_servers" width="100%">
	<thead>
		<tr>
			<th>'.$langHost.'</th>
			<th>IP</th>
			<th>'.$langBBBEnabled.'</th>
			<th>'.$langBBBOptions.'</th>
			<th>'.$langBBBConnectedUsers.'</th>
                        <th>'.$langBBBServerOrderP.'</th>
                        <th>'.$langBBBRemoveServer.'</th>                            
		</tr>
	</thead>
	<tbody>
	</tbody>
	<tfoot>
</table></fieldset></legend>';
    
if (isset($_GET['edit_server'])) {
    $tool_content .= '<fieldset><legend>';
    $tool_content .=  $langUpdateBBBServer;
    $tool_content .='</legend>
    <table width="100%" align="left" class="tbl">';
            $server = Database::get()->querySingle("SELECT * FROM bbb_servers WHERE id = ?d", $bbb_server);
            $tool_content .= '<input class="FormData_InputText" type="hidden" name="id_form" value="'.$bbb_server.'" />';
            $tool_content .= '<tr><th class="left" width="100"><b>'.$langHost.':</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="hostname_form" value="'.$server->hostname.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>IP:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="ip_form" value="'.$server->ip.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>'.$langPresharedKey.'</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="key_form" value="'.$server->server_key.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>API URL:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="api_url_form" value="'.$server->api_url.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>'.$langMaxRooms.':</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="max_rooms_form" value="'.$server->max_rooms.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>'.$langMaxUsers.':</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="max_users_form" value="'.$server->max_users.'" />&nbsp;(*)</td></tr>';
            $tool_content .= "<tr><th class='left' width='100'><b>$langBBBEnableRecordings</b></th>
            <td><input type='radio' id='recorings_off' name='enable_recordings' ";
            
            if($server->enable_recordings=="false")
            {
                $tool_content .= " checked='true' ";
            }
            $tool_content .=" value='false'/>
                <label for='recorings_off'>" . $langNo . "</label><br />
                <input type='radio' id='recorings_on' name='enable_recordings' ";
                        if($server->enable_recordings=="true")
            {
                $tool_content .= " checked='true' ";
            }
            $tool_content .= " value='true' />
                <label for='recorings_on'>" . $langYes . "</label></td>
            </th>";
            
            $tool_content .= "<tr><th class='left' width='100'><b>$langActivate</b></th>
            <td><input type='radio' id='enabled_false' name='enabled' ";
            
            if($server->enabled=="false")
            {
                $tool_content .= " checked='false' ";
            }
            $tool_content .=" value='false'/>
                <label for='enabled_false'>" . $langNo . "</label><br />
                <input type='radio' id='enabled_true' name='enabled' ";
                        if($server->enabled=="true")
            {
                $tool_content .= " checked='true' ";
            }
            $tool_content .= " value='true' />
                <label for='recorings_on'>" . $langYes . "</label></td>
            </th>";
            
            $tool_content .= '<tr><th class="left" width="100"><b>'.$langBBBServerOrder.':</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="weight" value="'.$server->weight.'" /></td></tr>';
            
            $tool_content .= '</table><div align="right"><input class="btn btn-primary" type="submit" name="submit" value="'.$langAddModify.'"></div>';
        }
            $tool_content .= '</fieldset></form>';    
            $tool_content .='<script language="javaScript" type="text/javascript">
                //<![CDATA[
                    var chkValidator  = new Validator("serverForm");
                    chkValidator.addValidation("hostname_form","req","'.$langΒΒΒServerAlertHostname.'");
                    chkValidator.addValidation("ip_form","req","'.$langΒΒΒServerAlertIP.'");
                    chkValidator.addValidation("ip_form","regexp=^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$","'.$langΒΒΒServerAlertIP.'");
                    chkValidator.addValidation("key_form","req","'.$langΒΒΒServerAlertKey.'");
                    chkValidator.addValidation("api_url_form","req","'.$langΒΒΒServerAlertAPIUrl.'");
                    chkValidator.addValidation("max_rooms_form","req","'.$langΒΒΒServerAlertMaxRooms.'");
                    chkValidator.addValidation("max_rooms_form","numeric","'.$langΒΒΒServerAlertMaxRooms.'");
                    chkValidator.addValidation("max_users_form","req","'.$langΒΒΒServerAlertMaxUsers.'");
                    chkValidator.addValidation("max_users_form","numeric","'.$langΒΒΒServerAlertMaxUsers.'");
                    chkValidator.addValidation("weight","req","'.$langΒΒΒServerAlertOrder.'");
                    chkValidator.addValidation("weight","numeric","'.$langΒΒΒServerAlertOrder.'");
                //]]></script>';
    // Display link to index.php
    $tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";
}

draw($tool_content, 3, null, $head_content);
