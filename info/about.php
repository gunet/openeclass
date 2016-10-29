<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

/**
 * @file about.php
 * @brief Displays general platform information.
 * @author original developed by Ophelia Neofytou.
 */

require_once '../include/baseTheme.php';
$pageName = $langInfo;

$data['course_inactive'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d", COURSE_INACTIVE)->count;
$data['course_open'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_OPEN)->count;
$data['course_registration'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_REGISTRATION)->count;
$data['course_closed'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_CLOSED)->count;


$count_total = 0;
$userCounts = Database::get()->queryArray("SELECT status, COUNT(*) as count FROM user WHERE expires_at > NOW() GROUP BY status");
$count_status = [USER_TEACHER => 0, USER_STUDENT => 0, USER_GUEST => 0];

foreach ($userCounts as $item) {
    $count_status[$item->status] = $item->count;
    $count_total += $item->count;
}

$data['count_total'] = $count_total;
$data['count_status'] = $count_status;
$data['institution'] = $Institution;
$data['institution_url'] = canonicalize_url($InstitutionUrl);
$data['siteName'] = $siteName;
$data['eclass_version'] = ECLASS_VERSION;
$data['admin_name'] = get_config('admin_name');
$data['action_bar'] = action_bar(
                                [
                                    [
                                        'title' => $langBack,
                                        'url' => $urlServer,
                                        'icon' => 'fa-reply',
                                        'level' => 'primary-label',
                                        'button-class' => 'btn-default'
                                    ]
                                ], false);
$data['menuTypeID'] = isset($uid) && $uid ? 1 : 0 ;

view('info.about', $data);