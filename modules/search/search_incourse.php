<?
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    	Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    	Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	search_incourse.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: Search function that searches data within a course.
        Requires $dbname to point to the course DB

   	This is an example of the MySQL queries used for searching:
   	SELECT * FROM articles WHERE MATCH (title,body,more_fields) AGAINST ('database') OR ('Security') AND ('lala')
==============================================================================*/


$require_current_course = TRUE;
$langFiles = array('course_info', 'create_course', 'opencours', 'search');

include '../../include/baseTheme.php';



/*// kwdikas gia debugging
mysql_select_db("$dbname");
$query = "SELECT * FROM accueil WHERE MATCH (rubrique,lien) AGAINST ('Έρευνα') OR ('video')";
echo "<br><br>$query<br><Br>";
$result = db_query($query);	

while($res = mysql_fetch_array($result))
{
	print_r($res);
	echo "<br><br><br>";
}
exit;
//tellos debugging kwdika
*/





$tool_content = "";

// ---------------------- Diasikasia domhshs tou query! -------------------------------
// afto to kommati kwdika analyei tous orous anazhthshs kai tous metatrepei se gekimevmeno erwthma SQL
// to erwthma periexetai sthn $query (den einai sthn telikh tou morfh alla xrhsimopoieitai san suffix parakatw)

//ean o xrhsths DEN exei ektelesei thn anazhthsh apo thn selida anazhthshs tote oi oroi
//anazhthshs einai sthn ousia oroi anazhthshs OR
if(@!empty($search_terms)) $or_search_terms = $search_terms;
if(@empty($or_search_terms)) $or_search_terms = "";
if(@empty($not_search_terms)) $not_search_terms = ""; //arxikopoihsh ths metavlhths ean einai adeia wste na apaleifthoun ta notices


$query = " AGAINST ('".$or_search_terms." ";

//ean yparxoun oroi NOT na prostethoun sto erwthma
if(!@empty($not_search_terms))
{
	$tmp = explode(" ", $not_search_terms);
	$query .= "-".implode(" -", $tmp);
}

$query .= "' IN BOOLEAN MODE)";
//$tool_content .= "το μέγα ερώτημα είναι: ".$query."<br><br><hr>";


//------------------------- Telos diadikasias domhshs tou query !----------------------



