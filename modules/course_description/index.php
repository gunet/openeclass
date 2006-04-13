<?php  
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$               |
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
$langFiles = array('course_description','pedaSuggest');
$require_help = TRUE;
$helpTopic = 'Coursedescription';
include ('../../include/init.php');

include('../../include/lib/textLib.inc.php'); 

$nameTools = $langCourseProgram;
begin_page();

?>

<tr>
<td colspan="2">

<?php 
if ($is_adminOfCourse)
{ 
	echo "
			<a href=\"edit.php\">
				".$langEditCourseProgram."
			</a>";
}
// else 
{
	mysql_select_db("$currentCourseID",$db);
	$sql = "SELECT `id`,`title`,`content` FROM `course_description` order by id";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) >0 )
	{
		echo "
			<hr noshade size=\"1\">";
		while ($bloc = mysql_fetch_array($res))
		{ 
			echo "
			<H4>
				".$bloc["title"]."
			</H4>
			<font size=2 face='arial, helvetica'>
				".make_clickable(nl2br($bloc["content"]))."
			</font>";
		}
	}
	else
	{
		echo "<br><h4>$langThisCourseDescriptionIsEmpty</h4>";
	}
}

?>
		</td>
	</tr>
	<tr name="bottomLine" >
		<td colspan="2">
			<br>
			<hr noshade size="1">
		</td>
	</tr>
</table>
</body>
</html>

