<?php

/* ========================================================================
 *   Open eClass 3.0
 *   E-learning and Course Management System
 * ========================================================================
 *  Copyright(c) 2003-2014  Greek Universities Network - GUnet
 *  A full copyright notice can be read in "/info/copyright.txt".
 *
 *  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
 * 			Yannis Exidaridis <jexi@noc.uoa.gr>
 * 			Alexandros Diamantidis <adia@noc.uoa.gr>
 * 			Tilemachos Raptis <traptis@noc.uoa.gr>
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
 * ======================================================================== */
/**
 * @file listusers.php
 * @brief display list of users
 */
$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'hierarchy_validations.php';

load_js('tools.js');
load_js('jquery');
load_js('datatables');
$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#search_results_table').DataTable ({
                'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
                ],
                'sPaginationType': 'full_numbers',
                    'aoColumns': [
                        null,
                        null,
                        null,
                        {'bSortable' : false },
                        {'bSortable' : false },
                        {'bSortable' : false },
                    ],
                    'oLanguage': {                       
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                       'sInfoFiltered': '',
                       'sInfoPostFix':  '',
                       'sSearch':       '".$langSearch."',
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
            });
        });
        </script>";

$tree = new Hierarchy();
$user = new User();

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'search_user.php', 'name' => $langSearchUser);
$nameTools = $langListUsersActions;

// get the incoming values
$search = isset($_GET['search']) ? $_GET['search'] : '';
$c = isset($_GET['c']) ? intval($_GET['c']) : '';
$lname = isset($_GET['lname']) ? $_GET['lname'] : '';
$fname = isset($_GET['fname']) ? $_GET['fname'] : '';
$uname = isset($_GET['uname']) ? canonicalize_whitespace($_GET['uname']) : '';
$am = isset($_GET['am']) ? $_GET['am'] : '';
$verified_mail = isset($_GET['verified_mail']) ? intval($_GET['verified_mail']) : 3;
$user_type = isset($_GET['user_type']) ? intval($_GET['user_type']) : '';
$auth_type = isset($_GET['auth_type']) ? intval($_GET['auth_type']) : '';
$email = isset($_GET['email']) ? mb_strtolower(trim($_GET['email'])) : '';
$reg_flag = isset($_GET['reg_flag']) ? intval($_GET['reg_flag']) : '';
$user_registered_at = isset($_GET['user_registered_at']) ? $_GET['user_registered_at'] : '';
$mail_ver_required = get_config('email_verification_required');

// Display Actions Toolbar
$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='$_SERVER[SCRIPT_NAME]'>$langAllUsers</a></li>      
      <li><a href='$_SERVER[SCRIPT_NAME]?search=inactive'>$langInactiveUsers</a></li>
    </ul>
  </div>";


/* * *************
  Criteria/Filters
 * ************* */
$criteria = array();
$params = array();

