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

require_once 'include/log.class.php';
require_once 'include/lib/hierarchy.class.php';

/**
 * @brief Get statistics of visits and visit duration to a course. The results are depicted in first plot of course statistics
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param string $interval the time interval to aggregate statistics on, values: 'year'|'month'|'week'|'day'
 * @param int $cid the id of the course
 * @param int $user_id the id of the user to filter out the statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_course_stats($cid, $interval, $start = null, $end = null, $user_id = null)
{

    $formattedr = array('time'=> array(), 'hits'=> array(), 'duration'=> array());
    if(!is_null($start) && !is_null($end && !empty($start) && !empty($end))){
        $g = build_group_selector_cond($interval);
        $groupby = $g['groupby'];
        $date_components = $g['select'];
        if(is_numeric($user_id)) {
            $q = "SELECT $date_components, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d $groupby";
            $r = Database::get()->queryArray($q, $cid, $start, $end, $user_id);
        } else {
            $q = "SELECT $date_components, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t $groupby";
            $r = Database::get()->queryArray($q, $cid, $start, $end);
        }
        foreach($r as $record){
           $formattedr['time'][] = $record->cat_title;
           $formattedr['hits'][] = $record->hits;
           $formattedr['duration'][] = $record->dur;
        }
    }
    return $formattedr;
}

/**
 * @brief Get preference statistics on the visits in different modules of the course. The
 * preference distribution is based on visits but can be changed to duration if
 * preferable. The results are shown in a pie (second plot) of the course stats
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param string $interval the time interval to aggregate statistics on, values: 'year'|'month'|'week'|'day'
 * @param int $cid the id of the course
 * @param int $mid the module id
 * @param int $user_id the id of the user to filter out the statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_course_module_stats($cid, $mid, $interval, $start = null, $end = null, $user_id = null){
    global $modules;
    $mtitle = which_module($mid);
    $g = build_group_selector_cond($interval);
    $groupby = $g['groupby'];
    $date_components = $g['select'];
    if(is_numeric($user_id)){
        $q = "SELECT $date_components, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE course_id=?d AND module_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d $groupby";
        $r = Database::get()->queryArray($q, $cid, $mid, $start, $end, $user_id);
    }
    else{
        $q = "SELECT $date_components, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE course_id=?d AND module_id=?d AND day BETWEEN ?t AND ?t $groupby";
        $r = Database::get()->queryArray($q, $cid, $mid, $start, $end);
    }
    $formattedr = array('time'=>array(),'hits'=>array(),'duration'=>array());
    foreach($r as $record){
       $formattedr['time'][] = $record->cat_title;
       $formattedr['hits'][] = $record->hits;
       $formattedr['duration'][] = $record->dur;
    }
    return array('charttitle'=>$mtitle, 'chartdata'=>$formattedr);
}

/**
 * @brief Get statistics of visits and visit duration to a module of a course. The
 * results are depicted in the plot of module statistics beside the pie of the
 * course statistics page (third plot).
  * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $cid the id of the course
 * @param int $user_id the id of the user to filter out the statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_module_preference_stats($cid, $start = null, $end = null, $user_id = null){

    if(is_numeric($user_id)){
        $q = "SELECT module_id mdl, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d GROUP BY module_id ORDER BY hits DESC";
        $r = Database::get()->queryArray($q, $cid, $start, $end, $user_id);
    }
    else{
        $q = "SELECT module_id mdl, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t GROUP BY module_id ORDER BY hits DESC";
        $r = Database::get()->queryArray($q, $cid, $start, $end);
    }
    $formattedr = array();
    $mdls = array();
    $pmid = null;
    foreach($r as $record){
        $mtitle = which_module($record->mdl);
        $formattedr[$record->mdl] = $record->hits;
        $mdls[$record->mdl] = $mtitle;
        if(is_null($pmid)){
            $pmid = $record->mdl;
        }
    }
    return array('modules'=>$mdls,'pmid' => $pmid, 'chartdata' => $formattedr);
}

/**
 * @brief Get statistics of user register and unregister actions of a course. The results are
 * depicted in the last plot of the course statistics
 * @param int $cid the id of the course
 * @param string $interval the time interval to aggregate statistics on, values: 'year'|'month'|'week'|'day'
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_course_registration_stats($cid, $interval, $start = null, $end = null)
{
    $formattedr = array('time'=> array(), 'regs'=> array(), 'unregs'=> array());
    if(!is_null($start) && !is_null($end && !empty($start) && !empty($end))){
        $g_reg = build_group_selector_cond($interval, 'reg_date');
        $groupby_reg = $g_reg['groupby'];
        $date_components_reg = $g_reg['select'];
        $reg_t = "SELECT $date_components_reg, COUNT(user_id) regs FROM course_user WHERE course_id=?d AND reg_date BETWEEN ?t AND ?t $groupby_reg";
        $g_unreg = build_group_selector_cond($interval, 'ts');
        $groupby_unreg = $g_unreg['groupby'];
        $date_components_unreg = $g_unreg['select'];
        $unreg_t = "SELECT $date_components_unreg, COUNT(user_id) unregs FROM log WHERE module_id=".MODULE_ID_USERS." AND action_type=".LOG_DELETE." AND course_id=?d AND ts BETWEEN ?t AND ?t $groupby_unreg";
        $q = "SELECT cat_title, ifnull(regs,0) regs, ifnull(unregs,0) unregs FROM ((SELECT cat_title, regs, unregs FROM ($reg_t) x NATURAL LEFT JOIN ($unreg_t) y) UNION (SELECT cat_title, regs, unregs FROM ($unreg_t) x NATURAL LEFT JOIN ($reg_t) y)) z";
        $r = Database::get()->queryArray($q, $cid, $start, $end, $cid, $start, $end, $cid, $start, $end, $cid, $start, $end);
        foreach($r as $record){
           $formattedr['time'][] = $record->cat_title;
           $formattedr['regs'][] = $record->regs;
           $formattedr['unregs'][] = $record->unregs;
        }
    }
    return array('chartdata'=>$formattedr);
}

/**
 * @brief Detailed list of user activity in course. The results are
 * listed in the first table of the course detailed statistics
 * @param int $user the id of the user to filter out the statistics for
 * @param int $course the id of the course
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $module the module id
 * @return array an array appropriate for displaying in a datatables table
*/
function get_course_activity_details($user, $course, $start = null, $end = null, $module = -1){

    $course_cond = " WHERE course_id = ?d";
    $pars = array($course);

    $user_cond1 = "";
    $user_cond2 = "";
    if($user > 0 ){
        $user_cond1 = " AND u.id = ?d";
        $user_cond2 = " AND l.user_id = ?d";
        $pars[] = $user;
        $pars[] = $user;
    }

    $pars[] = $course;

    if(!is_null($module) && $module > 0){
        $module_cond = " AND module_id = ?d ";
    }
    else {
        $module = 0;
        $module_cond = " AND module_id > ?d";
    }
    $pars[] = $module;

    $date_cond = "";
    if(!is_null($start) && !empty($start) && !is_null($end) && !empty($end)){
        $date_cond = " AND DATE(ts) BETWEEN ?t AND ?t ";
    }
    $pars[] = $start;
    $pars[] = $end;

    $q = "SELECT l.user_id, course_id, module_id, details, ip, action_type, ts, user_name, username, email FROM log l
                                JOIN
                                (SELECT user_id, username, email, concat(u.surname, ' ', u.givenname) user_name FROM course_user cui JOIN user u ON cui.user_id=u.id $course_cond $user_cond1) cu ON l.user_id=cu.user_id
                                $user_cond2 $course_cond $module_cond $date_cond
                                ORDER BY ts DESC";
    $r = Database::get()->queryArray($q, $pars);
    $l = new Log();
    $formattedr = array();
    foreach($r AS $record){
        $formattedr[] = array($record->ts, $record->user_name, which_module($record->module_id), $l->get_action_names($record->action_type), $l->course_action_details($record->module_id, $record->details), $record->ip, $record->username, $record->email);
    }
    return $formattedr;
}

