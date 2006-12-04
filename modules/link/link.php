<?php
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**
 * Links Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component organises the links of a lesson.
 * This module can:
 * - Organize links into categories
 * - move links up/down within a category
 * - move categories up/down
 * - expand/collapse all categories
 * 
 * Based on code by Patrick Cool
 * 
 */

$require_current_course = TRUE;
$langFiles = 'link';
$require_help = TRUE;
$helpTopic = 'Link';



include '../../include/baseTheme.php';
$dbname = $_SESSION['dbname'];
$tbl_link = "liens";
$tbl_categories = "link_categories";

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action_stats = new action();
$action_stats->record('MODULE_ID_LINKS');
/**************************************/


$nameTools = $langLinks;

$tool_content = "";



include("linkfunctions.php");

// treating the post date by calling the relevant function depending of the action querystring.
if (isset($action)) {
	switch($action)
	{
		case "addlink":	if(isset($submitLink)) {
			if(!addlinkcategory("link"))	// here we add a link
			unset($submitLink);
		}

		break;
		case "addcategory": if(isset($submitCategory)) {
			if(!addlinkcategory("category"))	// here we add a category
			unset($submitCategory);
		}
		break;
		case "deletelink": deletelinkcategory("link");
		break; // here we delete a link
		case "deletecategory":	deletelinkcategory("category");
		break; // here we delete a category
		case "editlink": editlinkcategory("link");
		break; // here we edit a link
		case "editcategory": editlinkcategory("category");
		break; // here we edit a category
	}
}

if($is_adminOfCourse) {

	//displaying the error / status messages if there is one
	if (!empty($catlinkstatus))	{

		$tool_content .=  "<table width=\"99%\"><tbody><tr><td class=\"success\">".$catlinkstatus."</td></tr></tbody></table>";
		$tool_content .= "<br>";
		unset($catlinkstatus);
	}

	$tool_content .="
	<div id=\"operations_container\">
	<ul id=\"opslist\">";


	if (isset($category))
	$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addlink&category=".$category."&urlview=@$urlview\">".$langLinkAdd."</a></li>";
	else
	$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addlink\">".$langLinkAdd."</a></li>";
	//	$tool_content .= "<li> | </li>";
	if (isset($urlview))
	$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addcategory&urlview=".$urlview."\">".$langCategoryAdd."</a></li>";
	else
	$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addcategory\">".$langCategoryAdd."</a></li>";

	$tool_content .=  "</ul></div>
	";



	// Displaying the correct title and the form for adding a category or link.
	//This is only shown when nothing has been submitted yet, hence !isset($submitLink)
	if (isset($action) and ($action=="addlink" or $action=="editlink") and !isset($submitLink))
	{
		$tool_content .=  "<h4>";
		if ($action=="addlink")
		{$tool_content .=  $langLinkAdd;}
		else
		{$tool_content .=  $langLinkMod;}
		$tool_content .=  "</h4>\n\n";
		if (isset($category) and $category=="")
		{$category=0;}
		$tool_content .=  "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=".$action."&urlview=".@$urlview."\">";
		if ($action=="editlink")
		{$tool_content .=  "<input type=\"hidden\" name=\"id\" value=\"".$id."\">";}
		$tool_content .=  	"<table><thead><tr>
					<th>URL :</th><td><input type=\"text\" name=\"urllink\" size=\"50\" value=\"
					".@htmlspecialchars($urllink)."\"></td>
					</tr>";
		$tool_content .=  	"<tr><th>".$langLinkName." :</th>
					<td><input type=\"text\" name=\"title\" size=\"50\" value=\"
					".@htmlspecialchars($title)."\"></td></tr>";
		$tool_content .=  	"<tr><th>".$langDescription." :</th>
					<td><textarea wrap=\"physical\" rows=\"3\" cols=\"50\" name=\"description\">".trim(@htmlspecialchars($description))."</textarea></td></tr>";

		$tool_content .= 	"<tr><th>".$langCategory." :</th><td>
					<select name=\"selectcategory\">";
		$tool_content .=  	"<option value=\"0\">--</option>";
		$sqlcategories="SELECT * FROM `".$tbl_categories."` ORDER BY ordre DESC";
		$resultcategories = db_query($sqlcategories, $dbname);
		while ($myrow = mysql_fetch_array($resultcategories))
		{
			$tool_content .=  "<option value=\"".$myrow["id"]."\"";
			if (isset($category) and $myrow["id"]==$category)
			$tool_content .=  " selected";
			$tool_content .= 	">".$myrow["categoryname"]."</option>";
		}
		$tool_content .= 	"</select></td></tr>";
		$tool_content .=  	"</thead></table><br/>";
		$tool_content .=  	"<input type=\"Submit\" name=\"submitLink\" value=\"".$langAdd."\">";

		$tool_content .=  "</form><br/>";
	}
	elseif(isset($action) and ($action=="addcategory" or $action=="editcategory") and !isset($submitCategory))
	{
		$tool_content .=  "<h4>";
		if ($action=="addcategory")
		{$tool_content .=  $langCategoryAdd;}
		else
		{$tool_content .=  $langCategoryMod;}
		$tool_content .=  "</h4>\n\n";
		$tool_content .=  "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=".$action."&urlview=".@$urlview."\">";
		if ($action=="editcategory")
		{$tool_content .=  "<input type=\"hidden\" name=\"id\" value=\"".$id."\">";}
		$tool_content .=  	"<table><thead><th>".$langCategoryName." :</th>
				<td><input type=\"text\" name=\"categoryname\" size=\"50\" value=\"
				".@htmlspecialchars($categoryname)."\"></td></tr>";
		$tool_content .=  	"<tr><th>".$langDescription." :</th>
				<td><textarea wrap=\"physical\" rows=\"3\" cols=\"50\" name=\"description\">
				".@htmlspecialchars($description)."</textarea></td></tr>";
		$tool_content .=  	"</thead></table>";
		$tool_content .=  	"<br><input type=\"Submit\" name=\"submitCategory\" value=\"".$langAdd."\">";

		$tool_content .=  "</form><br/>";
	}

}

