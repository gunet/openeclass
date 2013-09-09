<?php
/* ========================================================================
 * Open eClass 2.8
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

$nameTools = $langCourseCreate;
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


$tool_content .= "<form method='post' name='createform' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return checkrequired(this, 'intitule', 'titulaires');\">";

if (get_config("betacms")) { // added support for betacms
	// Import from BetaCMS Bridge
	doImportFromBetaCMSBeforeCourseCreation();
}

if (empty($faculte)) {
        list($homefac) = mysql_fetch_row(db_query("SELECT department FROM user WHERE user_id = $uid"));
} else {
        $homefac = intval($faculte);
}

$teacher = "$_SESSION[prenom] $_SESSION[nom]";
// --------------------------
// display form
// --------------------------
if (!isset($_POST['create_course'])) {   
    $tool_content .= "
    <fieldset><legend>$langCreateCourseStep1Title</legend>
        <table class='tbl'>
	<tr>
	  <th>$langTitle:</th>
	  <td><input type='text' name='intitule' size='60' value='".@$intitule."' /></td>	  
	</tr>
	<tr><th>$langFaculty:</th><td>";
	$facs = db_query("SELECT id, name FROM faculte order by id");
	while ($n = mysql_fetch_array($facs)) {
		$fac[$n['id']] = $n['name'];
	}
	$tool_content .= selection($fac, 'faculte', $homefac);
	$tool_content .= "</td></tr>";
	unset($repertoire);        
	$tool_content .= "
        <tr>
	  <th>$langTeachers:</th>
	  <td><input type='text' name='titulaires' size='60' value='" . q($teacher) . "' /></td>	  
        </tr>
	<tr>
	  <th class='left'>$langType:</th>
	  <td>" .  selection(array('pre' => $langpre, 
                                   'post' => $langpost, 
                                   'other' => $langother), 'type') . "</td>
        </tr>
	<tr>
	  <th class='left'>$langLanguage:</th>
	  <td>" . lang_select_options('localize') . "</td>          
        </tr>";
        $tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";
        @$tool_content .= "<tr><th colspan='2'>$langDescrInfo <span class='smaller'>$langUncompulsory</span><br /> ".  rich_text_editor('description', 4, 20, $description)."</th></tr>";
        $tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";
        @$tool_content .= "<tr><td class='sub_title1' colspan='2'>$langAvailableTypes</td></tr>
          <tr>
            <td colspan='2'>
              <table class='tbl'>
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
            </td>
          </tr>            
          <tr>
            <td class='right'>&nbsp;
              <input type='submit' name='create_course' value='".q($langFinalize)."' />
            </td>
          </tr>
          </table>          
          <div class='right smaller'>$langFieldsOptionalNote</div>
          </fieldset>";
        $tool_content .= "</form>";

} else { // create the course and the course database

        $nameTools = $langCourseCreate;
        $facid = intval($_POST['faculte']);
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
        if (!(mkdir("../../courses/$repertoire", 0775) and
              mkdir("../../courses/$repertoire/image", 0775) and
              mkdir("../../courses/$repertoire/document", 0775) and
              mkdir("../../courses/$repertoire/dropbox", 0775) and
              mkdir("../../courses/$repertoire/page", 0775) and
              mkdir("../../courses/$repertoire/work", 0775) and
              mkdir("../../courses/$repertoire/group", 0775) and
              mkdir("../../courses/$repertoire/temp", 0775) and
              mkdir("../../courses/$repertoire/scormPackages", 0775) and
              mkdir("../../video/$repertoire", 0775))) {
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
                        intitule = " . quote($_POST['intitule']) . ",
                        visible = " . quote($_POST['formvisible']) . ",
                        titulaires = " . quote($teacher) . ",
                        fake_code = " . quote($code) . ",
                        type = " . quote($_POST['type']) . ",
                        doc_quota = $doc_quota*1024*1024,
                        video_quota = $video_quota*1024*1024,
                        group_quota = $group_quota*1024*1024,
                        dropbox_quota = $dropbox_quota*1024*1024,
                        password = " . quote($_POST['password']) . ",
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

        if (isset($_POST['description'])) {
            $description = trim(autounquote($_POST['description']));
            $unit_id = description_unit_id($new_cours_id);
            if (!empty($description)) {
                    add_unit_resource($unit_id, 'description', -1, $langDescription, $description);
            }
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
        $tool_content .= "<p class='success'><b>$langJustCreated:</b> ".q($_POST['intitule'])."<br>
                <span class='smaller'>$langEnterMetadata</span></p>
                <p class='eclass_button'><a href='../../courses/$repertoire/index.php'>$langEnter</a></p>";
} // end of submit

draw($tool_content, 1, null, $head_content);
