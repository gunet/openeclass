<?

/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
// if we come from the home page
if (isset($_GET['from_home']) and ($_GET['from_home'] == TRUE) and isset($_GET['cid'])) {
        session_start();
        $_SESSION['dbname'] = $_GET['cid'];
}
$require_current_course = TRUE;
$require_prof = true;
$require_help = TRUE;
$helpTopic = 'Infocours';
include '../../include/baseTheme.php';

$nameTools = $langModifInfo;
$tool_content = "";

// submit
if (!$is_adminOfCourse) {
	$tool_content .= "<p>$langForbidden</p>";
        draw($tool_content, 2);
        exit;
}

$lang_editor = langname_to_code($language);

if (isset($_POST['submit'])) {
        if (empty($_POST['title'])) {
                $tool_content .= "<p class='caution_small'>$langNoCourseTitle<br />
                                  <a href='$_SERVER[PHP_SELF]'>$langAgain</a></p><br />";
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

                list($facid, $facname) = explode('--', $_POST['facu']);
                db_query("UPDATE `$mysqlMainDb`.cours
                          SET intitule = " . autoquote($_POST['title']) .",
                              faculte = " . autoquote($facname) . ",
                              course_keywords = ".autoquote($_POST['course_keywords']) . ",
                              visible = " . intval($_POST['formvisible']) . ",
                              titulaires = " . autoquote($_POST['titulary']) . ",
                              languageCourse = '$newlang',
                              type = " . autoquote($_POST['type']) . ",
                              password = " . autoquote($_POST['password']) . ",
                              faculteid = " . intval($facid) . "
                          WHERE cours_id = $cours_id");
                db_query("UPDATE `$mysqlMainDb`.cours_faculte
                          SET faculte = " . autoquote($facname) . ",
                              facid = " . intval($facid) . "
                          WHERE code='$currentCourseID'");

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
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langConference' WHERE define_var='MODULE_ID_CHAT'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langCourseDescription' WHERE define_var='MODULE_ID_DESCRIPTION'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langQuestionnaire' WHERE define_var='MODULE_ID_QUESTIONNAIRE'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langLearnPath' WHERE define_var='MODULE_ID_LP'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langUsage' WHERE define_var='MODULE_ID_USAGE'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langToolManagement' WHERE define_var='MODULE_ID_TOOLADMIN'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langWiki' WHERE define_var='MODULE_ID_WIKI'");
                db_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langCourseUnits' WHERE define_var='MODULE_ID_UNITS'");

                $tool_content .= "<p class='success_small'>$langModifDone<br />
                        <a href='".$_SERVER['PHP_SELF']."'>$langBack</a></p><br />
                        <p><a href='{$urlServer}courses/$currentCourseID/index.php'>$langBackCourse</a></p><br />";
        }
} else {

		$tool_content .= "
                <div id='operations_container'>
                  <ul id='opslist'>
		    <li><a href='archive_course.php'>$langBackupCourse</a></li>
  		    <li><a href='delete_course.php'>$langDelCourse</a></li>
    		    <li><a href='refresh_course.php'>$langRefreshCourse</a></li>
                  </ul>
                </div>";

                $sql = "SELECT cours_faculte.faculte, cours.intitule, cours.course_keywords, cours.visible,
                               cours.fake_code, cours.titulaires, cours.languageCourse, cours.departmentUrlName,
                               cours.departmentUrl, cours.type, cours.password, cours.faculteid
                        FROM `$mysqlMainDb`.cours, `$mysqlMainDb`.cours_faculte
                        WHERE cours.code='$currentCourseID' AND cours_faculte.code='$currentCourseID'";
		$result = mysql_query($sql);
		$c = mysql_fetch_array($result);
		$title = q($c['intitule']);
		$facu = $c['faculteid'];
		$type = $c['type'];
		$visible = $c['visible'];
		$visibleChecked[$visible] = " checked='1'";
		$fake_code = q($c['fake_code']);
		$titulary = q($c['titulaires']);
		$languageCourse	= $c['languageCourse'];
		$course_keywords = q($c['course_keywords']);
		$password = q($c['password']);

		@$tool_content .="
		<form method='post' action='$_SERVER[PHP_SELF]'>
                <fieldset>
                <legend>$langCourseIden</legend>
                <table class='tbl'>
                    <tr>
                        <td>$langCode&nbsp;:</td>
                        <td>$fake_code</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>$langCourseTitle&nbsp;:</td>
                        <td><input type='text' name='title' value='$title' size='60' /></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>$langTeachers&nbsp;:</td>
                        <td><input type='text' name='titulary' value='$titulary' size='60' /></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>$langFaculty&nbsp;:</td>
                        <td>
                        <select name='facu'>";
                $resultFac=mysql_query("SELECT id, name FROM `$mysqlMainDb`.faculte ORDER BY number");
                while ($myfac = mysql_fetch_array($resultFac)) {
                        if ($myfac['id'] == $facu) {
                                $selected = ' selected="1"';
                        } else {
                                $selected = '';
                        }
                        $tool_content .= "<option value='$myfac[id]--" .
                                         q($myfac['name']) . "'$selected>" .
                                         q($myfac['name']) . "</option>";
                }
                $tool_content .= "</select>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>$langType&nbsp;:</td>
                        <td>";
                $tool_content .= selection(array('pre' => $langpre, 'post' => $langpost, 'other' => $langother), 'type', $type);
                $tool_content .= "</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>$langCourseKeywords&nbsp;</td>
                        <td><input type='text' name='course_keywords' value='$course_keywords' size='60' /></td>
                        <td>&nbsp;</td>
                    </tr>
                    </table>
                    </fieldset>
    

                    <fieldset>
                    <legend>$langConfidentiality</legend>
                    <table class='tbl'>
                    <tr>
                        <td><img src='../../template/classic/img/OpenCourse.gif' alt='$m[legopen]' title='$m[legopen]' width='16' height='16' />&nbsp;$m[legopen]&nbsp;:</td>
                        <td width='1'><input type='radio' name='formvisible' value='2'".@$visibleChecked[2]." /></td>
                        <td>$langPublic&nbsp;</td>
                    </tr>
                    <tr>
                        <td rowspan='2'><img src='../../template/classic/img/Registration.gif' alt='$m[legrestricted]' title='$m[legrestricted]' width='16' height='16' />&nbsp;$m[legrestricted]&nbsp;:</td>
                        <td><input type='radio' name='formvisible' value='1'".@$visibleChecked[1]." /></td>
                        <td>$langPrivOpen</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;$langOptPassword&nbsp;<input type='text' name='password' value='$password' /></td>
                    </tr>
                    <tr>
                        <td><img src='../../template/classic/img/ClosedCourse.gif' alt='$m[legclosed]' title='$m[legclosed]' width='16' height='16' />&nbsp;$m[legclosed]&nbsp;:</td>
                        <td><input type='radio' name='formvisible' value='0'".@$visibleChecked[0]." /></td>
                        <td>$langPrivate&nbsp;</td>
                    </tr>
                    </table>
                    </fieldset>

                    <fieldset>
                    <legend>$langLanguage</legend>
                    <table class='tbl'>
                    <tr>
                        <td>$langOptions&nbsp;:</td>
                        <td width='1'>";
		$language = $c['languageCourse'];
		$tool_content .= lang_select_options('localize');
		$tool_content .= "
                        </td>
                        <td>$langTipLang</td>
                    </tr>
                    </table>
                    </fieldset>
                    <p><input type='submit' name='submit' value='$langSubmit' /></p>

                    </form>";
}

add_units_navigation(TRUE);
draw($tool_content, 2);
