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

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

$toolName = $langUnregUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

// get the incoming values and initialize them
$u = isset($_GET['u']) ? intval($_GET['u']) : false;
$c = isset($_GET['c']) ? intval($_GET['c']) : false;
$doit = isset($_GET['doit']);

if (isset($doti)) {
    $tool_content .= action_bar(array(    
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
} else {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "edituser.php?u=$u",
              'icon' => 'fa-reply',
              'level' => 'primary-label'),
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
}

if (isDepartmentAdmin()) {
    validateUserNodes(intval($u), true);
}

$u_account = $u ? q(uid_to_name($u, 'username')) : '';
$u_realname = $u ? q(uid_to_name($u)) : '';
$userdata = user_get_data($u);
$u_status = $userdata->status;

if (!$doit) {
    if ($u_account && $c) {
        $tool_content .= "<div class='alert alert-warning'>$langConfirmDeleteQuestion1 <em>$u_realname ($u_account)</em>
    	$langConfirmDeleteQuestion2 <em>" . q(course_id_to_title($c)) . "</em>
        </div>
        <div class='col-sm-offset-5 btn btn-primary'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;c=$c&amp;doit=yes'>$langDelete</a></div>";
    } else {
        $tool_content .= "<div class='alert alert-danger'>$langErrorUnreguser</div>";
    }
   
} else {
    if ($c and $u) {
        $q = Database::get()->query("DELETE from course_user WHERE user_id = ?d AND course_id = ?d", $u, $c);
        if ($q->affectedRows>0) {
            Database::get()->query("DELETE FROM group_members
                            WHERE user_id = ?d AND
                            group_id IN (SELECT id FROM `group` WHERE course_id = ?d)", $u, $c);
            $tool_content .= "<div class='alert alert-info'>$langWithUsername \"$u_account\" $langWasCourseDeleted <em>" . q(course_id_to_title($c)) . "</em></div>";
            $m = 1;
        }
    } else {
        $tool_content .= "<div class='alert alert-danger'>$langErrorDelete</div>";
    }
    $tool_content .= "<br>&nbsp;";
    if ((isset($m)) && (!empty($m))) {
        $tool_content .= "<br><a href='edituser.php?u=$u'>$langEditUser $u_account</a>&nbsp;&nbsp;&nbsp;";
    }    
}

draw($tool_content, 3);
