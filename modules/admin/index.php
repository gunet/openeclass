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

$require_usermanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'modules/admin/modalconfirmation.php';

$toolName = $langAdmin;

$col_size = '';

if (isset($is_admin) and $is_admin) {
    $data['is_admin'] = $is_admin;
    $col_size = '3';
} else if ($is_power_user or $is_departmentmanage_user) {
    $col_size = '2';
} else if ($is_usermanage_user) {
    $col_size = '1';
}
$data['col_size'] = $col_size;

$data['release_info'] = get_eclass_release();

// Construct a table with platform identification info
$data['action_bar'] = action_bar(
    array(
        [
            'title' => $langMaintenanceOff,
            'url' => "maintenance_config.php",
            'icon' => 'fa-unlock',
            'button-class' => 'btn-success',
            'level' => 'primary-label',
            'show' => get_config('maintenance') != 0
        ]
    ),
    false
);

$data['serverVersion'] = Database::get()->attributes()->serverVersion();
$data['siteName'] = $siteName;

if (!check_stored_procedures()) {
    Session::flash('message', $langNoStoredProcedures);
    Session::flash('alert-class', 'alert-danger');
}

$is_saek_admin  = $is_departmentmanage_user && !$is_admin;
$department_id = getCurrentTenant() ? getCurrentTenant()->department_id : 0;


// Count prof requests with status = 1
$data['count_prof_requests'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user_request WHERE state = 1 AND status = " . USER_TEACHER)->cnt;
// Find last course created
$data['lastCreatedCourse'] = Database::get()->querySingle("SELECT code, title, prof_names FROM course ORDER BY id DESC LIMIT 0, 1");
// Find last prof registration
if ($is_saek_admin) {
    $data['lastProfReg'] = Database::get()->querySingle(
        "
        SELECT u.givenname, u.surname, u.username, u.registered_at
        FROM user u
        JOIN user_department ud ON ud.user = u.id
        WHERE ud.department = ?d
          AND u.status = ?d
        ORDER BY u.registered_at DESC
        LIMIT 1
        ",
        $department_id,
        USER_TEACHER
    );
    $data['lastStudReg'] = Database::get()->querySingle(
        "
        SELECT u.givenname, u.surname, u.username, u.registered_at
        FROM user u
        JOIN user_department ud ON ud.user = u.id
        WHERE ud.department = ?d
          AND u.status = ?d
        ORDER BY u.registered_at DESC
        LIMIT 1
        ",
        $department_id,
        USER_STUDENT
    );
} else {
    $data['lastProfReg'] = Database::get()->querySingle(
        "
        SELECT givenname, surname, username, registered_at
        FROM user
        WHERE status = ?d
        ORDER BY registered_at DESC
        LIMIT 1
        ",
        USER_TEACHER
    );

    $data['lastStudReg'] = Database::get()->querySingle(
        "
        SELECT givenname, surname, username, registered_at
        FROM user
        WHERE status = ?d
        ORDER BY registered_at DESC
        LIMIT 1
        ",
        USER_STUDENT
    );
}
// Find admin's last login
$lastadminloginres = Database::get()->querySingle("SELECT `when` FROM loginout WHERE id_user = ?d AND action = 'LOGIN' ORDER BY `when` DESC LIMIT 1,1", $uid);
$data['lastregisteredprofs'] = 0;
$data['lastregisteredstuds'] = 0;
if ($lastadminloginres && $lastadminloginres->when) {
    $lastadminlogin = $lastadminloginres->when;
    if ($is_saek_admin) {
        $data['lastregisteredprofs'] = Database::get()->querySingle(
            "
            SELECT COUNT(DISTINCT u.id) AS cnt
            FROM user u
            JOIN user_department ud ON ud.user = u.id
            WHERE ud.department = ?d
              AND u.status = ?d
              AND u.registered_at > ?t
            ",
            $department_id,
            USER_TEACHER,
            $lastadminlogin
        )->cnt;
        $data['lastregisteredstuds'] = Database::get()->querySingle(
            "
            SELECT COUNT(DISTINCT u.id) AS cnt
            FROM user u
            JOIN user_department ud ON ud.user = u.id
            WHERE ud.department = ?d
              AND u.status = ?d
              AND u.registered_at > ?t
            ",
            $department_id,
            USER_STUDENT,
            $lastadminlogin
        )->cnt;
    } else {

        $data['lastregisteredprofs'] = Database::get()->querySingle(
            "
            SELECT COUNT(*) AS cnt
            FROM user
            WHERE status = ?d
              AND registered_at > ?t
            ",
            USER_TEACHER,
            $lastadminlogin
        )->cnt;

        $data['lastregisteredstuds'] = Database::get()->querySingle(
            "
            SELECT COUNT(*) AS cnt
            FROM user
            WHERE status = ?d
              AND registered_at > ?t
            ",
            USER_STUDENT,
            $lastadminlogin
        )->cnt;
    }
}

// INDEX RELATED
if (get_config('ext_solr_enabled')) {
    require_once 'modules/search/classes/SolrSearchEngine.php';
    $searchEngine = new SolrSearchEngine();
    $coreStatus = $searchEngine->coreStatus();
    $data['coreStats'] = $coreStatus;
    $data['idxModal'] = modalConfirmation('confirmReindexDialog', 'confirmReindexLabel', $langConfirmEnableIndexTitle, $langConfirmEnableSolrIndex, 'confirmReindexCancel', 'confirmReindexOk');
} else if (get_config('enable_indexing')) {
    require_once 'modules/search/lucene/indexer.class.php';
    $idx = new Indexer();

    $data['numDocs'] = 0;
    $data['isOpt'] = $langNo;
    if ($idx->getIndex()) {
        $data['numDocs'] = $idx->getIndex()->numDocs();
        $data['isOpt'] = (!$idx->getIndex()->hasDeletions()) ? $langYes : $langNo;
        $data['idxHasDeletions'] = $idx->getIndex()->hasDeletions();
    }

    $data['idxModal'] = modalConfirmation('confirmReindexDialog', 'confirmReindexLabel', $langConfirmEnableIndexTitle, $langConfirmEnableIndex, 'confirmReindexCancel', 'confirmReindexOk');
}
// CRON RELATED
$data['cronParams'] = $cronParams = Database::get()->queryArray("SELECT name, last_run FROM cron_params");
$colSize = '';
if (count($cronParams) > 0) {
    $colSize = '2';
} else {
    $colSize = '1';
}
$data['colSize'] = $colSize;

// online users
$data['onlineusers'] = getOnlineUsers();

// H5P related
$ts = get_config('h5p_update_content_ts');
$data['ts'] = format_locale_date(strtotime($ts), 'short', false);

// OpenBadges Statistics (only if enabled)
$openBadgesApp = ExtAppManager::getApp('openbadges');
$data['badge_stats'] = ($openBadgesApp && $openBadgesApp->isEnabled()) ? getBadgeStatistics() : null;

view('admin.index', $data);

/**
 * @brief get eclass latest version
 * @return mixed|null
 */
function get_eclass_release()
{
    $ts = get_config('eclass_release_timestamp');
    if (!$ts or time() - $ts > 24 * 3600) {
        set_config('eclass_release_timestamp', time());
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://resources.openeclass.org/current.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result) {
            set_config('eclass_release_info', $result);
            return json_decode($result);
        } else {
            return null;
        }
    }
    return json_decode(get_config('eclass_release_info'));
}


