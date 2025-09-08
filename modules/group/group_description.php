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


$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

$pageName = $langAddDescription;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langGroups);
$group_id = isset($_REQUEST['group_id']) ? intval($_REQUEST['group_id']) : '';

$userID = $uid;
if (isset($_GET['editByEditor']) && $_GET['u']) {
    $userID = intval($_GET['u']);
}

if (isset($_GET['delete'])) {
    $sql = Database::get()->query("UPDATE group_members SET description = ''
		WHERE group_id = ?d AND user_id = ?d", $group_id, $userID);
    if ($sql->affectedRows > 0) {
        Session::flash('message',$langBlockDeleted);
        Session::flash('alert-class', 'alert-success');
    }
    redirect_to_home_page("modules/group/index.php?course=$course_code");
} else if (isset($_POST['submit'])) {
    $sql = Database::get()->query("UPDATE group_members SET description = ?s
			WHERE group_id = ?d AND user_id = ?d", $_POST['group_desc'], $group_id, $_POST['u_id']);
    if ($sql->affectedRows > 0) {
        Session::flash('message',$langRegDone);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message',$langNoChanges);
        Session::flash('alert-class', 'alert-danger');
    }
    redirect_to_home_page("modules/group/index.php?course=$course_code");
} else { // display form
    $description = Database::get()->querySingle("SELECT description FROM group_members
			WHERE group_id = ?d AND user_id = ?d", $group_id, $userID)->description;
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'icon' => 'fa-reply',
            'url' => "index.php?course=$course_code",
            'level' => 'primary'
        )
    ))."
    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <input type='hidden' name='group_id' value='$group_id'>
            <input type='hidden' name='u_id' value='$userID'>
            <div class='form-group'>
                <div class='col-sm-12'>$langGroupDescInfo</div>
            </div>
            <div class='form-group mt-4'>
              <label for='group_desc' class='col-sm-6 control-label-notes'>$langDescription</label>
              <div class='col-sm-12'>
                <textarea class='form-control' name='group_desc' id='group_desc' rows='10'>" . @$description . "</textarea>
              </div>
            </div>
            <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                    <input class='btn submitAdminBtn' type='submit' name='submit' value='" . q($langAddModify) . "'>
                    <a class='btn cancelAdminBtn' href='index.php?course=$course_code'>$langCancel</a>
                </div>
            </div>            
        </form>
    </div></div><div class='d-none d-lg-block'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div>
</div>";
}
draw($tool_content, 2);
