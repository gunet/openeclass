<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
if (!defined('COMMON_DOCUMENTS')) {
    $require_current_course = TRUE;
    $require_login = true;
}
require_once '../../include/baseTheme.php';
require_once 'modules/document/doc_init.php';

if (isset($_GET['uploadPath'])) {
    $uploadPath = q($_GET['uploadPath']);
} else {
    $uploadPath = '';
}

if ($can_upload) {
    $tool_content .= "<div id='operations_container'>
                        <ul id='opslist'>
                           <li><a href='index.php?course=$course_code'>$langBack</a></li>
                        </ul>
                    </div>";
    if (isset($_GET['ext'])) {
        $group_hidden_input .= "<input type='hidden' name='ext' value='true'>";
        $nameTools = $langExternalFile;
        $fileinput = "<th width='200'>$langExternalFileInfo:</th>
                              <td><input type='text' name='fileURL' size='40' /></td>";
    } else {
        $nameTools = $langDownloadFile;
        $fileinput = "<th width='200'>$langPathUploadFile:</th>
                              <td><input type='file' name='userFile' size='35' /></td>";
    }
    $tool_content .= "<form action='$upload_target_url' method='post' enctype='multipart/form-data'>
      <fieldset>
        <legend>$langUpload</legend>
        <input type='hidden' name='uploadPath' value='$uploadPath' />
        $group_hidden_input
        <table class='tbl' width='100%'>
        <tr>
          $fileinput
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th>$langTitle:</th>
          <td><input type='text' name='file_title' value='' size='40' /></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th>$langComment:</th>
          <td><input type='text' name='file_comment' value='' size='40' /></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th>$langCategory:</th>
          <td>
          <select name='file_category'>
            <option selected='selected' value='0'>$langCategoryOther</option>
            <option value='1'>$langCategoryExcercise</option>
            <option value='2'>$langCategoryLecture</option>
            <option value='3'>$langCategoryEssay</option>
            <option value='4'>$langCategoryDescription</option>
            <option value='5'>$langCategoryExample</option>
            <option value='6'>$langCategoryTheory</option>
          </select>
          </td>
          <td>&nbsp;</td>
          <td><input type='hidden' name='file_creator' value='" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "' size='40' /></td>
        </tr>
        <tr>
          <th>$langSubject:</th>
          <td><input type='text' name='file_subject' value='' size='40' /></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th>$langDescription:</th>
          <td><input type='text' name='file_description' value='' size='40' /></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th>$langAuthor:</th>
          <td><input type='text' name='file_author' value='' size='40' /></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th><input type='hidden' name='file_date' value='' size='40' />
              <input type='hidden' name='file_format' value='' size='40' />
                $langLanguage:
          </th>
          <td>
            <select name='file_language'>
                <option value='en'>$langEnglish</option>
                <option value='fr'>$langFrench</option>
                <option value='de'>$langGerman</option>
                <option value='el' selected>$langGreek</option>
                <option value='it'>$langItalian</option>
                <option value='es'>$langSpanish</option>
            </select>
          </td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th>$langCopyrighted:</th>
          <td>" .
            selection(array('0' => $langCopyrightedUnknown,
                '2' => $langCopyrightedFree,
                '1' => $langCopyrightedNotFree,
                '3' => $langCreativeCommonsCCBY,
                '4' => $langCreativeCommonsCCBYSA,
                '5' => $langCreativeCommonsCCBYND,
                '6' => $langCreativeCommonsCCBYNC,
                '7' => $langCreativeCommonsCCBYNCSA,
                '8' => $langCreativeCommonsCCBYNCND), 'file_copyrighted') . "          
          </td>
          <td>&nbsp;</td>
        </tr>";
    if (!isset($_GET['ext'])) {
        $tool_content .= "
        <tr>
          <th>$langUncompress</th>
          <td><input type='checkbox' name='uncompress' value='1' /> </td>
          <td>&nbsp;</td>
        </tr>";
    }
    $tool_content .= "
        <tr>
          <th>$langReplaceSameName</th>
          <td><input type='checkbox' name='replace' value='1' /> </td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <th>&nbsp;</th>
        <td colspan='2' class='right'><input type='submit' value='$langUpload' /></td>
        </tr>
        </table>
        </fieldset>
        <div class='right smaller'>$langNotRequired<br />$langMaxFileSize " . ini_get('upload_max_filesize') . "</div>";
    $tool_content .= "</form>";
} else {
    $tool_content .= "<span class='caution'>$langNotAllowed</span>";
}

if (defined('COMMON_DOCUMENTS')) {
    draw($tool_content, 3);
} else {
    draw($tool_content, 2);
}
