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

$require_admin = true;
require_once '../../include/baseTheme.php';

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $messages = [];
    foreach (array('temp' => 2, 'garbage' => 5, 'archive' => 1, 'tmpUnzipping' => 1) as $dir => $days) {
        $messages[] = sprintf("$langCleaningUp", "<b>$days</b>", ($days == 1) ? $langDaySing : $langDayPlur, $dir);
        cleanup("$webDir/courses/$dir", $days);
    }
    Session::Messages($messages, 'alert-success');
    redirect_to_home_page('modules/admin/cleanup.php');
}

$toolName = $langCleanUp;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$data['action_bar'] = action_bar([
                                    [
                                        'title' => $langBack,
                                        'url' => "index.php",
                                        'icon' => 'fa-reply',
                                        'level' => 'primary-label'
                                    ]
                                ]);
$data['menuTypeID'] = 3;
view('admin.server.cleanup', $data);

// Remove all files under $path older than $max_age days
// Afterwards, remove $path as well if it points to an empty directory
function cleanup($path, $max_age) {
    $max_age_seconds = $max_age * 60 * 60 * 24;
    $files_left = 0;
    if ($dh = @opendir($path)) {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' and $file != '..') {
                $filepath = "$path/$file";
                if (is_dir($filepath)) {
                    if (cleanup($filepath, $max_age) == 0) {
                        rmdir($filepath);
                    } else {
                        $files_left++;
                    }
                } else {
                    if (file_older($filepath, $max_age_seconds)) {
                        unlink($filepath);
                    } else {
                        $files_left++;
                    }
                }
            }
        }
        closedir($dh);
    }
    return $files_left;
}

// Returns true if file pointed to by $path is older than $seconds
function file_older($path, $seconds) {
    if (filemtime($path) > time() - $seconds) {
        return false;
    } else {
        return true;
    }
}
