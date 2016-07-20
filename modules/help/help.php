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

session_start();

if (isset($_GET['language'])) {
    $language = preg_replace('/[^a-z-]/', '', $_GET['language']);
} else {
    $language = 'el';
}
if (isset($_SESSION['theme'])) {
    $theme = $_SESSION['theme'];
} else {
    $theme = 'classic';
}
$themeimg = '../../template/' . $theme . '/img';

if (file_exists("../../lang/$language/help.inc.php")) {
    $siteName = '';
    include "../../lang/$language/common.inc.php";
    include '../../include/main_lib.php';
    include "../../lang/$language/help.inc.php";
} else {
    die("$langNoHelpTopic");
}

// Default topic
if (!isset($_GET['topic']) or !isset($GLOBALS["lang$_GET[topic]Content"])) {
    $_GET['topic'] = 'Default';
}

header('Content-Type: text/html; charset=UTF-8');

$title = $GLOBALS['langH' . str_replace('_student', '', $_GET['topic'])];
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title><?php echo q($GLOBALS["langH$_GET[topic]"]); ?></title>
        <link href="../../template/<?php echo $theme ?>/help.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <h3><?php echo q($title); ?></h3>
        <?php echo $GLOBALS["lang$_GET[topic]Content"]; ?>
    </body>
</html>
