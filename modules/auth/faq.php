<?php session_start();
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
/**
 * REGISTRATION 1    from CLAROLINE http://www.claroline.net
 */
include('../include/config.php');
@include("../lang/english/trad4all.inc.php");
@include("../lang/$language/trad4all.inc.php");
@header('Content-Type: text/html; charset='. $charset);
@include("../lang/english/registration.inc.php");
@include("../lang/$language/registration.inc.php");
if(isset($already_second))
{
	session_register("uid");
	session_unregister("statut");
	session_unregister("prenom");
	session_unregister("nom");
	session_unregister("uname");
}
$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
$nameTools = "1";

//$interbredcrump[]= array ("url"=>"newuser_info.php", "name"=> "Εγγραφή Χρήστη");

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<style type="text/css">
    	BODY,H1,H2,H3,H4,H5,H6,P,BLOCKQUOTE,TD,OL,UL,input  {	font-family: Arial, Helvetica, sans-serif; }
</style>
<title>
	<?php echo "$nameTools - Συχνές Ερωτήσεις - $siteName"; ?>
</title>
</head>
<body bgcolor="white">
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?php echo $mainInterfaceWidth?>">
	<tr>
		<td>
			<?php include('../include/claroline_header.php'); ?>
			
		</td>
	</TR>
	<tr>
		<td>
			<h4>
				<?php echo "Συχνές Ερωτήσεις"?>
							
			</h4>
			<br>
		</td>
	</TR>
	<tr>
		<td>

<form action="newuser_second.php" method="post">
			<table cellpadding="3" cellspacing="0" border="0" width="100%">

				<tr valign="top" bgcolor="<?php echo $color2 ?>">
					<td>
						<font size="2" face="arial, helvetica">
				Η ζητούμενη λειτουργία είναι υπό κατασκευή !!				
				</font>
				<br><br><br><br><br><br><br><br><br>
				</td>
			</table>
	</tr>
</table>
</body>
</html>
