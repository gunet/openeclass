<?php
/* *=============================================================================
       	GUnet eClass 2.0 
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

/* ===========================================================================
	speedSubscribe.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
        @Description: Allow admin to enroll as professor to any course

 	This script allows the administrator to enroll as professor to any course

 	The user can : - Enroll to any course
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Get all courses
  2) Enroll to selected courses
  3) Display all on an HTML page
  
==============================================================================*/


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langSpeedSubscribe;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Register administrator to selected courses
if (isset($submit) && $submit == "$langRegistration") {
	// Constract a display table
	$tool_content .= "<table width=\"99%\"><caption>".$lang_subscribe_processing."</caption></tbody>";

	$lesStatutDeCours["1"] = "Καθηγητής";
	$lesStatutDeCours["5"] = "Φοιτητής";
	// Register admin to courses selected
	while (list($key,$contenu)= @each($course)) {
		// Insert query
		$sql = "INSERT INTO `cours_user` (code_cours, user_id, statut, reg_date) 
			VALUES ('$contenu', '$uid', '1', CURDATE())";
		$res =mysql_query($sql);
		// All OK
		if ($res)
			$tool_content .= "<tr><td>".$langSuccess."<td><tr>";	
		// Something went wrong
		elseif (mysql_errno() == 1062)
		{
			$sql2 = "SELECT `statut` sCours FROM `cours_user` 
				WHERE `code_cours` = '$contenu' AND `user_id`= '$uid'";
			$res2 =mysql_query($sql2);
			$lelienUserCours = mysql_fetch_array($res2);
			$tool_content .= "<tr><td><b>".$langAlreadySubscribe."</b> ".$langAs." ".$lesStatutDeCours[$lelienUserCours["sCours"]]."</td></tr>";
		}
	} // End while
	// Close table correctly
	$tool_content .= "<tr><td><b>$langAlreadyBrowsed</b></td></tr></tbody></table><br>";
}
// Display list of courses
$tool_content .= "<form name=\"speedSub\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
// Select all courses
$sql = "SELECT cours_faculte.faculte f, cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t
		FROM cours_faculte, cours WHERE cours.code=cours_faculte.code 
		ORDER BY cours_faculte.faculte, cours.code";

$result=mysql_query($sql);	
$firstfac = true;
while ($mycours = mysql_fetch_array($result)) { 
	// Constract different table for every faculte
	if($mycours['f'] != @$facOnce)
	{
		if ($firstfac) {
			$tool_content .= "<table width=\"99%\"><caption>".$mycours['f']."</caption><tbody>";
			$firstfac = false;
		} else {
			$tool_content .= "</tbody></table><br><table width=\"99%\"><caption>".$mycours['f']."</caption><tbody>";
		}
	}
	$facOnce=$mycours['f'];
	if($mycours['k'] != @$codeOnce)
	{
		$tool_content .= "<tr><td><input type=checkbox name=course[] value=$mycours[k]>$mycours[c] $mycours[i] $mycours[t]</td></tr>";
	}
	$codeOnce=$mycours['k'];
}
if (!$firstfac) {
	$tool_content .= "</tbody></table>";
}

$tool_content .= "<br><p><input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\"></p></form>";
// Display link to go back to index.php
$tool_content .= "<br><center><p><a href=\"index.php\">$langBack</a></p></center>";

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3);
?>