/**
 * @brief check if required stored procedures exist
 * @return bool
 */
function check_stored_procedures()
{
    global $mysqlMainDb;

    $procedure_names = [
        'add_node',
        'delete_node',
        'delete_nodes',
        'get_maxrgt',
        'get_parent',
        'move_nodes',
        'shift_end',
        'shift_left',
        'shift_right',
        'update_node'
    ];

    $db_procedures = Database::get()->queryArray("SHOW PROCEDURE STATUS WHERE Db='$mysqlMainDb'");

    if (count($db_procedures) == 0 or count($db_procedures) != count($procedure_names)) {
        return false;
    } else {
        foreach ($db_procedures as $item) {
            if (!in_array($item->Name, $procedure_names)) {
                return false;
            }
        }
    }
    return true;
}

/**
 * @brief get OpenBadges statistics for admin dashboard
 * @return array
 */
function getBadgeStatistics()
{
    $stats = [];
    
    // 1. Users with connected backpack provider
    $stats['users_with_backpack'] = Database::get()->querySingle(
        "SELECT COUNT(DISTINCT user_id) AS cnt 
         FROM user_backpack_connection 
         WHERE status = 'connected'"
    )->cnt ?? 0;
    
    // 2. Users who have imported OR exported badges
    $stats['active_backpack_users'] = Database::get()->querySingle(
        "SELECT COUNT(DISTINCT ubc.user_id) AS cnt 
         FROM user_backpack_connection ubc
         WHERE ubc.status = 'connected' 
         AND (
             EXISTS (
                 SELECT 1 FROM user_badge_external ube 
                 WHERE ube.user_id = ubc.user_id
             ) 
             OR EXISTS (
                 SELECT 1 FROM user_badge ub 
                 WHERE ub.user = ubc.user_id 
                 AND ub.external_assertion_id IS NOT NULL
             )
         )"
    )->cnt ?? 0;
    
    // 3. Local badges exported to external backpack
    $stats['exported_badges'] = Database::get()->querySingle(
        "SELECT COUNT(*) AS cnt 
         FROM user_badge 
         WHERE external_assertion_id IS NOT NULL"
    )->cnt ?? 0;
    
    // 4. External badges imported to the system
    $stats['imported_badges'] = Database::get()->querySingle(
        "SELECT COUNT(*) AS cnt 
         FROM user_badge_external"
    )->cnt ?? 0;
    
    // 5. Most exported local badge (with icon)
    $mostExportedBadge = Database::get()->querySingle(
        "SELECT b.title, bi.filename as icon_filename, COUNT(ub.id) as export_count
         FROM user_badge ub
         JOIN badge b ON ub.badge = b.id
         LEFT JOIN badge_icon bi ON b.icon = bi.id
         WHERE ub.external_assertion_id IS NOT NULL
         GROUP BY ub.badge, b.title, bi.filename
         ORDER BY export_count DESC
         LIMIT 1"
    );
    
    if ($mostExportedBadge) {
        $stats['most_exported_badge_title'] = $mostExportedBadge->title;
        $stats['most_exported_badge_count'] = $mostExportedBadge->export_count;
        $stats['most_exported_badge_icon'] = $mostExportedBadge->icon_filename;
    } else {
        $stats['most_exported_badge_title'] = null;
        $stats['most_exported_badge_count'] = 0;
        $stats['most_exported_badge_icon'] = null;
    }
    
    // 5b. Course with most badge exports
    $courseWithMostExports = Database::get()->querySingle(
        "SELECT c.id as course_id, c.title, c.code, COUNT(ub.id) as export_count
         FROM user_badge ub
         JOIN badge b ON ub.badge = b.id
         JOIN course c ON b.course_id = c.id
         WHERE ub.external_assertion_id IS NOT NULL
         GROUP BY b.course_id, c.id, c.title, c.code
         ORDER BY export_count DESC
         LIMIT 1"
    );
    
    if ($courseWithMostExports) {
        $stats['course_most_exports_title'] = $courseWithMostExports->title;
        $stats['course_most_exports_code'] = $courseWithMostExports->code;
        $stats['course_most_exports_count'] = $courseWithMostExports->export_count;
        $stats['course_most_exports_id'] = $courseWithMostExports->course_id;
    } else {
        $stats['course_most_exports_title'] = null;
        $stats['course_most_exports_code'] = null;
        $stats['course_most_exports_count'] = 0;
        $stats['course_most_exports_id'] = null;
    }
    
    // Additional meaningful statistics
    
    // 6. Total local badges in the system
    $stats['total_local_badges'] = Database::get()->querySingle(
        "SELECT COUNT(*) AS cnt FROM badge WHERE active = 1"
    )->cnt ?? 0;
    
    // 7. Users with at least one badge (local or external)
    $stats['users_with_badges'] = Database::get()->querySingle(
        "SELECT COUNT(DISTINCT user_id) AS cnt FROM (
            SELECT user as user_id FROM user_badge WHERE completed = 1
            UNION
            SELECT user_id FROM user_badge_external
         ) AS all_badge_users"
    )->cnt ?? 0;
    
    // 8. Active backpack providers
    $stats['active_providers'] = Database::get()->querySingle(
        "SELECT COUNT(*) AS cnt FROM backpack_provider WHERE active = 1"
    )->cnt ?? 0;
    
    // 9. Recent sync activity (last 30 days)
    $stats['recent_syncs'] = Database::get()->querySingle(
        "SELECT COUNT(DISTINCT user_id) AS cnt 
         FROM user_backpack_connection 
         WHERE last_sync IS NOT NULL 
         AND last_sync >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    )->cnt ?? 0;
    
    // 10. Total badge awards (completed badges)
    $stats['total_badge_awards'] = Database::get()->querySingle(
        "SELECT COUNT(*) AS cnt FROM user_badge WHERE completed = 1"
    )->cnt ?? 0;
    
    // 11. Most recent import
    $recentImport = Database::get()->querySingle(
        "SELECT created_at FROM user_badge_external ORDER BY created_at DESC LIMIT 1"
    );
    $stats['last_import'] = $recentImport && $recentImport->created_at ? 
        format_locale_date(strtotime($recentImport->created_at)) : 
        '-';
    
    // 12. Most recent export
    $recentExport = Database::get()->querySingle(
        "SELECT ub.updated FROM user_badge ub 
         WHERE ub.external_assertion_id IS NOT NULL 
         ORDER BY ub.updated DESC LIMIT 1"
    );
    $stats['last_export'] = $recentExport && $recentExport->updated ? 
        format_locale_date(strtotime($recentExport->updated)) : 
        '-';
    
    return $stats;
}
