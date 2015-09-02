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
$pageName = $langCourseMetadata;
require_once 'modules/course_metadata/CourseXML.php';

// exit if feature disabled
if (!get_config('course_metadata')) {
    header("Location: {$urlServer}courses/$course_code/index.php");
    exit();
}

if (isset($_POST['submit'])) {
    $tool_content .= submitForm();
}

// display Form
list($displayHtml, $xml) = displayForm();
$tool_content .= $displayHtml;

$naturalKeys = array('othernatural', 'maths', 'cis', 'phys', 'chem', 'environ', 'biology');
$naturalJSON = generateJSON($naturalKeys);
$agriKeys = array('otheragri', 'agrifor', 'animal', 'agribio', 'rural');
$agriJSON = generateJSON($agriKeys);
$engKeys = array('othereng', 'civil', 'eeeeie', 'mcec', 'mechan', 'chemic', 'mateng', 'medeng', 'enveng', 'architects', 'engecon');
$engJSON = generateJSON($engKeys);
$socKeys = array('othersoc', 'psych', 'edusoc', 'sociology', 'law', 'political', 'ecobi', 'ecogeosoc', 'mediacomm', 'infoscie', 'anthro');
$socJSON = generateJSON($socKeys);
$medKeys = array('othermed', 'basicmed', 'clinicalmed', 'healthsci', 'veterin', 'hcare', 'medbio', 'physedu', 'fnscie');
$medJSON = generateJSON($medKeys);
$humKeys = array('otherhum', 'hisarch', 'langlit', 'philosophy', 'arts', 'pedagogy');
$humJSON = generateJSON($humKeys);
$indepKeys = array('otherinddep', 'milit');
$indepJSON = generateJSON($indepKeys);

$instrFirst = $langCMeta['course_instructor_firstName'];
$instrLast = $langCMeta['course_instructor_lastName'];
$greek = $langCMeta['el'];
$english = $langCMeta['en'];
$instrPhoto = $langCMeta['course_instructor_photo'];
$instrCode = $langCMeta['course_instructor_registrationCode'];

