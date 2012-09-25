<?php
/*========================================================================
*   Open eClass 3.0
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2012  Greek Universities Network - GUnet
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
* ========================================================================*/

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'admin.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'hierarchy_validations.php';

$tree = new hierarchy();
$user = new user();

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$nameTools = $langListUsersActions;

define('USERS_PER_PAGE', 15);

// get the incoming values
$search = isset($_GET['search'])? $_GET['search']: '';
$c = isset($_GET['c'])? intval($_GET['c']): '';
$lname = isset($_GET['lname'])? $_GET['lname']: '';
$fname = isset($_GET['fname'])? $_GET['fname']: '';
$uname = isset($_GET['uname'])? canonicalize_whitespace($_GET['uname']): '';
$am = isset($_GET['am'])? $_GET['am']: '';
$verified_mail = isset($_GET['verified_mail'])? intval($_GET['verified_mail']): 3;
$user_type = isset($_GET['user_type'])? intval($_GET['user_type']): '';
$auth_type = isset($_GET['auth_type'])? intval($_GET['auth_type']): '';
$email = isset($_GET['email'])? mb_strtolower(trim($_GET['email'])): '';
$reg_flag = isset($_GET['reg_flag'])? intval($_GET['reg_flag']): '';
$hour = isset($_GET['hour'])? intval($_GET['hour']): 0;
$minute = isset($_GET['minute'])? intval($_GET['minute']): 0;
$ord = isset($_GET['ord'])? $_GET['ord']: '';
$limit = isset($_GET['limit'])? intval($_GET['limit']): 0;
$mail_ver_required = get_config('email_verification_required');

// Display Actions Toolbar
$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='$_SERVER[SCRIPT_NAME]'>$langAllUsers</a></li>
      <li><a href='search_user.php'>$langSearchUser</a></li>
      <li><a href='$_SERVER[SCRIPT_NAME]?search=inactive'>$langInactiveUsers</a></li>
    </ul>
  </div>";


/***************
Criteria/Filters
***************/
$criteria = array();
$params = array();
function add_param($name, $value = null) {
        global $params;
        if (!isset($value)) {
                $value = $GLOBALS[$name];
        }
        if ($value !== 0 and $value !== '') {
                $params[] = $name . '=' . urlencode($value);
        }
}
// Registration date/time search
if (isset($_GET['date']) or $hour or $minute) {
        $date = explode('-',  $_GET['date']);
        if (count($date) == 3) {
                $day = intval($date[0]);
                $month = intval($date[1]);
                $year = intval($date[2]);
                $user_registered_at = mktime($hour, $minute, 0, $month, $day, $year);
                add_param('date', "$day-$month-$year");
        } else {
                $user_registered_at = mktime($hour, $minute, 0, 0, 0, 0);
        }
        // join the above with registered at search
        $criteria[] = 'registered_at ' .
                      (($reg_flag === 1)? '>=': '<=') .
                      ' ' . $user_registered_at;
        add_param('reg_flag');
        add_param('hour');
        add_param('minute');
}
// surname search
if (!empty($lname)) {
	$criteria[] = 'nom LIKE ' . quote('%' . $lname . '%');
        add_param('lname');
}
// first name search
if (!empty($fname)) {
	$criteria[] = 'prenom LIKE ' . quote('%' . $fname . '%');
        add_param('fname');
}
// username search
if (!empty($uname)) {
	$criteria[] = 'username LIKE ' . quote('%' . $uname . '%');
        add_param('uname');
}
// mail verified
if ($verified_mail === EMAIL_VERIFICATION_REQUIRED or
    $verified_mail === EMAIL_VERIFIED or
    $verified_mail === EMAIL_UNVERIFIED) {
        $criteria[] = 'verified_mail = ' . $verified_mail;
        add_param('verified_mail');
}
//user am search
if (!empty($user_am)) {
	$criteria[] = "am LIKE " . quote('%' . $am . '%');
        add_param('am');
}
// user type search
if (!empty($user_type)) {
	$criteria[] = "statut = " . $user_type;
        add_param('user_type');
}
// auth type search
if (!empty($auth_type)) {
	if ($auth_type >= 2) {
		$criteria[] = 'password = ' . quote($auth_ids[$auth_type]);
	} elseif ($auth_type == 1) {
                $criteria[] = 'password NOT IN (' .
                        implode(', ', $auth_ids) . ')';
	}
        add_param('auth_type');
}
// email search
if (!empty($email)) {
	$criteria[] = 'email LIKE ' . quote('%' . $email . '%');
        add_param('email');
}
// search for inactive users
if ($search == 'inactive') {
	$criteria[] = 'expires_at < '.time().' AND user_id <> 1';
        add_param('search', 'inactive');
}

