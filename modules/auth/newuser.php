<?
/*
      +----------------------------------------------------------------------+
      | e-class version 1.0                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$		     |
      +----------------------------------------------------------------------+
      |   $Id$
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
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
      |                                                                      |
      | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      +----------------------------------------------------------------------+
 */

$langFiles = array('registration','gunet');
include '../../include/baseTheme.php';
$nameTools = $langUserDetails;

// Initialise $tool_content
$tool_content = "";
// Main body


/* Check for LDAP server entries */
$ldap_entries = mysql_fetch_array(mysql_query("SELECT ldapserver FROM institution"));
if ($ldap_entries['ldapserver'] <> NULL)
	$navigation[]= array ("url"=>"newuser_info.php", "name"=> $reguser);


$tool_content .= "<table><tr>
<td width=\"600\">
<form action=\"newuser_second.php\" method=\"post\">
<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"100%\" bgcolor=\"".$color2."\">
<tr valign=\"top\">
<td>".$langName."</td>
<td><input type=\"text\" name=\"prenom_form\"><font size=\"1\">&nbsp;(*)</font></td>
</tr>
<tr><td>".$langSurname."</td>
<td><input type=\"text\" name=\"nom_form\"><font size=\"1\">&nbsp;(*)</font></td>
</tr>
<tr><td>".$langUsername."</td>
<td><input type=\"text\" name=\"uname\" size=\"20\" maxlength=\"20\"><font size=\"1\">&nbsp;(*)</font></td>
</tr>
<tr><td>&nbsp;</td><td><font size=\"1\">".$langUserNotice."</font></td></tr>
<tr><td>".$langPass."</td>
<td><input type=\"password\" name=\"password1\" size=\"20\" maxlength=\"20\"><font size=\"1\">&nbsp;(*)</font></td>
</tr>
<tr>
<td>".$langConfirmation."<br>
</td>
<td valign=\"top\"><input type=\"password\" name=\"password\" size=\"20\" maxlength=\"20\"><font size=\"1\">&nbsp;(*)</font></td>
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
<td>
<select name=\"department\">";

$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id");
while ($dep = mysql_fetch_array($deps)) 
	$tool_content .= "\n<option value=\"$dep[1]\">$dep[0]</option>";


$tool_content .= "</select></td></tr>
<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\"></td></tr>
</table>
</form>
</td>
</tr>
<tr><td  align='right'><font size=\"1\">".$langRequiredFields."</font>
</td></tr></table>";

draw($tool_content,0);

?>