/**
 * @brief Detailed list of user visits and duaration in course. The results are
 * listed in the second table of the course detailed statistics
 * @param int $cid the id of the course
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @return array an array appropriate for displaying in a datatables table
*/
function get_course_details($cid, $start = null, $end = null, $user_id = null)
{
    if (is_numeric($user_id)){
        $q = "SELECT day, hits, duration, CONCAT(surname, ' ', givenname) uname, username, email, module_id FROM actions_daily a JOIN user u ON a.user_id=u.id WHERE course_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d ORDER BY day, module_id";
        $r = Database::get()->queryArray($q, $cid, $start, $end, $user_id);
    } else {
        $q = "SELECT day, hits, duration, CONCAT(surname, ' ', givenname) uname, username, email, module_id FROM actions_daily a JOIN user u ON a.user_id=u.id WHERE course_id=?d AND day BETWEEN ?t AND ?t ORDER BY day, module_id";
        $r = Database::get()->queryArray($q, $cid, $start, $end);
    }
    $formattedr = array();
    foreach($r as $record){
       $mtitle = which_module($record->module_id);
       $formattedr[] = array($record->day, $mtitle, $record->uname, $record->hits, $record->duration, $record->username, $record->email);
    }
    return $formattedr;
}

/**
 * Detailed list of user register and unregister actions of a course. The results are
 * listed in the second table of course detailed statistics
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $cid the id of the course
 * @return array an array appropriate for displaying in a datatables table
*/
function get_course_registration_details($cid, $start = null, $end = null)
{
    $formattedr = array();
    $reg_t = "SELECT reg_date day, user_id, CONCAT(surname, ' ', givenname) uname, username, email, 1 action FROM course_user cu JOIN user u ON cu.user_id=u.id WHERE cu.course_id=?d AND cu.reg_date BETWEEN ?t AND ?t ORDER BY cu.reg_date";
    $unreg_t = "SELECT ts day, user_id, CONCAT(surname, ' ', givenname) uname, username, email, 0 action FROM log l JOIN user u ON l.user_id=u.id WHERE l.module_id=".MODULE_ID_USERS." AND l.action_type=".LOG_DELETE." AND l.course_id=?d AND l.ts BETWEEN ?t AND ?t ORDER BY ts";
    $q = "SELECT DATE_FORMAT(x.day,'%d-%m-%Y') day, uname, username, email, action FROM (($reg_t) UNION ($unreg_t)) x  ORDER BY day";
    $r = Database::get()->queryArray($q, $cid, $start, $end, $cid, $start, $end);
    foreach($r as $record){
       $formattedr[] = array($record->day, $record->uname, which_registration_action($record->action), $record->username, $record->email);
    }
    return $formattedr;
}

/************************************** User personal stats *****************************/

