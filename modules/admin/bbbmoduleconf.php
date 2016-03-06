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
require_once 'modules/bbb/functions.php';

$toolName = $langBBBConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('validation.js');

$available_themes = active_subdirs("$webDir/template", 'theme.html');

$bbb_server = isset($_GET['edit_server']) ? intval(getDirectReference($_GET['edit_server'])) : '';

if (isset($_GET['add_server'])) {
    $pageName = $langAddBBBServer;
    $toolName = $langBBBConf;
    $navigation[] = array('url' => 'bbbmoduleconf.php', 'name' => $langBBBConf);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "bbbmoduleconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='host' class='col-sm-3 control-label'>$langHost:</label>
                    <div class='col-sm-9'><input class='form-control' id='host' type='text' name='hostname_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'><label for='ip_form' class='col-sm-3 control-label'>IP:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='ip_form' name='ip_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='key_form' class='col-sm-3 control-label'>$langPresharedKey:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='key_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='api_url_form' class='col-sm-3 control-label'>API URL:</label>
            <div class='col-sm-9'><input class='form-control' type='text' id='api_url_form' name='api_url_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxRooms:</label>
            <div class='col-sm-9'><input class='form-control' type='text' id='max_rooms_for' name='max_rooms_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxUsers:</label>
            <div class='col-sm-9'><input class='form-control' type='text' id='max_users_form' name='max_users_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langBBBEnableRecordings:</label>
            <div class='col-sm-9 radio'><label><input  type='radio' id='recordings_off' name='enable_recordings' checked='true' value='false'>$langNo</label></div>
            <div class='col-sm-9 radio'><label><input  type='radio' id='recordings_on' name='enable_recordings' value='true'>$langYes</label></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langActivate:</label>
            <div class='col-sm-9 radio'><label><input  type='radio' id='enabled_false' name='enabled' checked='false' value='false'>$langNo</label></div>
            <div class='col-sm-offset-3 col-sm-9 radio'><label><input  type='radio' id='enabled_true' name='enabled' checked='true' value='true'>$langYes</label></div>
        </div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langBBBServerOrder:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='weight'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'>";
    $tool_content .=    form_buttons(array(
                            array(
                                'text' => $langSave,
                                'name' => 'submit'
                            ),
                            array(
                                'href' => 'bbbmoduleconf.php'
                            )
                        ));
    $tool_content .= "</div></div></fieldset></form></div>";

    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("serverForm");
            chkValidator.addValidation("hostname_form","req","' . $langBBBServerAlertHostname . '");
            chkValidator.addValidation("ip_form","req","' . $langBBBServerAlertIP . '");
            chkValidator.addValidation("ip_form","regexp=^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$","' . $langBBBServerAlertIP . '");
            chkValidator.addValidation("key_form","req","' . $langBBBServerAlertKey . '");
            chkValidator.addValidation("api_url_form","req","' . $langBBBServerAlertAPIUrl . '");
            chkValidator.addValidation("max_rooms_form","req","' . $langBBBServerAlertMaxRooms . '");
            chkValidator.addValidation("max_rooms_form","numeric","' . $langBBBServerAlertMaxRooms . '");
            chkValidator.addValidation("max_users_form","req","' . $langBBBServerAlertMaxUsers . '");
            chkValidator.addValidation("max_users_form","numeric","' . $langBBBServerAlertMaxUsers . '");
            chkValidator.addValidation("weight","req","' . $langBBBServerAlertOrder . '");
            chkValidator.addValidation("weight","numeric","' . $langBBBServerAlertOrder . '");
        //]]></script>';
    
} else if (isset($_GET['delete_server'])) {
    $id = getDirectReference($_GET['delete_server']);
    Database::get()->querySingle("DELETE FROM bbb_servers WHERE id=?d", $id);
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/bbbmoduleconf.php');   
}

