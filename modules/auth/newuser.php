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

/**===========================================================================
	newuser.php
* @version $Id$
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
$tool_content .= "
<form action=\"newuser_second.php\" method=\"post\" name=\"newusersecond\">

<table width=\"99%\">
<thead>
<t>
<th>$langName</th>
<td><input type=\"text\" name=\"prenom_form\">(*)</td>
</tr>
<tr><th>$langSurname</th>
<td><input type=\"text\" name=\"nom_form\">(*)</td>
</tr>
<tr><th>$langUsername</th>
<td><input type=\"text\" name=\"uname\" size=\"20\" maxlength=\"20\">(*) (**) $langUserNotice</td>
</tr>

<tr><th>$langPass</th>
<td><input type=\"password\" name=\"password1\" size=\"20\" maxlength=\"20\">(*) (**)</td>
</tr>
<tr>
<th>$langConfirmation</th>
<td valign=\"top\"><input type=\"password\" name=\"password\" size=\"20\" maxlength=\"20\">(*) (**) $langUserNotice</td>
</tr>

<tr>
<th>$langEmail</th>
<td><input type=\"text\" name=\"email\"> $langEmailNotice</td>
</tr>

<tr><th>$langAm</th>
<td><input type=\"text\" name=\"am\"></td>
</tr>
<tr><th>$langDepartment</th>
<td><select name=\"department\">";
$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id");
while ($dep = mysql_fetch_array($deps)) 
	$tool_content .= "\n<option value=\"".$dep[1]."\">".$dep[0]."</option>";
$tool_content .= "</select></td>
</tr>
</thead>
</table>
<br/>
<input type=\"hidden\" name=\"auth\" value=\"1\">
<input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\"></td></tr>

</form>
<br/>
<p>$langRequiredFields</p>
<p>$star2 $langCharactersNotAllowed</p>
";

draw($tool_content,0);

?>
