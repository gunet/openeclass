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

$require_login = true;
$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'User'; //!!

require_once '../../include/baseTheme.php';
require_once 'modules/admin/admin.inc.php';
require_once 'include/log.php';
load_js('tools.js');

define('COURSE_USERS_PER_PAGE', 15);

$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 0;

$nameTools = $langAdminUsers;

$sql = "SELECT user.id, course_user.status FROM course_user, user
	WHERE course_user.course_id = $course_id AND course_user.user_id = user.id";
$result_numb = db_query($sql);
$countUser = mysql_num_rows($result_numb);

$st=1;
$openCoursesNum = Database::get()->querySingle("SELECT COUNT(id) as count FROM user WHERE status = ?d", $st)->count;
echo $openCoursesNum."xs";

$teachers = $students = $visitors = 0;


