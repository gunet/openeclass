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
    Database::get()->querySingle("DELETE FROM wc_servers WHERE id=?d", $id);
    // Display result message   
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/webconf.php');
}
// Save new config.php
else if (isset($_POST['submit'])) {
    $hostname = $_POST['hostname_form'];
    $enabled = $_POST['enabled'];
    
    if (isset($_POST['id_form'])) {
        $id = $_POST['id_form'];
        Database::get()->querySingle("UPDATE wc_servers SET hostname = ?s,
                enabled=?s
                WHERE id =?d", $hostname, $enabled, $id);
    } else {
        Database::get()->querySingle("INSERT INTO wc_servers (hostname,enabled) VALUES
        (?s,?s)", $hostname, $enabled);
    }    
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/webconf.php');
} // end of if($submit)

if (isset($_GET['add_server']) || isset($_GET['edit_server'])) {
    $pageName = isset($_GET['add_server']) ? $langAddWebConfServer : $langEdit;
    $toolName = $langWebConf;
    $navigation[] = array('url' => 'webconf.php', 'name' => $langWebConf);
    $data['action_bar'] = action_bar([
            [
                'title' => $langBack,
                'url' => "webconf.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            ]
        ]);
    $data['enabled'] = true;
    if (isset($_GET['edit_server'])) {
        $data['wc_server'] = $_GET['edit_server'];
        $data['server'] = Database::get()->querySingle("SELECT * FROM wc_servers WHERE id = ?d", $data['wc_server']);
        if ($data['server']->enabled == "false") {
            $data['enabled'] = false;
        }
    }
    $view = 'admin.other.extapps.webconf.create';


// Display config.php edit form
} else {    

    //display available WebConf servers
    $data['action_bar'] = action_bar(array(
        array('title' => $langAddWebConfServer,
            'url' => "webconf.php?add_server",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'),
        array('title' => $langBack,
            'url' => "extapp.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    $data['wc_servers'] = Database::get()->queryArray("SELECT * FROM wc_servers");
    $view = 'admin.other.extapps.webconf.index';
}

$data['menuTypeID'] = 3;
view($view, $data);

