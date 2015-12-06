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
/**
 * @file quotacours.php
 * @brief Edit course quota
 */
  
$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';

if (!isset($_GET['c'])) {
    die();
}

require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

// validate course Id
$cId = course_code_to_id(getDirectReference($_GET['c']));
validateCourseNodes($cId, isDepartmentAdmin());

$toolName = $langQuota;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourse);
$navigation[] = array('url' => 'editcours.php?c=' . q(getDirectReference($_GET['c'])), 'name' => $langCourseEdit);

if (isset($_GET['c'])) {
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "editcours.php?c=" . q(getDirectReference($_GET['c'])),
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));
    } else {
        $tool_content .= action_bar(array(
            array('title' => $langBackAdmin,
                  'url' => "index.php",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));           
    }

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
			WHERE code = ?s", $dq, $vq, $gq, $drq, getDirectReference($_GET['c']));
    // Some changes occured
    if ($sql->affectedRows > 0) {
        $tool_content .= "<div class='alert alert-info'>$langQuotaSuccess</div>";
    }
    // Nothing updated
    else {
        $tool_content .= "<div class='alert alert-warning'>$langQuotaFail</div>";
    }
}
// Display edit form for course quota
else {        
    $q = Database::get()->querySingle("SELECT code, title, doc_quota, video_quota, group_quota, dropbox_quota FROM course WHERE code = ?s", getDirectReference($_GET['c']));
    $quota_info .= $langTheCourse . " <b>" . q($q->title) . "</b> " . $langMaxQuota;
    $dq = $q->doc_quota / MB;
    $vq = $q->video_quota / MB;
    $gq = $q->group_quota / MB;
    $drq = $q->dropbox_quota / MB;

    $tool_content .= "<div class='form-wrapper'>
            <form role='form' class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?c=" . getIndirectReference(getDirectReference($_GET['c'])) . "' method='post'>
            <fieldset>                    
                <div class='alert alert-info'>$quota_info</div>
                <div class='form-group'>
                    <label class='col-sm-4 control-label'>$langLegend $langDoc:</label>
                        <div class='col-sm-6'><input type='text' name='dq' value='$dq' size='4' maxlength='4'> Mb.</div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-4 control-label'>$langLegend $langVideo:</label>
                        <div class='col-sm-6'><input type='text' name='vq' value='$vq' size='4' maxlength='4'> Mb.</div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-4 control-label'>$langLegend <b>$langGroups</b>:</label>
                        <div class='col-sm-6'><input type='text' name='gq' value='$gq' size='4' maxlength='4'> Mb.</div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-4 control-label'>$langLegend <b>$langDropBox</b>:</label>
                        <div class='col-sm-6'><input type='text' name='drq' value='$drq' size='4' maxlength='4'> Mb.</div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-4'>
                        <input class='btn btn-primary' type='submit' name='submit' value='$langModify'>
                    </div>
                </div>
            </fieldset>
            ". generate_csrf_token_form_field() ."
            </form>
        </div>";
}
draw($tool_content, 3);
