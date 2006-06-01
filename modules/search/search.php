<?

$require_current_course = TRUE;
$langFiles = array('course_info', 'create_course', 'opencours', 'search');







include '../../include/baseTheme.php';
//$nameTools = $langModifInfo;

$tool_content = "";
$head_content = "<script language=\"JavaScript\">
					function checkAll(field)
					{
					for (i = 0; i < field.length; i++)
						field[i].checked = true ;
					}
					
					function uncheckAll(field)
					{
					for (i = 0; i < field.length; i++)
						field[i].checked = false ;
					}
					
				</script>";






//$tool_content .= "<br><br>$search_terms<br><br>";



if(!isset($search_terms)) {
//emfanish formas gia anahzthsh
	
	
	$tool_content .= "
			<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
			<table width=\"99%\"><caption>Anazhthsh</caption><tbody>
			<tr>
				<td valign=\"middle\" align=\"right\">
					<b>Oroi anazhthshs:</b>
				</td>
				<td valign=\"middle\">
					<input name=\"search_terms\" type=\"text\" size=\"80\" />
				</td>
			</tr>
			<tr>
				<td valign=\"middle\" align=\"right\"><b>Anazhthsh se: </b></td>
				<td>
					<table width=\"99%\">
					
						<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"1\" checked=\"checked\" />
						$langAgenda</td>
						<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"2\" checked=\"checked\" />
						$langLinks</td>
						<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"3\" checked=\"checked\" />
						$langDoc</td>
						<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"4\" checked=\"checked\" />
						$langVideo</td>
						</tr>
						<tr>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"5\" />
						$langWorks</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"6\" />
						$langVideoLinks</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"7\" />
						$langAnnouncements</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"8\" />
						$langUsers</td>
						</tr>
						<tr>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"9\" />
						$langForums</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"10\" />
						$langExercices</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"11\" />
						$langStatistics</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"16\" />
						$langDropBox</td>
						</tr>
						
						<tr>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"20\" />
						$langCourseDesc</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"15\" />
						$langGroups</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"19\" />
						$langChat</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"21\" />
						$langSurvey</td>
						</tr>
						
						<tr>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"22\" />
						$langPoll</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						</tr>
					</table>
					
					<!-- <input type=\"button\" name=\"CheckAll\" value=\"Epilogh olwn\" onClick=\"checkAll(document.myform.subsystems)\">
					<input type=\"button\" name=\"UnCheckAll\" value=\"Epepilogh olwn\" onClick=\"uncheckAll(document.myform.subsystems)\"> -->
					
				</td>
			</tr>			
			<tr>
				<td colspan=\"2\" align=\"center\">
					<input type=\"Submit\" name=\"submit\" value=\"Anazhthsh\" />
				</td>
			</tr>
			</tbody></table>
			</form>
	
	
	
	
	
	
	
	
	";
	
}else 
{//if isset($submit) = true
	$tool_content .=$langSearchingFor."<br><h2>".$search_terms."...</h2><br><br>";

	
	//anazhthsh sthn kentrikh vash - epilogh ths kentrikhs DB
	mysql_select_db("$mysqlMainDb");
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka annonces (anakoinwseis)
	//
	// h anazhthsh perilamvanei mono to peron mathima
	//-------------------------------------------------------------------------------------------------
	$tool_content .= "$langAnnouncements<hr><ul>";
	
	$query = "SELECT * FROM annonces WHERE (contenu LIKE '%".$search_terms."%' OR temps LIKE '%".$search_terms."%') AND code_cours='".$currentCourseCode."'";	
	$result = mysql_query($query);	
	
	$c = 0;	
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['contenu'].": ".$res['temps']."<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	

	
	
	
	
	//anazhthsh sthn vash tou mathimatos
	mysql_select_db("$currentCourseCode");
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka agenda
	$tool_content .= "</ul><br><br><br>$langAgenda<hr><ul>";
	
	$query = "SELECT * FROM agenda WHERE titre LIKE '%".$search_terms."%' OR contenu LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['titre'].": ".$res['contenu']."<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";

	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka course_description
	$tool_content .= "</ul><br><br><br>$langCourseDesc<hr><ul>";
	
	$query = "SELECT * FROM course_description WHERE title LIKE '%".$search_terms."%' OR content LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['title'].": ".$res['content']."<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka documents (perioxh eggrafwn)
	$tool_content .= "</ul><br><br><br>$langDoc<hr><ul>";
	
	$query = "SELECT * FROM document WHERE filename LIKE '%".$search_terms."%' OR comment LIKE '%".$search_terms."%' OR category LIKE '%".$search_terms."%' OR title LIKE '%".$search_terms."%' OR creator LIKE '%".$search_terms."%' OR subject LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%' OR author LIKE '%".$search_terms."%' OR language LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li><b>".$res['filename']."</b>: (".$res['comment'].")<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka excercises
	$tool_content .= "</ul><br><br><br>$langExercices<hr><ul>";
	
	$query = "SELECT * FROM exercices WHERE titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['titre'].": ".$res['description']."<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka introduction (eisagwgiko mhnyma)
	$tool_content .= "</ul><br><br><br>$langIntroductionNote<hr><ul>";
	
	$query = "SELECT * FROM introduction WHERE texte_intro LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['texte_intro']."<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka posts_text (periexomeno mhnymatwn gia ta forums)
	$tool_content .= "</ul><br><br><br>$langForum<hr><ul>";
	
	$query = "SELECT * FROM posts_text WHERE post_text LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['post_text']."<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka liens (syndesmoi sto internet)
	$tool_content .= "</ul><br><br><br>$langLinks<hr><ul>";
	
	$query = "SELECT * FROM liens WHERE url LIKE '%".$search_terms."%' OR titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka pages (?)
	$tool_content .= "</ul><br><br><br>Selides<hr><ul>";
	
	$query = "SELECT * FROM pages WHERE url LIKE '%".$search_terms."%' OR titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka questions
	$tool_content .= "</ul><br><br><br>Questions<hr><ul>";
	
	$query = "SELECT * FROM questions WHERE question LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['question']." (".$res['description'].")<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka video
	$tool_content .= "</ul><br><br><br>$langVideo<hr><ul>";
	
	$query = "SELECT * FROM video WHERE url LIKE '%".$search_terms."%' OR titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	
	
	
	
	//-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka videolinks
	$tool_content .= "</ul><br><br><br>$langVideoLinks<hr><ul>";
	
	$query = "SELECT * FROM videolinks WHERE url LIKE '%".$search_terms."%' OR titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";	
	$result = mysql_query($query);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	
	

}//telos anazhthshs (if isset($submit) = true)

draw($tool_content,2,'search');


//katharisma ths $search_terms gia aofygh lathwn
$search_terms = "";
?>