// Department search
$depqryadd = '';
if ( (isset($_GET['department']) and count($_GET['department'])) or (isDepartmentAdmin()) )
{
    $depqryadd = ', user_department';

    $deps = array();
    if ( isset($_GET['department']) and count($_GET['department']) )
        $deps = array_map('intval', $_GET['department']);
    else if (isDepartmentAdmin())
        $deps = $user->getDepartmentIds($uid);

    $pref = ($c) ? 'a' : 'user';
    $criteria[] = $pref . '.user_id = user_department.user';
    $criteria[] = 'department IN (' . implode(', ', $deps) . ')';

    foreach ($deps as $dep_id) {
        add_param('department[]', $dep_id);
        validateNode($dep_id, isDepartmentAdmin());
    }
}

if (count($criteria)) {
        $qry_criteria = implode(' AND ', $criteria);
} else {
        $qry_criteria = '';
}

// end filter/criteria
$ord_param_id = 0;
if (!empty($ord)) { // if we want to order results
	switch ($ord) {
		case 's': $order = 'statut, prenom, nom'; break;
		case 'n': $order = 'nom, prenom, statut'; break;
		case 'p': $order = 'prenom, nom, statut'; break;
		case 'u': $order = 'username, statut, prenom'; break;
		default: $order = 'statut, prenom, nom'; break;
	}
        add_param('ord');
        $ord_param_id = count($params);
} else {
	$order = 'statut';
}
if ($c) { // users per course
        $qry_base = "FROM user AS a
                          LEFT JOIN course_user AS b
                               ON a.user_id = b.user_id
                          $depqryadd
                     WHERE b.course_id = $c";
        if ($qry_criteria) {
                $qry_base .= ' AND ' . $qry_criteria;
        }
        $count_qry = "SELECT count(DISTINCT a.user_id) AS num, b.statut AS user_type " .
                     $qry_base;
        $qry = "SELECT DISTINCT a.user_id,a.nom, a.prenom, a.username, a.email,
                       a.verified_mail, b.statut " . $qry_base;
        add_param('c');
} elseif ($search == 'no_login') { // users who have never logged in
        $qry_base = "FROM user
                          LEFT JOIN loginout
                               ON user.user_id = loginout.id_user
                          $depqryadd
                          WHERE loginout.id_user IS NULL";
        if ($qry_criteria) {
                $qry_base .= ' AND ' . $qry_criteria;
        }
        $count_qry = "SELECT count(DISTINCT user_id) AS num, statut AS user_type " . $qry_base;
        $qry = "SELECT DISTINCT user_id, nom, prenom, username, email, verified_mail, statut " .
               $qry_base;
        add_param('search', 'no_login');
} else {
	// Count users, with or without criteria/filters
        $qry_base = ' FROM user' . $depqryadd;
        if ($qry_criteria) {
                $qry_base .= ' WHERE ' . $qry_criteria;
        }
        $count_qry = 'SELECT count(DISTINCT user_id) AS num, statut AS user_type' .
                $qry_base;
        $qry = 'SELECT DISTINCT user_id, nom, prenom, username, email, statut, verified_mail' .
                $qry_base;
}

// User statistics
$sql = db_query($count_qry . ' GROUP BY user_type');
$countUser = $teachers = $students = $visitors = $other = 0;
while ($row = mysql_fetch_assoc($sql, MYSQL_ASSOC)) {
        $countUser += $row['num'];;
        switch ($row['user_type']) {
            case USER_TEACHER:
                $teachers += $row['num'];
                break;
            case USER_STUDENT:
                $students += $row['num'];
                break;
            case USER_GUEST:
                $visitors += $row['num'];
                break;
            default:
                $other += $row['num'];
                break;
        }
}
mysql_free_result($sql);

$caption = '';
$pagination_link = '&amp;' . implode('&amp;', $params);

// Remove 'ord' parameter
if ($ord_param_id) {
        unset($params[$ord_param_id -1]);
}
$header_link = '&amp;' . implode('&amp;', $params);

