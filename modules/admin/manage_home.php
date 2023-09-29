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
$toolName = $langAdminManageHomepage;
$pageName = $langAdminManageHomepage;

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toReorder'])) {
        reorder_table('homepagePriorities', null, null, $_POST['toReorder'],
            isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
    }
    exit;
}

if(isset($_POST['submit'])){
    set_config('total_courses',$_POST['total_courses']);
    set_config('visits_per_week',$_POST['visits_per_week']);
    set_config('show_only_loginScreen',$_POST['show_only_loginScreen']);
    Session::flash('message',"$langRegDone");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/manage_home.php");
}

$data['total_courses'] = get_config('total_courses');
$data['visits_per_week'] = get_config('visits_per_week');
$data['priorities'] = Database::get()->queryArray("SELECT * FROM homepagePriorities ORDER BY `order` ASC");

$data['action_bar'] = action_bar(
    [
        [
            'title' => $langBack,
            'url' => "{$urlServer}modules/admin/index.php",
            'icon' => 'fa-reply',
            'level' => 'primary'
        ],
        [
            'title' => $langAdminCreateHomeTexts.'-'.'Testimonials',
            'url' => "{$urlServer}modules/admin/homepageTexts_create.php",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ],
        [
            'title' => $langAdminAn,
            'url' => "{$urlServer}modules/admin/adminannouncements.php",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ],
    ],false); 


view('admin.other.manage_homepage', $data);