/**
 * Get user statistics in terms of her visits and their duration in the platform.
 * The results are shown in the top bar chart of the user stats (first plot)
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param string $interval the time interval to aggregate statistics on, values: 'year'|'month'|'week'|'day'
 * @param int $user the id of the user to filter out the statistics for
 * @param int $course the id of the course
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_user_stats($user, $interval, $start = null, $end = null, $course = null)
{
    $g = build_group_selector_cond($interval);
    $groupby = $g['groupby'];
    $date_components = $g['select'];
    if(is_numeric($course)){
        $q = "SELECT $date_components, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily where user_id = ?d AND day BETWEEN ?t AND ?t AND course_id = ?d $groupby";
        $r = Database::get()->queryArray($q, $user, $start, $end, $course);
    }
    else{
        $q = "SELECT $date_components, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily where user_id = ?d AND day BETWEEN ?t AND ?t $groupby";
        $r = Database::get()->queryArray($q, $user, $start, $end);
    }
    $formattedr = array('time'=>array(),'hits'=>array(),'duration'=>array());
    foreach($r as $record){
       $formattedr['time'][] = $record->cat_title;
       $formattedr['hits'][] = $record->hits;
       $formattedr['duration'][] = $record->dur;
    }
    return $formattedr;
}

/**
 * Get preference statistics of the user based on the visits in different courses of the platform.
 * The results are shown in the pie chart of the user stats (second plot)
 * @param int $user the id of the user to filter out the statistics for
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $course the id of the course
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_course_preference_stats($user, $start = null, $end = null, $course = null){

    $courses = null;
    $pcid = null;
    if(!is_null($course)){
        $formattedr = get_module_preference_stats($course, $start, $end, $user);
    }
    else{
        $q = "SELECT c.id cid, c.title, s.hits, s.dur FROM (SELECT course_id cid, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE user_id=?d AND day BETWEEN ?t AND ?t GROUP BY course_id) s JOIN course c on s.cid=c.id ORDER BY hits DESC LIMIT 10";
        $r = Database::get()->queryArray($q, $user, $start, $end);
        $formattedr = array();
        $courses = array();
        foreach($r as $record){
            $ctitle = $record->title;
            $formattedr[$record->cid] = $record->hits;
            $courses[$record->cid] = $ctitle;
            if(is_null($pcid)){
                $pcid = $record->cid;
            }
        }
    }
    return array('courses'=>$courses,'pcid' => $pcid, 'chartdata' => $formattedr);
}

/**
 * @brief  Get user visits and duration in a course. If the course is specified in the
 * reports' parameters then return the visits in a specified module of a course.
 * The results are shown in a bar chart (third plot) of the user stats
 * @param int $user the id of the user to filter out the statistics for
 * @param int $course the id of the course
 * @param int $module the module id
 * @param string $interval the time interval to aggregate statistics on, values: 'year'|'month'|'week'|'day'
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_user_course_stats($user, $course, $module, $interval, $start = null, $end = null){
    $ctitle = course_id_to_title($course);
    if(!is_null($module) && !empty($module)){
        $formattedr = get_course_module_stats($start, $end, $interval, $course, $module, $user);
    }
    else{
        $formattedr = get_user_stats($user, $interval, $start, $end, $course);
    }
    return array('charttitle'=>$ctitle, 'chartdata'=>$formattedr);
}

/**
 * @brief Detailed list of user's daily aggregated activity in course. The results are
 * listed in the first table of the user detailed statistics
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $user the id of the user to filter out the statistics for
 * @param int $course the id of the course
 * @return array an array appropriate for displaying in a datatables table
*/
function get_user_details($user, $start = null, $end = null, $course = null)
{
    if(is_numeric($course)){
        $q = "SELECT day, hits hits, duration dur, module_id, c.title FROM actions_daily a JOIN course c ON a.course_id=c.id WHERE user_id = ?d AND day BETWEEN ?t AND ?t AND course_id = ?d ORDER BY day";
        $r = Database::get()->queryArray($q, $user, $start, $end, $course);
    }
    else{
        $q = "SELECT day, hits hits, duration dur, c.title, module_id FROM actions_daily a JOIN course c ON a.course_id=c.id where user_id = ?d AND day BETWEEN ?t AND ?t ORDER BY day";
        $r = Database::get()->queryArray($q, $user, $start, $end);
    }
    $formattedr = array();
    foreach($r as $record){
        $mtitle = which_module($record->module_id);
        $formattedr[] = array($record->day, $record->title, $mtitle, $record->hits, $record->dur);
    }
    return $formattedr;
}

/**************************** Admin stats *************************************/


