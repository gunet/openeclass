<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | $Id$          |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |    This program is free software; you can redistribute it and/or     |
      |    modify it under the terms of the GNU General Public License       |
      |    as published by the Free Software Foundation; either version 2    |
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
      |   02111-1307, USA. The GPL license is also available through the     |
      |   world-wide-web at http://www.gnu.org/copyleft/gpl.html             |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Geschι <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */
$langFiles = array('registration','gunet');
include '../../include/baseTheme.php';
$nameTools = $langLDAPUser;
$navigation[]= array ("url"=>"newuser_info.php", "name"=> "$reguser");

// Initialise $tool_content
$tool_content = "";
// Main body

//$auth = get_auth_id();

$tool_content .= "<tr bgcolor=\"".$color2."\">
		<td>
			<form method=\"POST\" action=\"ldapsearch.php\">
				<table>
				<tr><td>Δώστε το username σας:</td>
					<td><input type=\"text\" name=\"ldap_email\"></td>
				</tr>
				<tr><td>".$ldapprompt."</td>
					<td><input type=\"password\" name=\"ldap_passwd\"></td>
				</tr>
				<tr colspan=2><td><br>";
				
				/*
					mysql_select_db($mysqlMainDb, $db);
					$result = mysql_query("select * from institution ORDER BY nom",$db);
					if (mysql_num_rows($result) > 1) {
						echo "<select name=ldap_server><option value=\"0\" SELECTED>$univprompt</option>\n";
						while (($row = mysql_fetch_object($result))) {
							echo "<option value=".$row->ldapserver."_".$row->basedn."_".$row->inst_id.">\n";
							echo $row->nom."\n";
							echo "</option>\n";
						}
						echo "</select>\n";
					} else {
						$row = mysql_fetch_object($result);
						echo "<strong>".$langInstitution." </strong>".($row->nom)."\n";
						echo "<input type='hidden' name='ldap_server' value=".$row->ldapserver."_".$row->basedn."_".$row->inst_id.">\n";
					}
					mysql_free_result($result);
				*/
				$tool_content .= "</td>
				</tr>
				<tr colspan=2>
					<td><br><input type=\"submit\" name=\"is_submit\" value=\"".$reg."\">
					<br><br>
					</td>
				</tr>
				<br><br></table>
			</form><br />";

$tool_content .= "<br />";

draw($tool_content,0);
?>