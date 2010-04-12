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
if (isset($from_home) and ($from_home == TRUE) and isset($_GET['cid'])) {
        session_start();
        $_SESSION['dbname'] = $cid;
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
        draw($tool_content, 2, 'course_info');
        exit;
}

$lang_editor = langname_to_code($language);

$head_content = <<<hContent
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config2.js"></script>
hContent;

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
                if (isset($_POST['checkpassword']) and
                    isset($_POST['formvisible']) and
                    $_POST['formvisible'] == '1') {
                        $password = $password;
                } else {
                        $password = "";
                }

                list($facid, $facname) = explode('--', $_POST['facu']);
                db_query("UPDATE `$mysqlMainDb`.cours
                          SET intitule = " . autoquote($_POST['title']) .",
                              faculte = " . autoquote($facname) . ",
                              description = " . autoquote($_POST['description']) . ",
                              course_addon = " . autoquote($_POST['course_addon']) . ",
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

		$tool_content .= "<div id='operations_container'><ul id='opslist'>";
		$tool_content .= "<li><a href='archive_course.php'>$langBackupCourse</a></li>
  		<li><a href='delete_course.php'>$langDelCourse</a></li>
    		<li><a href='refresh_course.php'>$langRefreshCourse</a></li></ul></div>";

		$sql = "SELECT cours_faculte.faculte,
			cours.intitule, cours.description, cours.course_keywords, cours.course_addon,
			cours.visible, cours.fake_code, cours.titulaires, cours.languageCourse,
			cours.departmentUrlName, cours.departmentUrl, cours.type, cours.password, cours.faculteid
			FROM `$mysqlMainDb`.cours, `$mysqlMainDb`.cours_faculte
			WHERE cours.code='$currentCourseID'
			AND cours_faculte.code='$currentCourseID'";
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
		$description = q($c['description']);
		$course_keywords = q($c['course_keywords']);
		$course_addon = q($c['course_addon']);
		$password = q($c['password']);
		$checkpasssel = empty($password)? '': " checked='1'";

		@$tool_content .="
		<form method='post' action='$_SERVER[PHP_SELF]'>
		<table width='99%' align='left'>
		<thead><tr>
		<td>
		<table width='100%' class='FormData' align='left'>
		<tbody>
		<tr>
			<th class='left' width='150'>&nbsp;</th>
			<td><b>$langCourseIden</b></td>
			<td>&nbsp;</td>
			</tr>
		<tr>
			<th class='left'>$langCode&nbsp;:</th>
			<td>$fake_code</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th class='left'>$langCourseTitle&nbsp;:</th>
			<td><input type='text' name='title' value='$title' size='60' class='FormData_InputText' /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th class='left'>$langTeachers&nbsp;:</th>
			<td><input type='text' name='titulary' value='$titulary' size='60' class='FormData_InputText' /></td>
		<td>&nbsp;</td>
		</tr>
			<tr><th class='left'>$langFaculty&nbsp;:</th>
			<td>
		<select name='facu' class='auth_input'>";
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
                $tool_content .= "</select></td><td>&nbsp;</td></tr>
		<tr>
		<th class='left'>$langType&nbsp;:</th>
		<td>";

                $tool_content .= selection(array('pre' => $langpre, 'post' => $langpost, 'other' => $langother), 'type', $type);
                $tool_content .= "</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th class='left'>$langDescription&nbsp;:</th>
        <td width='100'>
	      <table class='xinha_editor'>
          <tr>
             <td><textarea id='xinha' name='description' cols='20' rows='4' class='FormData_InputText'>$description</textarea></td>
          </tr>
          </table>
        </td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th class='left'>$langCourseKeywords&nbsp;</th>
        <td><input type='text' name='course_keywords' value='$course_keywords' size='60' class='FormData_InputText' /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th class='left'>$langCourseAddon&nbsp;</th>
        <td width='100'>
	      <table class='xinha_editor'>
          <tr>
        <td><textarea id='xinha2' name='course_addon' cols='20' rows='4' class='FormData_InputText'>$course_addon</textarea></td>
        </tr>
          </table>
          </td>
          <td>&nbsp;</td>
      </tr>
      </tbody>
      </table>
      <p>&nbsp;</p>
      <table width='100%' class='FormData' align='left'>
      <tbody>
      <tr>
        <th class='left' width='150'>&nbsp;</th>
        <td colspan='2'><b>$langConfidentiality</b></td>
      </tr>
      <tr>
        <th class='left'><img src='../../template/classic/img/OpenCourse.gif' alt='$m[legopen]' title='$m[legopen]' width='16' height='16' />&nbsp;$m[legopen]&nbsp;:</th>
        <td width='1'><input type='radio' name='formvisible' value='2'".@$visibleChecked[2]." /></td>
        <td>$langPublic&nbsp;</td>
      <tr>
        <th rowspan='2' class='left'><img src='../../template/classic/img/Registration.gif' alt='$m[legrestricted]' title='$m[legrestricted]' width='16' height='16' />&nbsp;$m[legrestricted]&nbsp;:</th>
        <td><input type='radio' name='formvisible' value='1'".@$visibleChecked[1]." /></td>
        <td>$langPrivOpen</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td bgcolor='#F8F8F8'><input type='checkbox' name='checkpassword'$checkpasssel />&nbsp;$langOptPassword&nbsp;<input type='text' name='password' value='$password' class='FormData_InputText' />
        </td>
      </tr>
      <tr>
        <th class='left'><img src='../../template/classic/img/ClosedCourse.gif' alt='$m[legclosed]' title='$m[legclosed]' width='16' height='16' />&nbsp;$m[legclosed]&nbsp;:</th>
        <td><input type='radio' name='formvisible' value='0'".@$visibleChecked[0]." /></td>
        <td>$langPrivate&nbsp;</td>
      </tr>
      </tbody>
      </table>
      <p>&nbsp;</p>
      <table width='100%' class='FormData' align='left'>
      <tbody>
      <tr>
        <th class='left' width='150'>&nbsp;</th>
        <td colspan='2'><b>$langLanguage</b></td>
      </tr>
      <tr>
        <th class='left'>$langOptions&nbsp;:</th>
        <td width='1'>";
		$language = $c['languageCourse'];
		$tool_content .= lang_select_options('localize');
		$tool_content .= "
        </td>
        <td><small>$langTipLang</small></td>
      </tr>
      <tr>
        <th class='left' width='150'>&nbsp;</th>
        <td><input type='submit' name='submit' value='$langSubmit' /></td>
        <td>&nbsp;</td>
      </tr>
      </tbody>
      </table>
    </td>
  </tr>
  </thead>
  </table>
</form>";
}

add_units_navigation(TRUE);
draw($tool_content, 2, 'course_info', $head_content);
