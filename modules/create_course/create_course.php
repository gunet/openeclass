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



$local_head = "

<script language=\"JavaScript\">

function validate()
{
	if (document.forms[0].intitule.value==\"\")
 	{
   		alert(\"Παρακαλώ συμπληρώστε τον τίτλο του μαθήματος!\"); 
   		return false;
 	}
 	
 	if (document.forms[0].description.value==\"\")
 	{
   		alert(\"Παρακαλώ συμπληρώστε την περιγραφή του μαθήματος!\"); 
   		return false;
 	}
 	
 	if (document.forms[0].titulaires.value==\"\")
 	{
   		alert(\"Παρακαλώ συμπληρώστε τουλάχιστον έναν διαχειριστή για το μάθημα!\"); 
   		return false;
 	}
 	

 	return true;
}
 
</script>

";


//ektypwnei ena <td> </td> me hyperlink pros to help me vash kapoio $topic
/*
	"
	<td valign=\"middle\">
		<a href=\"../help/help.php?topic=$topic\" onclick=\"window.open('../help/help.php?topic=$topic','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../images/help.gif\" border=\"0\"></a>
	</td>
	"
*/



//include '../../include/init.php';
include '../../include/baseTheme.php';

$tool_content = "";

$titulaire_probable="$prenom $nom";
$local_style = "input { font-size: 12px; }";
//begin_page($langCreateSite);

	//ean to mathima hdh yparxei emfanise mhnyma (elegxos mesw $_GET)
	if(isset($_GET["course_exists"]))
	{
		@include("../lang/$language/create_course.inc.php");

		$tool_content .=  "<tr bgcolor=\"$color2\">
		<td colspan=\"3\" valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">
			$langCodeTaken.
		</td></tr>";
	}

###################### FORM  #########################################
$tool_content .=  "
<!-- S T E P  1   [start] -->  

<tr bgcolor=\"$color1\">
	<td>
		<table bgcolor=\"$color1\" border=\"2\" width=\"99%\">
			<tr valign=\"top\" align=\"middle\">
				<td colspan=\"3\" valign=\"middle\">
					<table width=\"99%\">
						<tr>
							<td align=\"left\">
								<font face=\"arial, helvetica\" size=\"4\" color=\"gray\">$langCreateCourse</font>
							</td>
							<td align=\"right\">
								<font face=\"arial, helvetica\" size=\"4\" color=\"gray\">$langCreateCourseStep&nbsp;1&nbsp;$langCreateCourseStep2&nbsp;3</font>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr valign=\"top\">
				<td colspan=\"5\" valign=\"middle\">
					<font face=\"arial, helvetica\" size=\"2\"><b>$langCreateCourseStep1Title</b></font>
					<hr>
					<font face=\"arial, helvetica\" size=\"2\">$langFieldsRequ</font>
				</td>
			</tr>
			<tr><td colspan=\"3\">&nbsp;</td></tr>
			<tr valign=\"top\">
				<td valign=\"top\" align=\"right\">
					<form method=\"post\" action=\"create_course2.php\" onsubmit=\"return validate();\">
					<font face=\"arial, helvetica\" size=\"2\"><b>$langTitle:</b></font>
				</td>
				<td valign=\"top\">
					<font face=\"arial, helvetica\" size=\"2\">
					<input type=\"Text\" name=\"intitule\" size=\"60\">$langFieldsRequAsterisk<br>$langEx</font>
				</td>
				<td valign=\"middle\">
					<a href=\"../help/help.php?topic=CreateCourse_Title\" onclick=\"window.open('../help/help.php?topic=CreateCourse_Title','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../images/help.gif\" border=\"0\"></a>
				</td>
			</tr>
		</td>
	<tr>
		<td valign=\"top\" align=\"right\">
			<font face=\"arial, helvetica\" size=\"2\"><b>$langFac:</b></font>
		</td>
		<td valign=\"top\">";


$tool_content .=  "		<select name=\"faculte\">";
	    
		$resultFac=mysql_query("SELECT id,name FROM `$mysqlMainDb`.faculte ORDER BY number");
		while ($myfac = mysql_fetch_array($resultFac)) {	
			//if($myfac['name']==$facu)
			//	$tool_content .=  "<option value=\"".$myfac['id']."--".$myfac['name']."\" selected>$myfac[name]</option>";
			//else
				$tool_content .=   "<option value=\"".$myfac['id']."--".$myfac['name']."\">$myfac[name]</option>";
		}
		$tool_content .=  "</select>";


/* $tool_content .=  "<select name=\"faculte\">";

$resultFac=mysql_query("SELECT name FROM faculte ORDER BY number");

while ($myfac = mysql_fetch_array($resultFac)) {	
	if($myfac['name'] == $facu) 
		$tool_content .=  "<option selected>$myfac[name]</option>";
	else 
		$tool_content .=  "<option>$myfac[name]</option>";
}
$tool_content .=  "</select>"; */

$tool_content .=  "$langFieldsRequAsterisk<br><font face=\"arial, helvetica\" size=\"2\">$langTargetFac</font>
		</td>
		<td valign=\"middle\">
			<a href=\"../help/help.php?topic=CreateCourse_faculte\" onclick=\"window.open('../help/help.php?topic=CreateCourse_faculte','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../images/help.gif\" border=\"0\"></a>
		</td>
	</tr>
       <tr valign=\"top\">
       <td width=\"100\" valign=\"top\" align=\"right\">
       <font face=\"arial, helvetica\" size=\"2\"><b>$langDescrInfo:</b></font>
       </td>
       <td valign=top>
       <font face=\"arial, helvetica\" size=\"2\">
       <textarea name=\"description\" cols=\"30\" rows=\"4\"></textarea>
	   $langFieldsRequAsterisk
       <br>$langDescrInfo
       </font>
       </td>
       <td valign=\"middle\">
			<a href=\"../help/help.php?topic=CreateCourse_description\" onclick=\"window.open('../help/help.php?topic=CreateCourse_description','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../images/help.gif\" border=\"0\"></a>
		</td>
      </tr>";

unset($repertoire);

/* SAKIS:  edw na valw ta kwdikia gia thn epilogh twn diaxeiristwn!!!!!! */

$tool_content .=  "<tr valign=\"top\">
	<td width=\"100\" valign=\"top\" align=\"right\">
	<font face=\"arial, helvetica\" size=\"2\"><b>$langProfessors:</b></font>
	</td>
	<td valign=\"top\">
	<input type=\"Text\" name=\"titulaires\" size=\"60\" value=\"$titulaire_probable\"><font face=\"arial, helvetica\" size=\"2\">$langFieldsRequAsterisk<br>$langProfessorsInfo</font>
	</td>
	<td valign=\"middle\">
		<a href=\"../help/help.php?topic=CreateCourse_titulaires\" onclick=\"window.open('../help/help.php?topic=CreateCourse_titulaires','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../images/help.gif\" border=\"0\"></a>
	</td>
	</tr>
	<tr>
	<td align=\"right\"><font face=\"arial, helvetica\" size=\"2\"><b>$m[type]:</b></font></td>
	<td>
		<select name = \"type\">
			<option value=\"pre\">	".$m['pre']."</option>
			<option value=\"post\"> ".$m['post']."</option>
			<option value=\"other\">".$m['other']."</option>
		</select>
		
		";
//selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']), 'type');

$tool_content .=  "
	<font face=\"arial, helvetica\" size=\"2\">$langFieldsRequAsterisk<br>$langCourseCategory</br></td>
	<td valign=\"middle\">
		<a href=\"../help/help.php?topic=CreateCourse_Type\" onclick=\"window.open('../help/help.php?topic=CreateCourse_Type','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../images/help.gif\" border=\"0\"></a>
	</td>
	<tr>
		<td>
			&nbsp;
		</td>
		<td align=\"left\">
			<input type=\"button\" disabled=\"disabled\" name=\"button\" value=\"$langPreviousStep\">
		</td>
		<td align=\"right\">
			<input type=\"Submit\" name=\"submit\" value=\"$langNextStep\">
		</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
</form>
</body>
</html>";

draw($tool_content, '1', '', $local_head);
?>