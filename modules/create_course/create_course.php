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
session_start();

$require_login = TRUE;
$require_prof = TRUE;
$require_help = TRUE;
$helpTopic = 'CreateCourse';

include '../../include/baseTheme.php';
require_once("../betacms_bridge/include/bcms.inc.php");

$nameTools = $langCreateCourse . " (" . $langCreateCourseStep ." 1 " .$langCreateCourseStep2 . " 3)" ;
$tool_content = $head_content = "";
$lang_editor = langname_to_code($language);

$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry, entry2) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if ((tempobj.name == entry) || (tempobj.name == entry2)) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyFields");
		return false;
	} else {
		return true;
	}
}

</script>
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>
hContent;

$titulaire_probable="$prenom $nom";

$tool_content .= "<form method='post' name='createform' action='$_SERVER[PHP_SELF]' onsubmit=\"return checkrequired(this, 'intitule', 'titulaires');\">";

// Import from BetaCMS Bridge
doImportFromBetaCMSBeforeCourseCreation();

function escape_if_exists($name) {
        if (isset($_POST[$name])) {
                if (get_magic_quotes_gpc()) {
                		$tmp = stripslashes($_POST[$name]);
                } else {
                        $tmp = $_POST[$name];
                }
                $GLOBALS[$name] = $tmp;
                $GLOBALS[$name . '_html'] = '<input type="hidden" name="' . $name .
                       '" value="' . htmlspecialchars($tmp) . '" />';
        } else {
                $GLOBALS[$name . '_html'] = $GLOBALS[$name] = '';
        }
}

escape_if_exists('intitule');
escape_if_exists('faculte');
escape_if_exists('titulaires');
escape_if_exists('type');
escape_if_exists('languageCourse');
escape_if_exists('description');
escape_if_exists('course_addon');
escape_if_exists('course_keywords');
escape_if_exists('visit');

$tool_content .= $intitule_html .
                 $faculte_html .
                 $titulaires_html .
                 $type_html .
                 $languageCourse_html .
                 $description_html .
                 $course_addon_html .
                 $course_keywords_html .
                 $visit_html;

if (isset($_POST['back1']) or !isset($_POST['visit'])) {

   // display form
	$tool_content .= "<table width=\"99%\" align='left' class='FormData'>
	<tbody>
	<tr>
	<th width=\"220\">&nbsp;</th>
	<td><b>$langCreateCourseStep1Title</b></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<th class='left' width=\"160\">$langTitle&nbsp;:</th>
	<td width=\"160\"><input class='FormData_InputText' type='text' name='intitule' size='60' value='".@$intitule."' /></td>
	<td><small>$langEx</small></td>
	</tr>
	<tr>
	<th class='left'>$langFaculty&nbsp;:</th>
	<td>";
	list($homefac) = mysql_fetch_row(db_query("SELECT department FROM user WHERE user_id=$uid"));
	$facs = db_query("SELECT id, name FROM faculte order by id");
	while ($n = mysql_fetch_array($facs)) {
		$fac[$n['id']] = $n['name'];
	}
	$tool_content .= selection($fac, 'faculte', $homefac);
	$tool_content .= "</td><td>&nbsp;</td></tr>";
	unset($repertoire);
	$tool_content .= "<tr>
	<th class='left'>$langTeachers&nbsp;:</th>
	<td><input class='FormData_InputText' type='text' name='titulaires' size='60' value='".$titulaire_probable."' /></td>
	<td>&nbsp;</td></tr>
	<tr>
	<th class='left'>$langType&nbsp;:</th>
	<td>";
	$tool_content .= " ".selection(array('pre' => $langpre, 'post' => $langpost, 'other' => $langother), 'type', $type)." ";
	$tool_content .= "
	</td>
	<td>&nbsp;</td></tr>
	<tr>
	<th class='left'>$langLanguage&nbsp;:</th>
	<td>";
	$tool_content .= lang_select_options('languageCourse');
	$tool_content .= "</td><td>&nbsp;</td></tr>
	<tr><th>&nbsp;</th>
	<td><input type='submit' name='create2' value='$langNextStep >' /><input type='hidden' name='visit' value='true' /></td>
	<td><p align='right'><small>(*) &nbsp;$langFieldsRequ</small></p></td>
</tbody>
</table><br />";
}

