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
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
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
include '../../include/baseTheme.php';

$nameTools = $langAddPage;

$tool_content = "";

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
		
			<p>
				$langCouldNot
			</p>
	</tr>");
$sql = 'SELECT MAX(`id`) FROM `accueil` ';
		$res = db_query($sql,$dbname);
		while ($maxID = mysql_fetch_row($res)) {
			$mID = $maxID[0];
		}
		
		if($mID<101) $mID = 101;
		else $mID = $mID+1;

		db_query("INSERT INTO accueil VALUES (
					$mID,
					'$link_name',
					'../../courses/$currentCourse/page/$file_name \"target=_blank',
					'external_link',
					'1',
					'0',
					'',
					'HTML_PAGE'
					)", $currentCourse);
		
			$tool_content .=  "
					<table>
				<tbody>
					<tr>
						<td class=\"success\">
						$langOkSent.
					</td>
					</tr>
				</tbody>
			</table>";
		}
		else 
		{
			$tool_content .= "
			<table>
				<tbody>
					<tr>
						<td class=\"caution\">
					
						$langTooBig
					
						</td>
					</tr>
				</tbody>
			</table>
			";
			draw($tool_content, 2);
		}	// else
	}	// if submit

// if not submit

else
	{
		$tool_content .=  "
		<p>$langExplanation</p>
			
			
		<form method=\"POST\" action=\"$PHP_SELF?submit=yes\" enctype=\"multipart/form-data\">
			<table>
			<thead>
				<tr>
					<th>
						
							$langSendPage :
						
					</th>
					<td>
						<input type=\"file\" name=\"file\" size=\"35\" accept=\"text/html\">
					</td>
				</tr>
				<tr>
					<th>
						
							$langPgTitle :
						
					</th>
					<td>
						<input type=\"Text\" name=\"link_name\" size=\"50\">
					</td>
				</tr>
				</thead>
				</table>
				<br>
						<input type=\"Submit\" name=\"submit\" value=\"$langAddOk\">
				
</form>";
	}	// else
}	// if uid=prof_id

else {
	// Print You are not identified as responsible for this course
	$tool_content .=  "
				<table>
				<tbody>
					<tr>
						<td class=\"caution\">
					
						$langNotAllowed
					
						</td>
					</tr>
				</tbody>
			</table>
		
	";
}	// else

$tool_content .=  "";

draw($tool_content, 2);

?>

