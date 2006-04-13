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
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFiles = array('registration', 'admin', 'gunet');
include('../../include/init.php');

check_admin();

$nameTools = $langByLdap;
$navigation[] = array("url"=>"../admin/", "name"=> $admin);
$navigation[] = array("url"=>"newprof_info.php", "name"=> $regprof);
$page_title = $regprofldap;
begin_page();
?>
	<tr bgcolor="<?= $color2;?>">
		<td>
			<form method="POST" action="ldapsearch_prof.php">
				<table>
				<tr><td><?= $emailprompt ?></td>
					<td><input type=text name=ldap_email value=<?= @$m ?>></td>
				</tr>
				<tr colspan=2><td><br>
				<?
					$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
					mysql_select_db($mysqlMainDb, $db);
					$result = mysql_query("select * from institution ORDER BY nom",$db);
					if (mysql_num_rows($result) > 1) {
						?>
						<select name=ldap_server>
						<option value="0" SELECTED>
						<?php echo $univprompt?></option>
						<?
						while (($row = mysql_fetch_object($result))) {
							echo "<option value=".$row->ldapserver."_".$row->basedn."_".$row->inst_id.">\n";
							echo $row->nom."\n";
							echo "</option>\n";
						}
						echo "</select>\n";
					} else {
						$row = mysql_fetch_object($result);
						echo "<strong>$langInstitution</strong> ".($row->nom)."\n";
						echo "<input type='hidden' name=ldap_server value=".$row->ldapserver."_".$row->basedn."_".$row->inst_id.">\n";
					}
					mysql_free_result($result);
					?>
				</td></tr>
				<tr colspan=2>
					<td><br><input type=submit name=is_submit value='<? echo $reg?>'>
					<br><br>
					</td>
				</tr>
			<br><br></table>
		</form>
<?
	end_page();
?>