/**
 * @brief Get user login statistics in the platform.
 * The results are shown in the top chart of the admin stats (first plot).
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param string $interval the time interval to aggregate statistics on, values: 'year'|'month'|'week'|'day'
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_user_login_stats($interval, $start = null, $end = null, $root_department = 1){
    $temp = array();
    $g = build_group_selector_cond($interval, 'date_time');
    $groupby = $g['groupby'];
    $date_components = $g['select'];
    $q = "SELECT id, lft, rgt INTO @rootid, @rootlft, @rootrgt FROM hierarchy WHERE id=?d;";
    Database::get()->query($q, $root_department);
    $q1 = "SELECT $date_components, COUNT(*) c FROM logins WHERE "
            . "course_id IN (SELECT course FROM course_department cd JOIN hierarchy h ON cd.department=h.id where h.lft>=@rootlft and h.rgt<=@rootrgt) "
            . "AND date_time BETWEEN ?t AND ?t $groupby";
    $r1 = Database::get()->queryArray($q1, $start, $end);
    foreach($r1 as $record){
        $temp[$record->cat_title]['logins'] = $record->c;
    }

    $g = build_group_selector_cond($interval, 'day');
    $groupby = $g['groupby'];
    $date_components = $g['select'];
    $q2 = "SELECT $date_components, sum(hits) hits FROM actions_daily WHERE "
            . "course_id IN (SELECT course FROM course_department cd JOIN hierarchy h ON cd.department=h.id where h.lft>=@rootlft and h.rgt<=@rootrgt) "
            . "AND day BETWEEN ?t AND ?t $groupby";
    $r2 = Database::get()->queryArray($q2, $start, $end);
    foreach($r2 as $record){
        $temp[$record->cat_title]['visits'] = $record->hits;
    }

    $formattedr = array('time'=>array(),'logins'=>array());
    foreach($temp as $k=>$v){
        $formattedr['time'][] = $k;
        $formattedr['logins'][] = (isset($v['logins']))? $v['logins']:0;
        $formattedr['visits'][] = (isset($v['visits']))? $v['visits']:0;
    }
    return $formattedr;
}

/**
 * Get top-k most popular courses.
 * The results are shown in the top right chart of the admin stats (second plot).
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $root_department the department id for which to show popular courses
 * @param int $k the k parameter of top-k
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_popular_courses_stats($start = null, $end = null, $root_department = 1, $k = 20){
    $q = "SELECT id, lft, rgt INTO @rootid, @rootlft, @rootrgt FROM hierarchy WHERE id=?d;";
    Database::get()->query($q, $root_department);
    $q = "SELECT c.id cid, c.title, s.hits FROM (SELECT course_id cid, sum(hits) hits "
        . "FROM actions_daily WHERE day BETWEEN ?t AND ?t GROUP BY course_id) s "
        . "JOIN course c on s.cid=c.id "
        . "WHERE c.id IN (SELECT course FROM course_department cd JOIN hierarchy h ON cd.department=h.id where h.lft>=@rootlft and h.rgt<=@rootrgt) "
        . "ORDER BY hits DESC LIMIT $k";
    $r = Database::get()->queryArray($q, $start, $end);
    $formattedr = array();
    foreach($r as $record){
        $formattedr['courses'][] = $record->title;
        $formattedr['hits'][] = $record->hits;
    }
    return $formattedr;
}

/**
 * @brief Get number of users per type and per department of the platform.
 * The results are shown in the a bar chart of the admin stats (third plot)
 * @param date $root_department the deprtament for which statistics will be retrieved per subdepartment
 * @param boolean $total if true only the total number of users will be returned
 * (used by the footer of the corresponding datatable), otherwise per subdepartment
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_department_user_stats($root_department = 1, $total = false){
    global $langTeachers, $langStudents, $langGuests;

    $statsUserStatus = array(USER_TEACHER => $langTeachers,
        USER_STUDENT => $langStudents, USER_GUEST => $langGuests);
    /*Simple case to get total users of the platform*/
    $q = "SELECT id, lft, rgt INTO @rootid, @rootlft, @rootrgt FROM hierarchy WHERE id=?d;";
    Database::get()->query($q, $root_department);
    if($total && $root_department == 1){
        $q = "SELECT @rootid root, @rootid did, 'platform' dname, 0 leaf, status, count(distinct user_id) users_count FROM course_user GROUP BY status;";
    }
    else{
        $group_field = ($total)? "root":"toph.id";
        $q = "SELECT @rootid root, toph.id did, toph.name dname, IF((toph.rgt-toph.lft)>1, 0, 1) leaf, status, count(distinct user_id) users_count
        FROM (SELECT ch.id did, ch.name dname, ch.lft, ch.rgt, cu.status, cu.user_id
            FROM course_department cd
            JOIN course_user cu on cd.course=cu.course_id
            JOIN hierarchy ch ON cd.department=ch.id
            JOIN course c ON cd.course=c.id
            WHERE ch.lft BETWEEN @rootlft AND @rootrgt) chh
            RIGHT JOIN (SELECT descendant.id, descendant.name, descendant.lft, descendant.rgt, count(*) c
              FROM hierarchy descendant
              JOIN
              hierarchy ancestor ON descendant.lft>ancestor.lft AND descendant.lft<=ancestor.rgt
              WHERE ancestor.lft>=@rootlft AND ancestor.rgt<=@rootrgt AND descendant.lft>=@rootlft AND descendant.rgt<=@rootrgt
              GROUP BY descendant.id having c=1
            ) toph ON chh.lft>=toph.lft AND chh.lft<=toph.rgt
        GROUP BY ".$group_field.", status ORDER BY did";
    }
    $r = Database::get()->queryArray($q);
    $formattedr = array('department'=>array(),$statsUserStatus[USER_TEACHER]=>array(),$statsUserStatus[USER_STUDENT]=>array());
    $depids = array();
    $leaves = array();
    $d = '';
    $i = -1;
    foreach($r as $record){
        if($record->dname != $d){
            $i++;
            $depids[] = ($total)? $record->root:$record->did;
            $leaves[] = $record->leaf;
            $formattedr['department'][] = hierarchy::unserializeLangField($record->dname);
            $d = $record->dname;
            $formattedr[$statsUserStatus[USER_TEACHER]][] = 0;
            $formattedr[$statsUserStatus[USER_STUDENT]][] = 0;
            $formattedr[$statsUserStatus[USER_GUEST]][] = 0;
       }
       if(!is_null($record->status)){
           $formattedr[$statsUserStatus[$record->status]][$i] = $record->users_count;
       }
    }
    return array('deps'=>$depids,'leafdeps'=>$leaves,'chartdata'=>$formattedr);

}

