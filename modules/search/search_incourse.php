<?
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
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
	@version $Id$
	@authors list: Agorastos Sakis <th_agorastos@hotmail.com>
==============================================================================        
        @Description: Search function that searches data within a course.
        Requires $dbname to point to the course DB

   	This is an example of the MySQL queries used for searching:
   	SELECT * FROM articles WHERE MATCH (title,body,more_fields) AGAINST ('database') OR ('Security') AND ('lala')
==============================================================================*/


$require_current_course = TRUE;

$guest_allowed = true;
include '../../include/baseTheme.php';

$nameTools = $langSearch;

$tool_content = "";
if(isset($_POST['search_terms'])) $search_terms = $_POST['search_terms'];
// ---------------------- Diasikasia domhshs tou query! -------------------------------
// afto to kommati kwdika analyei tous orous anazhthshs kai tous metatrepei se gekimevmeno erwthma SQL
// to erwthma periexetai sthn $query (den einai sthn telikh tou morfh alla xrhsimopoieitai san suffix parakatw)

//ean o xrhsths DEN exei ektelesei thn anazhthsh apo thn selida anazhthshs tote oi oroi
//anazhthshs einai sthn ousia oroi anazhthshs OR
if(@!empty($search_terms)) $or_search_terms = mysql_real_escape_string($search_terms);
if(@empty($or_search_terms)) $or_search_terms = "";
else $or_search_terms = mysql_real_escape_string($or_search_terms);
if(@empty($not_search_terms)) $not_search_terms = ""; //arxikopoihsh ths metavlhths ean einai adeia wste na apaleifthoun ta notices

$query = " AGAINST ('".$or_search_terms." ";

//ean yparxoun oroi NOT na prostethoun sto erwthma
if(!@empty($not_search_terms))
{
	$tmp = explode(" ", $not_search_terms);
	$query .= "-".implode(" -", $tmp);
}

$query .= "' IN BOOLEAN MODE)";
//------------------------- Telos diadikasias domhshs tou query !----------------------


