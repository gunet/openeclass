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



if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toDelete'])) {
        Database::get()->query("UPDATE `homepageTexts` SET `order` = `order` - 1 WHERE `order` > ?d", $_POST['oldOrder']);
        Database::get()->query("DELETE FROM homepageTexts WHERE `id` = ?d", $_POST['toDelete']);
    } elseif (isset($_POST['toReorder'])) {
        reorder_table('homepageTexts', null, null, $_POST['toReorder'],
            isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
    }
    exit;
}


//create text
if(isset($_POST['submitText'])){
    $title = $_POST['textTitle'];
    $content = $_POST['content'];
    $languageText = $_POST['localize'];
    $type = $_POST['type'];

    $top = Database::get()->querySingle("SELECT MAX(`order`) as max FROM `homepageTexts`")->max;

    Database::get()->query("INSERT INTO homepageTexts (lang,title, body, `order`, `type`) VALUES (?s,?s, ?s, ?d, ?d)",$languageText, $title, $content, $top + 1, $type);

    Session::flash('message',"$langAddSuccess");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/homepageTexts_create.php");
}


//modify text
if(isset($_POST['modifyText'])){
    $title = $_POST['textTitle'];
    $content = $_POST['content'];
    $record = $_POST['id'];
    $languageText = $_POST['localize'];
    $type = $_POST['type'];

    Database::get()->query("UPDATE homepageTexts SET `lang`=?s, `title`=?s, `body`=?s , `type`=?d WHERE `id`=?d",$languageText, $title, $content, $type, $record);

    Session::flash('message',"$langRegDone");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/homepageTexts_create.php");
}

$data['modify'] = '';
if(isset($_GET['homepageText']) and $_GET['homepageText'] == 'modify'){
    $data['modify'] = $_GET['homepageText'];
    $data['id'] = $_GET['id'];

    $data['textModify'] = Database::get()->querySingle("SELECT * FROM `homepageTexts` WHERE `id`=?d", $data['id']);
    $data['editor'] = rich_text_editor('content', 5, 40, $data['textModify']->body );
    $textLang = $data['textModify']->lang;
    $data['lang_select_options'] = lang_select_options('localize', "class='form-control'", $textLang);
}


$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'manage_home.php', 'name' => $langAdminManageHomepage);
$toolName = $langAdminCreateHomeTexts;
$pageName = $langAdminCreateHomeTexts;


$data['texts'] = Database::get()->queryArray("SELECT * FROM homepageTexts WHERE `lang` = ?s ORDER BY `order` ASC",$language);

$data['new'] = isset($_GET['homepageText']) && $_GET['homepageText'] == 'new';

if ($data['new']) {
    $data['editor'] = rich_text_editor('content', 5, 40, '' );
    $data['lang_select_options'] = lang_select_options('localize', "class='form-control'");
}


if(!$data['new'] and !$data['modify']){
   $data['action_bar'] = action_bar(
    [
        [
            'title' => $langBack,
            'url' => "{$urlServer}modules/admin/manage_home.php",
            'icon' => 'fa-reply',
            'level' => 'primary'
        ],
        [
            'title' => $langAdd,
            'url' => $_SERVER['SCRIPT_NAME'].'?homepageText=new',
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ]
    ],false);
}else{
    $data['action_bar'] = action_bar(
        [
            [
                'title' => $langBack,
                'url' => $_SERVER['SCRIPT_NAME'],
                'icon' => 'fa-reply',
                'level' => 'primary'
            ]
        ],false);
}

view('admin.other.homepageTexts_create', $data);
