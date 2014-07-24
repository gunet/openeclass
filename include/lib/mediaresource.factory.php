<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

require_once 'include/lib/mediaresource.class.php';

class MediaResourceFactory {

    public static function initFromDocument($queryItem) {
        global $urlServer, $course_code;
        return new MediaResource(
                $queryItem->id, $queryItem->course_id, empty($queryItem->title) ? $queryItem->filename : $queryItem->title, // Override title member
                $queryItem->path, null, $urlServer . 'modules/document/mediafile.php?course=' . $course_code . '&amp;id=' . intval($queryItem->id), $urlServer . 'modules/document/play.php?course=' . $course_code . '&amp;id=' . intval($queryItem->id));
    }

    public static function initFromVideo($queryItem) {
        global $urlServer, $course_code;
        return new MediaResource(
                $queryItem->id, $queryItem->course_id, $queryItem->title, $queryItem->path, $queryItem->url, $urlServer . 'modules/video/file.php?course=' . $course_code . '&amp;id=' . intval($queryItem->id), $urlServer . 'modules/video/play.php?course=' . $course_code . '&amp;id=' . intval($queryItem->id));
    }

    public static function initFromVideoLink($queryItem) {
        global $urlServer, $course_code;
        // validate url
        $url = $queryItem->url;
        if ($url == 'http://' || empty($url) || !filter_var($url, FILTER_VALIDATE_URL) || preg_match('/^javascript/i', preg_replace('/\s+/', '', $url))) {
            $url = '#';
        }
        return new MediaResource(
                $queryItem->id, $queryItem->course_id, $queryItem->title, $url, // Override because path is url in db for videolinks
                $url, $url, $urlServer . 'modules/video/playlink.php?course=' . $course_code . '&amp;id=' . intval($queryItem->id));
    }

}