/**
 * @brief Get number of courses per type and per department of the platform.
 * The results are shown in the a bar chart of the admin stats (fourth plot)
 * @param date $root_department the deprtament for which statistics will be retrieved per subdepartment
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_department_course_stats($root_department = 1){
    global $langTypesInactive, $langTypesAccessControlled, $langTypesOpen, $langTypesClosed;
    $courseVisibility = array(COURSE_CLOSED => $langTypesClosed,
        COURSE_REGISTRATION => $langTypesAccessControlled,
        COURSE_OPEN => $langTypesOpen,
        COURSE_INACTIVE => $langTypesInactive);
    $q = "SELECT lft, rgt INTO @rootlft, @rootrgt FROM hierarchy WHERE id=?d;";
    Database::get()->query($q, $root_department);

    $q = "SELECT toph.id did, toph.name dname, IF((toph.rgt-toph.lft)>1, 0, 1) leaf, visible, SUM(courses) courses_count
      FROM (SELECT ch.lft, ch.rgt, c.visible, count(c.id) courses
            FROM course_department cd
            JOIN hierarchy ch ON cd.department=ch.id
            JOIN course c ON cd.course=c.id
            WHERE ch.lft BETWEEN @rootlft AND @rootrgt
            GROUP BY cd.department, c.visible ) ch
          RIGHT JOIN
          (SELECT descendant.id, descendant.name, descendant.lft, descendant.rgt, count(*) c
            FROM hierarchy descendant
            JOIN
            hierarchy ancestor ON descendant.lft>ancestor.lft AND descendant.lft<=ancestor.rgt
            WHERE ancestor.lft>=@rootlft AND ancestor.rgt<=@rootrgt AND descendant.lft>=@rootlft AND descendant.rgt<=@rootrgt
            GROUP BY descendant.id having c=1) toph
     ON ch.lft>=toph.lft AND ch.lft<=toph.rgt
    GROUP BY toph.id, ch.visible ORDER BY did";
    $r = Database::get()->queryArray($q);
    $formattedr = array('department'=>array(),$courseVisibility[COURSE_CLOSED]=>array(),$courseVisibility[COURSE_REGISTRATION]=>array(), $courseVisibility[COURSE_OPEN]=>array(), $courseVisibility[COURSE_INACTIVE]=>array());
    $depids = array();
    $leaves = array();
    $d = '';
    $i = -1;
    foreach($r as $record){
        if($record->dname != $d){
            $i++;
            $depids[] = $record->did;
            $leaves[] = $record->leaf;
            $formattedr['department'][] = hierarchy::unserializeLangField($record->dname);
            $d = $record->dname;
            $formattedr[$courseVisibility[COURSE_CLOSED]][] = 0;
            $formattedr[$courseVisibility[COURSE_REGISTRATION]][] = 0;
            $formattedr[$courseVisibility[COURSE_OPEN]][] = 0;
            $formattedr[$courseVisibility[COURSE_INACTIVE]][] = 0;
        }
        if(!is_null($record->visible)){
           $formattedr[$courseVisibility[$record->visible]][$i] = $record->courses_count;
        }
    }
    return array('deps'=>$depids,'leafdeps'=>$leaves,'chartdata'=>$formattedr);

}

/**
 * @brief Detailed list of user logins. The results are
 * listed in the first table of the admin detailed statistics
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @return array an array appropriate for displaying in a datatables table
*/
function get_user_login_details($start = null, $end = null, $root_department = 1){
    $q = "SELECT id, lft, rgt INTO @rootid, @rootlft, @rootrgt FROM hierarchy WHERE id=?d;";
    Database::get()->query($q, $root_department);
    $q = "SELECT l.date_time, concat(u.surname, ' ', u.givenname) user_name, username, email, c.title course_title, l.ip
            FROM logins l
            JOIN user u ON l.user_id=u.id
            JOIN course c ON l.course_id=c.id
            WHERE l.course_id IN (SELECT course FROM course_department cd JOIN hierarchy h ON cd.department=h.id where h.lft>=@rootlft and h.rgt<=@rootrgt) ";

    if(!is_null($start) && !empty($start) && !is_null($end) && !empty($end)){
        $q .= " AND l.date_time BETWEEN ?t AND ?t";
        $r = Database::get()->queryArray($q, $start, $end);
    }
    else {
        $r = Database::get()->queryArray($q);
    }
    $formattedr = array();
    foreach($r AS $record){
        $formattedr[] = array($record->date_time, $record->user_name, $record->course_title, $record->ip, $record->username, $record->email);
    }
    return $formattedr;
}

/************************************** Utilities ******************************/

/**
 * Build SELECT and GROUP BY clauses for queries which retrieve stats grouped on a specified time interval
 * @param string $interval a value among 'year', 'month', 'week', 'day'
 * @param string $date_field the name of the date field of the queried table
 * @return array(string, string) an array containing the 'select' and 'groupby' clauses
*/
function build_group_selector_cond($interval = 'month', $date_field = 'day')
{
    $groupby = "";
    $select = "";
    switch($interval){
        case 'year':
            $select = "DATE_FORMAT(MAX($date_field), '%Y-06-30') cat_title, DATE_FORMAT($date_field, '%Y') y";
            $groupby = "GROUP BY y";
            break;
        case 'month':
            $select = "DATE_FORMAT(MAX($date_field), '%Y-%m-15') cat_title, DATE_FORMAT($date_field, '%m') m, DATE_FORMAT($date_field, '%Y') y";
            $groupby = "GROUP BY y, m";
            break;
        case 'week':
            $select = "STR_TO_DATE(DATE_FORMAT($date_field, '%Y%u Monday'), '%X%V %W') cat_title, DATE_FORMAT($date_field,'%u') w, DATE_FORMAT($date_field, '%m') m, DATE_FORMAT($date_field, '%Y') y";
            $groupby = "GROUP BY y, w";
            break;
        case 'day':
            $select = "DATE_FORMAT(MAX($date_field), '%Y-%m-%d') cat_title, DATE_FORMAT($date_field,'%d') d, DATE_FORMAT($date_field,'%u') w, DATE_FORMAT($date_field, '%m') m, DATE_FORMAT($date_field, '%Y') y";
            $groupby = "GROUP BY y, m, d";
            break;
    }
    return array('groupby'=>$groupby,'select'=>$select);
}

