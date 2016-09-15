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


$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

$pageName = $langAddDescription;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langGroups);
$group_id = isset($_REQUEST['group_id']) ? intval($_REQUEST['group_id']) : '';

if (isset($_GET['delete'])) {
    $sql = Database::get()->query("UPDATE group_members SET description = ''
		WHERE group_id = ?d AND user_id = ?d", $group_id, $uid);
    if ($sql->affectedRows > 0) {
        Session::Messages($langBlockDeleted, 'alert-success');
    }
    redirect_to_home_page("modules/group/index.php?course=$course_code");
} else if (isset($_POST['submit'])) {
    $sql = Database::get()->query("UPDATE group_members SET description = ?s
			WHERE group_id = ?d AND user_id = ?d", $_POST['group_desc'], $group_id, $uid);
    if ($sql->affectedRows > 0) {
        Session::Messages($langRegDone, 'alert-success');
    } else {
        Session::Messages($langNoChanges);
    }
    redirect_to_home_page("modules/group/index.php?course=$course_code");
} else { // display form
    $description = Database::get()->querySingle("SELECT description FROM group_members
			WHERE group_id = ?d AND user_id = ?d", $group_id, $uid)->description;
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'icon' => 'fa-reply',
            'url' => "index.php?course=$course_code",
            'level' => 'primary-label'
        )
    ))."
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <input type='hidden' name='group_id' value='$group_id'>
            <div class='form-group'>
                <div class='col-xs-12'>$langGroupDescInfo</div>
            </div>
            <div class='form-group'>
              <label for='group_desc' class='col-sm-2 control-label'>$langInfoAbout:</label>
              <div class='col-sm-10'>
                <textarea class='form-control' name='group_desc' id='group_desc' rows='10'>" . @$description . "</textarea>
              </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='submit' value='" . q($langAddModify) . "'>
                    <a class='btn btn-default' href='index.php?course=$course_code'>$langCancel</a>
                </div>
            </div>            
        </form>
    </div>";
}
draw($tool_content, 2);
