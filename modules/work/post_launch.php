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
require_once '../../include/baseTheme.php';
require_once 'modules/lti_consumer/lti-functions.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_ASSIGN);
/* * *********************************** */

// guest user not allowed
if (check_guest()) {
    die();
}

// only course members (or editors / admins) may launch
if (!($is_admin or $is_editor or user_is_registered_to_course($uid, $course_id))) {
    die();
}

$resource_link_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($is_editor) {
    $assignment = Database::get()->querySingle("SELECT * FROM assignment
                                                    WHERE course_id = ?d AND id = ?d",
        $course_id, $resource_link_id);
} else {
    // same visibility rules as display_student_assignment()
    $user_group_info = user_group_info($uid, $course_id);
    $gids_sql_ready = !empty($user_group_info) ? implode(',', array_keys($user_group_info)) : "''";
    $assignment = Database::get()->querySingle("SELECT * FROM assignment
                                                    WHERE course_id = ?d
                                                      AND id = ?d
                                                      AND active = 1
                                                      AND (assign_to_specific = 0 OR
                                                           id IN
                                                             (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                                              UNION
                                                              SELECT assignment_id FROM assignment_to_specific
                                                                 WHERE group_id != 0 AND group_id IN ($gids_sql_ready)))",
        $course_id, $resource_link_id, $uid);
    // password locked assignments must have been unlocked first
    if ($assignment and $assignment->password_lock !== '' and empty($_SESSION['has_unlocked'][$assignment->id])) {
        die();
    }
}
if (!$assignment or $assignment->assignment_type != ASSIGNMENT_TYPE_TURNITIN) {
    die();
}
$lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $assignment->lti_template);
if (!$lti) {
    die();
}

echo create_join_button_for_assignment($lti, $assignment);
echo create_js_for_immediatelaunch($lti);
