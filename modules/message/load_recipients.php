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

$require_login = TRUE;
$guest_allowed = FALSE;

include '../../include/baseTheme.php';

if (isset($_POST['course'])) {
    if ($_POST['course'] != -1) {
        $jsonarr = array();
        $cid = course_code_to_id($_POST['course']);
        $student_to_student_allow = get_config('dropbox_allow_student_to_student');

        $sql = "SELECT COUNT(*) as c FROM course_user WHERE course_id = ?d AND user_id = ?d AND (status = ?d OR editor = ?d)";
        $res = Database::get()->querySingle($sql, $cid, $uid, USER_TEACHER, 1);
        if ($res->c != 0) {
            $is_editor = true;
        } else {
            $is_editor = false;
        }

        if ($is_editor || $student_to_student_allow == 1) {
            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                    FROM user u, course_user cu
        		    WHERE cu.course_id = ?d
                    AND cu.user_id = u.id
                    AND cu.status != ?d
                    AND u.id != ?d
                    ORDER BY name";
            $res = Database::get()->queryArray($sql, $cid, USER_GUEST, $uid);
            // find course groups (if any)
            $sql_g = "SELECT id, name FROM `group` WHERE course_id = ?d ORDER BY name";
            $result_g = Database::get()->queryArray($sql_g, $cid);

            foreach ($result_g as $res_g)
            {
                $jsonarr['_'.$res_g->id] = q($res_g->name);
            }
        } else {
            //if user is student an student-student messages not allowed for course messages show teachers
            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                        FROM user u, course_user cu
        			    WHERE cu.course_id = ?d
                            AND cu.user_id = u.id
                            AND (cu.status = ?d OR cu.editor = ?d)
                            AND u.id != ?d
                        ORDER BY name";

            $res = Database::get()->queryArray($sql, $cid, USER_TEACHER, 1, $uid);

            //check if user is group tutor
            $sql_g = "SELECT `g`.id, `g`.name FROM `group` as `g`, `group_members` as `gm`
                WHERE `g`.id = `gm`.group_id AND `g`.course_id = ?d AND `gm`.user_id = ?d AND `gm`.is_tutor = ?d";

            $result_g = Database::get()->queryArray($sql_g, $cid, $uid, 1);
            foreach ($result_g as $res_g)
            {
                $jsonarr['_'.$res_g->id] = q($res_g->name);
            }

            //find user's group and their tutors
            $tutors = array();
            $sql_g = "SELECT `group`.id FROM `group`, group_members
                          WHERE `group`.course_id = ?d
                          AND `group`.id = group_members.group_id
                          AND `group_members`.user_id = ?d";
            $result_g = Database::get()->queryArray($sql_g, $cid, $uid);
            foreach ($result_g as $res_g) {
                $sql_gt = "SELECT u.id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                               FROM user u, group_members g
                               WHERE g.group_id = ?d
                               AND g.is_tutor = ?d
                               AND g.user_id = u.id
                               AND u.id != ?d";
                $res_gt = Database::get()->queryArray($sql_gt, $res_g->id, 1, $uid);
                foreach ($res_gt as $t) {
                    $tutors[$t->id] = q($t->name)." (".q($t->username).")";
                }
            }
        }

        foreach ($res as $r) {
            if (isset($tutors) && !empty($tutors)) {
                if (isset($tutors[$r->user_id])) {
                    unset($tutors[$r->user_id]);
                }
            }
            $jsonarr[$r->user_id] = q($r->name)." (".q($r->username).")";
        }
        if (isset($tutors)) {
            foreach ($tutors as $key => $value) {
                $jsonarr[$key] = q($value);
            }
        }
        header('Content-Type: application/json');
        echo json_encode($jsonarr);
    } else {//selected the empty option
        echo json_encode(array());
    }
} elseif (isset($_GET['autocomplete']) && $_GET['autocomplete'] == 1) {
    if ($is_admin) {
        if (isset($_GET['q']) and $_GET['q'] !== '') {
            $res = Database::get()->queryArray("SELECT id user_id, CONCAT(surname,' ', givenname) AS name, username
                FROM user
                  WHERE id != ?d AND (surname LIKE ?s OR username LIKE ?s)
                ORDER BY name",
                $uid, "%".$_GET['q']."%", "%".$_GET['q']."%");
        } else {
            $res = Database::get()->queryArray("SELECT id user_id, CONCAT(surname,' ', givenname) AS name, username
                FROM user
                WHERE id != ?d
                ORDER BY name",
                $uid);
        }
    } else {
        if (isset($_GET['q']) and $_GET['q'] !== '') {
            $res = Database::get()->queryArray("SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                FROM user u, course_user cu
                  WHERE cu.course_id IN (SELECT course_id FROM course_user WHERE user_id = ?d)
                    AND cu.user_id = u.id
                    AND cu.status != ?d
                    AND u.id != ?d
                    AND (u.surname LIKE ?s OR u.username LIKE ?s)
                ORDER BY name", $uid, USER_GUEST, $uid, "%".$_GET['q']."%", "%".$_GET['q']."%");
        } else {
            $res = Database::get()->queryArray("SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name, u.username
                FROM user u, course_user cu
                WHERE cu.course_id IN (SELECT course_id FROM course_user WHERE user_id = ?d) 
                    AND cu.user_id = u.id 
                    AND cu.status != ?d 
                    AND u.id != ?d 
                ORDER BY name", $uid, USER_GUEST, $uid );
        }
    }

    $jsonarr["items"] = array();
    $i = 0;
    foreach ($res as $r) {
        $jsonarr["items"][$i] = new stdClass();
        $jsonarr["items"][$i]->id = $r->user_id;
        $jsonarr["items"][$i]->text = q($r->name)." (".q($r->username).")";

        $i++;
    }
    header('Content-Type: application/json');
    echo json_encode($jsonarr);
}

