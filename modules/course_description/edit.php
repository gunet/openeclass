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
 * Edit, Course Description
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract Actions for add/edit/delete portions of a course's descriptions
 *
 * Based on previous code of eclass 1.6
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Coursedescription';
$require_login = true;
$require_prof = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
// support for math symbols
include '../../include/phpmathpublisher/mathpublisher.php';

$tool_content = "";
$nameTools = $langEditCourseProgram ;
$navigation[]= array ("url"=>"index.php", "name"=> $langCourseProgram);

$db = $_SESSION['dbname'];
if ($language == 'greek')
        $lang_editor='el';
else
        $lang_editor='en';

$head_content = <<<hCont
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
        _editor_skin = "silva";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>
hCont;

$body_action = "onload=\"initEditor()\"";

if ($is_adminOfCourse) {

//Save  actions
	if (isset($save)) {
		if($_POST["edIdBloc"]=="add") {
		    $sql="SELECT MAX(id) as idMax from course_description";
			$res = db_query($sql, $db);
			$idMax = mysql_fetch_array($res);
			$idMax = max(sizeof($titreBloc),$idMax["idMax"]);
			$sql ="INSERT IGNORE INTO `course_description` (`id`)
				VALUES ('".($idMax+1)."');";
			$_POST["edIdBloc"]= $idMax+1;
		} else {
			$sql ="INSERT IGNORE INTO `course_description`(`id`)
				VALUES ('".$_POST["edIdBloc"]."');";
			}
		db_query($sql, $db);
		if ($edTitleBloc=="") {
			$edTitleBloc = $titreBloc[$edIdBloc];
		}
		$sql ="UPDATE course_description SET title= ".quote(trim($edTitleBloc)).",
			content = ".quote(trim($edContentBloc)).",
			`upDate` = NOW()
			WHERE id = '".$_POST["edIdBloc"]."';";
		db_query($sql, $db);
	}

//Delete action
	if (isset($deleteOK)) {
		$sql = "SELECT * FROM `course_description` where id = '".$_POST["edIdBloc"]."'";
		$res = db_query($sql,$db);
		$blocs = mysql_fetch_array($res);
		if (is_array($blocs)) {
			$tool_content .= "<h4>$langBlockDeleted</h4>";
			$tool_content .= "<div class=\"deleted\">
			<p>".$blocs["title"]."</p>
			<p>".mathfilter($blocs["content"], 12, "../../courses/mathimg/")."</p></div>";
		}
		$sql ="DELETE FROM `course_description` WHERE id = '".$_POST["edIdBloc"]."'";
		$res = db_query($sql,$db);
		$tool_content .= "<br><p><a href=\"".$_SERVER['PHP_SELF']."\">".$langBack."</a></p>";
	}
//Edit action
	elseif(isset($numBloc)) {
		if (is_numeric($numBloc)) {
			$sql = "SELECT * FROM `course_description`
				WHERE id = '".mysql_real_escape_string($numBloc)."'";
			$res = db_query($sql,$db);
			$blocs = mysql_fetch_array($res);
			if (is_array($blocs)) {
				$titreBloc[$numBloc]=$blocs["title"];
				$contentBloc = $blocs["content"];
			}
		}

// delete confirmation
if (isset($delete) and $delete == "ask") {
	$tool_content .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">";
        $sql = "SELECT * FROM `course_description` where id = '".$numBloc."'";
        $res = db_query($sql,$db);
        $blocs = mysql_fetch_array($res);
        if (is_array($blocs)) {
         	$tool_content .= "<table align=center width=96% border=\"0\" >";
		$tool_content .= "<tr><td class=color2>";
    		$tool_content .= "<b>".$blocs["title"].":</b></td></tr><tr><td class=color1>".mathfilter($blocs["content"], 12, "../../courses/mathimg/")."";
		$tool_content .= "</td></tr></table>";
		$tool_content .= "<table align=center width=96% border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		$tool_content .= "<tr><td>";
		$tool_content .= "<input type=\"hidden\" name=\"edIdBloc\" value=\"".$numBloc."\">";
		$tool_content .= "<br><div id=\"operations_container\">
		<input type=\"submit\" name=\"deleteOK\" value=\"".$langDelete."\"></div><br></td></tr></table>";
        }
	$tool_content .= "</form><br>";
    } else {

    $tool_content .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
            <table width='99%' class='FormData' align='center'>
            <tbody><tr><th>&nbsp;</th><td><b>".@$titreBloc[$numBloc]."</b></td></tr>";
		if (($numBloc =="add") || @(!$titreBlocNotEditable[$numBloc])) {
			$tool_content .= "<tr><th class='left' width='100'>".$langTitle."</th>
              		<td><input type=\"text\" name=\"edTitleBloc\" size=\"50\" value=\"".@$titreBloc[$numBloc]."\" class='FormData_InputText''></td></tr>";
		} else {
			$tool_content .= "<input type=\"hidden\" name=\"edTitleBloc\" value=\"".$titreBloc[$numBloc]."\" >";
		}
		if ($numBloc =="add") {
			$tool_content .= "<input type=\"hidden\" name=\"edIdBloc\" value=\"add\">";
		} else {
			$tool_content .= "<input type=\"hidden\" name=\"edIdBloc\" value=\"".$numBloc."\">";
		}
		$tool_content .= "<tr><th class='left' width='100'>&nbsp;</th>
              	<td><textarea id='xinha' name='edContentBloc' value='".@$contentBloc."' rows='20' cols='70'>".@$contentBloc."</textarea></td></tr>";

	$tool_content .= "<tr><th class='left'>&nbsp;</th><td>
              <input type=\"submit\" name=\"save\" value=\"".$langAdd."\">&nbsp;&nbsp;
              <input type=\"submit\" name=\"ignore\" value=\"".$langBackAndForget ."\">
              </td></tr>";
	$tool_content .= "<tbody></tr></table></form>";
	}
} else {
		$sql = "SELECT * FROM `course_description` order by id";
		$res = db_query($sql,$db);
		while($bloc = mysql_fetch_array($res)) {
			$blocState[$bloc["id"]] = "used";
			$titreBloc[$bloc["id"]]	= $bloc["title"];
			$contentBloc[$bloc["id"]] = $bloc["content"];
		}
		$tool_content .= "<form class='category' method='post' action='$_SERVER[PHP_SELF]'>
		<div id='operations_container'>
		<small><b>$langAddCat :</small></b>&nbsp;&nbsp;&nbsp;
		<select name='numBloc' size='1' class='auth_input'>";
		while (list($numBloc,) = each($titreBloc)) {
			if (!isset($blocState[$numBloc])||$blocState[$numBloc] != "used")
				$tool_content .= "<option value='".$numBloc."'>".$titreBloc[$numBloc]."</option>";
		}
		$tool_content .= "</select>&nbsp;&nbsp;
		<input type='submit' name='add' value='".$langAdd."'></div></form><br>";
		reset($titreBloc);
		while (list($numBloc,) = each($titreBloc)) {
			if (isset($blocState[$numBloc]) && $blocState[$numBloc]=="used") {

	$tool_content .= "
    <table width=\"99%\" class=\"FormData\">
    <thead>
    <tr>
      <th class=\"left\" width=\"220\" style=\"border: 1px solid #edecdf;\"><u>".$titreBloc[$numBloc]."</u></th>
      <th width=\"50\" class=\"right\" style=\"border: 1px solid #edecdf\"><a href='".$_SERVER['PHP_SELF']."?numBloc=".$numBloc."' ><img src='../../template/classic/img/edit.gif' border='0' title='".$langModify."'></a>&nbsp;<a href='".$_SERVER['PHP_SELF']."?delete=ask&numBloc=".$numBloc."'><img src='../../images/delete.gif' border='0' title='".$langDelete."'></a></th>
      <td></td>
    </tr>
    </thead>
    </table>

    <table width=\"99%\" class=\"CourseDescr\">
    <thead>
    <tr>
      <td>".mathfilter(make_clickable(nl2br($contentBloc[$numBloc])), 12, "../../courses/mathimg/")."</td>
    </tr>
    </tgead>
    </table>";

	$tool_content .= "<br />";
			}
		}
	}
} else {
	exit();
}

// End of page
if(isset($numBloc)) {
	draw($tool_content, 2, 'course_description', $head_content, $body_action);
} else {
	draw($tool_content, 2, 'course_description');
}
?>
