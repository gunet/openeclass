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
    global $uid;
    if(empty($uid)){
        return null;
    }
    $q = "SELECT l.course_id, l.module_id, count(l.id) notcount, ua.last_visit
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

function get_course_notifications($course = NULL) {

    global $uid, $modules;

    if (is_null($course)) {
        $cid = $GLOBALS['course_id'];
    } else {
        $cid = $course;
    }

    $notifications = [];
    if (is_null($uid)) {
        return null;
    }
    $last_login = last_login($uid); // user last login
    $gids = user_group_info($uid, $cid); // course user groups (if any)
    if (!empty($gids)) {
        $gids_sql_ready = implode(',',array_keys($gids));
    } else {
        $gids_sql_ready = "''";
    }

    foreach ($modules as $module_id => $module_data) {
        if (!visible_module($module_id, $cid)) {
            continue;
        } else {
            switch ($module_id) {
                case MODULE_ID_DOCS:
                    $cnt_docs = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM document 
                                                            WHERE document.course_id = ?d                                                              
                                                            AND document.date_modified > ?t 
                                                            AND document.visible = 1", $cid, $last_login)->cnt;
                    if ($cnt_docs > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_docs, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_ANNOUNCE:
                    $cnt_announce = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM announcement 
                                                            WHERE announcement.course_id = ?d 
                                                            AND announcement.visible = 1
                                                            AND (announcement.start_display <= " . DBHelper::timeAfter() . " OR announcement.start_display IS NULL)
                                                            AND (announcement.stop_display >= " . DBHelper::timeAfter() . " OR announcement.stop_display IS NULL)
                                                            AND announcement.`date` >= ?t", $cid, $last_login)->cnt;
                    if ($cnt_announce > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_announce, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_ASSIGN:
                    $cnt_assign = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM assignment 
                                                                WHERE course_id = ?d
                                                                AND submission_date > ?t
                                                                AND active = '1' AND
                                                                (assign_to_specific = 0 OR id IN
                                                                    (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                                                        UNION
                                                                    SELECT assignment_id FROM assignment_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))
                                                            )", $cid, $last_login, $uid)->cnt;
                    if ($cnt_assign > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_assign, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_EXERCISE:
                    $cnt_exercise = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM exercise
                                                            WHERE course_id = ?d AND active = 1 AND start_date > ?t AND
                                                              (assign_to_specific = '0' OR
                                                               (assign_to_specific != '0' AND id IN (
                                                                  SELECT exercise_id FROM exercise_to_specific WHERE user_id = ?d
                                                                    UNION
                                                                   SELECT exercise_id FROM exercise_to_specific WHERE group_id IN ('$gids_sql_ready'))))",
                                                            $cid, $last_login, $uid)->cnt;
                    if ($cnt_exercise > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_exercise, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_WIKI:
                    $cnt_wiki = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM wiki_pages
                                                                JOIN wiki_properties ON wiki_pages.wiki_id = wiki_properties.id 
                                                            WHERE course_id = ?d 
                                                            AND wiki_pages.ctime > ?t 
                                                            AND wiki_properties.visible = 1", $cid, $last_login)->cnt;
                    if ($cnt_wiki > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_wiki, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_VIDEO:
                    $cnt_video = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM video 
                                                    WHERE course_id = ?d 
                                                    AND date > ?t AND 
                                                video.visible = 1", $cid, $last_login)->cnt;
                    if ($cnt_video > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_video, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_QUESTIONNAIRE:
                    $cnt_questionnaire = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM poll 
                                                WHERE course_id = ?d AND
                                                (assign_to_specific = '0' OR assign_to_specific != '0' AND pid IN
                                                    (SELECT poll_id FROM poll_to_specific WHERE user_id = ?d 
                                                        UNION 
                                                    SELECT poll_id FROM poll_to_specific WHERE group_id IN ($gids_sql_ready))
                                                ) 
                                                AND start_date > ?t",
                                            $cid, $uid, $last_login)->cnt;
                    if ($cnt_questionnaire > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_questionnaire, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_ATTENDANCE:
                    $cnt_attendance = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM attendance 
                                                JOIN attendance_users ON attendance.id = attendance_users.attendance_id 
                                                 AND attendance_users.uid = ?d 
                                                 AND course_id = ?d 
                                                 AND active = 1 
                                                 AND start_date > ?t 
                                            AND end_date > " . DBHelper::timeAfter(), $uid, $cid, $last_login)->cnt;
                    if ($cnt_attendance > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_attendance, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_GRADEBOOK:
                    $cnt_gradebook = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM gradebook 
                                                                JOIN gradebook_users ON gradebook.id = gradebook_users.gradebook_id 
                                                                AND gradebook_users.uid = ?d 
                                                                AND course_id = ?d 
                                                                AND active = 1 
                                                                AND start_date > ?t 
                                                            AND end_date > ". DBHelper::timeAfter(), $uid, $cid, $last_login)->cnt;
                    if ($cnt_gradebook > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_gradebook, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_FORUM:
                    $cnt_forum_post = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM forum_post
                                                            JOIN forum_topic ON forum_post.topic_id = forum_topic.id 
                                                            JOIN forum ON forum.id = forum_topic.forum_id 
                                                            WHERE forum.course_id = ?d
                                                            AND forum_post.post_time > ?t", $cid, $last_login)->cnt;
                    if ($cnt_forum_post > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_forum_post, 'last_visit' => $last_login ];
                    }
                    break;
                case MODULE_ID_WALL:
                    $cnt_wall_post = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM wall_post
                                                                    WHERE UNIX_TIMESTAMP(?t) > timestamp
                                                                    AND course_id = ?d", $last_login, $cid)->cnt;
                    if ($cnt_wall_post > 0) {
                        $notifications[] = (object) [ 'course_id' => $cid, 'module_id' => $module_id, 'notcount' => $cnt_wall_post, 'last_visit' => $last_login ];
                    }
                    break;
            }
        }
    }

    // course units notifications
    $all_units = Database::get()->queryArray("SELECT id
                                            FROM course_units
                                            WHERE course_id = ?d
                                            AND visible = 1 ", $cid);
    $visible_user_units = findUserVisibleUnits($uid, $all_units, $cid);
    foreach ($visible_user_units as $unit_id) {
        $cnt_unit_resources = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM unit_resources
                                                WHERE unit_id = ?d
                                                AND date > ?t
                                                AND visible = 1", $unit_id->id, $last_login)->cnt;
        if ($cnt_unit_resources > 0) {
            $notifications[] = (object) [ 'course_id' => $cid, 'unit_id' => $unit_id->id, 'notcount' => $cnt_unit_resources, 'last_visit' => $last_login ];
            break;
        }
    }

    return $notifications;
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
