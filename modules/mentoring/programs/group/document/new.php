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


 $require_login = true;

require_once "../../../../../include/baseTheme.php";
require_once "modules/mentoring/programs/group/document/mentoring_doc_init.php";
require_once 'modules/mentoring/functions.php';

if(defined('MENTORING_GROUP_DOCUMENTS')){
    mentoring_program_access();
}

mentoring_doc_init();

$user_upload = $uid && $subsystem == MENTORING_GROUP && get_config('enable_docs_public_write') && mentoring_setting_get($program_group_id);
$uploading_as_user = !$can_upload_mentoring && $user_upload;

$menuTypeID = 2;
$toolName = $langDoc;


load_js('tools.js');

$pageName = $langCreateDoc;

$uploadPath = $editPath = null;
if (isset($_GET['uploadPath'])) {
    $uploadPath = q($_GET['uploadPath']);
} elseif (isset($_GET['editPath'])) {
    $editPath = q($_GET['editPath']);
    $uploadPath = my_dirname($editPath);
}

$backUrl = documentBackLink($uploadPath);
$back = '';

$navigation[] = array('url' => $backUrl, 'name' => $toolName);

$data = compact('can_upload_mentoring','uploading_as_user', 'group_hidden_input', 'upload_target_url', 'backUrl', 'menuTypeID', 'back');
$data['backButton'] = action_bar(array(
    array('title' => $langBackPage,
          'url' => $backUrl,
          'icon' => 'fa-chevron-left',
          'level' => 'primary-label',
          'button-class' => 'backButtonMentoring',
          'class' => 'back_btn')));

if ($editPath) {
    $pageName = $langEditDoc;
    $info = Database::get()->querySingle("SELECT * FROM mentoring_document WHERE $group_sql AND path = ?s", $editPath);
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
$data['sections'] = null;

if(!defined('MENTORING_MYDOCS') and !isset($_GET['editPathMydoc']) and !defined('MENTORING_COMMON_DOCUMENTS') and !isset($_GET['editPathCommon'])){
    $data['group_id'] = $program_group_id; 
    $data['is_group_doc'] = 1;

    $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$program_group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }

}else{
    $data['is_group_doc'] = 0;
}

if(isset($_GET['editPathMydoc']) or isset($_GET['editPathCommon'])){
    $data['is_group_doc'] = 0;
}

view('modules.mentoring.programs.group.document.new', $data);

function getHtmlBody($path) {
    $dom = new DOMDocument();
    $dom->loadHTMLFile($path);
    $body = $dom->getElementsByTagName('body')->item(0);
    return dom_save_html($dom, $body);
}
