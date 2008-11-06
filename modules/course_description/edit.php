<?
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
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

$tool_content = $head_content = "";
$nameTools = $langEditCourseProgram ;
$navigation[]= array ("url"=>"index.php", "name"=> $langCourseProgram);

$db = $_SESSION['dbname'];
if ($language == 'greek')
        $lang_editor='el';
else
        $lang_editor='en';


$head_content .= <<<hCont
<script type="text/javascript">
function confirmation ()
{
    if (confirm('$langConfirmDelete'))
        {return true;}
    else
        {return false;}
}
</script>
hCont;

$head_content .= <<<hCont
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
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
		if ($edTitleBloc == "") {
			$edTitleBloc = $titreBloc[$edIdBloc];
		}
		$sql ="UPDATE course_description SET title= ".quote(trim($edTitleBloc)).",
			content = ".quote(trim($edContentBloc)).",
			`upDate` = NOW()
			WHERE id = '".$_POST["edIdBloc"]."';";
		db_query($sql, $db);
	}

//Delete action
	if (isset($delete) and $delete == 'yes') {
		$sql ="DELETE FROM `course_description` WHERE id = '$_GET[numBloc]'";
		$res = db_query($sql,$db);
		$tool_content .= "<p class='success'>$langBlockDeleted<br><br><a href=\"".$_SERVER['PHP_SELF']."\">".$langBack."</a></p>";
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

    $tool_content .= "
    <form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
    <table width='99%' class='CourseDescr' align='left'>
    <tbody>
    <tr>
      <th class=\"left\">".$langTitle.": ".@$titreBloc[$numBloc]."</th>
    </tr>";
	if (($numBloc =="add") || @(!$titreBlocNotEditable[$numBloc])) {
		$tool_content .= "<tr><td>
		<input type=\"text\" name=\"edTitleBloc\" rows='20' cols='90' value=\"".@$titreBloc[$numBloc]."\">
		</td></tr>";
	} else {
		$tool_content .= "<input type=\"hidden\" name=\"edTitleBloc\" value=\"".$titreBloc[$numBloc]."\" >";
	}
		if ($numBloc =="add") {
			$tool_content .= "<input type=\"hidden\" name=\"edIdBloc\" value=\"add\">";
		} else {
			$tool_content .= "<input type=\"hidden\" name=\"edIdBloc\" value=\"".$numBloc."\">";
		}
		$tool_content .= "
    <tr>
      <td>
	<table class='xinha_editor'><tr><td>
<textarea id='xinha' width=\"100%\" name='edContentBloc' value='".@$contentBloc."'>".@$contentBloc."</textarea>
	</td></tr></table>
</td>
    </tr>";

	$tool_content .= "<tr>
      <th><input type=\"submit\" name=\"save\" value=\"".$langAdd."\">&nbsp;&nbsp;
          <input type=\"submit\" name=\"ignore\" value=\"".$langBackAndForget ."\">
      </th>
    </tr>";
	$tool_content .= "<tbody></tr></table></form>";
} else {
		$sql = "SELECT * FROM `course_description` order by id";
		$res = db_query($sql,$db);
		while($bloc = mysql_fetch_array($res)) {
			$blocState[$bloc["id"]] = "used";
			$titreBloc[$bloc["id"]]	= $bloc["title"];
			$contentBloc[$bloc["id"]] = $bloc["content"];
		}
		$tool_content .= "
    <form method='post' action='$_SERVER[PHP_SELF]'>

    <table width=\"99%\" align=\"left\" class=\"FormData\">
    <tbody>
    <tr>
      <th class=\"left\" width=\"220\">&nbsp;</th>
      <td><b>$langAddCat</b></td>
    </tr>
    <tr>
      <th class=\"left\">$langSelection :</th>
      <td><select name='numBloc' size='1' class='auth_input'>";
		while (list($numBloc,) = each($titreBloc)) {
			if (!isset($blocState[$numBloc])||$blocState[$numBloc] != "used")
				$tool_content .= "\n            <option value='".$numBloc."'>".$titreBloc[$numBloc]."</option>";
		}
		$tool_content .= "\n</select></td></tr><tr><th>&nbsp;</th>
      		<td><input type='submit' name='add' value='".$langAdd."'></td>
    		</tr></tbody></table>
    		<p>&nbsp;</p>
    </form>\n";

	reset($titreBloc);
		while (list($numBloc,) = each($titreBloc)) {
			if (isset($blocState[$numBloc]) && $blocState[$numBloc]=="used") {
				$tool_content .= "<table width=\"99%\" class=\"CourseDescr\">
    					<thead><tr><td>
        				<table width=\"100%\" class=\"FormData\">
        				<thead><tr>
          				<th class=\"left\" style=\"border: 1px solid #E6B45D;\">".$titreBloc[$numBloc].":</th>
          				<td width=\"50\" class=\"right\">
					<a href='".$_SERVER['PHP_SELF']."?numBloc=".$numBloc."' >
					<img src='../../template/classic/img/edit.gif' border='0' title='$langModify'></a>&nbsp;&nbsp;";
					$tool_content .= "<a href='$_SERVER[PHP_SELF]?delete=yes&numBloc=$numBloc' onClick='return confirmation();'><img src='../../images/delete.gif' border='0' title='$langDelete'></a>&nbsp;</td></tr></thead></table>
      					</td></tr><tr>
      				<td>".mathfilter(make_clickable(nl2br($contentBloc[$numBloc])), 12, "../../courses/mathimg/")."</td>
    				</tr></thead></table>";
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
	draw($tool_content, 2, 'course_description', $head_content);
}
?>
