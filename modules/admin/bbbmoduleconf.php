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
require_once 'modules/tc/functions.php';

$toolName = $langBBBConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

load_js('tools.js');
load_js('validation.js');

$available_themes = active_subdirs("$webDir/template", 'theme.html');
if (isset($_GET['delete_server'])) {
    $id = getDirectReference($_GET['delete_server']);
    Database::get()->querySingle("DELETE FROM tc_servers WHERE id=?d", $id);
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/bbbmoduleconf.php');   
} else if (isset($_POST['submit'])) {
    // Save new config
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
    $allcourses = $_POST['allcourses'];

    if (isset($_POST['id_form'])) {
        $id = getDirectReference($_POST['id_form']);
        Database::get()->querySingle("UPDATE tc_servers SET hostname = ?s,
                ip = ?s,
                server_key = ?s,
                api_url = ?s,
                max_rooms =?s,
                max_users =?s,
                enable_recordings =?s,
                enabled = ?s,
                weight = ?d,
                all_courses = ?d
                WHERE id =?d", $hostname, $ip, $key, $api_url, $max_rooms, $max_users, $enable_recordings, $enabled, $weight, $allcourses, $id);
    } else {
        Database::get()->querySingle("INSERT INTO tc_servers (`type`, hostname, ip, server_key, api_url, max_rooms, max_users, enable_recordings, enabled, weight, all_courses) VALUES
        ('bbb', ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?d, ?d)", $hostname, $ip, $key, $api_url, $max_rooms, $max_users, $enable_recordings, $enabled, $weight, $allcourses);
    }    
    // Display result message
    Session::Messages($langFileUpdatedSuccess,"alert-success");
    redirect_to_home_page("modules/admin/bbbmoduleconf.php");

} // end of if($submit)

if (isset($_GET['add_server']) || isset($_GET['edit_server'])) {
    $pageName = isset($_GET['add_server']) ? $langAddServer : $langEdit;
    $toolName = $langBBBConf;
    $navigation[] = array('url' => 'bbbmoduleconf.php', 'name' => $langBBBConf);
    $data['action_bar'] = action_bar([
                [
                    'title' => $langBack,
                    'url' => "bbbmoduleconf.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label'
                ]
            ]);
    $data['enabled_recordings'] = true;
    $data['enabled'] = true;
    $data['enabled_all_courses'] = true;
    if (isset($_GET['edit_server'])) {
         $data['bbb_server'] = getDirectReference($_GET['edit_server']);
         $data['server'] = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $data['bbb_server']);
         if ($data['server']->enable_recordings == "false") {
             $data['enabled_recordings'] = false;
         }
         if ($data['server']->enabled == "false") {
             $data['enabled'] = false;
         }
         if ($data['server']->all_courses == "1") {
             $data['enabled_all_courses'] = true;
         }
         if ($data['server']->all_courses == "0") {
             $data['enabled_all_courses'] = false;
         }       
    }

    $view = 'admin.other.extapps.bbb.create';
}
// Display config edit form
else {    

    //display available BBB servers
    $data['action_bar'] = action_bar(array(
        array('title' => $langAddServer,
            'url' => "bbbmoduleconf.php?add_server",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'),
        array('title' => $langBack,
            'url' => "extapp.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    $data['bbb_servers'] = Database::get()->queryArray("SELECT * FROM tc_servers");
    $view = 'admin.other.extapps.bbb.index';
}
$data['menuTypeID'] = 3;
view($view, $data);