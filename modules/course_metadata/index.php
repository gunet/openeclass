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

$xmlFile = $webDir . '/courses/' . $course_code . '/courseMetadata.xml';
$skeleton = $webDir . '/modules/course_metadata/skeleton.xml';

if (isset($_POST['submit']))
    $tool_content .= submitForm();
else
    $tool_content .= displayForm();


draw($tool_content, 2, null, $head_content);


//--- HELPER FUNCTIONS ---//

function displayForm() {
    global $xmlFile, $skeleton;
    
    $skeletonXML = simplexml_load_file ($skeleton, 'CourseXMLElement');
    $data = gatherExtraData(); // preload form with auto-generated data

    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile, 'CourseXMLElement');
        if (!$xml) // fallback if xml is broken
            return $skeletonXML->asForm($data);
    } else // fallback if starting fresh
        return $skeletonXML->asForm($data);
    
    // load form from skeleton if it has more fields (useful for incremental updates)
    if ($skeletonXML->countAll() > $xml->countAll()) {
        $skeletonXML->populate($xml->asFlatArray());
        return $skeletonXML->asForm($data);
    }

    return $xml->asForm($data);
}

function submitForm() {
    global $course_code, $urlServer, $xmlFile, $skeleton,
           $langModifDone, $langBack, $langBackCourse;
    
    $extraData = gatherExtraData();
    $data = array_merge($_POST, $extraData);
    $xml = simplexml_load_file($skeleton, 'CourseXMLElement');
    // TODO: append skeleton XML with additional fields (ex more instructors, units, etc) as necessary
    $xml->populate($data);

    // save xml file
    $doc = new DOMDocument('1.0');
    $doc->loadXML( $xml->asXML() );
    $doc->formatOutput = true;
    $doc->save($xmlFile);

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

function gatherExtraData() {
    global $course_id, $currentCourseLanguage, $urlServer, $course_code, 
           $titulaires, $title;
    $extra = array();
    
    $res = db_query("SELECT * FROM course WHERE id = " . intval($course_id));
    $course = mysql_fetch_assoc($res);
    if (!$course)
        return array();
    
    $extra['course_language'] = $currentCourseLanguage;
    $extra['course_url'] = $urlServer . 'courses/'. $course_code;
    $extra['course_instructor_fullName_' . $currentCourseLanguage] = $titulaires;
    $extra['course_title_' . $currentCourseLanguage] = $title;
    $extra['course_keywords_' . $currentCourseLanguage] = $course['keywords'];
    // TODO: course units
    // TODO: course description
    // TODO: course objectives

    return $extra;
}