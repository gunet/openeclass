<?
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

// creating passwords automatically
function create_pass($length) {
	$res = "";
	$PASSCHARS="abcdefghijklmnopqrstuvwxyz023456789";
	$PASSL = 35;
	srand ((double) microtime() * 1000000);
	for ($i = 1; $i<=$length ; $i++ ) {
		$res .= $PASSCHARS[rand(0,$PASSL-1)];
	}
	return $res;
}

$nameTools = $langNewProf;
$navigation[]= array ("url"=>"../admin/", "name"=> $admin);

/* Check for LDAP server entries */
$ldap_entries = mysql_fetch_array(mysql_query("SELECT * FROM institution"));
if ($ldap_entries['ldapserver'] <> NULL) 
	$navigation[]= array ("url"=>"newprof_info.php", "name"=> $regprof);

begin_page();
?>
	<tr>
	<td>
	<form action="newprof_second.php" method="post">
	<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr valign="top" bgcolor="<?= $color2 ?>">
	<td><font size="2" face="arial, helvetica"><?= $langSurname?></font></td>
	<td><input type="text" name="nom_form" value="<?= @$ps?>" ><font size="1">&nbsp;(*)</font></td>
	</tr>
	<tr bgcolor="<?= $color2 ?>">
	<td><font size="2" face="arial, helvetica"><?= $langName?></font></td>
	<td>
	<input type="text" name="prenom_form" value="<?= @$pn?>"><font size="1">&nbsp;(*)</font>
	</td>
	</tr>
	<tr bgcolor="<?= $color2 ?>">
	<td><font size="2" face="arial, helvetica"><?= $langUsername ?> </font></td>
	<td><input type="text" name="uname" value="<?= @$pu?>"><font size="1">&nbsp;(*)</font></td>
	</tr>
	<tr bgcolor="<?= $color2 ?>">
	<td><font size="2" face="arial, helvetica"><?= $langPass;?>&nbsp;:</font></td>
	<td><input type="text" name="password" value="<? echo create_pass(5); ?>"></td>
	</tr>
	<tr bgcolor="<?= $color2;?>">
	<td><font size="2" face="arial, helvetica"><?= $langEmail?></font></td>
	<td><input type="text" name="email_form" value="<?= @$pe?>"><font size="1">&nbsp;(*)</font></td>
	</tr>
	<tr bgcolor="<?= $color2 ?>">
	<td><font size="2" face="arial, helvetica"><?= $langDepartment ?>&nbsp;</font></td>
	<td>

	<?
	$dep = array();
        $deps=db_query("SELECT name FROM faculte order by id");
	while ($n = mysql_fetch_array($deps))
		$dep[$n[0]] = $n['name'];  

	if (isset($pt))
		selection ($dep, 'department', $pt);
	else 
		selection ($dep, 'department');
        ?>

	</td>
	</tr>
	<tr bgcolor="<?= $color2 ?>" ><td>&nbsp;</td>
	<td><input type="submit" name="submit" value="<?=  $langOk;?>" ></td>
	</tr>
	</table>
	</form>
	</td>
	</tr>
<tr><td align='right'><font size="1"><?= $langRequiredFields ?></font></td></tr>
</table>
</body>
</html>
