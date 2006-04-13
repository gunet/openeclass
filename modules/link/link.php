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


$require_current_course = TRUE;
$langFiles = 'link';
$require_help = TRUE;
$helpTopic = 'Link';
include ('../../include/init.php');

$tbl_link = "liens";
$tbl_categories = "link_categories";

$nameTools = $langLinks;
begin_page();


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
	echo "<ul>\n";
	if (isset($category))
		echo "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addlink&category=".$category."&urlview=@$urlview\">".$langLinkAdd."</a>\n";
	else
		echo "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addlink\">".$langLinkAdd."</a>\n";
	if (isset($urlview))
		echo "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addcategory&urlview=".$urlview."\">".$langCategoryAdd."</a>\n";
	else
		echo "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addcategory\">".$langCategoryAdd."</a>\n";
	echo "</ul>\n\n";

	//displaying the error / status messages if there is one
//	if (!empty($catlinkstatus) or !empty($msgErr))
	if (!empty($catlinkstatus))
		{
	//	echo "<table cellspacing=\"0\" border=\"0\">\n\t<tr><td bgcolor=\"#FsFCC00\">".$catlinkstatus.$msgErr."</td></tr>\n</table>";
		echo "<table cellspacing=\"0\" border=\"0\">\n\t<tr><td bgcolor=\"#FsFCC00\">".$catlinkstatus."</td></tr>\n</table>";
		unset($catlinkstatus);
	//	unset($msgErr);
		}

	// Displaying the correct title and the form for adding a category or link. This is only shown when nothing
	// has been submitted yet, hence !isset($submitLink)
	if (isset($action) and ($action=="addlink" or $action=="editlink") and !isset($submitLink))
		{
			echo "<h4>";
			if ($action=="addlink")
				{echo $langLinkAdd;}
			else
				{echo $langLinkMod;}
			echo "</h4>\n\n";
			if (isset($category) and $category=="")
				{$category=0;}
			echo "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=".$action."&urlview=".@$urlview."\">";
			if ($action=="editlink")
				{echo "<input type=\"hidden\" name=\"id\" value=\"".$id."\">";}
			echo 	"<table><tr>",
					"<td align=\"right\">URL :</td><td><input type=\"text\" name=\"urllink\" size=\"50\" value=\"",
					@htmlspecialchars($urllink)."\"></td>",
					"</tr>";
			echo 	"<tr><td align=\"right\">".$langLinkName." :</td>",
					"<td><input type=\"text\" name=\"title\" size=\"50\" value=\"",
					@htmlspecialchars($title)."\"></td></tr>";
			echo 	"<tr><td align=\"right\" valign=\"top\">".$langDescription." :</td>",
					"<td><textarea wrap=\"physical\" rows=\"3\" cols=\"50\" name=\"description\">",
					@htmlspecialchars($description)."</textarea></td></tr>";
			echo	"<tr><td align=\"right\">".$langCategory." :</td><td>",
					"<select name=\"selectcategory\">";
			echo 	"<option value=\"0\">--</option>";
			$sqlcategories="SELECT * FROM `".$tbl_categories."` ORDER BY ordre DESC";
			$resultcategories = db_query($sqlcategories);
			while ($myrow = mysql_fetch_array($resultcategories))
				{
				echo "<option value=\"".$myrow["id"]."\"";
				if (isset($category) and $myrow["id"]==$category)
					echo " selected";
				echo	">".$myrow["categoryname"]."</option>";
				}
			echo	"</select></td></tr>";
			echo 	"<tr><td></td><td><input type=\"Submit\" name=\"submitLink\" value=\"".$langAdd."\"></td></tr>";
			echo 	"</table>";
			echo "</form>";
		}
	elseif(isset($action) and ($action=="addcategory" or $action=="editcategory") and !isset($submitCategory))
		{
		echo "<h4>";
		if ($action=="addcategory")
			{echo $langCategoryAdd;}
		else
			{echo $langCategoryMod;}
		echo "</h4>\n\n";
		echo "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=".$action."&urlview=".@$urlview."\">";
		if ($action=="editcategory")
			{echo "<input type=\"hidden\" name=\"id\" value=\"".$id."\">";}
		echo 	"<table><td align=\"right\">".$langCategoryName." :</td>",
				"<td><input type=\"text\" name=\"categoryname\" size=\"50\" value=\"",
				@htmlspecialchars($categoryname)."\"></td></tr>";
		echo 	"<tr><td align=\"right\" valign=\"top\">".$langDescription." :</td>",
				"<td><textarea wrap=\"physical\" rows=\"3\" cols=\"50\" name=\"description\">",
				@htmlspecialchars($description)."</textarea></td></tr>";
		echo 	"<tr><td></td><td><input type=\"Submit\" name=\"submitCategory\" value=\"".$langAdd."\"></td></tr>";
		echo 	"</table>";
		echo "</form>";
		}
	echo "<hr>";
}


