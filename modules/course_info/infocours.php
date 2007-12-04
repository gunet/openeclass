<? 
// if we come from the home page
if (isset($from_home) and ($from_home == TRUE) and isset($_GET['cid'])) {
        session_start();
        $dbname = $cid;
        session_register("dbname");
}

$require_current_course = TRUE;
$require_prof = true;
$langFiles = array('course_info', 'create_course', 'opencours');
if (isset($localize)) {
	if ($localize == 'el')
		$newlang = 'greek';
	else
		$newlang = 'english';
	$language = $newlang;
}

include '../../include/baseTheme.php';

if(isset($newlang)) {
			include ($webDir."modules/lang/$newlang/messages.inc.php");
		}
$nameTools = $langModifInfo;
$tool_content = "";

// submit

if($is_adminOfCourse) {

	if (isset($submit)) {
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
				description='$description',
				course_addon='$course_addon',
				course_keywords='$course_keywords', 
				visible='$formvisible', 
				titulaires='$titulary', 
				languageCourse='$newlang',
				type='$type',
				password='$password',
				faculteid='$facid'
			WHERE code='$currentCourseID'";
		mysql_query($sql);
		mysql_query("UPDATE `$mysqlMainDb`.cours_faculte SET faculte='$facname', facid='$facid' WHERE code='$currentCourseID'");
		// UPDATE Home Page Menu Titles for new language
		mysql_select_db("$currentCourseID",$db);
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
		
  $tool_content .= "
  <div id=\"operations_container\">
  <ul id=\"opslist\">
    <li><a href=\"".$_SERVER['PHP_SELF']."\">$langModifInfo</a></li>
    <li><a href=\"../../courses/".$currentCourseID."/index.php\">".$langHome."</a></li>
  </ul>
  </div>";
  $tool_content .= "
  <table width=\"99%\">
  <tbody>
  <tr>
    <td class=\"success\">$langModifDone.</td>
  </tr>
  </tbody>
  </table><br />
";
		$tool_content .= "
		<center><p></center><br>
		<center><p></p></center>";
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

		@$tool_content .="
  <table width=\"99%\" align='left' class='FormData'>
  <thead>
  <tr>
    <td>
    <form method='post' action='$_SERVER[PHP_SELF]'>
  
    <table width=\"100%\">
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
      <th class='left'>$langProfessors&nbsp;:</th>
      <td><input type=\"text\" name=\"titulary\" value=\"$titulary\" size=\"60\" class='FormData_InputText'></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th class='left'>$langCourseTitle&nbsp;:</th>
      <td><input type=\"Text\" name=\"int\" value=\"$int\" size=\"60\" class='FormData_InputText'></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
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
		$tool_content .= "
        </select>
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th class='left'>$m[type]&nbsp;:</th>
      <td>";
      $tool_content .= selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']),'type', $type);
      $tool_content .= "
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th class='left'>$langDescription&nbsp;:</th>
      <td><textarea name=\"description\" value=\"$leCours[description]\" cols=\"40\" rows=\"4\" class='FormData_InputText'>$leCours[description]</textarea></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th class='left'>$langcourse_keywords&nbsp;:</th>
      <td><input type='text' name=\"course_keywords\" value=\"$leCours[course_keywords]\" size=\"60\" class='FormData_InputText'></td>
      <td>&nbsp;</td>
    </tr>
    </tbody>
    </table><br />

    <table width=\"100%\">
    <tbody>
    <tr>
      <th class='left' width='150'>&nbsp;</th>
      <td colspan='2'><b>$langConfidentiality</b></td>
    </tr>
    <tr>
      <th class='left'><img src=\"../../images/OpenCourse.gif\" alt=\"".$m['legopen']."\" title=\"".$m['legopen']."\" width=\"16\" height=\"16\">&nbsp;".$m['legopen']."&nbsp;:</th>
      <td width='1'><input type=\"radio\" name=\"formvisible\" value=\"2\"".@$visibleChecked[2]."></td>
      <td>$langPublic&nbsp;</td>
    </tr>
    <tr>
      <th class='left'><img src=\"../../images/Registration.gif\" alt=\"".$m['legrestricted']."\" title=\"".$m['legrestricted']."\" width=\"16\" height=\"16\">&nbsp;".$m['legrestricted']."&nbsp;:</th>
      <td><input type=\"radio\" name=\"formvisible\" value=\"1\"".@$visibleChecked[1]."></td>
      <td>$langPrivOpen<br />
            <p align='right'><input type=\"checkbox\" name=\"checkpassword\" ".$checkpasssel.">&nbsp;
            $langOptPassword&nbsp;
            <input type=\"text\" name=\"password\" value=\"".$password."\" class='FormData_InputText'></p></td>
      </td> 
    </tr>
    <tr>
      <th class='left'><img src=\"../../images/ClosedCourse.gif\" alt=\"".$m['legclosed']."\" title=\"".$m['legclosed']."\" width=\"16\" height=\"16\">&nbsp;".$m['legclosed']."&nbsp;:</th>
      <td><input type=\"radio\" name=\"formvisible\" value=\"0\"".@$visibleChecked[0]."></td>
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
		if ($leCours['languageCourse'] == 'english')
			$curLang = 'en';
		else
			$curLang = 'el';
		$tool_content .= selection(array('el' => $langNameOfLang['greek'],
			'en' => $langNameOfLang['english']),'localize', $curLang);

		$tool_content .= "
      </td>
      <td><p align='right'><small>$langTipLang&nbsp;</small></p></td>
    </tr>
    </tbody>
    </table>

    <table width=\"100%\">
    <tbody>
    <tr>
      <th class='left' width='150'>&nbsp;</th>
      <td><input type=\"Submit\" name=\"submit\" value=\"$langSubmit\"></td>
      <td>&nbsp;</td>
    </tr>
    </tbody>
    </table>

    </form>
    </td>
  </tr>
  </thead>
  </table>
";
	}     // else
}   // if uid==prof_id

// student view
else {
	$tool_content .= "<p>$langForbidden</p>";
}

draw($tool_content,2,'course_info');
?>
