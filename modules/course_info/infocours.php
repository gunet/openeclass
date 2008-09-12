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
// if we come from the home page
if (isset($from_home) and ($from_home == TRUE) and isset($_GET['cid'])) {
        session_start();
        $dbname = $cid;
        session_register("dbname");
}

$require_current_course = TRUE;
$require_prof = true;
$require_help = TRUE;
$helpTopic = 'Infocours'; 
if (isset($_POST['localize'])) {
	$newlang = $language = preg_replace('/[^a-z]/', '', $_POST['localize']);
}

include '../../include/baseTheme.php';

if(isset($newlang)) {
	include ($webDir."modules/lang/$newlang/messages.inc.php");
}
$nameTools = $langModifInfo;
$tool_content = "";

// submit
if ($is_adminOfCourse) {
	if (isset($submit)) {
		if (!empty($int)) {
			if(isset($newlang)) {
			include ($webDir."modules/lang/$newlang/messages.inc.php");
		}
		// update course settings
		if (isset($checkpassword) && $checkpassword=="on" && $formvisible=="1") {
			$password = $password;
		} else {
			$password = "";
		}

		list($facid, $facname) = split("--", $facu);
		$sql = "UPDATE $mysqlMainDb.cours
			SET intitule='$int', 
				faculte='$facname', 
				description=".autoquote($description).",
				course_addon=".autoquote($course_addon).",
				course_keywords=".autoquote($course_keywords).",
				visible=".autoquote($formvisible).", 
				titulaires=".autoquote($titulary).", 
				languageCourse=".autoquote($newlang).",
				type=".autoquote($type).",
				password=".autoquote($password).",
				faculteid=".autoquote($facid)."
			WHERE code=".autoquote($currentCourseID);
		mysql_query($sql);
		mysql_query("UPDATE `$mysqlMainDb`.cours_faculte
                             SET faculte=".autoquote($facname).",
                                 facid=".autoquote($facid)."
                             WHERE code='$currentCourseID'");

		// update Home Page Menu Titles for new language
		mysql_select_db($currentCourseID, $db);
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langAgenda' WHERE id='1'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langLinks' WHERE id='2'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langDoc' WHERE id='3'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langVideo' WHERE id='4'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langWorks' WHERE id='5'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langAnnouncements' WHERE id='7'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langUsers' WHERE id='8'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langForums' WHERE id='9'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langExercices' WHERE id='10'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langModifyInfo' WHERE id='14'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langGroups' WHERE id='15'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langDropBox' WHERE id='16'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langConference' WHERE id='19'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langCourseDescription' WHERE id='20'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langQuestionnaire' WHERE id='21'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langLearnPath' WHERE id='23'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langUsage' WHERE id='24'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langToolManagement' WHERE id='25'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langWiki' WHERE id='26'");
		
  $tool_content .= "<div id=\"operations_container\">
		<ul id=\"opslist\"><li><a href=\"".$_SERVER['PHP_SELF']."\">$langBack</a></li></ul>
	  </div>";
  $tool_content .= "<table width=\"99%\">
  <tbody>
  <tr><td class=\"success\">$langModifDone.</td></tr>
  </tbody>
  </table><br />";

	} else {
		$tool_content .= "<table width=\"99%\">
		  <tbody>
		<tr><td class=\"caution\" height='60'><p>$langNoCourseTitle</p>
    		<p><a href=\"$_SERVER[PHP_SELF]\">$langAgain</a></p>
		    </td>
		  </tr>
		  </tbody>
		  </table>";
		}
} else {

		$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\">";
		$tool_content .= "<li><a href=\"archive_course.php\">$langBackupCourse</a></li>
  		  <li><a href=\"delete_course.php\">$langDelCourse</a></li>
    		<li><a href=\"refresh_course.php\">$langRefreshCourse</a></li></ul></div>";

		$sql = "SELECT cours_faculte.faculte,
			cours.intitule, cours.description, course_keywords, course_addon,
			cours.visible, cours.fake_code, cours.titulaires, cours.languageCourse, 
			cours.departmentUrlName, cours.departmentUrl, cours.type, cours.password
			FROM `$mysqlMainDb`.cours, `$mysqlMainDb`.cours_faculte 
			WHERE cours.code='$currentCourseID' 
			AND cours_faculte.code='$currentCourseID'";
		$result = mysql_query($sql);
		$leCours = mysql_fetch_array($result);
		$int = $leCours['intitule'];
		$facu = $leCours['faculte'];
		$type = $leCours['type'];
		$visible = $leCours['visible'];
		$visibleChecked[$visible]="checked";
		$fake_code = $leCours['fake_code'];
		$titulary = $leCours['titulaires'];
		$languageCourse	= $leCours['languageCourse'];
		$description = $leCours['description'];
		$course_keywords = $leCours['course_keywords'];
		$course_addon = $leCours['course_addon'];
		$password = $leCours['password'];
		if ($password!="") $checkpasssel = "checked"; else $checkpasssel="";

		@$tool_content .=" <table width=\"99%\" align='left' class='FormData'>
								  <thead><tr><td>
   		<form method='post' action='$_SERVER[PHP_SELF]'>
		  <table width=\"100%\"><tbody>
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
      <td><input type=\"text\" name=\"int\" value=\"$int\" size=\"60\" class='FormData_InputText'></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
    <tr>
      <th class='left'>$langTeachers&nbsp;:</th>
      <td><input type=\"text\" name=\"titulary\" value=\"$titulary\" size=\"60\" class='FormData_InputText'></td>
      <td>&nbsp;</td>
    </tr>
      <th class='left'>$langFaculty&nbsp;:</th>
      <td>
        <select name=\"facu\" class='auth_input'>";
		$resultFac=mysql_query("SELECT id,name FROM `$mysqlMainDb`.faculte ORDER BY number");
		while ($myfac = mysql_fetch_array($resultFac)) {
			if($myfac['name']==$facu)
				$tool_content .= "
        <option value=\"".$myfac['id']."--".$myfac['name']."\" selected>$myfac[name]</option>";
			else
				$tool_content .= "
        <option value=\"".$myfac['id']."--".$myfac['name']."\">$myfac[name]</option>";
		}
		$tool_content .= "</select>
      </td><td>&nbsp;</td></tr>
		   <tr><th class='left'>$m[type]&nbsp;:</th><td>";

      $tool_content .= selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']),'type', $type);
      $tool_content .= "</td><td>&nbsp;</td></tr>
	    <tr>
      <th class='left'>$langDescription&nbsp;:</th>
      <td><textarea name='description' value='".q($leCours['description'])."' cols='57' rows='4' class='FormData_InputText'>".q($leCours['description'])."</textarea></td>
      <td>&nbsp;</td>
    </tr>";
		$tool_content .= "<tr><th class='left'>$langCourseKeywords&nbsp;</th> 
		<td><input type='text' name='course_keywords' value='".q($leCours['course_keywords'])."' size='60' class='FormData_InputText'></td>
			<td>&nbsp;</td>
    </tr>
		<tr>
     <th class='left'>$langCourseAddon&nbsp;</th>
     <td><textarea name='course_addon' value='".q($leCours['course_addon'])."' cols='57' rows='2' class='FormData_InputText'>".q($leCours['course_addon'])."</textarea></td><td>&nbsp;</td>
    </tr>
    </tbody>
    </table><br />";

	$tool_content .= "
    <table width=\"100%\">
    <tbody>
    <tr>
      <th class='left' width='150'>&nbsp;</th>
      <td colspan='2'><b>$langConfidentiality</b></td>
    </tr>
    <tr>
      <th class='left'><img src='../../template/classic/img/OpenCourse.gif' alt='$m[legopen]' title='$m[legopen]' width='16' height='16'>&nbsp;$m[legopen]&nbsp;:</th>
      <td width='1'><input type=\"radio\" name=\"formvisible\" value=\"2\"".@$visibleChecked[2]."></td>
      <td>$langPublic&nbsp;</td>
    <tr>
    <tr>
      <th rowspan='2' class='left'><img src=\"../../template/classic/img/Registration.gif\" alt=\"".$m['legrestricted']."\" title=\"".$m['legrestricted']."\" width=\"16\" height=\"16\">&nbsp;".$m['legrestricted']."&nbsp;:</th>
      <td><input type=\"radio\" name=\"formvisible\" value=\"1\"".@$visibleChecked[1]."></td>
      <td>$langPrivOpen</td> 
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td bgcolor='#F8F8F8'><input type=\"checkbox\" name=\"checkpassword\" ".$checkpasssel.">&nbsp;
            $langOptPassword&nbsp;
            <input type='text' name='password' value='".q($password)."' class='FormData_InputText'>
      </td> 
    </tr>
    <tr>
      <th class='left'><img src='../../template/classic/img/ClosedCourse.gif' alt='$m[legclosed]' title='$m[legclosed]' width='16' height='16'>&nbsp;$m[legclosed]&nbsp;:</th>
      <td><input type='radio' name='formvisible' value='0'".@$visibleChecked[0]."></td>
      <td>$langPrivate&nbsp;</td>
    </tr>
    </tbody>
    </table><br />

    <table width=\"100%\">
    <tbody>
    <tr>
      <th class='left' width='150'>&nbsp;</th>
      <td colspan='2'><b>$langLanguage</b></td>
    </tr>
    <tr>
      <th class='left'>$langOptions&nbsp;:</th>
      <td width='1'>";
		$language = $leCours['languageCourse'];
		$tool_content .= lang_select_options('localize');
		$tool_content .= "
      </td>
      <td><small>$langTipLang</p></td>
    </tr>
    </tbody>
    </table>

    <table width=\"100%\">
    <tbody>
    <tr>
      <th class='left' width='150'>&nbsp;</th>
      <td><input type='Submit' name='submit' value='$langSubmit'></td>
      <td>&nbsp;</td>
    </tr>
    </tbody>
    </table>
</form></td></tr></thead></table>";
	}     // else
}   // if uid==prof_id

// student view
else {
	$tool_content .= "<p>$langForbidden</p>";
}

draw($tool_content,2,'course_info');
