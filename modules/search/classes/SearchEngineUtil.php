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

class SearchEngineUtil {

    public static function makeDocumentFieldUrl(string $course_id, string $filename, string $format, string $path): string {
        global $urlServer;

        $courseCode = course_id_to_code($course_id);
        $fieldUrl = $urlServer . "modules/document/index.php?course=" . $courseCode . "&amp;download=" . getIndirectReference($path);
        if ($format == '.dir') {
            $urlAction = 'openDir';
            $fieldUrl = $urlServer . 'modules/document/index.php?course=' . $courseCode . '&amp;' . $urlAction . '=' . $path;
        }
        return $fieldUrl;
    }

}