// --------------------------------
// step 2 of creation
// --------------------------------

 elseif (isset($_POST['create2']) or isset($_POST['back2']))  {
	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 2 " .$langCreateCourseStep2 . " 3 )";
	$tool_content .= "<table width=\"99%\" align='left' class='FormData'>
	<tbody>
	<tr>
	<th width=\"220\">&nbsp;</th>
	<td><b>$langCreateCourseStep2Title</b></td>
	</tr>
	<tr>
	<th class='left'>$langDescrInfo&nbsp;:</th>
	<td>
	<table class='xinha_editor'>
	<tr>
	<td><textarea id='xinha' name='description' wrap=\"soft\">$description</textarea></td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<th class='left'>$langCourseKeywords&nbsp;</th>
	<td><textarea name='course_keywords' cols='85' rows='3' class='FormData_InputText'>$course_keywords</textarea></td>
	</tr>
	<tr>
	<th class='left' width=\"160\">$langCourseAddon&nbsp;</th>
	<td><textarea name='course_addon' cols='85' rows='5' class='FormData_InputText'>$course_addon</textarea></td>
	</tr>
	<tr>
	<th>&nbsp;</th>
	<td><input type='submit' name='back1' value='< $langPreviousStep ' />&nbsp;<input type='submit' name='create3' value='$langNextStep >' /></td>
	</tbody>
	</table>
	<p align='right'><small>$langFieldsOptionalNote</small></p>
	<br />";

}  elseif (isset($_POST['create3']) or isset($_POST['back2'])) {
	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 3 " .$langCreateCourseStep2 . " 3 )" ;
	@$tool_content .= "
	<table width=\"99%\" align='left' class='FormData'>
	<tbody>
	<tr>
	<th width=\"220\">&nbsp;</th>
	<td colspan='2'><b>$langCreateCourseStep3Title</b></td>
	</tr>
	<tr>
	<th class='left' width=\"160\" rowspan='2'>$langAccess<br /></th>
	<td colspan='2'><br />$langAvailableTypes</td>
	</tr>
	<tr>
	<td colspan='2'>
	<table>
	<tr>
	<td width='30'><img src=\"../../template/classic/img/OpenCourse.gif\" title=\"".$m['legopen']."\" width=\"16\" height=\"16\"></td>
	<td width='200'>".$m['legopen']."</td>
	<td width='5' ><input name=\"formvisible\" type=\"radio\" value=\"2\" checked=\"checked\" /></td>
	<td width='325'><p align='right'><small>$langPublic</small></p></td>
	</tr>
	<tr>
	<td width='30'><img src=\"../../template/classic/img/Registration.gif\" title=\"".$m['legrestricted']."\" width=\"16\" height=\"16\"></td>
	<td width='200'>".$m['legrestricted']."</td>
	<td width='5'><input name=\"formvisible\" type=\"radio\" value=\"1\" /></td>
	<td width='325'><p align='right'><small>$langPrivOpen</small></p></td></tr>
	<tr>
	<td colspan='4' class='right'><input type='checkbox' name='checkpassword' ".$checkpasssel.">&nbsp;$langOptPassword
	<input type='text' name='password' value='".q($password)."' class='FormData_InputText'>
	</td>
	</tr>
	<tr>
	<td width='30'><img src=\"../../template/classic/img/ClosedCourse.gif\" title=\"".$m['legclosed']."\" width=\"16\" height=\"16\"></td>
	<td width='200'>".$m['legclosed']."</td>
	<td width='5'><input name=\"formvisible\" type=\"radio\" value=\"0\" /></td>
	<td width='325'><p align='right'><small>$langPrivate</small></p></td>
	</tr>
	</table>
	<br />
	</td>
	</tr>
	<tr>
	<th class='left' rowspan='2'>$langModules</th>
	<td colspan='2'><br />$langSubsystems</td>
	</tr>
	<td colspan='2'>
	<table>
	<tr>
	<td width='30' ><img src=\"../../template/classic/img/calendar_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td width='200'>$langAgenda</td>
	<td width='30' ><input name=\"subsystems[]\" type=\"checkbox\" value=\"1\" checked=\"checked\" /></td>
	<th width='2' >&nbsp;</th>
	<td width='30' >&nbsp;<img src=\"../../template/classic/img/dropbox_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td width='200'>$langDropBox</td>
	<td width='30' ><input type=\"checkbox\" name=\"subsystems[]\" value=\"15\" /></td>
	</tr>
	<tr>
	<td><img src=\"../../template/classic/img/links_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langLinks</td>
	<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"2\" checked=\"checked\" /></td>
	<th>&nbsp;</th>
	<td>&nbsp;<img src=\"../../template/classic/img/groups_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langGroups</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"16\" /></td>
	</tr>
	<tr>
	<td><img src=\"../../template/classic/img/docs_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langDoc</td>
	<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"3\" checked=\"checked\" /></td>
	<th>&nbsp;</th>
	<td>&nbsp;<img src=\"../../template/classic/img/chat_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langConference</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"19\" /></td>
	</tr>
	<tr>
	<td><img src=\"../../template/classic/img/video_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langVideo</td>
	<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"4\"  /></td>
	<th>&nbsp;</th>
	<td>&nbsp;<img src=\"../../template/classic/img/description_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langCourseDescription</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"20\" checked=\"checked\" /></td>
	</tr>
	<tr>
	<td><img src=\"../../template/classic/img/assignments_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langWorks</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"5\" /></td>
	<th>&nbsp;</th>
	<td>&nbsp;<img src=\"../../template/classic/img/questionnaire_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langQuestionnaire</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"21\" /></td>
	</tr>
	<tr>
	<td><img src=\"../../template/classic/img/announcements_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langAnnouncements</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"7\" checked=\"checked\"/></td>
	<th>&nbsp;</th>
	<td>&nbsp;<img src=\"../../template/classic/img/lp_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langLearnPath</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\"  value=\"23\" /></td>
	</tr>
	<tr>
	<td><img src=\"../../template/classic/img/forum_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langForums</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"9\" /></td>
	<th>&nbsp;</th>
	<td>&nbsp;<img src=\"../../template/classic/img/wiki_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langWiki</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"26\" /></td>
	</tr>
	<tr>
	<td><img src=\"../../template/classic/img/exercise_on.gif\" alt=\"\" height=\"16\" width=\"16\"></td>
	<td>$langExercices</td>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"10\" /></td>
	<th>&nbsp;</th>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	</tr>
	</table><br />
	</td>
	</tr>
	<tr>
	<th>&nbsp;</th>
	<td width='400'><input type='submit' name='back2' value='< $langPreviousStep '>&nbsp;
	<input type='submit' name='create_course' value=\"$langFinalize\"></td>
	<td><p align='right'><small>$langFieldsOptionalNote</small></p></td>
	</tr>
	</tbody>
	</table><br />";
} // end of create3

