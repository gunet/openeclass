<?

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision: 1.1.1.1 $                             |
      +----------------------------------------------------------------------+
      | $Id: addadmin.php,v 1.1.1.1 2006/01/10 15:02:11 adia Exp $  |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
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
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFiles = array('admin','addadmin');
include '../../include/init.php';
@include "check_admin.inc";

$nameTools = $langNomPageAddHtPass;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();

if (isset($encodeLogin)) {
	$res = mysql_query("SELECT user_id FROM user WHERE username='$encodeLogin'");
	if (mysql_num_rows($res) == 1) {
		$row = mysql_fetch_row($res);
		if (mysql_query("INSERT INTO admin VALUES('$row[0]')")) 
			echo "$langUser $encodeLogin $langWith  id='$row[0]' $langDone";
		 else 
			echo "$langError <br>&nbsp;<br>";
		echo "<center><a href='index.php'>$langBack</a></center>";
	} else {
		echo "$langUser $encodeLogin $langNotFound.<br>&nbsp;<br>";
		printform($langLogin);
		echo "<center><a href='index.php'>$langBack</a></center>";
	}
} else {
	printform($langLogin);
	echo "<center><a href='index.php'>$langBack</a></center>";
}

end_page();

// -------------- functions -------------------------

function printform ($message) { 
	global $langAdd;

	echo "<form method='post' name='makeadmin' action='$_SERVER[PHP_SELF]'>";
	echo "$message: ";
	echo "<input type='text' name='encodeLogin' size='20' maxlength='30'>";
	echo "<input type='submit' name='crypt' value='$langAdd'>";
	echo "</form>";
}

?>