/**
 * Count users of the system based on their type
 * @param int $user_type a value among USER_TEACHER, USER_STUDENT, USER_GUEST
 * @return int the number of all the users or of specific type of the system
*/
function count_users($user_type = null){
    if(is_null($user_type)){
        return Database::get()->querySingle("SELECT COUNT(*) as count FROM user")->count;
    }
    elseif(!in_array($user_type,array(USER_TEACHER, USER_STUDENT, USER_GUEST))){
        return 0;
    }
    else{
        return Database::get()->querySingle("SELECT COUNT(*) as count FROM user WHERE status = ?d", $user_type)->count;
    }
}

/**
 * Count courses of the system based on their type
 * @param int $course_type a value among COURSE_INACTIVE, COURSE_OPEN, COURSE_REGISTRATION, COURSE_CLOSED
 * @return int the number of all the users or of specific type of the system
*/
function count_courses($course_type = null){
    if(is_null($course_type)){
        return Database::get()->querySingle("SELECT COUNT(*) as count FROM course")->count;
    }
    elseif(!in_array($course_type,array(COURSE_INACTIVE, COURSE_OPEN, COURSE_REGISTRATION, COURSE_CLOSED))){
        return 0;
    }
    else{
        return Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", $course_type)->count;
    }
}

/**
 * Count users of the system based on their type
 * @param int cid a value among USER_TEACHER, USER_STUDENT
 * @param int $user_type a value among USER_TEACHER, USER_STUDENT
 * @return int the number of all the users or of specific type of the system
*/
function count_course_users($cid, $user_type = null){

    if(is_null($user_type)){
        return Database::get()->querySingle("SELECT COUNT(*) as count FROM course_user WHERE course_id = ?d", $cid)->count;
    }
    elseif(!in_array($user_type,array(USER_TEACHER, USER_STUDENT, USER_GUEST))){
        return 0;
    }
    else{
        return Database::get()->querySingle("SELECT COUNT(*) as count FROM course_user WHERE course_id = ?d AND status = ?d", $cid, $user_type)->count;
    }
}

/**
 * Return module title based on the course module id
 * @param int mid the id of the module
 * @global $modules,
 * @global $admin_modules,
 * @global static_modules,
 * @return string the title of the course module
*/
function which_module($mid){
    global $modules, $static_modules, $admin_modules, $deprecated_modules;

    if (isset($modules[$mid])) {
        $m_title = $modules[$mid]['title'];
    } elseif (isset($admin_modules[$mid])) {
        $m_title = $admin_modules[$mid]['title'];
    }  elseif (isset($static_modules[$mid])) {
        $m_title = $static_modules[$mid]['title'];
    } elseif (isset($deprecated_modules[$mid]['title'])) {
        $m_title = $deprecated_modules[$mid]['title'];
    } else {
        $m_title = 'module '.$mid;
    }
    return $m_title;
}

function which_registration_action($registration_action_id) {
    global $langRegistration, $langUnRegister;

    if ($registration_action_id == 1) {
        $r_title = $langRegistration;
    } else {
        $r_title = $langUnRegister;
    }
    return $r_title;
}

/**
 * Count user groups of the course
 * @param int $cid cid the course id
 * @return int the number of user groups
*/
function count_course_groups($cid){

    return Database::get()->querySingle("SELECT COUNT(*) as count FROM `group` WHERE course_id = ?d", $cid)->count;
}

