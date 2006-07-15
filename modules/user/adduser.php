<?
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	Á full copyright notice can be read in "/info/copyright.txt".
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

/* This script allows a course admin to add users to the course. */

$langFiles = 'registration';
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';

include '../../include/baseTheme.php';

$nameTools = $langAddUser;
$navigation[] = array ("url"=>"user.php", "name"=> $langUsers);

$tool_content="";

// IF PROF ONLY
if($is_adminOfCourse) {

if (isset($add)) {

	mysql_select_db($mysqlMainDb);
	$result = db_query("INSERT INTO cours_user (user_id, code_cours, statut) ".
		"VALUES ('".mysql_escape_string($add)."', '$currentCourseID', ".
		"'5')");
	if ($result) {
		$tool_content .=  "<p>$langTheU $langAdded</p>";
	} else {
		$tool_content .=  "<p>$langAddError</p>";
	}
	$tool_content .=  "</td></tr><tr><td><br><br><a href=\"adduser.php\">$langAddBack</a></td></tr>\n";
} else {

$tool_content .= "
	<p>$langAskUser</p>
	
	
    	<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" enctype=\"multipart/form-data\">";

if(!isset($search_nom)) $search_nom = "";
if(!isset($search_prenom)) $search_prenom = "";
if(!isset($search_uname)) $search_uname = "";
$tool_content .= <<<tCont
	<table>	
	<thead>
	<tr><th>$langSurname</th><td><input type="text" name="search_nom" value="$search_nom"></td></tr>
	
	<tr><th>$langName</th><td><input type="text" name="search_prenom" value="$search_prenom"></td></tr>
	
	<tr><th>$langUsername</th><td><input type="text" name="search_uname" value="$search_uname"></td></tr>
	</thead>
	</table>
	<br>
	<input type="submit" value="$langSearch">
	</form>
	
	<tr><td>
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
	// added by jexi
	if (!empty($users_file)) {
		$tmpusers=trim($_FILES['users_file']['name']);
		$tool_content .= <<<tCont2
		<table width=99% >
		<tr bgcolor=silver>
		<th>$langUsers</th><th>$langResult</th>
tCont2;
		$f=fopen($users_file,"r");
		while (!feof($f))	{
			$uname=trim(fgets($f,1024));
			if (!$uname) continue;
			if (!check_uname_line($uname)) {
				$tool_content .= "<tr><td colspan=\"2\">$langFileNotAllowed</td></tr>\n";
				break;
			}
			$result=adduser($uname,$currentCourseID);
			$tool_content .= "<tr><td align=center>$uname</td><td>";
			if ($result == -1) {
				$tool_content .= $langUserNoExist;
			} elseif ($result == -2) {
				$tool_content .= $langUserAlready;
			} else {
				$tool_content .= $langTheU.$langAdded;
			}
			$tool_content .= "</td></tr>\n";
		}
		$tool_content .= "</table>\n";
		fclose($f);
	}

    // end
    
	$query = join(' AND ', $search);
	if (!empty($query)) {
			db_query("CREATE TEMPORARY TABLE lala AS
			SELECT user_id FROM cours_user WHERE code_cours='$currentCourseID'
			");
		$result = db_query("SELECT u.user_id, u.nom, u.prenom, u.username FROM
			user u LEFT JOIN lala c ON u.user_id = c.user_id WHERE
			c.user_id IS NULL AND $query
			");
		if (mysql_num_rows($result) == 0) {
			$tool_content .= "<p>$langNoUsersFound </p></td></tr>\n";
		} else {

			$tool_content .= <<<tCont3
	<table width=99% >
	<thead>
		<tr>
			<th></th>
			<th>$langName</th>
			<th>$langSurname</th>
			<th>$langUsername</th>
			<th></th>
		</tr>
	<thead>
	<tbody>
tCont3;
			$i = 1;
			while ($myrow = mysql_fetch_array($result)) {
				if ($i % 2 == 0) {
					$tool_content .= "<tr>";
		        	} else {
					$tool_content .= "<tr class=\"odd\">";
				}
				$tool_content .= "<td>$i</td>".
				     "<td>$myrow[prenom]</td>".
				     "<td>$myrow[nom]</td>".
				     "<td>$myrow[username]</td>".
				     "<td><a href=\"$_SERVER[PHP_SELF]?add=$myrow[user_id]\">".
				     "$langRegister</a></td></tr>\n";
				$i++;
			}

	$tool_content .= "</tbody>";
	$tool_content .= "</table>";

        	}
		db_query("DROP TABLE lala");
	}
	$tool_content .= "
	<p><a href=\"user.php\">$langBackUser</a></p>";
	}
}


draw($tool_content, 2);


// function for adding users 

// returns -1 (error - user doesnt exist)
// returns -2 (error - user is already in the course)
// returns userid (yes  everything is ok )

function adduser($user,$course) {
	$result=db_query("SELECT user_id FROM user WHERE username='".mysql_escape_string($user)."'");
	if (!mysql_num_rows($result))
		return -1;
 
	$userid=mysql_fetch_array($result);
	$userid=$userid[0];

	$result = db_query("SELECT * from cours_user WHERE user_id='$userid' AND code_cours='$course'");
	if (mysql_num_rows($result) > 0)
		return -2;
	
	$result = db_query("INSERT INTO cours_user (user_id, code_cours, statut) VALUES ('$userid', '$course', '5')");
	return $userid;
}

// function for checking file

function check_uname_line($uname)
{
	if (preg_match("/[^a-zA-Z0-9.-_á-ùÁ-Ù]/", $uname)) {
		return FALSE;
	} else {
		return 	TRUE;
	}

}
?>
