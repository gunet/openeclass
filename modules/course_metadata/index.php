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
$require_course_admin = true;
define('STATIC_MODULE', 1);
require_once '../../include/baseTheme.php';
$nameTools = $langCourseMetadata;
require_once 'modules/course_metadata/CourseXML.php';

// exit if feature disabled
if (!get_config('course_metadata')) {
    header("Location: {$urlServer}courses/$course_code/index.php");
    exit();
}


if (isset($_POST['submit']))
    $tool_content .= submitForm();
else
    $tool_content .= displayForm();


draw($tool_content, 2, null, $head_content);


//--- HELPER FUNCTIONS ---//

function displayForm() {
    global $course_code, $webDir;

    $filename = $webDir . '/courses/' . $course_code . '/courseMetadata.xml';
    if (file_exists($filename))
        $xml = simplexml_load_file($filename, 'CourseXMLElement');
    else {
        $filename = $webDir . '/modules/course_metadata/skeleton.xml';
        $xml = simplexml_load_file ($filename, 'CourseXMLElement');
    }

    return $xml->asForm();
}

function submitForm() {
    global $course_code, $urlServer, $webDir,
           $langModifDone, $langBack, $langBackCourse;
    
    $filename = $webDir . '/courses/' . $course_code . '/courseMetadata.xml';
    $skeleton = $webDir . '/modules/course_metadata/skeleton.xml';
    
    $xml = simplexml_load_file($skeleton, 'CourseXMLElement');
    // TODO: append $_POST with hidden data such as units
    // TODO: append skeleton XML with additional fields (ex more instructors, units, etc)
    $xml->populate($_POST);

    // save xml file
    $doc = new DOMDocument('1.0');
    $doc->loadXML( $xml->asXML() );
    $doc->formatOutput = true;
    $doc->save($filename);

    $out = "<p class='success'>$langModifDone</p>
            <p>&laquo; <a href='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code'>$langBack</a></p>
            <p>&laquo; <a href='{$urlServer}courses/$course_code/index.php'>$langBackCourse</a></p>";

    // debug TODO: remove after all todos have been implemented
//    $out .= "<pre>";
//    ob_start();
//    $out .= print_r($_POST, true);
//    $out .= $doc->saveXML();
//    $out .= print_r($xml, true);
//    $out .= ob_get_contents();
//    ob_end_clean();
//    $out .= "</pre>";

    return $out;
}