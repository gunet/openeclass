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
$langFiles = array('course_info', 'create_course', 'opencours', 'course_home');

$courseHome = true;
$path2add=1;
include '../../include/baseTheme.php';

$tool_content = "";
$main_content = "";
$bar_content = "";

$sql = 'SELECT `description`,`course_objectives`,`course_prerequisites`,`course_keywords`,
 `course_references`,`faculte`,`lastEdit`,`type`, `visible`, `titulaires`, `fake_code`
 FROM `cours` WHERE `code` = "'.$currentCourse.'"';
$res = db_query($sql, $mysqlMainDb);
while($result = mysql_fetch_row($res)) {
	$description = $result[0];
	$objectives = $result[1];
	$prerequisites  = $result[2];
	$keywords = $result[3];
	$references = $result[4];
	$faculte = $result[5];
	//will the dates work ?

	$type = $result[7];
	$visible = $result[8];
	$professor = $result[9];
	$fake_code = $result[10];
	//
}


$main_content .= "<h3>$langDescription</h3><p>$description</p>";
$main_content .= "<h3>$langcourse_keywords</h3><p>$keywords</p>";

$main_content .= "<h3>$langcourse_objectives</h3><p>$objectives</p>";
$main_content .= "<h3>$langcourse_prerequisites</h3><p>$prerequisites</p>";
$main_content .= "<h3>$langcourse_references</h3><p>". nl2br($references)."</p>";

switch ($type){
	case 'pre': { //pre
		$lessonType = $m['pre'];
		break;
	}

	case 'post': {//post
		$lessonType = $m['post'];
		break;
	}

	case 'other': { //other
		$lessonType = $m['other'];
		break;
	}
}

$bar_content .= "<h4>$langLessonCode</h4>";
$bar_content .= "<p>".$fake_code."</p>";
$bar_content .= "<h4>$langProfessors</h4>";
$bar_content .= "<p>".$professor."</p>";
$bar_content .= "<h4>$langFaculty</h4>";
$bar_content .= "<p>".$faculte."</p>";
$bar_content .= "<h4>$m[type]</h4>";
$bar_content .= "<p>".$lessonType."</p>";

if ($is_adminOfCourse) {
	$sql = "SELECT COUNT(user_id) AS numUsers
			FROM cours_user
			WHERE code_cours = '$currentCourse'";
	$res = db_query($sql, $mysqlMainDb);
	while($result = mysql_fetch_row($res)) {
		$numUsers = $result[0];
	}

	//set the lang var for lessons visibility status

	switch ($visible){
		case 0: { //closed
			$lessonStatus = $langPrivate;
			break;
		}

		case 1: {//open with registration
			$lessonStatus = $langPrivOpen;
			break;
		}

		case 2: { //open
			$lessonStatus = $langPublic;
			break;
		}
	}

	$main_content .= "<br><hr><br>";
	$main_content .= "
		<table>
			<thead>
				<tr>
					<th>
						$langConfidentiality
					</th>
					<td>
					$lessonStatus
					</td>
				</tr>
				<tr>
					<th>
						$langNumOfUsers
					</th>
					<td>
					$numUsers
					</td>
				</tr>
			</thead>
		</table>
	
	";
}

// professor view

//$table= "stat_accueil";

// statistics  - Count only if first visit during the session
//if (!isset($alreadyHome) || (isset($alreadyHome) && !$alreadyHome)) {
//	include ("../../../modules/stat/write_logs.php");
//}

//$alreadyHome = 1;
//session_register("alreadyHome");

// function for displaying tools

//$tool_content .= "<p>Content viewable by everyone</p>";


//-----------------------------------------------------------

$tool_content .= <<<lCont
<div id="container_login">

<div id="wrapper">
<div id="content_login">
<p>$main_content</p>


</div>
</div>
<div id="navigation">

 <table width="99%">
     <tbody>
      	<tr class="odd">
      		<td>
      			$bar_content
     		</td>
     	</tr>
      </tbody>
      </table>


</div>
<div id="extra">
<p></p>
</div>
<!--<div id="footer"><p>Here it goes the footer</p></div>-->
</div>

lCont;

//-----------------------------------------------------------
draw($tool_content, 2,'course_home');
?>
