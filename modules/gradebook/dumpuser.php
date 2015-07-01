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

$require_current_course = true;
$require_course_admin = true;

include '../../include/init.php';

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
    $sendSep = true;
} else {
    $charset = 'UTF-8';
    $sendSep = false;
}
$crlf = "\r\n";

if (!$is_editor) {
    Session::Messages($langForbidden);
    redirect_to_home_page('modules/glossary/index.php?course=' . $course_code);
}

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=listusers.csv");

if ($sendSep) {
    echo 'sep=;', $crlf;
}

echo join(';', array_map("csv_escape", array($langSurname, $langName, $langEmail, $langAm, $langUsername, $langGroups))),
$crlf;
$sql = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname, user.email, user.am, user.username
                        FROM course_user, user
                        WHERE `user`.`id` = `course_user`.`user_id` AND
                              `course_user`.`course_id` = ?d
                        ORDER BY user.surname, user.givenname", $course_id);

foreach ($sql as $a) {
    echo join(';', array_map("csv_escape", array($a->surname, $a->givenname, $a->email, $a->am, $a->username, user_groups($course_id, $a->id, 'txt'))));    
    echo "$crlf";
}
