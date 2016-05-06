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

if (isset($_GET['delete_server'])) {
    $id = getDirectReference($_GET['delete_server']);
    Database::get()->querySingle("DELETE FROM om_servers WHERE id=?d", $id);
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/openmeetingsconf.php');
}
// Save new config.php
else if (isset($_POST['submit'])) {
    $hostname = canonicalize_url($_POST['hostname_form']);
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
        $id = getDirectReference($_POST['id_form']);
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

        $hostname = canonicalize_url($hostname);
        Database::get()->query("INSERT INTO om_servers (hostname,port,username,password,module_key,webapp,enabled,max_rooms,max_users,enable_recordings) VALUES
        (?s,?s,?s,?s,?s,?s,?s,?d,?d,?s)", $hostname, $port, $username, $password, $module, $webapp, $enabled,$max_rooms,$max_users,$enable_recordings);
    }    
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/openmeetingsconf.php');
} // end of if($submit)


if (isset($_GET['add_server']) || isset($_GET['edit_server'])) {
    $pageName = isset($_GET['add_server']) ? $langAddBBBServer : $langEdit;
    $toolName = $langOpenMeetingsConf;
    $navigation[] = array('url' => 'openmeetingsconf.php', 'name' => $langOpenMeetingsConf);
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
            'url' => "openmeetingsconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    $data['enabled_recordings'] = true;
    $data['enabled'] = true;
    if (isset($_GET['edit_server'])) {
         $data['om_server'] = getDirectReference($_GET['edit_server']);
         $data['server'] = Database::get()->querySingle("SELECT * FROM om_servers WHERE id = ?d", $data['om_server']);
         if ($data['server']->enable_recordings == "false") {
             $data['enabled_recordings'] = false;
         }
         if ($data['server']->enabled == "false") {
             $data['enabled'] = false;
         }       
    }
    $view = 'admin.other.extapps.openmeetings.create';
}
else {    

    //display available OpenMeetings servers
    $data['action_bar'] = action_bar(array(
        array('title' => $langAddOpenMeetingsServer,
            'url' => "openmeetingsconf.php?add_server",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'),
        array('title' => $langBack,
            'url' => "extapp.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    $data['om_servers'] = Database::get()->queryArray("SELECT * FROM om_servers");
    $view = 'admin.other.extapps.openmeetings.index';

}
$data['menuTypeID'] = 3;
view($view, $data);