// Registration date/time search
if (!empty($user_registered_at)) {
    add_param('reg_flag');
    add_param('user_registered_at');
    // join the above with registered at search
    $criteria[] = 'registered_at ' . (($reg_flag === 1) ? '>=' : '<=') . ' ' . quote($user_registered_at);
}
// surname search
if (!empty($lname)) {
    $criteria[] = 'surname LIKE ' . quote('%' . $lname . '%');
    add_param('lname');
}
// first name search
if (!empty($fname)) {
    $criteria[] = 'givenname LIKE ' . quote('%' . $fname . '%');
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
if (!empty($am)) {
    $criteria[] = "am LIKE " . quote('%' . $am . '%');
    add_param('am');
}
// user type search
if (!empty($user_type)) {
    $criteria[] = "status = " . $user_type;
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
    $criteria[] = 'expires_at < ' . time() . ' AND id <> 1';
    add_param('search', 'inactive');
}

// Department search
$depqryadd = '';
$dep = (isset($_GET['department'])) ? intval($_GET['department']) : 0;
if ($dep || isDepartmentAdmin()) {
    $depqryadd = ', user_department';

    $subs = array();
    if ($dep) {
        $subs = $tree->buildSubtrees(array($dep));
        add_param('department', $dep);
    } else if (isDepartmentAdmin())
        $subs = $user->getDepartmentIds($uid);

    $ids = '';
    foreach ($subs as $key => $id) {
        $ids .= $id . ',';
        validateNode($id, isDepartmentAdmin());
    }
    // remove last ',' from $ids
    $deps = substr($ids, 0, -1);

    $pref = ($c) ? 'a' : 'user';
    $criteria[] = $pref . '.id = user_department.user';
    $criteria[] = 'department IN (' . $deps . ')';
}

if (count($criteria)) {
    $qry_criteria = implode(' AND ', $criteria);
} else {
    $qry_criteria = '';
}

// end filter/criteria
if ($c) { // users per course
    $qry_base = "FROM user AS a
                          LEFT JOIN course_user AS b
                               ON a.id = b.user_id
                          $depqryadd
                     WHERE b.course_id = $c";
    if ($qry_criteria) {
        $qry_base .= ' AND ' . $qry_criteria;
    }
    $count_qry = "SELECT COUNT(DISTINCT a.id) AS num, b.status AS user_type " .
            $qry_base;
    $qry = "SELECT DISTINCT a.id, a.surname, a.givenname, a.username, a.email,
                       a.verified_mail, b.status " . $qry_base;
    add_param('c');
} elseif ($search == 'no_login') { // users who have never logged in
    $qry_base = "FROM user
                          LEFT JOIN loginout
                               ON user.id = loginout.id_user
                          $depqryadd
                          WHERE loginout.id_user IS NULL";
    if ($qry_criteria) {
        $qry_base .= ' AND ' . $qry_criteria;
    }
    $count_qry = "SELECT COUNT(DISTINCT id) AS num, status AS user_type " . $qry_base;
    $qry = "SELECT DISTINCT id, surname, givenname, username, email, verified_mail, status " .
            $qry_base;
    add_param('search', 'no_login');
} else {
    // Count users, with or without criteria/filters
    $qry_base = ' FROM user' . $depqryadd;
    if ($qry_criteria) {
        $qry_base .= ' WHERE ' . $qry_criteria;
    }
    $count_qry = 'SELECT COUNT(DISTINCT id) AS num, status AS user_type' .
            $qry_base;
    $qry = 'SELECT DISTINCT id, surname, givenname, username, email, status, verified_mail' .
            $qry_base;
}

// User statistics
$teachers = $students = $visitors = $other = 0;
$sql = Database::get()->queryArray($count_qry . ' GROUP BY user_type');
foreach ($sql as $row) {
    $row = (array) $row;
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

$pagination_link = '&amp;' . implode('&amp;', $params);

if ($search == 'inactive') {  // inactive users
    $caption .= "&nbsp;$langAsInactive<br />";
    $caption .= "<a href='updatetheinactive.php?activate=1'>" . $langAddSixMonths . "</a><br />";
}    
$qry .= " ORDER BY status";
$sql = Database::get()->queryArray($qry);

$tool_content .= "<table id='search_results_table' class='display'>
            <thead>
            <tr>
              <th width='150'>$langSurname</th>
              <th width='100' class='left'>$langName</th>
              <th width='170' class='left'>$langUsername</th>
              <th>$langEmail</th>
              <th>$langProperty</th>
              <th width='130' class='center'>$langActions</th>
            </tr></thead>";
$tool_content .= "<tbody>";

foreach ($sql as $logs) {
    $tool_content .= "<td>" . q($logs->surname) . "</td>
                    <td>" . q($logs->givenname) . "</td>
                    <td>" . q($logs->username) . "</td>
                    <td width='200'>" . q($logs->email);
    if ($mail_ver_required) {
        switch ($logs->verified_mail) {
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
        $tool_content .= ' ' . icon($icon, $tip);
    }

    switch ($logs->status) {
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
                    q($logs->status) . ')</td>';
            break;
    }
    if ($icon) {
        $tool_content .= "</td><td class='center'>" . icon($icon, $tip) . "</td>";
    }
    if ($logs->id == 1) { // don't display actions for admin user
        $tool_content .= "<td class='center'>&mdash;&nbsp;</td>";
    } else {
        $changetip = q("$langChangeUserAs $logs->username");
        $width = (!isDepartmentAdmin()) ? 100 : 80;
        $tool_content .= "<td width='$width'>" .
                icon('edit', $langEdit, "edituser.php?u=$logs->id") . '&nbsp;' .
                icon('delete', $langDelete, "deluser.php?u=$logs->id") . '&nbsp;' .
                icon('platform_stats', $langStat, "userstats.php?u=$logs->id") . '&nbsp;' .
                icon('platform_stats', $langActions, "userlogs.php?u=$logs->id");
        if (!isDepartmentAdmin()) {
            $tool_content .= '&nbsp;' . icon('log_as', $changetip, 'change_user.php?username=' . urlencode($logs->username));
        }
        $tool_content .= "</td>";
    }
    $tool_content .= "</tr>";   
}
$tool_content .= "</tbody></table>";
$caption = "<b>$teachers</b> $langTeachers, <b>$students</b> $langStudents
            $langAnd <b>$visitors</b> $langVisitors<br />";
$tool_content .= "<br><br><div class='info'>$caption</div>";
// delete all function
$tool_content .= " <form action='multideluser.php' method='post' name='delall_user_search'>";
// redirect all request vars towards delete all action
foreach ($_REQUEST as $key => $value) {
    $tool_content .= "<input type='hidden' name='$key' value='$value' />";
}
$tool_content .= "<input type='submit' name='dellall_submit' value='$langDelList'></form>";
$tool_content .= "<p align='right'><a href='search_user.php?$pagination_link'>$langBack</a></p>";

draw($tool_content, 3, null, $head_content);

/**
 * make links from one page to another during search results
 * @global string $params
 * @param type $name
 * @param type $value
 */
function add_param($name, $value = null) {
    global $params;
    if (!isset($value)) {
        $value = $GLOBALS[$name];
    }
    if ($value !== 0 and $value !== '') {
        $params[] = $name . '=' . urlencode($value);
    }
}
