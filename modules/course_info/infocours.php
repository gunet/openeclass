<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

// if we come from the home page
if (isset($_GET['from_home']) and ($_GET['from_home'] == TRUE) and isset($_GET['cid'])) {
        session_start();
        $_SESSION['dbname'] = $_GET['cid'];
}
$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_help = TRUE;
$helpTopic = 'Infocours';
include '../../include/baseTheme.php';

include '../../include/lib/user.class.php';
include '../../include/lib/course.class.php';
include '../../include/lib/hierarchy.class.php';

$user = new user();
$course = new course();
$tree = new hierarchy();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');

$nameTools = $langModifInfo;

$lang_editor = langname_to_code($language);

if (isset($_POST['submit'])) {
        if (empty($_POST['title'])) {
                $tool_content .= "<p class='caution'>$langNoCourseTitle</p>
                                  <p>&laquo; <a href='$_SERVER[PHP_SELF]?course=$course_code'>$langAgain</a></p>";
        } else {
                if (isset($_POST['localize'])) {
                        $newlang = $language = $_POST['localize'];
                        // include_messages
                        include "$webDir/lang/$language/common.inc.php";
                        $extra_messages = "$webDir/config/$language.inc.php";
                        if (file_exists($extra_messages)) {
                                include $extra_messages;
                        } else {
                                $extra_messages = false;
                        }
                        include "$webDir/lang/$language/messages.inc.php");
                        if ($extra_messages) {
                                include $extra_messages;
                        }
                }
                // update course settings
                if (isset($_POST['formvisible']) and
                    $_POST['formvisible'] == '1') {
                        $password = $_POST['password'];
                } else {
                        $password = "";
                }

                $departments = isset($_POST['department']) ? $_POST['department'] : array();
                $deps_valid = true;

                foreach ($departments as $dep) {
                    if ( get_config('restrict_teacher_owndep') && !$is_admin && !in_array($dep, $user->getDepartmentIds($uid)) )
                        $deps_valid = false;
                }

                // Check if the teacher is allowed to create in the departments he chose
                if (!$deps_valid) {
                    $tool_content .= "<p class='caution'>$langCreateCourseNotAllowedNode</p>
                                      <p>&laquo; <a href='$_SERVER[PHP_SELF]?course=$course_code'>$langAgain</a></p>";
                } else {
                    
                    db_query("UPDATE course
                            SET title = " . autoquote($_POST['title']) .",
                                public_code = " . autoquote($_POST['fcode']) .",
                                keywords = ".autoquote($_POST['course_keywords']) . ",
                                visible = " . intval($_POST['formvisible']) . ",
                                prof_names = " . autoquote($_POST['titulary']) . ",
                                lang = '$newlang',
                                password = " . autoquote($_POST['password']) . "
                            WHERE id = $course_id");
                    $course->refresh($course_id, $departments);

                    $tool_content .= "<p class='success'>$langModifDone</p>
                            <p>&laquo; <a href='".$_SERVER['PHP_SELF']."?course=$course_code'>$langBack</a></p>
                            <p>&laquo; <a href='{$urlServer}courses/$course_code/index.php'>$langBackCourse</a></p>";
                }
        }
} else {
	$tool_content .= "
	<div id='operations_container'>
	  <ul id='opslist'>
	    <li><a href='archive_course.php?course=$course_code'>$langBackupCourse</a></li>
	    <li><a href='delete_course.php?course=$course_code'>$langDelCourse</a></li>
	    <li><a href='refresh_course.php?course=$course_code'>$langRefreshCourse</a></li>
	  </ul>
	</div>";

	$sql = "SELECT course.title, course.keywords, course.visible,
		       course.public_code, course.prof_names, course.lang,
		       course.password, course.id
		  FROM course
                 WHERE course.code = '$course_code'";
	$result = db_query($sql);
	$c = mysql_fetch_array($result);
	$title = q($c['title']);
	$visible = $c['visible'];
	$visibleChecked[$visible] = " checked='1'";
	$public_code = q($c['public_code']);
	$titulary = q($c['prof_names']);
	$languageCourse	= $c['lang'];
	$course_keywords = q($c['keywords']);
	$password = q($c['password']);

	$tool_content .="
	<form method='post' action='$_SERVER[PHP_SELF]?course=$course_code' onsubmit='return validateNodePickerForm();'>
	<fieldset>
	<legend>$langCourseIden</legend>
	<table class='tbl' width='100%'>
	    <tr>
		<th width='170'>$langCode:</th>
		<td><input type='text' name='fcode' value='$public_code' size='60' /></td>
	    </tr>
	    <tr>
		<th>$langCourseTitle:</th>
		<td><input type='text' name='title' value='$title' size='60' /></td>
	    </tr>
	    <tr>
		<th>$langTeachers:</th>
		<td><input type='text' name='titulary' value='$titulary' size='60' /></td>
	    </tr>
	    <tr>
                <th>$langFaculty:</th>
                <td>";
        $allow_only_defaults = ( get_config('restrict_teacher_owndep') && !$is_admin ) ? true : false;
        list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($c['id']), 'allow_only_defaults' => $allow_only_defaults));
        $head_content .= $js;
        $tool_content .= $html;
	$tool_content .= "
                </td>
            </tr>
	    <tr>
		<th>$langCourseKeywords</th>
		<td><input type='text' name='course_keywords' value='$course_keywords' size='60' /></td>
	    </tr>
	    </table>
	</fieldset>

	<fieldset>
	<legend>$langConfidentiality</legend>
	    <table class='tbl' width='100%'>
	    <tr>
		<th width='170'><img src='$themeimg/lock_open.png' alt='$m[legopen]' title='$m[legopen]' width='16' height='16' />&nbsp;$m[legopen]:</th>
		<td width='1'><input type='radio' name='formvisible' value='2'".@$visibleChecked[2]." /></td>
		<td class='smaller'>$langPublic</td>
	    </tr>
	    <tr>
		<th rowspan='2' valign='top'><img src='$themeimg/lock_registration.png' alt='$m[legrestricted]' title='$m[legrestricted]' width='16' height='16' />&nbsp;$m[legrestricted]:</th>
		<td><input type='radio' name='formvisible' value='1'".@$visibleChecked[1]." /></td>
		<td class='smaller'>$langPrivOpen</td>
	    </tr>
	    <tr>
		<td>&nbsp;</td>
		<td class='smaller'><i>$langOptPassword</i>&nbsp;<input type='text' name='password' value='$password' /></td>
	    </tr>
	    <tr>
		<th><img src='$themeimg/lock_closed.png' alt='$m[legclosed]' title='$m[legclosed]' width='16' height='16' />&nbsp;$m[legclosed]:</th>
		<td><input type='radio' name='formvisible' value='0'".@$visibleChecked[0]." /></td>
		<td class='smaller'>$langPrivate</td>
	    </tr>
             <tr>
		<th><img src='$themeimg/lock_inactive.png' alt='$m[linactive]' title='$m[linactive]' width='16' height='16' />&nbsp;$m[linactive]:</th>
		<td><input type='radio' name='formvisible' value='3'".@$visibleChecked[3]." /></td>
		<td class='smaller'>$langCourseInactive</td>
	    </tr>
	    </table>
	</fieldset>

	<fieldset>
	    <legend>$langLanguage</legend>
	    <table class='tbl'>
	    <tr>
		<th width='170'>$langOptions:</th>
		<td width='1'>";
                $language = $c['lang'];
                $tool_content .= lang_select_options('localize');
                $tool_content .= "
	        </td>
	        <td class='smaller'>$langTipLang</td>
	    </tr>
	</table>
	</fieldset>
	<p class='right'><input type='submit' name='submit' value='$langSubmit' /></p>
	</form>";
}
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
