<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langCollaborationPlatform;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$data['action_bar'] = action_bar([
    [
        'title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary'
    ]
]);


if(isset($_POST['submit'])){

    if(isset($_POST['always_enabled_collaboration']) and $_POST['always_enabled_collaboration'] == 'on'){
        if (!isset($_POST['enable_collaboration'])) {
            Session::flash('message',$langForbidden);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/admin/collaboration_enable.php");
        }
    }

    if (isset($_POST['enable_collaboration']) and $_POST['enable_collaboration'] == 'on') {
        $enable_collaboration = 1;
    } else {
        $enable_collaboration = 0;
    }

    if (isset($_POST['always_enabled_collaboration']) and $_POST['always_enabled_collaboration'] == 'on') {
        session_start();
        $_SESSION['collaboration_platform'] = 1;
        $always_enabled = 1;
    } else {
        $always_enabled = 0;
        unset($_SESSION['collaboration_platform']);
    }

    if($enable_collaboration == 0){
         unset($_SESSION['collaboration_platform']);
    }

    set_config('show_collaboration',$enable_collaboration);
    set_config('show_always_collaboration',$always_enabled);

    Session::flash('message',$langFaqEditSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/collaboration_enable.php");
}

view ('admin.collaboration.collaboration_enable', $data);
