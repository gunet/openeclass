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


$require_login = TRUE;
require_once '../../include/init.php';
header('Content-Type: text/plain; charset=UTF-8');

if (isset($_POST['cid']) and isset($_POST['state'])) {
        $cid = intval($_POST['cid']);
        $state = $_POST['state'];
        if (isset($_POST['password'])) {
                $password = $_POST['password'];
        } else {
                $password = '';
        }
} else {
        die('invalid');
}

$q = db_query("SELECT visible, password FROM course WHERE id = $cid");
if ($q and mysql_num_rows($q)) {
        list($visible, $course_password) = mysql_fetch_row($q);
        if ($state == 'true') {
                if ($visible == COURSE_OPEN or
                    ($visible == COURSE_REGISTRATION and $password == $course_password)) {
                        db_query("INSERT IGNORE INTO `course_user` (`course_id`, `user_id`, `statut`, `reg_date`)
                                         VALUES ($cid, $uid, 5, CURDATE())");
                        die('registered');
                } else {
                        die('unauthorized');
                }
        } else {
                db_query("DELETE FROM group_members
                                 WHERE user_id = $uid AND
                                       group_id IN (SELECT id FROM `group` WHERE course_id = $cid)");
                db_query("DELETE FROM `course_user` WHERE course_id = $cid AND user_id = $uid");
                die('unregistered');
        }
} else {
        die('invalid');
}

