<?
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

/* This script allows a course admin to add users to the course. */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';

include '../../include/baseTheme.php';

$nameTools = $langAddUser;
$navigation[] = array ("url"=>"user.php", "name"=> $langAdminUsers);

$tool_content="";

// IF PROF ONLY
if($is_adminOfCourse) {

if (isset($add)) {
	mysql_select_db($mysqlMainDb);
	$result = db_query("INSERT INTO cours_user (user_id, cours_id, statut, reg_date) ".
		"VALUES ('".mysql_escape_string($add)."', $cours_id, ".
		"'5', CURDATE())");

		$tool_content .= "<p class=\"success_small\">";
	if ($result) {
		$tool_content .=  "$langTheU $langAdded";
	} else {
		$tool_content .=  "$langAddError";
	}
		$tool_content .= "<br /><a href=\"adduser.php\">$langAddBack</a></p><br />\n";

} else {

	$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]'>";

if(!isset($search_nom)) $search_nom = "";
if(!isset($search_prenom)) $search_prenom = "";
if(!isset($search_uname)) $search_uname = "";
$tool_content .= <<<tCont

    <table width="99%" class="FormData">
    <tbody>
    <tr>
      <th width="220">&nbsp;</th>
      <td>$langAskUser<br /><br /><b>$langUserData</b></td>
    </tr>
    <tr>
      <th class="left">$langSurname</th>
      <td><input type="text" name="search_nom" value="$search_nom" class="FormData_InputText"></td>
    </tr>
	<tr>
      <th class="left">$langName</th>
      <td><input type="text" name="search_prenom" value="$search_prenom" class="FormData_InputText"></td>
    </tr>
	<tr>
      <th class="left">$langUsername</th>
      <td><input type="text" name="search_uname" value="$search_uname" class="FormData_InputText"></td>
    </tr>
    <tr>
      <th class="left">&nbsp;</th>
      <td><input type="submit" value="$langSearch"></td>
    </tr>
	</tbody>
	</table>
	<br />

    </form>

tCont;

	mysql_select_db($mysqlMainDb);
	$search=array();
	if(!empty($search_nom)) {
		$search[] = "u.nom LIKE '".mysql_escape_string($search_nom)."%'";
	}
	if(!empty($search_prenom)) {
		$search[] = "u.prenom LIKE '".mysql_escape_string($search_prenom)."%'";
	}
	if(!empty($search_uname)) {
		$search[] = "u.username LIKE '".mysql_escape_string($search_uname)."%'";
	}

	$query = join(' AND ', $search);
	if (!empty($query)) {
			db_query("CREATE TEMPORARY TABLE lala AS
			SELECT user_id FROM cours_user WHERE cours_id = $cours_id
			");
			$result = db_query("SELECT u.user_id, u.nom, u.prenom, u.username FROM
			user u LEFT JOIN lala c ON u.user_id = c.user_id WHERE
			c.user_id IS NULL AND $query");
			if (mysql_num_rows($result) == 0) {
				$tool_content .= "<p class=\"caution_small\">$langNoUsersFound</p>\n";
			} else {
				$tool_content .= <<<tCont3

    <table width=99% class="FormData">
    <tbody>
    <tr>
      <th class="right" width="2%">$langID</th>
      <th class="left">$langName</th>
      <th class="left">$langSurname</th>
      <th class="left">$langUsername</th>
      <th>$langActions</th>
    </tr>
tCont3;
			$i = 1;
			while ($myrow = mysql_fetch_array($result)) {
				if ($i % 2 == 0) {
					$tool_content .= "<tr>";
		        	} else {
					$tool_content .= "<tr class=\"odd\">";
				}
				$tool_content .= "<td align=\"right\">$i.</td><td>$myrow[prenom]</td>
      				<td>$myrow[nom]</td><td>$myrow[username]</td>
      				<td align=\"center\">
				<a href=\"$_SERVER[PHP_SELF]?add=$myrow[user_id]\">$langRegister</a></td></tr>\n";
				$i++;
			}
			$tool_content .= "</tbody>";
			$tool_content .= "</table>";
        		}
			db_query("DROP TABLE lala");
		} 
	}
}

draw($tool_content, 2, 'user');

?>
