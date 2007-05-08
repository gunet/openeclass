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
	create_course2.php
* @version $Id$
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: 2nd step for the Create New Course Wizard

    The script transfers data from the 1st step of the wizard in hidden
    input tags.
        
 	The script requires some fields to be filled-in, thus it checks the
 	validity of the entries by javascripts.
==============================================================================*/

$require_login = TRUE;
$require_prof = TRUE;

$langFiles = 'create_course';

$require_help = TRUE;
$helpTopic = 'CreateCourse';

include '../../include/baseTheme.php';
$nameTools = $langCreateCourse . " (" . $langCreateCourseStep." 2 " .$langCreateCourseStep2 . " 3 )" ;

$local_head = "<script language=\"javascript\">
function previous_step()
{
	document.location.href = \"./create_course.php\";
}
</script>";

/*
function validate() {
		if (document.forms[0].description.value==\"\") {
				alert(\"Παρακαλώ συμπληρώστε μια σύντομη περιγραφή για το μάθημα!\");	
				return false;																																											
		}
	    if (document.forms[0].course_keywords.value==\"\") {
					alert(\"Παρακαλώ συμπληρώστε τις λέξεις κλειδιά του μαθήματος!\");
					return false;
		}
	return true;		
}
*/
$tool_content = "";

//  display form
$tool_content .= "
<FIELDSET style=\"PADDING-RIGHT: 7px; PADDING-LEFT: 7px; PADDING-BOTTOM: 7px; PADDING-TOP: 7px\">
        <LEGEND>$langCreateCourseStep2Title</LEGEND>
        <table border=0><tr valign=\"top\">
			<td colspan=\"2\" valign=\"top\">
        <span class='explanationtext' style='font-weight:bold;'>$langFieldsOptionalNote</span></td>
        </tr>

		<form method=\"post\" action=\"create_course3.php\" onsubmit=\"return validate();\">
		<input type=\"hidden\" name=\"intitule\" value=\"".htmlspecialchars($_POST['intitule'])."\">
		<input type=\"hidden\" name=\"faculte\" value=\"".htmlspecialchars($_POST['faculte'])."\">
		<input type=\"hidden\" name=\"titulaires\" value=\"".htmlspecialchars($_POST['titulaires'])."\">
		<input type=\"hidden\" name=\"type\" value=\"".htmlspecialchars($_POST['type'])."\">
			<tr><th>$langDescrInfo:</th>   
			<td>
			<textarea name=\"description\" value='".@$description."' cols=\"50\" rows=\"4\"></textarea>
			</td> </tr>
			<tr>
			<th>$langCourseKeywords</th><td>
			<textarea name=\"course_keywords\" value='".@$course_keywords."' cols=\"50\" rows=\"2\"></textarea>
		 </td>
			</tr>
			<tr>
			<th>$langCourseAddon</th>
			<td>
			<textarea name=\"course_addon\" value='".@$course_addon."' cols=\"50\" rows=\"4\"></textarea>
			</td>
			</tr>
			</table></fieldset>
			<br/>
			<input type='submit' name='submit' value='< $langPreviousStep '>
			<input type='submit' name='submit' value='$langNextStep >'>
		</form>";

draw($tool_content, 1, '', $local_head);

?>