if($countUser > 0) {
        $caption .= "$langThereAre: <b>$teachers</b> $langTeachers, <b>$students</b> $langStudents
                $langAnd <b>$visitors</b> $langVisitors<br />";
        $caption .= "$langTotal: <b>$countUser</b> $langUsers<br />";
        if ($search == 'inactive') {  // inactive users
                $caption .= "&nbsp;$langAsInactive<br />";
                $caption .= "<a href='updatetheinactive.php?activate=1'>".$langAddSixMonths."</a><br />";
        }
        if ($countUser >= USERS_PER_PAGE) { // display navigation links if more than USERS_PER_PAGE
                $tool_content .= show_paging($limit, USERS_PER_PAGE, $countUser, $_SERVER['SCRIPT_NAME'], $pagination_link);
        }
        $qry .= " ORDER BY $order LIMIT $limit, ".USERS_PER_PAGE;
        $sql = db_query($qry);

        $tool_content .= "
        <table class='tbl_alt' width='100%'>
        <tr>
          <th colspan='2' width='150'><div align='left'><a href='$_SERVER[SCRIPT_NAME]?ord=n$header_link'>$langSurname</a></div></th>
          <th width='100'><div align='left'><a href='$_SERVER[SCRIPT_NAME]?ord=p$header_link'>$langName</a></div></th>
          <th width='170'><div align='left'><a href='$_SERVER[SCRIPT_NAME]?ord=u$header_link'>$langUsername</a></div></th>
          <th scope='col'>$langEmail</th>
          <th scope='col'><a href='$_SERVER[SCRIPT_NAME]?ord=s$header_link'>$langProperty</a></th>
          <th scope='col'>$langActions</th>
        </tr>";
        $k = 0;
        for ($j = 0; $j < mysql_num_rows($sql); $j++) {
                while($logs = mysql_fetch_assoc($sql)) {
                        if ($k%2 == 0) {
                                $tool_content .= "<tr class='even'>";
                        } else {
                                $tool_content .= "<tr class='odd'>";
                        }
                        $tool_content .= "<td width='1'>
                        <img src='$themeimg/arrow.png' alt=''></td>
                        <td>".q($logs['nom'])."</td>
                        <td>".q($logs['prenom'])."</td>
                        <td>".q($logs['username'])."</td>
                        <td width='200'>".q($logs['email']);
                        if ($mail_ver_required) {
                                switch($logs['verified_mail']) {
                                    case EMAIL_VERIFICATION_REQUIRED:
                                        $icon = 'pending';
                                        $tip = $langMailVerificationPendingU;
                                        break;
                                   case EMAIL_VERIFIED:
                                        $icon = 'tick_1';
                                        $tip = $langMailVerificationYesU;
                                        break;
                                   default:
                                        $icon = 'not_confirmed';
                                        $tip = $langMailVerificationNoU;
                                        break;
                                }
                                $tool_content .= " <img align='right' src='$themeimg/$icon.png' " .
                                        "title='$tip' alt='$tip'>";
                        }

                        switch ($logs['statut']) {
                            case USER_TEACHER:
                                $icon = 'teacher';
                                $tip = $langTeacher;
                                break;
                            case USER_STUDENT:
                                $icon = 'student';
                                $tip = $langStudent;
                                break;
                            case USER_GUEST:
                                $icon = 'guest';
                                $tip = $langVisitor;
                                break;
                            default:
                                $icon = false;
                                $tool_content .= "</td><td class='center'>$langOther (" .
                                        q($logs['statut']) . ')</td>';
                                break;
                        }
                        if ($icon) {
                                $tool_content .= "</td><td class='center'><img src='$themeimg/" .
                                        "$icon.png' title='$tip' alt='$tip'></td>";
                        }
                        if ($logs['user_id'] == 1) { // don't display actions for admin user
                                $tool_content .= "<td class='center'>&mdash;&nbsp;</td>";
                        } else {
                                $changetip = q("$langChangeUserAs $logs[username]");
                                $width = (!isDepartmentAdmin()) ? 100 : 80;
                                $tool_content .= "<td width='". $width ."'>
                                        <a href='edituser.php?u=$logs[user_id]'><img src='$themeimg/edit.png' title='$langEdit' alt='$langEdit'></a>
                                        <a href='deluser.php?u=$logs[user_id]'><img src='$themeimg/delete.png' title='$langDelete' alt='$langDelete'></a>
                                        <a href='userstats.php?u=$logs[user_id]'><img src='$themeimg/platform_stats.png' title='$langStat' alt='$langStat'></a>
                                        <a href='userlogs.php?u=$logs[user_id]'><img src='$themeimg/platform_stats.png' title='$langActions' alt='$langActions'></a>";
                                if (!isDepartmentAdmin())
                                        $tool_content .= "<a href='change_user.php?username=".urlencode($logs['username'])."'><img src='$themeimg/log_as.png' title='$changetip' alt='$changetip'></a>";
                                $tool_content .= "</td>\n";
                        }
                        $tool_content .= "</tr>";
                        $k++;
                }
        }
        $tool_content .= "</table> <br><div class='right smaller'>".$caption."</div>";
        if ($countUser >= USERS_PER_PAGE) { // display navigation links if more than USERS_PER_PAGE
                $tool_content .= show_paging($limit, USERS_PER_PAGE, $countUser, $_SERVER['SCRIPT_NAME'], $pagination_link);
        }
} else {
        $tool_content .= "<p class='caution'>$langNoSuchUsers</p>";
}
$tool_content .= "<p align='right'><a href='search_user.php?$pagination_link'>$langBack</a></p>";

draw($tool_content, 3);
