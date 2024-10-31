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
$require_editor = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'modules/request/functions.php';
require_once 'include/log.class.php';

if (isset($_POST['id'])) {
    $id = getDirectReference($_POST['id']);
    $request = Database::get()->querySingle('SELECT * FROM request
        WHERE id = ?d AND course_id = ?d',
        $id, $course_id);
    if ($request) {
        Log::record($course_id, MODULE_ID_ANNOUNCE, LOG_DELETE, [
            'id' => $request->id,
            'title' => $request->title,
            'content' => $request->description ]);
        Database::get()->querySingle('DELETE FROM request WHERE id = ?d', $id);
    }
}
