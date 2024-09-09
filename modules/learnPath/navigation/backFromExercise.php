<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

// This script refreshes the upper frame for the user to see
// his updated learning path progress and prompts him
// to click next after finishing an exercise.

$require_current_course = true;
require_once '../../../include/init.php';

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>">
        <title>-</title>
        <link href="../../../template/modern/css/lp.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="../../../template/modern/css/bootstrap.min.css">
        <link rel="stylesheet" href="../../../template/modern/css/font-Manrope/css/Manrope.css?<?php echo time(); ?> ">
        <link href='../../../template/modern/css/font-awesome-6.4.0/css/all.css' rel='stylesheet'>
        <link rel="stylesheet" href="../../../template/modern/css/default.css?<?php echo time(); ?> ">
        <script src="../../../js/bootstrap.bundle.min.js"></script>
    </head>
    <body class='body-learningPath' style="margin: 0px; padding-left: 0px; height: 100%!important; height: auto;">
        <div id="content" style="width:800px; margin: 0 auto;">
            <br /><br /><br />
            <?php
            if ($_GET['op'] == 'cancel') {
                echo "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langExerciseCancelled</span></div>";
            } elseif ($_GET['op'] == 'finish') { // exercise done
                echo "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langExerciseDone</span></div>";
            }
            ?>
        </div>
</html>
