<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

$require_current_course = TRUE;
$require_editor = TRUE;

define('CLARO_FILE_PERMISSIONS', 0777);

require_once "include/baseTheme.php";

require_once "include/lib/fileManageLib.inc.php";
require_once "include/lib/fileUploadLib.inc.php";
require_once "include/lib/fileDisplayLib.inc.php";
require_once "imsqtilib.php";

$pwd = getcwd();

$pageName = $langImportQTI;

// error handling
$errorFound = false;

// init msg arays
$okMsgs = array();
$errorMsgs = array();

$msgs = array();

//3 MB
$maxFileSize = 3145728; 
$allowed_file_types =  array('xml');

// handle upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($_POST)) {

    /*
     * Check file
     */

    if (!isset($_FILES['uploadedPackage']) || !is_uploaded_file($_FILES['uploadedPackage']['tmp_name'])) {
      $errorFound = true;
      array_push($errorMsgs, $langFileScormError);
    }
    elseif ($_FILES['uploadedPackage']['size'] > $maxFileSize) {
      $errorFound = true;
      array_push($errorMsgs, $langMaxFileSize . ": &nbsp;" . $maxFileSize/1024/1024 . "MB" );
    } elseif (!in_array(pathinfo($_FILES['uploadedPackage']['name'], PATHINFO_EXTENSION), $allowed_file_types)) {
      $errorFound = true;
      array_push($errorMsgs, $langUploadWhitelist . ": &nbsp;" . implode(', ', $allowed_file_types));
    } 

   if (!$errorFound) {	
    $msgs = qti_import_file_form_submit(file_get_contents($_FILES['uploadedPackage']['tmp_name']), $course_id);
  } 

    /* --------------------------------------
      status messages
      -------------------------------------- */

      $tool_content .= "\n<p><!-- Messages -->\n";
      foreach ($okMsgs as $msg) {
        $tool_content .= "<i class='fa fa-check-square'></i>&nbsp;$langSuccessOk&nbsp;" . $msg . "<br />";
      }

      foreach ($errorMsgs as $msg) {
        $tool_content .= "<i class='fa fa-close'></i>&nbsp;$langError&nbsp;" . $msg . "<br />";
      }

      foreach ($msgs as $msg) {
        if($msg[0]) {
          $tool_content .= "<i class='fa fa-check-square'></i>&nbsp;$langSuccessOk&nbsp;" . $msg[1] . "<br />";
        } else {
          $tool_content .= "<i class='fa fa-close'></i>&nbsp;$langError&nbsp;" . $msg[1] . "<br />";
        }
      }

      $tool_content .= "\n<!-- End messages -->\n";
      $tool_content .= "\n<br /><a href='question_pool.php?course=$course_code'>$langBack</a></p>";

} else {
    // if method == 'post'
    // don't display the form if user already sent it
    /* --------------------------------------
      UPLOAD FORM
      -------------------------------------- */
    $tool_content .= "
      <form enctype=\"multipart/form-data\" action=\"" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code&importIMSQTI=yes\" method=\"post\">
      <fieldset>
      <legend>$langImport</legend>
      <table width=\"100%\" class=\"tbl\">
      <tr>
      <th width=\"120\">$langFileName:</th>
      <td>
      <input type=\"hidden\" name=\"qtiFormId\" value=\"" . uniqid('') . "\" >
      <input type=\"file\" name=\"uploadedPackage\">
      <br />
      <span class='smaller'>$langIMSQTIUploadFile</span>
      </td>
      </tr>
      <tr>
      <th class=\"left\">&nbsp;</th>
      <td class='right'><input type=\"submit\" value=\"" . $langImport . "\"></td>
      </tr>
      <tr>
      <th>&nbsp;</th>
      <td class='right smaller'>$langMaxFileSize " . $maxFileSize/1024/1024 . "&nbsp;MB</td>
      </tr>
      </table>
      </fieldset>
      </form>
      <p>";

}

chdir($pwd);

