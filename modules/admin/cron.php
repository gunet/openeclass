<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

require_once '../../include/baseTheme.php';

if (get_config('disable_cron_jobs')) {
    exit;
}

ini_set("error_log", $webDir . '/courses/cron.log');
error_log("cron START");

require_once 'include/lib/cronutil.class.php';
require_once 'include/log.class.php';
session_write_close();


CronUtil::lock();
ignore_user_abort(true);
CronUtil::imgOut();
CronUtil::flush();
monthlycronjob();
CronUtil::unlock();
error_log("cron END");

/**
 * @brief run jobs once a month
 */
function monthlycronjob() {
    error_log("cron monthlyjob START");
    $monthlyname = 'admin_monthly';
    $lastmonth = mktime(date("H"), date("i"), date("s"), date("n") - 1, date("j"), date("Y"));

    $lastrunmonthly = ($res = Database::get()->querySingle("SELECT UNIX_TIMESTAMP(last_run) as last_run FROM cron_params WHERE name = ?s", $monthlyname)) ? $res->last_run : 0;
    $nevermonthly = ($lastrunmonthly > 0) ? false : true;

    if ($lastmonth > $lastrunmonthly) {
        // do monthly work here
        summarizeLogins();
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
    } else {
        error_log("cron monthlyjob was recently (< 1month) run, skipping ...");
    }
    error_log("cron monthlyjob END");
}

/**
 *
 */
function summarizeLogins() {
    error_log("cron summarizeLogins START");
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
    error_log("cron summarizeLogins END");
}

/**
 * @brief summarize monthly actions
 */
function summarizeMonthlyActions() {
    error_log("cron summarizeMonthlyActions START");
    require_once 'include/action.php';

    $action = new action();
    Database::get()->queryFunc("SELECT id FROM course", function($course) use (&$action) {
        $min_time = ($res = Database::get()->querySingle("SELECT MIN(day) as min_time FROM actions_daily WHERE course_id = ?d", $course->id)) ? strtotime($res->min_time) : time();
        if ($min_time + get_config('actions_expire_interval') * 30 * 24 * 3600 < time()) {
            $action->summarize($course->id);
        }
    });
    error_log("cron summarizeMonthlyActions END");
}

/**
 *
 * @global type $webDir
 */
function optimizeIndex() {
    error_log("cron optimizeIndex START");
    global $webDir; // required for indexer
    require_once 'modules/search/lucene/indexer.class.php';
    $idx = new Indexer();
    set_time_limit(0);
    $idx->getIndex()->optimize();
    error_log("cron optimizeIndex END");
}
