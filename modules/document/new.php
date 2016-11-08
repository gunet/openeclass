<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2014  Greek Universities Network - GUnet
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

/**
 * @file new.php
 * @brief Create / edit HTML document
 */

$require_admin = defined('COMMON_DOCUMENTS');
$require_current_course = !(defined('COMMON_DOCUMENTS') or defined('MY_DOCUMENTS'));
$require_login = true;

require_once "../../include/baseTheme.php";
require_once "modules/document/doc_init.php";

doc_init();

if (defined('COMMON_DOCUMENTS')) {
    $menuTypeID = 3;
    $toolName = $langCommonDocs;
} elseif (defined('MY_DOCUMENTS')) {
    if (!get_config('mydocs_teacher_enable')) {
        redirect_to_home_page();        
    }
    if (!get_config('mydocs_student_enable')) {
        redirect_to_home_page();        
    }
    $menuTypeID = 1;
    $toolName = $langMyDocs;
} else {
    $menuTypeID = 2;
    $toolName = $langDoc;
}

load_js('tools.js');

$pageName = $langCreateDoc;

$uploadPath = $editPath = null;
if (isset($_GET['uploadPath'])) {
    $uploadPath = $_GET['uploadPath'];
} elseif (isset($_GET['editPath'])) {
    $editPath = $_GET['editPath'];
    $uploadPath = my_dirname($editPath);
}

if (defined('EBOOK_DOCUMENTS')) {
    $navigation[] = array('url' => 'edit.php?course=' . $course_code . '&amp;id=' . $ebook_id, 'name' => $langEBookEdit);
}

$backUrl = documentBackLink($uploadPath);

$navigation[] = array('url' => $backUrl, 'name' => $toolName);

$data = compact('can_upload', 'group_hidden_input', 'upload_target_url', 'backUrl', 'menuTypeID');
$data['backButton'] = action_bar(array(
    array('title' => $langBack,
          'url' => $backUrl,
          'icon' => 'fa-reply',
          'level' => 'primary-label',
          'class' => 'back_btn')));

if ($editPath) {
    $pageName = $langEditDoc;
    $info = Database::get()->querySingle("SELECT * FROM document WHERE $group_sql AND path = ?s", $editPath);
    $data['title'] = Session::has('file_title') ? Session::get('file_title') : $info->title;
    $fileContent = Session::has('file_content') ? Session::get('file_content') : getHtmlBody($basedir . $info->path);
    $data['pathName'] = 'editPath';
    $data['pathValue'] = $editPath;
    $data['filename'] = $info->filename;
} else {
    $pageName = $langCreateDoc;
    $data['title'] = '';
    $fileContent = Session::has('file_content') ? Session::get('file_content') : '';
    $data['pathName'] = 'uploadPath';
    $data['pathValue'] = $uploadPath;
    $data['filename'] = '';
}
$data['rich_text_editor'] = rich_text_editor('file_content', 20, 40, $fileContent);

if (isset($_GET['ebook_id'])){
    $sections = Database::get()->queryArray("SELECT id, public_id, title FROM ebook_section
                       WHERE ebook_id = ?d
                       ORDER BY CONVERT(public_id, UNSIGNED), public_id", $_GET['ebook_id']);
    $data['section_id'] = '';
    if ($editPath) {
        $section = Database::get()->querySingle("SELECT section_id
            FROM ebook_subsection WHERE file_id = ?d", $info->id);
        if ($section) {
            $data['section_id'] = $section->section_id;
        }
    } else {
        if (count($sections)) {
            $data['section_id'] =  $sections[0]->id;
        }
    }
    $sections_array = array('' => '---');
    foreach ($sections as $sid => $section){
        $sid = $section->id;
        $qsid = $section->public_id;
        $sections_array[$sid] = $qsid . '. ' . ellipsize($section->title, 25);
    }
    $data['sections'] = $sections_array;
} else {
    $data['sections'] = null;
}

view('modules.document.new', $data);


function getHtmlBody($path) {
    $dom = new DOMDocument();
    $dom->loadHTMLFile($path);
    $body = $dom->getElementsByTagName('body')->item(0);
    return dom_save_html($dom, $body);
}
