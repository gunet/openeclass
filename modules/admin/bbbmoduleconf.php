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

$head_content .="
<script type='text/javascript'>
function confirmation ()
{
        if (confirm('$langConfirmDelete'))
                {return true;}
        else
                {return false;}
}
</script>";

$available_themes = active_subdirs("$webDir/template", 'theme.html');

$bbb_server = isset($_GET['edit_server']) ? intval($_GET['edit_server']) : '';

if (isset($_GET['delete_server']))
{
    $id = $_GET['delete_server'] ;
    db_query("DELETE FROM bbb_servers WHERE id=".quote($id));
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
    
    if(isset($_POST['id_form'])) {
        $id = $_POST['id_form'] ;
        db_query("UPDATE bbb_servers SET hostname = " . quote($hostname)
            . ", ip = ". quote($ip) .""
            . ", server_key = ". quote($key).""
            . ", api_url = ". quote($api_url).""
            . ", max_rooms =". quote($max_rooms) .""
            . ", max_users =". quote($max_users) .""
            . " WHERE id =".quote($id)."");
    }
    else
    {
        db_query("INSERT INTO bbb_servers (hostname,ip,server_key,api_url,max_rooms,max_users) VALUES"
                . "(".quote($hostname).""
                . ",".quote($ip).""
                . ",".quote($key).""
                . ",".quote($api_url).""
                . ",".quote($max_rooms).""
                . ",".quote($max_users).")");
        
        
    }
    
    #register_posted_variables($config_vars, 'all', 'intval');
    $_SESSION['theme'] = $theme = $available_themes[$theme];


    // Display result message
    $tool_content .= "<p class='success'>$langFileUpdatedSuccess</p>";
    // Display link to go back to index.php
    $tool_content .= "<p class='right'><a href='bbbmoduleconf.php'>$langBack</a></p>";
} // end of if($submit)
// Display config.php edit form
else {
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>";

    $tool_content .= '<fieldset><legend>'.$langAvailableBBBServers.'</legend>
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="bbb_servers" width="100%">
	<thead>
		<tr>
			<th>Server hostname</th>
			<th>Server IP</th>
			<th>Enabled</th>
			<th>Options</th>
			<th>Connected users</th>
                        <th>Remove server</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
	<tfoot>
</table></fieldset></legend>
<fieldset><legend>';
    if($bbb_server<>'')
        { 
            $tool_content .=  $langAvailableBBBServers;
        } else
        {
            $tool_content .= "Add new BigBlueButton server";
        }
$tool_content .='</legend>
<table width="100%" align="left" class="tbl">';
    if ($bbb_server<>'') {
        $sql = db_query("SELECT * FROM bbb_servers WHERE id=$bbb_server");
        while ($server = mysql_fetch_array($sql)) {
            $tool_content .= '<tr><th class="left" width="100"><b>Server id:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="id_form" value="'.$bbb_server.'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>Hostname:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="hostname_form" value="'.q($server['hostname']).'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>IP:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="ip_form" value="'.q($server['ip']).'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>Pre shared key:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="key_form" value="'.q($server['server_key']).'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>API URL:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="api_url_form" value="'.q($server['api_url']).'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>Max rooms:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="max_rooms_form" value="'.q($server['max_rooms']).'" />&nbsp;(*)</td></tr>';
            $tool_content .= '<tr><th class="left" width="100"><b>Max users:</b></th>
            <td class="smaller"><input class="FormData_InputText" type="text" name="max_users_form" value="'.q($server['max_users']).'" />&nbsp;(*)</td></tr>';
        }
    }
    else
    {
        $tool_content .= '<tr><th class="left" width="100"><b>Hostname:</b></th>
        <td class="smaller"><input class="FormData_InputText" type="text" name="hostname_form" value="" />&nbsp;(*)</td></tr>';
        $tool_content .= '<tr><th class="left" width="100"><b>IP:</b></th>
        <td class="smaller"><input class="FormData_InputText" type="text" name="ip_form" value="" />&nbsp;(*)</td></tr>';
        $tool_content .= '<tr><th class="left" width="100"><b>Pre shared key:</b></th>
        <td class="smaller"><input class="FormData_InputText" type="text" name="key_form" value="" />&nbsp;(*)</td></tr>';
        $tool_content .= '<tr><th class="left" width="100"><b>API URL:</b></th>
        <td class="smaller"><input class="FormData_InputText" type="text" name="api_url_form" value="" />&nbsp;(*)</td></tr>';
        $tool_content .= '<tr><th class="left" width="100"><b>Max rooms:</b></th>
        <td class="smaller"><input class="FormData_InputText" type="text" name="max_rooms_form" value="" />&nbsp;(*)</td></tr>';
        $tool_content .= '<tr><th class="left" width="100"><b>Max users:</b></th>
        <td class="smaller"><input class="FormData_InputText" type="text" name="max_users_form" value="" />&nbsp;(*)</td></tr>';
    }
    
    $tool_content .= '</table></fieldset><input type="submit" name="submit" value="'.$langModify.'"></form>';
    
    // Display link to index.php
    $tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";
}

draw($tool_content, 3, null, $head_content);
