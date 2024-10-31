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

$require_login = true;
$require_current_course = true;
require_once '../../include/baseTheme.php';

deleteContent($_GET['id']);

Session::flash('message',$langH5pDeleteSuccess);
Session::flash('alert-class', 'alert-success');
redirect($urlAppend . 'modules/h5p/index.php?course=' . $course_code);

function deleteContent($contentId): bool {
    global $course_id, $course_code, $webDir;

    $editorTmpDir = $webDir . "/courses/h5p/editor/";
    $contentDir = $webDir . "/courses/" . $course_code . "/h5p/content/" . $contentId;
    $filesDir = $contentDir . "/workspace/content";
    $contentDirMod = $webDir . "/courses/h5p/content/" . $contentId;

    foreach (scandir($filesDir) as $didx => $dir) {
        if (!in_array($dir,array(".", "..")) && is_dir($filesDir . "/" . $dir)) {
            foreach (scandir($filesDir . "/" . $dir) as $fidx => $file) {
                if (!in_array($file,array(".", "..")) && is_file($filesDir . "/" . $dir . "/" . $file)) {
                    if (file_exists($editorTmpDir . $dir . "/" . $file)) {
                        unlink($editorTmpDir . $dir . "/" . $file);
                    }
                }
            }
        }
    }

    H5PCore::deleteFileTree($contentDir);
    Database::get()->query("DELETE FROM h5p_content WHERE course_id = ?d AND id = ?d ", $course_id, $contentId);
    Database::get()->query("DELETE FROM h5p_content_dependency WHERE content_id = ?d ", $contentId);
    return H5PCore::deleteFileTree($contentDirMod);
}
