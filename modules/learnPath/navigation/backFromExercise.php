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

$TOCurl = "../viewer_toc.php?course=$course_code";
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset ?>">
        <title>-</title>
        <link href="../../../template/<?php echo $theme ?>/CSS/lp.css" rel="stylesheet" type="text/css" />
        <script type='text/javascript'>
            <!-- //
          parent.tocFrame.location.href = "<?php echo $TOCurl; ?>";
//-->
        </script>
    </head>
    <body style="margin: 0px; padding-left: 0px; height: 100%!important; height: auto; background-color: #ffffff;">
        <div id="content" style="width:800px; margin: 0 auto;">
            <br /><br /><br />
            <?php
            if ($_GET['op'] == 'cancel') {
                echo "<div class='alert alert-warning'>$langExerciseCancelled</div>";
            } elseif ($_GET['op'] == 'finish') { // exercise done
                echo "<div class='alert alert-success'>$langExerciseDone</div>";
            }
            ?>
        </div>
</html>
