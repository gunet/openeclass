<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

define('UPGRADE', true);

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
