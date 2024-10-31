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
/**
 * @file message_download.php
 * @brief Download files from message
 */
$require_login = TRUE;
$require_current_course = TRUE;

include '../../include/baseTheme.php';
require_once 'class.msg.php';
require_once 'include/lib/forcedownload.php';

$message_dir = $webDir . "/courses/" . $course_code . "/dropbox";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} else {
    header("Location: $urlServer");
}

$work = new Msg($id, $uid, 'any');
if (!$work->error) {
    $path = $message_dir . "/" . $work->filename; //path to file as stored on server
    $file = $work->real_filename;

    send_file_to_client($path, $file, null, true);
}
exit;
