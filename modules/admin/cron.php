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

/**
 * @brief run jobs once a month
 */
function monthlycronjob() {    
    $monthlyname = 'admin_monthly';
    $lastmonth = mktime(date("H"), date("i"), date("s"), date("n") - 1, date("j"), date("Y"));

    $lastrunmonthly = ($res = Database::get()->querySingle("SELECT UNIX_TIMESTAMP(last_run) as last_run FROM cron_params WHERE name = ?s", $monthlyname)) ? $res->last_run : 0;
    $nevermonthly = ($lastrunmonthly > 0) ? false : true;

    if ($lastmonth > $lastrunmonthly) {
        // do monthly work here
        summarizeLogins();
        summarizeMonthlyData();
        summarizeMonthlyActions();
        Log::rotate();
        Log::purge();
        optimizeIndex();

        // update last run time
        if ($nevermonthly) {
            Database::get()->query("INSERT INTO cron_params (name, last_run) VALUES (?s, CURRENT_TIMESTAMP)", $monthlyname);
        } else {
            Database::get()->query("UPDATE cron_params SET last_run = CURRENT_TIMESTAMP WHERE name = ?s", $monthlyname);
        }
    }
}

/**
 * 
 */
function summarizeLogins() {
    $stop_stmp = time() - (get_config('actions_expire_interval') - 1) * 30 * 24 * 3600;
    $stop_month = date('Y-m-01 00:00:00', $stop_stmp);

    $res = Database::get()->querySingle("SELECT min(`when`) as min_date, max(`when`) as max_date FROM loginout");
    if ($res) {
        $min_date = $res->min_date;
        $max_date = $res->max_date;

        $minstmp = strtotime($min_date);
        $maxstmp = strtotime($max_date);

        if ($minstmp + (get_config('actions_expire_interval') - 1) * 30 * 24 * 3600 < $maxstmp) { //data more than X months old
            $stmp = strtotime($min_date);
            $end_stmp = $stmp + 31 * 24 * 60 * 60;  //min time + 1 month
            $start_date = $min_date;
            $end_date = date('Y-m-01 00:00:00', $end_stmp);

            while ($end_date < $stop_month) {
                $sql_1 = "SELECT count(idLog) as visits FROM loginout " .
                         " WHERE `when` >= ?t AND `when` < ?t AND action = 'LOGIN'";
                $visits = Database::get()->querySingle($sql_1, $start_date, $end_date)->visits;

                $sql_2 = "INSERT INTO loginout_summary SET " .
                         " login_sum = ?d, " .
                         " start_date = ?t, " .
                         " end_date = ?t";
                Database::get()->query($sql_2, $visits, $start_date, $end_date);

                $sql_3 = "DELETE FROM loginout " .
                         " WHERE `when` >= ?t AND " .
                         " `when` < ?t ";
                Database::get()->query($sql_3, $start_date, $end_date);

                // next month
                $start_date = $end_date;
                $stmp = $end_stmp;
                $end_stmp += 31 * 24 * 60 * 60;  //end time + 1 month
                $end_date = date('Y-m-01 00:00:00', $end_stmp);
                $start_date = date('Y-m-01 00:00:00', $stmp);
            }
        }
    }
}

/**
 * @brief store summarized monthly statistics
 * @global type $langCourse
 * @global type $langCoursVisible
 * @global type $langFaculty
 * @global type $langTeacher
 * @global type $langNbUsers
 * @global type $langTypeClosed
 * @global type $langTypeRegistration
 * @global type $langTypeOpen
 */
