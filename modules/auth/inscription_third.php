<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$   |
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
      |   world-wide-web at http://www.gnu.org/copyleft/gpl.html             |                                |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFiles = 'registration';
$require_login = TRUE;
include('../../include/init.php');

$nameTools = $langChoiceLesson;
begin_page();

# This file registers only students
$statut = 5;

if($submit)
{
	if (isset($course)) {
		$nbrElements=count($course);
		if ($nbrElements == 1) {
			$courses = $langCourse;
		} else {
			$courses = $langCourses;
		}	
		for ($i = 0; $i <$nbrElements; $i++) 
		{
			$inscr_cours=mysql_query("INSERT INTO cours_user
				(code_cours, user_id, statut, role)
				VALUES ('$course[$i]', '$uid', '$statut', '')");
		}	
	echo "<tr bgcolor=$color2 height=200>
		<td colspan=3 valign=top align=center><font size=2>
		<br><font size=2 face=\"arial, helvetica\">
		<blockquote>
		$langCoursesRegistered<br><br>
		$langYourRegTo <b>$nbrElements</b> $courses.<br><br>
		<a href='../../index.php'>$langCanEnter</a>
		</blockquote>
		</td>
		</tr>
		";
	} else {
	echo "<tr bgcolor=$color2 height=200>
               <td colspan=3 valign=top align=center><font size=2>
                <br><font size=2 face=\"arial, helvetica\">
		$langNoCoursesRegistered<br><br>
		<a href='../../index.php'>$langCanEnter</a>
		</td>
		</tr>";
	}
}	// if submit

end_page();
