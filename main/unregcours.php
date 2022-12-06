<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */


$require_login = TRUE;
include '../include/baseTheme.php';
require_once 'include/log.class.php';

$pageName = $langUnregCourse;

if (isset($_GET['cid'])) {
    $cid = q($_GET['cid']);
    $_SESSION['cid_tmp'] = $cid;
}
if (!isset($_GET['cid'])) {
    $cid = $_SESSION['cid_tmp'];
}

if (!isset($_POST['doit'])) {
    $tool_content .= "
    <div class='col-12'>
      <div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]?u=$_SESSION[uid]&amp;cid=$cid'>
          <div class='form-group'>
            <div class='col-sm-12'>
              $langConfirmUnregCours: <b>" . q(course_id_to_title($cid)) . "</b>
            </div>
          </div>
          <div class='form-group mt-4'>
            <div class='d-inline-flex align-items-center'>
              <label class='control-label-notes'>$langYes:</label>
              <button class='btn btn-sm btn-danger ms-2' name='doit'> $langUnCourse</button>
            </div>
          </div>
          <div class='form-group mt-4'>
            <div class='d-inline-flex align-items-center'>
              <label class='control-label-notes'>$langNo:</label>
              <a href='{$urlAppend}main/portfolio.php' class='btn btn-outline-secondary cancelAdminBtn ms-2'> $langCancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>";
} else {
    if (isset($_SESSION['uid']) and $_GET['u'] == $_SESSION['uid']) {
        $q = Database::get()->query("DELETE from course_user
                                    WHERE course_id = ?d
                                    AND user_id = ?d", $cid, $_GET['u']);
        if ($q->affectedRows > 0) {
            Database::get()->query("DELETE FROM group_members
                                WHERE user_id = ?d AND
                                  group_id IN (SELECT id FROM `group` WHERE course_id = ?d)", $_GET['u'], $cid);
            Database::get()->query("DELETE FROM user_badge_criterion WHERE user = ?d AND 
                                     badge_criterion IN
                                            (SELECT id FROM badge_criterion WHERE badge IN
                                            (SELECT id FROM badge WHERE course_id = ?d))", $_GET['u'], $cid);
            Database::get()->query("DELETE FROM user_badge WHERE user = ?d AND                 
                                      badge IN (SELECT id FROM badge WHERE course_id = ?d)", $_GET['u'], $cid);
            Database::get()->query("DELETE FROM user_certificate_criterion WHERE user = ?d AND 
                                    certificate_criterion IN
                                    (SELECT id FROM certificate_criterion WHERE certificate IN
                                        (SELECT id FROM certificate WHERE course_id = ?d))", $_GET['u'], $cid);
            Database::get()->query("DELETE FROM user_certificate WHERE user = ?d AND 
                                 certificate IN (SELECT id FROM certificate WHERE course_id = ?d)", $_GET['u'], $cid);
            Log::record($cid, MODULE_ID_USERS, LOG_DELETE, array('uid' => $_GET['u'],
                                                                 'right' => 0));
            $code = course_id_to_code($cid);
            // clear session access to lesson
            unset($_SESSION['dbname']);
            unset($_SESSION['cid_tmp']);
            unset($_SESSION['courses'][$code]);
            //Session::Messages($langCoursDelSuccess, 'alert-success');
            Session::flash('message',$langCoursDelSuccess);
            Session::flash('alert-class', 'alert-success');
            if(isset($_POST['fromMyCoursesPage']) and $_POST['fromMyCoursesPage'] == 1){
                redirect_to_home_page('main/my_courses.php');
            }else{
                redirect_to_home_page('main/portfolio.php');
            }
          
        } else {
            $tool_content .= "<div class='col-12'><div class='alert alert-danger'>$langCoursError</div></div>";
        }
    }
    $tool_content .= "<br><br><div class='text-end'><a href='../index.php' class=mainpage>$langBack</a></div>";
}

if (isset($_SESSION['uid'])) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}
