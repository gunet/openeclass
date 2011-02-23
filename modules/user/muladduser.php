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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';

include '../../include/baseTheme.php';

$nameTools = $langAddManyUsers;
$navigation[] = array ("url"=>"user.php", "name"=> $langAdminUsers);

$tool_content = "";

// IF PROF ONLY
if($is_adminOfCourse) {
    $tool_content .= "
    <form method='post' action='".$_SERVER['PHP_SELF']."' enctype='multipart/form-data'>";
    $tool_content .= "
    <fieldset>
    <legend>$langUsersData</legend>
    <table width='99%' class='tbl'>
    <tr>
      <th width='180' class='left'>$langAskUserFile</th>
      <td><input type='file' name='users_file'></td>
      <td align='justify'><small>$langAskManyUsers</small></td>
    </tr>
    <tr>
      <th class='left'>&nbsp;</th>
      <td><input type='submit' value='$langAdd'></td>
      <td align='right'>&nbsp;</td>
    </tr>
    </table>
    </fieldset>
    </form>
    <p>$langAskManyUsers1<br />$langAskManyUsers2</small></p>";

    mysql_select_db($mysqlMainDb);
    
    if (isset($_FILES['users_file']) && is_uploaded_file($_FILES['users_file']['tmp_name'])) {
	$tmpusers = trim($_FILES['users_file']['tmp_name']);
	$tool_content .= "<table width=99%><thead>
	    <tr><th>$langUsers</th><th>$langResult</th></thead><tbody>";
	$f = fopen($tmpusers,"r");
	while (!feof($f)) {
	    $uname = trim(fgets($f,1024));
	    if (!$uname) continue;
	    if (!check_uname_line($uname)) {
		    $tool_content .= "<tr><td colspan=\"2\">$langFileNotAllowed</td></tr>\n";
		    break;
	    }
	    $result = adduser($uname, $cours_id);
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
	$tool_content .= "</tbody></table>\n";
	fclose($f);
    }
} // end

draw($tool_content, 2);

// function for adding users

// returns -1 (error - user doesnt exist)
// returns -2 (error - user is already in the course)
// returns userid (yes  everything is ok )

function adduser($user, $cid) {
	$result = db_query("SELECT user_id FROM user WHERE username='".mysql_escape_string($user)."'");
	if (!mysql_num_rows($result))
	return -1;

	list($userid) = mysql_fetch_array($result);

	$result = db_query("SELECT * from cours_user WHERE user_id = $userid AND cours_id = $cid");
	if (mysql_num_rows($result) > 0)
	return -2;

	$result = db_query("INSERT INTO cours_user (user_id, cours_id, statut, reg_date)
			VALUES ($userid, $cid, '5', CURDATE())");
	return $userid;
}

// function for checking file
function check_uname_line($uname)
{
	if (preg_match("/[^a-zA-Z0-9.-_�-��-�-]/", $uname)) {
		return FALSE;
	} else {
		return 	TRUE;
	}

}