// create the course and the course database
if (isset($_POST['create_course'])) {

        $nameTools = $langCourseCreate;
        $facid = intval($faculte);
        $facname = find_faculty_by_id($facid);

        // create new course code: uppercase, no spaces allowed
        $repertoire = strtoupper(new_code($facid));
        $repertoire = str_replace (' ', '', $repertoire);

        $language = langcode_to_name($_POST['languageCourse']);
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

        // create directories
        umask(0);
        if (! (mkdir("../../courses/$repertoire", 0777) and
                                mkdir("../../courses/$repertoire/image", 0777) and
                                mkdir("../../courses/$repertoire/document", 0777) and
                                mkdir("../../courses/$repertoire/dropbox", 0777) and
                                mkdir("../../courses/$repertoire/page", 0777) and
                                mkdir("../../courses/$repertoire/work", 0777) and
                                mkdir("../../courses/$repertoire/group", 0777) and
                                mkdir("../../courses/$repertoire/temp", 0777) and
                                mkdir("../../courses/$repertoire/scormPackages", 0777) and
                                mkdir("../../video/$repertoire", 0777))) {
                $tool_content .= "<div class='caution'>$langErrorDir</div>";
                draw($tool_content, '1', 'create_course', $head_content);
                exit;
        }
        // ---------------------------------------------------------
        //  all the course db queries are inside the following script
        // ---------------------------------------------------------
        require "create_course_db.php";

        // ------------- update main Db------------
        mysql_select_db("$mysqlMainDb");

        db_query("INSERT INTO cours SET
                        code = '$code',
                        languageCourse =" . quote($language) . ",
                        intitule = " . quote($intitule) . ",
                        description = " . quote($description) . ",
                        course_addon = " . quote($course_addon) . ",
                        course_keywords = " . quote($course_keywords) . ",
                        faculte = " . quote($facname) . ",
                        visible = " . quote($formvisible) . ",
                        titulaires = " . quote($titulaires) . ",
                        fake_code = " . quote($code) . ",
                        type = " . quote($type) . ",
                        faculteid = '$facid',
                        first_create = NOW()");
        $new_cours_id = mysql_insert_id();
        mysql_query("INSERT INTO cours_user SET
                        cours_id = $new_cours_id,
                        user_id = '$uid',
                        statut = '1',
                        tutor='1',
                        reg_date = CURDATE()");

        mysql_query("INSERT INTO cours_faculte SET
                        faculte = '$faculte',
                        code = '$repertoire',
                        facid = '$facid'");

        $titou='$dbname';

        // ----------- main course index.php -----------

        $fd=fopen("../../courses/$repertoire/index.php", "w");
        $string="<?php
                session_start();
        $titou=\"$repertoire\";
        session_register(\"dbname\");
        include(\"../../modules/course_home/course_home.php\");
        ?>";

        fwrite($fd, "$string");
        $status[$repertoire] = 1;
        $_SESSION['status'] = $status;

        // ----------- Import from BetaCMS Bridge -----------
        $tool_content .= doImportFromBetaCMSAfterCourseCreation($repertoire, $mysqlMainDb, $webDir);
        // --------------------------------------------------
        $tool_content .= "
                <p class=\"success_small\">$langJustCreated: &nbsp;<b>$intitule</b></p>
                <p><small>$langEnterMetadata</small></p><br />
                <p align='center'>&nbsp;<a href='../../courses/$repertoire/index.php' class=mainpage>$langEnter</a>&nbsp;</p>";
} // end of submit

$tool_content .= "</form>";

draw($tool_content, '1', 'create_course', $head_content);
?>
