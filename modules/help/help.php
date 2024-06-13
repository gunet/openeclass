<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

session_start();

if (isset($_GET['language']) and $_GET['language'] == 'el') {
    $language = 'el';
} else {
    $language = 'en';
}

$siteName = '';

include "../../lang/$language/common.inc.php";
include '../../include/main_lib.php';

$shortVer = preg_replace('/^(\d\.\d+).*$/', '\1', ECLASS_VERSION);

$topic = $subtopic = '';
if (isset($_GET['topic'])) {
    $topic = htmlspecialchars($_GET['topic'], ENT_QUOTES);
}
if (isset($_GET['subtopic'])) {
    $subtopic = '/' . htmlspecialchars($_GET['subtopic'], ENT_QUOTES);
}
if (isset($_SESSION['is_admin']) and !isset($course_code)) {
    $user_status = 'admin';
} else if (isset($_SESSION['status']) and  $_SESSION['status'] == USER_TEACHER) {
    $user_status = 'teacher';
} else {
    $user_status = 'student';
}

$link = "https://docs.openeclass.org/$language/$shortVer/$user_status/$topic$subtopic?do=export_xhtml";
header('Content-Type: text/html; charset=UTF-8');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <body>
        <iframe frameborder="0" width="100%" height="500px" src="<?php echo $link ?>"></iframe>
    </body>
</html>