function summarizeMonthlyData() {
    global $langCourse, $langCoursVisible, $langFaculty, $langTeacher,
    $langNbUsers, $langTypeClosed, $langTypeRegistration, $langTypeOpen;

    // Check if data for last month have already been inserted in 'monthly_summary'...
    $lmon = mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'));
    $last_month = date('m Y', $lmon);
    $res = Database::get()->querySingle("SELECT id FROM monthly_summary WHERE `month` = ?s", $last_month);

    if (empty($res)) {
        $current_month = date('Y-m-01 00:00:00');
        $prev_month = date('Y-m-01 00:00:00', $lmon);

        $login_sum = Database::get()->querySingle("SELECT COUNT(idLog) as sum_id FROM loginout WHERE `when` >= ?t AND `when`< ?t AND action = 'LOGIN'", $prev_month, $current_month)->sum_id;
        $cours_sum = Database::get()->querySingle("SELECT COUNT(id) as cours_sum FROM course")->cours_sum;
        $prof_sum = Database::get()->querySingle("SELECT COUNT(id) as prof_sum FROM user WHERE status = 1")->prof_sum;
        $stud_sum = Database::get()->querySingle("SELECT COUNT(id) as stud_sum FROM user WHERE status = 5")->stud_sum;
        $vis_sum = Database::get()->querySingle("SELECT COUNT(id) as vis_sum FROM user WHERE status = 10")->vis_sum;

        $mtext = "<table class='table-default'>
                <tbody>
                <tr><th>" . $langCourse . "</th>
                <th>" . $langCoursVisible . "</th>
                <th>" . $langFaculty . "</th>
                <th>" . $langTeacher . "</th>
                <th>" . $langNbUsers . "</th></tr>";

        $sql = "SELECT course.title AS name,
                       course.visible AS visible,
                       hierarchy.name AS dept,
                       course.prof_names AS proff,
                       COUNT(user_id) AS cnt
                FROM course JOIN course_department ON course.id = course_department.course
                            JOIN hierarchy ON hierarchy.id = course_department.department
                            LEFT JOIN course_user ON course.id = course_user.course_id
                GROUP BY course.id";
        Database::get()->queryFunc($sql, function($row) use (&$mtext, $langTypeClosed, $langTypeRegistration, $langTypeOpen, $langInactiveCourse) {
            //declare course visibility
            if ($row->visible == COURSE_CLOSED) {
                $cvisible = $langTypeClosed;
            } else if ($row->visible == COURSE_REGISTRATION) {
                $cvisible = $langTypeRegistration;
            } else if ($row->visible == COURSE_OPEN) {
                $cvisible = $langTypeOpen;
            } else {
                $cvisible = $langInactiveCourse;
            }
            $mtext .= "<tr><td>" . $row->name . "</td><td> " . $cvisible . "</td>
                <td class='text-center'>" . getSerializedMessage($row->dept) . "</td>
                <td>" . $row->proff . "</td><td class='text-center'>" . $row->cnt . "</td></tr>";
        });
        
        $mtext .= '</tbody></table>';
        $sql = "INSERT INTO monthly_summary SET month = ?s, profesNum = ?d, studNum = ?d,
            visitorsNum = ?d, coursNum = ?d, logins = ?d, details = ?s";
        Database::get()->query($sql, $last_month, $prof_sum, $stud_sum, $vis_sum, $cours_sum, $login_sum, $mtext);
    }
}

/**
 * @brief summarize monthly actions
 */
function summarizeMonthlyActions() {
    require_once 'include/action.php';
    
    $action = new action();
    Database::get()->queryFunc("SELECT id FROM course", function($course) use (&$action) {
        $min_time = ($res = Database::get()->querySingle("SELECT MIN(day) as min_time FROM actions_daily WHERE course_id = ?d", intval($course->id))) ? $res->min_time : time();
        if ($min_time + get_config('actions_expire_interval') * 30 * 24 * 3600 < time()) {
            $action->summarize($course->id);
        }
    });
}

/**
 * 
 * @global type $webDir
 */
function optimizeIndex() {
    global $webDir; // required for indexer
    require_once 'modules/search/indexer.class.php';
    $idx = new Indexer();
    set_time_limit(0);
    $idx->getIndex()->optimize();
}
