<?php
/* ========================================================================
 * Open eClass 2.6
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

session_start();

$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'CreateCourse';

include '../../include/baseTheme.php';

if (isset($_SESSION['statut']) and $_SESSION['statut'] != 1) { // if we are not teachers
    redirect_to_home_page();
}
if (get_config("betacms")) { // added support for betacms
	require_once '../betacms_bridge/include/bcms.inc.php';
}

$nameTools = $langCreateCourse . " (" . $langCreateCourseStep ." 1 " .$langCreateCourseStep2 . " 3)" ;

$lang_editor = langname_to_code($language);

// javascript
load_js('jquery');
load_js('pwstrength.js');
$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

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
		alert("$langFieldsMissing");
		return false;
	} else {
		return true;
	}
}

    var lang = {
hContent;
    $head_content .= "pwStrengthTooShort: '". js_escape($langPwStrengthTooShort) ."', ";
    $head_content .= "pwStrengthWeak: '". js_escape($langPwStrengthWeak) ."', ";
    $head_content .= "pwStrengthGood: '". js_escape($langPwStrengthGood) ."', ";
    $head_content .= "pwStrengthStrong: '". js_escape($langPwStrengthStrong) ."'";
$head_content .= <<<hContent
    };
    
    $(document).ready(function() {
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });
    });

/* ]]> */
</script>
hContent;

$titulaire_probable = "$_SESSION[prenom] $_SESSION[nom]";

$tool_content .= "<form method='post' name='createform' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return checkrequired(this, 'intitule', 'titulaires');\">";

if (get_config("betacms")) { // added support for betacms
	// Import from BetaCMS Bridge
	doImportFromBetaCMSBeforeCourseCreation();
}

