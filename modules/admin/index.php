<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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
require_once 'modules/admin/modalconfirmation.php';

$toolName = $langAdmin;
define('HIDE_TOOL_TITLE', 1);

if (isset($is_admin) and $is_admin) {
    $data['is_admin'] = $is_admin;
}
$data['release_info'] = get_eclass_release();

// Construct a table with platform identification info
$data['action_bar'] = action_bar(array(
       [ 'title' => $langBack,
         'url' => "{$urlServer}main/portfolio.php",
         'icon' => 'fa-reply',
         'button-class' => 'submitAdminBtn d-none',
         'level' => 'primary' ],
        [ 'title' => $langMaintenanceOn,
          'url' => "maintenance_config.php",
          'icon' => 'fa-lock',
          'button-class' => 'btn-danger',
          'level' => 'primary-label',
          'show' => get_config('maintenance') != 1 ],
        [ 'title' => $langMaintenanceOff,
          'url' => "maintenance_config.php",
          'icon' => 'fa-unlock',
          'button-class' => 'btn-success',
          'level' => 'primary-label',
          'show' => get_config('maintenance') != 0 ]),
        false);

$data['serverVersion'] = Database::get()->attributes()->serverVersion();
$data['siteName'] = $siteName;

// Count prof requests with status = 1
$data['count_prof_requests'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user_request WHERE state = 1 AND status = " . USER_TEACHER)->cnt;
// Find last course created
$data['lastCreatedCourse'] = Database::get()->querySingle("SELECT code, title, prof_names FROM course ORDER BY id DESC LIMIT 0, 1");
// Find last prof registration
$data['lastProfReg'] = Database::get()->querySingle("SELECT givenname, surname, username, registered_at FROM user WHERE status = " . USER_TEACHER . " ORDER BY id DESC LIMIT 0,1");
// Find last stud registration
$data['lastStudReg'] = Database::get()->querySingle("SELECT givenname, surname, username, registered_at FROM user WHERE status = " . USER_STUDENT . " ORDER BY id DESC LIMIT 0,1");
// Find admin's last login
$lastadminloginres = Database::get()->querySingle("SELECT `when` FROM loginout WHERE id_user = ?d AND action = 'LOGIN' ORDER BY `when` DESC LIMIT 1,1", $uid);
$data['lastregisteredprofs'] = 0;
$data['lastregisteredstuds'] = 0;
if ($lastadminloginres && $lastadminloginres->when) {
    $lastadminlogin = $lastadminloginres->when;
    // Count profs registered after last login
    $data['lastregisteredprofs'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = " . USER_TEACHER . " AND registered_at > ?t", $lastadminlogin)->cnt;
    // Count studs registered after last login
    $data['lastregisteredstuds'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = " . USER_STUDENT . " AND registered_at > ?t", $lastadminlogin)->cnt;
}
// INDEX RELATED
if (get_config('enable_indexing')) {
    require_once 'modules/search/indexer.class.php';
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
$data['cronParams'] = Database::get()->queryArray("SELECT name, last_run FROM cron_params");

// H5P related
$ts = get_config('h5p_update_content_ts');
$data['ts'] = format_locale_date(strtotime($ts), 'short', false);

view('admin.index', $data);

function get_eclass_release() {
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
