<?

/*
      +----------------------------------------------------------------------+
      | e-Class version 1.2                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$                   |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
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
      | e-Class Authors:    Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      |		   (v2 changes)	Sakis Agorastos <th_agorastos@hotmail.com>	     |
      |                                                                      |
      | Claroline Authors:  Thomas Depraetere <depraetere@ipm.ucl.ac.be>     |
      |                     Hugues Peeters    <peeters@ipm.ucl.ac.be>        |
      |                     Christophe Geschι <gesche@ipm.ucl.ac.be>         |
      |                                                                      |
      +----------------------------------------------------------------------+
*/

/**
 * COURSE SITE CREATION TOOL
 * GOALS
 * *******
 * Allow professors and administrative staff to create course sites.
 * This big script makes, basically, 6 things:
 *     1. Create a database whose name=course code (sort of course id)
 *     2. Create tables in this base and fill some of them
 *     3. Create a www directory with the same name as the db name
 *     4. Add the course to the main icampus/course table
 *     5. Check whether the course code is not already taken.
 *     6. Associate the current user id with the course in order to let 
 *        him administer it.
 * 
 * One of the functions of this script is to merge the different 
 * Open Source Tools used in the courses (statistics by EzBoo, 
 * forum by phpBB...) under one unique user session and one unique 
 * course id.
 * ******************************************************************
 */

$require_login = TRUE;
$require_prof = TRUE;

$langFiles = array('create_course', 'opencours');

$local_head = "<script language=\"javascript\">
function previous_step()
{
	document.location.href = \"./create_course.php\";
}
</script>";

//ektypwnei ena <td> </td> me hyperlink pros to help me vash kapoio $topic
function help ($topic)
{
	echo"
	<td valign=\"middle\">
		<a href=\"../help/help.php?topic=$topic\" onclick=\"window.open('../help/help.php?topic=$topic','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../images/help.gif\" border=\"0\"></a></td>";
}


include '../../include/init.php';

$titulaire_probable="$prenom $nom";
$local_style = "input { font-size: 12px; }";
begin_page($langCreateSite);

###################### FORM  #########################################

    echo "
<!-- S T E P  2   [start] -->    

<tr bgcolor=\"$color1\">
	<td>
		<table bgcolor=\"$color1\" border=\"2\">
			<tr valign=\"top\" align=\"middle\">
				<td colspan=\"3\" valign=\"middle\">
					<table width=\"100%\">
						<tr>
							<td align=\"left\">
								<font face=\"arial, helvetica\" size=\"4\" color=\"gray\">$langCreateCourse</font>
							</td>
							<td align=\"right\">
								<font face=\"arial, helvetica\" size=\"4\" color=\"gray\">$langCreateCourseStep&nbsp;2&nbsp;$langCreateCourseStep2&nbsp;3</font>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr valign=\"top\">
				<td colspan=\"5\" valign=\"middle\">
					<font face=\"arial, helvetica\" size=\"2\"><b>$langCreateCourseStep2Title</b></font>
					<hr>
					<font face=\"arial, helvetica\" size=\"2\">$langFieldsRequ</font>
				</td>
			</tr>
			<tr><td colspan=\"3\">&nbsp;</td></tr>

<form method=\"post\" action=\"create_course3.php\">
	
	<input type=\"hidden\" name=\"intitule\" value=\"$intitule\">
	<input type=\"hidden\" name=\"faculte\" value=\"$faculte\">
	<input type=\"hidden\" name=\"description\" value=\"$description\">
	<input type=\"hidden\" name=\"titulaires\" value=\"$titulaires\">
	

	<tr>
	<td colspan=\"3\">
		<table bgcolor=\"$color1\" border=\"2\">
			<tr>
				<td valign=\"top\" align=\"right\">		
					<font face=\"arial, helvetica\" size=\"2\"><b>Στόχοι του μαθήματος:</b></font>
				</td>
				<td valign=\"top\">
					<font face=\"arial, helvetica\" size=\"2\">
				<textarea name=\"course_objectives\" cols=\"30\" rows=\"5\"></textarea>$langFieldsRequAsterisk<br>$langObjectives</font>
				</td>
				<td>";
					help("CreateCourse_course_objectives");
echo "					
				</td>
			</tr>
			<tr>				
				<td valign=\"top\" align=\"right\">		
					<font face=\"arial, helvetica\" size=\"2\"><b>Εισαγωγικό Σημείωμα:</b></font>
				</td>
				<td valign=\"top\">
					<textarea name=\"course_intronote\" cols=\"30\" rows=\"5\"></textarea><br>$langIntroNote
				</td>
				<td>";
					help("CreateCourse_course_intronote");
echo "
				</td>
			</tr>
		</table>
	</td>
	</tr>
	<tr>
		<td align=\"left\">
			<input type=\"button\" name=\"button\" value=\"$langPreviousStep\" onclick=\"previous_step();\">
		</td>
		<td align=\"right\">
			<input type=\"Submit\" name=\"submit\" value=\"$langNextStep\">
		</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
</form>";

?>
</body>
</html>
