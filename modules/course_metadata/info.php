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

$require_current_course = true;
define('STATIC_MODULE', 1);
require_once '../../include/baseTheme.php';
$pageName = $langCourseMetadata;
require_once 'modules/course_metadata/CourseXML.php';
require_once 'modules/course_metadata/CourseXMLConfig.php';

// exit if feature disabled or no metadata present
if (!get_config('course_metadata') || !file_exists(CourseXMLConfig::getCourseXMLPath($course_code))) {
    header("Location: {$urlServer}courses/$course_code/index.php");
    exit();
}

$xml = CourseXMLElement::init($course_id, $course_code);
$tool_content .= $xml->asDiv();

$head_content .= <<<EOF
<style type="text/css">
.ui-widget {
    font-family: "Trebuchet MS",Tahoma,Arial,Helvetica,sans-serif;
    font-size: 13px;
}

.ui-widget-content {
    color: rgb(119, 119, 119);
}
</style>
EOF;
draw($tool_content, 2, null, $head_content);
