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


$data['new'] = false;
$data['modify'] = false;
$data['native_language_names_init'] = $native_language_names_init;

if(isset($_GET['lang'])){
    $langCode = $_GET['lang'];
    $data['langCode'] = $langCode;
}else{
    $langCode = get_config('default_language');
    $data['langCode'] = $langCode;
}

$toolName = $langMentoringIntro.' ('.$native_language_names_init[$langCode].')';
$data['intro'] = "mentoring_homepage_intro_$langCode";

if(isset($_POST['submit'])){
    $langText = $_POST['langText'];
    set_config('mentoring_homepage_intro_'.$langText, purify($_POST['content']));
    Session::flash('message',"$langAddSuccess");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/mentoring_homepageIntro.php");
}

if(isset($_GET['new'])){
    $data['new'] = true;
    $data['editor'] = rich_text_editor('content', 5, 40, '');
}

if(isset($_GET['modify'])){
    $data['modify'] = true;
    $data['editor'] = rich_text_editor('content', 5, 40, get_config('mentoring_homepage_intro_'.$langCode));
}

if(isset($_GET['del'])){
    set_config('mentoring_homepage_intro_'.$langCode, '');
    Session::flash('message',"$langdelHomePageIntroSuccess");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/mentoring_homepageIntro.php");
}


$data['action_bar'] = action_bar(
    [
        [ 'title' => trans('langBack'),
            'url' => $urlServer.'modules/admin/mentoring_homepageTexts_create.php',
            'icon' => 'fa-reply',
            'level' => 'primary-label',
            'button-class' => 'btn-secondary'],
        [
            'title' => $langAdd,
            'url' => $_SERVER['SCRIPT_NAME'].'?new&lang='.$langCode,
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success',
            'show' => (!get_config('mentoring_homepage_intro_'.$langCode))
        ]
    ],false); 


view('admin.mentoring_platform.mentoring_homepageIntro', $data);