//elegxos ean *yparxoun* oroi anazhthshs
if(empty($or_search_terms) && empty($not_search_terms)) {
/**********************************************************************************************
				emfanish formas anahzthshs ean oi oroi anazhthshs einai kenoi 
***********************************************************************************************/
	
		$tool_content .= "
			<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
			<table width=\"99%\"><caption>$langSearch</caption>
			<tr>
				<td valign=\"middle\" align=\"right\">
					<b>$langOR</b>
				</td>				
				<td valign=\"middle\">
					<input name=\"or_search_terms\" type=\"text\" size=\"80\"/>
				</td>
				</tr>
				<tr>
				<td valign=\"middle\" align=\"right\">
					<b>$langNOT</b>
				</td>				
				<td valign=\"middle\">
					<input name=\"not_search_terms\" type=\"text\" size=\"80\" />
				</td>
				</tr>
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
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type=\"Submit\" name=\"submit\" value=\"$langNewSearch\" />
				</td>
			</tr>
			</table>
			</form>
		";
	
}else 
{
/**********************************************************************************************
					ektelesh anazhthshs afou yparxoun oroi anazhthshs
						 emfanish arikown mhnymatwn anazhthshs
***********************************************************************************************/
	
	
	//elegxos ean o xrhsths ektelei thn anazhthsh apo to input box tou UI
	if(@!empty($subsystems))
	{
		//h anazhthsh ektelesesthke apo th forma anazhthshs
		
		//arxikopoihsh tou array gia ta checkboxes
		for ($i=0; $i<=50; $i++)
		{
			$sbsystems[$i] = "0";
		}
		
		//allagh timwn sto array analoga me to poio checkbox exei epilegei
		foreach ( $subsystems as $sb )
		{
			$sbsystems[$sb] = "1";
		}
				
	}else 
	{
		//h anazhthsh ektelestike apo to inputbox tou UI
		//ola ta yposysthmata tha symperilifthoun sthn anazhthsh
		
		//arxikopoihsh tou array gia ta checkboxes
		for ($i=0; $i<=50; $i++)
		{
			$sbsystems[$i] = "1";
		}
		
	}
	
	
	//ektypwsh mhnymatwn anazhthshs
	$tool_content .= "<h2>$langSearchingFor</h2><h3>$langOR: $or_search_terms</h3><br>";
	if (@!empty($not_search_terms)) $tool_content .= "<h3>$langNOT: $not_search_terms</h3><br>";

	
	
	
	/******************************************************************************************************
										ektelesh erwthmatwn gia anazhthsh
	******************************************************************************************************/
			
	if($sbsystems["7"] == "1")
	{
		//-------------------------------------------------------------------------------------------------
		//anazhthsh ston pinaka annonces (anakoinwseis)
		//
		// h anazhthsh perilamvanei MONO to paron mathima
		//-------------------------------------------------------------------------------------------------
		$tool_content .= "$langAnnouncements<hr><ul>";
		
		//palios tropos: $query = "SELECT * FROM annonces WHERE (contenu LIKE '%".$search_terms."%' OR temps LIKE '%".$search_terms."%') AND code_cours='".$dbname."'";	
		
		//anazhthsh sthn kentrikh vash - epilogh ths kentrikhs DB
		mysql_select_db("$mysqlMainDb");
		$myquery = "SELECT * FROM annonces WHERE MATCH (contenu,code_cours)".$query;
		$result = db_query($myquery);	

		
		$c = 0;	
		while(@$res = mysql_fetch_array($result))
		{
			//emfanish apotelesmatos mono gia to yparxon mathima
			if($res["code_cours"] == $dbname)
			{
				$c++;
				$tool_content .= "<li>".$res['contenu'].": ".$res['temps']."<br>";
			}
		}
		if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
		
	}
	
	
	//epilosh ths vashs tou mathimatos
	mysql_select_db("$dbname");


	/*o pinakas den yparxei pleon (?)
	-------------------------------------------------------------------------------------------------
	//anazhthsh ston pinaka introduction (eisagwgiko mhnyma)
	$tool_content .= "</ul><br><br><br>$langIntroductionNote<hr><ul>";
	
	//$query = "SELECT * FROM introduction WHERE texte_intro LIKE '%".$search_terms."%'";	
	$myquery = "SELECT * FROM introduction WHERE MATCH (texte_intro)".$query;
	$result = mysql_query($myquery);	
	
	$c = 0;
	while($res = mysql_fetch_array($result))
	{
		$c++;
		$tool_content .= "<li>".$res['texte_intro']."<br>";
	}
	if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	*/
	
	
	
	
	if($sbsystems["1"] == "1")
	{

		//-------------------------------------------------------------------------------------------------
		//anazhthsh ston pinaka agenda
		$tool_content .= "</ul><br><br><br>$langAgenda<hr><ul>";
		
		//$query = "SELECT * FROM agenda WHERE titre LIKE '%".$search_terms."%' OR contenu LIKE '%".$search_terms."%'";	
		$myquery = "SELECT * FROM agenda WHERE MATCH (titre,contenu)".$query;
		$result = mysql_query($myquery);	
		
		$c = 0;
		while(@$res = mysql_fetch_array($result))
		{
			$c++;
			$tool_content .= "<li>".$res['titre'].": ".$res['contenu']."<br>";
		}
		if ($c == 0) $tool_content .= "<li>$langNoResult<br>";

	}	
	
	
	
	if($sbsystems["20"] == "1")
	{
		//-------------------------------------------------------------------------------------------------
		//anazhthsh ston pinaka course_description
		$tool_content .= "</ul><br><br><br>$langCourseDesc<hr><ul>";
		
		//$query = "SELECT * FROM course_description WHERE title LIKE '%".$search_terms."%' OR content LIKE '%".$search_terms."%'";	
		$myquery = "SELECT * FROM course_description WHERE MATCH (title,content)".$query;
		$result = mysql_query($myquery);	
		
		$c = 0;
		while(@$res = mysql_fetch_array($result))
		{
			$c++;
			$tool_content .= "<li>".$res['title'].": ".$res['content']."<br>";
		}
		if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
	}
	
	
	
		
	if($sbsystems["3"] == "1")
	{
		//-------------------------------------------------------------------------------------------------
		//anazhthsh ston pinaka documents (perioxh eggrafwn)
		$tool_content .= "</ul><br><br><br>$langDoc<hr><ul>";
		
		//$query = "SELECT * FROM document WHERE filename LIKE '%".$search_terms."%' OR comment LIKE '%".$search_terms."%' OR category LIKE '%".$search_terms."%' OR title LIKE '%".$search_terms."%' OR creator LIKE '%".$search_terms."%' OR subject LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%' OR author LIKE '%".$search_terms."%' OR language LIKE '%".$search_terms."%'";	
		$myquery = "SELECT * FROM document WHERE MATCH (filename,comment,title,creator,subject,description,author,language)".$query;
		$result = mysql_query($myquery);

		$c = 0;
		while(@$res = mysql_fetch_array($result))
		{
			//apokrypsh twn eggrafwn pou exoun dhkwthei san invisible apo ton aplo mathiti
			if(check_prof())
			{
				$c++;
				$tool_content .= "<li><b>".$res['filename']."</b>: (".$res['comment'].")<br>";

			}elseif ($res["visibility"] == "v")
			{
				$c++;
				$tool_content .= "<li><b>".$res['filename']."</b>: (".$res['comment'].")<br>";
			}
		}
		if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
		
	}
	
	
	
	
	if($sbsystems["10"] == 1)
	{
		//-------------------------------------------------------------------------------------------------
		//anazhthsh ston pinaka excercises
		$tool_content .= "</ul><br><br><br>$langExercices<hr><ul>";
		
		//$query = "SELECT * FROM exercices WHERE titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";
		$myquery = "SELECT * FROM exercices WHERE MATCH (titre,description)".$query;
		$result = mysql_query($myquery);	
		
		$c = 0;
		while(@$res = mysql_fetch_array($result))
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
		
		//$query = "SELECT * FROM posts_text WHERE post_text LIKE '%".$search_terms."%'";
		$myquery = "SELECT * FROM posts_text WHERE MATCH (post_text)".$query;
		$result = mysql_query($myquery);	
		
		$c = 0;
		while(@$res = mysql_fetch_array($result))
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
		
		//$query = "SELECT * FROM liens WHERE url LIKE '%".$search_terms."%' OR titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";
		$myquery = "SELECT * FROM liens WHERE MATCH (url,titre,description)".$query;
		$result = mysql_query($myquery);
		
		$c = 0;
		while(@$res = mysql_fetch_array($result))
		{
			$c++;
			$tool_content .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")<br>";
		}
		if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
		
	}
	
	
	
	
	if($sbsystems["4"] == 1)
	{	
		//-------------------------------------------------------------------------------------------------
		//anazhthsh ston pinaka video
		$tool_content .= "</ul><br><br><br>$langVideo<hr><ul>";
		
		//$query = "SELECT * FROM video WHERE url LIKE '%".$search_terms."%' OR titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";
		$myquery = "SELECT * FROM video WHERE MATCH (url,titre,description)".$query;
		$result = mysql_query($myquery);
		
		$c = 0;
		while(@$res = mysql_fetch_array($result))
		{
			$c++;
			$tool_content .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")<br>";
		}
		if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
		
		
		
		
		
		//-------------------------------------------------------------------------------------------------
		//anazhthsh ston pinaka videolinks
		$tool_content .= "</ul><br><br><br>$langVideoLinks<hr><ul>";
		
		//$query = "SELECT * FROM videolinks WHERE url LIKE '%".$search_terms."%' OR titre LIKE '%".$search_terms."%' OR description LIKE '%".$search_terms."%'";
		$myquery = "SELECT * FROM videolinks WHERE MATCH (url,titre,description)".$query;
		$result = mysql_query($myquery);	
		
		$c = 0;
		while($res = mysql_fetch_array($result))
		{
			$c++;
			$tool_content .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")<br>";
		}
		if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
		
	}//telos if($sbsystems["3"] == 1) <- theorw pws videos & videolinks perilamvanetai sto idio checkbox
	
	
	//ektypwsh koumpiou gia nea anazhthsh
	$tool_content .= "<br><br><p align=\"center\"><form method=\"post\" action=\"$_SERVER[PHP_SELF]\"><input type=\"Submit\" name=\"submit\" value=\"$langNewSearch\" /></form></p>";
	

}//telos anazhthshs (if empty($search_terms) = false)

draw($tool_content, 2);


//katharisma ths $search_terms gia apofygh lathwn
$search_terms = "";
?>
