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

$toolName = $langWebConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('validation.js');

$available_themes = active_subdirs("$webDir/template", 'theme.html');

if (isset($_GET['delete_server'])) {
    $id = $_GET['delete_server'];
    Database::get()->querySingle("DELETE FROM tc_servers WHERE id=?d", $id);
    // Display result message   
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/webconf.php');
}
// Save new config.php
else if (isset($_POST['submit'])) {
    $hostname = $_POST['hostname_form'];    
    $enabled = $_POST['enabled'];
    $allcourses = $_POST['allcourses'];
    
    if (isset($_POST['id_form'])) {
        $id = $_POST['id_form'];
        Database::get()->querySingle("UPDATE tc_servers SET 
                                            hostname = ?s,
                                            enabled=?s,
                                            all_courses=?d
                                        WHERE id =?d", $hostname, $enabled, $allcourses, $id);
    } else {
        Database::get()->querySingle("INSERT INTO tc_servers (`type`, hostname, enabled, max_rooms, max_users, weight, all_courses) 
                                            VALUES ('webconf', ?s, ?s, ?s, 0, 0, 1, ?d)", $hostname, $enabled, $allcourses);
    }
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/webconf.php');
} // end of if($submit)

if (isset($_GET['add_server']) or isset($_GET['edit_server'])) {    
    $wc_id = $hostnamevalue = '';
    $adminactivate_true = "checked value='true'";
    $adminassignall_true = "checked value='1'";
    $adminactivate_false = "value='false'";
    $adminassignall_false = "value='0'";
                    
    if (isset($_GET['edit_server'])) {
        $pageName = $langEdit;
        $wc_server = $_GET['edit_server'];
        $server = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $wc_server);
        if ($server) {
            $hostnamevalue = $server->hostname;            
            if ($server->enabled == 'true') {
                $adminactivate_true = "value='true' checked ";
                $adminactivate_false = "value='false'";
            } else {
                $adminactivate_true = "value='true'";
                $adminactivate_false = "value='false' checked ";
            }
            if ($server->all_courses == '1') {
                $adminassignall_true = "value='1' checked ";
                $adminassignall_false = "value='0'";
            } else {
                $adminassignall_true = "value='1'";
                $adminassignall_false = "value='0' checked ";
            }
        }
        $wc_id = "<input class='form-control' type = 'hidden' name = 'id_form' value='$server->id'>";
    } else {
        $pageName = $langAddServer;        
    }
    $toolName = $langWebConf;
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]", 'name' => $langWebConf);
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
    
    $tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>
        <fieldset>
            <div class='form-group'>
                <label for='host' class='col-sm-3 control-label'>$langWebConfServer:</label>
                <div class='col-sm-9'>
                    <input class='form-control' id='host' type='text' name='hostname_form' value='$hostnamevalue'>
                </div>
            </div>            
            <div class='form-group'>
                <label class='col-sm-3 control-label'>$langActivate:</label>
                <div class='col-sm-9 radio'><label><input type='radio' name='enabled' $adminactivate_true>$langYes</label></div>
                <div class='col-sm-offset-3 col-sm-9 radio'><label><input type='radio' name='enabled' $adminactivate_false>$langNo</label></div>
            </div>
            <div class='form-group'>
                <label class='col-sm-3 control-label'>$langUseOfTc:</label>
                <div class='col-sm-9 radio'><label><input type='radio' name='allcourses' $adminassignall_true>$langToAllCourses</label>
                    <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langToAllCoursesInfo'></span>
                </div>
                <div class='col-sm-offset-3 col-sm-9 radio'><label><input type='radio' name='allcourses' $adminassignall_false>$langToSomeCourses</label>
                    <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langToSomeCoursesInfo'></span>
                </div>
            </div>";
        $tool_content .= $wc_id;
        $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'><input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'></div></div>";
        $tool_content .= "</fieldset></form></div>";    
        $tool_content .= '<script language="javaScript" type="text/javascript">
            var chkValidator  = new Validator("serverForm");
            chkValidator.addValidation("hostname_form","req", "' . $langWebConfServerAlertHostname . '");            
        </script>';

// Display config.php edit form
} else {    

    //display available WebConf servers
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

    $q = Database::get()->queryArray("SELECT * FROM tc_servers WHERE `type`= 'webconf'");
    if (count($q) > 0) {
        $tool_content .= "<div class='table-responsive'>
            <table class='table-default'>
                <thead>
                    <tr>
                        <th class = 'text-center'>$langWebConfServer</th>                        
                        <th class = 'text-center'>$langBBBEnabled</th>
                        <th class = 'text-center'>" . icon('fa-gears') . "</th>
                    </tr>
                </thead>";
                foreach ($q as $wc_server) {
                    $enabled_wc_server = ($wc_server->enabled == 'true')? $langYes : $langNo;
                    $tool_content .= "<tr>";
                    $tool_content .= "<td>$wc_server->hostname</td>";                    
                    $tool_content .= "<td class='text-center'>$enabled_wc_server</td>";
                    $tool_content .= "<td class='option-btn-cell'>".action_button(array(
                                                array('title' => $langEditChange,
                                                      'url' => "$_SERVER[SCRIPT_NAME]?edit_server=$wc_server->id",
                                                      'icon' => 'fa-edit'),
                                                array('title' => $langDelete,
                                                      'url' => "$_SERVER[SCRIPT_NAME]?delete_server=$wc_server->id",
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