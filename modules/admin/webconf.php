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

if (isset($_GET['delete_server'])) {
    $id = $_GET['delete_server'];
    Database::get()->querySingle("DELETE FROM tc_servers WHERE id=?d", $id);
    Database::get()->query("DELETE FROM course_external_server WHERE external_server=?d", $id);
    // Display result message   
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/webconf.php');
}
// Save new config.php
else if (isset($_POST['submit'])) {
    $hostname = $_POST['hostname_form'];    
    $enabled = $_POST['enabled'];
    $tc_courses = $_POST['tc_courses'];    
    if (in_array(0, $tc_courses)) {
        $allcourses = 1; // tc server is assigned to all courses
    } else {
        $allcourses = 0; // tc server is assigned to specific courses
    }
    
    if (isset($_POST['id_form'])) {
        $id = $_POST['id_form'];
        Database::get()->querySingle("UPDATE tc_servers SET 
                                            hostname = ?s,
                                            enabled=?s,
                                            all_courses=?d
                                        WHERE id =?d", $hostname, $enabled, $allcourses, $id);
        Database::get()->query("DELETE FROM course_external_server WHERE external_server = ?d", $id);
        if ($allcourses == 0) {        
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $id);
            }
        }
    } else {
        Database::get()->querySingle("INSERT INTO tc_servers (`type`, hostname, enabled, max_rooms, max_users, weight, all_courses) 
                                            VALUES ('webconf', ?s, ?s, 0, 0, 1, ?d)", $hostname, $enabled, $allcourses);
        $tc_id = $q->lastInsertID;
        if ($allcourses == 0) {
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $tc_id);
            }
        }
    }
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/webconf.php');
} // end of if($submit)

if (isset($_GET['add_server']) or isset($_GET['edit_server'])) {    
    $wc_id = $hostnamevalue = '';
    $adminactivate_true = "checked value='true'";    
    $adminactivate_false = "value='false'";    
                    
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
            </div>";
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