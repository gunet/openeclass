<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

$require_login = TRUE;

$require_valid_uid = true;
if (!session_id()) {
    session_start();
}
if(isset($_SESSION['dbname'])){
    $require_current_course = true;
}
require_once '../../include/baseTheme.php';
require_once 'notifications.inc.php';

if(isset($_GET['c']) && isset($_GET['m'])){
    $x='cm';$res = get_course_module_notifications($_GET['c'], $_GET['m']);
}
elseif(isset($_GET['c'])){
    $x='c';$res = get_course_notifications($_GET['c']);
}
elseif(isset($_GET['m'])){
   $x='m';$res = get_module_notifications($_GET['m']);
} else {
   $x='u';$res = get_user_notifications();
}

if(!is_null($res)){
        echo json_encode($res);
} else {
    echo "$x: No data";
}



