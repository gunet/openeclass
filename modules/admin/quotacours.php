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
/**
 * @file quotacours.php
 * @brief Edit course quota
 */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

if (!isset($_GET['c'])) {
    redirect_to_home_page();
}

$tree = new Hierarchy();
$course = new Course();
$user = new User();

// validate course Id
$cId = course_code_to_id($_GET['c']);
validateCourseNodes($cId, isDepartmentAdmin());

$data['course'] = Database::get()->querySingle("SELECT code, title, doc_quota, video_quota, group_quota, dropbox_quota FROM course WHERE code = ?s", $_GET['c']);

// Initialize some variables
$quota_info = '';
define('MB', 1048576);

// Update course quota
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $dq = $_POST['dq'] * MB;
    $vq = $_POST['vq'] * MB;
    $gq = $_POST['gq'] * MB;
    $drq = $_POST['drq'] * MB;
    // Update query
    $sql = Database::get()->query("UPDATE course SET doc_quota=?f, video_quota=?f, group_quota=?f, dropbox_quota=?f
            WHERE code = ?s", $dq, $vq, $gq, $drq, $_GET['c']);
    // Some changes occured
    if ($sql->affectedRows > 0) {
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
    }
    // Nothing updated
    else {
        Session::flash('message', $langQuotaFail);
        Session::flash('alert-class', 'alert-danger');
    }
    redirect_to_home_page('modules/admin/quotacours.php?c=' . $_GET['c']);
}
// Display edit form for course quota
$toolName = $langQuota;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourse);
$navigation[] = array('url' => 'editcours.php?c=' . q($_GET['c']), 'name' => $langCourseEdit);

if (isset($_GET['c'])) {
        $data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                  'url' => "editcours.php?c=$_GET[c]",
                  'icon' => 'fa-reply',
                  'level' => 'primary')));
} else {
        $data['action_bar'] = action_bar(array(
            array('title' => $langBackAdmin,
                  'url' => "index.php",
                  'icon' => 'fa-reply',
                  'level' => 'primary')));
}


$data['dq'] = round($data['course']->doc_quota / MB);
$data['vq'] = round($data['course']->video_quota / MB);
$data['gq'] = round($data['course']->group_quota / MB);
$data['drq'] = round($data['course']->dropbox_quota / MB);

view('admin.courses.quotacours', $data);
