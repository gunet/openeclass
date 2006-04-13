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


// FUNCTION addlinkcategory
// The function used to add a link or a category
// type = add a link or add a category
function addlinkcategory($type)
{
	global $catlinkstatus;
	global $msgErr;

	$ok=true;

	if($type == "link")
	{
		global $tbl_link;
		global $urllink;
		global $title;
		global $description;
		global $selectcategory;
		global $langGiveURL;
		global $langLinkAdded;

		$urllink=trim($urllink);
		$title=trim($title);
		$description=trim($description);

		// if title is empty, an error occurs
		if(empty($urllink))
		{
			$msgErr=$langGiveURL;

			$ok=false;
		}
		// if the title is empty, we use the url as the title
		else
		{
			if(empty($title))
			{
				$title=$urllink;
			}

			// we check weither the $url starts with http://, if not we add this
			if(!ereg("://",$urllink))
			{
				$urllink="http://".$urllink;
			}

			// looking for the largest ordre number for this category
			$result=db_query("SELECT MAX(ordre) FROM  `".$tbl_link."` WHERE category='$selectcategory'");

			list($orderMax)=mysql_fetch_row($result);

			$ordre=$orderMax+1;

			$sql="INSERT INTO `".$tbl_link."` (url, titre, description, category,ordre) VALUES ('$urllink','$title','$description','$selectcategory','$ordre')";
			$catlinkstatus=$langLinkAdded;

			unset($urllink,$title,$description,$selectcategory);
		}
	}
	if($type == "category")
	{
		global $tbl_categories;
		global $categoryname;
		global $description;
		global $langCategoryAdded;
		global $langGiveCategoryName;

		$categoryname=trim($categoryname);

		if(empty($categoryname))
		{
			$msgErr=$langGiveCategoryName;

			$ok=false;
		}
		else
		{
			// looking for the largest ordre number for this category
			$result = db_query("SELECT MAX(ordre) FROM  `".$tbl_categories."`");

			list($orderMax) = mysql_fetch_row($result);

			$ordre=$orderMax+1;

			$sql="INSERT INTO `".$tbl_categories."` (categoryname, description, ordre) VALUES ('$categoryname','$description', '$ordre')";

			$catlinkstatus=$langCategoryAdded;

			unset($categoryname,$description);
		}
	}

	db_query($sql);
	return $ok;
}
// End of the function addlinkcategory


// function delete link or delete category
function deletelinkcategory($type)
{
global $tbl_categories;
global $tbl_link;
global $catlinkstatus;

if ($type=="link")
	{
	global $id;
	global $langLinkDeleted;
	$sql="DELETE FROM `".$tbl_link."` WHERE id='".$id."'";
	$catlinkstatus=$langLinkDeleted;
	unset($id);
	}
if ($type=="category")
	{
	global $id;
	global $langCategoryDeleted;
	// first we delete the category itself and afterwards all the links of this category.
	$sql="DELETE FROM `".$tbl_categories."` WHERE id='".$id."'";
	db_query($sql);
	$sql="DELETE FROM `".$tbl_link."` WHERE category='".$id."'";
	$catlinkstatus=$langCategoryDeleted;
	unset($id);
	}
db_query($sql);
// End of the function deletelinkcategory
}



// function edit link or delete category
function editlinkcategory($type)
{
global $tbl_categories;
global $tbl_link;
global $catlinkstatus;
global $id;
global $submitLink;
global $submitCategory;

if ($type=="link")
	{
	global $urllink;
	global $title;
	global $description;
	global $category;

	// this is used to populate the link-form with the info found in the database
	if (!$submitLink)
		{
		$sql="SELECT * FROM `".$tbl_link."` WHERE id='".$id."'";
		$result=db_query($sql);
		if ($myrow=mysql_fetch_array($result))
			{
			$urllink = $myrow["url"];
			$title = $myrow["titre"];
			$description = $myrow["description"];
			$category = $myrow["category"];
			}
		}
	// this is used to put the modified info of the link-form into the database
	if ($submitLink)
		{
		global $langLinkModded;
		global $selectcategory;

		$sql="UPDATE `".$tbl_link."` set url='$urllink', titre='$title', description='$description', category='$selectcategory' WHERE id='".$id."'";
		mysql_query($sql);
		$catlinkstatus=$langLinkModded;

		}
	}
if ($type=="category")
	{
	global $description;
	global $categoryname;

	// this is used to populate the category-form with the info found in the database
	if (!$submitCategory)
		{
		$sql="SELECT * FROM `".$tbl_categories."` WHERE id='".$id."'";
		$result=mysql_query($sql);
		if ($myrow=mysql_fetch_array($result))
			{
			$categoryname= $myrow["categoryname"];
			$description = $myrow["description"];
			}
		}
	// this is used to put the modified info of the category-form into the database
	if ($submitCategory)
		{
		global $langCategoryModded;
		$sql="UPDATE `".$tbl_categories."` set categoryname='$categoryname', description='$description' WHERE id='".$id."'";
		mysql_query($sql);
		$catlinkstatus=$langCategoryModded;
		}
	}
}
// END of function editlinkcat







