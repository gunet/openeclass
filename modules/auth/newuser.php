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

if (isset($close_user_registration) and $close_user_registration == TRUE) {
			 $tool_content .= "<div class='td_main'>$langForbidden</div>";
       draw($tool_content,0);
	     exit;
 }

// Main body
$tool_content .= "
<table width=\"99%\" class='FormData'>
<thead>
<tr>
<td>
<form action=\"newuser_second.php\" method=\"post\" name=\"newusersecond\">


<table width=\"100%\">
<tbody>
<tr>
	<th class='left' width='20%'>$langName</th>
	<td><input type=\"text\" name=\"prenom_form\"><small>&nbsp;(*)</small></td>
</tr>
<tr>
	<th class='left'>$langSurname</th>
	<td><input type=\"text\" name=\"nom_form\"><small>&nbsp;(*)</small></td>
</tr>
<tr>
	<th class='left'>$langUsername</th>
	<td><input type=\"text\" name=\"uname\" size=\"20\" maxlength=\"20\"><small>&nbsp;(*) (**) $langUserNotice</small></td>
</tr>

<tr>
	<th class='left'>$langPass</th>
	<td><input type=\"password\" name=\"password1\" size=\"20\" maxlength=\"20\"><small>&nbsp;(*) (**)</small></td>
</tr>
<tr>
	<th class='left'>$langConfirmation</th>
	<td valign=\"top\"><input type=\"password\" name=\"password\" size=\"20\" maxlength=\"20\"><small>&nbsp;(*) (**) $langUserNotice</small></td>
</tr>

<tr>
	<th class='left'>$langEmail</th>
	<td><input type=\"text\" name=\"email\"> $langEmailNotice</td>
</tr>

<tr>
	<th class='left'>$langAm</th>
	<td><input type=\"text\" name=\"am\"></td>
</tr>
<tr>
	<th class='left'>$langDepartment</th>
	<td><select name=\"department\">";
$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id");
while ($dep = mysql_fetch_array($deps)) 
	$tool_content .= "\n<option value=\"".$dep[1]."\">".$dep[0]."</option>";
$tool_content .= "</select>
	</td>
</tr>

<tr><th class='left'>&nbsp;</th>
    <td>
	<input type=\"hidden\" name=\"auth\" value=\"1\">
    <input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\">
    <br/><br/>
    <p>$langRequiredFields <br/>$langStar2 $langCharactersNotAllowed</p>
	</td>
</tr>
</tbody>
</table>

</td>
</tr>
</thead>
</table>
</form>


";

draw($tool_content,0);

?>
