<?php
/* ========================================================================
 * Open eClass 2.6
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


$require_login = TRUE;
include '../../include/baseTheme.php';

$nameTools = $langUnregCourse;

if (isset($_GET['cid'])) {
  $cid = q($_GET['cid']);
  $_SESSION['cid_tmp']=$cid;
}
if(!isset($_GET['cid'])) {
  $cid=$_SESSION['cid_tmp'];
}

if (!isset($_GET['doit']) or $_GET['doit'] != "yes") {

  $tool_content .= "
    <table width='40%'>
    <tbody>
    <tr>
      <td class='caution_NoBorder' height='60' colspan='2'>
      	<p>$langConfirmUnregCours:</p><p> <em>".q(course_code_to_title($cid))."</em>&nbsp;? </p>
	<ul class='listBullet'>
	<li>$langYes: 
	<a href='$_SERVER[SCRIPT_NAME]?u=$_SESSION[uid]&amp;cid=$cid&amp;doit=yes' class=mainpage>$langUnregCourse</a>
	</li>
	<li>$langNo: <a href='../../index.php' class=mainpage>$langBack</a>
	</li></ul>
      </td>
    </tr>
    </tbody>
    </table>";

} else {
        if (isset($_SESSION['uid']) and $_GET['u'] == $_SESSION['uid']) {
                $row = mysql_fetch_row(db_query("SELECT cours_id FROM cours WHERE code = " . quote($cid)));
                if ($row !== false) {
                    $course_id = intval($row[0]);
                    
                    db_query("DELETE FROM group_members
                                    WHERE group_id IN ( SELECT id FROM `group`
                                                                WHERE course_id = $course_id ) AND
                                        user_id = $_SESSION[uid]");
                    db_query("DELETE FROM cours_user
                                    WHERE cours_id = $course_id AND
                                        user_id = $_SESSION[uid]");
                    if (mysql_affected_rows() > 0) {
                            // clear session access to lesson
                            unset($_SESSION['dbname']);
                            unset($_SESSION['cid_tmp']);
                            unset($_SESSION['status'][$cid]);
                            $tool_content .= "<p class='success_small'>$langCoursDelSuccess</p>";
                    } else {
                            $tool_content .= "<p class='caution_small'>$langCoursError</p>";
                    }
                }
        }
        $tool_content .= "<br><br><div align=right><a href='../../index.php' class=mainpage>$langBack</a></div>";
}

if (isset($_SESSION['uid'])) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}
