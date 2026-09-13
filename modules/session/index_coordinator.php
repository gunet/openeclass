<?php

 $data['action_bar'] = action_bar([
    [
        'title' => $langAddSession,
        'url' => 'new.php?course=' . $course_code,
        'icon' => 'fa-plus-circle',
        'button-class' => 'btn-success',
        'level' => 'primary-label',
        'show' => ($is_editor || !$is_course_reviewer)
    ],
    [
        'title' => $langReportAttendances,
        'url' => $urlAppend . "modules/session/consulting_completion_coordinator.php?course=$course_code",
        'icon' => 'fa-solid fa-users',
        'button-class' => 'btn-success',
        'level' => 'primary-label'
    ],
    [
        'title' => $langSummaryScheduledSessions,
        'url' => 'session_scheduled.php?course=' . $course_code,
        'icon' => 'fa-solid fa-list',
        'button-class' => 'btn-success',
        'level' => 'primary-label'
    ],
    [
        'title' => $langPercentageCompletedConsultingByUser,
        'url' => $urlAppend . "modules/session/completion.php?course=" . $course_code . "&showCompletedConsulting=true",
        'icon' => 'fa-solid fa-percent',
        'button-class' => 'btn-success'
    ],
    [
        'title' => $langDelAllSessions,
        'url' => $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&delete_all_sessions=true',
        'class' => "delete",
        'confirm' => $langContinueToDelAllSessions,
        'icon' => 'fa-xmark'
    ],
], false);

if (!isset($_GET['searchOn']) && !isset($_POST['submit_search'])) {
    $_SESSION['sql_consultant'] = '';
    $_SESSION['sql_consultant_args'] = [];
    $_SESSION['sql_users'] = '';
    $_SESSION['sql_users_args'] = [];
    $_SESSION['sql_course_id'] = "WHERE s.course_id = ?d";
    $_SESSION['sql_session_type'] = '';
    $_SESSION['sql_session_type_args'] = [];
    $_SESSION['sql_type_remote'] = '';
    $_SESSION['sql_type_remote_args'] = [];
    
    $data['chooseSelectionVal'] = 0;
    $data['for_consultant'] = 0;
    $data['searchUserId'] = 0;
    $data['remoteType'] = -1;
    $data['sessionType'] = 'other';
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_POST['submit_search'])) {
    $data['chooseSelectionVal'] = $_POST['chooseSelection'] ?? '';
    $_SESSION['chooseSelectionVal_Session'] = $data['chooseSelectionVal'];
    if ($data['chooseSelectionVal'] == 0) { // ola default
        $_SESSION['sql_users'] = '';
        $_SESSION['sql_users_args'] = [];
        $_SESSION['sql_consultant'] = '';
        $_SESSION['sql_consultant_args'] = [];
        $_SESSION['sql_course_id'] = "WHERE s.course_id = ?d";
    } elseif ($data['chooseSelectionVal'] == 1) { // search specific consultant
        $_SESSION['sql_users'] = '';
        $_SESSION['sql_users_args'] = [];
        $_SESSION['sql_consultant'] = "AND s.creator = ?d";
        $_SESSION['sql_consultant_args'] = [intval($_POST['forConsultant']) ?? 0];
        $_SESSION['sql_course_id'] = "WHERE s.course_id = ?d";
    } elseif ($data['chooseSelectionVal'] == 2) {  // search specific user
        $_SESSION['sql_users'] = "INNER JOIN mod_session_users su ON su.session_id=s.id WHERE su.participants = ?d AND su.is_accepted = 1";
        $_SESSION['sql_users_args'] = [intval($_POST['forUserSearch']) ?? 0];
        $_SESSION['sql_consultant'] = '';
        $_SESSION['sql_consultant_args'] = [];
        $_SESSION['sql_course_id'] = "AND s.course_id = ?d";
    }

    if (isset($_POST['sessionType']) && $_POST['sessionType'] != 'other') {
        $_SESSION['sql_session_type'] = "AND s.type = ?s";
        $_SESSION['sql_session_type_args'] = [$_POST['sessionType']];
    } elseif (isset($_POST['sessionType']) && $_POST['sessionType'] == 'other') {
        $_SESSION['sql_session_type'] = '';
        $_SESSION['sql_session_type_args'] = [];
    }
    if (isset($_POST['remoteType']) && $_POST['remoteType'] != -1) {
        $_SESSION['sql_type_remote'] = "AND s.type_remote = ?d";
        $_SESSION['sql_type_remote_args'] = [$_POST['remoteType']];
    } elseif (isset($_POST['remoteType']) && $_POST['remoteType'] == -1) {
        $_SESSION['sql_type_remote'] = '';
        $_SESSION['sql_type_remote_args'] = [];
    }

    redirect_to_home_page("modules/session/index.php?course=$course_code&searchOn=true");
}

if (isset($_GET['searchOn'])) {
    $data['chooseSelectionVal'] = $_SESSION['chooseSelectionVal_Session'];
    $data['for_consultant'] = (count($_SESSION['sql_consultant_args']) == 1) ? $_SESSION['sql_consultant_args'][0] : '';
    $data['searchUserId'] = (count($_SESSION['sql_users_args']) == 1) ? $_SESSION['sql_users_args'][0] : '';
    $data['sessionType'] = (count($_SESSION['sql_session_type_args']) == 1) ? $_SESSION['sql_session_type_args'][0] : '';
    $data['remoteType'] = (count($_SESSION['sql_type_remote_args']) == 1) ? $_SESSION['sql_type_remote_args'][0] : '';
}

$data['individuals_group_sessions'] = Database::get()->queryArray("SELECT s.* FROM mod_session s
                                                                    $_SESSION[sql_users]
                                                                    $_SESSION[sql_course_id]
                                                                    $_SESSION[sql_consultant]
                                                                    $_SESSION[sql_session_type]
                                                                    $_SESSION[sql_type_remote]
                                                                    ORDER BY s.`start` DESC",
                                                                    $_SESSION['sql_users_args'],
                                                                    $course_id, 
                                                                    $_SESSION['sql_consultant_args'],
                                                                    $_SESSION['sql_session_type_args'],
                                                                    $_SESSION['sql_type_remote_args']);


$data['all_consultants'] = Database::get()->queryArray("
    SELECT DISTINCT
        u.id,
        u.givenname,
        u.surname,
        ms.creator

    FROM mod_session ms

    INNER JOIN user u
        ON u.id = ms.creator

    WHERE ms.course_id = ?d
", $course_id);


$data['usersInCoordinatorView'] = Database::get()->queryArray("
    SELECT DISTINCT
        msu.participants,
        u.id,
        u.givenname,
        u.surname

    FROM mod_session_users msu

    INNER JOIN user u
        ON u.id = msu.participants

    INNER JOIN mod_session ms
        ON ms.id = msu.session_id

    WHERE msu.is_accepted = ?d
      AND ms.course_id = ?d
", 1, $course_id);


if(count($data['individuals_group_sessions']) > 0){
    $participants = array();
    foreach ($data['individuals_group_sessions'] as $s) {
        $s->consultant = participant_name($s->creator);
        $s->user_participant = session_participants_ids($s->id);
    }
}