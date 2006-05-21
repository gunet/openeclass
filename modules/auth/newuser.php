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

$langFiles = array('registration', 'gunet');
//include('../../include/init.php');
include '../../include/baseTheme.php';
$nameTools = $langUserDetails;

if(isset($already_second) and $already_second) {
	session_register("uid");
	session_unregister("statut");
	session_unregister("prenom");
	session_unregister("nom");
	session_unregister("uname");
}

/* Check for LDAP server entries */
$ldap_entries = mysql_fetch_array(mysql_query("SELECT ldapserver FROM institution"));
if ($ldap_entries['ldapserver'] <> NULL)
	$navigation[]= array ("url"=>"newuser_info.php", "name"=> $reguser);

//begin_page();
$tool_content = "";			
$tool_content .= <<<tCont

<form action="newuser_second.php" method="post">
<table  width="99%">
<thead>
<tr>
<th>$langName</th>
<td><input type="text" name="prenom_form">(*)</td>
</tr>
<tr><th>$langSurname</th>
<td><input type="text" name="nom_form">(*)</td>
</tr>
<tr><th>$langUsername</th>
<td><input type="text" name="uname" size="20" maxlength="20">(*) $langUserNotice</td>
</tr>

<tr><th>$langPass</th>
<td><input type="password" name="password1" size="20" maxlength="20">(*) $langUserNotice</td>
</tr>
<tr>
<th>$langConfirmation </th>
<td><input type="password" name="password" size="20" maxlength="20">(*) $langUserNotice</td>
</tr>

<tr>
<th>$langEmail</th>
<td><input type="text" name="email"> $langEmailNotice</td>
</tr>

<tr><th>$langAm</th>
<td><input type="text" name="am"></td>
</tr>
<tr><th>$langDepartment</th>
<td>
<select name="department">

tCont;
$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id");
while ($dep = mysql_fetch_array($deps)) 
	$tool_content .=  "\n<option value=\"$dep[1]\">$dep[0]</option>";

$tool_content .= "
</select></td></tr></thead>
</table>
<br>
<input type=\"submit\" name=\"submit\" value=\"$langRegistration\" >
</form><br>
<p>$langRequiredFields</p>";

draw($tool_content, 0);
?>
