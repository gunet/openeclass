<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * @file upload.php
 * @brief upload form for subsystem documents
 */

$require_admin = defined('COMMON_DOCUMENTS');
$require_current_course = !(defined('COMMON_DOCUMENTS') or defined('MY_DOCUMENTS'));
$require_login = true;

require_once "../../include/baseTheme.php";
require_once "modules/document/doc_init.php";
require_once 'modules/drives/clouddrive.php';

if (defined('COMMON_DOCUMENTS')) {
    $menuTypeID = 3;
    $toolName = $langCommonDocs;
} elseif (defined('MY_DOCUMENTS')) {
    if ($session->status == USER_TEACHER and !get_config('mydocs_teacher_enable')) {
        redirect_to_home_page();        
    }
    if ($session->status == USER_STUDENT and !get_config('mydocs_student_enable')) {
        redirect_to_home_page();
    }
    $menuTypeID = 1;
    $toolName = $langMyDocs;
} else {
    $menuTypeID = 2;
    $toolName = $langDoc;
}

enableCheckFileSize();

if (defined('EBOOK_DOCUMENTS')) {
    $navigation[] = array('url' => 'edit.php?course=' . $course_code . '&amp;id=' . $ebook_id, 'name' => $langEBookEdit);
}

if (isset($_GET['uploadPath'])) {
    $uploadPath = q($_GET['uploadPath']);
} else {
    $uploadPath = '';
}

$backUrl = documentBackLink($uploadPath);

