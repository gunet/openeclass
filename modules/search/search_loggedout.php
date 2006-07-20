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
	search_loggedout.php
	@last update: 18-07-2006 by Sakis Agorastos
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: Search function that searches data within public courses
        This script is intened to be used by unlogged users (visitors)

   	This is an example of the MySQL queries used for searching:
   	SELECT * FROM articles WHERE MATCH (title,body,more_fields) AGAINST ('database') OR ('Security') AND ('lala')
==============================================================================*/

$require_current_course = FALSE;
$langFiles = array('course_info', 'create_course', 'opencours', 'search');
include '../../include/baseTheme.php';

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
	
		//ektypwsh mhnymatwn anazhthshs
	$tool_content .= "<h2>$langSearchingFor</h2><h3>$langOR: $or_search_terms</h3><br>";
	if (@!empty($not_search_terms)) $tool_content .= "<h3>$langNOT: $not_search_terms</h3><br>";

	
	
	
	
	//**************************************************************************************************************************************
	//vrogxos gia na diatreksoume ola ta mathimata sta opoia enai anoixta
	
	
	
	//////////////////////////////////////////////////////////////
	// ANAZHTHSH SE MATHIMATA POU O XRHSTHS EINAI EGGEGRAMMENOS //
	//////////////////////////////////////////////////////////////
	$result2 = mysql_query("SELECT DISTINCT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t
		FROM cours, cours_user WHERE cours.visible='2'");
    while ($mycours = mysql_fetch_array($result2)) 
    {
    	
		$tool_content .= "<h2>".$langSearchIn." ".$mycours['i']."</h2>";
		
		
		//***************** EKTELESH ERWTHMATWN *************************
		@$backup_dname = $dbname;
		$dbname = $mycours["k"];
		   		
				
		    	
		    	
		    /******************************************************************************************************
												ektelesh erwthmatwn gia anazhthsh
			******************************************************************************************************/
		    	
		    	
					//anazhthsh sthn kentrikh vash - epilogh ths kentrikhs DB
					mysql_select_db("$mysqlMainDb");
					
					
					//-------------------------------------------------------------------------------------------------
					//anazhthsh ston pinaka cours (pinakas mathimatos)
					//-------------------------------------------------------------------------------------------------
					$tool_content .= "$langCourseInfo<hr><ul>";
					
					$myquery = "SELECT * FROM cours WHERE MATCH (code,description,intitule,course_objectives,course_prerequisites,course_keywords,course_references)".$query;
					$result = db_query($myquery);


					$c = 0;	
					while(@$res = mysql_fetch_array($result))
					{
						//emfanish apotelesmatos mono gia to yparxon mathima
						if($res["code"] == $dbname)
						{
							$c++;
							$tool_content .= "<li>[".$res['code']."] ".$res['intitule'].": ".$res['description']."| ".$res['course_keywords']."<br>";
						}
					}
					if ($c == 0) $tool_content .= "<li>$langNoResult<br>";
					
					
					
					
					//-------------------------------------------------------------------------------------------------
					//anazhthsh ston pinaka annonces (anakoinwseis)
					//
					// h anazhthsh perilamvanei MONO to paron mathima
					//-------------------------------------------------------------------------------------------------
					$tool_content .= "</ul><br><br><br>$langAnnouncements<hr><ul>";
					
					//palios tropos: $query = "SELECT * FROM annonces WHERE (contenu LIKE '%".$search_terms."%' OR temps LIKE '%".$search_terms."%') AND code_cours='".$dbname."'";	
					
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
					

				
				
				//epilosh ths vashs tou mathimatos
				mysql_select_db("$dbname");
			
			
			
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
					

				
				
				//ektypwsh koumpiou gia nea anazhthsh
				$tool_content .= "</ul><br><br><p align=\"center\"><form method=\"post\" action=\"$_SERVER[PHP_SELF]\"><input type=\"Submit\" name=\"submit\" value=\"$langNewSearch\" /></form></p>";
			//********************** TELOS EKTELESHS ERWTHMATWN *******************************
    }
    
}

draw($tool_content, 0);


//katharisma ths $search_terms gia apofygh lathwn
$search_terms = "";

?>
