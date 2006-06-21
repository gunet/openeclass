<?
//$require_current_course = TRUE;
$langFiles = array('course_info', 'create_course', 'opencours', 'search');

include '../../include/baseTheme.php';

$tool_content = "";

if(empty($search_terms)) {
//emfanish formas anahzthshs
	
		$tool_content .= "
			<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
			<table width=\"99%\"><caption>$langSearch</caption>
			<tr>
				<td valign=\"middle\" align=\"right\">
					<b>$langSearch_terms</b>
				</td>
				<td valign=\"middle\">
					<input name=\"search_terms\" type=\"text\" size=\"80\" />
				</td>
			</tr>
			<tr>
				<td valign=\"middle\" align=\"right\"><b>$langSearchIn</b></td>
				<td>
					<table width=\"99%\">
					<tr>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"7\" checked=\"checked\" />
						$langAnnouncements</td>
						<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"1\" checked=\"checked\" />
						$langAgenda</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"20\" checked=\"checked\" />
						$langCourseDesc</td>
						<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"3\" checked=\"checked\" />
						$langDoc</td>
					</tr>
					<tr>						
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"9\" checked=\"checked\" />
						$langForums</td>
						<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"10\" checked=\"checked\" />
						$langExercices</td>
						<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"4\" checked=\"checked\" />
						$langVideo</td>
						<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"2\" checked=\"checked\" />
						$langLinks</td>
					</tr>					
					</table>													
				</td>
			</tr>			
			<tr>
				<td colspan=\"2\" align=\"center\">
					<input type=\"Submit\" name=\"submit\" value=\"$langDoSearch\" />
				</td>
			</tr>
			</table>
			</form>
		";
	
}else 
{//if isset($submit) = true
	
	
	//arxikopoihsh tou array gia ta checkboxes
	for ($i=0; $i<=50; $i++)
	{
		$sbsystems[$i] = 0;
	}
	
	//allagh timwn sto array analoga me to poio checkbox exei epilegei
	foreach ( $subsystems as $sb )
	{
		$sbsystems[$sb] = 1;
	}
	
	
	//ektypwsh mhnymatwn anazhthshs
	$tool_content .=$langSearchingFor."<br><h2>".$search_terms."...</h2><br><br>";

		//anazhthsh sthn kentrikh vash - epilogh ths kentrikhs DB
//	mysql_select_db("$mysqlMainDb");
		
	if($sbsystems["7"] == 1 && !empty($currentCourseCode))
	{
		//-------------------------------------------------------------------------------------------------
		//anazhthsh ston pinaka annonces (anakoinwseis)
		//
		// h anazhthsh perilamvanei MONO to paron mathima
		//-------------------------------------------------------------------------------------------------
		$tool_content .= "$langAnnouncements<hr><ul>";
		
		$query = "SELECT * FROM annonces WHERE (contenu LIKE '%".$search_terms."%' OR temps LIKE '%".$search_terms."%') AND code_cours='".$currentCourseCode."'";	
		$result = db_query($query);	
		
		$c = 0;	
		while($res = mysql_fetch_array($result))
		{
			$c++;
			$tool_content .= "<li>".$res['contenu'].": ".$res['temps']."<br>";
		}
		if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
		
	}
	
	
	//anazhthsh sthn vash tou mathimatos
	mysql_select_db("$currentCourseCode");

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
	
	if($sbsystems["1"] == 1)
	{

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

	}	
	
	
	
	if($sbsystems["20"] == 1)
	{
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
	}
	
		
	if($sbsystems["3"] == 1)
	{
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
		
	}
	
	
	
	
	if($sbsystems["10"] == 1)
	{
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
	
	}
	
	
	if($sbsystems["9"] == 1)
	{
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
		
	}	
	
	
	
	if($sbsystems["2"] == 1)
	{
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
		
	}
	
	
	
/* afta ta theloume ?	
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

*/
	
	
	if($sbsystems["4"] == 1)
	{	
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
		
	}//telos if($sbsystems["3"] == 1) <- theoroume pws videos & videolinks perilamvanetai sto idio checkbox
	
	

}//telos anazhthshs (if empty($search_terms) = false)

draw($tool_content, 2);


//katharisma ths $search_terms gia apofygh lathwn
$search_terms = "";
?>
