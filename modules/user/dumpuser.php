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
require_once 'include/lib/csv.class.php';

$csv = new CSV();
if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . '_users.csv';

$csv->outputRecord($langSurname, $langName, $langEmail, $langAm, $langUsername, $langGroups);

$sql = Database::get()->queryFunc("SELECT user.id, user.surname, user.givenname, user.email, user.am, user.username
                        FROM course_user, user
                        WHERE `user`.`id` = `course_user`.`user_id` AND
                              `course_user`.`course_id` = ?d
                        ORDER BY user.surname, user.givenname",
            function ($item) use ($csv, $course_id) {
                $csv->outputRecord($item->surname, $item->givenname,
                    $item->email, $item->am, $item->username,
                    user_groups($course_id, $item->id, 'txt'));
            }, $course_id);
