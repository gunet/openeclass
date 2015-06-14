<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== */

/**
 * @file link.php
 * @brief redirect user to external link
 */

$require_current_course = TRUE;
$require_help = true;
$helpTopic = 'Link';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
$course_id = course_code_to_id($_GET['course']);

$id = getDirectReference($_GET['id']);
if ($course_id !== false) {
    Database::get()->query("UPDATE link SET hits = hits + 1 WHERE course_id = ?d AND id = ?d", $course_id, $id);
    $q = Database::get()->querySingle("SELECT url FROM link WHERE course_id = ?d AND id = ?d",  $course_id, $id);
    if ($q) {
        $url = $q->url;
        header('Location: ' . $url);
        exit;
    }
}
Session::Messages($langAccountResetInvalidLink, 'alert-danger');
header('Location: ' . $urlServer);
