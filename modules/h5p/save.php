<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_current_course = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'classes/H5PFactory.php';
require_once 'include/lib/fileUploadLib.inc.php';

uploadContent();
header("location: index.php");

/**
 * @brief upload h5p content
 * @return bool
 */
function uploadContent(): bool {
    global $course_id, $course_code, $webDir;

    $upload_dir = $webDir . '/courses/temp/h5p/' . $course_code;
    if (file_exists($upload_dir)) {
        H5PCore::deleteFileTree($upload_dir);
    }
    mkdir($upload_dir, 0755, true);
    if (getPureFileExtension($_FILES['userFile']['name']) != 'h5p') {
        Session::flash('message', trans('langUploadedFileNotAllowed') . " <b>" .
            q($_FILES['userFile']['name']) . "</b>");
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/h5p/index.php?course=' . $course_code);
    }
    $target_file = $upload_dir . "/" . my_basename($_FILES["userFile"]["name"]);
    move_uploaded_file($_FILES["userFile"]["tmp_name"], $target_file);

    $factory = new H5PFactory();
    $framework = $factory->getFramework();

    $h5pPath = $webDir . '/courses/h5p';
    $classCore = new H5PCore($framework, $h5pPath, $upload_dir, 'en', FALSE);
    $classVal = new H5PValidator($framework, $classCore);
    $classStor = new H5PStorage($framework, $classCore);

    if ($classVal->isValidPackage()) {
        $classStor->savePackage();
        $content_id = Database::get()->querySingle("SELECT * FROM h5p_content WHERE course_id = ?d ORDER BY id DESC", $course_id)->id;

        // handle package title
        Database::get()->query("UPDATE h5p_content SET title = ?s WHERE id = ?d AND course_id = ?d", $classCore->mainJsonData["title"], $content_id, $course_id);

        // handle package final location and contents
        $courseH5pBase = $webDir . "/courses/" . $course_code . "/h5p";
        $courseH5pWorkspace = $courseH5pBase . "/content/" . $content_id . "/workspace";
        $courseH5pContent = $courseH5pWorkspace . "/content";
        $srcH5pContent = $h5pPath . "/content/" . $content_id;

        mkdir($courseH5pContent, 0755, true);
        recurse_copy($srcH5pContent, $courseH5pContent);
        $fp = fopen($courseH5pWorkspace . "/h5p.json", "w");
        fwrite($fp, json_encode($classCore->mainJsonData));
        fclose($fp);

        return true;
    } else {
        foreach ($framework->getMessages('error') as $errObj) {
            Session::flash('message', $errObj->code . ": " . $errObj->message);
            Session::flash('alert-class', 'alert-warning');
        }
        return false;
    }
}
