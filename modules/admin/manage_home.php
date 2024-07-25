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
    set_config('users_registered',$_POST['users_registered']);
    set_config('homepage_title',$_POST['homepage_title']);
    set_config('homepage_name',$_POST['homepage_name']);
    set_config('homepage_intro', purify($_POST['homepage_intro']));
    set_config('homepage_testimonial_title',$_POST['homepage_testimonial_title']);
    set_config('show_only_loginScreen', $_POST['show_only_loginScreen'] ?? '');
    set_config('dont_display_login_form', $_POST['dont_display_login_form'] ?? '');
    set_config('hide_login_link', $_POST['hide_login_link'] ?? '');
    set_config('banner_link',$_POST['link_banner'] ?? '');

    Session::flash('message',"$langRegDone");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/manage_home.php");
}

if(isset($_GET['edit_priority'])){
    $updated = Database::get()->query("UPDATE `homepagePriorities` SET visible = ?d WHERE id = ?d",$_GET['val'],$_GET['edit']);
    $visible = ($_GET['val']==1 ? 0 : 1);
    if($_GET['titleEdit'] == 'announcements'){
        set_config('dont_display_announcements', $visible);
    }elseif($_GET['titleEdit'] == 'popular_courses'){
        set_config('dont_display_popular_courses', $visible);
    }elseif($_GET['titleEdit'] == 'texts'){
        set_config('dont_display_texts', $visible);
    }elseif($_GET['titleEdit'] == 'testimonials'){
        set_config('dont_display_testimonials', $visible);
    }elseif($_GET['titleEdit'] == 'statistics'){
        set_config('dont_display_statistics', $visible);
    }else{
        set_config('dont_display_open_courses', $visible);
    }

    Session::flash('message',"$langRegDone");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/manage_home.php");
}

$data['total_courses'] = get_config('total_courses');
$data['visits_per_week'] = get_config('visits_per_week');

$data['homepage_intro'] = rich_text_editor('homepage_intro', 5, 20, get_config('homepage_intro'));
$data['priorities'] = Database::get()->queryArray("SELECT * FROM homepagePriorities ORDER BY `order` ASC");

$data['action_bar'] = action_bar(
    [
        [
            'title' => $langAdminCreateHomeTexts.'-'.'Testimonials',
            'url' => "{$urlAppend}modules/admin/homepageTexts_create.php",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ],
        [
            'title' => $langAdminAn,
            'url' => "{$urlAppend}modules/admin/adminannouncements.php",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ],
    ],false);

view('admin.other.manage_homepage', $data);
