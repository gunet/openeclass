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

$require_admin = TRUE;
require_once '../../include/baseTheme.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminManageFooter;
$pageName = $langAdminManageFooter;

if(isset($_POST['submit'])){

    set_config('footer_intro', purify($_POST['footer_intro']));
    set_config('link_fb', $_POST['link_fb']);
    set_config('link_tw', $_POST['link_tw']);
    set_config('link_ln', $_POST['link_ln']);
    set_config('link_footer_image', $_POST['link_footer_image']);

    $config_vars = [
        'activate_privacy_policy_text' => true,
        'activate_privacy_policy_consent' => true,
        'dont_display_courses_menu' => true,
        'dont_display_about_menu' => true,
        'dont_display_contact_menu' => true,
        'dont_display_manual_menu' => true,
        'enable_social_sharing_links' => true
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
$data['link_fb'] = get_config('link_fb') ?? '';
$data['link_tw'] = get_config('link_tw') ?? '';
$data['link_ln'] = get_config('link_ln') ?? '';
$data['link_footer_image'] = get_config('link_footer_image') ?? '';

view('admin.other.manage_footer', $data);
