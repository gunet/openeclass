<?php
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
 
/*******************************************************
*               EXTERNAL MODULE / LINK                 *
********************************************************

Add link to external site directly form Home page main menu
************************************************************/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Module';
include '../../include/baseTheme.php';
$nameTools = $langLinkSite;

$tool_content = "";
if ($is_adminOfCourse) 
{ 

$tool_content .=  "<p>$langSubTitle</p>";

	if(isset($submit)) 
	{
		if (($link == "http://") or ($link == "ftp://") or empty($link))  {
			$tool_content .= "<table><tbody>
			<tr><td class=\"caution\"><p>$langInvalidLink</p>
			<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
			</td></tr></tbody></table>";
			
			draw($tool_content, 2);
			exit();
		}
		
		$sql = 'SELECT MAX(`id`) FROM `accueil` ';
		$res = db_query($sql,$dbname);
		while ($maxID = mysql_fetch_row($res)) {
			$mID = $maxID[0];
		}
		
		if($mID<101) $mID = 101;
		else $mID = $mID+1;
		
		
		mysql_query("INSERT INTO accueil VALUES ($mID,
					'$name_link',
					'$link \"target=_blank',
					'external_link',
					'1',
					'0',
					'$link',
					''
					)");
		
		$tool_content .= "<table><tbody><tr><td class=\"success\"><p>$langLinkAdded</p>
		<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
		</td></tr></tbody></table>";
		
	} 
	else 
	{  // display form
		$tool_content .=  "<form method=\"post\" action=\"$_SERVER[PHP_SELF]?submit=yes\">
		<table><thead><tr><th>$langLink&nbsp;:</th>
		<td><input type=\"text\" name=\"link\" size=\"50\" value=\"http://\"></td>
		</tr><tr><th>$langName&nbsp;:</th><td>
		<input type=\"Text\" name=\"name_link\" size=\"50\"></td>
		</tr></thead></table><br><input type=\"Submit\" name=\"submit\" value=\"$langAdd\"></form>";
	}
} else // student view 
	{
		$tool_content .=  "<tr><td colspan=\"2\">$langNotAllowed<br><br>
		<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
		</td></tr></table>";
	}

$tool_content .=  "<tr><td colspan=\"2\"></td></tr></table>";
draw($tool_content, 2);
?>

