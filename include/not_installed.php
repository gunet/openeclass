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

require_once '../vendor/autoload.php';

$viewsDir = '../resources/views/install';
$cacheDir = '../storage/views/';
if (!is_dir($cacheDir)) {
    $tempDir = $cacheDir;
    $cacheDir = null;
    if (mkdir($tempDir, 0755, true)) {
        $cacheDir = $tempDir;
    }
}
if (!is_writable($cacheDir) or !$cacheDir) {
    $cacheDir = sys_get_temp_dir() . '/storage';
    if (!(is_dir($cacheDir) or mkdir($cacheDir, 0755, true))) {
        die("Error: Unable to find a writable storage directory - tried '$cacheDir'.");
    }
}

use Jenssegers\Blade\Blade;
$blade = new Blade($viewsDir, $cacheDir);

if (isset($_GET['err_config'])) {
    $data['err_config'] = true;
} else if (isset($_GET['err_db'])) {
    $data['err_db'] = true;
} else {
    exit;
}
echo $blade->make('not_installed', $data)->render();
