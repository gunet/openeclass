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
//require_once 'modules/openmeetings/functions.php';

$toolName = $langOpenMeetingsConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('validation.js');

$available_themes = active_subdirs("$webDir/template", 'theme.html');

$om_server = isset($_GET['edit_server']) ?  $_GET['edit_server'] : '';

if (isset($_GET['add_server'])) {
    $pageName = $$langAddOpenMeetingsServer;
    $toolName = $langOpenMeetingsConf;
    $navigation[] = array('url' => 'openmeetingsconf.php', 'name' => $langOpenMeetingsConf);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "openmeetingsconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<fieldset>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='host' class='col-sm-3 control-label'>$langOpenMeetingsServer:</label>
                    <div class='col-sm-9'><input class='form-control' id='host' type='text' name='hostname_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'><label for='ip_form' class='col-sm-3 control-label'>$langOpenMeetingsPort:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='ip_form' name='port_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='key_form' class='col-sm-3 control-label'>$langOpenMeetingsAdminUser:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='username_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='api_url_form' class='col-sm-3 control-label'>$langOpenMeetingsAdminPass:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='password_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langOpenMeetingsModuleKey:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='module_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langOpenMeetingsWebApp:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='webapp_form'></div>";
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
    $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'><input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'></div></div>";
    $tool_content .= "</fieldset></form></div>";
/*
    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("serverForm");
            chkValidator.addValidation("hostname_form","req","' . $langBBBServerAlertHostname . '");
            chkValidator.addValidation("ip_form","req","' . $langBBBServerAlertIP . '");
            chkValidator.addValidation("ip_form","regexp=^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$","' . $langBBBServerAlertIP . '");
            chkValidator.addValidation("key_form","req","' . $langBBBServerAlertKey . '");
            chkValidator.addValidation("api_url_form","req","' . $langBBBServerAlertAPIUrl . '");
            chkValidator.addValidation("max_rooms_form","req","' . $langBBBServerAlertMaxRooms . '");
        //]]></script>';
  */  
} else if (isset($_GET['delete_server'])) {
    $id = $_GET['delete_server'];
    Database::get()->querySingle("DELETE FROM om_servers WHERE id=?d", $id);
    // Display result message
    $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "openmeetingsconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
}
// Save new config.php
else if (isset($_POST['submit'])) {
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
    $weight = $_POST['weight'];
//print_r($_POST);die();
    
    if (isset($_POST['id_form'])) {
        $id = $_POST['id_form'];
        Database::get()->querySingle("UPDATE om_servers SET hostname = ?s,
                port = ?s,
                username = ?s,
                password = ?s,
                module_key =?s,
                webapp =?s,
                enabled=?s,
                max_rooms=?d,
                max_users=?d,
                enable_recordings=?s 
                WHERE id =?d", $hostname, $port, $username, $password, $module, $webapp, $enabled, $max_rooms, $max_users, $enable_recordings, $id);
    } else {
        Database::get()->querySingle("INSERT INTO om_servers (hostname,port,username,password,module_key,webapp,enabled,max_rooms,max_users,enable_recordings) VALUES
        (?s,?s,?s,?s,?s,?s,?s,?d,?d,?s)", $hostname, $port, $username, $password, $module, $webapp,$enabled,$max_rooms,$max_users,$enable_recordings);
    }    
    // Display result message
    $tool_content .= "<div class='alert alert-success'>$langFileUpdatedSuccess</div>";
    // Display link to go back to index.php
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "openmeetingsconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
} // end of if($submit)
// Display config.php edit form
else {    
    if (isset($_GET['edit_server'])) {
        $pageName = $langEdit;
        $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "openmeetingsconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
        
        $server = Database::get()->querySingle("SELECT * FROM om_servers WHERE id = ?d", $om_server);
        
        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<fieldset>";        
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='host' class='col-sm-3 control-label'>$langOpenMeetingsServer:</label>
                        <div class='col-sm-9'><input class='form-control' id='host' type='text' name='hostname_form' value='$server->hostname'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'><label for='ip_form' class='col-sm-3 control-label'>$langOpenMeetingsPort:</label>
                    <div class='col-sm-9'><input class='form-control' type='text' id='ip_form' name='port_form' value='$server->port'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='key_form' class='col-sm-3 control-label'>$langOpenMeetingsAdminUser:</label>
                <div class='col-sm-9'><input class='form-control' type='text' name='username_form' value='$server->username'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='api_url_form' class='col-sm-3 control-label'>$langOpenMeetingsAdminPass:</label>
                <div class='col-sm-9'><input class='form-control' type='text' name='password_form' value='$server->password'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='module_key_form' class='col-sm-3 control-label'>$langOpenMeetingsModuleKey:</label>
                <div class='col-sm-9'><input class='form-control' type='text' name='module_form' value='$server->module_key'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='webapp_form' class='col-sm-3 control-label'>$langOpenMeetingsWebApp:</label>
                <div class='col-sm-9'><input class='form-control' type='text' name='webapp_form' value='$server->webapp'></div>";
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
        $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxRooms:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='max_rooms_for' name='max_rooms_form' value='$server->max_rooms'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxUsers:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='max_users_form' name='max_users_form' value='$server->max_users'></div>";
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
            </div>";      $tool_content .= "<input class='form-control' type = 'hidden' name = 'id_form' value='$om_server'>";
        $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'><input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'></div></div>";
        $tool_content .= "</fieldset></form></div>";
/*        
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
  */                  
    } else {
        //display available OpenMeetings servers
        $tool_content .= action_bar(array(
            array('title' => $langAddOpenMeetingsServer,
                'url' => "openmeetingsconf.php?add_server",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langBack,
                'url' => "extapp.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));

        $q = Database::get()->queryArray("SELECT * FROM om_servers");
        if (count($q)>0) {
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                <tr><th class = 'text-center'>$langOpenMeetingsServer</th>
                    <th class = 'text-center'>$langOpenMeetingsPort</th>
                    <th class = 'text-center'>$langOpenMeetingsAdminUser</th>
                    <th class = 'text-center'>$langOpenMeetingsModuleKey</th>
                    <th class = 'text-center'>$langOpenMeetingsWebApp</th>
                    <th class = 'text-center'>$langBBBEnabled</th>
                    <th class = 'text-center'>".icon('fa-gears')."</th></tr>
                </thead>";
            foreach ($q as $srv) {
                $enabled_bbb_server = ($srv->enabled=='true')? $langYes : $langNo;

                $tool_content .= "<tr>";
                $tool_content .= "<td>$srv->hostname</td>";
                $tool_content .= "<td>$srv->port</td>";
                $tool_content .= "<td>$srv->username</td>";
                $tool_content .= "<td>$srv->module_key</td>";
                $tool_content .= "<td>$srv->webapp</td>";
                $tool_content .= "<td class = 'text-center'>$enabled_bbb_server</td>";


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
             $tool_content .= "<div class='alert alert-warning'>Δεν υπάρχουν διαθέσιμοι εξυπηρετητές.</div>";
        }
    }
}

draw($tool_content, 3, null, $head_content);