//making the show none / show all links. Show none means urlview=0000 (number of zeros depending on the
//number of categories). Show all means urlview=1111 (number of 1 depending on teh number of categories).
$sqlcategories="SELECT * FROM `".$tbl_categories."` ORDER BY `ordre` DESC";
$resultcategories=db_query($sqlcategories);
$aantalcategories = @mysql_num_rows($resultcategories);
if ($aantalcategories > 0) {
echo "<a href=\"".$_SERVER['PHP_SELF']."?urlview=";
	for($j = 1; $j <= $aantalcategories; $j++)
	{
	echo "0";
	}
echo "\">$shownone</a>";
echo " | <a href=\"".$_SERVER['PHP_SELF']."?urlview=";
	for($j = 1; $j <= $aantalcategories; $j++)
	{
	echo "1";
	}
echo "\">$showall</a><p>";
}

if (isset($down))
	movecatlink($down);
if (isset($up))
	movecatlink($up);

$sqlcategories="SELECT * FROM `".$tbl_categories."` order by ordre DESC";
$resultcategories=mysql_query($sqlcategories);

if (mysql_num_rows($resultcategories) > 0) {

//Starting the table which contains the categories
echo "<table width=100%>";
// displaying the links which have no category (thus category = 0 or NULL), if none present this will not be displayed
	$sqlLinks = "SELECT * FROM `".$tbl_link."` WHERE category=0 or category IS NULL";
	$result = mysql_query($sqlLinks);
	$numberofzerocategory=mysql_num_rows($result);
	if ($numberofzerocategory!==0)
		{
		echo "<tr><td bgcolor=\"#E6E6E6\"><i>$langNoCategory</i></td></tr>";
		echo "<tr><td>";
		showlinksofcategory(0);
		echo "</td></tr>";
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
		echo "<tr><td bgcolor=\"#E6E6E6\"><b>- <a href=\"".$_SERVER['PHP_SELF']."?urlview=".$newurlview."\">".$myrow["categoryname"]."</a></b><br>&nbsp;&nbsp;&nbsp;";
		echo "<font size=\"2\">".$myrow["description"]."</font>";
		if ($is_adminOfCourse)
			showcategoryadmintools($myrow["id"]);
		echo "</td></tr>";
		echo "<tr><td>";
		showlinksofcategory($myrow["id"]);
		echo "</td></tr>";
	} else {
		echo "<tr><td bgcolor=\"#E6E6E6\"><b>+ <a href=\"".$_SERVER['PHP_SELF']."?urlview=";
		echo is_array($view)?implode('',$view):$view;
		echo "\">".$myrow["categoryname"]."</a></b><br>&nbsp;&nbsp;&nbsp;";
		echo "<font size=\"2\">".$myrow["description"]."</font>";
		if ($is_adminOfCourse)
			showcategoryadmintools($myrow["id"]);
		echo "</td></tr>";
	}
	// displaying the link of the category
	$i++;
	}
echo "</table>";

} else {   // no category 
	echo "<table>";
	echo "<tr><td bgcolor=\"#E6E6E6\"><i>$langLinks</i></td></tr>";
                echo "<tr><td>";
                showlinksofcategory(0);
                echo "</td></tr>";
	echo "</table>";
	}
?>
