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

$toolName = $langOpenMeetingsConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('validation.js');

$available_themes = active_subdirs("$webDir/template", 'theme.html');

if (isset($_GET['add_server']) or isset($_GET['edit_server'])) {
    
    $om_id = $hostnamevalue = $portnamevalue = $adminuservalue = $adminpassvalue = $adminmodulevalue = $adminwebappvalue = $adminmaxusers = $adminmaxrooms = '' ;
    $adminenrecordings_true = "checked value='true'";
    $adminenrecordings_false = "value='false'";
    $adminactivate_true = "checked value='true'";
    $adminactivate_false = "value='false'";
    
    if (isset($_GET['edit_server'])) {
        $pageName = $langEdit;
        $om_server = $_GET['edit_server'];        
        $server = Database::get()->querySingle("SELECT * FROM om_servers WHERE id = ?d", $om_server);    
        if ($server) {
            $hostnamevalue = $server->hostname;    
            $portnamevalue = $server->port;
            $adminuservalue = $server->username;
            $adminpassvalue = $server->password;
            $adminmodulevalue = $server->module_key;
            $adminwebappvalue = $server->webapp;
            $adminmaxrooms = $server->max_rooms;
            $adminmaxusers = $server->max_users;        
            if ($server->enable_recordings == 'true') {        
                $adminenrecordings_true = "value='true' checked";
                $adminenrecordings_false = "value='false'";
            } else {            
                $adminenrecordings_true = "value='true'";
                $adminenrecordings_false = "value='false' checked ";
            }
            if ($server->enabled == 'true') {
                $adminactivate_true = "value='true' checked ";
                $adminactivate_false = "value='false'";
            } else {
                $adminactivate_true = "value='true'";
                $adminactivate_false = "value='false' checked ";
            }
            $om_id = "<input class='form-control' type = 'hidden' name = 'id_form' value='$server->id'>";
        }
    } else {
        $pageName = $langAddOpenMeetingsServer;
    }
    $toolName = $langOpenMeetingsConf;
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]", 'name' => $langOpenMeetingsConf);    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "$_SERVER[SCRIPT_NAME]",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
            
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";    
    $tool_content .= "<label for='host' class='col-sm-3 control-label'>$langOpenMeetingsServer:</label>
        <div class='col-sm-9'><input class='form-control' id='host' type='text' name='hostname_form' value='$hostnamevalue'></div>";
    $tool_content .= "</div>";    
    $tool_content .= "<div class='form-group'><label for='port_form' class='col-sm-3 control-label'>$langOpenMeetingsPort:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='port_form' name='port_form' value='$portnamevalue'></div>";
    $tool_content .= "</div>";    
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='key_form' class='col-sm-3 control-label'>$langOpenMeetingsAdminUser:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='username_form' value='$adminuservalue'></div>";
    $tool_content .= "</div>";    
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='api_url_form' class='col-sm-3 control-label'>$langOpenMeetingsAdminPass:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='password_form' value='$adminpassvalue'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='module_key_form' class='col-sm-3 control-label'>$langOpenMeetingsModuleKey:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='module_form' value='$adminmodulevalue'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='webapp_form' class='col-sm-3 control-label'>$langOpenMeetingsWebApp:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='webapp_form' value='$adminwebappvalue'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxRooms:</label>
            <div class='col-sm-9'><input class='form-control' type='text' id='max_rooms_for' name='max_rooms_form' value='$adminmaxrooms'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>
                <label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxUsers:</label>
            <div class='col-sm-9'><input class='form-control' type='text' id='max_users_form' name='max_users_form' value='$adminmaxusers'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langBBBEnableRecordings:</label>
        <div class='col-sm-9 radio'><label><input type='radio' name='enable_recordings' $adminenrecordings_true>$langYes</label></div>
        <div class='col-sm-offset-3 col-sm-9 radio'><label><input type='radio' name='enable_recordings' $adminenrecordings_false>$langNo</label></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langActivate:</label>
            <div class='col-sm-9 radio'><label><input type='radio' name='enabled' $adminactivate_true>$langYes</label></div>
            <div class='col-sm-offset-3 col-sm-9 radio'><label><input type='radio' name='enabled' $adminactivate_false>$langNo</label></div>
        </div>";
    $tool_content .= $om_id;
    $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'><input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'></div></div>";
    $tool_content .= "</fieldset></form></div>";
           
    $tool_content .='<script language="javaScript" type="text/javascript">        
                var chkValidator  = new Validator("serverForm");
                chkValidator.addValidation("hostname_form","req","' . $langBBBServerAlertHostname . '");
                chkValidator.addValidation("key_form","req","' . $langBBBServerAlertKey . '");
                chkValidator.addValidation("api_url_form","req","' . $langBBBServerAlertAPIUrl . '");
                chkValidator.addValidation("max_rooms_form","req","' . $langBBBServerAlertMaxRooms . '");
            </script>';
} else if (isset($_GET['delete_server'])) {
        $id = $_GET['delete_server'];
        Database::get()->querySingle("DELETE FROM om_servers WHERE id=?d", $id);
        // Display result message
        $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";    
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
} elseif (isset($_POST['submit'])) { // Save new config.php        
    $hostname = $_POST['hostname_form'];
    $port = $_POST['port_form'];
    $username = $_POST['username_form'];
    $password = $_POST['password_form'];
    $module = $_POST['module_form'];
    $webapp = $_POST['webapp_form'];    
    $max_rooms = $_POST['max_rooms_form'];
    $max_users = $_POST['max_users_form'];
    $enable_recordings = $_POST['enable_recordings'];
    $enabled = $_POST['enabled'];    
    
    if (isset($_POST['id_form'])) {        
        $id = $_POST['id_form'];
        Database::get()->querySingle("UPDATE om_servers SET hostname = ?s,
                port = ?s,
                username = ?s,
                password = ?s,
                module_key = ?s,
                webapp = ?s,
                enabled = ?s,
                max_rooms = ?d,
                max_users = ?d,
                enable_recordings=?s 
                WHERE id = ?d", $hostname, $port, $username, $password, $module, $webapp, $enabled, $max_rooms, $max_users, $enable_recordings, $id);
    } else {
        Database::get()->querySingle("INSERT INTO om_servers (hostname,port,username,password,module_key,webapp,enabled,max_rooms,max_users,enable_recordings) VALUES
        (?s,?s,?s,?s,?s,?s,?s,?d,?d,?s)", $hostname, $port, $username, $password, $module, $webapp,$enabled,$max_rooms,$max_users,$enable_recordings);
    }    
    // Display result message
    $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";
    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "$_SERVER[SCRIPT_NAME]",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
} else {
    //display available OpenMeetings servers
    $tool_content .= action_bar(array(
    array('title' => $langAddOpenMeetingsServer,
        'url' => "$_SERVER[SCRIPT_NAME]?add_server",
        'icon' => 'fa-plus-circle',
        'level' => 'primary-label',
        'button-class' => 'btn-success'),
    array('title' => $langBack,
        'url' => "extapp.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

    $q = Database::get()->queryArray("SELECT * FROM om_servers");
    if (count($q) > 0) {
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>
            <thead>
            <tr><th class = 'text-center'>$langOpenMeetingsServer</th>
                <th class = 'text-center'>$langOpenMeetingsPort</th>
                <th class = 'text-center'>$langOpenMeetingsAdminUser</th>
                <th class = 'text-center'>$langOpenMeetingsModuleKey</th>
                <th class = 'text-center'>$langOpenMeetingsWebApp</th>
                <th class = 'text-center'>".icon('fa-gears')."</th></tr>
            </thead>";
        foreach ($q as $srv) {
            $enabled_bbb_server = ($srv->enabled)? $langYes : $langNo;
            $tool_content .= "<tr>";
            $tool_content .= "<td>$srv->hostname</td>";
            $tool_content .= "<td>$srv->port</td>";
            $tool_content .= "<td>$srv->username</td>";
            $tool_content .= "<td>$srv->module_key</td>";
            $tool_content .= "<td>$srv->webapp</td>";
            $tool_content .= "<td class='option-btn-cell'>".action_button(array(
                                                array('title' => $langEditChange,
                                                      'url' => "$_SERVER[SCRIPT_NAME]?edit_server=$srv->id",
                                                      'icon' => 'fa-edit'),
                                                array('title' => $langDelete,
                                                      'url' => "$_SERVER[SCRIPT_NAME]?delete_server=$srv->id",
                                                      'icon' => 'fa-times',
                                                      'class' => 'delete',
                                                      'confirm' => $langConfirmDelete)
                                                ))."</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoAvailableBBBServers</div>";
    }
}

draw($tool_content, 3, null, $head_content);