function escape_if_exists($name) {
        if (isset($_POST[$name])) {
                $tmp = autounquote($_POST[$name]);
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
escape_if_exists('localize');
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
                 $localize_html .
                 $description_html .
                 $course_keywords_html .
                 $visit_html .
                 $password_html;

if (isset($_POST['back1']) or !isset($_POST['visit'])) {
   // display form
    $tool_content .= "


  <fieldset>
      <legend>$langCreateCourseStep1Title</legend>
        <table class='tbl' width='100%'>
	<tr>
	  <th>$langTitle:</th>
	  <td><input type='text' name='intitule' size='60' value='".@$intitule."' /></td>
	  <td class='smaller'>$langEx</td>
	</tr>
	<tr>
	  <th>$langFaculty:</th>
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
        //<td>" . lang_select_options('languageCourse', '', $languageCourse) . "</td>
	$tool_content .= "
        <tr>
	  <th>$langTeachers:</th>
	  <td><input type='text' name='titulaires' size='60' value='" . q($titulaires) . "' /></td>
	  <td>&nbsp;</td>
        </tr>
	<tr>
	  <th class='left'>$langType:</th>
	  <td>" .  selection(array('pre' => $langpre, 'post' => $langpost, 'other' => $langother), 'type', $type) . "</td>
	  <td>&nbsp;</td>
        </tr>
	<tr>
	  <th class='left'>$langLanguage:</th>
	  <td>" . lang_select_options('localize') . "</td>
          <td>&nbsp;</td>
        </tr>
	<tr>
          <th>&nbsp;</th>
	  <td class='right'>&nbsp;</td>
	  <td class='right'>
	    <input type='submit' name='create2' value='".q($langNextStep)." &raquo;' />
	    <input type='hidden' name='visit' value='true' />
	  </td>
        </tr>
        </table>
              </fieldset>
        <div align='right' class='smaller'>(*) &nbsp;$langFieldsRequ</div>



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
      <table class='tbl' width='100%'>
      <tr>
        <td>$langDescrInfo&nbsp;:<br /> ".  rich_text_editor('description', 4, 20, $description)."</td>
      </tr>
      <tr>
	<td>$langCourseKeywords&nbsp;<br />
	  <input type='text' name='course_keywords' size='65' value='".q($course_keywords)."' />
        </td>
      </tr>
      <tr>
	<td class='right'><input type='submit' name='back1' value='&laquo; ".q($langPreviousStep)." ' />&nbsp;
                <input type='submit' name='create3' value='".q($langNextStep)." &raquo;' /></td>
      </tr>
      </table>
    </fieldset>
    <div align='right' class='smaller'>$langFieldsOptionalNote</div>
    <br />";

}  elseif (isset($_POST['create3']) or isset($_POST['back2'])) {
	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 3 " .$langCreateCourseStep2 . " 3 )" ;
	@$tool_content .= "
    <fieldset>
      <legend>$langCreateCourseStep3Title</legend>
		      <table class='tbl' width='100%'>
      <tr>
      <td class='sub_title1'>$langAvailableTypes<br></td>
      </tr>
      <tr>
	<td>
	  <table class='tbl' width='100%'>
	  <tr class='smaller'>
	    <th width='130'><img src='$themeimg/lock_open.png' title='".$m['legopen']."' alt='".$m['legopen']."'width='16' height='16' /> ".$m['legopen']."</th>
	    <td><input name='formvisible' type='radio' value='2' checked='checked' /></td>
	    <td>$langPublic</td>
	  </tr>
	  <tr class='smaller'>
	    <th valign='top'><img src='$themeimg/lock_registration.png' title='".$m['legrestricted']."' alt='".$m['legrestricted']."' width='16' height='16' /> ".$m['legrestricted']."</th>
	    <td valign='top'><input name='formvisible' type='radio' value='1' /></td>
	    <td>
              $langPrivOpen<br />
              <div class='smaller' style='padding: 3px;'><em>$langOptPassword</em> <input type='text' name='password' value='".q($password)."' class='FormData_InputText' id='password' />&nbsp;<span id='result'></span></div>
            </td>
          </tr>
	  <tr class='smaller'>
	    <th valign='top'><img src='$themeimg/lock_closed.png' title='".$m['legclosed']."' alt='".$m['legclosed']."' width=\"16\" height=\"16\" /> ".$m['legclosed']."</th>
	    <td valign='top'><input name='formvisible' type='radio' value='0' /></td>
	    <td>$langPrivate</td>
	  </tr>
          <tr class='smaller'>
	    <th valign='top'><img src='$themeimg/lock_inactive.png' title='".$m['linactive']."' alt='".$m['linactive']."' width='16' height='16' /> ".$m['linactive']."</th>
	    <td valign='top'><input name='formvisible' type='radio' value='3' /></td>
	    <td>$langCourseInactive</td>
	  </tr>
	  </table>      
	  <br />
	</td>
      </tr>
      <tr>
	<td class='sub_title1'>$langSubsystems</td>
      </tr>
      <tr>
	<td>
 	  <table class='tbl smaller' width='100%'>
	  <tr>
	    <td width='10' ><img src='$themeimg/calendar_on.png' alt='' height='16' width='16' /></td>
	    <td width='150'>$langAgenda</td>
	    <td width='30' ><input name='subsystems[]' type='checkbox' value='1' checked='checked' /></td>
	    <th width='2' >&nbsp;</th>
	    <td width='10' >&nbsp;<img src='$themeimg/dropbox_on.png' alt='' height='16' width='16' /></td>
	    <td width='150'>$langDropBox</td>
 	    <td width='30' ><input type='checkbox' name='subsystems[]' value='16' /></td>
	  </tr>
	  <tr  class='even'>
	    <td><img src='$themeimg/links_on.png' alt='' height='16' width='16' /></td>
	    <td>$langLinks</td>
	    <td><input name='subsystems[]' type='checkbox' value='2' checked='checked' /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src='$themeimg/groups_on.png' alt='' height='16' width='16' /></td>
	    <td>$langGroups</td>
	    <td><input type='checkbox' name='subsystems[]' value='15' /></td>
	  </tr>
	  <tr>
	    <td><img src='$themeimg/docs_on.png' alt='' height='16' width='16' /></td>
	    <td>$langDoc</td>
	    <td><input name='subsystems[]' type='checkbox' value='3' checked='checked' /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src='$themeimg/conference_on.png' alt='' height='16' width='16' /></td>
	    <td>$langConference</td>
	    <td><input type='checkbox' name='subsystems[]' value='19' /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src='$themeimg/videos_on.png' alt='' height='16' width='16' /></td>
	    <td>$langVideo</td>
	    <td><input name='subsystems[]' type='checkbox' value='4'  /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src='$themeimg/description_on.png' alt='' height='16' width='16' /></td>
	    <td>$langCourseDescription</td>
	    <td><input type='checkbox' name='subsystems[]' value='20' checked='checked' /></td>
	  </tr>
	  <tr>
	    <td><img src='$themeimg/assignments_on.png' alt='' height='16' width='16' /></td>
	    <td>$langWorks</td>
	    <td><input type='checkbox' name='subsystems[]' value='5' /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src='$themeimg/questionnaire_on.png' alt='' height='16' width='16' /></td>
	    <td>$langQuestionnaire</td>
	    <td><input type='checkbox' name='subsystems[]' value='21' /></td>
	  </tr>
	  <tr  class='even'>
	    <td><img src='$themeimg/announcements_on.png' alt='' height='16' width='16' /></td>
	    <td>$langAnnouncements</td>
	    <td><input type='checkbox' name='subsystems[]' value='7' checked='checked'/></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src='$themeimg/lp_on.png' alt='' height='16' width='16' /></td>
	    <td>$langLearnPath</td>
	    <td><input type='checkbox' name='subsystems[]'  value='23' /></td>
	  </tr>
	  <tr>
	    <td><img src='$themeimg/forum_on.png' alt='' height='16' width='16' /></td>
	    <td>$langForums</td>
	    <td><input type='checkbox' name='subsystems[]' value='9' /></td>
	    <th>&nbsp;</th>
	    <td>&nbsp;<img src='$themeimg/wiki_on.png' alt='' height='16' width='16' /></td>
	    <td>$langWiki</td>
	    <td><input type='checkbox' name='subsystems[]' value='26' /></td>
	  </tr>
	  <tr class='even'>
	    <td><img src='$themeimg/exercise_on.png' alt='' height='16' width='16' /></td>
	    <td>$langExercices</td>
	    <td><input type='checkbox' name='subsystems[]' value='10' /></td>
	    <th>&nbsp;</th>
            <td>&nbsp;<img src='$themeimg/glossary_on.png' alt='' height='16' width='16' /></td>
	    <td>$langGlossary</td>
	    <td><input type='checkbox' name='subsystems[]' value='17' checked='checked' /></td>
	  </tr>
	  <tr>
	    <td><img src='$themeimg/ebook_on.png' alt='' height='16' width='16' /></td>
	    <td>$langEBook</td>
	    <td><input type='checkbox' name='subsystems[]' value='18' /></td>
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
	<td class='right'>
          <input type='submit' name='back2' value='&laquo; ".q($langPreviousStep)."' />&nbsp;
	  <input type='submit' name='create_course' value='".q($langFinalize)."' />
        </td>
      </tr>
      </table>
      </fieldset>
      <div class='right smaller'>$langFieldsOptionalNote</div>
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

        $language = langcode_to_name($_POST['localize']);        
        
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
                draw($tool_content, 1, null, $head_content);
                exit;
        }
        // ---------------------------------------------------------
        //  all the course db queries are inside the following script
        // ---------------------------------------------------------
        require "create_course_db.php";

        // ------------- update main Db------------
        mysql_select_db($mysqlMainDb);
        
        // get default quota values
        $doc_quota = get_config('doc_quota');
        $group_quota = get_config('group_quota');
        $video_quota = get_config('video_quota');
        $dropbox_quota = get_config('dropbox_quota');
        
        db_query("INSERT INTO cours SET
                        code = '$code',
                        languageCourse =" . quote($language) . ",
                        intitule = " . quote($intitule) . ",
                        course_keywords = " . quote($course_keywords) . ",
                        visible = " . quote($formvisible) . ",
                        titulaires = " . quote($titulaires) . ",
                        fake_code = " . quote($code) . ",
                        type = " . quote($type) . ",
                        doc_quota = $doc_quota*1024*1024,
                        video_quota = $video_quota*1024*1024,
                        group_quota = $group_quota*1024*1024,
                        dropbox_quota = $dropbox_quota*1024*1024,
                        password = " . quote($password) . ",
                        faculteid = $facid,
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
	if (get_config("betacms")) {
	       $tool_content .= doImportFromBetaCMSAfterCourseCreation($repertoire, $mysqlMainDb, $webDir);
	}
        // --------------------------------------------------
        $tool_content .= "
                <p class='success'><b>$langJustCreated:</b> ".q($intitule)."<br>
                <span class='smaller'>$langEnterMetadata</span></p>
                <p class='eclass_button'><a href='../../courses/$repertoire/index.php'>$langEnter</a></p>";
} // end of submit

$tool_content .= "</form>";

draw($tool_content, 1, null, $head_content);
