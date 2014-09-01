<?php

/* ========================================================================
 * Open eClass 3.0
* E-learning and Course Management System
* ========================================================================
* Copyright 2003-2013  Greek Universities Network - GUnet
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

$require_login = TRUE;
$guest_allowed = FALSE;

include '../../include/baseTheme.php';

if (isset($_POST['course'])) {
    if ($_POST['course'] != -1) {
        $jsonarr = array();
        $cid = course_code_to_id($_POST['course']);
        $student_to_student_allow = get_config('dropbox_allow_student_to_student');
        
        $sql = "SELECT COUNT(*) as c FROM course_user WHERE course_id = ?d AND user_id = ?d AND status = ?d";
        $res = Database::get()->querySingle($sql, $cid, $uid, USER_TEACHER);
        if ($res->c != 0) {
            $is_editor = true;
        } else {
            $is_editor = false;
        }
        
        if ($is_editor || $student_to_student_allow == 1) {
            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
                    FROM user u, course_user cu
        		    WHERE cu.course_id = ?d
                    AND cu.user_id = u.id
                    AND cu.status != ?d
                    AND u.id != ?d
                    ORDER BY UPPER(u.surname), UPPER(u.givenname)";
            $res = Database::get()->queryArray($sql, $cid, USER_GUEST, $uid);
            
            if ($is_editor) {
                $sql_g = "SELECT id, name FROM `group` WHERE course_id = ?d ORDER BY id DESC";
                $result_g = Database::get()->queryArray($sql_g, $cid);
            } else {//allow students to send messages only to groups they are members of
                $sql_g = "SELECT `g`.id, `g`.name FROM `group` as `g`, `group_members` as `gm`
                              WHERE `g`.id = `gm`.group_id AND `g`.course_id = ?d AND `gm`.user_id = ?d";
                $result_g = Database::get()->queryArray($sql_g, $cid, $uid);
            }
            
            foreach ($result_g as $res_g)
            {
                $jsonarr['_'.$res_g->id] = q($res_g->name);
            }
        } else {
            //if user is student an student-student messages not allowed for course messages show teachers
            $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
                        FROM user u, course_user cu
        			    WHERE cu.course_id = ?d
                        AND cu.user_id = u.id
                        AND cu.status = ?d
                        AND u.id != ?d
                        ORDER BY UPPER(u.surname), UPPER(u.givenname)";
            
            $res = Database::get()->queryArray($sql, $cid, USER_TEACHER, $uid);
        }
        
        foreach ($res as $r) {
            $jsonarr[$r->user_id] = q($r->name);
        }
        header('Content-Type: application/json');
        echo json_encode($jsonarr);
    } else {//selected the empty option
        echo json_encode(array());
    }
} elseif (isset($_GET['autocomplete']) && $_GET['autocomplete'] == 1) {
    $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
            FROM user u, course_user cu
		    WHERE cu.course_id IN (SELECT course_id FROM course_user WHERE user_id = ?d)
            AND cu.user_id = u.id
            AND cu.status != ?d
            AND u.id != ?d
            AND CONCAT(u.surname,' ', u.givenname) LIKE ?s
            ORDER BY UPPER(u.surname), UPPER(u.givenname)";
    $res = Database::get()->queryArray($sql, $uid, USER_GUEST, $uid, "%".$_GET['term']."%");
    
    $jsonarr = array();
    $i = 0;
    
    foreach ($res as $r) {
        $jsonarr[$i]['value'] = $r->user_id;
        $jsonarr[$i]['label'] = $r->name;
        $i++;
    }
    header('Content-Type: application/json');
    echo json_encode($jsonarr);
}
