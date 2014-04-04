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
        $cid = course_code_to_id($_POST['course']);
        $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
                FROM user u, course_user cu
    		    WHERE cu.course_id = ?d
                AND cu.user_id = u.id
                AND cu.status != ?d
                AND u.id != ?d
                ORDER BY UPPER(u.surname), UPPER(u.givenname)";
        $res = Database::get()->queryArray($sql, $cid, USER_GUEST, $uid);
        $jsonarr = array();
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