// START of function makedefaultviewcode, which creates a correct $view for in the URL.
function makedefaultviewcode($locatie)
{
global $aantalcategories;
global $view;

for($j = 0; $j <= $aantalcategories-1; $j++)
	{
	$view[$j]=0;
	}
$view[intval($locatie)]="1";
}
// END of function makedefaultviewcode









// START of function showlinksofcategory, which displays all the links of a given category.
function showlinksofcategory($catid)
{
global $tbl_link;
global $is_adminOfCourse;
global $urlview;
global $up;
global $down;
global $langLinkDelconfirm;
global $langDelete;
global $langCategoryDelconfirm;
global $langModify, $langLinks;


$sqlLinks = "SELECT * FROM `".$tbl_link."` WHERE category='".$catid."' ORDER BY ordre DESC";
$result = mysql_query($sqlLinks);
$numberoflinks=mysql_num_rows($result);
echo "<table border=\"0\">";
$i=1;
while ($myrow = mysql_fetch_array($result))
	{
	$myrow[3] = parse_tex($myrow[3]);
	echo	"<tr>",
			"<td align=\"right\" valign=\"top\" width=\"40\">",
			"<img src=\"../../images/pixel.gif\" border=\"0\" width=\"10\">",
			"<a href=\"link_goto.php?link_id=",$myrow[0],"&link_url=",urlencode($myrow[1]),"\" target=\"_blank\">",
			"<img src=\"../../images/liens.png\" border=\"0\" alt=\"".$langLinks."\">",
			"</td>",

			"<td width=\"580\" valign=\"top\">",
                        "<a href=\"link_goto.php?link_id=",$myrow[0],"&link_url=",urlencode($myrow[1]),"\" target=\"_blank\">",$myrow[2],"</a>\n",
			"<br>",$myrow[3],"";
	if ($is_adminOfCourse)
		{
		echo	"<br>";
			if (isset($category))
				echo "<a href=\"$_SERVER[PHP_SELF]?action=editlink&category=$category&id=$myrow[0]&urlview=$urlview\">";
			else 
				echo "<a href=\"$_SERVER[PHP_SELF]?action=editlink&id=$myrow[0]&urlview=$urlview\">";
			echo "<img src=\"../../images/edit.gif\" border=\"0\" alt=\"",$langModify,"\">",
				"</a>",
				" <a href=\"",$_SERVER['PHP_SELF'],"?action=deletelink&id=",$myrow[0],"&urlview=",$urlview,"\" onclick=\"javascript:if(!confirm('".$langLinkDelconfirm."')) return false;\">",
				"<img src=\"../../images/delete.gif\" border=\"0\" alt=\"",$langDelete,"\">",
				"</a>";
		// DISPLAY MOVE UP COMMAND only if it is not the top link
		if ($i!=1)
			{
			echo	"<a href=\"$_SERVER[PHP_SELF]?urlview=".$urlview."&up=",$myrow["id"],"\">",
					"<img src=../../images/up.gif border=0 alt=\"Up\">",
					"</a>\n";
			}
		// DISPLAY MOVE DOWN COMMAND only if it is not the bottom link
		if($i < $numberoflinks)
			{
				echo	"<a href=\"$_SERVER[PHP_SELF]?urlview=".$urlview."&down=".$myrow["id"]."\">",
						"<img src=\"../../images/down.gif\" border=\"0\" alt=\"Down\">",
						"</a>\n";
			}
		}
	echo	"</td>",
			"</tr>";
	$i++;
	}
echo "</table>";
}



