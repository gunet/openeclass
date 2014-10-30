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


require_once '../../include/baseTheme.php';
require_once 'modules/course_metadata/CourseXML.php';
require_once 'modules/course_metadata/CourseXMLConfig.php';

if (isset($_REQUEST['course'])) {
    $code = $_REQUEST['course'];
    $course_id = course_code_to_id($code);
}

// exit if feature disabled or no metadata present
if (!isset($_REQUEST['course']) || !get_config('course_metadata') || !file_exists(CourseXMLConfig::getCourseXMLPath($code))) {
    echo "course error";
    exit();
}

$xml = CourseXMLElement::init($course_id, $code);
echo $xml->asDiv();
exit();