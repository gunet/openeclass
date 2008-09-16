<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/


$require_login = TRUE;
$require_prof = TRUE;
$require_help = TRUE;
$helpTopic = 'CreateCourse';

include '../../include/baseTheme.php';
$nameTools = $langCreateCourse . " (" . $langCreateCourseStep ." 1 " .$langCreateCourseStep2 . " 3 )" ;
$tool_content = $head_content = "";

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

$titulaire_probable="$prenom $nom";
$local_style = "input { font-size: 12px; }";

$tool_content .= "<form method='post' name='createform' action='$_SERVER[PHP_SELF]' onsubmit=\"return checkrequired(this, 'intitule', 'titulaires');\">";
//$tool_content .= "<form method='post' name='createform' action='$_SERVER[PHP_SELF]'>";
@$tool_content .= "<input type='hidden' name='intitule' value='".htmlspecialchars($_POST['intitule'])."' />
      <input type='hidden' name='faculte' value='".htmlspecialchars($_POST['faculte'])."' />
      <input type='hidden' name='titulaires' value='".htmlspecialchars($_POST['titulaires'])."' />
      <input type='hidden' name='type' value='".htmlspecialchars($_POST['type'])."' />
      <input type='hidden' name='languageCourse' value='".htmlspecialchars($_POST['languageCourse'])."' />
      <input type='hidden' name='description' value='".htmlspecialchars($_POST['description'])."' />
      <input type='hidden' name='course_addon' value='".htmlspecialchars($_POST['course_addon'])."' />
      <input type='hidden' name='course_keywords' value='".htmlspecialchars($_POST['course_keywords'])."' />";

@$tool_content .= "<input type='hidden' name='visit' value='".htmlspecialchars($_POST['visit'])."' />";

