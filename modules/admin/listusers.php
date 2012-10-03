<?php
/*========================================================================
*   Open eClass 2.5
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
* ========================================================================*/

$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
include_once '../../modules/auth/auth.inc.php';
include 'admin.inc.php';

$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$nameTools = $langListUsersActions;

define ('USERS_PER_PAGE', 15);

$caption = "";
// get the incoming values
$search = isset($_GET['search'])? $_GET['search']:'';
$c = isset($_REQUEST['c'])? intval($_REQUEST['c']):'';
$user_surname = isset($_REQUEST['user_surname'])?$_REQUEST['user_surname']:'';
$user_firstname = isset($_REQUEST['user_firstname'])?$_REQUEST['user_firstname']:'';
$user_username = isset($_REQUEST['user_username'])?canonicalize_whitespace($_REQUEST['user_username']):'';
$user_am = isset($_REQUEST['user_am'])?$_REQUEST['user_am']:'';
$verified_mail = isset($_REQUEST['verified_mail'])?intval($_REQUEST['verified_mail']):3;
$user_type = isset($_REQUEST['user_type'])?$_REQUEST['user_type']:'';
$auth_type = isset($_REQUEST['auth_type'])?$_REQUEST['auth_type']:'';
$user_email = isset($_REQUEST['user_email'])?mb_strtolower(trim($_REQUEST['user_email'])):'';
$user_registered_at_flag = isset($_REQUEST['user_registered_at_flag'])?$_REQUEST['user_registered_at_flag']:'';
$hour = isset($_REQUEST['hour'])?$_REQUEST['hour']:0;
$minute = isset($_REQUEST['minute'])?$_REQUEST['minute']:0;
$ord = isset($_GET['ord'])?$_GET['ord']:'';
$limit = isset($_GET['limit'])?$_GET['limit']:0;
$mail_ver_required = get_config('email_verification_required');

