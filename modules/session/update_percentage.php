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
 * @file consulting_completion.php
 * @brief Display a detailed table about consulting completion for each user
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/progress/process_functions.php';
require_once 'functions.php';

check_activation_of_collaboration();

$pageName = $langUpdatePercentage;

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

if ($is_simple_user) {
    Session::flash('message', $langForbidden);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/session/index.php?course=$course_code");
}

$head_content .= "
    <script>
        $(function() {
            $('.link-Update-Percentage').on('click', function () {
                $('.show-calculation-message').removeClass('d-none').addClass('d-block');
            });
        });
    </script>
";

if (isset($_GET['update_percentage'])) {

    $sql_consultant = "";
    if ($is_consultant && !$is_coordinator) {
        $sql_consultant = "AND s.creator = ?d";
        $query_vars = [$course_id, $uid];
    } elseif ($is_coordinator) {
        $query_vars = [$course_id];
    }

    $res = Database::get()->queryArray("
                    SELECT
                        s.id AS session_id,
                        s.title,
                        s.type_remote,
                        b.id AS badge_id,
                        msu.participants
                    FROM mod_session s

                    LEFT JOIN badge b
                        ON b.session_id = s.id
                    AND b.course_id = s.course_id

                    LEFT JOIN mod_session_users msu
                        ON msu.session_id = s.id
                    AND msu.is_accepted = 1

                    WHERE s.course_id = ?d

                    $sql_consultant

                    ORDER BY s.start DESC
                ", $query_vars);

    if (count($res) > 0) {
        foreach ($res as $s) {
            // Calculate sessions copletion
            if (!$s->type_remote) {
                // This refers to session completion with completed meeting.
                check_session_completion_by_meeting_completed($s->session_id, $s->participants);
            } elseif($s->type_remote) {
                // This refers to session completion with completed tc.
                check_session_completion_by_tc_completed($s->session_id, $s->participants);
            }

            // This refers to session completion for other activities.
            // δημιουργεί delay το check_session_progress
            check_session_progress($s->session_id, $s->participants);  // check session completion - call to Game.php
            check_session_completion_without_activities($s->session_id);
            check_session_completion_with_expired_time($s->session_id);

            $per = 0;
            if($s->badge_id){
                $per = get_cert_percentage_completion_by_user('badge', $s->badge_id, $s->participants);
            }
            Database::get()->query("UPDATE mod_session_users SET `percentage` = ?d 
                                    WHERE session_id = ?d AND participants = ?d 
                                    AND is_accepted = ?d", $per, $s->session_id, $s->participants, 1);

        }

    }

    redirect_to_home_page("modules/session/update_percentage.php?course=$course_code&update=ok&status=complete");
}

if (isset($_GET['status']) && $_GET['status'] == 'complete') {
    if ($is_consultant && !$is_coordinator) {
       $html = "<a href='{$urlAppend}modules/session/consulting_completion_consultant.php?course={$course_code}&status=complete'>ΑΝΑΦΟΡΕΣ -- ΠΑΡΟΥΣΙΟΛΟΓΙΑ</a>";
    } elseif ($is_coordinator) {
        $html = "<a href='{$urlAppend}modules/session/consulting_completion_coordinator.php?course={$course_code}&status=complete'>ΑΝΑΦΟΡΕΣ -- ΠΑΡΟΥΣΙΟΛΟΓΙΑ</a>";
    }

    Session::flash('message', 'Η ενημέρωση ολοκληρώθηκε επιτυχώς. Εξάγετε τα αποτελέσματα πατώντας εδώ: ' . $html);
    Session::flash('alert-class', 'alert-success');
}

$data = array();

view('modules.session.update_percentage', $data);