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
require_once 'include/lib/forcedownload.php';

// validate
$content_id = intval($_GET['id']);
$onlyEnabledWhere = ($is_editor) ? '' : " AND enabled = 1 ";
$content = Database::get()->querySingle("SELECT * FROM h5p_content WHERE id = ?d AND course_id = ?d $onlyEnabledWhere", $content_id, $course_id);
if (!$content) {
    exit;
}
if (!$content->reuse_enabled) {
    exit;
}

$real_filename = $content->title . '.h5p';
$dload_filename = $webDir . '/courses/temp/' . safe_filename('zip');
$contentDir = $webDir . '/courses/' . $course_code . '/h5p/content/' . $content_id . '/workspace';
$libsDir = $webDir . '/courses/h5p/libraries/';
validateMainH5pJson($contentDir, $content->title);
zip_h5p_package($dload_filename, $contentDir, $libsDir);
send_file_to_client($dload_filename, $real_filename, null, true, true);
exit;

function libToFolder($lib): string {
    return $lib['machineName'] . '-' . $lib['majorVersion'] . '.' . $lib['minorVersion'];
}

function getDepJson(string $jsonpath): mixed {
    $jsonfile = file_get_contents($jsonpath);
    return json_decode($jsonfile, true);
}

function zip_h5p_package($zip_filename, $contentDir, $libsDir) {
    $zipFile = new ZipArchive();
    $zipFile->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // handle content
    zip_add_files($zipFile, $contentDir, strlen($contentDir . "/"));

    // handle dependencies
    $depjson = getDepJson($contentDir . "/h5p.json");
    $depsarray = [];

    // handle preloadedDependencies and editorDependencies, until 2nd level of dependency tree
    $depkeys = ['preloadedDependencies', 'editorDependencies'];
    foreach ($depjson['preloadedDependencies'] as $dep) {
        $depFolder = libToFolder($dep);
        $depsarray[] = $depFolder;
        $libdepjson = getDepJson($libsDir . $depFolder . "/library.json");
        foreach ($depkeys as $depkey) {
            if (isset($libdepjson[$depkey])) {
                foreach ($libdepjson[$depkey] as $libdep) {
                    $libdepFolder = libToFolder($libdep);
                    $depsarray[] = $libdepFolder;
                    $lib2depjson = getDepJson($libsDir . $libdepFolder . "/library.json");
                    foreach ($depkeys as $dep2key) {
                        if (isset($lib2depjson[$dep2key])) {
                            foreach ($lib2depjson[$dep2key] as $lib2dep) {
                                $depsarray[] = libToFolder($lib2dep);
                            }
                        }
                    }
                }
            }
        }
    }

    // zip all resolved dependencies
    foreach (array_unique($depsarray) as $dep) {
        zip_add_files($zipFile, $libsDir . $dep, strlen($libsDir));
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

function validateMainH5pJson($contentDir, $contentTitle) {
    $jsonpath = $contentDir . "/h5p.json";
    $jsonfile = file_get_contents($jsonpath);
    $data = json_decode($jsonfile, true);
    $update = false;
    if (!isset($data["title"])) {
        $update = true;
        if (isset($contentTitle) && strlen($contentTitle) > 0) {
            $data["title"] = $contentTitle;
        } else {
            $data["title"] = "und";
        }
    }
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
