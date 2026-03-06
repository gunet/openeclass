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

use Jenssegers\Blade\Blade;

require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

/**
 * Lightweight Blade renderer for the learning path/scorm player and fragments.
 * Bypasses the heavy view() function (breadcrumbs, sidebar courses, LTI, etc.)
 * since the player is a standalone fullscreen page.
 * This function returns the HTML string instead of directly echoing, thus the partial nature.
 */
function render_lp_partial(string $template, array $data): string {
    global $webDir;

    $views = $webDir . '/resources/views/';
    $cacheDir = $webDir . '/storage/views/';

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

    $blade = new Blade($views, $cacheDir);
    return $blade->make($template, $data)->render();
}

/**
 * Map a learning path module content type to a Font Awesome icon class.
 */
function lp_module_icon(string $contentType, ?string $path): string {
    switch ($contentType) {
        case CTEXERCISE_:
            return 'fa-solid fa-edit';
        case CTLINK_:
            return 'fa-solid fa-link';
        case CTCOURSE_DESCRIPTION_:
            return 'fa-solid fa-circle-info';
        case CTDOCUMENT_:
            return choose_image(basename($path ?? ''));
        case CTSCORM_:
        case CTSCORMASSET_:
            return 'fa-solid fa-file-code';
        case CTMEDIA_:
        case CTMEDIALINK_:
            return 'fa-solid fa-film';
        default:
            return choose_image(basename($path ?? ''));
    }
}