if (isset($back1) or !isset($visit)) {

   // display form
   $tool_content .= "
    <table width=\"99%\" align='left' class='FormData'>
    <tbody>
    <tr>
      <th>&nbsp;</th>
      <td><b>$langCreateCourseStep1Title</b></td>
      <td>&nbsp;</td>
     </tr>
    <tr>
      <th class='left' width=\"160\">$langTitle&nbsp;:</th>
      <td width=\"160\"><input class='FormData_InputText' type='text' name='intitule' size='60' value='".@$intitule."' /></td>
      <td><small>$langEx</small></td>
     </tr>
     <tr>
       <th class='left'>$langFac&nbsp;:</th>
       <td>";

        // selection of faculty
        $resultFac=db_query("SELECT id,name FROM faculte ORDER BY number");
        $tool_content .= "
        <select name='faculte' class=auth_input>";
        while ($myfac = mysql_fetch_array($resultFac)) {
                if(isset($faculte) and implode('--',array($myfac['id'],$myfac['name'])) == $faculte)	
                   $tool_content .= "
          <option selected value='".$myfac['id']."--".$myfac['name']."'>$myfac[name]</option>";
                else
                        $tool_content .= "
          <option value='".$myfac['id']."--".$myfac['name']."'>$myfac[name]</option>";
        }
        $tool_content .= "</select></td><td>&nbsp;</td></tr>";
        unset($repertoire);
        $tool_content .= "
      <tr>
        <th class='left'>$langTeachers&nbsp;:</th>
        <td><input class='FormData_InputText' type='text' name='titulaires' size='60' value='".$titulaire_probable."' /></td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <th class='left'>$m[type]&nbsp;:</th>
        <td>";
        @$tool_content .= " ".selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']), 'type', $type)." ";
        $tool_content .= "
        </td>
        <td>&nbsp;</td>
      </tr>
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

 elseif (isset($create2) or isset($back2))  {
	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 2 " .$langCreateCourseStep2 . " 3 )";
	$tool_content .= "
    <table width=\"99%\" align='left' class='FormData'>
    <tbody>
    <tr>
      <th>&nbsp;</th>
      <td><b>$langCreateCourseStep2Title</b></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th class='left' width=\"160\">$langDescrInfo&nbsp;:</th>
      <td width=\"160\"><textarea name='description' cols='50' rows='6' class='FormData_InputText' wrap=\"soft\">$description</textarea></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th class='left' width=\"160\">$langCourseKeywords&nbsp;</th>
      <td width=\"160\"><textarea name='course_keywords' cols='50' rows='6' class='FormData_InputText'>$course_keywords</textarea></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th class='left' width=\"160\">$langCourseAddon&nbsp;</th>
      <td width=\"160\"><textarea name='course_addon' cols='50' rows='6' class='FormData_InputText'>$course_addon</textarea></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
       <th>&nbsp;</th>
       <td><input type='submit' name='back1' value='< $langPreviousStep ' />&nbsp;<input type='submit' name='create3' value='$langNextStep >' /></td>
       <td><p align='right'><small>$langFieldsOptionalNote</p></td>
    </tbody>
    </table><br />";

}  elseif (isset($create3) or isset($back2)) {
	$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 3 " .$langCreateCourseStep2 . " 3 )" ;
    $tool_content .= "
    <table width=\"99%\" align='left' class='FormData'>
    <tbody>
    <tr>
      <th>&nbsp;</th>
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
         <td width='30'><img src=\"../../template/classic/img/OpenCourse.gif\" alt=\"".$m['legopen']."\" title=\"".$m['legopen']."\" width=\"16\" height=\"16\"></td>
         <td width='200'>".$m['legopen']."</td>
         <td width='5' ><input name=\"formvisible\" type=\"radio\" value=\"2\" checked=\"checked\" /></td>
         <td width='325'><p align='right'><small>$langPublic</small></p></td>
      </tr>
      <tr>
         <td width='30'><img src=\"../../template/classic/img/Registration.gif\" alt=\"".$m['legrestricted']."\" title=\"".$m['legrestricted']."\" width=\"16\" height=\"16\"></td>
         <td width='200'>".$m['legrestricted']."</td>
         <td width='5'><input name=\"formvisible\" type=\"radio\" value=\"1\" /></td>
         <td width='325'><p align='right'><small>$langPrivOpen</small></p></td>
      </tr>
      <tr>
         <td width='30'><img src=\"../../template/classic/img/ClosedCourse.gif\" alt=\"".$m['legclosed']."\" title=\"".$m['legclosed']."\" width=\"16\" height=\"16\"></td>
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
        <td width='30' ><img src=\"../../template/classic/img/calendar_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td width='200'>$langAgenda</td>
        <td width='30' ><input name=\"subsystems[]\" type=\"checkbox\" value=\"1\" checked=\"checked\" /></td>
        <th width='2' >&nbsp;</th>
        <td width='30' >&nbsp;<img src=\"../../template/classic/img/dropbox_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td width='200'>$langDropBox</td>
        <td width='30' ><input type=\"checkbox\" name=\"subsystems[]\" value=\"15\" /></td>
      </tr>
      <tr>
        <td><img src=\"../../template/classic/img/links_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langLinks</td>
        <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"2\" checked=\"checked\" /></td>
        <th>&nbsp;</th>
        <td>&nbsp;<img src=\"../../template/classic/img/groups_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langGroups</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"16\" /></td>
      </tr>
      <tr>
        <td><img src=\"../../template/classic/img/docs_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langDoc</td>
        <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"3\" checked=\"checked\" /></td>
        <th>&nbsp;</th>
        <td>&nbsp;<img src=\"../../template/classic/img/chat_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langConference</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"19\" /></td>
      </tr>
      <tr>
        <td><img src=\"../../template/classic/img/video_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langVideo</td>
        <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"4\"  /></td>
        <th>&nbsp;</th>
        <td>&nbsp;<img src=\"../../template/classic/img/description_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langCourseDescription</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"20\" checked=\"checked\" /></td>
      </tr>
      <tr>
      <td><img src=\"../../template/classic/img/assignments_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langWorks</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"5\" /></td>
        <th>&nbsp;</th>
        <td>&nbsp;<img src=\"../../template/classic/img/questionnaire_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langQuestionnaire</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"21\" /></td>
      </tr>
      <tr>
        <td><img src=\"../../template/classic/img/announcements_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langAnnouncements</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"7\" checked=\"checked\"/></td>
        <th>&nbsp;</th>
        <td>&nbsp;<img src=\"../../template/classic/img/lp_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langLearnPath</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\"  value=\"23\" /></td>
      </tr>
      <tr>
        <td><img src=\"../../template/classic/img/forum_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langForums</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"9\" /></td>
        <th>&nbsp;</th>
        <td>&nbsp;<img src=\"../../template/classic/img/wiki_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
        <td>$langWiki</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"26\" /></td>
      </tr>
      <tr>
        <td><img src=\"../../template/classic/img/exercise_on.gif\" alt=\"\" border=\"0\" height=\"16\" width=\"16\"></td>
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
      <td width='400'><input type='submit' name='back2' value='< $langPreviousStep '>&nbsp;<input type='submit' name='create_course' value=\"$langFinalize\"></td>
      <td><p align='right'><small>$langFieldsOptionalNote</small></p></td>
    </tr>
    </tbody>
    </table><br />";
} // end of create3