//elegxos ean *yparxoun* oroi anazhthshs
if(empty($or_search_terms) && empty($not_search_terms)) {
/**********************************************************************************************
	emfanish formas anahzthshs ean oi oroi anazhthshs einai kenoi 
***********************************************************************************************/
	
	$tool_content .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">
	<table width=\"99%\"><tbody><tr><th class='left'>$langOR</th>				
	<td colspan=\"2\"><input class='FormData_InputText' name=\"or_search_terms\" type=\"text\" size=\"80\"/></td>
	</tr>
	<tr>
	<th width=\"30%\" class='left' valign=\"top\" rowspan=\"4\">$langSearchIn</th>
	<td width=\"35%\"><input type=\"checkbox\" name=\"subsystems[]\" value=\"7\" checked=\"checked\" />$langAnnouncements</td>
	<td width=\"35%\"><input name=\"subsystems[]\" type=\"checkbox\" value=\"1\" checked=\"checked\" />$langAgenda</td>
    </tr>
	<tr>
	<td><input type=\"checkbox\" name=\"subsystems[]\" value=\"20\" checked=\"checked\" />$langCourseDescription</td>
        <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"3\" checked=\"checked\" />$langDoc</td>
    </tr>
	<tr>
	<td><input class='FormData_InputText' type=\"checkbox\" name=\"subsystems[]\" value=\"9\" checked=\"checked\" />$langForums</td>
        <td><input type=\"checkbox\" name=\"subsystems[]\" value=\"10\" checked=\"checked\" />$langExercices</td>
    </tr>
	<tr>
	<td><input name=\"subsystems[]\" type=\"checkbox\" value=\"4\" checked=\"checked\" />$langVideo</td>
        <td><input name=\"subsystems[]\" type=\"checkbox\" value=\"2\" checked=\"checked\" />$langLinks</td>
    </tr>
	<tr>
	<th>&nbsp;</th>
	<td colspan=\"2\"><input type=\"Submit\" name=\"submit\" value=\"$langDoSearch\" />&nbsp;<input type=\"Submit\" name=\"submit\" value=\"$langNewSearch\" /></td>
    </tr>
	</tbody></table></form>";
	
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
				
	} else 
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
	if (@!empty($not_search_terms)) $tool_content .= "<h3>$langNOT: $not_search_terms</h3><br>";
	$tool_content .= "<p>";
		
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
		$tmp_result = "<div id=\"marginForm\"><fieldset>
		<legend>$langAnnouncements</legend><label>
		<ul class=\"listBullet\">";		
		
		//anazhthsh sthn kentrikh vash - epilogh ths kentrikhs DB
		mysql_select_db("$mysqlMainDb");
		$myquery = "SELECT * FROM annonces WHERE MATCH (contenu,code_cours)".$query;
		$result = db_query($myquery);	

		$c = 0;	
		if(mysql_num_rows($result) > 0)
		{
			while($res = mysql_fetch_array($result))
			{
				//emfanish apotelesmatos mono gia to yparxon mathima
				if($res["code_cours"] == $dbname)
				{
					$c++;
					$tmp_result .= "<li>".$res['contenu'].": ".$res['temps']."<br>";
				}
			}
		}
		if ($c != 0) $tool_content .= $tmp_result."</ul>
		</label>
		<div class=\"clearer\"></div>
		</fieldset>
		</div>
		";		
	}
	
	//epilosh ths vashs tou mathimatos
	mysql_select_db("$dbname");

	if($sbsystems["1"] == "1") {

	//anazhthsh ston pinaka agenda
	$tmp_result = "<div id=\"marginForm\"><fieldset>
	<legend>$langAgenda</legend>
	<label><ul class=\"listBullet\">";	
	$myquery = "SELECT * FROM agenda WHERE MATCH (titre,contenu)".$query;
	$result = mysql_query($myquery);	
		
	$c = 0;
	if(mysql_num_rows($result) > 0) {
		while(@$res = mysql_fetch_array($result))
		 {
			$c++;
			$tmp_result .= "<li>".$res['titre'].": ".$res['contenu']."</li>";
			}
		}
		if ($c != 0) $tool_content .= $tmp_result."</ul>
		</label>
		<div class=\"clearer\"></div>
		</fieldset>
		</div>
		";
	}	
	
	if($sbsystems["20"] == "1") {
	//anazhthsh ston pinaka course_description
	$tmp_result = "<div id=\"marginForm\">
	<fieldset><legend>$langCourseDescription</legend><label><ul class=\"listBullet\">";	
	$myquery = "SELECT * FROM course_description WHERE MATCH (title,content)".$query;
	$result = mysql_query($myquery);	
	$c = 0;
	if(mysql_num_rows($result) > 0) {
		while(@$res = mysql_fetch_array($result))
		{
			$c++;
			$tmp_result .= "<li>".$res['title'].": ".$res['content']."</li>";
		}
	}
	if ($c != 0) $tool_content .= $tmp_result."</ul>
	</label>
	<div class=\"clearer\"></div>
	</fieldset>
	</div>
		";
	}
	
		
	if($sbsystems["3"] == "1")
	{
	
		//anazhthsh ston pinaka documents (perioxh eggrafwn)
		$tmp_result = "<div id=\"marginForm\"><fieldset>
		<legend>$langDoc</legend><label>
		<ul class=\"listBullet\">";	
		$myquery = "SELECT * FROM document WHERE MATCH (filename,comment,title,creator,subject,description,author,language)".$query;
		$result = mysql_query($myquery);
		$c = 0;
		if(mysql_num_rows($result) > 0)
		{
			while($res = mysql_fetch_array($result))
			{
				//apokrypsh twn eggrafwn pou exoun dhkwthei san invisible apo ton aplo mathiti
				if($is_adminOfCourse)
				{
					$c++;
					$tmp_result .= "<li><b>".$res['filename']."</b>: (".$res['comment'].")</li>";
	
				}elseif ($res['visibility'] == "v")
				{
					
					$c++;
					$tmp_result .= "<li><b>".$res['filename']."</b>: (".$res['comment'].")</li>";
				}
			}
		}
		if ($c != 0) $tool_content .= $tmp_result."</ul>
		</label>
				<div class=\"clearer\"></div>
			</fieldset>
			</div>
		";
		
	}
	
	
	
	if($sbsystems["10"] == 1)
	{
		//anazhthsh ston pinaka excercises
		$tmp_result = "
		<div id=\"marginForm\"><fieldset>
		<legend>$langExercices</legend>
		<label>
		<ul class=\"listBullet\">";	

		$myquery = "SELECT * FROM exercices WHERE MATCH (titre,description)".$query;
		$result = mysql_query($myquery);	
		
		$c = 0;
		if(mysql_num_rows($result) > 0)
		{
			while(@$res = mysql_fetch_array($result))
			{
				$c++;
				$tmp_result .= "<li>".$res['titre'].": ".$res['description']."</li>";
			}
		}
		if ($c != 0) $tool_content .= $tmp_result."</ul>
		</label>
				<div class=\"clearer\"></div>
			</fieldset>
			</div>
		";
	
	}
	
	
	if($sbsystems["9"] == 1)
	{
		
		//anazhthsh ston pinaka posts_text (periexomeno mhnymatwn gia ta forums)
		$tmp_result = "<div id=\"marginForm\"><fieldset>
		<legend>$langForum</legend><label><ul class=\"listBullet\">";	
		
		//$query = "SELECT * FROM posts_text WHERE post_text LIKE '%".$search_terms."%'";
		$myquery = "SELECT * FROM posts_text WHERE MATCH (post_text)".$query;
		$result = mysql_query($myquery);	
		
		$c = 0;
		if(mysql_num_rows($result) > 0)
		{
			while(@$res = mysql_fetch_array($result))
			{
				$c++;
				$tmp_result .= "<li>".$res['post_text']."</li>";
			}
		}
		if ($c != 0) $tool_content .= $tmp_result."</ul>
		</label>
				<div class=\"clearer\"></div>
			</fieldset>
			</div>
		";
		
	}	
	
	
	
	if($sbsystems["2"] == 1)
	{
		//anazhthsh ston pinaka liens (syndesmoi sto internet)
		$tmp_result = "<div id=\"marginForm\"><fieldset>
		<legend>$langLinks</legend>
		<label>
		<ul class=\"listBullet\">";	
		$myquery = "SELECT * FROM liens WHERE MATCH (url,titre,description)".$query;
		$result = mysql_query($myquery);
		
		$c = 0;
		if(mysql_num_rows($result) > 0)
		{
			while(@$res = mysql_fetch_array($result))
			{
				$c++;
				$tmp_result .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")</li>";
			}
		}
		if ($c != 0) $tool_content .= $tmp_result."</ul>
		</label>
				<div class=\"clearer\"></div>
			</fieldset>
			</div>
		";
		
	}
	
	if($sbsystems["4"] == 1)
	{	
		
		//anazhthsh ston pinaka video
		$tmp_result = "<div id=\"marginForm\"><fieldset>
		<legend>$langVideo</legend><label>
		<ul class=\"listBullet\">";	
		$myquery = "SELECT * FROM video WHERE MATCH (url,titre,description)".$query;
		$result = mysql_query($myquery);
		
		$c = 0;
		if(mysql_num_rows($result) > 0)
		{
			while(@$res = mysql_fetch_array($result))
			{
				$c++;
				$tmp_result .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")</li>";
			}
		}
		if ($c != 0) $tool_content .= $tmp_result."</ul>
		</label>
				<div class=\"clearer\"></div>
			</fieldset>
			</div>
		";
		

		//anazhthsh ston pinaka videolinks
		$tmp_result = "<div id=\"marginForm\">
		<fieldset><legend>$langVideoLinks
		</legend>
		<label>
		<ul class=\"listBullet\">";	
		$myquery = "SELECT * FROM videolinks WHERE MATCH (url,titre,description)".$query;
		$result = mysql_query($myquery);	
		
		$c = 0;
		if(mysql_num_rows($result) > 0)
		{
			while($res = mysql_fetch_array($result))
			{
				$c++;
				$tmp_result .= "<li>".$res['url'].": ".$res['titre']." (".$res['description'].")</li>";
			}
		}
		if ($c != 0) $tool_content .= $tmp_result."</ul>
		</label>
				<div class=\"clearer\"></div>
			</fieldset>
			</div>
		";
		
	}//telos if($sbsystems["3"] == 1) <- theorw pws videos & videolinks perilamvanetai sto idio checkbox

	//ean den vrethikan apotelesmata, emfanish analogou mhnymatos
	if(stristr($tool_content, "<fieldset>") === FALSE) $tool_content .= "<p>$langNoResult</p>";
	
	//ektypwsh syndesmou gia nea anazhthsh
	$tool_content .= "<p align=\"center\"><a href=\"search_incourse.php\">$langNewSearch</a></p>";	

}//telos anazhthshs (if empty($search_terms) = false)

draw($tool_content, 0);

//katharisma ths $search_terms gia apofygh lathwn
$search_terms = "";
?>