if ($can_upload) {
    $navigation[] = array('url' => $backUrl, 'name' => $pageName);
    $pendingCloudUpload = CloudDriveManager::getFileUploadPending();

    if ($pendingCloudUpload) {
        $group_hidden_input .= "<input type='hidden' name='ext' value='true'>";
        $pageName = $langDownloadFile;
        $fileinput = "
        <div class='form-group'>
          <label for='fileCloudName' class='col-sm-2 control-label'>$langCloudFile</label>
          <div class='col-sm-10'>
            <input type='hidden' class='form-control' id='fileCloudInfo' name='fileCloudInfo' value='".q($pendingCloudUpload)."'>
            <input type='text' class='form-control' name='fileCloudName' value='" . q(CloudFile::fromJSON($pendingCloudUpload)->name()) . "' readonly>
          </div>
        </div>";
    } else if (isset($_GET['ext'])) {
        $group_hidden_input .= "<input type='hidden' name='ext' value='true'>";
        $pageName = $langExternalFile;
        $fileinput = "
        <div class='form-group'>
          <label for='fileURL' class='col-sm-2 control-label'>$langExternalFileInfo:</label>
          <div class='col-sm-10'>
            <input type='text' class='form-control' id='fileURL' name='fileURL'>
          </div>
        </div>";
    } else {
        $pageName = $langDownloadFile;
        $fileinput = "
        <div class='form-group'>
          <label for='userFile' class='col-sm-2 control-label'>$langPathUploadFile:</label>
          <div class='col-sm-10'>" .
                fileSizeHidenInput() .
                CloudDriveManager::renderAsButtons() . "<input type='file' id='userFile' name='userFile'></span>
          </div>
        </div>";
    }
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => $backUrl,
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    $tool_content .= "
        <div class='row'>
            <div class='col-md-12'>
                <div class='form-wrapper'>

        <form class='form-horizontal' role='form' action='$upload_target_url' method='post' enctype='multipart/form-data'>      
          <input type='hidden' name='uploadPath' value='$uploadPath' />
          $group_hidden_input
          $fileinput
      <div class='form-group'>
        <label for='inputFileTitle' class='col-sm-2 control-label'>$langTitle:</label>
        <div class='col-sm-10'>
          <input type='text' class='form-control' id='inputFileTitle' name='file_title'>
        </div>
      </div>

      <div class='form-group'>
        <label for='inputFileComment' class='col-sm-2 control-label'>$langComment:</label>
        <div class='col-sm-10'>
          <input type='text' class='form-control' id='inputFileComment' name='file_comment'>
        </div>
      </div>

      <div class='form-group'>
        <label for='inputFileCategory' class='col-sm-2 control-label'>$langCategory:</label>
        <div class='col-sm-10'>
          <select class='form-control' name='file_category'>
            <option selected='selected' value='0'>$langCategoryOther</option>
            <option value='1'>$langCategoryExcercise</option>
            <option value='2'>$langCategoryLecture</option>
            <option value='3'>$langCategoryEssay</option>
            <option value='4'>$langCategoryDescription</option>
            <option value='5'>$langCategoryExample</option>
            <option value='6'>$langCategoryTheory</option>
          </select>
        </div>

        <input type='hidden' name='file_creator' value='" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "' size='40' />

      </div>

      <div class='form-group'>
        <label for='inputFileSubject' class='col-sm-2 control-label'>$langSubject:</label>
        <div class='col-sm-10'>
          <input type='text' class='form-control' id='inputFileSubject' name='file_subject'>
        </div>
      </div>

      <div class='form-group'>
        <label for='inputFileDescription' class='col-sm-2 control-label'>$langDescription:</label>
        <div class='col-sm-10'>
          <input type='text' class='form-control' id='inputFileDescription' name='file_description'>
        </div>
      </div>

      <div class='form-group'>
        <label for='inputFileAuthor' class='col-sm-2 control-label'>$langAuthor:</label>
        <div class='col-sm-10'>
          <input type='text' class='form-control' id='inputFileAuthor' name='file_author'>
        </div>
      </div>

      <div class='form-group'>
        <input type='hidden' name='file_date' value='' size='40' />
        <input type='hidden' name='file_format' value='' size='40' />

        <label for='inputFileLanguage' class='col-sm-2 control-label'>$langLanguage:</label>
        <div class='col-sm-10'>
          <select class='form-control' name='file_language'>
                <option value='en'>$langEnglish</option>
                <option value='fr'>$langFrench</option>
                <option value='de'>$langGerman</option>
                <option value='el' selected>$langGreek</option>
                <option value='it'>$langItalian</option>
                <option value='es'>$langSpanish</option>
            </select>
        </div>
      </div>

      <div class='form-group'>
        <label for='inputFileCopyright' class='col-sm-2 control-label'>$langCopyrighted:</label>
        <div class='col-sm-10'>
          " .
            selection(array('0' => $langCopyrightedUnknown,
                '2' => $langCopyrightedFree,
                '1' => $langCopyrightedNotFree,
                '3' => $langCreativeCommonsCCBY,
                '4' => $langCreativeCommonsCCBYSA,
                '5' => $langCreativeCommonsCCBYND,
                '6' => $langCreativeCommonsCCBYNC,
                '7' => $langCreativeCommonsCCBYNCSA,
                '8' => $langCreativeCommonsCCBYNCND), 'file_copyrighted', '', 'class="form-control"') . "
        </div>
      </div>";

    if (!isset($_GET['ext'])) {
        $tool_content .= "
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
                <div class='checkbox'>
                    <label>
                        <input type='checkbox' name='uncompress' value='1'>
                        <strong>$langUncompress</strong>
                    </label>
                </div>          
              </div>
        </div>";
    }

    $tool_content .= "
      <div class='form-group'>
        <div class='col-sm-offset-2 col-sm-10'>
            <div class='checkbox'>
                <label>
                    <input type='checkbox' name='replace' value='1'>
                    <strong>$langReplaceSameName</strong>
                </label>
            </div>         
        </div>
      </div>      

    <div class='row'>
        <div class='infotext col-sm-offset-2 col-sm-10 margin-bottom-fat'>$langNotRequired $langMaxFileSize " . ini_get('upload_max_filesize') . "</div>
    </div>";

    $tool_content .= "
      <div class='form-group'>
        <div class='col-xs-offset-2 col-xs-10'>".
            form_buttons(array(
                array(
                    'text' => $langUpload
                ),
                array(
                    'href' => "index.php?course=$course_code",
                )
            ))
            ."
        </div>
      </div>
    </form>

    </div></div></div>";
} else {
    $tool_content .= "<div class='alert alert-warning'>$langNotAllowed</div>";
}

draw($tool_content, $menuTypeID, null, $head_content);
