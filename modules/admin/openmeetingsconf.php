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

$available_themes = active_subdirs("$webDir/template", 'theme.html');

load_js('tools.js');
load_js('validation.js');
load_js('select2');

$head_content .= "<script type='text/javascript'>
    $(document).ready(function () {                
        $('#select-courses').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-courses').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-courses').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-courses').val(stringVal).trigger('change');
        });
    });
</script>";

if (isset($_GET['add_server']) or isset($_GET['edit_server'])) {
    
    $om_id = $hostnamevalue = $portnamevalue = $adminuservalue = $adminpassvalue = $adminwebappvalue = $adminmaxusers = $adminmaxrooms = $weight = '';
    $adminenrecordings_true = "checked value='true'";
    $adminenrecordings_false = "value='false'";
    $adminactivate_true = "checked value='true'";
    $adminactivate_false = "value='false'";    
    
    if (isset($_GET['edit_server'])) {
        $pageName = $langEdit;
        $om_server = $_GET['edit_server'];        
        $server = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $om_server);    
        if ($server) {
            $hostnamevalue = $server->hostname;    
            $portnamevalue = $server->port;
            $adminuservalue = $server->username;
            $adminpassvalue = $server->password;            
            $adminwebappvalue = $server->webapp;
            $adminmaxrooms = $server->max_rooms;
            $adminmaxusers = $server->max_users;
            $weight = $server->weight;
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
        $pageName = $langAddServer;
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
    $tool_content .= "<div class='form-group'><label for='port_form' class='col-sm-3 control-label'>$langPort:</label>
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
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langBBBServerOrder:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='weight' value='$weight'></div>";
    $tool_content .= "</div>";    
    $tool_content .= "<div class='form-group' id='courses-list'>
                <label class='col-sm-3 control-label'>$langUseOfTc:&nbsp;&nbsp;
                <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langToAllCoursesInfo'></span></label>                    
                <div class='col-sm-9'>                                
                    <select class='form-control' name='tc_courses[]' multiple class='form-control' id='select-courses'>";
                    $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                                                    NOT IN (SELECT course_id FROM course_external_server) 
                                                                ORDER BY title");
                    if (isset($_GET['edit_server'])) {
                        if ($server->all_courses == '1') {
                            $tool_content .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
                        } else {
                            $tc_courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                                        IN (SELECT course_id FROM course_external_server WHERE external_server = ?d) ORDER BY title", $_GET['edit_server']);
                            if (count($tc_courses_list) > 0) {
                                foreach($tc_courses_list as $c) {
                                    $tool_content .= "<option value='$c->id' selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
                                }
                                $tool_content .= "<option value='0'><h2>$langToAllCourses</h2></option>";
                            }
                        }
                    } else {
                        $tool_content .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
                    }
                    foreach($courses_list as $c) {
                        $tool_content .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
                    }
        $tool_content .= "</select>
                    <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                </div>
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
                chkValidator.addValidation("weight","req","' . $langBBBServerAlertOrder . '");
                chkValidator.addValidation("weight","numeric","' . $langBBBServerAlertOrder . '");
            </script>';
} else if (isset($_GET['delete_server'])) {
        $id = $_GET['delete_server'];
        Database::get()->querySingle("DELETE FROM tc_servers WHERE id=?d", $id);
        Database::get()->query("DELETE FROM course_external_server WHERE external_server=?d", $id);
        // Display result message
        Session::Messages($langFileUpdatedSuccess, 'alert-success');
        redirect_to_home_page('modules/admin/bbbmoduleconf.php');
        
} elseif (isset($_POST['submit'])) { // Save new config.php        
    $hostname = $_POST['hostname_form'];
    $port = $_POST['port_form'];
    $username = $_POST['username_form'];
    $password = $_POST['password_form'];    
    $webapp = $_POST['webapp_form'];    
    $max_rooms = $_POST['max_rooms_form'];
    $max_users = $_POST['max_users_form'];
    $enable_recordings = $_POST['enable_recordings'];
    $enabled = $_POST['enabled'];
    $weight = $_POST['weight'];
    $tc_courses = $_POST['tc_courses'];    
    if (in_array(0, $tc_courses)) {
        $allcourses = 1; // tc server is assigned to all courses
    } else {
        $allcourses = 0; // tc server is assigned to specific courses
    }
    
    if (isset($_POST['id_form'])) {        
        $id = $_POST['id_form'];
        Database::get()->querySingle("UPDATE tc_servers SET hostname = ?s,
                port = ?s,
                username = ?s,
                password = ?s,                
                webapp = ?s,
                enabled = ?s,
                max_rooms = ?d,
                max_users = ?d,
                enable_recordings = ?s,
                weight = ?d,
                all_courses = ?d
                WHERE id = ?d", $hostname, $port, $username, $password, $webapp, $enabled, $max_rooms, $max_users, $enable_recordings, $weight, $allcourses, $id);
        Database::get()->query("DELETE FROM course_external_server WHERE external_server = ?d", $id);
        if ($allcourses == 0) {
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $id);
            }
        }
    } else {
        Database::get()->querySingle("INSERT INTO tc_servers (`type`, hostname, port, username, password, webapp, enabled, max_rooms, max_users, enable_recordings, weight, all_courses) VALUES
        ('om', ?s, ?s, ?s, ?s, ?s, ?s, ?d, ?d, ?s, ?d, ?d)", $hostname, $port, $username, $password, $webapp, $enabled, $max_rooms, $max_users, $enable_recordings, $weight, $allcourses);
        $tc_id = $q->lastInsertID;
        if ($allcourses == 0) {
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $tc_id);
            }
        }
    }
    // Display result message
    Session::Messages($langFileUpdatedSuccess,"alert-success");
    redirect_to_home_page("modules/admin/openmeetingsconf.php");        
} else {
    //display available OpenMeetings servers
    $tool_content .= action_bar(array(
    array('title' => $langAddServer,
        'url' => "$_SERVER[SCRIPT_NAME]?add_server",
        'icon' => 'fa-plus-circle',
        'level' => 'primary-label',
        'button-class' => 'btn-success'),
    array('title' => $langBack,
        'url' => "extapp.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

    $q = Database::get()->queryArray("SELECT * FROM tc_servers WHERE `type` = 'om' ORDER BY weight");
    if (count($q) > 0) {
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>
            <thead>
            <tr><th class = 'text-center'>$langOpenMeetingsServer</th>
                <th class = 'text-center'>$langPort</th>
                <th class = 'text-center'>$langBBBEnabled</th>                    
                <th class = 'text-center'>$langOpenMeetingsAdminUser</th>                
                <th class = 'text-center'>$langOpenMeetingsWebApp</th>
                <th class = 'text-center'>".icon('fa-gears')."</th></tr>
            </thead>";
        foreach ($q as $srv) {
            $enabled_om_server = ($srv->enabled == 'true')? $langYes : $langNo;
            $tool_content .= "<tr>";
            $tool_content .= "<td>$srv->hostname</td>";
            $tool_content .= "<td>$srv->port</td>";
            $tool_content .= "<td>$enabled_om_server</td>";
            $tool_content .= "<td>$srv->username</td>";            
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