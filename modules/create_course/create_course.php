<?php
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
hContent;

$titulaire_probable = "$_SESSION[prenom] $_SESSION[nom]";

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
escape_if_exists('course_keywords');
escape_if_exists('visit');
escape_if_exists('password');
escape_if_exists('formvisible');

if (empty($faculte)) {
        list($homefac) = mysql_fetch_row(db_query("SELECT department FROM user WHERE user_id=$uid"));
} else {
        $homefac = intval($faculte);
}

if (empty($titulaires)) {
        $titulaires = $titulaire_probable;
}

$tool_content .= $intitule_html .
                 $faculte_html .
                 $titulaires_html .
                 $type_html .
                 $languageCourse_html .
                 $description_html .
                 $course_keywords_html .
                 $visit_html .
                 $password_html;

if (isset($_POST['back1']) or !isset($_POST['visit'])) {
   // display form
    $tool_content .= "
      <fieldset>
      <legend>$langCreateCourseStep1Title</legend>
        <table class='tbl'>
	<tr>
	  <td>$langTitle&nbsp;:</td>
	  <td><input type='text' name='intitule' size='60' value='".@$intitule."' /></td>
	  <td>$langEx</td>
	</tr>
	<tr>
	  <td>$langFaculty&nbsp;:</td>
	  <td>";
	$facs = db_query("SELECT id, name FROM faculte order by id");
	while ($n = mysql_fetch_array($facs)) {
		$fac[$n['id']] = $n['name'];
	}
	$tool_content .= selection($fac, 'faculte', $homefac);
	$tool_content .= "</td>
          <td>&nbsp;</td>
        </tr>";
	unset($repertoire);
	$tool_content .= "
        <tr>
	  <td>$langTeachers&nbsp;:</td>
	  <td><input type='text' name='titulaires' size='60' value='" . q($titulaires) . "' /></td>
	  <td>&nbsp;</td>
        </tr>
	<tr>
	  <td class='left'>$langType&nbsp;:</td>
	  <td>" .  selection(array('pre' => $langpre, 'post' => $langpost, 'other' => $langother), 'type', $type) . "</td>
	  <td>&nbsp;</td>
        </tr>
	<tr>
	  <td class='left'>$langLanguage&nbsp;:</td>
	  <td>" . lang_select_options('languageCourse', '', $languageCourse) . "</td>
          <td>&nbsp;</td>
        </tr>
	<tr>
          <td>&nbsp;</td>
	  <td><input type='submit' name='create2' value='$langNextStep >' /><input type='hidden' name='visit' value='true' /></td>
	  <td>&nbsp;</td>
        </tr>
        </table>
      </fieldset>
      <div align='right'>(*) &nbsp;$langFieldsRequ</div>
        <br />";
}

