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


define('RESPONSE_OK', 'OK');
define('RESPONSE_FAILED', 'FAILED');
define('RESPONSE_EXPIRED', 'EXPIRED');


if (isset($require_mlogin) && $require_mlogin) {
    
    if (!isset($_REQUEST['token'])) {
        echo RESPONSE_FAILED;
        exit();
    } else {
        session_id($_REQUEST['token']);
        session_start();
        $_SESSION['mobile'] = true;
    }

    if (!isset($_SESSION['uid'])) {
        echo RESPONSE_EXPIRED;
        exit();
    }
}

require_once ('init.php');
$nohierarchy = (mysql_num_rows(db_query("SHOW TABLES LIKE 'faculte'")) == 1) ? true : false;

if (isset($require_mcourse) && $require_mcourse) {
    if (!isset($_REQUEST['course'])) {
        echo RESPONSE_FAILED;
        exit();
    } else {
        $coursesql = "SELECT cours_id, cours.code, 
                             fake_code, intitule, hierarchy.name AS faculte,
                             titulaires, languageCourse, departmentUrlName, departmentUrl, visible
                        FROM cours, course_department, hierarchy
                       WHERE cours.cours_id = course_department.course 
                         AND hierarchy.id = course_department.department 
                         AND cours.code=" . autoquote($_REQUEST['course']);
        if ($nohierarchy)
            $coursesql = "SELECT cours_id, cours.code, 
                                 fake_code, intitule, faculte.name AS faculte,
                                 titulaires, languageCourse, departmentUrlName, departmentUrl, visible
                            FROM cours, faculte
                           WHERE cours.faculteid = faculte.id 
                             AND cours.code=" . autoquote($_REQUEST['course']);
        $result = db_query($coursesql);
        
        if (!$result or mysql_num_rows($result) == 0) {
            echo RESPONSE_FAILED;
            exit();
        }
        
        while ($theCourse = mysql_fetch_array($result))
            $cours_id = $theCourse['cours_id'];
        
        $currentCourse = $currentCourseID = $_REQUEST['course'];
        
        if ($is_admin) {
            $is_course_admin = true;
            if (isset($currentCourse))
                $_SESSION['status'][$currentCourse] = 1;
        } else
            $is_course_admin = false;

        $is_editor = false;
        if (isset($_SESSION['status'])) {
            $status = $_SESSION['status'];
            if (isset($currentCourse)) {
                if (check_editor())
                    $is_editor = true;
                if (@$status[$currentCourse] == 1) {
                    $is_course_admin = true;
                    $is_editor = true;
                }            	
            }
        } else
            unset($status);

    }
}

if (isset($_REQUEST['profile']))
    $_SESSION['profile'] = $_REQUEST['profile'];