load_js('select2');
$head_content .= <<<EOF
<link rel="stylesheet" type="text/css" href="{$urlAppend}modules/course_metadata/course_metadata.css">
<script type='text/javascript'>
/* <![CDATA[ */
        
    var subThematics = {
        "othersubj" : [{"val" : "othersubsubj", "name" : "{$langCMeta['othersubsubj']}"}],
        "natural" : {$naturalJSON},
        "agricultural" : {$agriJSON},
        "engineering" : {$engJSON},
        "social" : {$socJSON},
        "medical" : {$medJSON},
        "humanities" : {$humJSON},
        "independents" : {$indepJSON}
    };
        
    var populateSubThematic = function(key) {
        var subthem = $( "#course_subthematic" );
        subthem.empty();
        $.each(subThematics[key], function() {
            subthem.append( $( "<option />" ).val(this.val).text(this.name) );
        });
    };
        
    var photoDelete = function(id) {
        $( id + "_image" ).remove();
        $( id + "_hidden" ).remove();
        $( id + "_hidden_mime" ).remove();
        $( id + "_delete" ).remove();
    };

    $(document).ready(function(){
        $( ".cmetarow" ).tooltip({
            html: true
        });
        
        $( "#multiselect" ).select2({width: '476'});
        
        $( "#course_coursePhoto_delete" ).on('click', function() {
            $( "#course_coursePhoto_image" ).remove();
            $( "#course_coursePhoto_hidden" ).remove();
            $( "#course_coursePhoto_hidden_mime" ).remove();
        });
        
        $( ".course_instructor_photo_delete" ).on('click', function() {
            $(this).parent().children( ".course_instructor_photo_image" ).remove();
            $(this).parent().children( ".course_instructor_photo_hidden" ).val('');
            $(this).parent().children( ".course_instructor_photo_hidden_mime" ).val('');
            $(this).parent().children( ".course_instructor_photo_delete" ).remove();
        });
        
        $( ".instructor_add" ).on('click', function() {
            $(this).parent().parent().children( ".instructor_container" ).append(
                '<div class="cmetarow">' +
                    '<span class="cmetalabel">{$instrFirst} ({$greek}):</span>' +
                    '<span class="cmetafield"><input size="55" name="course_instructor_firstName_el[]" type="text"></span>' +
                    '<span class="cmetamandatory">*</span>' +
                '</div>' +
                '<div class="cmetarow">' +
                    '<span class="cmetalabel">{$instrLast} ({$greek}):</span>' +
                    '<span class="cmetafield"><input size="55" name="course_instructor_lastName_el[]" type="text"></span>' +
                    '<span class="cmetamandatory">*</span>' +
                '</div>' +
                '<div class="cmetarow">' +
                    '<span class="cmetalabel">{$instrFirst} ({$english}):</span>' +
                    '<span class="cmetafield"><input size="55" name="course_instructor_firstName_en[]" type="text"></span>' +
                    '<span class="cmetamandatory">*</span>' +
                '</div>' +
                '<div class="cmetarow">' +
                    '<span class="cmetalabel">{$instrLast} ({$english}):</span>' +
                    '<span class="cmetafield"><input size="55" name="course_instructor_lastName_en[]" type="text"></span>' +
                    '<span class="cmetamandatory">*</span>' +
                '</div>' +
                '<div class="cmetarow">' +
                    '<span class="cmetalabel">{$instrPhoto}:</span>' +
                    '<span class="cmetafield">' +
                        '<input class="course_instructor_photo_hidden" type="hidden" name="course_instructor_photo[]">' +
                        '<input class="course_instructor_photo_hidden_mime" type="hidden" name="course_instructor_photo_mime[]">' +
                        '<input size="30" name="course_instructor_photo[]" type="file">' +
                    '</span>' +
                '</div>' +
                '<div class="cmetarow">' +
                    '<span class="cmetalabel">{$instrCode}:</span>' +
                    '<span class="cmetafield"><input size="55" name="course_instructor_registrationCode[]" type="text"></span>' +
                '</div>'
                    );
        });
        
        $( "#course_thematic" ).on('change', function() {
            populateSubThematic( $( "#course_thematic" ).val() );
        });
        
        populateSubThematic( $( "#course_thematic" ).val() );
        $( "#course_subthematic" ).val('{$xml->subthematic}');
    });

/* ]]> */
</script>
EOF;
draw($tool_content, 2, null, $head_content);

//--- HELPER FUNCTIONS ---//

function displayForm() {
    global $course_id, $course_code;
    $xml = CourseXMLElement::init($course_id, $course_code);
    return array($xml->asForm(), $xml);
}

function submitForm() {
    global $course_id, $course_code, $webDir, $langModifDone;

    // handle uploaded files
    $fileData = array();
    foreach (CourseXMLConfig::$binaryFields as $bkey) {
        if (in_array($bkey, CourseXMLConfig::$multipleFields) || in_array($bkey, CourseXMLConfig::$arrayFields)) {
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
                    } else {
                        // add to array as empty, in order to keep correspondence
                        $fileData[$bkey][$i] = '';
                        $fileData[$bkey . '_mime'][$i] = '';
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
    // manually merge instructor photo, to achieve multiplicity sync
    foreach ($fileData['course_instructor_photo'] as $key => $value) {
        if (!empty($value)) {
            $_POST['course_instructor_photo'][$key] = $value;
        }
    }
    unset($fileData['course_instructor_photo']);
    foreach ($fileData['course_instructor_photo_mime'] as $key => $value) {
        if (!empty($value)) {
            $_POST['course_instructor_photo_mime'][$key] = $value;
        }
    }
    unset($fileData['course_instructor_photo_mime']);
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

    return "<div class='alert alert-success'>$langModifDone</div>";
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

function generateJSON($keys) {
    $json = "[";
    foreach($keys as $key) {
        $json .= "{\"val\" : \"" . $key . "\", \"name\" : \"" . $GLOBALS['langCMeta'][$key] . "\"}, ";
    }
    $json .= "]";
    return $json;
}
