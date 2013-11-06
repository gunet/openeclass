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
$nameTools = $langCourseMetadataControlPanel;
require_once 'CourseXML.php';

// exit if feature disabled or is not reviewer
if (!get_config('course_metadata') || !$is_opencourses_reviewer) {
    header("Location: {$urlServer}courses/$course_code/index.php");
    exit();
}

$xml = CourseXMLElement::init($course_id, $course_code);
$xmlData = $xml->asFlatArray();
list($visible) = mysql_fetch_row(db_query("SELECT visible FROM course WHERE id = " . intval($course_id)));

$hasOpenAccess = ($visible == 2) ? true : false;
$hasMandatoryMetadata = false;
$hasLicense = false;
$hasTeacherConfirm = (isset($xmlData['course_confirmCurriculum']) && $xmlData['course_confirmCurriculum'] == 'true');
list($numDocs) = mysql_fetch_row(db_query("SELECT count(id) FROM document WHERE course_id = " . intval($course_id)));
list($numUnits) = mysql_fetch_row(db_query("SELECT count(id) FROM course_units WHERE course_id = " . intval($course_id) . " AND `order` >= 1 AND visible = 1"));
list($numAudio) = mysql_fetch_row(db_query(""));
list($numVideo) = mysql_fetch_row(db_query(""));
$hasTeacherConfirmVideo = (isset($xmlData['course_confirmVideolectures']) && $xmlData['course_confirmVideolectures'] == 'true');

$openAccessImg = ($hasOpenAccess) ? 'tick' : 'delete';
$mandatoryMetadataImg = ($hasMandatoryMetadata) ? 'tick' : 'delete';
$licenseImg = ($hasLicense) ? 'tick' : 'delete';
$teacherConfirmImg = ($hasTeacherConfirm) ? 'tick' : 'delete';
$docsImg = ($numDocs > 0) ? 'tick' : 'delete';
$unitsImg = ($numUnits > 0) ? 'tick' : 'delete';
$audioImg = ($numAudio > 0) ? 'tick' : 'delete';
$videoImg = ($numVideo > 0) ? 'tick' : 'delete';
$teacherConfirmVideoImg = ($hasTeacherConfirmVideo) ? 'tick' : 'delete';

$tool_content .= <<<EOF
        <table class="tbl_courseid" width="100%">
	<tbody><tr>
	  <td class="title1" colspan="2">$langOpenCoursesCharacteristics</td>
	</tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesOpenAccess</td> 
          <td align="right"><img src="$themeimg/$openAccessImg.png" alt=""></td>
        </tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesMandatoryMetadata</td>
          <td align="right"><img src="$themeimg/$mandatoryMetadataImg.png" alt=""></td>
        </tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesLicense</td>
          <td align="right"><img src="$themeimg/$licenseImg.png" alt=""></td>
        </tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesTeacherConfirm</td>
          <td align="right"><img src="$themeimg/$teacherConfirmImg.png" alt=""></td>
        </tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesHasDocuments ($numDocs $langDoc)</td>
          <td align="right"><img src="$themeimg/$docsImg.png" alt=""></td>
        </tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesHasUnits ($numUnits $langSections)</td>
          <td align="right"><img src="$themeimg/$unitsImg.png" alt=""></td>
        </tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesHasAudio ($numAudio $langOpenCoursesFiles)</td>
          <td align="right"><img src="$themeimg/$audioImg.png" alt=""></td>
        </tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesHasVideo ($numVideo $langOpenCoursesFiles)</td>
          <td align="right"><img src="$themeimg/$videoImg.png" alt=""></td>
        </tr>
        <tr>
          <td class="smaller"><img src="$themeimg/arrow.png" alt="">&nbsp;$langOpenCoursesTeacherConfirmVideo</td>
          <td align="right"><img src="$themeimg/$teacherConfirmVideoImg.png" alt=""></td>
        </tr>
        </tbody></table>
        <br/><br/>
        <p>&laquo; <a href='{$urlServer}modules/course_info/index.php?course=$course_code'>$langBack</a></p>
        <p>&laquo; <a href='{$urlServer}courses/$course_code/index.php'>$langBackCourse</a></p>
EOF;

draw($tool_content, 2);
