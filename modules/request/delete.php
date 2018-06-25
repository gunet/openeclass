<?php
/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
 * ========================================================================
 */

$require_login = true;
$require_editor = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'modules/request/functions.php';
require_once 'include/log.class.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_GET['id'])) {
    $id = getDirectReference($_GET['id']);
    $request = Database::get()->querySingle('SELECT * FROM request
        WHERE id = ?d AND course_id = ?d',
        $id, $course_id);
    if ($request) {
        Log::record($course_id, MODULE_ID_ANNOUNCE, LOG_DELETE, [
            'id' => $request->id,
            'title' => $request->title,
            'content' => $request->description ]);
        Database::get()->querySingle('DELETE FROM request WHERE id = ?d', $id);
        Session::Messages(trans('langRequestDeleted'), 'alert-info');
    }
}
redirect_to_home_page("modules/request/?course=$course_code");
