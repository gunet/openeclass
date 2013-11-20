<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
} else {
        $charset = 'UTF-8';
}
$crlf="\r\n";

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=listusers.csv");

echo join(';', array_map("csv_escape", array($langSurname, $langName, $langEmail, $langAm, $langUsername, $langGroups))),
     $crlf;
$sql = db_query("SELECT user.id AS user_id, user.surname, user.givenname, user.email, user.am, user.username
                        FROM course_user, user
                        WHERE `user`.`user_id` = `course_user`.`user_id` AND
                              `course_user`.`course_id` = $course_id
                        ORDER BY user.surname, user.givenname");
$r=0;
while ($r < mysql_num_rows($sql)) {
        $a = mysql_fetch_array($sql);
        echo "$crlf";
        $f = 1;
        while ($f < mysql_num_fields($sql)) {
                if ($f > 1) {
                        echo ';';
                }
                echo csv_escape($a[$f]);
                $f++;
        }
        echo ';';
        echo csv_escape(user_groups($course_id, $a['user_id'], 'txt'));
        $r++;
}
echo "$crlf";
