<?php

/* ========================================================================
 * Open eClass 3.2
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * @file: video.php
 *
 * @abstract Handle old links to media files for backward compatibility with 2.x code
 */

$require_current_course = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';

if (isset($_GET['course']) and isset($_GET['id'])) {
    $video = Database::get()->querySingle('SELECT * FROM video
        WHERE course_id = ?d AND path = ?s',
        $course_id, $_GET['id']);
    if ($video and resource_access($video->visible, $video->public)) {
        $url = 'modules/video/file.php?course=' .  urlencode($course_code) .
               '&id=' . $video->id;
        if (isset($_GET['action']) and $_GET['action'] == 'download') {
            $url .= 'attachment';
        }
        redirect_to_home_page($url);
    }
}

not_found();
