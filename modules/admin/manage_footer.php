<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
 * ======================================================================== */

$require_admin = TRUE;
require_once '../../include/baseTheme.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminManageFooter;
$pageName = $langAdminManageFooter;

if(isset($_POST['submit'])){

    set_config('footer_intro', purify($_POST['footer_intro']));

    $config_vars = [
        'activate_privacy_policy_text' => true,
        'activate_privacy_policy_consent' => true,
        'dont_display_courses_menu' => true,
        'dont_display_about_menu' => true,
        'dont_display_contact_menu' => true,
        'dont_display_manual_menu' => true
        ];

    register_posted_variables($config_vars, 'all', 'intval');

    // update table `config`
    foreach ($config_vars as $varname => $what) {
        set_config($varname, $GLOBALS[$varname]);
    }

    Session::flash('message',"$langRegDone");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/manage_footer.php");
}


$data['footer_intro'] = rich_text_editor('footer_intro', 5, 20, get_config('footer_intro'));

$data['action_bar'] = action_bar(
    [
        [
            'title' => $langBack,
            'url' => "{$urlAppend}modules/admin/index.php",
            'icon' => 'fa-reply',
            'level' => 'primary'
        ]
    ],false);


view('admin.other.manage_footer', $data);
