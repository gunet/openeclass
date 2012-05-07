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

/*
===========================================================================
    admin/summarizeMonthlyData.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description:  Takes general statistics information for each month and inserts
     it to table 'monthly_summary'. Data from 'monthly summary are used for
     monthly reports.
==============================================================================
*/

if (!defined('ECLASS_VERSION')) {
                exit;
}

// Check if data for last month have already been inserted in 'monthly_summary'...
$lmon = mktime(0, 0, 0, date('m')-1, date('d'),  date('Y'));
$last_month = date('m Y', $lmon);
$sql = "SELECT id FROM monthly_summary WHERE `month` = '$last_month'";
$result = db_query($sql, $mysqlMainDb);

if (!$result or mysql_num_rows($result) == 0) {
        echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
<title>$langUpdatingStatistics</title>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
</head><body><div style='text-align: center; font-size: 150%; border: 1px solid black; padding: 1 em;'>$langUpdatingStatistics<br />
$langPleaseWait</div>
";
        stop_output_buffering();
        flush();
        $current_month = date('Y-m-01 00:00:00');
        $prev_month = date('Y-m-01 00:00:00', $lmon);

        $login_sum = 0;
        $cours_sum = 0;
        $prof_sum = 0;
        $stud_sum = 0;
        $vis_sum = 0;

        $sql = "SELECT COUNT(idLog) as sum_id FROM loginout WHERE `when` >= '$prev_month' AND `when`< '$current_month' AND action='LOGIN'";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $login_sum = $row['sum_id'];
        }

        mysql_free_result($result);
        if (!isset($cours_sum)) {$cours_sum = 0;}

        $sql = "SELECT COUNT(id) as cours_sum FROM course";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $cours_sum = $row['cours_sum'];
        }
        mysql_free_result($result);
        if (!isset($cours_sum)) {$cours_sum = 0;}

        $sql = "SELECT COUNT(user_id) as prof_sum FROM user WHERE statut=1";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $prof_sum = $row['prof_sum'];
        }
        mysql_free_result($result);
        if (!isset($prof_sum)) {$prof_sum = 0;}

        $sql = "SELECT COUNT(user_id) as stud_sum FROM user WHERE statut=5";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $stud_sum = $row['stud_sum'];
        }
        mysql_free_result($result);
        if (!isset($stud_sum)) {$stud_sum = 0;}

        $sql = "SELECT COUNT(user_id) as vis_sum FROM user WHERE statut=10";
        $result = db_query($sql, $mysqlMainDb);
        while ($row = mysql_fetch_assoc($result)) {
            $vis_sum = $row['vis_sum'];
        }
        mysql_free_result($result);
        if (!isset($vis_sum)) {$vis_sum = 0;}

        $mtext = "<table>";
        $mtext .= "<tr><th>".$langCourse."</th>
		<th>".$langCoursVisible."</th>
		<th>".$langType."</th>
		<th>".$langFaculty."</th>
		<th>".$langTeacher."</th>
		<th>".$langNbUsers."</th></tr>";

        $sql = "SELECT course.title AS name,
                       course.visible AS visible,                       
                       hierarchy.name AS dept,
                       course.prof_names AS proff,
                       COUNT(user_id) AS cnt
                FROM course JOIN course_department ON course.id = course_department.course
                           JOIN hierarchy ON hierarchy.id = course_department.department
                           LEFT JOIN cours_user ON course.id = cours_user.cours_id
                GROUP BY course.id ";
        $result = db_query($sql);
        while ($row = mysql_fetch_assoc($result)) {            
            //declare visibility
            if ($row['visible'] == 0) {
              $cvisible = $langTypeClosed;
            }
            else if ($row['visible']==1) {
              $cvisible = $langTypeRegistration;
            }
            else {
                $cvisible = $langTypeOpen;
            }
            $mtext .= "<tr><td>".$row['name']."</td><td> ".$cvisible."</td><td> ".$ctype."</td>
		<td align=center>".$row['dept']."</td>
		<td>".$row['proff']."</td><td align=center>".$row['cnt']."</td></tr>";
        }
        mysql_free_result($result);
        $mtext .= '</table>';
        $mtext = quote($mtext);
        $sql = "INSERT INTO monthly_summary SET month='$last_month', profesNum = '$prof_sum', studNum = '$stud_sum',
            visitorsNum = '$vis_sum', coursNum = '$cours_sum', logins = '$login_sum', details = $mtext";
        db_query($sql, $mysqlMainDb);
        echo "<div style='text-align: center; padding: 2em;'>",
             "<a href='{$urlServer}modules/admin/'>$langCont</a></div>",
             "<script type='text/javascript'>window.location = '",
             js_escape($urlServer . 'modules/admin/'),
             "'</script></body></html>\n";
	exit;
}