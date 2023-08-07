<?php
session_start();
$_SESSION['mentoring_platform'] = 1;

$require_login = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/mentoring/functions.php';

//after_reconnect_go_to_mentoring_homepage();
if(!get_config('mentoring_platform')){
    redirect_to_home_page("main/portfolio.php");
}

if(get_config('mentoring_always_active') ){
    if ($language != get_config('default_language')) {
        $language = get_config('default_language');
        // include_messages
        include "lang/$language/common.inc.php";
        $extra_messages = "config/{$language_codes[$language]}.inc.php";
        if (file_exists($extra_messages)) {
            include $extra_messages;
        } else {
            $extra_messages = false;
        }
        include "lang/$language/messages.inc.php";
        if ($extra_messages) {
            include $extra_messages;
        }
    }
}

$toolName = $langWelcomeMentoringPlatform;

unset($_SESSION['program_code']);
unset($_SESSION['program_id']);
unset($_SESSION['mentoring_group_id']);
unset($mentoring_program_code);
unset($mentoring_program_id);
unset($is_editor_mentoring_program);

$data['texts'] = Database::get()->queryArray("SELECT * FROM `mentoring_homepageTexts` WHERE `lang` = ?s ORDER BY `order` ASC",$language);

view('modules.mentoring.home.home', $data);
