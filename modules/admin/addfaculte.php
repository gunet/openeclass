<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*===========================================================================
	addfaculte.php
	@last update: 12-07-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Manage Facultes

 	This script allows the administrator to list the available facultes, to
 	delete them or to make new ones.

 	The user can : - See the available facultes
 	               - Delete a faculte
 	               - Create a new faculte
 	               - Edit a faculte
                 - Return to main administrator page

 	@Comments: The script is organised in four sections.

  1) List of available facultes
  2) Add a new faculte
  3) Delete a faculte
  4) Display all on an HTML page

==============================================================================*/

/*****************************************************************************
		DEAL WITH  BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools=$langListFaculteActions;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
if (isset($a)) {
	switch ($a) {
		case 1:
			$navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langListFaculteActions);
			$nameTools = $langFaculteAdd;
			break;
		case 2:
			$navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langListFaculteActions);
			$nameTools = $langFaculteDel;
			break;
		case 3:
			$navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langListFaculteActions);
			$nameTools = $langFaculteEdit;
			break;
	}
}
// Initialise $tool_content
$tool_content = "";
/*****************************************************************************
		MAIN BODY
******************************************************************************/
	// Give administrator a link to add a new faculty
    $tool_content .= "<div id='operations_container'>
	<ul id='opslist'>
	<li><a href='$_SERVER[PHP_SELF]?a=1'>".$langAdd."</a></li>
	</ul>
	</div>";

