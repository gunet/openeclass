<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*===========================================================================
	upload.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================
        @Description: Upload form that aids the user to select
		  a file to upload and add some metadata with it.

    The script shows a form with a "Browse file" tag and some simpl
    inputs for metadata. The actual uploading takes place at document.php
==============================================================================*/

$require_current_course = TRUE;
$require_login = true;

include "../../include/baseTheme.php";
$tool_content = "";

if($is_adminOfCourse) {

	if(!isset($_REQUEST['uploadPath'])) {
		$_REQUEST['uploadPath'] = "";
	}
	
	$nameTools = $langDownloadFile;
	$navigation[]= array ("url"=>"document.php", "name"=> $langDoc);
	$tool_content .= "
	<form action='document.php' method='post' enctype='multipart/form-data'>
	<input type='hidden' name='uploadPath' value='".htmlspecialchars($_REQUEST['uploadPath'])."' />
	<table width='99%'>
	<tbody>
	<tr>
	<th class='left' width='300'>&nbsp;</th>
	<td><b>$dropbox_lang[uploadFile]</b></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'>$langPathUploadFile:</th>
	<td><input type='file' name='userFile' size='35' class='FormData_InputText' /></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'>$langTitle:</th>
	<td><input type='text' name='file_title' value='' size='40' class='FormData_InputText' /></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
		<th class='left'>$langComment:</th>
		<td><input type='text' name='file_comment' value='' size='40' class='FormData_InputText' /></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'>$langCategory:</th>
	<td>
		<select name='file_category' class='auth_input'>
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
		<td><input type='hidden' name='file_creator' value='$prenom $nom' size='40' /></td>
	</tr>
	<tr>
	<th class='left'>$langSubject:</th>
	<td><input type='text' name='file_subject' value='' size='40' class='FormData_InputText' /></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'>$langDescription:</th>
	<td><input type='text' name='file_description' value='' size='40' class='FormData_InputText' /></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'>$langAuthor:</th>
	<td><input type='text' name='file_author' value='' size='40' class='FormData_InputText' /></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'><input type='hidden' name='file_date' value='' size='40' />
		<input type='hidden' name='file_format' value='' size='40' />
		$langLanguage:
	</th>
	<td>
		<select name='file_language' class='auth_input'>
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
	<th class='left'>$langCopyrighted:</th>
	<td>
		<input name='file_copyrighted' type='radio' value='0' checked='checked' /> $langCopyrightedUnknown&nbsp;
			<input name='file_copyrighted' type='radio' value='2' /> $langCopyrightedFree&nbsp;
		<input name='file_copyrighted' type='radio' value='1' /> $langCopyrightedNotFree
	</td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'>$langUncompress</th>
	<td> <input type='checkbox' name='uncompress' value='1' /> </td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left'>&nbsp;</th>
	<td colspan='2'><input type='submit' value='$langUpload' /><p align='right'><small>$langNotRequired<br />$langMaxFileSize ".
        ini_get('upload_max_filesize')."</small></p></td>
	</tr>
	</tbody>
	</table>
	<br/>";
	$tool_content .=  "</form>";
} else {
	$tool_content .= "<span class='caution_small'>$langNotAllowed</span>";
}

draw($tool_content, '2');

?>