// START of function showcategoryadmintools.
function showcategoryadmintools($categoryid)
{
global $urlview;
global $aantalcategories;
global $catcounter;
global $langDelete;
global $langCatDel;

global $langModify;

echo "<a href=\"$_SERVER[PHP_SELF]?action=editcategory&id=$categoryid&urlview=$urlview\">",
		"<img src=\"../../images/edit.gif\" border=\"0\" alt=\"",$langModify,"\">",
		"</a> \n";

echo " <a href=\"",$_SERVER['PHP_SELF'],"?action=deletecategory&id=",$categoryid,"&urlview=",$urlview,"\" onclick=\"javascript:if(!confirm('".$langCatDel."')) return false;\">", "<img src=\"../../images/delete.gif\" border=\"0\" alt=\"",$langDelete,"\">",
"</a>";


// DISPLAY MOVE UP COMMAND only if it is not the top link
if ($catcounter!=1)
	{
	echo "<a href=\"$_SERVER[PHP_SELF]?catmove=true&up=",$categoryid,"&urlview=$urlview\">",
			"<img src=../../images/up.gif border=0 alt=\"Up\">",
			"</a>\n";
	}
// DISPLAY MOVE DOWN COMMAND only if it is not the bottom link
if($catcounter < $aantalcategories)
	{
	echo "<a href=\"$_SERVER[PHP_SELF]?catmove=true&down=".$categoryid."&urlview=$urlview\">",
			"<img src=\"../../images/down.gif\" border=\"0\" alt=\"Down\">",
			"</a>\n";
	}
$catcounter++;
}


//START of function movecatlink
function movecatlink($catlinkid)
{
global $catmove;
global $up;
global $down;
global $tbl_link;
global $tbl_categories;

if ($down)
	{
	$thiscatlinkId = $down;
	$sortDirection = "DESC";
	}
if ($up)
	{
	$thiscatlinkId = $up;
	$sortDirection = "ASC";
	}


// We check if it is a category we are moving or a link. If it is a category, a querystring catmove = true is present in the url
if ($catmove=="true")
	{
	$movetable=$tbl_categories;
	$catid=$catlinkid;
	}
else
	{
	$movetable=$tbl_link;
	//getting the category of the link
	$sql="SELECT category from `".$movetable."` WHERE id='$thiscatlinkId'";
	$result=mysql_query($sql);
	$catid=mysql_fetch_array($result);
	}


// this code is copied and modified from announcements.php
if ($sortDirection)
	{
	if (!in_array(trim(strtoupper($sortDirection)), array('ASC', 'DESC'))) die("Bad sort direction used."); //sanity check of sortDirection var
	if ($catmove=="true")
		{
		$sqlcatlinks="SELECT id, ordre FROM `".$movetable."` ORDER BY `ordre` $sortDirection";
		}
	else
		{
		$sqlcatlinks="SELECT id, ordre FROM `".$movetable."` WHERE category='".$catid[0]."' ORDER BY `ordre` $sortDirection";
		}
	$linkresult = mysql_query($sqlcatlinks);
	while ($sortrow=mysql_fetch_array($linkresult))
		{
		// STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER, COMMIT SWAP
		// This part seems unlogic, but it isn't . We first look for the current link with the querystring ID
		// and we know the next iteration of the while loop is the next one. These should be swapped.
		if (isset($thislinkFound) && $thislinkFound == true)
			{
			$nextlinkId=$sortrow["id"];
			$nextlinkOrdre=$sortrow["ordre"];

			mysql_query("UPDATE `".$movetable."`
			             SET ordre = '$nextlinkOrdre'
			             WHERE id =  '$thiscatlinkId'");

			mysql_query("UPDATE `".$movetable."`
			             SET ordre = '$thislinkOrdre'
						 WHERE id =  '$nextlinkId'");

			break;
			}

		if ($sortrow["id"]==$thiscatlinkId )
			{
			$thislinkOrdre=$sortrow["ordre"];
			$thislinkFound = true;
			}
		}
	}
}
?>
