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

$toolName = $langAdmin;
$pageName = $langAdminManageHomepage;

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toReorder'])) {
        reorder_table('homepagePriorities', null, null, $_POST['toReorder'],
            $_POST['prevReorder'] ?? null);
    }
    exit;
}

if (isset($_POST['submit'])) {
    set_config('total_courses',$_POST['total_courses']);
    set_config('visits_per_week',$_POST['visits_per_week']);
    set_config('users_registered',$_POST['users_registered']);

    foreach ($session->active_ui_languages as $langCode) {
        set_config('homepage_title_' . $langCode, $_POST['homepage_title_' . $langCode]);
        set_config('homepage_testimonial_title_' . $langCode, $_POST['homepage_testimonial_title_' . $langCode]);
        set_config('homepage_name_' . $langCode, $_POST['homepage_name_' . $langCode]);
        set_config('homepage_intro_' . $langCode, $_POST['homepage_intro_' . $langCode]);
    }

    set_config('homepage_testimonial_title', $_POST['homepage_testimonial_title']);
    set_config('display_login_form', $_POST['display_login_form'] ?? '');
    set_config('banner_link', $_POST['link_banner'] ?? '');
    set_config('dont_display_login_link', $_POST['dont_display_login_link'] ?? '');
    set_config('dont_display_courses_menu', $_POST['dont_display_courses_menu'] ?? '');
    set_config('dont_display_contact_menu', $_POST['dont_display_contact_menu'] ?? '');
    set_config('dont_display_about_menu', $_POST['dont_display_about_menu'] ?? '');
    set_config('dont_display_manual_menu', $_POST['dont_display_manual_menu'] ?? '');
    set_config('dont_display_faq_menu', $_POST['dont_display_faq_menu'] ?? '');

    Session::flash('message',"$langRegDone");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/manage_home.php");
}

if(isset($_GET['edit_priority'])) {
    $updated = Database::get()->query("UPDATE `homepagePriorities` SET visible = ?d WHERE id = ?d",$_GET['val'],$_GET['edit']);
    $visible = ($_GET['val']==1 ? 0 : 1);
    if($_GET['titleEdit'] == 'announcements') {
        set_config('dont_display_announcements', $visible);
    } elseif($_GET['titleEdit'] == 'popular_courses') {
        set_config('dont_display_popular_courses', $visible);
    } elseif($_GET['titleEdit'] == 'texts') {
        set_config('dont_display_texts', $visible);
    } elseif($_GET['titleEdit'] == 'testimonials') {
        set_config('dont_display_testimonials', $visible);
    } elseif($_GET['titleEdit'] == 'statistics') {
        set_config('dont_display_statistics', $visible);
    } else {
        set_config('dont_display_open_courses', $visible);
    }

    Session::flash('message', "$langRegDone");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/manage_home.php");
}

$display_login_form = get_config('display_login_form');
if ($display_login_form == 0) {
    $selected_dont_display_login_form = 'checked';
    $selected_display_only_login_form = $selected_display_login_form_and_image = '';
} else if ($display_login_form == 1) {
    $selected_display_only_login_form = 'checked';
    $selected_dont_display_login_form = $selected_display_login_form_and_image = '';
} else if ($display_login_form == 2) {
    $selected_display_login_form_and_image = 'checked';
    $selected_dont_display_login_form = $selected_display_only_login_form = '';
}

$data['selected_dont_display_login_form'] = $selected_dont_display_login_form;
$data['selected_display_only_login_form'] = $selected_display_only_login_form;
$data['selected_display_login_form_and_image'] = $selected_display_login_form_and_image;
$data['cbox_dont_display_login_link'] = get_config('dont_display_login_link') ? 'checked' : '';
$data['cbox_dont_display_courses_menu'] = get_config('dont_display_courses_menu') ? 'checked' : '';
$data['cbox_dont_display_about_menu'] = get_config('dont_display_about_menu') ? 'checked' : '';
$data['cbox_dont_display_manual_menu']= get_config('dont_display_manual_menu') ? 'checked' : '';
$data['cbox_dont_display_faq_menu']= get_config('dont_display_faq_menu') ? 'checked' : '';
$data['cbox_dont_display_contact_menu'] = get_config('dont_display_contact_menu') ? 'checked' : '';

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

$active_ui_languages = explode(' ', get_config('active_ui_languages'));
$langdirs = active_subdirs($webDir . '/lang', 'messages.inc.php');
$data['selectable_langs'] = [];
foreach ($language_codes as $langcode => $langname) {
    if (in_array($langcode, $langdirs)) {
        $loclangname = $langNameOfLang[$langname];
        if (in_array($langcode, $active_ui_languages)) {
            $data['selectable_langs'][$langcode] = $loclangname;
            $data['sel'][] = '<option value="' . $langcode . '">' . $loclangname . '</option>';
        }
    }
}

view('admin.other.manage_homepage', $data);