//making the show none / show all links. Show none means urlview=0000 (number of zeros depending on the
//number of categories). Show all means urlview=1111 (number of 1 depending on teh number of categories).
$sqlcategories="SELECT * FROM `".$tbl_categories."` ORDER BY `ordre` DESC";
$resultcategories=db_query($sqlcategories, $dbname);
$aantalcategories = @mysql_num_rows($resultcategories);
if ($aantalcategories > 0) {
	$tool_content .= "<p>";

	$tool_content .=  "<a href=\"".$_SERVER['PHP_SELF']."?urlview=";
	for($j = 1; $j <= $aantalcategories; $j++)
	{
		$tool_content .=  "0";
	}
	$tool_content .=  "\">$shownone</a>";
	$tool_content .=  " | <a href=\"".$_SERVER['PHP_SELF']."?urlview=";
	for($j = 1; $j <= $aantalcategories; $j++)
	{
		$tool_content .=  "1";
	}
	$tool_content .=  "\">$showall</a>";
	$tool_content .= "</p>";
}

if (isset($down))
movecatlink($down);
if (isset($up))
movecatlink($up);

$sqlcategories="SELECT * FROM `".$tbl_categories."` order by ordre DESC";
$resultcategories=db_query($sqlcategories, $dbname);

if (mysql_num_rows($resultcategories) > 0) {

	//Starting the table which contains the categories
	$tool_content .=  "<table width=\"99%\">";
	// displaying the links which have no category (thus category = 0 or NULL), if none present this will not be displayed
	$sqlLinks = "SELECT * FROM `".$tbl_link."` WHERE category=0 or category IS NULL";
	$result = db_query($sqlLinks, $dbname);
	$numberofzerocategory=mysql_num_rows($result);
	if ($numberofzerocategory!==0)
	{
		$tool_content .=  "<thead><tr><td class=\"category\" colspan=\"2\">$langNoCategory</td></tr></thead>";

		showlinksofcategory(0);
	}
	$i=0;
	$catcounter=1;
	$view="0";
	while ($myrow=@mysql_fetch_array($resultcategories))
	{
		if (!isset($urlview))
		{
			// No $view set in the url, thus for each category link it should be all zeros except it's own
			makedefaultviewcode($i);
		}
		else
		{
			$view=$urlview;
			$view[$i]="1";
		}
		// if the $urlview has a 1 for this categorie, this means it is expanded and should be desplayed as a
		// - instead of a +, the category is no longer clickable and all the links of this category are displayed
		$myrow["description"]=parse_tex($myrow["description"]);
		if ((isset($urlview[$i]) and $urlview[$i]=="1")) {
			$newurlview=$urlview;
			$newurlview[$i]="0";
			$tool_content .=  "<tr><td class=\"category\" colspan=\"2\" ><b>- <a href=\"".$_SERVER['PHP_SELF']."?urlview=".$newurlview."\">".$myrow["categoryname"]."</a></b><br>&nbsp;&nbsp;&nbsp;";
			$tool_content .=  "<font size=\"2\">".$myrow["description"]."</font>";
			if ($is_adminOfCourse)
			showcategoryadmintools($myrow["id"]);
			$tool_content .=  "</td></tr>";

			showlinksofcategory($myrow["id"]);
			$tool_content .=  "</td></tr>";
		} else {
			$tool_content .=  "<tr><td class=\"category\" colspan=\"2\" ><b>+ <a href=\"".$_SERVER['PHP_SELF']."?urlview=";
			$tool_content .=  is_array($view)?implode('',$view):$view;
			$tool_content .=  "\">".$myrow["categoryname"]."</a></b><br>&nbsp;&nbsp;&nbsp;";
			$tool_content .=  "<font size=\"2\">".$myrow["description"]."</font>";
			if ($is_adminOfCourse)
			showcategoryadmintools($myrow["id"]);
			$tool_content .=  "</td></tr>";
		}
		// displaying the link of the category
		$i++;
	}
	$tool_content .=  "</table>";

} else {   // no category
	if (getNumberOfLinks(0)>0){
		$tool_content .=  "<table>";
		$tool_content .=  "<tbody><tr><td class=\"category\" colspan=\"2\" >$langLinks</td></tr>";

		showlinksofcategory(0);
		$tool_content .=  "</td></tr>";
		$tool_content .=  "</tbody></table>";
	} else {
		if($is_adminOfCourse){
		//if the user is the course administrator instruct him/her
		//what he can do to add links	
		$tool_content .= "<p>$langProfNoLinksExist</p>";
		} else {
		//if the user has no course administrator access
		//inform him/her that no links exist
		$tool_content .= "<p>$langNoLinksExist</p>";
		}
	}
}

draw($tool_content, 2, 'link');
//call draw as shown below to hide the left nav
//draw($tool_content, 2, 'link', '', '', true);
?>