// create the course and the course database
if (isset($create_course)) {

	$nameTools = $langCourseCreate;
        // H metavlhth faculte periexei to fac_id kai to
        // onoma tou tmhmatos xwrismena me dyo dashes
        // to $facid pairnei timh apo thn $faculte
        list($facid, $facname) = split("--", $faculte);
        // to $faculte ksanapairnei thn timh mono tou onomatos
        // tou tmhmatos gia logous compatibility
        $faculte = $facname;
	// find new code
        $repertoire = new_code(find_faculty_by_name($faculte));
        $language = preg_replace('/[^a-z]/', '', $_POST['languageCourse']);
        include("../lang/$language/common.inc.php");
        include("../lang/$language/messages.inc.php");
        // replace lower case letters by upper case in code_cours
        $repertoire=strtoupper($repertoire);
        $faculte_lower=strtolower($faculte);

                //remove space in code_cours
                $repertoire = str_replace (" ", "", $repertoire);
                $repertoire_lower=strtolower($repertoire);
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
                        draw($tool_content, '1', null, $head_content);
                        exit;
                }
                // ---------------------------------------------------------
                //  all the course db queries are inside the following script
                // ---------------------------------------------------------
                require "create_course_db.php";

                // ------------- update main Db------------
                mysql_select_db("$mysqlMainDb");

                mysql_query("INSERT INTO cours SET
                                code = '$code',
                                languageCourse = '$language',
                                intitule = '$intitule',
                                description = '$description',
                                course_addon = '$course_addon',
                                course_keywords = '$course_keywords',
                                faculte = '$facname',
                                visible = '$formvisible',
                                titulaires = '$titulaires',
                                fake_code = '$code',
                                type = '$type',
                                faculteid = '$facid',
		first_create = NOW()");
                mysql_query("INSERT INTO cours_user SET
                                code_cours = '$repertoire',
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
                $status[$repertoire]=1;
                session_register("status");

                $tool_content .= "
  <table width=\"99%\">
  <tbody>
  <tr>
    <td class=\"success\" width='1'>&nbsp;</td>
    <td>$langJustCreated: &nbsp;<b>$intitule</b><br/><small>$langEnterMetadata</small></td>
  </tr>
  </tbody>
  </table><br /><br />
  <p align='center'><a href='../../courses/$repertoire/index.php' class=mainpage><img src=\"../../template/classic/img/go.gif\" alt=\"\" border=\"0\" height=\"46\" width=\"46\"></a><p>
  <p align='center'>&nbsp;<a href='../../courses/$repertoire/index.php' class=mainpage>$langEnter</a>&nbsp;</p>
";
} // end of submit

$tool_content .= "</form>";

draw($tool_content, '1', null, $head_content);
?>
