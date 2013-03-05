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

require_once '../../include/baseTheme.php';
require_once 'include/lib/cronutil.class.php';
require_once 'include/log.php';
session_write_close();


CronUtil::lock();
ignore_user_abort(true);
CronUtil::imgOut();
CronUtil::flush();
monthlycronjob();
CronUtil::unlock();


function monthlycronjob() {
    $monthlyname = 'admin_monthly';
    $nevermonthly = true;
    $lastrunmonthly = 0;
    $lastmonth = mktime(date("H"), date("i"), date("s"), date("n")-1, date("j"), date("Y") );
    
    $res = db_query("SELECT UNIX_TIMESTAMP(last_run) as last_run FROM cron_params WHERE name = '" . $monthlyname . "'");
    if (mysql_num_rows($res) > 0) {
        $nevermonthly = false;
        $row = mysql_fetch_assoc($res);
        $lastrunmonthly = $row['last_run'];
    }
    
    if ($lastmonth > $lastrunmonthly) {
        // do monthly work here
        summarizeLogins();
        summarizeMonthlyData();
        summarizeMonthlyActions();
        Log::rotate();
        Log::purge();
        
        // update last run time
        if ($nevermonthly)
            db_query("INSERT INTO cron_params (name, last_run) VALUES ('" . $monthlyname . "', CURRENT_TIMESTAMP)");
        else
            db_query("UPDATE cron_params SET last_run = CURRENT_TIMESTAMP WHERE name = '" . $monthlyname . "'");
    }
    
}


function summarizeLogins() {
    $stop_stmp = time() - (get_config('actions_expire_interval')-1) * 30 * 24 * 3600;
    $stop_month = date('Y-m-01 00:00:00', $stop_stmp);

    $sql_0 = "SELECT min(`when`) as min_date, max(`when`) as max_date FROM loginout";

    $result = db_query($sql_0);
    while ($row = mysql_fetch_assoc($result)) {
        $min_date = $row['min_date'];
        $max_date = $row['max_date'];
    }
    mysql_free_result($result);


    $minstmp = strtotime($min_date);
    $maxstmp = strtotime($max_date);


    if ( $minstmp + (get_config('actions_expire_interval')-1) *30*24*3600 < $maxstmp ) { //data more than X months old
	$stmp = strtotime($min_date);
        $end_stmp = $stmp + 31*24*60*60;  //min time + 1 month
        $start_date = $min_date;
        $end_date = date('Y-m-01 00:00:00', $end_stmp);


        while ($end_date < $stop_month){
                $sql_1 = "SELECT count(idLog) as visits FROM loginout ".
                    " WHERE `when` >= '$start_date' AND `when` < '$end_date' AND action='LOGIN'";

                $result_1 = db_query($sql_1);
                while ($row1 = mysql_fetch_assoc($result_1)) {
                    $visits = $row1['visits'];
                }
                mysql_free_result($result_1);

                $sql_2 = "INSERT INTO loginout_summary SET ".
                    " login_sum = '$visits', ".
                    " start_date = '$start_date', ".
                    " end_date = '$end_date' ";
                $result_2 = db_query($sql_2);
                @mysql_free_result($result_2);

                $sql_3 = "DELETE FROM loginout ".
                    "WHERE `when` >= '$start_date' AND ".
                    " `when` < '$end_date' ";
                $result_3 = db_query($sql_3);
                @mysql_free_result($result_3);


            // next month
            $start_date = $end_date;
	    $stmp = $end_stmp;
            $end_stmp += 31*24*60*60;  //end time + 1 month
            $end_date = date('Y-m-01 00:00:00', $end_stmp);
	    $start_date = date('Y-m-01 00:00:00', $stmp);

        }
    }
}


function summarizeMonthlyData() {
    global $langCourse, $langCoursVisible, $langFaculty, $langTeacher, 
           $langNbUsers, $langTypeClosed, $langTypeRegistration, $langTypeOpen;
            
    // Check if data for last month have already been inserted in 'monthly_summary'...
    $lmon = mktime(0, 0, 0, date('m')-1, date('d'),  date('Y'));
    $last_month = date('m Y', $lmon);
    $sql = "SELECT id FROM monthly_summary WHERE `month` = '$last_month'";
    $result = db_query($sql);

    if (!$result or mysql_num_rows($result) == 0) {
        $current_month = date('Y-m-01 00:00:00');
        $prev_month = date('Y-m-01 00:00:00', $lmon);

        $login_sum = 0;
        $cours_sum = 0;
        $prof_sum = 0;
        $stud_sum = 0;
        $vis_sum = 0;

        $sql = "SELECT COUNT(idLog) as sum_id FROM loginout WHERE `when` >= '$prev_month' AND `when`< '$current_month' AND action='LOGIN'";
        $result = db_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $login_sum = $row['sum_id'];
        }

        mysql_free_result($result);
        if (!isset($cours_sum)) {$cours_sum = 0;}

        $sql = "SELECT COUNT(id) as cours_sum FROM course";
        $result = db_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $cours_sum = $row['cours_sum'];
        }
        mysql_free_result($result);
        if (!isset($cours_sum)) {$cours_sum = 0;}

        $sql = "SELECT COUNT(user_id) as prof_sum FROM user WHERE statut=1";
        $result = db_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $prof_sum = $row['prof_sum'];
        }
        mysql_free_result($result);
        if (!isset($prof_sum)) {$prof_sum = 0;}

        $sql = "SELECT COUNT(user_id) as stud_sum FROM user WHERE statut=5";
        $result = db_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $stud_sum = $row['stud_sum'];
        }
        mysql_free_result($result);
        if (!isset($stud_sum)) {$stud_sum = 0;}

        $sql = "SELECT COUNT(user_id) as vis_sum FROM user WHERE statut=10";
        $result = db_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $vis_sum = $row['vis_sum'];
        }
        mysql_free_result($result);
        if (!isset($vis_sum)) {$vis_sum = 0;}

        $mtext = "<table>
                <tr><th>".$langCourse."</th>
                <th>".$langCoursVisible."</th>
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
                            LEFT JOIN course_user ON course.id = course_user.course_id
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
            $mtext .= "<tr><td>".$row['name']."</td><td> ".$cvisible."</td>
                <td align=center>".$row['dept']."</td>
                <td>".$row['proff']."</td><td align=center>".$row['cnt']."</td></tr>";
        }
        mysql_free_result($result);
        $mtext .= '</table>';
        $sql = "INSERT INTO monthly_summary SET month='$last_month', profesNum = '$prof_sum', studNum = '$stud_sum',
            visitorsNum = '$vis_sum', coursNum = '$cours_sum', logins = '$login_sum', details = " . quote($mtext);
        db_query($sql);
    }
}


function summarizeMonthlyActions() {
    require_once 'include/action.php';
    $action = new action();
    $res = db_query("SELECT id FROM course");
    $min_time = time();
    
    while ($course = mysql_fetch_assoc($res)) {
        $course_id = $course['id'];
        
        $res2 = db_query("SELECT MIN(day) as min_time FROM actions_daily WHERE course_id = " . intval($course_id));
        while ($row = mysql_fetch_assoc($res2)) {
            if (!empty($row['min_time']))
                $min_time = strtotime($row['min_time']);
            else
                break;
        }

        if ($min_time + get_config('actions_expire_interval') * 30 * 24 * 3600 < time())
            $action->summarize($course_id);
    }   
}
