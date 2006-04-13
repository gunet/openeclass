<?php
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
/*
 * Send a user's password via e-mail
 */

$langFiles = 'lostpass';

include '../../include/init.php';

$nameTools = $lang_remind_pass;
begin_page();

include('../../include/sendMail.inc.php');

function valid_email($e) {
	$elements = explode('@', $e);
	if (sizeof($elements) != 2) {
		return FALSE;
	}
	return TRUE;
}

?>
<html>
<body bgcolor="white">
	<tr><td>
	<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr valign="top" bgcolor="<?= $color2 ?>">
	<td><font size="2" face="arial, helvetica">
	<p align="justify" style="padding-left: 10px; padding-right: 10px;">
		
<?
if (!isset($femail)) {
	/***** Email address entry form *****/

echo $lang_pass_intro;
?>
	<form method="post" action="<?= $REQUEST_URI ?>">
	<em style="padding-left:5px; font-size:10pt;"><?= $lang_email ?>: </em>
	<input type="text" name="femail" size="40"><br><br>
	<input type="submit" name="doit" value="<?= $lang_pass_submit ?>">
	</form>
<?
} else {
	if (!valid_email($femail)) {
		echo $lang_pass_invalid_mail1;
		echo "<code> $femail </code>";
		echo $lang_pass_invalid_mail2;
		echo " <a href='mailto: $emailhelpdesk'>$emailhelpdesk</a>, "; 
		echo $lang_pass_invalid_mail3;
?>
		<form method="post" action="<?= $REQUEST_URI ?>">
		<input type="text" name="femail" size="50"><br>
		<input type="submit" name="doit" value="<?= $lang_pass_submit ?>">
		</form>
<?
	} else {
/***** If valid e-mail address was entered, find user and send email *****/
		$res = mysql_query("SELECT nom, prenom, username, password, statut, inst_id FROM user
			WHERE email = '" . mysql_escape_string($femail) . "'");
		if (mysql_num_rows($res) > 0) {
		$text = $lang_pass_email_intro. $emailhelpdesk;
			if (mysql_num_rows($res) == 1) {
				$text .= "\n$lang_pass_email_account\n";
			} else {
				$text .= "\n$lang_pass_email_many_accounts\n";
			}
			while ($s = mysql_fetch_array($res, MYSQL_ASSOC)) {
				$text .= "
$lang_pass_email_name " . htmlspecialchars($s['prenom']." ".$s['nom']) . "
$lang_pass_email_status " . (($s['statut'] == 1)? "$lang_prof": (
		($s['statut'] == 5)? "$lang_student": "$lang_other")) . "
$lang_pass_email_username " . htmlspecialchars($s['username']) .
($s['inst_id']? " $lang_pass_email_ldap": "
$lang_pass_email_password " . htmlspecialchars($s['password']) . "\n");
			}
			/***** Account details found, now send e-mail *****/
			$emailheaders = "From: $siteName <$emailAdministrator>\n".
					"MIME-Version: 1.0\n".
					"Content-Type: text/plain; charset=$charset\n".
					"Content-Transfer-Encoding: 8bit";
			$emailsubject = "Account information";
			if (!send_mail($siteName, $emailAdministrator, '', $femail,
				   $emailsubject, $text, $charset)) {
					echo $lang_pass_email_error1;
					echo "<code> $femail </code>.";
					echo $lang_pass_email_error2;
					echo "<a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>.";
			} else {
				echo $lang_pass_email_ok;
				echo "<code> $femail </code>.";
			}
		} else {
			echo $lang_pass_not_found1;	
			echo "<code> $femail </code>."; 
			echo $lang_pass_not_found2;
			echo "<a href='mailto: $emailhelpdesk'>$emailhelpdesk</a>"; 
			echo $lang_pass_not_found3;
		}
	}
}
?>		
			</p></font></td></tr></table>
	</td></tr>
</table>
</body>
</html>