/**
 * Get the sum of hits with their duration for a course
 * @param int $cid cid the course id
 * @param int $userid userid the user id
 * @return array(int, string) an array with the number of visits and their duration formatted as h:mm:ss
*/
function course_hits($cid, $userid = 0){
    if ($userid > 0) {
        $r = Database::get()->querySingle("SELECT SUM(hits) hits FROM actions_daily
                            WHERE course_id = ?d
                            AND user_id = ?d
                            AND module_id != " . MODULE_ID_TC, $cid, $userid);

        return $r->hits;
    } else {
        $r = Database::get()->querySingle("SELECT SUM(hits) hits, SUM(ABS(duration)) dur FROM actions_daily WHERE course_id=?d", $cid);
        return array('hits' => $r->hits, 'duration' => user_friendly_seconds($r->dur));
    }
}

/**
 * @brief Get course logins
 * @param type $cid
 * @return type
 */
function course_visits($cid) {

    $logins = Database::get()->querySingle("SELECT COUNT(id) AS cnt FROM logins WHERE course_id = ?d", $cid)->cnt;

    return $logins;
}

/**
 * @brief Transform seconds to h:mm:ss
 * @param int $seconds the number of seconds to be shown properly
 * @return string a formated time string
*/
function user_friendly_seconds($seconds){
    $hours = floor($seconds / 3600);
    $mins = floor(($seconds - ($hours*3600)) / 60);
    $secs = floor($seconds % 60);
    $fd = ($hours<10)? '0'.$hours:$hours;
    $fd .= ':';
    $fd .= ($mins<10)? '0'.$mins:$mins;
    $fd .= ':';
    $fd .= ($secs<10)? '0'.$secs:$secs;
    return $fd;
}

/**
 * @brief get course user registration date
 * @param $course_id
 * @param $user_id
 * @return void
 */
function get_course_user_registration($course_id, $user_id) {
    $q = Database::get()->querySingle("SELECT DATE_FORMAT(DATE(reg_date),'%e-%c-%Y') AS reg_date
                    FROM course_user
                    WHERE course_id = ?d AND user_id = ?d ORDER BY reg_date ASC LIMIT 1", $course_id, $user_id);
    if ($q) {
        return $q->reg_date;
    }
}


/**
 * @brief Create the panel to show a plot
 * @param string $plot_id the id of the id of the div where the plot will be drawn
 * @param string $title the caption of the plot
 * @return string a formatted element ready to display a plot
*/
function plot_placeholder($plot_id, $title = null){
    $p = "<div class='panel panel-default'><div class='panel-body'>";
    if(!is_null($title)){
        $p .= "<div class='inner-heading'><h3 id='{$plot_id}_title'>"
            . q($title)
            . "</h3>"

            . "</div>";
    }
    $p .= "<div id='$plot_id'></div>";
    $p .= "</div></div>";
    return $p;
}

/**
 * Create the panel and the table structure of a table to be filled with AJAX with data
 * @param string $table_id table_id the id of the table in the DOM
 * @param string $table_class table_class the class of the table element
 * @param string $table_schema table_schema the header and footer of the table which also specify the column number of the table
 * @param string $title title the caption of the table
 * @return string a formatted element containing the specified table
*/
function table_placeholder($table_id, $table_class, $table_schema, $title = null){
    $t = "<div class='panel-body'>";
    if(!is_null($title)){
        $t .= "<div class='inner-heading d-flex justify-content-between align-items-start gap-3 flex-wrap'>"
            . "<h3 id='".$table_id."_title'>"
            . q($title)
            . "</h3>"
            ."<div id='{$table_id}_buttons'></div>"
            . "</div>";
    }
    $t .= "<div class='table-responsive'>"
       . "<table id='$table_id' class='$table_class'>"
       . "$table_schema"
       . "</table>"
       . "</div></div>";
    return $t;
}

/**
 * @brief calculate user duration per course
 * @param integer $uid
*/
function user_duration_per_course($u) {

    global $tool_content, $langDurationVisitsPerCourse, $langNotEnrolledToLessons, $langInfoUserDuration, $langTotalDuration;

    $totalDuration = 0;
    $result = Database::get()->queryArray("SELECT SUM(hits) AS cnt, SUM(ABS(duration)) AS duration, course.code
                                        FROM course
                                        LEFT JOIN course_user ON course.id = course_user.course_id
                                        LEFT JOIN actions_daily
                                        ON actions_daily.user_id = course_user.user_id
                                        AND actions_daily.course_id = course_user.course_id
                                        AND actions_daily.module_id != " . MODULE_ID_TC . "
                                        WHERE course_user.user_id = ?d
                                        AND course.visible != " . COURSE_INACTIVE . "
                                        GROUP BY course.id
                                        ORDER BY duration DESC", $u);
    if (count($result) > 0) {  // found courses ?
        foreach ($result as $item) {
            $totalDuration += $item->duration;
            $duration[$item->code] = $item->duration;
        }

    $totalDuration = format_time_duration(0 + $totalDuration, 240);

    $tool_content .= "<div class='text-center mb-4'>
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langInfoUserDuration</span></div>
                        <h3>"  . q(uid_to_name($_SESSION['uid'])) . "</h3>
                        <span><strong>$langTotalDuration:</strong> " . $totalDuration . "</span>
                        
                    </div>";

    $tool_content .= "
                <div class='margin-bottom-fat margin-top-fat mb-4'>
                  <div class='col-12'>
                    <ul class='list-group list-group-flush'>
                      <li class='list-group-item list-group-item-action'>
                          <div>$langDurationVisitsPerCourse</div>
                      </li>";
    foreach ($duration as $code => $time) {
        $tool_content .= "
                      <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                          <div>" . q(course_code_to_title($code)) . "</div>
                          <div>" . format_time_duration(0 + $time) . "</div>
                      </li>";
    }
    $tool_content .= "
                    </ul>
                  </div>
                </div>";
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNotEnrolledToLessons</span></div></div>";
    }

}

/**
 * @brief calculate user duration in course
 * @param $u
 * @return int|string
 */
function user_duration_course($u) {

    global $course_id;

    $totalDuration = 0;
    $q = Database::get()->querySingle("SELECT SUM(ABS(duration)) AS duration
                                FROM actions_daily
                                WHERE course_id = ?d
                                AND user_id = ?d
                                AND actions_daily.module_id != " . MODULE_ID_TC,
                            $course_id, $u);

    if ($q) {
        $totalDuration = format_time_duration(0 + $q->duration, 24, false);
    }
    return $totalDuration;
}


/**
 * @brief get user 5 last logins
 * @param type $u
 */
function user_last_logins($u) {

    global $langLastVisits, $tool_content;

    $result = Database::get()->queryArray("SELECT * FROM loginout
                                        WHERE id_user = ?d ORDER by idLog DESC LIMIT 5", $u);
    if (count($result) > 0) {
        $tool_content .= "<div class='margin-bottom-fat margin-top-fat mb-3'>
                  <div class='col-12'>
                    <ul class='list-group list-group-flush'>
                       <li class='list-group-item list-group-item-action'>
                            <div>$langLastVisits</div>
                        </li>";

        foreach ($result as $lastVisit) {
            $tool_content .= "<li class='list-group-item element'>
                                <div>" . format_locale_date(strtotime($lastVisit->when)) . "</div>
                              </li>";
        }
        $tool_content .= "
                    </ul>
                  </div>
                </div>";
    } else {
        $tool_content .= '';
    }
}


/**
 * Get old statistics of visits and visit duration to a course.
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $cid the id of the course
 * @param int $user_id the id of the user to filter out the statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_course_old_stats($cid, $mid, $start = null, $end = null)
{
    $formattedr = array('time'=> array(), 'hits'=> array(), 'duration'=> array());
    if(!is_null($start) && !is_null($end && !empty($start) && !empty($end))){
        $g = build_group_selector_cond('month', 'start_date');
        $groupby = $g['groupby'];
        $date_components = $g['select'];
        if(is_numeric($mid) && $mid>0){
            $q = "SELECT $date_components, SUM(visits) visits, ROUND(SUM(duration)/3600) dur FROM actions_summary WHERE course_id=?d AND start_date BETWEEN ?t AND ?t AND module_id=?d $groupby";
            $r = Database::get()->queryArray($q, $cid, $start, $end, $mid);
        }
        else{
            $q = "SELECT $date_components, SUM(visits) visits, ROUND(SUM(duration)/3600) dur FROM actions_summary WHERE course_id=?d AND start_date BETWEEN ?t AND ?t $groupby";
            $r = Database::get()->queryArray($q, $cid, $start, $end);
        }
        foreach($r as $record){
           $formattedr['time'][] = $record->cat_title;
           $formattedr['hits'][] = $record->visits;
           $formattedr['duration'][] = $record->dur;
        }
    }
    return $formattedr;
}

/**
 * @brief Get old statistics of logins/logouts to the platform.
 * @return array an array appropriate for displaying in a c3 plot when json encoded
*/
function get_login_old_stats(): array
{

    $r = get_user_login_archives();
    foreach($r as $data) {
        $formattedr['time'][] = $data[0];
        $formattedr['hits'][] = $data[1];
    }
    return $formattedr;
}

/**
 * @brief get user logins. First we seek archived logins in `login_sum` table.
 * Otherwise we seek `loginout` table and update the `login_sum`
 * @return array
 */
function get_user_login_archives(): array
{
    $content = [];
    $start = new DateTime('now');
    $interval = DateInterval::createFromDateString('first day of last month');
    $period = new DatePeriod($start, $interval, 12, DatePeriod::EXCLUDE_START_DATE); // last 12 months

    foreach($period as $time) {
        $year_month_val = $time->format("Y-m");
        $data = Database::get()->querySingle("SELECT login_sum,
                                    DATE_FORMAT(start_date,'%Y-%m-%d') AS start_date
                            FROM loginout_summary
                            WHERE DATE_FORMAT(start_date,'%Y-%m') = ?s", $year_month_val);
        if ($data) {
            $data_month_year = $data->start_date;
            $content[] = [ $data_month_year, $data->login_sum ];
        } else {
            $start_date = date_create($year_month_val);
            $formatted_start_date = $start_date->format("Y-m-d H:i:s");
            $end_date = date_add($start_date, date_interval_create_from_date_string("30 days"));
            $formatted_end_date = $end_date->format("Y-m-d H:i:s");
            $q_cnt = Database::get()->querySingle("SELECT COUNT(*) AS cnt
                            FROM loginout
                            WHERE action='LOGIN' AND
                        `when` BETWEEN DATE(?s) AND DATE_ADD(DATE(?s), INTERVAL 1 MONTH)",
                $time->format("Y-m-d") , $time->format("Y-m-d"));
            $content[] = [ $year_month_val."-01", $q_cnt->cnt ];

            Database::get()->query("INSERT INTO loginout_summary (login_sum, start_date, end_date) VALUES (?s, ?s, ?s)",
                                            $q_cnt->cnt, $formatted_start_date, $formatted_end_date);
        }
    }
    return $content;
}

/**
 * @brief get monthly statistics (e.g. courses, users).  First we seek archived statistics in `monthly_summary` table.
 * Otherwise we seek `courses` and `users` table and update the `monthly_summary`.
 * @return array
 */
function get_monthly_archives(): array
{
    $content = [];
    $start = new DateTime('now');
    $interval = DateInterval::createFromDateString('first day of last month');
    $period = new DatePeriod($start, $interval, 24, DatePeriod::EXCLUDE_START_DATE); // last 12 months

    foreach ($period as $time) {
        $year_month_val = $time->format("Y-m");
        $data = Database::get()->querySingle("SELECT profesNum, studNum, visitorsNum, coursNum,
                                    DATE_FORMAT(month,'%Y-%m') AS month
                            FROM monthly_summary
                            WHERE DATE_FORMAT(month,'%Y-%m') = ?s", $year_month_val);
        if ($data) {
            $data_month_year = $data->month;
            $content[] = [ $data_month_year."-01", $data->profesNum, $data->studNum, $data->visitorsNum, $data->coursNum ];
        } else {
            $year_month_day = $time->format("Y-m-d");
            $cnt_courses = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM course WHERE created <= ?t AND visible != ". COURSE_INACTIVE, $year_month_day)->cnt;
            $cnt_prof = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = " . USER_TEACHER . " AND registered_at <= ?t AND expires_at > " . DBHelper::timeAfter() , $year_month_day)->cnt;
            $cnt_students = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = " . USER_STUDENT . " AND registered_at <= ?t AND expires_at > " . DBHelper::timeAfter(), $year_month_day)->cnt;
            $cnt_guest = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = " . USER_GUEST . " AND registered_at <= ?t AND expires_at > " . DBHelper::timeAfter(), $year_month_day)->cnt;
            $content[] = [ $year_month_day, $cnt_prof, $cnt_students, $cnt_guest, $cnt_courses ];

            Database::get()->query("INSERT INTO monthly_summary (month, profesNum, studNum, visitorsNum, coursNum) VALUES (?t, ?s, ?s, ?s, ?s)",
                                        $year_month_day, $cnt_prof, $cnt_students, $cnt_guest, $cnt_courses);
        }
    }
    return $content;
}
