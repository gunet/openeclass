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

if (!defined('COMMON_DOCUMENTS')) {
        $require_current_course = true;
        $require_login = true;
}

require_once "../../include/baseTheme.php";
require_once "modules/document/doc_init.php";

load_js('tools.js');

$uploadPath = $editPath = false;
if (isset($_GET['uploadPath'])) {
    $uploadPath = q($_GET['uploadPath']);
} elseif (isset($_GET['editPath'])) {
    $editPath = q($_GET['editPath']);
    $uploadPath = dirname($editPath);
}

$action = defined('COMMON_DOCUMENTS')? 'commondocs.php?': 'document.php?';
$navigation[] = array('url' => $action . $groupset . "openDir=$uploadPath", 'name' => $pageName);

if ($can_upload) {
    if ($editPath) {
        $pageName = $langEditDoc;
        $info = Database::get()->querySingle("SELECT * FROM document WHERE $group_sql AND path = ?s", $editPath);
        $htmlFileName = q($info->filename);
        $htmlTitle = ' value="' . q($info->title) . '"';
        $fileContent = file_get_contents($basedir . $info->path);
	    $htmlPath = "<input type='hidden' name='editPath' value='$editPath'>";
    } else {
        $pageName = $langCreateDoc;
        $htmlFileName = "<input type='text' name='file_name' size='40'>";
        $htmlTitle = '';
        $fileContent = '';
	    $htmlPath = "<input type='hidden' name='uploadPath' value='$uploadPath'>";
    }
    $action = defined('COMMON_DOCUMENTS')? 'commondocs': 'document';
    $tool_content .= "<form action='$upload_target_url' method='post'>
<fieldset>
    <legend>$pageName</legend>
    $htmlPath
    $group_hidden_input
	<table class='tbl' width='100%'>
	<tr>
	  <th>$langFileName:</th>
	  <td>$htmlFileName</td>
	</tr>
	<tr>
	  <th>$langTitle:</th>
	  <td><input type='text' name='file_title'$htmlTitle size='40'></td>
	</tr>
	<tr>
	  <th>$langContent:</th>
	  <td>" . rich_text_editor('file_content', 20, 40, $fileContent) . "</td>
    </tr>
	<tr>
      <td class='right' colspan=2>
        <input class='btn btn-primary' type='submit' value='" . $langSubmit . "'>
      </td>
    </tr>
  </table>
</fieldset>
</form>";
} else {
	$tool_content .= "<div class='alert alert-danger'>$langNotAllowed</div>";
}

draw($tool_content, 
    defined('COMMON_DOCUMENTS')? 3: 2,
    null, $head_content);
