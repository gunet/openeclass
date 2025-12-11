<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2025, Greek Universities Network - GUnet
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

require_once '../../../include/lib/course.class.php';
require_once '../../../include/lib/fileManageLib.inc.php';
require_once '../../../include/lib/fileDisplayLib.inc.php';
require_once '../../../include/lib/forcedownload.php';
require_once '../../../modules/document/doc_init.php';

function api_method($access) {
    global $webDir, $course_code, $course_id;

    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }

    if (isset($_GET['course_id'])) {
        $course_code = $_GET['course_id'];
        $course_id = course_code_to_id($course_code);

        if (!$course_id) {
            Access::error(404, "Error: course with code '$course_code' not found");
        } elseif (!$access->allCourses and !in_array($course_code, $access->courseCodes)) {
            Access::error(403, "Error: course with code '$course_code' is not accessible by this app");
        } else {
            $_SERVER['SCRIPT_NAME'] = '';
            doc_init();
            if (($_GET['format'] ?? null) == 'zip') {
                $zip_filename = $course_code . '.zip';
                $dload_filename = $webDir . '/courses/temp/' . safe_filename('zip');
                zip_documents_directory($dload_filename, '/', true);
                send_file_to_client($dload_filename, $zip_filename, null, true, true);
            } else {
                $documents = array_map(function ($document) {
                    $public_path = public_file_path($document->path, $document->filename);
                    if ($document->format == '.dir') {
                        $type = 'dir';
                    } elseif ($document->format == '.meta') {
                        $type = 'meta';
                    } else {
                        $type = 'file';
                    }
                    return [
                        'filename' => $document->filename,
                        'path' => $public_path,
                        'visible' => $document->visible? true: false,
                        'title' => $document->title ?? '',
                        'comment' => $document->comment ?? '',
                        'type' => $type ];
                }, Database::get()->queryArray('SELECT id, filename, title, path, extra_path, visible, comment
                    FROM document WHERE course_id = ?d AND subsystem = 0
                    ORDER BY path',
                    $course_id));
                echo json_encode($documents, JSON_UNESCAPED_UNICODE);
            }
            exit;
        }
    } else {
        Access::error(400, 'Required parameter missing: course_id');
    }
}

chdir('..');
require_once 'apiCall.php';
