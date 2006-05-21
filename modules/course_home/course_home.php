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

$require_current_course = TRUE;
$langFiles = 'course_home';
//include ('../../include/init.php');
include ('../../../include/lib/textLib.inc.php'); 
unset($relativePath);
//$relativePath = "../../";
$path2add=1;
include '../../include/baseTheme.php';
//echo "dbname is " . $dbname;
mysql_select_db($dbname);
$tool_content = "";
//begin_page();
//
//$moduleId = 1;
//echo "<tr><td colspan='4' bgcolor='$color1'style='padding-left: 15px; padding-right:15px; padding-bottom: 5px;'>";
//include "introductionSection.inc.php"; 
?>

<?
if ($is_adminOfCourse) {
		
$tool_content .= "This is the content for the course administrator";
}


// work with data post by admin of  course
if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"]) {
	if(isset($askDelete) && $askDelete) {
		$tool_content .= "<tr>
		<td colspan=\"4\" bgcolor=\"".$color2."\" >
		<font face=\"arial, helvetica\" color=\"#ff0000\">
		<strong>".$langDelLk."</strong>
		<br>
		<a href=\"$_SERVER[PHP_SELF]\"><font face=\"arial, helvetica\">$langNo</font></a>
		&nbsp;|&nbsp;
		<a href=\"$_SERVER[PHP_SELF]?delete=yes&id=$id\">
		<font face=\"arial, helvetica\">$langYes</font></a>
		<br>
		<br>
		</font>
		</td>
		</tr>";
	} elseif (isset($delete) && $delete) {
		$sql = "DELETE FROM accueil WHERE id=$id";
		db_query($sql, $dbname);
	}
 }

//showtools('Public');

// professor view


// tools for admin only
//
//$tool_content .= "<tr><td colspan=\"4\"><hr noshade size=\"1\"></td></tr></table>";
//$tool_content .= "</body></html>";

$table= "stat_accueil"; 

// statistics  - Count only if first visit during the session
if (!isset($alreadyHome) || (isset($alreadyHome) && !$alreadyHome)) {
//	include ("../../../modules/stat/write_logs.php"); 
}

$alreadyHome = 1;
session_register("alreadyHome");

// function for displaying tools

$tool_content .= "<p>Content viewable by everyone</p>";
draw($tool_content, 2);
?>
