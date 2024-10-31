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
require_once 'include/lib/forcedownload.php';
require_once 'modules/request/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $comment = Database::get()->querySingle('SELECT filename, real_filename FROM request, request_action
        WHERE request_action.id = ?d AND course_id = ?d AND request.id = request_id',
        $id, $course_id);
    if (!$comment or !$comment->real_filename) {
        not_found($_SERVER['REQUEST_URI']);
    }
    $filePath = "$webDir/courses/$course_code/request/" . $comment->real_filename;
    send_file_to_client($filePath, $comment->filename, 'inline', true);
} else {
    not_found($_SERVER['REQUEST_URI']);
}
