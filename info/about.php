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
 * @file about.php
 * @brief Displays general platform information.
 * @author original developed by Ophelia Neofytou.
 */

require_once '../include/baseTheme.php';

if (get_config('dont_display_about_menu')) {
    redirect_to_home_page();
}

$toolName = $langPlatformIdentity;

$data['course_inactive'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d AND is_collaborative = ?d", COURSE_INACTIVE, $collaboration_value)->count;
$data['course_open'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d AND is_collaborative = ?d", COURSE_OPEN, $collaboration_value)->count;
$data['course_registration'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d AND is_collaborative = ?d", COURSE_REGISTRATION, $collaboration_value)->count;
$data['course_closed'] = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible = ?d AND is_collaborative = ?d", COURSE_CLOSED, $collaboration_value)->count;


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
                                        'level' => 'primary',
                                        'button-class' => 'btn-secondary'
                                    ]
                                ], false);

view('info.about', $data);
