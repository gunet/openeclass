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

$data['a'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d", COURSE_INACTIVE)->count;
$data['a1'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_OPEN)->count;
$data['a2'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_REGISTRATION)->count;
$data['a3'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d", COURSE_CLOSED)->count;


$total = 0;
$userCounts = Database::get()->queryArray("SELECT status, COUNT(*) as count FROM user WHERE expires_at > NOW() GROUP BY status");

foreach ($userCounts as $item) {
    $total += $count[$item->status] = $item->count;
}

$data['total'] = $total;
$data['count'] = [USER_TEACHER => 0, USER_STUDENT => 0, USER_GUEST => 0];
$data['institution'] = $Institution;
$data['institution_url'] = $InstitutionUrl;
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