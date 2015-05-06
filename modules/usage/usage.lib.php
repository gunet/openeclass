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

function get_course_stats($start = null, $end = null, $interval, $cid, $user_id = null)
{
    $formattedr = array('time'=> array(), 'hits'=> array(), 'duration'=> array());
    if(!is_null($start) && !is_null($end && !empty($start) && !empty($end))){
        $g = build_group_selector_cond($interval);
        $groupby = $g['groupby'];
        $date_components = $g['select'];
        if(is_numeric($user_id)){
            $q = "SELECT $date_components, sum(hits) hits, sum(duration) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d $groupby";
            $r = Database::get()->queryArray($q, $cid, $start, $end, $user_id);    
        }
        else{
            $q = "SELECT $date_components, sum(hits) hits, sum(duration) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t $groupby";
            $r = Database::get()->queryArray($q, $cid, $start, $end);
        }
        error_log("$q, $cid, $start, $end");
        foreach($r as $record){
           $formattedr['time'][] = $record->cat_title;
           $formattedr['hits'][] = $record->hits;
           $formattedr['duration'][] = $record->dur;
        }
    }
    return $formattedr;
}

function get_module_preference_stats($start = null, $end = null, $cid, $user_id = null){
    
    global $modules;
    if(is_numeric($user_id)){
        $q = "SELECT module_id mdl, sum(hits) hits, sum(duration) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d GROUP BY module_id ORDER BY hits DESC";
        $r = Database::get()->queryArray($q, $cid, $start, $end, $user_id);    
    }
    else{
        $q = "SELECT module_id mdl, sum(hits) hits, sum(duration) dur FROM actions_daily WHERE course_id=?d AND day BETWEEN ?t AND ?t GROUP BY module_id ORDER BY hits DESC";
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

function get_course_module_stats($start = null, $end = null, $interval, $cid, $mid, $user_id = null){
    global $modules;
    $mtitle = (isset($modules[$mid]))? $modules[$mid]['title']:'module '.$mid;
    $g = build_group_selector_cond($interval);
    $groupby = $g['groupby'];
    $date_components = $g['select'];
    if(is_numeric($user_id)){
        $q = "SELECT $date_components, sum(hits) hits, sum(duration) dur FROM actions_daily WHERE course_id=?d AND module_id=?d AND day BETWEEN ?t AND ?t AND user_id=?d $groupby";
        $r = Database::get()->queryArray($q, $cid, $mid, $start, $end, $user_id);    
    }
    else{
        $q = "SELECT $date_components, sum(hits) hits, sum(duration) dur FROM actions_daily WHERE course_id=?d AND module_id=?d AND day BETWEEN ?t AND ?t $groupby";
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


/*** User personal stats ***/

function get_user_stats($start = null, $end = null, $interval, $user, $course = null)
{
    $g = build_group_selector_cond($interval);
    $groupby = $g['groupby'];
    $date_components = $g['select'];
    if(is_numeric($course)){
        $q = "SELECT $date_components, sum(hits) hits, sum(duration) dur FROM actions_daily where user_id = ?d AND day BETWEEN ?t AND ?t AND course_id = ?d $groupby";
        $r = Database::get()->queryArray($q, $user, $start, $end, $course);    
    }
    else{
        $q = "SELECT $date_components, sum(hits) hits, sum(duration) dur FROM actions_daily where user_id = ?d AND day BETWEEN ?t AND ?t $groupby";
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
        $q = "SELECT c.id cid, c.title, s.hits, s.dur from (SELECT course_id cid, sum(hits) hits, sum(duration) dur FROM actions_daily WHERE user_id=?d AND day BETWEEN ?t AND ?t GROUP BY course_id) s JOIN course c on s.cid=c.id ORDER BY hits DESC";
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

/*** admin stats ***/
function get_admin_stats($start = null, $end = null, $interval, $user, $course, $module)
{
    $g = build_group_selector_cond($interval);
    $groupby = $g['groupby'];
    $date_components = $g['select'];
    $q = "SELECT $date_components, sum(hits) hits, sum(duration) dur FROM actions_daily WHERE day BETWEEN ?t AND ?t $groupby";
    
    $r = Database::get()->queryArray($q, $start, $end);
    $formattedr = array('time'=>array(),'hits'=>array(),'duration'=>array());
    foreach($r as $record){
       $formattedr['time'][] = $record->cat_title;
       $formattedr['hits'][] = $record->hits;
       $formattedr['duration'][] = $record->dur;
    }
    return $formattedr;
}

function build_group_selector_cond($interval = 'month')
{
    $groupby = "";
    $select = "";
    switch($interval){
        case 'year':
            $select = "DATE_FORMAT(day, '%Y') cat_title, DATE_FORMAT(day,'%d') d, DATE_FORMAT(day,'%u') w, DATE_FORMAT(day, '%m') m, DATE_FORMAT(day, '%Y') y";
            $groupby = "GROUP BY y";
            break;
        case 'month':
            $select = "CONCAT(DATE_FORMAT(day, '%m'),'-',DATE_FORMAT(day, '%Y')) cat_title, DATE_FORMAT(day,'%d') d, DATE_FORMAT(day,'%u') w, DATE_FORMAT(day, '%m') m, DATE_FORMAT(day, '%Y') y";
            $groupby = "GROUP BY y, m";
            break;
        case 'week':
            $select = "CONCAT(DATE_FORMAT(day, '%u'),'-',DATE_FORMAT(day, '%Y')) cat_title, DATE_FORMAT(day,'%d') d, DATE_FORMAT(day,'%u') w, DATE_FORMAT(day, '%m') m, DATE_FORMAT(day, '%Y') y";
            $groupby = "GROUP BY y, w";
            break;
        case 'day':
            $select = "CONCAT(DATE_FORMAT(day, '%d'),'-',DATE_FORMAT(day, '%m'),'-',DATE_FORMAT(day, '%Y')) cat_title, DATE_FORMAT(day,'%d') d, DATE_FORMAT(day,'%u') w, DATE_FORMAT(day, '%m') m, DATE_FORMAT(day, '%Y') y";
            $groupby = "GROUP BY y, m, d";
            break;
    }
    return array('groupby'=>$groupby,'select'=>$select);
}


