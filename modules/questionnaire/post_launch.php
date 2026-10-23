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
$action->record(MODULE_ID_QUESTIONNAIRE);
/* * *********************************** */

// guest user not allowed
if (check_guest()) {
    die();
}

// only course members (or editors / admins) may launch
if (!($is_admin or $is_editor or user_is_registered_to_course($uid, $course_id))) {
    die();
}

$resource_link_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($is_admin or $is_editor) {
    $thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $resource_link_id);
} else {
    // Check poll visibility rules
    $user_group_info = user_group_info($uid, $course_id);
    $gids_sql_ready = !empty($user_group_info) ? implode(',', array_keys($user_group_info)) : "''";
    $thePoll = Database::get()->querySingle("SELECT * FROM poll
                                                WHERE course_id = ?d
                                                  AND pid = ?d
                                                  AND active = 1
                                                  AND (assign_to_specific = 0 OR
                                                       (assign_to_specific <> 0 AND pid IN
                                                         (SELECT poll_id FROM poll_to_specific WHERE user_id = ?d
                                                          UNION
                                                          SELECT poll_id FROM poll_to_specific WHERE group_id != 0 AND group_id IN ($gids_sql_ready))))",
        $course_id, $resource_link_id, $uid);

    if ($thePoll) {
        $now = new DateTime();
        if (($thePoll->start_date and $now < new DateTime($thePoll->start_date)) or
            ($thePoll->end_date and $now >= new DateTime($thePoll->end_date))) {
            die();
        }

        if (!$thePoll->multiple_submissions) {
            $has_participated = Database::get()->querySingle("SELECT COUNT(*) AS count
                FROM poll_user_record WHERE uid = ?d AND pid = ?d", $uid, $thePoll->pid)->count;
            if ($has_participated > 0) {
                die();
            }
        }
    }
}

if (!$thePoll or $thePoll->type != POLL_LIMESURVEY) {
    die();
}

$lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $thePoll->lti_template);
if (!$lti) {
    die();
}

$_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$thePoll->pid.'_COURSE_ID'] = $course_id;
$_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$thePoll->pid.'_COURSE_CODE'] = $course_code;

echo create_join_button(
    $lti->lti_provider_url,
    $lti->lti_provider_key,
    $lti->lti_provider_secret,
    $thePoll->pid,
    RESOURCE_LINK_TYPE_POLL,
    $thePoll->name,
    $thePoll->description,
    $thePoll->launchcontainer,
    ''
);

echo '<script type="text/javascript">
//<![CDATA[
document.ltiLaunchForm.submit();
//]]>
</script>';
