<?php
/* ========================================================================
 * Open eClass 3.0
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

$require_current_course = TRUE;
$require_course_admin = TRUE;
include '../../include/baseTheme.php';

$nameTools = $langDelCourse;

if(isset($_POST['delete'])) {
    delete_course($cours_id);
    $tool_content .= "<p class='success_small'>$langTheCourse <b>($title $currentCourseID)</b> $langHasDel</p>
                      <br /><p align='right'><a href='../../index.php'>$langBackHome $siteName</a></p>";
    unset($currentCourseID);
    unset($_SESSION['dbname']);
    draw($tool_content, 1);
    exit();
} else {
    $tool_content .= "
    <table class='tbl'>
    <tr>
    <td class='caution_NoBorder' height='60' colspan='3'>
            <p>$langByDel_A <b>$title ($currentCourseID) </b>&nbsp;?  </p>
    </td>
    </tr>
    <tr>
    <th rowspan='2' class='left' width='220'>$langConfirmDel:</th>
    <td width='52' align='center'>
    <form method='post' action='delete_course.php?course=$code_cours'>
    <input type='submit' name='delete' value='$langDelete' /></form></td>
    <td><small>$langByDel</small></td>
    </tr>
    <tr>
    <td align='center'><form method='get' action='infocours.php'><input type='hidden' name='course' value='$code_cours'/>
                    <input type='submit' name='dont_delete' value='$langCancel' /></form></td>
    <td>&nbsp;</td>
    </tr>
    </table>";
    $tool_content .= "<p align='right'><a href='infocours.php?course=$code_cours'>$langBack</a></p>";
} 
draw($tool_content, 2);
