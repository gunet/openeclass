<?php
/*
 * ========================================================================
 * Open eClass 3.11 - E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
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
 *
 * For a full list of contributors, see "credits.txt".
 */

$require_current_course = true;
require_once '../../include/baseTheme.php';
require_once 'classes/H5PFactory.php';

function upload_content() { // anebasma // na elen3w to database & na balw to user_input
    global $course_id, $course_code, $webDir;

    $upload_dir = $webDir . '/courses/temp/h5p/' . $course_code;
    if (file_exists($upload_dir)) {
        H5PCore::deleteFileTree($upload_dir);
    }
    mkdir($upload_dir, 0700, true);

    $target_file = $upload_dir . "/" . basename($_FILES["userFile"]["name"]);
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

        mkdir($courseH5pContent, 0700, true);
        recurse_copy($srcH5pContent, $courseH5pContent);
        $fp = fopen($courseH5pWorkspace . "/h5p.json", "w");
        fwrite($fp, json_encode($classCore->mainJsonData));
        fclose($fp);

        return true;
    } else {
        return false;
    }
}

function show_content($content_id) {
    global $course_code, $webDir;

    $content_dir = $webDir . "/courses/" . $course_code . "/h5p/content/" . $content_id;
    $workspace_dir = $content_dir . "/workspace";
    if (file_exists($workspace_dir)) {
        return true;
    } else {
        $h5p = scandir($content_dir, 1);
        $sentence_we_need = '.h5p';
        foreach ($h5p as $h) {
            if (strpos($h, $sentence_we_need)) {
                $h5p = $h;
            }
        }
        mkdir($workspace_dir);
        $content = $content_dir . "/" . $h5p;

        $zip = new ZipArchive;
        $res = $zip->open($content);
        if ($res) {
            $zip->extractTo($workspace_dir);
            if ($zip->close()) {
                return true;
            } else {
                return false;
            }
        }
    }
}

function delete_content($content_id) {
    global $course_id, $course_code, $webDir;
    $content_dir = $webDir . "/courses/" . $course_code . "/h5p/content/" . $content_id;
    H5PCore::deleteFileTree($content_dir);
    Database::get()->query("DELETE FROM h5p_content WHERE course_id = ?d AND id = ?d ", $course_id, $content_id);
    Database::get()->query("DELETE FROM h5p_content_dependency WHERE content_id = ?d ", $content_id);
    $content_dir_mod = $webDir . "/courses/h5p/content/" . $content_id;
    return H5PCore::deleteFileTree($content_dir_mod);
}