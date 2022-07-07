<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
 * ========================================================================

  ============================================================================
  @Description: Main script for the work tool
  ============================================================================
 */

$require_current_course = TRUE;
$require_login = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/chat/functions.php';

$actionBar .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label'
    )
));

$tool_content = $actionBar . "<div class='alert alert-danger'>" . $langColmoocRegisterStudentFailed . "</div>";

if (isset($_GET['activity_id']) && isset($_GET['session_status']) && isset($_GET['partner_colstudent_id'])) {

    $colmoocUserSession = Database::get()->querySingle("SELECT * FROM colmooc_user_session WHERE user_id = ?d AND activity_id = ?d", $uid, $_GET['activity_id']);
    if ($colmoocUserSession && $colmoocUserSession->session_id && $colmoocUserSession->session_token) {
        Database::get()->query("UPDATE colmooc_user_session SET session_status = ?d, session_status_updated = ?t WHERE user_id = ?d AND activity_id = ?d", $_GET['session_status'], gmdate('Y-m-d H:i:s'), $uid, $_GET['activity_id']);
        $partnerUserSession = Database::get()->querySingle("SELECT cus.* FROM colmooc_user_session cus JOIN colmooc_user cu ON (cu.user_id = cus.user_id) WHERE cu.colmooc_id = ?d AND cus.activity_id = ?d", $_GET['partner_colstudent_id'], $_GET['activity_id']);
        if ($partnerUserSession && $partnerUserSession->session_id && $partnerUserSession->session_token && $partnerUserSession->user_id) {
            Database::get()->query("UPDATE colmooc_user_session SET session_status = ?d, session_status_updated = ?t WHERE user_id = ?d AND activity_id = ?d", $_GET['session_status'], gmdate('Y-m-d H:i:s'), $partnerUserSession->user_id, $_GET['activity_id']);
            // pair log
            Database::get()->query("INSERT INTO colmooc_pair_log (activity_id, moderator_id, partner_id, session_status, created) VALUES (?d, ?d, ?d, ?d, ?t)", $_GET['activity_id'], $uid, $partnerUserSession->user_id, $_GET['session_status'], gmdate('Y-m-d H:i:s'));
            $tool_content = $actionBar . "<div class='alert alert-info'>" . $langColmoocRegisterStudentSuccess . "</div>";
        }
    }
} else if (isset($_GET['activity_id']) && isset($_GET['session_status']) && !isset($_GET['partner_colstudent_id'])) {

    $colmoocUserSession = Database::get()->querySingle("SELECT * FROM colmooc_user_session WHERE user_id = ?d AND activity_id = ?d", $uid, $_GET['activity_id']);
    if ($colmoocUserSession && $colmoocUserSession->session_id && $colmoocUserSession->session_token) {
        Database::get()->query("UPDATE colmooc_user_session SET session_status = ?d, session_status_updated = ?t WHERE user_id = ?d AND activity_id = ?d", $_GET['session_status'], gmdate('Y-m-d H:i:s'), $uid, $_GET['activity_id']);
        $tool_content = $actionBar . "<div class='alert alert-info'>" . $langColmoocRegisterStudentSuccess . "</div>";
        if (intval($_GET['session_status']) == 0) {
            // αποτυχημένη εύρεση συνεργάτη, προσπάθησε ξανά
            $tool_content = $actionBar . "<div class='alert alert-danger'>" . $langColmoocRegisterStudentNoPartner . "</div>";
        }
    }
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);