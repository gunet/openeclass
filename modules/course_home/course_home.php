<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/*
 * Course Home Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component creates the content for the course's home page
 *
 */

$require_current_course = TRUE;
$guest_allowed = true;

//$courseHome is used by the breadcrumb logic 
//See function draw() in baseTheme.php for details
//$courseHome = true;
//$path2add is used in init.php to fix relative paths
$path2add=1;
include '../../include/baseTheme.php';
$nameTools = $langIdentity;
$tool_content = "";
$main_content = "";
$bar_content = "";

//For statistics: record login
global $uid, $currentCourse, $REMOTE_ADDR;
$sql_log = "INSERT INTO logins SET user_id='$uid', ip='$REMOTE_ADDR', date_time=NOW()";
db_query($sql_log, $currentCourse);

$sql = 'SELECT `description`,`course_keywords`, `course_addon`,`faculte`,`lastEdit`,`type`, `visible`, `titulaires`, `fake_code` FROM `cours` WHERE `code` = "'.$currentCourse.'"';
$res = db_query($sql, $mysqlMainDb);
while($result = mysql_fetch_row($res)) {
	$description = $result[0];
	$keywords = $result[1];
	$addon = $result[2];
	$faculte = $result[3];
	$type = $result[5];
	$visible = $result[6];
	$professor = $result[7];
	$fake_code = $result[8];

}

if(strlen($description) > 0) {
	$main_content .= "<div id=\"course_home_id\">$langDescription</div><p>$description</p>";
}

if (strlen($keywords) > 0) {
	$main_content .= "<p><b>$langCourseKeywords </b>$keywords</p>";
}

if(strlen($addon) > 0) {
	$main_content .= "<div id=\"course_home_id\">$langCourseAddon</div><p>". nl2br($addon)."</p>";
}


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

$bar_content .= "<p><b>".$langLessonCode."</b>: ".$fake_code."</p>";
$bar_content .= "<p><b>".$langTeachers."</b>: ".$professor." <a href='../../modules/contact/index.php'>(".$langEmail.")</a></p>";
$bar_content .= "<p><b>".$langFaculty."</b>: ".$faculte."</p>";
$bar_content .= "<p><b>".$m['type']."</b>: ".$lessonType."</p>";

$require_help = TRUE;
$helpTopic = $is_adminOfCourse? 'course_home_prof': 'course_home_stud';

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
	$bar_content .= "<p><b>".$langConfidentiality."</b>: ".$lessonStatus."</p>";
	$bar_content .= "<p><b>".$langUsers."</b>: ".$numUsers." ".$langRegistered."</p>";
}


$tool_content .= <<<lCont
<div id="container_login">
<div id="wrapper">
<div id="content_login"><p>$main_content</p></div>
</div>
<div id="navigation">
  <table width="99%">
  <tbody>
  <tr>
    <td class="odd">
    $bar_content
    </td>
  </tr>
  </tbody>
  </table>
</div>
</div>

lCont;

//-----------------------------------------------------------
draw($tool_content, 2,'course_home');
?>
