<?php 

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$            |
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

/*******************************************************************
*         IMPORT A PAGE INTO THE WEBSITE
********************************************************************

GOALS
*****
Allow professor to send quickly a page that will be integrated under the website header.

DETAIL
*********

1. Send a HTML file to /courseName/page directory
2. Insert document name / title correspondence in "accueil" SQL table

************************************************************/

$require_current_course = TRUE;
$langFiles = 'import';
include('../../include/init.php');

$nameTools = $langAddPage;
begin_page();

// Check if user=prof or assistant

if($is_adminOfCourse) 
{ 

	if(isset($submit))
	{

		// UPLOAD FILE TO "documents" DIRECTORY + INSERT INTO documents TABLE
		$updir = "$webDir/courses/$currentCourseID/page/"; //path to upload directory
		$size = "20000000"; //file size ex: 5000000 bytes = 5 megabytes
		if (($file_name != "") && ($file_size <= "$size" )) {

		$file_name = str_replace(" ", "", $file_name);
		$file_name = str_replace("é", "e", $file_name);
		$file_name = str_replace("è", "e", $file_name);
		$file_name = str_replace("ê", "e", $file_name);
		$file_name = str_replace("à", "a", $file_name);

		@copy("$file", "$updir/$file_name")
		or die("
		<tr>
		<td colspan=\"2\">
			<font face=\"arial, helvetica\" size=2>
				$langCouldNot
			</font>
		</td>
	</tr>");

		mysql_query("INSERT INTO accueil VALUES ('NULL',
				'$nom_fichier',
				'../../modules/import/import_page.php?link=$file_name \"target=_blank',
				'../../../images/travaux.png',
				'1',
				'0',
				'../../$currentCourseID/page/$file_name'
				)");

			echo "
					<font face=\"arial, helvetica\" size=\"2\">
						$langOkSent.
					</font>";
		}
		else 
		{
			die("
			<tr>
				<td colspan=\"2\">
					<font face=\"arial, helvetica\" size=\"2\">
						$langTooBig.
					</font>
				</td>
			</tr>");
		}	// else
	}	// if submit

// if not submit

else
	{
		echo "
		<font face=\"arial, helvetica\" size=\"2\">
			$langExplanation
			<br>
			<hr noshade size=\"1\">
		<form method=\"POST\" action=\"$PHP_SELF?submit=yes\" enctype=\"multipart/form-data\">
			<table>
				<tr>
					<td>
						<font face=\"arial, helvetica\" size=\"2\">
							$langSendPage :
						</font>
					</td>
					<td>
						<input type=\"file\" name=\"file\" size=\"35\" accept=\"text/html\">
					</td>
				</tr>
				<tr>
					<td>
						<font face=\"arial, helvetica\" size=\"2\">
							$langPgTitle :
						</font>
					</td>
					<td>
						<input type=\"Text\" name=\"nom_fichier\" size=\"50\">
					</td>
				</tr>
				<tr>
					<td colspan=\"2\">
						<input type=\"Submit\" name=\"submit\" value=\"$langAddOk\">
					</td>
				</tr>
			</table>
</form>";
	}	// else
}	// if uid=prof_id

else {
	// Print You are not identified as responsible for this course
	echo "
	<font face=\"arial, helvetica\" size=\"2\">
		$langNotAllowed
	</font>";
}	// else

echo "";
?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr noshade size="1">
		</td>
	</tr>
</table>
</body>
</html>
