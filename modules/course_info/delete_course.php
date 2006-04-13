<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0  $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$
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

$require_current_course = TRUE;
$langFiles = 'course_info';
include '../../include/init.php';

$nameTools = $langDelCourse;

begin_page();

if($is_adminOfCourse) {
	if(isset($delete)) {
		mysql_select_db("$mysqlMainDb",$db); 
		mysql_query("DROP DATABASE `$currentCourseID`");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours WHERE code='$currentCourseID'");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours_user WHERE code_cours='$currentCourseID'");
		mysql_query("DELETE FROM `$mysqlMainDb`.cours_faculte WHERE code='$currentCourseID'");
		mysql_query("DELETE FROM `$mysqlMainDb`.annonces WHERE code_cours='$currentCourseID'");
		@mkdir("../../courses/garbage");
		rename("../../courses/$currentCourseID", "../../courses/garbage/$currentCourseID");
		echo "<hr noshade size=\"1\">
			<font size=\"2\" face=\"arial, helvetica\">
			$langCourse $currentCourseID $intitule $langHasDel.
			<br>&nbsp;<br><a href=\"../../index.php\">".$langBackHome." ".$siteName."</a>
		</font>";
	} else {
		echo "<p><font face=\"arial, helvetica\" size=\"2\" color=\"#CC0000\">
			$langByDel $currentCourseID $intitule&nbsp;?</p>
			<p><font face=\"arial, helvetica\" size=\"2\" color=\"#CC0000\">
			<a href=\"".$_SERVER['PHP_SELF']."?delete=yes\">
			$langY</a>&nbsp;|&nbsp;<a href=\"infocours.php\">$langN</a></font>
			</p>";
	} // else
} else  {
	echo "<font size=\"2\" face=\"arial, helvetica\">$langForbidden</font>";
}

echo "<hr noshade size=1>";
end_page();

?>