// Save new config
else if (isset($_POST['submit'])) {
    $hostname = $_POST['hostname_form'];
    $ip = $_POST['ip_form'];
    $key = $_POST['key_form'];
    $api_url = $_POST['api_url_form'];
    if (!preg_match('/\/$/', $api_url)) { // append '/' if doesn't exist
        $api_url = $api_url . '/';
    }
    $max_rooms = $_POST['max_rooms_form'];
    $max_users = $_POST['max_users_form'];
    $enable_recordings = $_POST['enable_recordings'];
    $enabled = $_POST['enabled'];
    $weight = $_POST['weight'];

    if (isset($_POST['id_form'])) {
        $id = getDirectReference($_POST['id_form']);
        Database::get()->querySingle("UPDATE bbb_servers SET hostname = ?s,
                ip = ?s,
                server_key = ?s,
                api_url = ?s,
                max_rooms =?s,
                max_users =?s,
                enable_recordings =?s,
                enabled = ?s,
                weight = ?d
                WHERE id =?d", $hostname, $ip, $key, $api_url, $max_rooms, $max_users, $enable_recordings, $enabled, $weight, $id);
    } else {
        Database::get()->querySingle("INSERT INTO bbb_servers (hostname,ip,server_key,api_url,max_rooms,max_users,enable_recordings,enabled,weight) VALUES
        (?s,?s,?s,?s,?s,?s,?s,?s,?d)", $hostname, $ip, $key, $api_url, $max_rooms, $max_users, $enable_recordings, $enabled, $weight);
    }    
    // Display result message
    Session::Messages($langFileUpdatedSuccess,"alert-success");
    redirect_to_home_page("modules/admin/bbbmoduleconf.php");
    // Display link to go back to index.php
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "bbbmoduleconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
} // end of if($submit)
// Display config edit form
else {    
    if (isset($_GET['edit_server'])) {
        $pageName = $langEdit;
        $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "bbbmoduleconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
        
        $server = Database::get()->querySingle("SELECT * FROM bbb_servers WHERE id = ?d", $bbb_server);
        
        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<fieldset>";        
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='host' class='col-sm-3 control-label'>$langHost:</label>
                        <div class='col-sm-9'><input class='form-control' id='host' type='text' name='hostname_form' value='$server->hostname'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'><label for='ip_form' class='col-sm-3 control-label'>IP:</label>
                    <div class='col-sm-9'><input class='form-control' type='text' id='ip_form' name='ip_form' value='$server->ip'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='key_form' class='col-sm-3 control-label'>$langPresharedKey:</label>
                <div class='col-sm-9'><input class='form-control' type='text' name='key_form' value='$server->server_key'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='api_url_form' class='col-sm-3 control-label'>API URL:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='api_url_form' name='api_url_form' value='$server->api_url'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxRooms:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='max_rooms_for' name='max_rooms_form' value='$server->max_rooms'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxUsers:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='max_users_form' name='max_users_form' value='$server->max_users'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label class='col-sm-3 control-label'>$langBBBEnableRecordings:</label>";
        if ($server->enable_recordings == "false") {
            $checkedfalse = " checked='true' ";
        } else $checkedfalse = '';
        $tool_content .= "<div class='col-sm-9 radio'><label><input  type='radio' id='recordings_off' name='enable_recordings' value='false' $checkedfalse>$langNo</label></div>";
        if ($server->enable_recordings == "true") {
            $checkedtrue = " checked='true' ";
        } else $checkedtrue = '';
        $tool_content .= "<div class='col-sm-9 radio'><label><input  type='radio' id='recordings_on' name='enable_recordings' value='true' $checkedtrue>$langYes</label></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label class='col-sm-3 control-label'>$langActivate:</label>";
        if ($server->enabled == "false") {
            $checkedfalse2 = " checked='false' ";
        } else $checkedfalse2 = '';
        
        $tool_content .= "<div class='col-sm-9 radio'><label><input  type='radio' id='enabled_false' name='enabled' $checkedfalse2 value='false'>$langNo</label></div>";
        
        if ($server->enabled == "true") {
            $checkedtrue2 = " checked='false' ";
        } else $checkedtrue2 = '';
        
         $tool_content .= "<div class='col-sm-offset-3 col-sm-9 radio'><label><input  type='radio' id='enabled_true' name='enabled' $checkedtrue2 value='true'>$langYes</label></div>
            </div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label class='col-sm-3 control-label'>$langBBBServerOrder:</label>
                <div class='col-sm-9'><input class='form-control' type='text' name='weight' value='$server->weight'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<input class='form-control' type = 'hidden' name = 'id_form' value='" . getIndirectReference($bbb_server) . "'>";
        $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'><input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'></div></div>";
        $tool_content .= "</fieldset></form></div>";
        $tool_content .='<script language="javaScript" type="text/javascript">
                //<![CDATA[
                    var chkValidator  = new Validator("serverForm");
                    chkValidator.addValidation("hostname_form","req","' . $langBBBServerAlertHostname . '");
                    chkValidator.addValidation("ip_form","req","' . $langBBBServerAlertIP . '");
                    chkValidator.addValidation("ip_form","regexp=^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$","' . $langBBBServerAlertIP . '");
                    chkValidator.addValidation("key_form","req","' . $langBBBServerAlertKey . '");
                    chkValidator.addValidation("api_url_form","req","' . $langBBBServerAlertAPIUrl . '");
                    chkValidator.addValidation("max_rooms_form","req","' . $langBBBServerAlertMaxRooms . '");
                    chkValidator.addValidation("max_rooms_form","numeric","' . $langBBBServerAlertMaxRooms . '");
                    chkValidator.addValidation("max_users_form","req","' . $langBBBServerAlertMaxUsers . '");
                    chkValidator.addValidation("max_users_form","numeric","' . $langBBBServerAlertMaxUsers . '");
                    chkValidator.addValidation("weight","req","' . $langBBBServerAlertOrder . '");
                    chkValidator.addValidation("weight","numeric","' . $langBBBServerAlertOrder . '");
                //]]></script>';
                    
    } else {
        //display available BBB servers
        $tool_content .= action_bar(array(
            array('title' => $langAddBBBServer,
                'url' => "bbbmoduleconf.php?add_server",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langBack,
                'url' => "extapp.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));

        $q = Database::get()->queryArray("SELECT * FROM bbb_servers");
        if (count($q)>0) {
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                <tr><th class = 'text-center'>$langHost</th>
                    <th class = 'text-center'>IP</th>
                    <th class = 'text-center'>$langBBBEnabled</th>
                    <th class = 'text-center'>$langOnlineUsers</th>
                    <th class = 'text-center'>$langMaxRooms</th>
                    <th class = 'text-center'>$langBBBServerOrderP</th>
                    <th class = 'text-center'>".icon('fa-gears')."</th></tr>
                </thead>";
            foreach ($q as $srv) {
                $enabled_bbb_server = ($srv->enabled == 'true')? $langYes : $langNo;
                $connected_users = get_connected_users($srv->server_key, $srv->api_url, $srv->ip);
                $tool_content .= "<tr>" .
                    "<td>$srv->hostname</td>" .
                    "<td>$srv->ip</td>" .
                    "<td class = 'text-center'>$enabled_bbb_server</td>" .
                    "<td class = 'text-center'>$connected_users</td>" .
                    "<td class = 'text-center'>$srv->max_rooms</td>" .
                    "<td class = 'text-center'>$srv->weight</td>" .
                    "<td class='option-btn-cell'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                              'url' => "$_SERVER[SCRIPT_NAME]?edit_server=" . getIndirectReference($srv->id),
                              'icon' => 'fa-edit'),
                        array('title' => $langDelete,
                              'url' => "$_SERVER[SCRIPT_NAME]?delete_server=" . getIndirectReference($srv->id),
                              'icon' => 'fa-times',
                              'class' => 'delete',
                              'confirm' => $langConfirmDelete))) . "</td>" .
                    "</tr>";
            }            	
            $tool_content .= "</table></div>";
        } else {
             $tool_content .= "<div class='alert alert-warning'>$langNoAvailableBBBServers</div>";
        }
    }
}

draw($tool_content, 3, null, $head_content);
