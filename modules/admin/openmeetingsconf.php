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

$available_themes = active_subdirs("$webDir/template", 'theme.html');

if (isset($_GET['add_server']) or isset($_GET['edit_server'])) {
    $pageName = isset($_GET['add_server']) ? $langAddServer : $langEdit;        
    $toolName = $langOpenMeetingsConf;
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]", 'name' => $langOpenMeetingsConf);
    
    $data['enabled_recordings'] = true;
    $data['enabled'] = true;
    $data['action_bar'] = action_bar(array(
                            array('title' => $langBack,
                                  'url' => "$_SERVER[SCRIPT_NAME]",
                                  'icon' => 'fa-reply',
                                  'level' => 'primary-label')));
    
    
    if (isset($_GET['add_server'])) {
        $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course 
                                            WHERE id NOT IN (SELECT course_id FROM course_external_server) 
                                            AND visible != " . COURSE_INACTIVE . "
                                            ORDER BY title");        
        $data['listcourses'] = "<option value='0' selected><h2>$langToAllCourses</h2></option>";
        foreach ($courses_list as $c) {
            $data['listcourses'] .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
        }
    } else {
        $data['om_server'] = getDirectReference($_GET['edit_server']);
        $data['server'] = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $data['om_server']);
        if ($data['server']->enable_recordings == "false") {
            $data['enabled_recordings'] = false;
        }
        if ($data['server']->enabled == "false") {
            $data['enabled'] = false;
        }
        $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                                        NOT IN (SELECT course_id FROM course_external_server) 
                                                        AND visible != " . COURSE_INACTIVE . "
                                                    ORDER BY title");
        $listcourses = '';
        if ($data['server']->all_courses == '1') {
            $listcourses .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
        } else {
            $tc_courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                        IN (SELECT course_id FROM course_external_server WHERE external_server = ?d) 
                                        ORDER BY title", $data['om_server']);
            if (count($tc_courses_list) > 0) {
                foreach($tc_courses_list as $c) {
                    $listcourses .= "<option value='$c->id' selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
                }
                $listcourses .= "<option value='0'><h2>$langToAllCourses</h2></option>";
            }
        }
        foreach($courses_list as $c) {
            $listcourses .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
        }        
        $data['listcourses'] = $listcourses;        
    }   
    $view = 'admin.other.extapps.openmeetings.create';
} else if (isset($_GET['delete_server'])) {
        $id = $_GET['delete_server'];
        Database::get()->querySingle("DELETE FROM tc_servers WHERE id=?d", $id);
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
        $id = getDirectReference($_POST['id_form']);
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
        $q = Database::get()->query("INSERT INTO tc_servers (`type`, hostname, port, username, password, webapp, enabled, max_rooms, max_users, enable_recordings, weight, all_courses) VALUES
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
    $data['action_bar'] = action_bar(array(
                array('title' => $langAddServer,
                    'url' => "$_SERVER[SCRIPT_NAME]?add_server",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'),
                array('title' => $langBack,
                    'url' => "extapp.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));

    $data['om_servers'] = Database::get()->queryArray("SELECT * FROM tc_servers WHERE `type` = 'om' ORDER BY weight");
    $view = 'admin.other.extapps.openmeetings.index';
}

$data['menuTypeID'] = 3;
view($view, $data);