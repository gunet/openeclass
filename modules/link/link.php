<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

/*
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
$require_help = TRUE;
$helpTopic = 'Link';
$guest_allowed = true;

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
$tool_content = $head_content = "";

$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyLinkURL");
		return false;
	} else {
		return true;
	}
}

</script>
hContent;


if (isset($_GET['urlview'])) {
	$urlview = $_GET['urlview'];
}
if (isset($_GET['down'])) {
	$down = $_GET['down'];
}
if (isset($_GET['up'])) {
	$up = $_GET['up'];
}
if (isset($_GET['catmove'])) {
	$catmove = $_GET['catmove'];
}
if (isset($_GET['id'])) {
	$id = $_GET['id'];
}
if (isset($_POST['urllink'])) {
	$urllink = $_POST['urllink'];
}
if (isset($_POST['title'])) {
	$title = $_POST['title'];
}
if (isset($_POST['description'])) {
	$description = $_POST['description'];
}
if (isset($_POST['selectcategory'])) {
	$selectcategory = $_POST['selectcategory'];
}
if (isset($_POST['categoryname'])) {
	$categoryname = $_POST['categoryname	'];
}
if (isset($_POST['id'])) {
	$id = $_POST['id'];
}

include("linkfunctions.php");

// treating the post date by calling the relevant function depending of the action querystring.
if (isset($_GET['action']) && ($is_adminOfCourse)) { //allow link management actions only for course admin
	switch($_GET['action'])
	{
		case "addlink":	if(isset($_POST['submitLink'])) {
			if(!addlinkcategory("link"))	// here we add a link
			unset($_POST['submitLink']);
		}
		break;
		case "addcategory": if(isset($_POST['submitCategory'])) {
			if(!addlinkcategory("category"))	// here we add a category
			unset($_POST['submitCategory']);
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
	if (!empty($catlinkstatus))	{
	   $tool_content .=  "<p class=\"success_small\">".$catlinkstatus."</p><br />";
	   unset($catlinkstatus);
	}

	$tool_content .="<div id=\"operations_container\">
        <ul id=\"opslist\">";
	if (isset($category))
		$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addlink&amp;category=".$category."&amp;urlview=@$urlview\">".$langLinkAdd."</a></li>";
	else
		$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addlink\">".$langLinkAdd."</a></li>";
	if (isset($urlview))
		$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addcategory&amp;urlview=".$urlview."\">".$langCategoryAdd."</a></li>";
	else
		$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?action=addcategory\">".$langCategoryAdd."</a></li>";

	$tool_content .=  "</ul></div>";
	// Displaying the correct title and the form for adding a category or link.
	// This is only shown when nothing has been submitted yet, hence !isset($submitLink)
	if (isset($_GET['action']) and ($_GET['action'] == "addlink" or $_GET['action'] == "editlink")
		and !isset($_POST['submitLink'])) {
		
		$action = $_GET['action'];
		if (isset($_GET['category']) and $category == "") {
			$category=0;
		}
		$tool_content .= "<form method='post' action='".$_SERVER['PHP_SELF']."?action=".$action."&amp;urlview=".@$urlview."' onsubmit=\"return checkrequired(this, 'urllink');\">";
		if ($action == "editlink") {
			$tool_content .= "<input type=\"hidden\" name=\"id\" value=\"".$id."\" />";
		}

		$tool_content .= "<table width='99%' class='FormData'>
		<tbody>
		<tr>
		<th class='left' width='220'>&nbsp;</th>
		<td><b>";
		if ($action == "addlink") {
			$tool_content .= $langLinkAdd;
		}
		else
		{
			$tool_content .= $langLinkModify;
			$langAdd = $langLinkModify;
		}

		$tool_content .=  "</b></td>
		</tr>
		<tr>
		<th class='left'>URL :</th>
		<td><input type=\"text\" name=\"urllink\" size=\"53\" value=\"".@htmlspecialchars($urllink)."\" class='FormData_InputText' /></td>
		</tr>
		<tr>
		<th class='left'>".$langLinkName." :</th>
		<td><input type=\"text\" name=\"title\" size=\"53\" value=\"".@htmlspecialchars($title)."\" class='FormData_InputText' /></td>
		</tr>
		<tr>
		<th class='left'>".$langDescription." :</th>
		<td><textarea rows=\"3\" cols=\"50\" name=\"description\" class='FormData_InputText'>".trim(@htmlspecialchars($description))."</textarea></td>
		</tr>
		<tr>
		<th class='left'>".$langCategory." :</th>
		<td><select name='selectcategory' class='auth_input'>
		    <option value=\"0\">--</option>
		";
		$sqlcategories="SELECT * FROM `".$tbl_categories."` ORDER BY ordre DESC";
		$resultcategories = db_query($sqlcategories, $dbname);
		while ($myrow = mysql_fetch_array($resultcategories))
		{
			$tool_content .=  "<option value=\"".$myrow["id"]."\"";
			if (isset($_GET['category']) and $myrow["id"]==$category)
			$tool_content .=  " selected";
			$tool_content .= 	">".$myrow["categoryname"]."</option>\n";
		}
		$tool_content .=  "</select></td></tr>";
		$tool_content .=  "<tr><th class='left'>&nbsp;</th>
		<td><input type='submit' name='submitLink' value='$langAdd' /></td>
		</tr></tbody></table><br/></form><br/>";
	}
	elseif(isset($_GET['action']) and ($_GET['action'] == "addcategory" or $_GET['action'] == "editcategory")
		and !isset($_POST['submitCategory'])) {
		
		$action = $_GET['action'];
		
		$tool_content .=  "
		<form method='post' action='".$_SERVER['PHP_SELF']."?action=".$action."&amp;urlview=".@$urlview."'>";
		      $tool_content .=  "<table width='99%' class='FormData'>
			<tbody>
			<tr><th class='left' width='220'>&nbsp;</th><td><b>";

		if ($action == "addcategory") {
		   $tool_content .=  $langCategoryAdd;
		} else {
		   $tool_content .=  $langCategoryMod;
		}
		if ($action == "editcategory") {
			$tool_content .=  "<input type='hidden' name='id' value='$id' />";
		}

		$tool_content .=  "</b>
		</td></tr>
		<tr>
		<th class='left'>".$langCategoryName." :</th>
		<td><input type='text' name='categoryname' size='53' value='".@htmlspecialchars($categoryname)."' class='FormData_InputText' /></td>
		</tr>
		<tr>
		<th class='left'>".$langDescription." :</th>
		<td><textarea rows='5' cols='50' name='description' class='FormData_InputText' >" .
		    @htmlspecialchars($description)."</textarea></td>
		</tr>
		<tr>
		<th>&nbsp;</th>
		<td><input type='submit' name='submitCategory' value='$langAdd' /></td>
		</tr>
		</tbody>
		</table>
		<br />
		</form>
		<br />";
	}
}

if (isset($down))
	movecatlink($down);
if (isset($up))
	movecatlink($up);

$sqlcategories="SELECT * FROM `".$tbl_categories."` order by ordre DESC";
$resultcategories=db_query($sqlcategories, $dbname);

if (mysql_num_rows($resultcategories) > 0) {
	//Starting the table which contains the categories
	// displaying the links which have no category (thus category = 0 or NULL), if none present this will not be displayed
	$sqlLinks = "SELECT * FROM `".$tbl_link."` WHERE category=0 or category IS NULL";
	$result = db_query($sqlLinks, $dbname);
	$numberofzerocategory=mysql_num_rows($result);
	//making the show none / show all links. Show none means urlview=0000 (number of zeros depending on the
	//number of categories). Show all means urlview=1111 (number of 1 depending on teh number of categories).
	$sqlcategories="SELECT * FROM `".$tbl_categories."` ORDER BY `ordre` DESC";
	$resultcategories=db_query($sqlcategories, $dbname);
	$aantalcategories = @mysql_num_rows($resultcategories);

	if ($aantalcategories > 0) {
		$more_less = "
		<table width=\"99%\" class=\"FormData\" style=\"border: 1px solid #edecdf;\">
		<thead>
		<tr>
		<th class=\"left\" style=\"border: 1px solid #edecdf;\">$langCategorisedLinks</th>
		<th class=\"left\" style=\"border: 1px solid #edecdf;\" width=\"1\"><img src=\"../../template/classic/img/closeddir.gif\" title=\"$showall\" /></th>
		<th class=\"left\" style=\"border: 1px solid #edecdf;\" width=\"70\"><a href=\"".$_SERVER['PHP_SELF']."?urlview=";
		for($j = 1; $j <= $aantalcategories; $j++){
			$more_less .=  "0";
		}
		$more_less .=  "\">$shownone</a></th>";
		$more_less .=  "
		<th class=\"left\" style=\"border: 1px solid #edecdf;\" width=\"1\"><img src=\"../../template/classic/img/opendir.gif\" title=\"$showall\" /></th>
		<th class=\"left\" style=\"border: 1px solid #edecdf;\" width=\"70\"><a href=\"".$_SERVER['PHP_SELF']."?urlview=";
		for($j = 1; $j <= $aantalcategories; $j++) {
			$more_less .=  "1";
		}
		$more_less .=  "\">$showall</a></th>";
		$more_less .= "</tr></thead></table>";
	}


    // Edw fiaxnei ton pinaka me tis Genikes kathgories
	if ($numberofzerocategory !== 0) {
		$tool_content .= "\n
		<table width=\"99%\" class=\"FormData\" style=\"border: 1px solid #edecdf;\">
		<thead>
		<tr>
		<th width='15' style=\"border: 1px solid #edecdf;\"><img src=\"../../template/classic/img/opendir.gif\" title=\"$langNoCategory\" /></th>
		<th class='left' colspan='6' style=\"border: 1px solid #edecdf;\">$langNoCategory</th>
		</tr>
		</thead>
		<tbody>";
		showlinksofcategory(0);
		$tool_content .= "</tbody></table>";
	}

	// Edw fiaxnei to tool bar me tin emfanisi apokripsi
	$tool_content .= "<br />$more_less
	<table width=\"99%\" class=\"FormData\" style=\"border: 1px solid #edecdf;\">
	<tbody>";
	$i=0;
	$catcounter=1;
	$view="0";
	while ($myrow=@mysql_fetch_array($resultcategories)) {
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
			$newurlview = $urlview;
			$newurlview[$i] = "0";
			$tool_content .= "<tr>
			<td class=\"left\" width='15'><img src=\"../../template/classic/img/opendir.gif\" title=\"$shownone\" /></td>
			<td colspan=\"2\"><a href=\"".$_SERVER['PHP_SELF']."?urlview=".$newurlview."\">".q($myrow["categoryname"])."</a>";
                        if (!empty($myrow["description"])) {
                                $tool_content .= "<br /><small>".q($myrow["description"])."</small></td>";
                        }
                        if ($is_adminOfCourse) {
                                showcategoryadmintools($myrow["id"]);
                        } else {
                                $tool_content .=  "<td width='45'>&nbsp;</td>
                                        <td width='45'>&nbsp;</td>
                                        </tr>";
                        }
			showlinksofcategory($myrow["id"]);
		} else {
			$tool_content .=  "
			<tr class=\"odd\">
			<td class=\"left\" width='15'><img src=\"../../template/classic/img/closeddir.gif\" title=\"$showall\" /></td>
			<td colspan=\"2\"><a href=\"".$_SERVER['PHP_SELF']."?urlview=";
			$tool_content .=  is_array($view)?implode('',$view):$view;
			$tool_content .=  "\">".q($myrow["categoryname"])."</a>";
                        if (!empty($myrow["description"])) {
                                $tool_content .= "<br /><small>".q($myrow["description"])."</small></td>";
                        }
			if ($is_adminOfCourse) {
			showcategoryadmintools($myrow["id"]);
			} else {
				$tool_content .=  "
				<td width='45'>&nbsp;</td>
				<td width='45'>&nbsp;</td>
				</tr>";
			}
		}
		// displaying the link of the category
		$i++;
	}
	$tool_content .=  "</tbody></table>";
} else {   // no category
   if (getNumberOfLinks(0) > 0) {
		$tool_content .=  "<table width=\"99%\" class=\"FormData\" style=\"border: 1px solid #edecdf;\">
		<tbody>
		<tr>
		<td width='1' style='background:#FBFBFB; border-left: 1px solid #edecdf; border-top: 1px solid #edecdf;'><img src=\"../../template/classic/img/opendir.gif\" title=\"$langNoCategory\" /></td>
		<td class=\"left\" colspan=\"4\" style='background:#FBFBFB; border-top: 1px solid #edecdf; border-right: 1px solid #edecdf;'><b>$langLinks</b></td>
		</tr>";
		showlinksofcategory(0);
		$tool_content .=  "</tbody></table>";
	} else {
		if($is_adminOfCourse){
			//if the user is the course administrator instruct him/her
			//what he can do to add links
			$tool_content .= "<p class='alert1'>$langProfNoLinksExist</p>";
		} else {
			//if the user has no course administrator access
			//inform him/her that no links exist
			$tool_content .= "<p class='alert1'>$langNoLinksExist</p>";
		}
	}
}
add_units_navigation(TRUE);
draw($tool_content, 2, '', $head_content);
?>