$user_registered_at = '';
if ($search == 'yes')	{ // coming from search_user.php (search with criteria)
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

// Display Actions Toolbar
$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='$_SERVER[SCRIPT_NAME]?search=yes'>$langAllUsers</a></li>
      <li><a href='search_user.php'>$langSearchUser</a></li>
      <li><a href='$_SERVER[SCRIPT_NAME]?search=inactive'>$langInactiveUsers</a></li>
    </ul>
  </div>";


/***************
Criteria/Filters
***************/
$criteria = array();
// surname search
if (!empty($user_surname)) {
	$criteria[] = "nom LIKE " . autoquote('%' . $user_surname . '%');
}
// first name search
if (!empty($user_firstname)) {
	$criteria[] = "prenom LIKE " . autoquote('%' . $user_firstname . '%');
}
// username search
if (!empty($user_username)) {
	$criteria[] = "username LIKE " . autoquote('%' . $user_username . '%');
}
// mail verified
if ($verified_mail === EMAIL_VERIFICATION_REQUIRED or
    $verified_mail === EMAIL_VERIFIED or
    $verified_mail === EMAIL_UNVERIFIED) {
        $criteria[] = "verified_mail=" . autoquote($verified_mail);
}
//user am search
if (!empty($user_am)) {
	$criteria[] = "am LIKE " . autoquote('%' . $user_am . '%');
}
// user type search
if (!empty($user_type)) {
	$criteria[] = "statut=" . intval($user_type);
}
// auth type search
if (!empty($auth_type)) {
	if ($auth_type >= 2) {
		$criteria[] = "password=".quote($auth_ids[$auth_type]);
	} elseif ($auth_type == 1) {
                $criteria[] = "password NOT IN ('" .
                        implode("', '", $auth_ids) .
                        "')";
	}
}
// email search
if (!empty($user_email)) {
	$criteria[] = " email LIKE " . autoquote('%' . $user_email . '%');
}
// join the above with registered at search
if (!empty($user_registered_at_flag) and !empty($user_registered_at)) {
	$user_registered_at_qry = "registered_at " .
                (($user_registered_at_flag == 1)? '>=': '<=') .
                ' ' . $user_registered_at;
	$criteria[] = $user_registered_at_qry;
}

// end filter/criteria

if ($search == 'inactive') { // search for inactive users
	$criteria[] = "expires_at < ".time()." AND user_id <> 1";
} elseif ($search == 'no_login') {
	$no_login_qry = "SELECT `user_id`, `nom`, `prenom`, `username`, `email`, `verified_mail`, `statut`
                                FROM `user` LEFT JOIN `loginout`
                                ON `user`.`user_id` = `loginout`.`id_user`
                                WHERE `loginout`.`id_user` IS NULL";
}

if (!empty($ord)) { // if we want to order results
	switch ($ord) {
		case "s": $order = "statut,prenom,nom"; break;
		case "n": $order = "nom,prenom,statut"; break;
		case "p": $order = "prenom,nom,statut"; break;
		case "u": $order = "username,statut,prenom"; break;
		default: $order = "statut,prenom,nom"; break;
	}
} else {
	$order = "statut";
}

if (!empty($c)) { // users per course
	$qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, a.verified_mail, b.statut
		FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
		WHERE b.cours_id = $c";
} elseif (!empty($users_active_qry)) { // inactive users
         $qry = "SELECT user_id, nom, prenom, username, email, verified_mail, statut
                 FROM user WHERE $users_active_qry";
} elseif (!empty($no_login_qry)) { // users who have never logged in
         $qry = $no_login_qry;
} else {
	// Count users, with or without criteria/filters
	$qry = "SELECT user_id, nom, prenom, username, email, statut, verified_mail FROM user";
	if (count($criteria)) {
                $qry .= " WHERE " . implode(' AND ', $criteria);
        }
}
$sql = db_query($qry);
$countUser = mysql_num_rows($sql);
$teachers = 0;
$students = 0;
$visitors = 0;
$other = 0;
while($numrows = mysql_fetch_array($sql, MYSQL_ASSOC)) {
        switch ($numrows['statut'])
        {
                case 1:	$teachers++; break;
                case 5:	$students++; break;
                case 10: $visitors++; break;
                default: $other++; break;
        }
}

if($countUser > 0) {
        $caption .= "$langThereAre: <b>$teachers</b> $langTeachers, <b>$students</b> $langStudents
                $langAnd <b>$visitors</b> $langVisitors<br />";
        $caption .= "$langTotal: <b>$countUser</b> $langUsers<br />";
        if($search == 'inactive') {  // inactive users
                $caption .= "&nbsp;$langAsInactive<br />";
                $caption .= "<a href='updatetheinactive.php?activate=1'>".$langAddSixMonths."</a><br />";
                $header_link = $pagination_link = '';
        } elseif ($search == 'no_login') {
                $qry = $no_login_qry;
                $header_link = $pagination_link = "&amp;search=no_login";
        } elseif (!empty($c)) { //users per course
                $header_link = $pagination_link = "&amp;c=$c";
                $qry = "SELECT a.user_id,a.nom, a.prenom, a.username, a.email, a.verified_mail, b.statut
                FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
                WHERE b.cours_id=$c";
        } else { // search with criteria
                $header_link = $pagination_link =
                        "&amp;user_surname=".urlencode($user_surname).
                        "&amp;user_firstname=".urlencode($user_firstname).
                        "&amp;user_username=".urlencode($user_username).
                        "&amp;user_am=".urlencode($user_am).
                        "&amp;user_email=".urlencode($user_email).
                        "&amp;user_type=".urlencode($user_type).
                        "&amp;auth_type=".urlencode($auth_type).
                        "&amp;user_registered_at_flag=".urlencode($user_registered_at_flag).
                        "&amp;verified_mail=$verified_mail";

                $qry = "SELECT user_id, nom, prenom, username, email, statut, verified_mail FROM user";
                if (count($criteria)) {
                        $qry .= " WHERE " . implode(' AND ', $criteria);
                }
        }
        if (!empty($ord)) { // if we want to order results
                $pagination_link .= "&amp;ord=$ord";
        }
        if ($countUser >= USERS_PER_PAGE) { // display navigation links if more than USERS_PER_PAGE
                $tool_content .= show_paging($limit, USERS_PER_PAGE, $countUser, $_SERVER['SCRIPT_NAME'], $pagination_link);
        }
        $qry .= " ORDER BY $order LIMIT $limit, ".USERS_PER_PAGE."";
        mysql_free_result($sql);
        $sql = db_query($qry);

        $tool_content .= "
        <table class='tbl_alt' width='100%'>
        <tr>
          <th colspan='2' width='150'><div align='left'><a href='$_SERVER[SCRIPT_NAME]?ord=n$header_link'>$langSurname</a></div></th>
          <th width='100'><div align='left'><a href='$_SERVER[SCRIPT_NAME]?ord=p$header_link'>$langName</a></div></th>
          <th width='170'><div align='left'><a href='$_SERVER[SCRIPT_NAME]?ord=u$header_link'>$langUsername</a></div></th>
          <th scope='col'>$langEmail</th>
          <th scope='col'><a href='$_SERVER[SCRIPT_NAME]?ord=s$header_link'>$langProperty</a></th>
          <th scope='col' colspan='4'>$langActions</th>
        </tr>";
        $k = 0;
        for ($j = 0; $j < mysql_num_rows($sql); $j++) {
                while($logs = mysql_fetch_array($sql, MYSQL_ASSOC)) {
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
                                                $tool_content .= " <img align='right' src='$themeimg/pending.png' title='".q($langMailVerificationPendingU)."' alt='".q($langMailVerificationPendingU)."'>";
                                                break;
                                        case EMAIL_VERIFIED:
                                                $tool_content .= " <img align='right' src='$themeimg/tick_1.png' title='".q($langMailVerificationYesU)."' alt='".q($langMailVerificationYesU)."'>";
                                                break;
                                        case EMAIL_UNVERIFIED:
                                                $tool_content .= " <img align='right' src='$themeimg/not_confirmed.png' title='".q($langMailVerificationNoU)."' alt='".q($langMailVerificationNoU)."'>";
                                                break;
                                }
                        }
                        $tool_content .= "</td>
                        <td align='center'>";
                        switch ($logs['statut'])
                        {
                                case 1:	$tool_content .= "<img src='$themeimg/teacher.png' title='".q($langTeacher)."' alt='".q($langTeacher)."'>";break;
                                case 5:	$tool_content .= "<img src='$themeimg/student.png' title='".q($langStudent)."' alt='".q($langStudent)."'>";break;
                                case 10: $tool_content .= "<img src='$themeimg/guest.png' title='".q($langVisitor)."' alt='".q($langVisitor)."'>";break;
                                default: $tool_content .= "$langOther (".q($logs[6]).")";break;
                        }
                        $tool_content .= "</td>";
                        if ($logs['user_id'] == 1) { // don't display actions for admin user
                                $tool_content .= "<td class='center'>&mdash;&nbsp;</td>";
                        } else {
                                $tool_content .= "<td width='80'><a href='edituser.php?u=".$logs['user_id']."'>
                                <img src='$themeimg/edit.png' title='".q($langEdit)."' alt='".q($langEdit)."'></a>
                                <a href='deluser.php?u=".$logs['user_id']."'>
                                <img src='$themeimg/delete.png' title='".q($langDelete)."' alt='".q($langDelete)."'>
                                </a>
                                <a href='userstats.php?u=".$logs['user_id']."'>
                                <img src='$themeimg/platform_stats.png' title='".q($langStat)."' alt='".q($langStat)."'></a>

                                <a href='change_user.php?username=".urlencode($logs['username'])."'>
                                <img src='$themeimg/log_as.png' title='".q($langChangeUserAs)." ".
                                     q($logs['username'])."' alt='".q($langChangeUserAs)." ".
                                     q($logs['username'])."'></a>
                                </td>\n";
                        }
                        $tool_content .= "</tr>";
                        $k++;
                }
        }
        
        // caption
        $tool_content .= "</table> <br/> <div class='right smaller'>".$caption;
        
        // delete all function
        $tool_content .= " <form action='multideluser.php' method='post' name='delall_user_search'>";
        // redirect all request vars towards delete all action
        foreach ($_REQUEST as $key => $value) {
            $tool_content .= "<input type='hidden' name='$key' value='$value' />";
        }
        $tool_content .= "<input type='submit' name='dellall_submit' value='$langDelList'></form>";
        $tool_content .= "</div>";
        
        if ($countUser >= USERS_PER_PAGE) { // display navigation links if more than USERS_PER_PAGE
                $tool_content .= show_paging($limit, USERS_PER_PAGE, $countUser, "$_SERVER[SCRIPT_NAME]", "$pagination_link");
        }
} else {
        $tool_content .= "<p class='caution'>$langNoSuchUsers</p>";
}
$tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";

draw($tool_content, 3);
