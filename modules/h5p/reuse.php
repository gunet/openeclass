<?php
/*
 * ========================================================================
 * Open eClass 3.13 - E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2022  Greek Universities Network - GUnet
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
require_once 'include/lib/forcedownload.php';

// validate
$content_id = intval($_GET['id']);
$onlyEnabledWhere = ($is_editor) ? '' : " AND enabled = 1 ";
$content = Database::get()->querySingle("SELECT * FROM h5p_content WHERE id = ?d AND course_id = ?d $onlyEnabledWhere", $content_id, $course_id);
if (!$content) {
    exit;
}

$real_filename = $content->title . '.h5p';
$dload_filename = $webDir . '/courses/temp/' . safe_filename('zip');
$contentDir = $webDir . '/courses/' . $course_code . '/h5p/content/' . $content_id . '/workspace';
$libsDir = $webDir . '/courses/h5p/libraries/';
validateMainH5pJson($contentDir);
zip_h5p_package($dload_filename, $contentDir, $libsDir);
send_file_to_client($dload_filename, $real_filename, null, true, true);
exit;

function zip_h5p_package($zip_filename, $contentDir, $libsDir) {
    $zipFile = new ZipArchive();
    $zipFile->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // handle content
    zip_add_files($zipFile, $contentDir, strlen($contentDir . "/"));

    // handle dependencies
    $jsonpath = $contentDir . "/h5p.json";
    $jsonfile = file_get_contents($jsonpath);
    $depjson = json_decode($jsonfile, true);
    foreach ($depjson['preloadedDependencies'] as $dep) {
        $machinename = $dep['machineName'];
        $majorVersion = $dep['majorVersion'];
        $minorVersion = $dep['minorVersion'];
        $libDir = $libsDir . $machinename . '-' . $majorVersion . '.' . $minorVersion;
        zip_add_files($zipFile, $libDir, strlen($libsDir));
    }

    if (!$zipFile->close()) {
        die("Error while creating ZIP file!");
    }
}

function zip_add_files($zipFile, $filesDir, $excludeLen) {
    // Create recursive directory iterator for h5p content
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($filesDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        // Get real and filename to be added for current file
        $filePath = fix_directory_separator($file->getRealPath());
        $localPath = substr($filePath, $excludeLen);

        // ignore editor entries
        $editor = "editor";
        if (substr($localPath, 0, strlen($editor)) === $editor) {
            continue;
        }

        // Skip directories (they will be added automatically)
        if (!$file->isDir()) {
            // Add current file to archive
            $zipFile->addFile($filePath, $localPath);
        }
    }
}

function validateMainH5pJson($contentDir) {
    $jsonpath = $contentDir . "/h5p.json";
    $jsonfile = file_get_contents($jsonpath);
    $data = json_decode($jsonfile, true);
    $update = false;
    if (!isset($data["language"])) {
        $update = true;
        $data["language"] = "und";
    }
    if (!isset($data["embedTypes"])) {
        $update = true;
        $data["embedTypes"] = array("div");
    }
    if ($update) {
        file_put_contents($jsonpath, json_encode($data));
    }
}
