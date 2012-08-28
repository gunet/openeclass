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



/**===========================================================================
	unreguser.php
	@last update: 27-06-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
        @Description: Delete user from platform and from courses (eclass version)

 	This script allows the admin to :
 	- permanently delete a user account
 	- delete a user from participating into a course

==============================================================================
*/

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new hierarchy();
$user = new user();

$nameTools = $langUnregUser;
$navigation[] = array ('url' => 'index.php', 'name' => $langAdmin);

// get the incoming values and initialize them
$u = isset($_GET['u'])? intval($_GET['u']): false;
$c = isset($_GET['c'])? intval($_GET['c']): false;
$doit = isset($_GET['doit']);

if (isDepartmentAdmin())
	validateUserNodes(intval($u), true);

$u_account = $u? q(uid_to_username($u)): '';
$u_realname = $u? q(uid_to_name($u)): '';
$u_statut = get_uid_statut($u);
$t = 0;

if (!$doit) {
    $tool_content .= "<p class='title1'>$langConfirmDelete</p>
        <div class='alert1'>$langConfirmDeleteQuestion1 <em>$u_realname ($u_account)</em>";
        if ($c) {
                $tool_content .= " $langConfirmDeleteQuestion2 <em>".q(course_id_to_title($c))."</em>";
        }
        $tool_content .= ";</div>

                <p class='eclass_button'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;c=$c&amp;doit=yes'>$langDelete</a></p>
                <div class='right'> <a href='edituser.php?u=$u'>$langBack</a></div>
                ";
} else {
        if (!$c) {
                if ($u == 1) {
                        $tool_content .= $langTryDeleteAdmin;
                } else {
                        // now check if the user has registered courses...
                        $q1 = db_query("SELECT * FROM course_user WHERE user_id = $u");
                        $total = mysql_num_rows($q1);
                        if ($total>0) {
                                // user has courses, so not allowed to delete
                                $tool_content .= "$langUnregForbidden <em>$u_realname ($u_account)</em><br />";
                                $v = 0;	$s = 0;
                                for ($p = 0; $p < mysql_num_rows($q1); $p++) {
                                        $l1 = mysql_fetch_array($q1);
                                        $tutor = $l1[5];
                                        if ($tutor == '1') {
                                                $v++;
                                        } else {
                                                $s++;
                                        }
                                }

                                if ($v > 0) {
                                        if ($s > 0) {
                                                //display list
                                                $tool_content .= "$langUnregFirst <br/ ><br />";
                                                $sql = db_query("SELECT a.code, a.title, b.statut, a.id
                                                                        FROM course AS a
                                                                        JOIN course_department ON a.id = course_department.course
                                                                        JOIN hierarchy ON course_department.department = hierarchy.id
                                                                        LEFT JOIN course_user AS b ON a.id = b.course_id
                                                                        WHERE b.user_id = $u AND b.tutor = 0
                                                                        ORDER BY b.statut, hierarchy.name");
                                                // αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα
                                                if (mysql_num_rows($sql) > 0) {
                                                        $tool_content .= "<h4>$langStudentParticipation</h4>\n".
                                                                "<table>\n<tr><th>$langCourseCode</th><th>$langLessonName</th>".
                                                                "<th>$langProperty</th><th>$langActions</th></tr>";
                                                        for ($j = 0; $j < mysql_num_rows($sql); $j++) {
                                                                $logs = mysql_fetch_array($sql);
                                                                $tool_content .= "<tr><td>". q($logs['code']) ."</td><td>".
                                                                        q($logs['title']) ."</td><td align='center'>";
                                                                switch ($logs[4])
                                                                {
                                                                        case '1':
                                                                                $tool_content .= $langTeacher;
                                                                                $tool_content .= "</td><td align='center'>---</td></tr>\n";
                                                                                break;
                                                                        case '0':
                                                                                $tool_content .= $langStudent;
                                                                                $tool_content .= "</td><td align='center'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;c=". q($logs['id']) ."'>$langDelete</a></td></tr>\n";
                                                                                break;
                                                                        default:
                                                                                $tool_content .= $langVisitor;
                                                                                $tool_content .= "</td><td align='center'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;c=". q($logs['id']) ."'>$langDelete</a></td></tr>\n";
                                                                                break;
                                                                }
                                                        }
                                                        $tool_content .= "</table>\n";
                                                }
                                        } else {
                                                $tool_content .= "$langUnregTeacher<br />";
                                                $sql = db_query("SELECT a.code, a.title, b.statut, a.id
                                                                        FROM course AS a
                                                                        JOIN course_department ON a.id = course_department.course
                                                                        JOIN hierarchy ON course_department.department = hierarchy.id
                                                                        LEFT JOIN course_user AS b ON a.id = b.course_id
                                                                        WHERE b.user_id = $u AND b.statut = 1
                                                                        ORDER BY b.statut, hierarchy.name");
                                                // αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα
                                                if (mysql_num_rows($sql) > 0) {
                                                        $tool_content .= "<h4>$langStudentParticipation</h4>\n".
                                                                "<table border=\"1\">\n<tr><th>$langCourseCode</th><th>$langLessonName</th>".
                                                                "<th>$langProperty</th><th>$langActions</th></tr>";
                                                        for ($j = 0; $j < mysql_num_rows($sql); $j++) {
                                                                $logs = mysql_fetch_array($sql);
                                                                $tool_content .= "<tr><td>".q($logs[0])."</td><td>".
                                                                        q($logs[1])."</td><td align=\"center\">";
                                                                $tool_content .= $langTeacher;
                                                                $tool_content .= "</td><td align=\"center\">---</td></tr>\n";
                                                        }
                                                }
                                                $tool_content .= "</table>\n";

                                        }
                                } else {
                                        // display list
                                        $tool_content .= "$langUnregFirst <br /><br />";
                                        $sql = db_query("SELECT a.code, a.title, b.statut, a.id
                                                                FROM course AS a
                                                                JOIN course_department ON a.id = course_department.course
                                                                JOIN hierarchy ON course_department.department = hierarchy.id
                                                                LEFT JOIN course_user AS b ON a.id = b.course_id
                                                                WHERE b.user_id = $u
                                                                ORDER BY b.statut, hierarchy.name");
                                        // αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα
                                        if (mysql_num_rows($sql) > 0) {
                                                $tool_content .= "<h4>$langStudentParticipation</h4>\n".
                                                        "<table border='1'>\n<tr><th>$langCourseCode</th><th>$langLessonName</th>".
                                                        "<th>$langProperty</th><th>$langActions</th></tr>";
                                                for ($j = 0; $j < mysql_num_rows($sql); $j++) {
                                                        $logs = mysql_fetch_array($sql);
                                                        $tool_content .= "<tr><td>". q($logs['code']) ."</td><td>".
                                                                q($logs['title']) ."</td><td align=\"center\">";
                                                        switch ($logs['statut']) {
                                                                case 1:
                                                                        $tool_content .= $langTeacher;
                                                                        $tool_content .= "</td><td align='center'>---</td></tr>\n";
                                                                        break;
                                                                case 5:
                                                                        $tool_content .= $langStudent;
                                                                        $tool_content .= "</td><td align='center'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;c=". q($logs['id']) ."'>$langDelete</a></td></tr>\n";
                                                                        break;
                                                                default:
                                                                        $tool_content .= $langVisitor;
                                                                        $tool_content .= "</td><td align='center'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;c=". q($logs['id']) ."'>$langDelete</a></td></tr>\n";
                                                                        break;
                                                        }
                                                }
                                                $tool_content .= "</table>\n";
                                        }
                                }
                                $t = 1;
                        } else {
                                $q = db_query("DELETE from user WHERE user_id = " . intval($u));
                                if ($q and mysql_affected_rows() > 0) {
                                        $t = 2;
                                        db_query("DELETE FROM user_department WHERE user = ". intval($u));
                                } else {
                                        $t = 3;
                                }
                        }

                        switch($t)
                        {
                                case '1': $tool_content .= "";	$m = 1; break;
                                case '2': $tool_content .= "<p>$langUserWithId $u $langWasDeleted.</p>\n"; $m = 0; break;
                                case '3': $tool_content .= "$langErrorDelete"; $m = 1; break;
                                default: $m = 0; break;
                        }

                        if ($u != 1) {
                                db_query("DELETE from admin WHERE idUser = '".mysql_real_escape_string($u)."'");
                        }
                        if (mysql_affected_rows() > 0) {
                                $tool_content .= "<p>$langUserWithId ".q($u)." $langWasAdmin.</p>\n";
                        }

                        // delete guest user from course_user
                        if ($u_statut == '10') {
                                $sql = db_query("DELETE from course_user WHERE user_id = $u");
                        }
                }

        } elseif ($c and $u) {
                $q = db_query("DELETE from course_user WHERE user_id = $u AND course_id = $c");
                if (mysql_affected_rows() > 0) {
                        db_query("DELETE FROM group_members
                                         WHERE user_id = $u AND
                                               group_id IN (SELECT id FROM `group` WHERE course_id = $c)");
                        $tool_content .= "<p>$langUserWithId $u $langWasCourseDeleted <em>".q(course_id_to_title($c))."</em></p>\n";
                        $m = 1;
                }
        }
        else
        {
                $tool_content .= "$langErrorDelete";
        }
        $tool_content .= "<br />&nbsp;";
        if((isset($m)) && (!empty($m))) {
                $tool_content .= "<br /><a href='edituser.php?u=$u'>$langEditUser $u_account</a>&nbsp;&nbsp;&nbsp;";
        }
        $tool_content .= "<a href='index.php'>$langBackAdmin</a>.<br />\n";
}

function get_uid_statut($u)
{
	global $mysqlMainDb;

	if ($r = mysql_fetch_row(db_query("SELECT statut FROM user WHERE user_id = '".mysql_real_escape_string($u)."'",	$mysqlMainDb)))
	{
		return $r[0];
	}
	else
	{
		return FALSE;
	}
}

draw($tool_content,3);
