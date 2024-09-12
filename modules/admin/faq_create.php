<?php

/* ========================================================================
 * Open eClass 3.5
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
$require_help = true;
$helpTopic = 'system_settings';
$helpSubTopic = 'faq_creation';

require_once '../../include/baseTheme.php';

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toDelete'])) {
        Database::get()->query("UPDATE `faq` SET `order` = `order` - 1 WHERE `order` > ?d", $_POST['oldOrder']);
        Database::get()->query("DELETE FROM faq WHERE `id` = ?d", $_POST['toDelete']);
    } elseif (isset($_POST['toReorder'])) {
        reorder_table('faq', null, null, $_POST['toReorder'],
            isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
    }
    exit;
}

if (isset($_POST['submitFaq'])) {
    if (empty(trim($_POST['question'])) or empty(trim($_POST['answer']))) {
        Session::flash('message', "$langEmptyFaculte");
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/admin/faq_create.php");
    } else {
        $question = $_POST['question'];
        $answer = $_POST['answer'];
        $top = Database::get()->querySingle("SELECT MAX(`order`) as max FROM `faq`")->max;
        Database::get()->query("INSERT INTO faq (title, body, `order`) VALUES (?s, ?s, ?d)", $question, $answer, $top + 1);
        Session::flash('message', "$langFaqAddSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/admin/faq_create.php");
    }
}

if (isset($_POST['modifyFaq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    $record = $_POST['id'];

    Database::get()->query("UPDATE faq SET `title`=?s, `body`=?s WHERE `id`=?d", $question, $answer, $record);

    Session::flash('message',"$langFaqEditSuccess");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/faq_create.php");
}

if (isset($_GET['faq']) && $_GET['faq'] == 'delete') {
    $record = $_GET['id'];

    Database::get()->query("DELETE FROM faq WHERE `id`=?d", $record);

    Session::flash('message',"$langFaqDeleteSuccess");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/faq_create.php");
}

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminCreateFaq;
$pageName = $toolName;

$data['action_bar'] = action_bar(
    [
        [
            'title' => $langFaqAdd,
            'url' => $_SERVER['SCRIPT_NAME'].'?faq=new',
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ],
        [
            'title' => $langFaqExpandAll,
            'url' => "#",
            'class' => 'expand',
            'icon' => 'fa-folder-open',
            'level' => 'primary-label',
            'modal-class' => 'expand',
            'show' => !isset($_GET['faq'])
        ]
    ],false);

$data['faqs'] = Database::get()->queryArray("SELECT * FROM faq ORDER BY `order` ASC");

$data['new'] = isset($_GET['faq']) && $_GET['faq'] == 'new';
$data['modify'] = isset($_GET['faq']) && $_GET['faq'] == 'modify';

if ($data['modify']) {
    $data['id'] = $_GET['id'];
    $data['faq_mod'] = Database::get()->querySingle("SELECT * FROM `faq` WHERE `id`=?d", $_GET['id']);
    $data['editor'] = rich_text_editor('answer', 5, 40, $data['faq_mod']->body );
}

if ($data['new']) {
    $data['id'] = '';
    $data['editor'] = rich_text_editor('answer', 5, 40, '' );
}

view('admin.other.faq_create', $data);
