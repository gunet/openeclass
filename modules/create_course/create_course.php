<?

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
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
* @version $Id$
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

$nameTools = $langCreateCourse . " (" . $langCreateCourseStep ." 1 " .$langCreateCourseStep2 . " 3 )" ;

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
<p><b>$langCreateCourseStep1Title</b> <em>($langFieldsRequ)</em> </p>
<form method=\"post\" action=\"create_course2.php\" onsubmit=\"return validate();\">
	<table width=\"99%\">
		<thead>

			<tr>
				<th>
					$langTitle:
				</th>
				<td>
					
					<input type=\"Text\" name=\"intitule\" size=\"60\">$langFieldsRequAsterisk ($langEx)
				</td>
			</tr>
		
	<tr>
		<th>
			$langFac:
		</th>
		<td>";


$tool_content .=  "		<select name=\"faculte\">";
	    
		$resultFac=mysql_query("SELECT id,name FROM `$mysqlMainDb`.faculte ORDER BY number");
		while ($myfac = mysql_fetch_array($resultFac)) {	
			//if($myfac['name']==$facu)
			//	$tool_content .=  "<option value=\"".$myfac['id']."--".$myfac['name']."\" selected>$myfac[name]</option>";
			//else
				$tool_content .=   "<option value=\"".$myfac['id']."--".$myfac['name']."\">$myfac[name]</option>";
		}
		$tool_content .=  "</select>";

$tool_content .=  "$langFieldsRequAsterisk ($langTargetFac)
		</td>
	</tr>";

unset($repertoire);

/* SAKIS:  edw na valw ta kwdikia gia thn epilogh twn diaxeiristwn!!!!!! */

$tool_content .=  "<tr>
	<th>
		$langProfessors:
	</th>
	<td>
	<input type=\"Text\" name=\"titulaires\" size=\"60\" value=\"$titulaire_probable\">$langFieldsRequAsterisk ($langProfessorsInfo)
	</td>
	</tr>
	
	<tr>
	<th>$m[type]:</th>
	<td>
		<select name = \"type\">
			<option value=\"pre\">	".$m['pre']."</option>
			<option value=\"post\"> ".$m['post']."</option>
			<option value=\"other\">".$m['other']."</option>
		</select>
		
		";
//selection(array('pre' => $m['pre'], 'post' => $m['post'], 'other' => $m['other']), 'type');

$tool_content .=  "
	$langFieldsRequAsterisk ($langCourseCategory)</br></td>
	</tr>
	</thead>
	</table>
	<br/>
			<input type=\"Submit\" name=\"submit\" value=\"$langNextStep >\">

	
</form>
";

draw($tool_content, 1, '', $local_head);
?>
