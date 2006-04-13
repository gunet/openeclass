<?
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$                |
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

$showPedaSuggest = false;

$nameTools = $langEditCourseProgram ;
$navigation[]= array ("url"=>"index.php", "name"=> $langCourseProgram);
begin_page();

if ($language == 'greek')
        $lang_editor='gr';
else
        $lang_editor='en';
?>

<script type="text/javascript">
  _editor_url = '<?= $urlAppend ?>/include/htmlarea/';
  _css_url='<?= $urlAppend ?>/css/';
  _image_url='<?= $urlAppend ?>/include/htmlarea/images/';
  _editor_lang = '<?= $lang_editor ?>';
</script>
<script type="text/javascript" src='<?= $urlAppend ?>/include/htmlarea/htmlarea.js'></script>

<script type="text/javascript">
var editor = null;

function initEditor() {

  var config = new HTMLArea.Config();
  config.height = '180px';
  config.hideSomeButtons(" showhelp undo redo popupeditor ");

  editor = new HTMLArea("ta",config);

  // comment the following two lines to see how customization works
  editor.generate();
  return false;
}

</script>

<body onload="initEditor()">
</td></tr>
<tr>
<td colspan="2">

<?
if ($is_adminOfCourse) { 

//// SAVE THE BLOC
	if (isset($save))
	{
	// it's second  submit,  data  must be write in db
	// if edIdBloc contain Id  was edited
	// So  if  it's add,   line  must be created
	
		if($_POST["edIdBloc"]=="add")
		{
		    $sql="SELECT MAX(id) as idMax from course_description";
			$res = db_query($sql);
			$idMax = mysql_fetch_array($res);
			$idMax = max(sizeof($titreBloc),$idMax["idMax"]);
			$sql ="
	INSERT IGNORE
		INTO `course_description` 
		(`id`) 
		VALUES
		('".($idMax+1)."');";
		$_POST["edIdBloc"]= $idMax+1;
		}
		else
		{
			$sql ="
	INSERT IGNORE
		INTO `course_description` 
		(`id`) 
		VALUES 
		('".$_POST["edIdBloc"]."');";
		}
		db_query($sql);
		if ($edTitleBloc=="")
		{
			$edTitleBloc = $titreBloc[$edIdBloc];
		};
		$sql ="
		UPDATE 
		`course_description` 
		SET
		`title`= '".trim($edTitleBloc)."',
		`content` ='".trim($edContentBloc)."',
		`upDate` = NOW() 
		WHERE id = '".$_POST["edIdBloc"]."';";
		db_query($sql);
	}
	
//// Kill THE BLOC
	if (isset($deleteOK)) {
		$sql = "SELECT * FROM `course_description` where id = '".$_POST["edIdBloc"]."'";
		$res = db_query($sql,$db);
		$blocs = mysql_fetch_array($res);
		if (is_array($blocs)) {
			echo "<h4>$langBlockDeleted</h4>";
			echo "
			<div class=\"deleted\">
				<B>
					".$blocs["title"]."
				</B>
				<BR>
				".$blocs["content"]."
			</div>";
		}
		
		$sql ="DELETE FROM `course_description` WHERE id = '".$_POST["edIdBloc"]."'";
		$res = db_query($sql,$db);
		echo "
		<BR>
		<a href=\"".$_SERVER['PHP_SELF']."\">
			<font face=\"Arial, Helvetica, sans-serif\" size=\"2\">
				".$langBack."
			</font></a>";
	}
//// Edit THE BLOC 
	elseif(isset($numBloc)) {
		if (is_numeric($numBloc)) {
			$sql = "SELECT * FROM `course_description` where id = '".$numBloc."'";
			$res = db_query($sql,$db);
			$blocs = mysql_fetch_array($res);
			if (is_array($blocs))
			{
				$titreBloc[$numBloc]=$blocs["title"];
				$contentBloc = $blocs["content"];
			}
		}
	echo "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
		<p>
		<b>
		<font face=\"Arial, Helvetica, sans-serif\" size=\"2\">
			".@$titreBloc[$numBloc]."
		</font>
		</b>
		<br><br>";
		if (isset($delete) and $delete == "ask") {
			echo "<input type=\"submit\" name=\"deleteOK\" value=\"".$langDelete."\"><br>";
		}

		if (($numBloc =="add") || @(!$titreBlocNotEditable[$numBloc])) { 
			echo "<font face=\"Arial, Helvetica, sans-serif\" size=\"2\">
					".$langOuAutreTitre."
				</font>
				<br>
			<input type=\"text\" name=\"edTitleBloc\" size=\"50\" value=\"".@$titreBloc[$numBloc]."\" >";
		} else {
			echo "<input type=\"hidden\" name=\"edTitleBloc\" value=\"".$titreBloc[$numBloc]."\" >";
		}

		if ($numBloc =="add") { 
			echo "<input type=\"hidden\" name=\"edIdBloc\" value=\"add\">";
		} else {
			echo "<input type=\"hidden\" name=\"edIdBloc\" value=\"".$numBloc."\">";
		}
		echo "</p><table><tr><td valign=\"top\">";
		
	echo "<textarea id='ta' name='edContentBloc' value='".@$contentBloc."' rows='20' cols='70'>".@$contentBloc."</textarea>";
	echo "</td>";
	
	if ($showPedaSuggest) {
		if (isset($questionPlan[$numBloc])) {
			echo "<td valign=\"top\">		
				<table><tr>
				<td valign=\"top\" class=\"QuestionDePlanification\">		
				<b><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">".$langQuestionPlan."</font></b>
				<br><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">".$questionPlan[$numBloc]."</font>
				</td></tr></table>";
			}
			if (isset($info2Say[$numBloc])) {
				echo "<table><tr><td valign=\"top\" class=\"InfoACommuniquer\">		
				<b><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">$langInfo2Say</font></b>
				<br><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">".$info2Say[$numBloc]."</font>
				</td></tr></table></td>";
			}
		}
		echo "</tr></table>
		<input type=\"submit\" name=\"save\" value=\"".$langValid."\">
		<input type=\"submit\" name=\"ignore\" value=\"".$langBackAndForget ."\"></form>";
	} else {
		$sql = " SELECT * FROM `course_description` order by id";
		$res = db_query($sql,$db);
		while($bloc = mysql_fetch_array($res))
		{
			$blocState[$bloc["id"]] = "used";
			$titreBloc[$bloc["id"]]	= $bloc["title"];
			$contentBloc[$bloc["id"]] = $bloc["content"];
		}
		echo "<table width=\"100%\"><tr>
		<td valign=\"middle\">
		<b><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">$langAddCat</font></b>
		</td>
		<td align=\"right\" valign=\"middle\">
		
		<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
		<select name=\"numBloc\" size=\"1\">";
		while (list($numBloc,) = each($titreBloc)) { 
			if (!isset($blocState[$numBloc])||$blocState[$numBloc]!="used")
			echo "<option value=\"".$numBloc."\">".$titreBloc[$numBloc]."</option>";
		}
		echo "<option value=\"add\">".$langNewBloc."</option></select>
		<input type=\"submit\" name=\"add\" value=\"".$langAdd."\"></form>
		</td></tr></table>";
		echo "<table width=\"100%\"><tr><td colspan=\"2\" bgcolor=\"".$color2."\"></td></tr>";
		reset($titreBloc);		
		while (list($numBloc,) = each($titreBloc)) { 
			if (isset($blocState[$numBloc])&&$blocState[$numBloc]=="used") {
				echo "<tr><td  bgcolor=\"$color1\">
					<h4>".$titreBloc[$numBloc]."</h4></td>
					<td align=\"left\">
					<a href=\"".$_SERVER['PHP_SELF']."?numBloc=".$numBloc."\">
					<font size=\"2\" face=\"Arial, Helvetica, sans-serif\" >".$langModify."</font></a>
					| 
					<a href=\"".$_SERVER['PHP_SELF']."?delete=ask&numBloc=".$numBloc."\">
					<font size=\"2\" face=\"Arial, Helvetica, sans-serif\">".$langDelete."</font></a>
					</td>
					</tr>
					<tr><td colspan=\"2\">
					<font face=\"Arial, Helvetica, sans-serif\" size=\"2\">
					".make_clickable(nl2br($contentBloc[$numBloc]))."
					</font>
					</td></tr>";
			}
		}
		echo "</table>";
	}
} else {
	exit();
}

// End of page
?>

</td></tr>
<tr name="bottomLine">
<td colspan=2><br>
<hr noshade size=1>

<?
end_page();
?>