// Display all available faculties
if (!isset($a)) {
	// Count available faculties
	$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM faculte"));
	// Construct a table
	$tool_content .= "<table width='99%' class='FormData' align='left'>
	<tbody>
	<tr>
	<td class='odd'><b>".$langFaculteCatalog."</b>:
	<div align='right'><i>".$langManyExist.": <b>$a[0]</b> ".$langFaculties."</i></div></td>
	</tr>
	</tbody>
	</table>
	<br />";
	$tool_content .= "<table width='99%' class='FormData' align='left'>
	<tbody><tr>
	<th scope='col' colspan='2'><div align='left'>&nbsp;&nbsp;".$langFaculty."</div></th scope='col'>
	<th scope='col'>$langCode</th>
	<th>".$langActions."</th>
	</tr>";
	$sql=mysql_query("SELECT code,name,id FROM faculte");
	$k = 0;
	// For all faculties display some info
	for ($j = 0; $j < mysql_num_rows($sql); $j++) {
		$logs = mysql_fetch_array($sql);
		if ($k%2==0) {
			$tool_content .= "\n  <tr>";
		} else {
			$tool_content .= "\n  <tr class='odd'>";
		}
		$tool_content .= "\n    <td width='1'>
		<img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
		$tool_content .= "\n    <td>".htmlspecialchars($logs[1])."</td>";
		$tool_content .= "\n    <td align='center'>".htmlspecialchars($logs[0])."</td>";
		// Give administrator a link to delete or edit a faculty
		$tool_content .= "\n    <td width='15%' align='center' nowrap>
		<a href='$_SERVER[PHP_SELF]?a=2&c=".$logs['id']."'>
		<img src='../../images/delete.gif' border='0' title='$langDelete'></img></a>&nbsp;&nbsp;
		<a href='$_SERVER[PHP_SELF]?a=3&c=".$logs['id']."'>
		<img src='../../template/classic/img/edit.gif' border='0' title='$langEdit'></img></a></td>
		</tr>\n";
		$k++;
	}
	// Close table correctly
	$tool_content .= "</tbody></table><br />";
	$tool_content .= "<br /><p align=\"right\"><a href=\"index.php\">".$langBack."</a></p>";
}
// Add a new faculte
elseif ($a == 1)  {
	if (isset($add)) {
		// Check for empty fields
		if (empty($codefaculte) or empty($faculte)) {
			$tool_content .= "<p>".$langEmptyFaculte."</p><br />";
			$tool_content .= "<center><p>
			<a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddFaculte."</a></p></center>";
			}
		// Check for greek letters
		elseif (!preg_match("/^[A-Z0-9a-z_-]+$/", $codefaculte)) {
			$tool_content .= "<p>".$langGreekCode."</p><br />";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddFaculte."</a></p></center>";
			}
		// Check if faculty code already exists
		elseif (mysql_num_rows(mysql_query("SELECT * from faculte WHERE code=" . autoquote($codefaculte))) > 0) {
			$tool_content .= "<p>".$langFCodeExists."</p><br />";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddFaculte."</a></p></center>";
			}
		// Check if faculty name already exists
		elseif (mysql_num_rows(mysql_query("SELECT * from faculte WHERE name=" . autoquote($faculte))) > 0) {
			$tool_content .= "<p>".$langFaculteExists."</p><br />";
			$tool_content .= "<center><p><a href=\"$_SERVER[PHP_SELF]?a=1\">".$langReturnToAddFaculte."</a></p></center>";
		} else {
		// OK Create the new faculty
			mysql_query("INSERT into faculte(code,name,generator,number) VALUES(" . autoquote($codefaculte) . ',' . autoquote($faculte) . ",'100','1000')")
				or die ($langNoSuccess);
			$tool_content .= "<p>".$langAddSuccess."</p><br />";
			}
	} else {
		// Display form for new faculte information
		$tool_content .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?a=1\">";
		$tool_content .= "<table width='99%' class='FormData'>
		<tbody><tr>
		<th width=\"220\">&nbsp;</th>
		<td colspan=\"2\"><b>$langFaculteIns</b></td>
		<tr>
		<th class='left'>".$langCodeFaculte1.":</th>
		<td><input class='FormData_InputText' type='text' name='codefaculte' value='".@$codefaculte."' /></td><td><i>".$langCodeFaculte2."</i></td>
		</tr>
		<tr>
		<th class='left'>".$langFaculty.":</th>
		<td><input class='FormData_InputText' type='text' name='faculte' value='".@$faculte."' /></td><td><i>".$langFaculte2."</i></td>
		</tr>
		<tr>
		<th>&nbsp;</th>
		<td><input type='submit' name='add' value='".$langAdd."' /></td>
		</tr>
		</tbody>
		</table>
		</form>";
	}
	$tool_content .= "<br /><p align='right'><a href='$_SERVER[PHP_SELF]'>".$langBack."</a></p>";
}
// Delete faculty
elseif ($a == 2) {
        $c = intval($_GET['c']);
	$s=mysql_query("SELECT * from cours WHERE faculteid=$c");
	// Check for existing courses of a faculty
	if (mysql_num_rows($s) > 0)  {
		// The faculty cannot be deleted
		$tool_content .= "<p>".$langProErase."</p><br />";
		$tool_content .= "<p>".$langNoErase."</p><br />";
	} else {
		// The faculty can be deleted
		mysql_query("DELETE from faculte WHERE id=$c");
		$tool_content .= "<p>$langErase</p><br />";
	}
	$tool_content .= "<br><p align='right'><a href='$_SERVER[PHP_SELF]'>".$langBack."</a></p>";
}
// Edit a faculte
elseif ($a == 3)  {
        $c = @intval($_REQUEST['c']);
	if (isset($_POST['edit'])) {
		// Check for empty fields
                $faculte = $_POST['faculte'];
		if (empty($faculte)) {
			$tool_content .= "<p>".$langEmptyFaculte."</p><br>";
			$tool_content .= "<p align='right'><a href='$_SERVER[PHP_SELF]?a=3&c=$c'>$langReturnToEditFaculte</a></p>";
			}
		// Check if faculte name already exists
		elseif (mysql_num_rows(mysql_query("SELECT * from faculte WHERE id <> $c AND name=" .
                                                   autoquote($faculte))) > 0) {
			$tool_content .= "<p>".$langFaculteExists."</p><br>";
			$tool_content .= "<p align='right'><a href='$_SERVER[PHP_SELF]?a=3&amp;c=$c'>$langReturnToEditFaculte</a></p>";
		} else {
		// OK Update the faculte
			mysql_query("UPDATE faculte SET name = " .
                                    autoquote($faculte) . " WHERE id=$c")
				or die ($langNoSuccess);
		// For backwards compatibility update cours and cours_facult also
			db_query("UPDATE cours SET faculte = " .
                                    autoquote($faculte) . " WHERE faculteid=$c")
				or die ($langNoSuccess);
			db_query("UPDATE cours_faculte SET faculte = " .
                                    autoquote($faculte) . " WHERE facid=$c")
				or die ($langNoSuccess);
			$tool_content .= "<p>$langEditFacSucces</p><br>";
			}
	} else {
		// Get faculte information
                $c = intval($_GET['c']);
		$sql = "SELECT code, name FROM faculte WHERE id=$c";
		$result = mysql_query($sql);
		$myrow = mysql_fetch_array($result);
		// Display form for edit faculte information
		$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]?a=3'>";
		$tool_content .= "<table width='99%' class='FormData'>
		<tbody>
		<tr>
		<th width='220'>&nbsp;</th>
		<td colspan='2'><b>$langFaculteEdit</b></td>
		</tr>
		<tr>
		<th class='left'>".$langCodeFaculte1.":</th>
		<td><input type='text' name='codefaculte' value='".$myrow['code']."' readonly='1' />&nbsp;<i>".$langCodeFaculte2."</i></td>
		</tr>
		<tr>
		<th class='left'>".$langFaculte1.":</th>
		<td><input type='text' name='faculte' value='".htmlspecialchars($myrow['name'], ENT_QUOTES)."' />&nbsp;<i>".$langFaculte2."</i></td>
		</tr>
		<tr>
		<th>&nbsp;</th>
		<td><input type='hidden' name='c' value='$c' />
		<input type='submit' name='edit' value='$langAcceptChanges' />
		</td>
		</tr>
		</tbody>
		</table>
		</form>";
	}
$tool_content .= "<br /><p align='right'><a href='$_SERVER[PHP_SELF]'>".$langBack."</a></p>";
}

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3);