// --------------------------------
// step 2 of creation
// --------------------------------

 elseif (isset($_POST['create2']) or isset($_POST['back2']))  {
	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 2 " .$langCreateCourseStep2 . " 3 )";
	$tool_content .= "
    <fieldset>
      <legend>$langCreateCourseStep2Title</legend>
      <table class='tbl'>
      <tr>
        <td>$langDescrInfo&nbsp;:<br /> ".  rich_text_editor('description', 4, 20, $description)."</td>
      </tr>
      <tr>
	<td>$langCourseKeywords&nbsp;<br />
	  <input type='text' name='course_keywords' size='65' value='$course_keywords' />
        </td>
      </tr>
      <tr>
	<td><input type='submit' name='back1' value='< $langPreviousStep ' />&nbsp;<input type='submit' name='create3' value='$langNextStep >' /></td>
      </tr>
      </table>
    </fieldset>
    <div align='right'>$langFieldsOptionalNote</div>
    <br />";

}  elseif (isset($_POST['create3']) or isset($_POST['back2'])) {
	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 3 " .$langCreateCourseStep2 . " 3 )" ;
	@$tool_content .= "
    <fieldset>
      <legend>$langCreateCourseStep3Title</legend>
      <table class='tbl' width='99%'>
      <tr>
        <td>$langAvailableTypes</td>
      </tr>
      <tr>
	<td>
	  <table class='tbl_alt' width='99%'>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/OpenCourse.gif\" title=\"".$m['legopen']."\" width=\"16\" height=\"16\" /></td>
	    <td width='100'>".$m['legopen']."</td>
	    <td><input name=\"formvisible\" type=\"radio\" value=\"2\" checked=\"checked\" /></td>
	    <td class='right'>$langPublic</td>
	  </tr>
	  <tr class='even'>
	    <td valign='top'><img src=\"../../template/classic/img/Registration.gif\" title=\"".$m['legrestricted']."\" width=\"16\" height=\"16\" /></td>
	    <td valign='top'>".$m['legrestricted']."</td>
	    <td valign='top'><input name=\"formvisible\" type=\"radio\" value=\"1\" /></td>
	    <td class='right'>
              $langPrivOpen<br />
              $langOptPassword <input type='text' name='password' value='".q($password)."' class='FormData_InputText' />
            </td>
          </tr>
	  <tr class='even'>
	    <td valign='top'><img src=\"../../template/classic/img/ClosedCourse.gif\" title=\"".$m['legclosed']."\" width=\"16\" height=\"16\" /></td>
	    <td valign='top'>".$m['legclosed']."</td>
	    <td valign='top'><input name=\"formvisible\" type=\"radio\" value=\"0\" /></td>
	    <td class='right'>$langPrivate</td>
	  </tr>
	  </table>
	  <br />
	</td>
      </tr>
      <tr>
	<td>$langSubsystems</td>
      </tr>
      <tr>
	<td>
 	  <table class='tbl_border'>
	  <tr class='even'>
	    <td width='30' ><img src=\"../../template/classic/img/calendar_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td width='150'>$langAgenda</td>
	    <td width='30' ><input name=\"subsystems[]\" type=\"checkbox\" value=\"1\" checked=\"checked\" /></td>
	    <th width='2' >&nbsp;</th>
	    <td width='30' >&nbsp;<img src=\"../../template/classic/img/dropbox_on.gif\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td width='150'>$langDropBox</td>
 	    <td width='30' ><input type=\"checkbox\" name=\"subsystems[]\" value=\"16\" /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/links_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langLinks</td>
	    <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"2\" checked=\"checked\" /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src=\"../../template/classic/img/groups_on.gif\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langGroups</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"15\" /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/docs_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langDoc</td>
	    <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"3\" checked=\"checked\" /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src=\"../../template/classic/img/chat_on.gif\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langConference</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"19\" /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/videos_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langVideo</td>
	    <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"4\"  /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src=\"../../template/classic/img/description_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langCourseDescription</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"20\" checked=\"checked\" /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/assignments_on.gif\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langWorks</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"5\" /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src=\"../../template/classic/img/questionnaire_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langQuestionnaire</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"21\" /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/announcements_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langAnnouncements</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"7\" checked=\"checked\"/></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src=\"../../template/classic/img/lp_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langLearnPath</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\"  value=\"23\" /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/forum_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langForums</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"9\" /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src=\"../../template/classic/img/wiki_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langWiki</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"26\" /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/exercise_on.gif\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langExercices</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"10\" /></td>
	    <th>&nbsp;</th>
            <td>&nbsp;<img src=\"../../template/classic/img/glossary_on.gif\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langGlossary</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"17\" checked=\"checked\" /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src=\"../../template/classic/img/ebook_on.png\" alt=\"\" height=\"16\" width=\"16\" /></td>
	    <td>$langEBook</td>
	    <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"18\" /></td>
	    <th>&nbsp;</th>
            <td>&nbsp;</td>
	    <td>&nbsp;</td>
	    <td>&nbsp;</td>
	  </tr>
	  </table>
        <br />
	</td>
      </tr>
      <tr>
	<td>
          <input type='submit' name='back2' value='< $langPreviousStep ' />&nbsp;
	  <input type='submit' name='create_course' value=\"$langFinalize\" />
        </td>
      </tr>
      </table>
      </fieldset>
      <div align='right'>$langFieldsOptionalNote</div>
      <br />";
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
        if (!(mkdir("../../courses/$repertoire", 0777) and
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
                draw($tool_content, '1', '', $head_content);
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
                        course_keywords = " . quote($course_keywords) . ",
                        faculte = " . quote($facname) . ",
                        visible = " . quote($formvisible) . ",
                        titulaires = " . quote($titulaires) . ",
                        fake_code = " . quote($code) . ",
                        type = " . quote($type) . ",
                        password = " . quote($password) . ",
                        faculteid = '$facid',
                        first_create = NOW()");
        $new_cours_id = mysql_insert_id();
        db_query("INSERT INTO cours_user SET
                        cours_id = $new_cours_id,
                        user_id = '$uid',
                        statut = '1',
                        tutor='1',
                        reg_date = CURDATE()");
        db_query("INSERT INTO group_properties SET
                        course_id = $new_cours_id,
                        self_registration = 1,
                        multiple_registration = 0,
                        forum = 1,
                        private_forum = 0,
                        documents = 1,
                        wiki = 0,
                        agenda = 0");

        $description = trim(autounquote($description));
        $unit_id = description_unit_id($new_cours_id);
        if (!empty($description)) {
                add_unit_resource($unit_id, 'description', -1, $langDescription, trim(autounquote($description)));
        }

        // ----------- main course index.php -----------

        $fd = fopen("../../courses/$repertoire/index.php", "w");
        fwrite($fd, "<?php\nsession_start();\n" .
                    "\$_SESSION['dbname']='$repertoire';\n" .
                    "include '../../modules/course_home/course_home.php';\n");
        fclose($fd);
        $status[$repertoire] = 1;
        $_SESSION['status'] = $status;

        // ----------- Import from BetaCMS Bridge -----------
        $tool_content .= doImportFromBetaCMSAfterCourseCreation($repertoire, $mysqlMainDb, $webDir);
        // --------------------------------------------------
        $tool_content .= "
                <p class=\"success\">$langJustCreated: &nbsp;<b>$intitule</b></p>
                <p><small>$langEnterMetadata</small></p><br />
                <p align='center'>&nbsp;<a href='../../courses/$repertoire/index.php' class=mainpage>$langEnter</a>&nbsp;</p>";
} // end of submit

$tool_content .= "</form>";

draw($tool_content, '1', '', $head_content);
