<?

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    	Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    	Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	create_course.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: 1st step for the Create New Course Wizard

 	The script requires some fields to be filled-in, thus it checks the
 	validity of the entries by javascripts.
==============================================================================*/


$require_login = TRUE;
$require_prof = TRUE;

$langFiles = array('create_course', 'opencours');

$require_help = TRUE;
$helpTopic = 'CreateCourse';

$local_head = "

<script language=\"JavaScript\">

function validate()
{
	if (document.forms[0].intitule.value==\"\")
 	{
   		alert(\"Παρακαλώ συμπληρώστε τον τίτλο του μαθήματος!\"); 
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
		<a href=\"../help/help.php?topic=$topic\" onclick=\"window.open('../help/help.php?topic=$topic','help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;\"><img src=\"../../template/classic/img/help.gif\" border=\"0\"></a>
	</td>
	"
*/

include '../../include/baseTheme.php';

$nameTools = $langCreateCourse;

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
$tool_content .= "
<!-- S T E P  1   [start] -->  

<tr bgcolor=\"$color1\">
	<td>
		<table bgcolor=\"$color1\" border=\"2\" width=\"99%\">
			<tr valign=\"top\" align=\"middle\">
				<td colspan=\"3\" valign=\"middle\" align=\"center\">
					<font face=\"arial, helvetica\" size=\"4\" color=\"gray\">$langCreateCourseStep&nbsp;1&nbsp;$langCreateCourseStep2&nbsp;3</font>
				</td>
			</tr>			
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
					&nbsp;
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

$tool_content .=  "$langFieldsRequAsterisk<br><font face=\"arial, helvetica\" size=\"2\">$langTargetFac</font>
		</td>
		<td valign=\"middle\">
			&nbsp;
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
		&nbsp;
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
		&nbsp;
	</td>
	<tr>
		<td>
			&nbsp;
		</td>
		<td align=\"center\">
			<input type=\"Submit\" name=\"submit\" value=\"$langNextStep\">
		</td>
		<td align=\"right\">
			&nbsp;
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
