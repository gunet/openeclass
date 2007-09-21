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
			$inclPath =  $webDir."modules/lang/$newlang/course_info.inc.php";
			include($inclPath);
		}
$nameTools = $langModifInfo;
$tool_content = "";

// submit

if($is_adminOfCourse) {

	if (isset($submit)) {
		if(isset($newlang)) {
			$inclPath =  $webDir."modules/lang/$newlang/create_course.inc.php";
			include($inclPath);
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
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langCourseDesc' WHERE id='20'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langQuestionnaire' WHERE id='21'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langLearnPath' WHERE id='23'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langUsage' WHERE id='24'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langToolManagement' WHERE id='25'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langWiki' WHERE id='26'");
		
		$tool_content .= "<p>$langModifDone.</p>
		<center><p><a href=\"".$_SERVER['PHP_SELF']."\">$langBack</a></p></center><br>
		<center><p><a href=\"../../courses/".$currentCourseID."/index.php\">".$langHome."</a></p></center>";
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
	  
	  <form method='post' action='$_SERVER[PHP_SELF]'>
		<FIELDSET>
    <LEGEND><span class='labeltext'><b>$langCourseIden</b></span></LEGEND>
		<table width=\"100%\" align=center cellpadding=\"1\" cellspacing=\"0\" border=\"0\">		

		<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' valign=\"top\">$langCode&nbsp;:</th>
    <td valign=\"top\">$fake_code</td></tr>
    <tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langProfessors&nbsp;:</th>
    <td><input type=\"text\" name=\"titulary\" value=\"$titulary\" size=\"60\" class=auth_input></td></tr>
    <tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langCourseTitle:</th>
    <td><input type=\"Text\" name=\"int\" value=\"$int\" size=\"60\" class=auth_input></td></tr>
    <tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langFaculty :</th>
		<td>
		<select name=\"facu\">";

		$resultFac=mysql_query("SELECT id,name FROM `$mysqlMainDb`.faculte ORDER BY number");
		while ($myfac = mysql_fetch_array($resultFac)) {
			if($myfac['name']==$facu)
				$tool_content .= "<option value=\"".$myfac['id']."--".$myfac['name']."\" selected>$myfac[name]</option>";
			else
				$tool_content .= "<option value=\"".$myfac['id']."--".$myfac['name']."\">$myfac[name]</option>";
		}
		$tool_content .= "</select></td></tr>
		<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$m[type]:</th><td>";

		$tool_content .= selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']),'type', $type);

		$tool_content .= "</td></tr>";

 		@$tool_content .= "<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langDescription:</th>
		<td><textarea name=\"description\" value=\"$leCours[description]\" cols=\"40\" rows=\"4\">$leCours[description]</textarea></td></tr>
		<tr>
			<th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langcourse_references:</th>
			<td><textarea name=\"course_addon\" value=\"$leCours[course_addon]\" cols=\"40\" rows=\"4\">$leCours[course_addon]</textarea></td>
		</tr>
		<tr>
			<th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langcourse_keywords:</th>
			<td><input type='text' name=\"course_keywords\" value=\"$leCours[course_keywords]\" size=\"60\"></td>
		</tr>		
		</thead></table></fieldset>
		<br>
		<FIELDSET>
    <LEGEND><span class='labeltext'><b>$langConfidentiality</b></span></LEGEND>
		<table>
		<tr>
    <td colspan=\"2\"><span class='explanationtext'>$langConfTip</span></td></tr>
    <tr><td align=\"right\"><input type=\"radio\" name=\"formvisible\" value=\"2\"".@$visibleChecked[2]."></td>
    <td><span class='labeltext'>$langPublic</span></td></tr>
	  <tr><td align=\"right\"><input type=\"radio\" name=\"formvisible\" value=\"1\"".@$visibleChecked[1]."></td>
    <td><span class='labeltext'>$langPrivOpen</span>
		<br><input type=\"checkbox\" name=\"checkpassword\" ".$checkpasssel.">$langOptPassword
		<input type=\"text\" name=\"password\" value=\"".$password."\"></td>
    <tr><td align=\"right\"><input type=\"radio\" name=\"formvisible\" value=\"0\"".@$visibleChecked[0]."></td>
    <td><span class='labeltext'>$langPrivate</span>
    </td></tr>
    </table>
    </FIELDSET>
		<br>
		<FIELDSET>
    <LEGEND><span class='labeltext'><b>$langLanguage</b></span></LEGEND>
    <table>
		<tr><td><span class='explanationtext'>$langTipLang</span></td>
		<td><span class='labeltext'>";
		if ($leCours['languageCourse'] == 'english')
			$curLang = 'en';
		else
			$curLang = 'el';
		$tool_content .= selection(array('el' => $langNameOfLang['greek'],
			'en' => $langNameOfLang['english']),'localize', $curLang);

		$tool_content .= "</td></tr>";
		$tool_content .= "</table></fieldset>

		<br>

		<div align=center><input type=\"Submit\" name=\"submit\" value=\"$langSubmit\"></div>
		</form>";
	}     // else
}   // if uid==prof_id

// student view
else {
	$tool_content .= "<p>$langForbidden</p>";
}

draw($tool_content,2,'course_info');
?>
