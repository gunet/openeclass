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

if (isset($_POST['submit'])) {
    $tool_content .= submitForm();
}
$tool_content .= displayForm();

load_js('jquery');
load_js('jquery-ui');
load_js('jquery-multiselect');
$head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */
        
    var photoDelete = function(id) {
        $( id + "_image" ).remove();
        $( id + "_hidden" ).remove();
        $( id + "_hidden_mime" ).remove();
        $( id + "_delete" ).remove();
    };

    $(document).ready(function(){
        $( ".cmetaaccordion" ).accordion({
            collapsible: true,
            active: false
        });
        
        $( document ).tooltip({
            track: true
        });
        
        $( "#tabs" ).tabs();
        
        $( "#multiselect" ).multiselect();
        
        $( "#course_coursePhoto_delete" ).on('click', function() {
            $( "#course_coursePhoto_image" ).remove();
            $( "#course_coursePhoto_hidden" ).remove();
            $( "#course_coursePhoto_hidden_mime" ).remove();
        });
        
        $( "#course_instructor_photo_add" ).on('click', function() {
            $( "#course_instructor_photo_container" ).append( '<div class="cmetarow"><span class="cmetalabelinaccordion"></span><span class="cmetafield"><input size="30" name="course_instructor_photo[]" type="file"></span></div>' );
        });
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
    global $course_id, $course_code;
    $xml = CourseXMLElement::init($course_id, $course_code);
    return $xml->asForm();
}

function submitForm() {
    global $course_id, $course_code, $urlServer, $webDir,
    $langModifDone, $langBack, $langBackCourse;

    // handle uploaded files
    $fileData = array();
    foreach (CourseXMLConfig::$binaryFields as $bkey) {
        if (in_array($bkey, CourseXMLConfig::$multipleFields)) {
            if (isset($_FILES[$bkey]) && isset($_FILES[$bkey]['tmp_name']) && isset($_FILES[$bkey]['type'])
                    && is_array($_FILES[$bkey]['tmp_name'])) {
                for ($i = 0; $i < count($_FILES[$bkey]['tmp_name']); $i++) {
                    if (is_uploaded_file($_FILES[$bkey]['tmp_name'][$i])
                            && isValidImage($_FILES[$bkey]['type'][$i])) {
                        // convert to resized jpg if possible
                        $uploaded = $_FILES[$bkey]['tmp_name'][$i];
                        $copied = $_FILES[$bkey]['tmp_name'][$i] . '.new';
                        $type = $_FILES[$bkey]['type'][$i];

                        if (copy_resized_image($uploaded, $type, IMAGESIZE_LARGE, IMAGESIZE_LARGE, $copied)) {
                            $fileData[$bkey][$i] = base64_encode(file_get_contents($copied));
                            $fileData[$bkey . '_mime'][$i] = 'image/jpeg'; // copy_resized_image always outputs jpg
                        } else { // erase possible previous image or failed conversion
                            $fileData[$bkey][$i] = '';
                            $fileData[$bkey . '_mime'][$i] = '';
                        }
                    }
                }
            }
        } else {
            if (isset($_FILES[$bkey])
                    && is_uploaded_file($_FILES[$bkey]['tmp_name'])
                    && isValidImage($_FILES[$bkey]['type'])) {
                // convert to resized jpg if possible
                $uploaded = $_FILES[$bkey]['tmp_name'];
                $copied = $_FILES[$bkey]['tmp_name'] . '.new';
                $type = $_FILES[$bkey]['type'];

                if (copy_resized_image($uploaded, $type, IMAGESIZE_LARGE, IMAGESIZE_LARGE, $copied)) {
                    $fileData[$bkey] = base64_encode(file_get_contents($copied));
                    $fileData[$bkey . '_mime'] = 'image/jpeg'; // copy_resized_image always outputs jpg
                    // unset old photo because array_merge_recursive below will keep the old one
                    unset($_POST[$bkey]);
                    unset($_POST[$bkey . '_mime']);
                } else { // erase possible previous image or failed conversion
                    $fileData[$bkey] = '';
                    $fileData[$bkey . '_mime'] = '';
                }
            }
        }
    }

    $skeleton = $webDir . '/modules/course_metadata/skeleton.xml';
    $extraData = CourseXMLElement::getAutogenData($course_id);
    $data = array_merge($_POST, $extraData, $fileData);
    // course-based adaptation
    $dnum = Database::get()->querySingle("select count(id) as count from document where course_id = ?d", $course_id)->count;
    $vnum = Database::get()->querySingle("select count(id) as count from video where course_id = ?d", $course_id)->count;
    $vlnum = Database::get()->querySingle("select count(id) as count from videolink where course_id = ?d", $course_id)->count;
    if ($dnum + $vnum + $vlnum < 1) {
        $data['course_confirmVideolectures'] = 'false';
    }

    $xml = simplexml_load_file($skeleton, 'CourseXMLElement');
    $xml->adapt($data);
    $xml->populate($data);

    CourseXMLElement::save($course_id, $course_code, $xml);

    return "<p class='success'>$langModifDone</p>";
//    return "<p>&laquo; <a href='" . $_SERVER['SCRIPT_NAME'] . "?course=$course_code'>$langBack</a></p>
//            <p>&laquo; <a href='{$urlServer}courses/$course_code/index.php'>$langBackCourse</a></p>";
}

function isValidImage($type) {
    $ret = false;
    if ($type == 'image/jpeg') {
        $ret = true;
    } elseif ($type == 'image/png') {
        $ret = true;
    } elseif ($type == 'image/gif') {
        $ret = true;
    } elseif ($type == 'image/bmp') {
        $ret = true;
    }

    return $ret;
}
