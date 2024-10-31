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

function get_user_notifications(){
    global $uid, $course_id;
    if(empty($uid)){
        return null;
    }
    $q = "SELECT l.course_id, l.module_id, count(l.id) notcount notification_count, ua.last_visit
            FROM log l
            JOIN course_user cu ON l.course_id=cu.course_id
            LEFT JOIN
            (SELECT cu.course_id, ad.module_id, MAX(ad.last_update) last_visit FROM course_user cu
            JOIN actions_daily ad ON cu.user_id=ad.user_id AND cu.course_id=ad.course_id
            WHERE cu.user_id = ?d
            GROUP BY ad.course_id, ad.module_id) ua
            ON l.course_id=ua.course_id AND l.module_id=ua.module_id
            WHERE cu.user_id = ?d AND l.action_type=1 AND l.user_id <> ?d AND (l.ts>ua.last_visit OR ua.last_visit is null)
            GROUP BY l.course_id, l.module_id";

    return Database::get()->queryArray($q,$uid,$uid,$uid);
}

function get_course_notifications($course = NULL){
    global $uid, $course_id;
    if(empty($uid) || (empty($course_id) && empty($course))){
        return null;
    }
    $course = (is_null($course))? $course_id:$course;
    $q = "SELECT l.course_id, l.module_id, count(l.id) notcount, ua.last_visit
            FROM log l
            JOIN course_user cu ON l.course_id=cu.course_id
            LEFT JOIN
            (SELECT cu.course_id, ad.module_id, MAX(ad.last_update) last_visit FROM course_user cu
            JOIN actions_daily ad ON cu.user_id=ad.user_id AND cu.course_id=ad.course_id
            WHERE cu.user_id = ?d AND cu.course_id = ?d
            GROUP BY ad.course_id, ad.module_id) ua
            ON l.course_id=ua.course_id AND l.module_id=ua.module_id
            WHERE cu.user_id = ?d AND l.course_id = ?d AND l.module_id <> " . MODULE_ID_MESSAGE . " AND l.action_type=1 AND l.user_id<>?d AND (l.ts>ua.last_visit OR ua.last_visit is null)
            GROUP BY l.course_id, l.module_id";

    return Database::get()->queryArray($q,$uid,$course,$uid,$course,$uid);
}

function get_course_module_notifications($course = NULL, $module = NULL){
    global $uid, $course_id;
    if(empty($uid) || (empty($course_id) && empty($course)) || empty($module)){
        return null;
    }
    $course = (is_null($course))? $course_id:$course;
    $q = "SELECT l.course_id, l.module_id, count(l.id) notcount, ua.last_visit
            FROM log l
            JOIN course_user cu ON l.course_id=cu.course_id
            LEFT JOIN
            (SELECT cu.course_id, ad.module_id, MAX(ad.last_update) last_visit FROM course_user cu
            JOIN actions_daily ad ON cu.user_id=ad.user_id AND cu.course_id=ad.course_id
            WHERE cu.user_id = ?d AND cu.course_id = ?d AND ad.module_id = ?d
            GROUP BY ad.course_id, ad.module_id) ua
            ON l.course_id=ua.course_id AND l.module_id=ua.module_id
            WHERE cu.user_id = ?d AND l.course_id = ?d AND l.action_type=1 AND l.user_id<>?d AND l.module_id = ?d AND (l.ts>ua.last_visit OR ua.last_visit is null)
            GROUP BY l.course_id, l.module_id";

    return Database::get()->queryArray($q,$uid,$course,$module, $uid,$course,$uid,$module);
}

function get_module_notifications($module = NULL){
    global $uid;
    if(empty($uid) || empty($module)){
        return null;
    }
    $q = "SELECT l.course_id, l.module_id, count(l.id) notcount, ua.last_visit
            FROM log l
            JOIN course_user cu ON l.course_id=cu.course_id
            LEFT JOIN
            (SELECT cu.course_id, ad.module_id, MAX(ad.last_update) last_visit FROM course_user cu
            JOIN actions_daily ad ON cu.user_id=ad.user_id AND cu.course_id=ad.course_id
            WHERE cu.user_id = ?d AND ad.module_id = ?d
            GROUP BY ad.course_id, ad.module_id) ua
            ON l.course_id=ua.course_id AND l.module_id=ua.module_id
            WHERE cu.user_id = ?d AND l.action_type=1 AND l.user_id<>?d AND l.module_id = ?d AND (l.ts>ua.last_visit OR ua.last_visit is null)
            GROUP BY l.course_id, l.module_id";

    return Database::get()->queryArray($q,$uid,$module, $uid,$uid,$module);
}
