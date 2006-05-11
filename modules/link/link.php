<?php
// Developped by Patrick Cool, Ghent University
// December 2003 / January 2004
// http://icto.UGent.be

// This is a complete remake of the existing link tools.
// The new link tool has some new features:
// - Organize links into categories
// - favorites/bookmarks interface
// - move links up/down within a category
// - move categories up/down
// - expand/collapse all categories
// - add link to 'root' category => category-less link is always visible
//echo $dbname;
//echo $_SESSION['dbname'];

//TODO: line 202, remove <thead>

$dbname = 'TMA101';
$require_current_course = TRUE;
$langFiles = 'link';
$require_help = TRUE;
$helpTopic = 'Link';
//include ('../../include/init.php');
include '../../include/baseTheme.php';
$dbname = $_SESSION['dbname'];
$tbl_link = "liens";
$tbl_categories = "link_categories";

$nameTools = $langLinks;
//begin_page();

$tool_content = "";

?>
<script language="JavaScript" type="text/JavaScript">
function MM_popupMsg(msg) { 
  confirm(msg);
}
</script>

<?php

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

if($is_adminOfCourse)
{
	

	$tool_content .= "<div id=\"tool_operations\">";
	if (isset($category))
		$tool_content .=  "<span class=\"operation\"><a href=\"".$_SERVER['PHP_SELF']."?action=addlink&category=".$category."&urlview=@$urlview\">".$langLinkAdd."</a></span>";
	else
		$tool_content .=  "<span class=\"operation\"><a href=\"".$_SERVER['PHP_SELF']."?action=addlink\">".$langLinkAdd."</a> | </span>";
	if (isset($urlview))
		$tool_content .=  "<span class=\"operation\"><a href=\"".$_SERVER['PHP_SELF']."?action=addcategory&urlview=".$urlview."\">".$langCategoryAdd."</a></span>";
	else
		$tool_content .=  "<span class=\"operation\"><a href=\"".$_SERVER['PHP_SELF']."?action=addcategory\">".$langCategoryAdd."</a> | </span>";
	$tool_content .=  "</div>";

	//displaying the error / status messages if there is one
//	if (!empty($catlinkstatus) or !empty($msgErr))
	if (!empty($catlinkstatus))
		{
	//	$tool_content .=  "<table cellspacing=\"0\" border=\"0\">\n\t<tr><td bgcolor=\"#FsFCC00\">".$catlinkstatus.$msgErr."</td></tr>\n</table>";
		$tool_content .=  "<table cellspacing=\"0\" border=\"0\">\n\t<tr><td bgcolor=\"#FsFCC00\">".$catlinkstatus."</td></tr>\n</table>";
		unset($catlinkstatus);
	//	unset($msgErr);
		}

	// Displaying the correct title and the form for adding a category or link. This is only shown when nothing
	// has been submitted yet, hence !isset($submitLink)
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
			$tool_content .=  	"<table><tr>
					<td align=\"right\">URL :</td><td><input type=\"text\" name=\"urllink\" size=\"50\" value=\"
					".@htmlspecialchars($urllink)."\"></td>
					</tr>";
			$tool_content .=  	"<tr><td align=\"right\">".$langLinkName." :</td>
					<td><input type=\"text\" name=\"title\" size=\"50\" value=\"
					".@htmlspecialchars($title)."\"></td></tr>";
			$tool_content .=  	"<tr><td align=\"right\" valign=\"top\">".$langDescription." :</td>
					<td><textarea wrap=\"physical\" rows=\"3\" cols=\"50\" name=\"description\">
					".trim(@htmlspecialchars($description))."</textarea></td></tr>";
			echo "string is" . $description;
			$tool_content .= 	"<tr><td align=\"right\">".$langCategory." :</td><td>
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
			$tool_content .=  	"<tr><td></td><td><input type=\"Submit\" name=\"submitLink\" value=\"".$langAdd."\"></td></tr>";
			$tool_content .=  	"</table>";
			$tool_content .=  "</form>";
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
		$tool_content .=  	"<table><td align=\"right\">".$langCategoryName." :</td>
				<td><input type=\"text\" name=\"categoryname\" size=\"50\" value=\"
				".@htmlspecialchars($categoryname)."\"></td></tr>";
		$tool_content .=  	"<tr><td align=\"right\" valign=\"top\">".$langDescription." :</td>
				<td><textarea wrap=\"physical\" rows=\"3\" cols=\"50\" name=\"description\">
				".@htmlspecialchars($description)."</textarea></td></tr>";
		$tool_content .=  	"<tr><td></td><td><input type=\"Submit\" name=\"submitCategory\" value=\"".$langAdd."\"></td></tr>";
		$tool_content .=  	"</table>";
		$tool_content .=  "</form>";
		}
//	$tool_content .=  "<hr>";
} 

//making the show none / show all links. Show none means urlview=0000 (number of zeros depending on the
//number of categories). Show all means urlview=1111 (number of 1 depending on teh number of categories).
$sqlcategories="SELECT * FROM `".$tbl_categories."` ORDER BY `ordre` DESC";
$resultcategories=db_query($sqlcategories, $dbname);
$aantalcategories = @mysql_num_rows($resultcategories);
if ($aantalcategories > 0) {
	$tool_content .= "<div id=\"tool_operations\">";
	
$tool_content .=  "<span class=\"operation\"><a href=\"".$_SERVER['PHP_SELF']."?urlview=";
	for($j = 1; $j <= $aantalcategories; $j++)
	{
	$tool_content .=  "0";
	}
$tool_content .=  "\">$shownone</a></span>";
$tool_content .=  "<span class=\"operation\">| <a href=\"".$_SERVER['PHP_SELF']."?urlview=";
	for($j = 1; $j <= $aantalcategories; $j++)
	{
	$tool_content .=  "1";
	}
$tool_content .=  "\">$showall</a></span><p>";
$tool_content .= "</div>";
}

if (isset($down))
	movecatlink($down);
if (isset($up))
	movecatlink($up);

$sqlcategories="SELECT * FROM `".$tbl_categories."` order by ordre DESC";
$resultcategories=db_query($sqlcategories, $dbname);

if (mysql_num_rows($resultcategories) > 0) {

//Starting the table which contains the categories
$tool_content .=  "<table width=99%>";
// displaying the links which have no category (thus category = 0 or NULL), if none present this will not be displayed
	$sqlLinks = "SELECT * FROM `".$tbl_link."` WHERE category=0 or category IS NULL";
	$result = db_query($sqlLinks, $dbname);
	$numberofzerocategory=mysql_num_rows($result);
	if ($numberofzerocategory!==0)
		{
		$tool_content .=  "<thead><tr><td class=\"category\" colspan=\"2\">$langNoCategory</td></tr></thead>";
//		$tool_content .=  "<tr><td>";
		showlinksofcategory(0);
//		$tool_content .=  "</td></tr>";
//		echo $tool_content;
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
//		$tool_content .=  "<tr><td>";
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
//echo $tool_content;
} else {   // no category 
	$tool_content .=  "<table>";
	$tool_content .=  "<tr><td colspan=\"2\" bgcolor=\"#000000\"><i>$langLinks</i></td></tr>";
                $tool_content .=  "<tr><td>";
                showlinksofcategory(0);
                $tool_content .=  "</td></tr>";
	$tool_content .=  "</table>";
	}
	
	draw($tool_content, 2, 'link');
?>
