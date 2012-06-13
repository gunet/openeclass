<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


/*===========================================================================
	upload.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================
        @Description: Upload form that aids the user to select
		  a file to upload and add some metadata with it.

    The script shows a form with a "Browse file" tag and some simple
    inputs for metadata. The actual uploading takes place at document.php
==============================================================================*/

$require_current_course = true;
$require_login = true;
define('EBOOK', 2);
require_once '../../include/baseTheme.php';

if (isset($_GET['uploadPath'])) {
        $uploadPath = q($_GET['uploadPath']);
} else {
        $uploadPath = '';
}

$can_upload = $is_editor;
if (defined('GROUP_DOCUMENTS')) {
        require_once 'modules/group/group_functions.php';
        initialize_group_id();
        initialize_group_info($group_id);
	$can_upload = $can_upload || $is_member;
        $group_hidden_input = "<input type='hidden' name='group_id' value='$group_id' />";
        $navigation[] = array ('url' => 'group.php?course='.$course_code, 'name' => $langGroups);
        $navigation[] = array ('url' => 'group_space.php?course='.$course_code.'&amp;group_id=' . $group_id, 'name' => q($group_name));
	$navigation[] = array ('url' => "document.php?course=$course_code&amp;group_id=$group_id&amp;openDir=$uploadPath", 'name' => $langDoc);
} elseif (defined('EBOOK_DOCUMENTS')) {
	if (isset($_REQUEST['ebook_id'])) {    
            $ebook_id = intval($_REQUEST['ebook_id']);
        }
	$subsystem = EBOOK;
        $subsystem_id = $ebook_id;
        $group_sql = "course_id = $course_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id";
        $group_hidden_input = "<input type='hidden' name='ebook_id' value='$ebook_id' />";
} else {
	$navigation[] = array ('url' => "document.php?course=$course_code&amp;openDir=$uploadPath", 'name' => $langDoc);
        $group_hidden_input = '';
}

if ($can_upload) {
	$nameTools = $langDownloadFile;
	$tool_content .= "
	<form action='document.php?course=$course_code' method='post' enctype='multipart/form-data'>
        <fieldset>
	<input type='hidden' name='uploadPath' value='$uploadPath' />
        $group_hidden_input
        <legend>$dropbox_lang[uploadFile]</legend>
	<table class='tbl' width='100%'>
	<tr>
	  <th width='200'>$langPathUploadFile:</th>
	  <td><input type='file' name='userFile' size='35' /></td>
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
	  <td><input type='hidden' name='file_creator' value='$_SESSION[prenom] $_SESSION[nom]' size='40' /></td>
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
	  <td>
	    <input name='file_copyrighted' type='radio' value='0' checked='checked' /> $langCopyrightedUnknown&nbsp;
	    <input name='file_copyrighted' type='radio' value='2' /> $langCopyrightedFree&nbsp;
	    <input name='file_copyrighted' type='radio' value='1' /> $langCopyrightedNotFree
	  </td>
	  <td>&nbsp;</td>
	</tr>
	<tr>
	  <th>$langUncompress</th>
	  <td><input type='checkbox' name='uncompress' value='1' /> </td>
	  <td>&nbsp;</td>
	</tr>
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
          <div class='right smaller'>$langNotRequired<br />$langMaxFileSize ".
        ini_get('upload_max_filesize')."</div>";
	$tool_content .=  "\n        </form>";
} else {
	$tool_content .= "<span class='caution'>$langNotAllowed</span>";
}

draw($tool_content, '2');
