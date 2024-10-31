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


define('UPGRADE', true);
define('MAINTENANCE_PAGE', true);

require_once '../include/baseTheme.php';

if ($urlAppend[strlen($urlAppend) - 1] != '/') {
    $urlAppend .= '/';
}

$pageName = $langUpgrade;

if ($language == 'el') {
    $upgrade_info_file = 'https://docs.openeclass.org/el/upgrade';
    $link_changes_file = 'https://docs.openeclass.org/el/current';
} else {
    $upgrade_info_file = 'https://docs.openeclass.org/en/upgrade';
    $link_changes_file = 'https://docs.openeclass.org/el/current';
}
$data['upgrade_info_file'] = $upgrade_info_file;
$data['link_changes_file'] = $link_changes_file;

view('upgrade.index', $data);
