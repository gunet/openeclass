<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

require_once 'include/log.php';

/**
 * Get statistics of visits and visit duration to a course. The results are 
 * depicted in te first plot of course statistics 
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param string $interval the time interval to aggregate statistics on
 * @param int $cid the id of the course
 * @param int $user_id the id of the user to filter out the statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded 
*/
function get_course_stats($start = null, $end = null, $interval, $cid, $user_id = null)
{
    global $langHits,$langDuration;
    $formattedr = array('time'=> array(), 'hits'=> array(), 'duration'=> array());
    if(!is_null($start) && !is_null($end && !empty($start) && !empty($end))){
        $g = build_group_selector_cond($interval);
        $groupby = $g['groupby'];
        $date_components = $g['select'];
        if(is_numeric($user_id)){
            $q = "SELECT $date_components, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d $groupby";
            $r = Database::get()->queryArray($q, $cid, $start, $end, $user_id);    
        }
        else{
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
 * Get statistics of user register and unregister actions of a course. The results are 
 * depicted in the last plot of course statistics 
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param string $interval the time interval to aggregate statistics on
 * @param int $cid the id of the course
*/
function get_course_registration_stats($start = null, $end = null, $interval, $cid)
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
        error_log("course_id=".$cid);
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
 * Detailed list of user register and unregister actions of a course. The results are 
 * listed in the second table of course detailed statistics 
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $cid the id of the course
*/
function get_course_registration_details($start = null, $end = null, $cid)
{
    $formattedr = array();
    $reg_t = "SELECT reg_date day, user_id, CONCAT(surname, ' ', givenname) uname, username, email, 1 action FROM course_user cu JOIN user u ON cu.user_id=u.id WHERE cu.course_id=?d AND cu.reg_date BETWEEN ?t AND ?t ORDER BY cu.reg_date";
    $unreg_t = "SELECT ts day, user_id, CONCAT(surname, ' ', givenname) uname, username, email, 0 action FROM log l JOIN user u ON l.user_id=u.id WHERE l.module_id=".MODULE_ID_USERS." AND l.action_type=".LOG_DELETE." AND l.course_id=?d AND l.ts BETWEEN ?t AND ?t ORDER BY ts";
    $q = "SELECT DATE_FORMAT(x.day,'%d-%m-%Y') day, uname, username, email, action FROM (($reg_t) UNION ($unreg_t)) x  ORDER BY day";
    $r = Database::get()->queryArray($q, $cid, $start, $end, $cid, $start, $end);
    foreach($r as $record){
       $formattedr[] = array($record->day, $record->uname, $record->action, $record->username, $record->email);
    }
    return $formattedr;
}

/**
 * Get statistics of visits and visit duration to a module of a course. The 
 * results are depicted in the plot of module statistics beside the pie of the 
 * course statistics page. 
 * preferable. The results are shown in a pie of the course stats
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $cid the id of the course
 * @param int $user_id the id of the user to filter out the statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded 
*/
function get_module_preference_stats($start = null, $end = null, $cid, $user_id = null){
    
    global $modules;
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
        $mtitle = (isset($modules[$record->mdl]))? $modules[$record->mdl]['title']:'module '.$record->mdl;
        $formattedr[$mtitle] = $record->hits;
        $mdls[] = $record->mdl;
        if(is_null($pmid)){
            $pmid = $record->mdl;
        }
    }
    return array('modules'=>$mdls,'pmid' => $pmid, 'chartdata' => $formattedr);
}

/**
 * Get preference statistics on the visits of different modules of the course. The 
 * preference distribution is based on visits but can be changed to duration if 
 * preferable. The results are shown in a pie of the course stats
 * @param date $start the start of period to retrieve statistics for
 * @param date $end the end of period to retrieve statistics for
 * @param int $cid the id of the course
 * @param int $user_id the id of the user to filter out the statistics for
 * @return array an array appropriate for displaying in a c3 plot when json encoded 
*/
function get_course_module_stats($start = null, $end = null, $interval, $cid, $mid, $user_id = null){
    global $modules;
    $mtitle = (isset($modules[$mid]))? $modules[$mid]['title']:'module '.$mid;
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

function get_course_details($start = null, $end = null, $interval, $cid, $user_id = null)
{
    global $modules;
    if(is_numeric($user_id)){
        $q = "SELECT day, hits, duration, CONCAT(surname, ' ', givenname) uname, username, email, module_id FROM actions_daily a JOIN user u ON a.user_id=u.id WHERE course_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d ORDER BY day, module_id";
        $r = Database::get()->queryArray($q, $cid, $start, $end, $user_id);    
    }
    else{
        $q = "SELECT day, hits, duration, CONCAT(surname, ' ', givenname) uname, username, email, module_id FROM actions_daily a JOIN user u ON a.user_id=u.id WHERE course_id=?d AND day BETWEEN ?t AND ?t ORDER BY day, module_id";
        $r = Database::get()->queryArray($q, $cid, $start, $end);
    }
    $formattedr = array();
    foreach($r as $record){
       $mtitle = (isset($modules[$record->module_id]))? $modules[$record->module_id]['title']:'module '.$record->module_id;
       $formattedr[] = array($record->day, $mtitle, $record->uname, $record->hits, $record->duration, $record->username, $record->email);
    }
    return $formattedr;
}

/*** User personal stats ***/

function get_user_stats($start = null, $end = null, $interval, $user, $course = null)
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

function get_course_preference_stats($start = null, $end = null, $user, $course = null){
    
    $courses = null;
    $pcid = null;
    if(!is_null($course)){
        $formattedr = get_module_preference_stats($start, $end, $course, $user);
    }
    else{
        $q = "SELECT c.id cid, c.title, s.hits, s.dur from (SELECT course_id cid, sum(hits) hits, round(sum(duration)/3600,1) dur FROM actions_daily WHERE user_id=?d AND day BETWEEN ?t AND ?t GROUP BY course_id) s JOIN course c on s.cid=c.id ORDER BY hits DESC";
        $r = Database::get()->queryArray($q, $user, $start, $end);
        $formattedr = array();
        $courses = array();
        foreach($r as $record){
            $ctitle = $record->title;
            $formattedr[$ctitle] = $record->hits;
            $courses[] = $record->cid;
            if(is_null($pcid)){
                $pcid = $record->cid;
            }
        }
    }
    return array('courses'=>$courses,'pcid' => $pcid, 'chartdata' => $formattedr);
}

function get_user_course_stats($start = null, $end = null, $interval, $user, $course, $module){
    $ctitle = course_id_to_title($course);
    if(!is_null($module) && !empty($module)){
        $formattedr = get_course_module_stats($start, $end, $interval, $course, $module, $user);
    }
    else{
        $formattedr = get_user_stats($start, $end, $interval, $user, $course);
    }
    return array('charttitle'=>$ctitle, 'chartdata'=>$formattedr);
}

function get_user_details($start = null, $end = null, $interval, $user, $course = null)
{
    global $modules;
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
        $mtitle = (isset($modules[$record->module_id]))? $modules[$record->module_id]['title']:'module '.$record->module_id;
        $formattedr[] = array($record->day, $record->title, $mtitle, $record->hits, $record->dur);
    }
    return $formattedr;
}

/*** admin stats ***/
function get_department_course_stats($root_department = 1){
    global $langCourseVisibility;
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
    $formattedr = array('department'=>array(),$langCourseVisibility[COURSE_CLOSED]=>array(),$langCourseVisibility[COURSE_REGISTRATION]=>array(), $langCourseVisibility[COURSE_OPEN]=>array(), $langCourseVisibility[COURSE_INACTIVE]=>array());
    $depids = array();
    $leaves = array();
    $d = '';
    $i = -1;
    foreach($r as $record){
        if($record->dname != $d){
            $i++;
            $depids[] = $record->did;
            $leaves[] = $record->leaf;
            $formattedr['department'][] = $record->dname;
            $d = $record->dname;
            $formattedr[$langCourseVisibility[COURSE_CLOSED]][] = 0;
            $formattedr[$langCourseVisibility[COURSE_REGISTRATION]][] = 0;
            $formattedr[$langCourseVisibility[COURSE_OPEN]][] = 0;
            $formattedr[$langCourseVisibility[COURSE_INACTIVE]][] = 0;
        }
        if(!is_null($record->visible)){
           $formattedr[$langCourseVisibility[$record->visible]][$i] = $record->courses_count;
        }
    }
    return  array('deps'=>$depids,'leafdeps'=>$leaves,'chartdata'=>$formattedr);

}

function get_department_user_stats($root_department = 1, $total = false){
    global $langStatsUserStatus;
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
    $r = Database::get()->queryArray($q);//,$root_department);
    $formattedr = array('department'=>array(),$langStatsUserStatus[USER_TEACHER]=>array(),$langStatsUserStatus[USER_STUDENT]=>array());
    $depids = array();
    $leaves = array();
    $d = '';
    $i = -1;
    foreach($r as $record){
        if($record->dname != $d){
            $i++;
            $depids[] = ($total)? $record->root:$record->did;
            $leaves[] = $record->leaf;
            $formattedr['department'][] = $record->dname;
            $d = $record->dname;
            $formattedr[$langStatsUserStatus[USER_TEACHER]][] = 0;
            $formattedr[$langStatsUserStatus[USER_STUDENT]][] = 0;
       }
       if(!is_null($record->status)){
           $formattedr[$langStatsUserStatus[$record->status]][$i] = $record->users_count;
       }
    }
    return array('deps'=>$depids,'leafdeps'=>$leaves,'chartdata'=>$formattedr);

}

function get_user_login_stats($start = null, $end = null, $interval, $user, $course, $module){
    $g = build_group_selector_cond($interval, 'date_time');
    $groupby = $g['groupby'];
    $date_components = $g['select'];
    $q = "SELECT $date_components, COUNT(*) c FROM logins WHERE date_time BETWEEN ?t AND ?t $groupby";
    $r = Database::get()->queryArray($q, $start, $end);
    $formattedr = array('time'=>array(),'logins'=>array());
    foreach($r as $record){
        $formattedr['time'][] = $record->cat_title;
        $formattedr['logins'][] = $record->c;
    }
    return $formattedr;
}

function get_user_login_details($start = null, $end = null, $interval, $user, $course, $module){
    $q = "SELECT l.date_time, concat(u.surname, ' ', u.givenname) user_name, username, email, c.title course_title, l.ip 
            FROM logins l 
            JOIN user u ON l.user_id=u.id 
            JOIN course c ON l.course_id=c.id 
            WHERE l.date_time BETWEEN ?t AND ?t";
    $r = Database::get()->queryArray($q, $start, $end);
    $formattedr = array();
    foreach($r AS $record){
        $formattedr[] = array($record->date_time, $record->user_name, $record->course_title, $record->ip, $record->username, $record->email);
    }
    return $formattedr;
}

function build_group_selector_cond($interval = 'month', $date_field = 'day')
{
    $groupby = "";
    $select = "";
    switch($interval){
        case 'year':
            $select = "DATE_FORMAT($date_field, '%Y-01-01') cat_title, DATE_FORMAT($date_field, '%Y') y";
            $groupby = "GROUP BY y";
            break;
        case 'month':
            $select = "DATE_FORMAT($date_field, '%Y-%m-01') cat_title, DATE_FORMAT($date_field, '%m') m, DATE_FORMAT($date_field, '%Y') y";
            $groupby = "GROUP BY y, m";
            break;
        case 'week':
            $select = "STR_TO_DATE(DATE_FORMAT($date_field, '%Y%u Monday'), '%X%V %W') cat_title, DATE_FORMAT($date_field,'%u') w, DATE_FORMAT($date_field, '%m') m, DATE_FORMAT($date_field, '%Y') y";
            $groupby = "GROUP BY y, w";
            break;
        case 'day':
            $select = "DATE_FORMAT($date_field, '%Y-%m-%d') cat_title, DATE_FORMAT($date_field,'%d') d, DATE_FORMAT($date_field,'%u') w, DATE_FORMAT($date_field, '%m') m, DATE_FORMAT($date_field, '%Y') y";
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
        return Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d", $course_type)->count;
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
 * Count users of the system based on their type
 * @param int cid a value among USER_TEACHER, USER_STUDENT
 * @param int $user_type a value among USER_TEACHER, USER_STUDENT
 * @return int the number of all the users or of specific type of the system 
*/
function count_course_groups($cid){
    
    return Database::get()->querySingle("SELECT COUNT(*) as count FROM `group` WHERE course_id = ?d", $cid)->count;
}

function course_visits($cid){
    $r = Database::get()->querySingle("SELECT sum(hits) hits, sum(duration) dur FROM actions_daily WHERE course_id=?d", $cid);
    return array('hits' => $r->hits, 'duration' => user_friendly_seconds($r->dur));
}


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

function plot_placeholder($plot_id, $title = null){
    $p = "<ul class='list-group'>";
    if(!is_null($title)){
        $p .= "<li class='list-group-item'>"
            . "<label id='".$plot_id."_title'>"
            . $title
            . "</label>"
            . "</li>";
    }
    $p .= "<li class='list-group-item'>"
            . "<div id='$plot_id'></div>"
            . "</li></ul>";
    return $p;
}

function table_placeholder_old($table_id, $table_class, $table_schema, $title = null){
    $t = "<ul class='list-group'>";
    if(!is_null($title)){
        $t .= "<li class='list-group-item'>"
            . "<label id='".$table_id."_title'>"
            . $title
            . "</label>"
            ."<div class='pull-right' id='{$table_id}_buttons'></div>"
            . "</li>";
    }
    $t .= "<li class='list-group-item'>"
       . "<table id='$table_id' class='$table_class'>"
       . "$table_schema"
       . "</table>"
       . "</li></ul>";
    return $t;
}

function table_placeholder($table_id, $table_class, $table_schema, $title = null){
    $t = "";
    if(!is_null($title)){
        $t .= "<div class='panel-heading'>"
            . "<label id='".$table_id."_title'>"
            . $title
            . "</label>"
            ."<div class='pull-right' id='{$table_id}_buttons'></div><div style='clear:both;'></div>"
            . "</div>";
    }
    $t .= "<div class='panel-body'>"
       . "<table id='$table_id' class='$table_class'>"
       . "$table_schema"
       . "</table>"
       . "</div>";
    return $t;
}
