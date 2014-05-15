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
define('STATIC_MODULE', 1);
require_once '../../include/baseTheme.php';
$nameTools = $langCourseMetadata;
require_once 'CourseXML.php';

// exit if feature disabled or no metadata present
if (!get_config('course_metadata') || !file_exists(CourseXMLElement::getCourseXMLPath($code_cours))) {
    header("Location: {$urlServer}courses/$code_cours/index.php");
    exit();
}

$xml = CourseXMLElement::init($cours_id, $code_cours);
$tool_content .= $xml->asDiv();

$head_content .= "<link href='../../js/jquery-ui.css' rel='stylesheet' type='text/css'>";
load_js('jquery');
load_js('jquery-ui-new');
load_js('jquery-multiselect');
$head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */

    $(document).ready(function(){
        $( ".cmetaaccordion" ).accordion({
            collapsible: true,
            active: false
        });
        
        $( ".tabs" ).tabs();
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
