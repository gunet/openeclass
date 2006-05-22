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
//include ('../../include/init.php');
include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php'); 

$showPedaSuggest = false;

$nameTools = $langEditCourseProgram ;
$navigation[]= array ("url"=>"index.php", "name"=> $langCourseProgram);
//begin_page();
$db = $_SESSION['dbname'];
if ($language == 'greek')
        $lang_editor='gr';
else
        $lang_editor='en';
        
      
$head_content = <<<hCont

<script type="text/javascript">
  _editor_url = '$urlAppend/include/htmlarea/';
  _css_url='$urlAppend/css/';
  _image_url='$urlAppend/include/htmlarea/images/';
  _editor_lang = '$lang_editor';
</script>
<script type="text/javascript" src='$urlAppend/include/htmlarea/htmlarea.js'></script>

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
hCont;
//<body onload="initEditor()">
//</td></tr>
//<tr>
//<td colspan="2">

$body_action = "onload=\"initEditor()\"";
//
//if(!isset($numBloc)){
//	unset($head_content);
//	unset($body_action);
//}
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
		db_query($sql, $db);
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
		db_query($sql, $db);
	}
	
//// Kill THE BLOC
	if (isset($deleteOK)) {
		
		$sql = "SELECT * FROM `course_description` where id = '".$_POST["edIdBloc"]."'";
		$res = db_query($sql,$db);
		$blocs = mysql_fetch_array($res);
		if (is_array($blocs)) {
			$tool_content .=  "<h4>$langBlockDeleted</h4>";
			$tool_content .=  "
			<div class=\"deleted\">
				<p>
					".$blocs["title"]."
				</p>
				<p>
				".$blocs["content"]."
				</p>
			</div>";
		}
		
		$sql ="DELETE FROM `course_description` WHERE id = '".$_POST["edIdBloc"]."'";
		$res = db_query($sql,$db);
		$tool_content .=  "
		<BR>
		<p><a href=\"".$_SERVER['PHP_SELF']."\">
			
				".$langBack."
			</a></p>";
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
	$tool_content .=  "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
				
		<p>
		<b>
		
			".@$titreBloc[$numBloc]."
		
		</b>
		<br><br>";
		if (isset($delete) and $delete == "ask") {
			$tool_content .=  "<input type=\"submit\" name=\"deleteOK\" value=\"".$langDelete."\"><br>";
		}

		if (($numBloc =="add") || @(!$titreBlocNotEditable[$numBloc])) { 
			$tool_content .=  "
					".$langOuAutreTitre."
				
				<br>
			<input type=\"text\" name=\"edTitleBloc\" size=\"50\" value=\"".@$titreBloc[$numBloc]."\" >";
		} else {
			$tool_content .=  "<input type=\"hidden\" name=\"edTitleBloc\" value=\"".$titreBloc[$numBloc]."\" >";
		}

		if ($numBloc =="add") { 
			$tool_content .=  "<input type=\"hidden\" name=\"edIdBloc\" value=\"add\">";
		} else {
			$tool_content .=  "<input type=\"hidden\" name=\"edIdBloc\" value=\"".$numBloc."\">";
		}
		$tool_content .=  "</p><table><tr><td valign=\"top\">";
		
	$tool_content .=  "<textarea id='ta' name='edContentBloc' value='".@$contentBloc."' rows='20' cols='70'>".@$contentBloc."</textarea>";
	$tool_content .=  "</td>";
	
	if ($showPedaSuggest) {
		if (isset($questionPlan[$numBloc])) {
			$tool_content .=  "<td valign=\"top\">		
				<table><tr>
				<td valign=\"top\" class=\"QuestionDePlanification\">		
				<b><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">".$langQuestionPlan."</font></b>
				<br><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">".$questionPlan[$numBloc]."</font>
				</td></tr></table>";
			}
			if (isset($info2Say[$numBloc])) {
				$tool_content .=  "<table><tr><td valign=\"top\" class=\"InfoACommuniquer\">		
				<b><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">$langInfo2Say</font></b>
				<br><font face=\"Arial, Helvetica, sans-serif\" size=\"2\">".$info2Say[$numBloc]."</font>
				</td></tr></table></td>";
			}
		}
		$tool_content .=  "</tr></table>
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
		$tool_content .=  "<table width=\"99%\">
		<thead>
		<tr>
		<th width=\"200\">
		$langAddCat
		</th>
		<td>
		
		<form class=\"category\" method=\"post\" action=\"$_SERVER[PHP_SELF]\">
		<select name=\"numBloc\" size=\"1\">";
		while (list($numBloc,) = each($titreBloc)) { 
			if (!isset($blocState[$numBloc])||$blocState[$numBloc]!="used")
			$tool_content .=  "<option value=\"".$numBloc."\">".$titreBloc[$numBloc]."</option>";
		}
		$tool_content .=  "<option value=\"add\">".$langNewBloc."</option></select>
		<input type=\"submit\" name=\"add\" value=\"".$langAdd."\"></form>
		</td></tr></thead></table>";
		$tool_content .= "<br><br>";
		
		reset($titreBloc);		
		while (list($numBloc,) = each($titreBloc)) { 
			if (isset($blocState[$numBloc])&&$blocState[$numBloc]=="used") {

				$tool_content .=  "<table width=\"99%\">";
				$tool_content .=  "
				<thead>
					<tr>
						<th width=\"20%\">".$titreBloc[$numBloc]."</th>
						<td>
						<div class=\"cellpos\">
							<a href=\"".$_SERVER['PHP_SELF']."?numBloc=".$numBloc."\">
								<img src=\"../../images/edit.gif\" border=\"0\" alt=\"".$langModify."\"></a>
					 
							<a href=\"".$_SERVER['PHP_SELF']."?delete=ask&numBloc=".$numBloc."\">
								<img src=\"../../images/delete.gif\" border=\"0\" alt=\"".$langDelete."\"></a>
						</div>
						</td>
					</tr>
					<tr>
						<td  colspan=\"2\">
					
						".make_clickable(nl2br($contentBloc[$numBloc]))."
					
						</td>
					</tr>";
				$tool_content .=  "</thead></table><br>";
			}
		}
		
	}
} else {
	exit();
}

// End of page
if(isset($numBloc)){
draw($tool_content, 2, '', $head_content, $body_action);
} else {
	draw($tool_content, 2, 'course_description');
}
?>

<!--</td></tr>
<tr name="bottomLine">
<td colspan=2><br>
<hr noshade size=1>-->


