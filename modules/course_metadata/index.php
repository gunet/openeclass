<?php
/* ========================================================================
 * Open eClass 2.8
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
require_once 'CourseXML.php';

// exit if feature disabled
if (!get_config('course_metadata')) {
    header("Location: {$urlServer}courses/$code_cours/index.php");
    exit();
}

if (isset($_POST['submit']))
    $tool_content .= submitForm();
else
    $tool_content .= displayForm();

$head_content .= "<link href='../../js/jquery-ui.css' rel='stylesheet' type='text/css'>";
load_js('jquery');
load_js('jquery-ui-new');
load_js('jquery-multiselect');
$head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */

    $(function() {
        $( "#tabs" ).tabs();
    });
        
    $(document).ready(function(){
        $( "#multiselect" ).multiselect();
    });

/* ]]> */
</script>
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


//--- HELPER FUNCTIONS ---//

function displayForm() {
    global $cours_id, $code_cours;
    $xml = CourseXMLElement::init($cours_id, $code_cours);
    return $xml->asForm();
}

function submitForm() {
    global $cours_id, $code_cours, $urlServer, $webDir,
           $langModifDone, $langBack, $langBackCourse, $mysqlMainDb;
    
    // handle uploaded files
    $fileData = array();
    foreach(CourseXMLElement::$binaryFields as $bkey) {
        if (isset($_FILES[$bkey]) && is_uploaded_file($_FILES[$bkey]['tmp_name'])) {
            $fileData[$bkey] = base64_encode(file_get_contents($_FILES[$bkey]['tmp_name']));
            $fileData[$bkey .'_mime'] = $_FILES[$bkey]['type'];
        }
    }
    
    $skeleton = $webDir . '/modules/course_metadata/skeleton.xml';
    $extraData = CourseXMLElement::getAutogenData($cours_id);
    $data = array_merge($_POST, $extraData, $fileData);
    // course-based adaptation
    list($dnum)  = mysql_fetch_row(db_query("select count(id) from document where course_id = " . $cours_id, $mysqlMainDb));
    list($vnum)  = mysql_fetch_row(db_query("select count(id) from video", $code_cours));
    list($vlnum) = mysql_fetch_row(db_query("select count(id) from videolinks", $code_cours));
    if ($dnum + $vnum + $vlnum < 1)
        $data['course_confirmVideolectures'] = 'false';
    
    $xml = simplexml_load_file($skeleton, 'CourseXMLElement');
    $xml->adapt($data);
    $xml->populate($data);
    
    CourseXMLElement::save($code_cours, $xml);

    return "<p class='success'>$langModifDone</p>
            <p>&laquo; <a href='" . $_SERVER['SCRIPT_NAME'] . "?course=$code_cours'>$langBack</a></p>
            <p>&laquo; <a href='{$urlServer}courses/$code_cours/index.php'>$langBackCourse</a></p>";
}
