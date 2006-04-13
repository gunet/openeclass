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
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
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
include '../../include/init.php';

$nameTools = $langModifInfo;
begin_page();

####################### SUBMIT #################################
// check if prof logged
if($is_adminOfCourse) {
	// check if form submitted
	if (isset($submit)) {
		@include("../lang/english/create_course.inc");
		@include("../lang/$default_language/create_course.inc");
		@include("../lang/$languageInterface/create_course.inc");
		// UPDATE course settings
		$sql = "UPDATE $mysqlMainDb.cours 
			SET intitule='$int', faculte='$facu', description='$description', 
			visible='$formvisible', titulaires='$titulary', 
			languageCourse='$lanCourseForm',
			departmentUrlName ='$_POST[departmentUrlName]',
			departmentUrl='$_POST[departmentUrl]',
			type='$type'
			WHERE code='$currentCourseID'";
		mysql_query($sql);
		mysql_query("UPDATE `$mysqlMainDb`.cours_faculte SET faculte='$facu' WHERE code='$currentCourseID'");
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
		echo "<font face=\"arial, helvetica\" size=\"2\">$langModifDone.<br><br>
		<a href=\"".$_SERVER['PHP_SELF']."\">$langBack</a> 
		<br><br>
		<a href=\"../../courses/".$currentCourseID."/index.php\">".$langHome."</a>
		<br><br>";
	} else {
	       $sql = "SELECT cours_faculte.faculte, 
			cours.intitule, cours.description,
			cours.visible, cours.fake_code, cours.titulaires, cours.languageCourse, 
			cours.departmentUrlName, cours.departmentUrl, cours.type
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
	    echo "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
		<table width=\"600\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\">
		<tr><td valign=\"top\"><font face=\"arial, helvetica\" size=\"2\">$langCode&nbsp;:</font></td>
		<td valign=\"top\">$fake_code</td></tr>
		<tr><td><font face=\"arial, helvetica\" size=\"2\">$langProfessors&nbsp;:</font></td>
		<td><input type=\"text\" name=\"titulary\" value=\"$titulary\" size=\"60\"></td></tr>
		<tr><td><font face=\"arial, helvetica\" size=\"2\">$langTitle:</font></td>
		<td><input type=\"Text\" name=\"int\" value=\"$int\" size=\"60\"></td></tr>
		<tr><td><font face=\"arial, helvetica\" size=\"2\">$langDescription :</font></td>
		<td><input type=\"Text\" name=\"description\" value=\"$leCours[description]\" size=\"60\"></td></tr>
		<tr><td><font face=\"arial, helvetica\" size=\"2\">$langFaculty :</font></td>
		<td>
		<select name=\"facu\">";
		$resultFac=mysql_query("SELECT name FROM `$mysqlMainDb`.faculte ORDER BY number");
		while ($myfac = mysql_fetch_array($resultFac)) {	
			if($myfac[name]==$facu) echo "<option selected>$myfac[name]</option>";
			else echo "<option>$myfac[name]</option>";
		}
		echo "</select></td></tr>
		<tr><td><font face=\"arial, helvetica\" size=\"2\">$m[type]:</font></td><td>";
		selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']),'type', $type);
		echo "</td></tr><tr><td><font face=\"arial, helvetica\" size=\"2\">$langDepartmentUrlName&nbsp;:</font></td>
		<td><input type=\"text\" name=\"departmentUrlName\" value=\"$departmentUrlName\" size=\"60\" maxlength=\"60\"></td>
		</tr>
		<tr><td><font face=\"arial, helvetica\" size=\"2\">$langDepartmentUrl&nbsp;:</font></td>
		<td><input type=\"text\" name=\"departmentUrl\" value=\"$departmentUrl\" size=\"60\" maxlength=\"180\"></td></tr>
		<tr><td colspan=\"2\"><hr noshade size=\"1\">
		<font face=\"arial, helvetica\" size=\"2\"><b>$langConfidentiality</b></font>
		</td></tr><tr>
		<td colspan=\"2\"><font face=\"arial, helvetica\" size=\"2\">$langConfTip</font></td></tr>
		<tr><td align=\"right\"><input type=\"radio\" name=\"formvisible\" value=\"2\"".@$visibleChecked[2]."></td>
		<td><font face=\"arial, helvetica\" size=\"2\">$langPublic</font></td></tr>
		<tr><td align=\"right\"><input type=\"radio\" name=\"formvisible\" value=\"1\"".@$visibleChecked[1]."></td>
		<td><font face=\"arial, helvetica\" size=\"2\">$langPrivOpen</font></td></tr>
		<tr><td align=\"right\"><input type=\"radio\" name=\"formvisible\" value=\"0\"".@$visibleChecked[0]."></td>
		<td><font face=\"arial, helvetica\" size=\"2\">$langPrivate</font></td></tr>
		<tr><td colspan=\"2\"><hr noshade size=\"1\"><font face=\"arial, helvetica\" size=\"2\"><b>$langLanguage</b></font></td></tr>
		<tr><td colspan=\"2\"><font face=\"arial, helvetica\" size=\"2\">$langTipLang</font></td></tr>
		<tr><td colspan=\"2\"><font face=\"arial, helvetica\" size=\"2\">
		
		<select name=\"lanCourseForm\">";
		// determine past language of the course 

		$dirname = "../lang/";
		if($dirname[strlen($dirname)-1]!='/') $dirname.='/';
		$handle=opendir($dirname);
		while ($entries = readdir($handle)) {
			if ($entries=='.'||$entries=='..'||$entries=='CVS')
			continue;
			if (is_dir($dirname.$entries)) {
				echo "<option value=\"$entries\"";
				if ($entries == $languageCourse) 
					echo " selected ";
				echo ">$entries"; 
				if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
					echo " - $langNameOfLang[$entries]";
				echo "</option>";
			}
		}	
		closedir($handle);
		echo "</select></font></td></tr>
		<tr><td colspan=\"2\"><input type=\"Submit\" name=\"submit\" value=\"$langOk\"></td></tr>
		<tr><td colspan=\"2\"><hr noshade size=\"1\">
		<font face=\"arial, helvetica\" size=\"2\"><a href=\"archive_course.php\">$langBackupCourse</a>
		<br><br>
		<a href=\"delete_course.php\">$langDelCourse</a>	
		<br><br>
		<a href=\"refresh_course.php\">$langRefreshCourse</a>
                </font>
		</td>
		</tr>
		</table>
		</form>";
	}     // else
}   // if uid==prof_id

// student view
else {
	echo "<font face=\"arial, helvetica\" size=\"2\">$langForbidden</font>";
}  

echo "</td></tr><tr><td colspan=\"2\"><hr noshade size=\"1\"></td></tr></table>";
?>
</body>
</html>
