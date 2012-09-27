<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
require_once 'admin.inc.php';

$nameTools = $langMultiDelUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
load_js('tools.js');


if (isset($_POST['submit'])) {
    
    $line = strtok($_POST['user_names'], "\n");
    
    while ($line !== false) {
        // strip comments
        $line = preg_replace('/#.*/', '', trim($line));
        
        if (!empty($line)) {
            // fetch uid
            $u = usernameToUid($line);
            
            // for real uids not equal to admin
            if ($u !== false && $u > 1) {
                // full deletion
                $success = deleteUser($u);
                // progress report
                if ($success === true)
                    $tool_content .= "<p>$langUserWithId $line $langWasDeleted.</p>\n";
                else
                    $tool_content .= "<p>$langErrorDelete: $line.</p>\n";
            }
        }
        
        $line = strtok("\n");
    }
    
} else {
    
    $usernames = '';
    
    if (isset($_POST['dellall_submit'])) {
        // get the incoming values
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $c = isset($_POST['c']) ? intval($_POST['c'])  :'';
        $user_surname = isset($_POST['user_surname']) ? $_POST['user_surname'] : '';
        $user_firstname = isset($_POST['user_firstname']) ? $_POST['user_firstname'] : '';
        $user_username = isset($_POST['user_username']) ? canonicalize_whitespace($_POST['user_username']) : '';
        $user_am = isset($_POST['user_am']) ? $_POST['user_am'] : '';
        $verified_mail = isset($_POST['verified_mail']) ? intval($_POST['verified_mail']) : 3;
        $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';
        $auth_type = isset($_POST['auth_type']) ? $_POST['auth_type'] : '';
        $user_email = isset($_POST['user_email']) ? mb_strtolower(trim($_POST['user_email'])) : '';
        $user_registered_at_flag = isset($_POST['user_registered_at_flag']) ? $_POST['user_registered_at_flag'] : '';
        $hour = isset($_POST['hour']) ? $_POST['hour'] : 0;
        $minute = isset($_POST['minute']) ? $_POST['minute'] : 0;
        
        $user_registered_at = '';
        if ($search == 'yes') {
            if (isset($_POST['date'])) {
                $date = explode('-',  $_POST['date']);
                if (count($date) == 3) {
                    $day = intval($date[0]);
                    $month = intval($date[1]);
                    $year = intval($date[2]);
                    $user_registered_at = mktime($hour, $minute, 0, $month, $day, $year);
                } else {
                    $user_registered_at = mktime($hour, $minute, 0, 0, 0, 0);
                }
            }
        }
        
        // Criteria/Filters
        $criteria = array();
        
        if (!empty($user_surname))
            $criteria[] = "nom LIKE " . autoquote('%' . $user_surname . '%');
        
        if (!empty($user_firstname))
            $criteria[] = "prenom LIKE " . autoquote('%' . $user_firstname . '%');

        if (!empty($user_username))
            $criteria[] = "username LIKE " . autoquote('%' . $user_username . '%');

        if ($verified_mail === EMAIL_VERIFICATION_REQUIRED or $verified_mail === EMAIL_VERIFIED or $verified_mail === EMAIL_UNVERIFIED)
            $criteria[] = "verified_mail=" . autoquote($verified_mail);

        if (!empty($user_am))
            $criteria[] = "am LIKE " . autoquote('%' . $user_am . '%');

        if (!empty($user_type))
            $criteria[] = "statut=" . intval($user_type);

        if (!empty($auth_type)) {
            if ($auth_type >= 2)
                $criteria[] = "password=".quote($auth_ids[$auth_type]);
            elseif ($auth_type == 1)
                $criteria[] = "password NOT IN ('" . implode("', '", $auth_ids) . "')";
        }

        if (!empty($user_email))
            $criteria[] = " email LIKE " . autoquote('%' . $user_email . '%');
        
        if (!empty($user_registered_at_flag) and !empty($user_registered_at))
            $criteria[] = "registered_at " . (($user_registered_at_flag == 1)? '>=': '<=') . ' ' . $user_registered_at;

        if ($search == 'inactive')
            $criteria[] = "expires_at < ".time()." AND user_id <> 1";
        elseif ($search == 'no_login')
            $no_login_qry = "SELECT `username` FROM `user` LEFT JOIN `loginout` ON `user`.`user_id` = `loginout`.`id_user` WHERE `loginout`.`id_user` IS NULL ORDER BY `username` ASC";
        // end filter/criteria
        
        if (!empty($c))
        	$qry = "SELECT a.username FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id WHERE b.cours_id = $c ORDER BY a.username ASC";
        elseif (!empty($no_login_qry))
        	$qry = $no_login_qry;
        else {
        	$qry = "SELECT username FROM user";
        	if (count($criteria))
        		$qry .= " WHERE " . implode(' AND ', $criteria);
        	$qry .= " ORDER BY username ASC";
        }
        
        $sql = db_query($qry);
        while($users = mysql_fetch_array($sql))
            $usernames .= $users['username'] . "\n";
    }
    
    
    $tool_content .= "<div class='noteit'>". $langMultiDelUserInfo ."</div>
        <form method='post' action='". $_SERVER['SCRIPT_NAME'] ."'>
        <fieldset>
        <legend>". $langMultiDelUserData ."</legend>
        <table class='tbl' width='100%'>
        <tr>
            <th>". $langUsersData .":</th>
            <td><textarea class='auth_input' name='user_names' rows='30' cols='60'>$usernames</textarea></td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td class='right'><input type='submit' name='submit' value='". $langSubmit ."' onclick='return confirmation(\"". $langMultiDelUserConfirm ."\");' /></td>
        </tr>
        </table>
        </fieldset>
        </form>";
}

$tool_content .= "<div class='right'><a href='index.php'>$langBackAdmin</a></div><br/>\n";
draw($tool_content, 3, 'admin', $head_content);


// Translate username to uid
function usernameToUid($uname)
{
	global $mysqlMainDb;

	if ($r = mysql_fetch_row(db_query("SELECT user_id FROM user WHERE username = ". quote($uname), $mysqlMainDb))) {
		return intval($r[0]);
	} else {
		return false;
	}
}
