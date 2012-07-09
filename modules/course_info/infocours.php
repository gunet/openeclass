<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

$nameTools = $langModifInfo;

$lang_editor = langname_to_code($language);

if (isset($_POST['submit'])) {
        if (empty($_POST['title'])) {
                $tool_content .= "<p class='caution'>$langNoCourseTitle</p>
                                  <p>&laquo; <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours'>$langAgain</a></p>";
        } else {
                if (isset($_POST['localize'])) {
                        $newlang = $language = langcode_to_name($_POST['localize']);
                        // include_messages
                        include("${webDir}modules/lang/$language/common.inc.php");
                        $extra_messages = "${webDir}/config/$language.inc.php";
                        if (file_exists($extra_messages)) {
                                include $extra_messages;
                        } else {
                                $extra_messages = false;
                        }
                        include("${webDir}modules/lang/$language/messages.inc.php");
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

                $department = intval($_POST['department']);
		
		$facname = find_faculty_by_id($department);
                db_query("UPDATE `$mysqlMainDb`.cours
                          SET intitule = " . quote($_POST['title']) .",
                              fake_code = " . quote($_POST['fcode']) .",
                              course_keywords = ". quote($_POST['course_keywords']) .",
                              visible = " . intval($_POST['formvisible']) .",
                              titulaires = " . quote($_POST['titulary']) .",
                              languageCourse = '$newlang',
                              type = " . quote($_POST['type']) .",
                              password = " . quote($_POST['password']) .",
                              faculteid = $department
                          WHERE cours_id = $cours_id");

                // update Home Page Menu Titles for new language
                mysql_select_db($currentCourseID, $db);
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langAgenda' WHERE define_var='MODULE_ID_AGENDA'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langLinks' WHERE define_var='MODULE_ID_LINKS'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langDoc' WHERE define_var='MODULE_ID_DOCS'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langVideo' WHERE define_var='MODULE_ID_VIDEO'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langWorks' WHERE define_var='MODULE_ID_ASSIGN'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langAnnouncements' WHERE define_var='MODULE_ID_ANNOUNCE'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langAdminUsers' WHERE define_var='MODULE_ID_USERS'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langForums' WHERE define_var='MODULE_ID_FORUM'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langExercices' WHERE define_var='MODULE_ID_EXERCISE'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langModifyInfo' WHERE define_var='MODULE_ID_COURSEINFO'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langGroups' WHERE define_var='MODULE_ID_GROUPS'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langDropBox' WHERE define_var='MODULE_ID_DROPBOX'");
		db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langGlossary' WHERE define_var='MODULE_ID_GLOSSARY'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langConference' WHERE define_var='MODULE_ID_CHAT'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langCourseDescription' WHERE define_var='MODULE_ID_DESCRIPTION'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langQuestionnaire' WHERE define_var='MODULE_ID_QUESTIONNAIRE'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langLearnPath' WHERE define_var='MODULE_ID_LP'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langUsage' WHERE define_var='MODULE_ID_USAGE'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langToolManagement' WHERE define_var='MODULE_ID_TOOLADMIN'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langWiki' WHERE define_var='MODULE_ID_WIKI'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langCourseUnits' WHERE define_var='MODULE_ID_UNITS'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langGlossary' WHERE define_var='MODULE_ID_GLOSSARY'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langEBook' WHERE define_var='MODULE_ID_EBOOK'");

                $tool_content .= "<p class='success'>$langModifDone</p>
                        <p>&laquo; <a href='".$_SERVER['SCRIPT_NAME']."?course=$code_cours'>$langBack</a></p>
                        <p>&laquo; <a href='{$urlServer}courses/$currentCourseID/index.php'>$langBackCourse</a></p>";
        }
} else {
	$tool_content .= "
	<div id='operations_container'>
	  <ul id='opslist'>
	    <li><a href='archive_course.php?course=$code_cours'>$langBackupCourse</a></li>
	    <li><a href='delete_course.php?course=$code_cours'>$langDelCourse</a></li>
	    <li><a href='refresh_course.php?course=$code_cours'>$langRefreshCourse</a></li>
	  </ul>
	</div>";

	$sql = "SELECT cours.intitule, cours.course_keywords, cours.visible,
		       cours.fake_code, cours.titulaires, cours.languageCourse, cours.type,
		       cours.password, cours.faculteid
		FROM `$mysqlMainDb`.cours WHERE cours.code = '$currentCourseID'";
	$result = db_query($sql);
	$c = mysql_fetch_array($result);
	$title = q($c['intitule']);
	$department = $c['faculteid'];
	$type = $c['type'];
	$visible = $c['visible'];
	$visibleChecked[$visible] = " checked";
	$fake_code = q($c['fake_code']);
	$titulary = q($c['titulaires']);
	$languageCourse	= $c['languageCourse'];
	$course_keywords = q($c['course_keywords']);
	$password = q($c['password']);

	$tool_content .="
	<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours'>
	<fieldset>
	<legend>$langCourseIden</legend>
	<table class='tbl' width='100%'>
	    <tr>
		<th width='170'>$langCode:</th>
		<td><input type='text' name='fcode' value='$fake_code' size='60' /></td>
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
	$tool_content .= list_departments($department);
	$tool_content .= "
                </td>
            </tr>
	    <tr>
	        <th>$langType:</th>
	        <td>";
	$tool_content .= selection(array('pre' => $langpre, 'post' => $langpost, 'other' => $langother), 'type', $type);
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
	$language = $c['languageCourse'];
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
draw($tool_content, 2);
