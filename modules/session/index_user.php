<?php

$data['action_bar'] = action_bar([
    [
        'title' => $langPercentageCompletedConsulting,
        'url' => $urlAppend . "modules/session/completion.php?course=" . $course_code . "&showCompletedConsulting=true",
        'icon' => 'fa-solid fa-percent',
        'level' => 'primary-label',
        'button-class' => 'btn-success'
    ],
    [
        'title' => $langSummaryScheduledSessions,
        'url' => 'session_scheduled.php?course=' . $course_code,
        'icon' => 'fa-solid fa-list',
        'button-class' => 'btn-success',
        'level' => 'primary-label'
    ],
], false);

if (!isset($_GET['searchOn']) && !isset($_POST['submit_search'])) {
    $_SESSION['sql_session_type'] = '';
    $_SESSION['sql_session_type_args'] = [];
    $_SESSION['sql_type_remote'] = '';
    $_SESSION['sql_type_remote_args'] = [];
    //$data['searchUserId'] = 0;
    $data['remoteType'] = -1;
    $data['sessionType'] = 'other';
}

if (isset($_POST['submit_search'])) {
    if (isset($_POST['sessionType']) && $_POST['sessionType'] != 'other') {
        $_SESSION['sql_session_type'] = "AND ms.type = ?s";
        $_SESSION['sql_session_type_args'] = [$_POST['sessionType']];
    } elseif (isset($_POST['sessionType']) && $_POST['sessionType'] == 'other') {
        $_SESSION['sql_session_type'] = '';
        $_SESSION['sql_session_type_args'] = [];
    }
    if (isset($_POST['remoteType']) && $_POST['remoteType'] != -1) {
        $_SESSION['sql_type_remote'] = "AND ms.type_remote = ?d";
        $_SESSION['sql_type_remote_args'] = [$_POST['remoteType']];
    } elseif (isset($_POST['remoteType']) && $_POST['remoteType'] == -1) {
        $_SESSION['sql_type_remote'] = '';
        $_SESSION['sql_type_remote_args'] = [];
    }

    redirect_to_home_page("modules/session/index.php?course=$course_code&searchOn=true");
}

if (isset($_GET['searchOn'])) {
    $data['sessionType'] = (count($_SESSION['sql_session_type_args']) == 1) ? $_SESSION['sql_session_type_args'][0] : '';
    $data['remoteType'] = (count($_SESSION['sql_type_remote_args']) == 1) ? $_SESSION['sql_type_remote_args'][0] : '';
}

$data['individuals_group_sessions'] = Database::get()->queryArray("
    SELECT DISTINCT ms.*
    
    FROM mod_session ms

    INNER JOIN mod_session_users msu
        ON msu.session_id = ms.id
       AND msu.participants = ?d

    WHERE ms.visible = ?d
      AND ms.course_id = ?d
      $_SESSION[sql_type_remote]
      $_SESSION[sql_session_type]

    ORDER BY ms.start DESC
", $uid, 1, $course_id, $_SESSION['sql_type_remote_args'], $_SESSION['sql_session_type_args']);


foreach ($data['individuals_group_sessions'] as $s) {
    if(!$s->type_remote){
        // This refers to session completion with completed meeting.
        check_session_completion_by_meeting_completed($s->id,$uid);
    }elseif($s->type_remote){
        // This refers to session completion with completed tc.
        check_session_completion_by_tc_completed($s->id,$uid);
    }

    // This refers to session completion for other activities.
    check_session_progress($s->id,$uid);  // check session completion - call to Game.php
    check_session_completion_without_activities($s->id);
    check_session_completion_with_expired_time($s->id);
}

$visible_sessions_id = [];
$visible_user_sessions = findUserVisibleSessions($uid, $data['individuals_group_sessions']);
foreach ($visible_user_sessions as $d) {
    $visible_sessions_id[] = $d->id;
}

foreach($data['individuals_group_sessions'] as $cu){
    $not_shown = false;
    $vis = $cu->visible;
    $per = 0;
    $has_badge = 0;
    if(participation_in_session($cu->id)){
        if (!(is_null($cu->start)) and (date('Y-m-d H:i:s') < $cu->start)) {
            $not_shown = true;
            $icon = icon('fa-clock fa-md', $langSessionNotStarted);
            $has_badge = -1;
        } else if (!in_array($cu->id, $visible_sessions_id)) {
            $not_shown = true;
            $icon = icon('fa-minus-circle fa-md', $langSessionNotCompleted);
            $has_badge = -2;
        } else {
            if (in_array($cu->id, $visible_sessions_id)) {
                $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $cu->id);
                if ($sql_badge) {
                    $badge_id = $sql_badge->id;
                    $has_badge = $badge_id;
                    $per = get_cert_percentage_completion('badge', $badge_id);
                    if ($per == 100) {
                        $icon = icon('fa-check-circle fa-md', $langInstallEnd);
                    } else {
                        $icon = icon('fa-hourglass-2 fa-md', $per . "%");
                    }
                }
            }
        }
    }
    $cu->display = ($vis == 0 or $not_shown) ? 'not_visible' : '';
    $cu->icon = $icon ?? '';
    $cu->percentage = round($per);
    $cu->has_badge = $has_badge;
    $cu->consultant = participant_name($cu->creator);
    $cu->is_accepted_user = Database::get()->querySingle("SELECT is_accepted FROM mod_session_users WHERE session_id = ?d AND participants = ?d",$cu->id,$uid)->is_accepted;
    $cu->user_participant = session_participants_ids($cu->id);
}