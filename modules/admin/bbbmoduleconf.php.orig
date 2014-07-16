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

load_js('jquery');
load_js('datatables/jquery.dataTables.js');
load_js('tools.js');

    
$head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */
        $(document).ready(
            function() {
                $('#bbb_servers').dataTable(
                    {
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": "./listbbbservers.php"
                     
                }
            );    
        }
    );
/* ]]> */
</script>
EOF;

$available_themes = active_subdirs("$webDir/template", 'theme.html');

$bbb_server = isset($_GET['edit_server']) ? intval($_GET['edit_server']) : '';

if (isset($_GET['add_server']))
{
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= '<fieldset><legend>';
    $tool_content .=  $langAddBBBServer;
    $tool_content .='</legend>
    <table width="100%" align="left" class="tbl">';
    //$tool_content .= '<tr><th class="left" width="100"><b>Server id:</b></th>
    //<td class="smaller"><input class="FormData_InputText" type="text" name="id_form" />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>Hostname:</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="hostname_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>IP:</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="ip_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>Pre shared key:</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="key_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>API URL:</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="api_url_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>Max rooms:</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="max_rooms_form"  />&nbsp;(*)</td></tr>';
    $tool_content .= '<tr><th class="left" width="100"><b>Max users:</b></th>
    <td class="smaller"><input class="FormData_InputText" type="text" name="max_users_form" />&nbsp;(*)</td></tr>';
    $tool_content .= "<tr><th class='left' width='100'><b>$langBBBEnableRecordings</b></th>
            <td><input type='radio' id='recorings_off' name='enable_recordings' checked='true' value='no' />
            <label for='recorings_off'>" . $m['no'] . "</label><br />
            <input type='radio' id='recorings_on' name='enable_recordings' value='yes' />
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
    
    $tool_content .= '</table><div align="right"><input type="submit" name="submit" value="'.$langAddModify.'"></div>';

    $tool_content .= '</fieldset></form>';    
}
else if (isset($_GET['delete_server']))
{
    $id = $_GET['delete_server'] ;
    Database::get()->querySingle("DELETE FROM bbb_servers WHERE id=?d",$id);
    // Display result message
    $tool_content .= "<p class='success'>$langFileUpdatedSuccess</p>";
    // Display link to go back to index.php
    $tool_content .= "<p class='right'><a href='bbbmoduleconf.php'>$langBack</a></p>";
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
    $tool_content .= "<p class='success'>$langFileUpdatedSuccess</p>";
    // Display link to go back to index.php
    $tool_content .= "<p class='right'><a href='bbbmoduleconf.php'>$langBack</a></p>";
} // end of if($submit)
// Display config.php edit form
else {
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>";

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
			<th>Ηostname</th>
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
            $server = Database::get()->querySingle("SELECT * FROM bbb_servers WHERE id=?d",$bbb_server);
            $tool_content .= '<input class="FormData_InputText" type="hidden" name="id_form" value="'.$bbb_server.'" />';
            $tool_content .= '<tr><th class="left" width="100"><b>Hostname:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="hostname_form" value="'.$server->hostname.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>IP:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="ip_form" value="'.$server->ip.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>Pre shared key:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="key_form" value="'.$server->server_key.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>API URL:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="api_url_form" value="'.$server->api_url.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>Max rooms:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="max_rooms_form" value="'.$server->max_rooms.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>Max users:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="max_users_form" value="'.$server->max_users.'" />&nbsp;(*)</td></tr>';
            $tool_content .= "<tr><th class='left' width='100'><b>$langBBBEnableRecordings</b></th>
            <td><input type='radio' id='recorings_off' name='enable_recordings' ";
            
            if($server->enable_recordings=="no")
            {
                $tool_content .= " checked='true' ";
            }
            $tool_content .=" value='no'/>
                <label for='recorings_off'>" . $m['no'] . "</label><br />
                <input type='radio' id='recorings_on' name='enable_recordings' ";
                        if($server->enable_recordings=="yes")
            {
                $tool_content .= " checked='true' ";
            }
            $tool_content .= " value='yes' />
                <label for='recorings_on'>" . $m['yes'] . "</label></td>
            </th>";
            
            $tool_content .= "<tr><th class='left' width='100'><b>$langActivate</b></th>
            <td><input type='radio' id='enabled_false' name='enabled' ";
            
            if($server->enabled=="false")
            {
                $tool_content .= " checked='false' ";
            }
            $tool_content .=" value='false'/>
                <label for='enabled_false'>" . $m['no'] . "</label><br />
                <input type='radio' id='enabled_true' name='enabled' ";
                        if($server->enabled=="true")
            {
                $tool_content .= " checked='true' ";
            }
            $tool_content .= " value='true' />
                <label for='recorings_on'>" . $m['yes'] . "</label></td>
            </th>";
            
            $tool_content .= '<tr><th class="left" width="100"><b>'.$langBBBServerOrder.':</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="weight" value="'.$server->weight.'" /></td></tr>';
            
            $tool_content .= '</table><div align="right"><input type="submit" name="submit" value="'.$langAddModify.'"></div>';
        }
            $tool_content .= '</fieldset></form>';    
    
    // Display link to index.php
    $tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";
}

draw($tool_content, 3, null, $head_content);
