<?php
/* ========================================================================
 * Open eClass 3.0
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

session_start();

$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'CreateCourse';

require_once '../../include/baseTheme.php';

if (isset($_SESSION['statut']) and $_SESSION['statut'] != 1) { // if we are not teachers
    redirect_to_home_page();
}
if (get_config("betacms")) { // added support for betacms
	require_once 'betacms_bridge/include/bcms.inc.php';
}

$TBL_USER_DEPARTMENT   = 'user_department';

require_once 'include/log.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';

$tree = new hierarchy();
$course = new course();
$user = new user();

$nameTools = $langCreateCourse . " (" . $langCreateCourseStep ." 1 " .$langCreateCourseStep2 . " 3)" ;
$lang_editor = langname_to_code($language);

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');
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

if (isset($_POST['back1']) or !isset($_POST['visit']))
    $tool_content .= "<form method='post' name='createform' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return validateNodePickerForm() && checkrequired(this, 'title', 'titulaires');\">";
else
    $tool_content .= "<form method='post' name='createform' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"checkrequired(this, 'title', 'titulaires');\">";

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
escape_if_exists('title');
escape_if_exists('titulaires');
escape_if_exists('languageCourse');
escape_if_exists('description');
escape_if_exists('course_keywords');
escape_if_exists('visit');
escape_if_exists('password');
escape_if_exists('formvisible');

$departments = isset($_POST['department']) ? $_POST['department'] : array();
$faculte_html = "";
$deps_valid = true;

foreach ($departments as $dep) {
    if ( get_config('restrict_teacher_owndep') && !$is_admin && !in_array($dep, $user->getDepartmentIds($uid)) )
        $deps_valid = false;
    $faculte_html .= '<input type="hidden" name="department[]" value="'. $dep .'" />';
}

// Check if the teacher is allowed to create in the departments he chose
if (!$deps_valid) {
    $nameTools = "";
    $tool_content .= "
                <p class='caution'>$langCreateCourseNotAllowedNode</p>
                <p class='eclass_button'><a href='create_course.php'>$langBack</a></p>";
    draw($tool_content, 1, null, $head_content);
    exit();
}

if (empty($titulaires)) {
        $titulaires = $titulaire_probable;
}

$tool_content .= $title_html .
                 $faculte_html .
                 $titulaires_html .
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
        <table class='tbl' width='100%'>
	<tr>
	  <th>$langTitle:</th>
	  <td><input type='text' name='title' size='60' value='".@$title."' /></td>
	  <td class='smaller'>$langEx</td>
	</tr>
	<tr>
	  <th>$langFaculty:</th>
	  <td>";
        $allow_only_defaults = ( get_config('restrict_teacher_owndep') && !$is_admin ) ? true : false;
        list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $user->getDepartmentIds($uid), 'allow_only_defaults' => $allow_only_defaults));
        $head_content .= $js;
        $tool_content .= $html;
	$tool_content .= "</td>
          <td>&nbsp;</td>
        </tr>";
	unset($code);
	$tool_content .= "
        <tr>
        <th>$langTeachers:</th>
        <td><input type='text' name='titulaires' size='60' value='" . q($titulaires) . "' /></td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <th class='left'>$langLanguage:</th>
        <td>" . lang_select_options('languageCourse', '', $languageCourse) . "</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <th>&nbsp;</th>
        <td class='right'>&nbsp;</td>
        <td class='right'>
        <input type='submit' name='create2' value='$langNextStep &raquo;' />
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
                <td class='right'><input type='submit' name='back1' value='&laquo; $langPreviousStep ' />&nbsp;<input type='submit' name='create3' value='$langNextStep &raquo;' /></td>
        </tr>
        </table>
        </fieldset>
        <div align='right' class='smaller'>$langFieldsOptionalNote</div>
        <br />";

}  elseif (isset($_POST['create3']) or isset($_POST['back2'])) {
	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 3 " .$langCreateCourseStep2 . " 3 )" ;
	$tool_content .= "
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
                <th width='130'><img src='$themeimg/lock_open.png' title='".$m['legopen']."' width='16' height='16' /> ".$m['legopen']."</th>
                <td><input name='formvisible' type='radio' value='2' checked='checked' /></td>
                <td>$langPublic</td>
                </tr>
                <tr class='smaller'>
                <th valign='top'><img src='$themeimg/lock_registration.png' title='".$m['legrestricted']."' width='16' height='16' /> ".$m['legrestricted']."</th>
                <td valign='top'><input name='formvisible' type='radio' value='1' /></td>
                <td>
                $langPrivOpen<br />
                <div class='smaller' style='padding: 3px;'><em>$langOptPassword</em> <input type='text' name='password' value='".q($password)."' class='FormData_InputText' id='password' />&nbsp;<span id='result'></span></div>
                </td>
                </tr>
                <tr class='smaller'>
                <th valign='top'><img src='$themeimg/lock_closed.png' title='".$m['legclosed']."' width=\"16\" height=\"16\" /> ".$m['legclosed']."</th>
                <td valign='top'><input name='formvisible' type='radio' value='0' /></td>
                <td>$langPrivate</td>
                </tr>
                <tr class='smaller'>
                <th valign='top'><img src=\"$themeimg/lock_inactive.png\" title=\"".$m['linactive']."\" width=\"16\" height=\"16\" /> ".$m['linactive']."</th>
                <td valign='top'><input name=\"formvisible\" type=\"radio\" value='3' /></td>
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
        <tr>";
        $tool_content .= create_td($modules[MODULE_ID_AGENDA], MODULE_ID_AGENDA, 1);
        $tool_content .= "<th width='2' >&nbsp;</th>";
        $tool_content .= create_td($modules[MODULE_ID_DROPBOX], MODULE_ID_DROPBOX, 0);
        $tool_content .= "</tr><tr class='even'>";
        $tool_content .= create_td($modules[MODULE_ID_LINKS], MODULE_ID_LINKS, 1);
        $tool_content .= "<th>&nbsp;</th>";
        $tool_content .= create_td($modules[MODULE_ID_GROUPS], MODULE_ID_GROUPS, 0);
        $tool_content .= "</tr><tr>";
        $tool_content .= create_td($modules[MODULE_ID_DOCS], MODULE_ID_DOCS, 1);
        $tool_content .= "<th>&nbsp;</th>";
        $tool_content .= create_td($modules[MODULE_ID_CHAT], MODULE_ID_CHAT, 0);
        $tool_content .= "</tr><tr class='even'>";
        $tool_content .= create_td($modules[MODULE_ID_VIDEO], MODULE_ID_VIDEO, 0);
        $tool_content .= "<th>&nbsp;</th>";
        $tool_content .= create_td($modules[MODULE_ID_DESCRIPTION], MODULE_ID_DESCRIPTION, 1);
        $tool_content .= "</tr><tr>";
        $tool_content .= create_td($modules[MODULE_ID_ASSIGN], MODULE_ID_ASSIGN, 0);
        $tool_content .= "<th>&nbsp;</th>";
        $tool_content .= create_td($modules[MODULE_ID_QUESTIONNAIRE], MODULE_ID_QUESTIONNAIRE, 0);
        $tool_content .= "</tr><tr class='even'>";
        $tool_content .= create_td($modules[MODULE_ID_ANNOUNCE], MODULE_ID_ANNOUNCE, 1);
        $tool_content .= "<th>&nbsp;</th>";
        $tool_content .= create_td($modules[MODULE_ID_LP], MODULE_ID_LP, 0);
        $tool_content .= "</tr><tr>";
        $tool_content .= create_td($modules[MODULE_ID_FORUM], MODULE_ID_FORUM, 0);
        $tool_content .= "<th>&nbsp;</th>";
        $tool_content .= create_td($modules[MODULE_ID_WIKI], MODULE_ID_WIKI, 0);
        $tool_content .= "</tr><tr class='even'>";
        $tool_content .= create_td($modules[MODULE_ID_EXERCISE], MODULE_ID_EXERCISE, 0);
        $tool_content .= "<th>&nbsp;</th>";
        $tool_content .= create_td($modules[MODULE_ID_GLOSSARY], MODULE_ID_GLOSSARY, 1);
        $tool_content .= "</tr><tr>";
        $tool_content .= create_td($modules[MODULE_ID_EBOOK], MODULE_ID_EBOOK, 0);
        $tool_content .= "<th>&nbsp;</th><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                </tr>
                </table>
                <br />
                </td>
        </tr>
        <tr>
                <td class='right'>
                <input type='submit' name='back2' value='&laquo; $langPreviousStep ' />&nbsp;
                <input type='submit' name='create_course' value='$langFinalize' />
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

        // create new course code: uppercase, no spaces allowed
        $code = strtoupper(new_code($departments[0]));
        $code = str_replace(' ', '', $code);

        $language = validate_language_code($_POST['languageCourse']);
        // include_messages
        include "lang/$language/common.inc.php";
        $extra_messages = "config/{$language_codes[$language]}.inc.php";
        if (file_exists($extra_messages)) {
                include $extra_messages;
        } else {
                $extra_messages = false;
        }
        include "lang/$language/messages.inc.php";
        if ($extra_messages) {
                include $extra_messages;
        }

        // create directories
        umask(0);
        if (!(mkdir("courses/$code", 0775) and
              mkdir("courses/$code/image", 0775) and
              mkdir("courses/$code/document", 0775) and
              mkdir("courses/$code/dropbox", 0775) and
              mkdir("courses/$code/page", 0775) and
              mkdir("courses/$code/work", 0775) and
              mkdir("courses/$code/group", 0775) and
              mkdir("courses/$code/temp", 0775) and
              mkdir("courses/$code/scormPackages", 0775) and
              mkdir("video/$code", 0775))) {
                $tool_content .= "<div class='caution'>$langErrorDir</div>";
                draw($tool_content, 1, null, $head_content);
                exit;
        }

        // get default quota values
        $doc_quota = get_config('doc_quota');
        $group_quota = get_config('group_quota');
        $video_quota = get_config('video_quota');
        $dropbox_quota = get_config('dropbox_quota');

        db_query("INSERT INTO course SET
                        code = ".quote($code) . ",
                        lang =" . quote($language) . ",
                        title = " . quote($title) . ",
                        keywords = " . quote($course_keywords) . ",
                        visible = " . quote($formvisible) . ",
                        prof_names = " . quote($titulaires) . ",
                        public_code = " . quote($code) . ",
                        doc_quota = $doc_quota*1024*1024,
                        video_quota = $video_quota*1024*1024,
                        group_quota = $group_quota*1024*1024,
                        dropbox_quota = $dropbox_quota*1024*1024,
                        password = " . quote($password) . ",
                        created = NOW()");
        $new_course_id = mysql_insert_id();


        // checkboxes array
        for ($i = 0; $i <= 50; $i++) {
                $sbsystems[$i] = 0;
        }

        // allagh timwn sto array analoga me to poio checkbox exei epilegei
        if (isset($_POST['subsystems'])) {
            foreach ($_POST['subsystems'] as $sb) {
                    $sbsystems[$sb] = 1;
            }
        }
        // create entries in table `modules`
        create_modules($new_course_id, $sbsystems);

        db_query("INSERT INTO course_user SET
                        course_id = $new_course_id,
                        user_id = $uid,
                        statut = 1,
                        tutor = 1,
                        reg_date = CURDATE()");
        db_query("INSERT INTO group_properties SET
                        course_id = $new_course_id,
                        self_registration = 1,
                        multiple_registration = 0,
                        forum = 1,
                        private_forum = 0,
                        documents = 1,
                        wiki = 0,
                        agenda = 0");
        $course->refresh($new_course_id, $departments);

        $description = purify($description);
        $unit_id = description_unit_id($new_course_id);
        if (!empty($description)) {
                add_unit_resource($unit_id, 'description', -1, $langDescription, $description);
        }

        // ----------- main course index.php -----------

        $fd = fopen("courses/$code/index.php", "w");
        fwrite($fd, "<?php\nsession_start();\n" .
                    "\$_SESSION['dbname']='$code';\n" .
                    "include '../../modules/course_home/course_home.php';\n");
        fclose($fd);
        $status[$code] = 1;
        $_SESSION['status'] = $status;

        // ----------- Import from BetaCMS Bridge -----------
	if (get_config('betacms')) {
                $tool_content .= doImportFromBetaCMSAfterCourseCreation($code, $mysqlMainDb, $webDir);
	}
        // --------------------------------------------------
        $tool_content .= "
                <p class='success'><b>$langJustCreated:</b> " . q($title) . "<br>
                <span class='smaller'>$langEnterMetadata</span></p>
                <p class='eclass_button'><a href='../../courses/$code/index.php'>$langEnter</a></p>";
        // logging
        Log::record(0, 0, LOG_CREATE_COURSE,
                        array('id' => $new_course_id,
                                'code' => $code,
                                'title' => $title,
                                'language' => $language,
                                'visible' => $formvisible));
} // end of submit

$tool_content .= "</form>";

draw($tool_content, 1, null, $head_content);

// ---------------------------------------------
// create entries in table `module`
// ---------------------------------------------
function create_modules($cid, $sbsystems) {

        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_AGENDA.", ".$sbsystems[MODULE_ID_AGENDA].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_LINKS.", ".$sbsystems[MODULE_ID_LINKS].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_DOCS.", ".$sbsystems[MODULE_ID_DOCS].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_VIDEO.", ".$sbsystems[MODULE_ID_VIDEO].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_ASSIGN.", ".$sbsystems[MODULE_ID_ASSIGN].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_ANNOUNCE.", ".$sbsystems[MODULE_ID_ANNOUNCE].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_FORUM.", ".$sbsystems[MODULE_ID_FORUM].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_EXERCISE.", ".$sbsystems[MODULE_ID_EXERCISE].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_GROUPS.", ".$sbsystems[MODULE_ID_GROUPS].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_DROPBOX.", ".$sbsystems[MODULE_ID_DROPBOX].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_GLOSSARY.", ".$sbsystems[MODULE_ID_GLOSSARY].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_EBOOK.", ".$sbsystems[MODULE_ID_EBOOK].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_CHAT.", ".$sbsystems[MODULE_ID_CHAT].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_DESCRIPTION.", ".$sbsystems[MODULE_ID_DESCRIPTION].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_QUESTIONNAIRE.", ".$sbsystems[MODULE_ID_QUESTIONNAIRE].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_LP.", ".$sbsystems[MODULE_ID_LP].", $cid)");
        db_query("INSERT INTO course_module (module_id, visible, course_id) VALUES (".MODULE_ID_WIKI.", ".$sbsystems[MODULE_ID_WIKI].", $cid)");
}

// ----------------------------------------
// create <td>....</td> for each module
// ----------------------------------------
function create_td($m, $value, $selected) {

        global $themeimg;

        $checkbox = '';
        if ($selected) {
                $checkbox = "checked='checked'";
        }
        $td = "<td width='10' ><img src='$themeimg/$m[image]_on.png' alt='' height='16' width='16' /></td>
        <td width='150'>$m[title]</td>
        <td width='30' ><input name='subsystems[]' type='checkbox' value='$value' $checkbox /></td>";

        return $td;
}
