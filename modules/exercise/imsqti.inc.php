<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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

$allowed_file_types =  array('xml');

// handle upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($_POST)) {

    /*
     * Check file
     */
    if (!isset($_FILES['uploadedPackage']) || !is_uploaded_file($_FILES['uploadedPackage']['tmp_name'])) {
      $errorFound = true;
      array_push($errorMsgs, $langFileScormError);
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

      foreach ($okMsgs as $msg) {
        $tool_content .= "<div class='alert alert-success'>$langImportQTIAnswer&nbsp;" . $msg . "</div>";
      }
      foreach ($errorMsgs as $msg) {
        $tool_content .= "<div class='alert alert-warning'>$langError&nbsp;" . $msg . "</div>";
      }

      foreach ($msgs as $msg) {
        if ($msg[0]) {
            $tool_content .= "<div class='alert alert-success'>$langImportQTIAnswer&nbsp;(" . $msg[1] . ")</div>";
          } else {
            $tool_content .= "<div class='alert alert-warning'>$langError&nbsp;" . $msg[1] . "</div>";
          }
      }
      
      $tool_content .= "<div class='text-center' style='margin-top:15px;'><a href='question_pool.php?course=$course_code'>$langBack</a></div>";

} else {
    // if method == 'post'
    // don't display the form if user already sent it
    /* --------------------------------------
      UPLOAD FORM
      -------------------------------------- */
    $tool_content .= 
        "<div class='col-12'><div class='form-wrapper form-edit p-3 rounded'>
            <form class='form-horizontal' role='form' enctype='multipart/form-data' action='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code&importIMSQTI=yes' method='post'>
                <input type='hidden' name='qtiFormId' value='" . uniqid('') . "' >
                    <h4>$langImport</h4>
                    <div class='form-group mt-3'>
                        <label class='col-sm-6 control-label-notes'>$langIMSQTIUploadFile:</label>
                        <div class='col-sm-12'>
                            <input type='file' name='uploadedPackage'>                            
                        </div>
                    </div>
                <div class='form-group mt-3'>
                    <div class='col-sm-offset-3 col-sm-9'>
                        <input type='submit' value='" . $langImport . "'>
                        <span class='help-block'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</small>
                    </div>
                </div>
            </form>
        </div></div>";
}
chdir($pwd);