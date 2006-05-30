<?

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$        |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Geschι <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

/******************************************************
*                  MODIFY COURSE INFO                 *
*******************************************************


Modify course settings like:
    1. Course title
    2. Department
    3. Course description URL in the university web

*/

// If course language was changed, we need to include message files for the new language
if(isset($submit)) {
	$language_override=$lanCourseForm;
}

$require_current_course = TRUE;
$langFiles = array('course_info', 'create_course', 'opencours');
include '../../include/baseTheme.php';
$nameTools = $langModifInfo;

$tool_content = "";

####################### SUBMIT #################################
// check if prof logged
if($is_adminOfCourse) {
	// check if form submitted
	if (isset($submit)) {
		@include("../lang/english/create_course.inc");
		@include("../lang/$default_language/create_course.inc");
		@include("../lang/$languageInterface/create_course.inc");
		// UPDATE course settings
		if ($checkpassword=="on" && $formvisible=="1") {
			$password = $password;
		} else {
			$password = "";
		}
		list($facid, $facname) = split("--", $facu);
		$sql = "UPDATE $mysqlMainDb.cours 
			SET intitule='$int', 
				faculte='$facname', 
				description='$description',
				course_objectives='$course_objectives',
				course_prerequisites='$course_prerequisites',
				course_references='$course_references',
				course_keywords='$course_keywords', 
				visible='$formvisible', 
				titulaires='$titulary', 
				languageCourse='$lanCourseForm',
				departmentUrlName ='$_POST[departmentUrlName]',
				departmentUrl='$_POST[departmentUrl]',
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
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langVideoLinks' WHERE id='6'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langAnnouncements' WHERE id='7'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langUsers' WHERE id='8'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langForums' WHERE id='9'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langExercices' WHERE id='10'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langStatistics' WHERE id='11'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langAddPageHome' WHERE id='12'");	
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langLinkSite' WHERE id='13'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langModifyInfo' WHERE id='14'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langGroups' WHERE id='15'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langDropBox' WHERE id='16'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langChat' WHERE id='19'");
		mysql_query("UPDATE `$currentCourseID`.accueil SET rubrique='$langCourseDesc' WHERE id='20'");
		$tool_content .= "<p>$langModifDone.</p>
		<center><p><a href=\"".$_SERVER['PHP_SELF']."\">$langBack</a></p></center><br>
		<center><p><a href=\"../../courses/".$currentCourseID."/index.php\">".$langHome."</a></p></center>";
	} else {
	  $sql = "SELECT cours_faculte.faculte, 
			cours.intitule, cours.description, course_keywords, course_objectives, course_prerequisites, course_references,
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
	  $departmentUrlName = $leCours['departmentUrlName'];
	  $departmentUrl = $leCours['departmentUrl'];
	  $course_keywords = $leCours['course_keywords'];
	  $course_objectives = $leCours['course_objectives'];
	  $course_prerequisites = $leCours['course_prerequisites'];
	  $course_references = $leCours['course_references'];
	  $password = $leCours['password'];
	  if ($password!="") $checkpasssel = "checked"; else $checkpasssel="";
	    
	  $tool_content .="<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
		<table width=\"99%\"><caption>Γενικές Πληροφορίες</caption><tbody>
		<tr><td valign=\"top\"><b>$langCode:</b></td>
		<td valign=\"top\">$fake_code</td></tr>
		<tr><td><b>$langProfessors:</b></td>
		<td><input type=\"text\" name=\"titulary\" value=\"$titulary\" size=\"60\"></td></tr>
		<tr><td><b>$langTitle:</b></td>
		<td><input type=\"Text\" name=\"int\" value=\"$int\" size=\"60\"></td></tr>
		<tr><td><b>$langDescription:</b></td>
		<td><input type=\"Text\" name=\"description\" value=\"$leCours[description]\" size=\"60\"></td></tr>
		<tr><td><b>$langFaculty:</b></td>
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
		<tr><td><b>$m[type]:</b></td><td>";

		$tool_content .= selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']),'type', $type);
		
		$tool_content .= "</td></tr><tr><td><b>$langDepartmentUrlName:</b></td>
		<td><input type=\"text\" name=\"departmentUrlName\" value=\"$departmentUrlName\" size=\"60\" maxlength=\"60\"></td>
		</tr>
		<tr><td><b>$langDepartmentUrl:</b></td>
		<td><input type=\"text\" name=\"departmentUrl\" value=\"$departmentUrl\" size=\"60\" maxlength=\"180\"></td></tr>		
		<tr>
			<td><b>$langcourse_objectives:</b></td>
			<td><input type=\"Text\" name=\"course_objectives\" value=\"$leCours[course_objectives]\" size=\"60\"></td>
		</tr>
		<tr>
			<td><b>$langcourse_prerequisites:</b></td>
			<td><input type=\"Text\" name=\"course_prerequisites\" value=\"$leCours[course_prerequisites]\" size=\"60\"></td>
		</tr>
		<tr>
			<td><b>$langcourse_references:</b></td>
			<td><input type=\"Text\" name=\"course_references\" value=\"$leCours[course_references]\" size=\"60\"></td></tr>
		</tr>
		<tr>
			<td><b>$langcourse_keywords:</b></td>
			<td><input type=\"Text\" name=\"course_keywords\" value=\"$leCours[course_keywords]\" size=\"60\"></td>
		</tr>		
		</tbody></table><br><table width=\"99%\"><caption>$langConfidentiality</caption><tbody>
		<tr>	<td colspan=\"2\"><i>$langConfTip</i></td></tr>
		<tr><td align=\"right\"><input type=\"radio\" name=\"formvisible\" value=\"2\"".@$visibleChecked[2]."></td>
		<td>$langPublic</td></tr>
		<tr><td align=\"right\" valign=\"top\"><input type=\"radio\" name=\"formvisible\" value=\"1\"".@$visibleChecked[1]."></td>
		<td>$langPrivOpen<br>&nbsp;&nbsp;<input type=\"checkbox\" name=\"checkpassword\" ".$checkpasssel.">Προαιρετικό συνθηματικό: <input type=\"text\" name=\"password\" value=\"".$password."\"></td></tr>
		<tr><td align=\"right\"><input type=\"radio\" name=\"formvisible\" value=\"0\"".@$visibleChecked[0]."></td>
		<td>$langPrivate</td></tr>
		</tbody></table><br><table width=\"99%\"><caption>$langLanguage</caption><tbody>
		<tr><td><i>$langTipLang</i></td></tr>
		<tr><td>		
		<select name=\"lanCourseForm\">";

		// determine past language of the course 

		$dirname = "../lang/";
		if($dirname[strlen($dirname)-1]!='/') $dirname.='/';
		$handle=opendir($dirname);
		while ($entries = readdir($handle)) {
			if ($entries=='.'||$entries=='..'||$entries=='CVS')
			continue;
			if (is_dir($dirname.$entries)) {
				$tool_content .= "<option value=\"$entries\"";
				if ($entries == $languageCourse) 
					$tool_content .= " selected ";
				$tool_content .= ">$entries"; 
				if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
					$tool_content .= " - $langNameOfLang[$entries]";
				$tool_content .= "</option>";
			}
		}	
		closedir($handle);
		$tool_content .= "</select></td></tr>
		</tbody></table><br><p><input type=\"Submit\" name=\"submit\" value=\"$langOk\"></p><br>
		<table width=\"99%\"><caption>Αλλες Ενέργειες</caption><tbody>
		<tr><td><a href=\"archive_course.php\">$langBackupCourse</a></td></tr>
		<tr><td><a href=\"delete_course.php\">$langDelCourse</a>	</td></tr>
		<tr><td><a href=\"refresh_course.php\">$langRefreshCourse</a></td></tr>
		</tbody></table></form>";
	}     // else
}   // if uid==prof_id

// student view
else {
	$tool_content .= "<p>$langForbidden</p>";
}  

draw($tool_content,2,'admin');
?>