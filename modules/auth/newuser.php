<?
 /**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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

/**===========================================================================
	newuser.php
	@last update: 07-06-2006 by Stratos Karatzidis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
        @Description: First step in new user registration

 	Purpose: The file displays the form that that the candidate user must fill
 	in with all the basic information.

==============================================================================
*/

$langFiles = array('registration','gunet');
include '../../include/baseTheme.php';
include 'auth.inc.php';
$nameTools = $langUserDetails;

$tool_content = "";		// Initialise $tool_content

// Main body
$tool_content .= "<table width=\"99%\"><tr>
<td width=\"600\">
<form action=\"newuser_second.php\" method=\"post\" name=\"newusersecond\">
<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"100%\" bgcolor=\"".$color2."\">
<tr valign=\"top\">
<td>".$langName."</td>
<td><input type=\"text\" name=\"prenom_form\"><font size=\"1\">&nbsp;(*)</font></td>
</tr>
<tr><td>".$langSurname."</td>
<td><input type=\"text\" name=\"nom_form\"><font size=\"1\">&nbsp;(*)</font></td>
</tr>
<tr><td>".$langUsername."</td>
<td><input type=\"text\" name=\"uname\" size=\"20\" maxlength=\"20\"><font size=\"1\">&nbsp;(*)</font><font size=\"1\">&nbsp;(**)</font></td>
</tr>
<tr><td>&nbsp;</td><td><font size=\"1\">".$langUserNotice."</font></td></tr>
<tr><td>".$langPass."</td>
<td><input type=\"password\" name=\"password1\" size=\"20\" maxlength=\"20\"><font size=\"1\">&nbsp;(*)</font><font size=\"1\">&nbsp;(**)</font></td>
</tr>
<tr>
<td>".$langConfirmation."<br /></td>
<td valign=\"top\"><input type=\"password\" name=\"password\" size=\"20\" maxlength=\"20\"><font size=\"1\">&nbsp;(*)</font><font size=\"1\">&nbsp;(**)</font></td>
</tr>
<tr><td>&nbsp;</td><td><font size=\"1\">".$langUserNotice."</font></td></tr>
<tr>
<td>".$langEmail."</td>
<td><input type=\"text\" name=\"email\"></td>
</tr>
<tr><td>&nbsp;</td><td><font size=\"1\">".$langEmailNotice."</font></td></tr>
<tr><td>".$langAm."</td>
<td><input type=\"text\" name=\"am\"></td>
</tr>
<tr><td>".$langDepartment."</td>
<td><select name=\"department\">";
$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id");
while ($dep = mysql_fetch_array($deps)) 
	$tool_content .= "\n<option value=\"".$dep[1]."\">".$dep[0]."</option>";
$tool_content .= "</select></td>
</tr>
<tr><td>&nbsp;</td><td>
<input type=\"hidden\" name=\"auth\" value=\"1\">
<input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\"></td></tr>
</table>
</form>
</td>
</tr>
<tr><td align=\"right\"><font size=\"1\">".$langRequiredFields."</font><br />
<font size=\"1\">".$star2 . $langCharactersNotAllowed."</font>
</td></tr></table>";

draw($tool_content,0);

?>