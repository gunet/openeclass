<?php

/* ========================================================================
 *   Open eClass 3.6
 *   E-learning and Course Management System
 * ========================================================================
 *  Copyright(c) 2003-2017  Greek Universities Network - GUnet
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

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $tree = new Hierarchy();
    $user = new User();
    // get the incoming values
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $c = isset($_GET['c']) ? intval($_GET['c']) : ''; // course id
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
    // pagination
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);

    // 'LIKE' argument prefix/postfix - default is substring search
    $l1 = $l2 = '%';
    $cs = 'COLLATE utf8_general_ci';
    if (isset($_GET['search_type'])) {
        if ($_GET['search_type'] == 'exact') {
            $l1 = $l2 = $cs = '';
        } elseif ($_GET['search_type'] == 'begin') {
            $l1 = '';
        }
    }

    /*
      Criteria/Filters
     */
    $criteria = array();
    $terms = array();
    $params = array();
    // Registration date/time search
    if (!empty($user_registered_at)) {
        add_param('reg_flag');
        add_param('user_registered_at');
        // join the above with registered at search
        $criteria[] = 'registered_at ' . (($reg_flag === 1) ? '>=' : '<=') . ' ?s';
        $date_user_registered_at = DateTime::createFromFormat("d-m-Y H:i", $user_registered_at);
        $terms[] = $date_user_registered_at->format("Y-m-d H:i:s");
    }

    // surname search
    if (!empty($lname)) {
        $criteria[] = 'surname LIKE ?s ' . $cs;
        $terms[] = $l1 . $lname . $l2;
        add_param('lname');
    }

    // first name search
    if (!empty($fname)) {
        $criteria[] = 'givenname LIKE ?s '. $cs;
        $terms[] = $l1 . $fname . $l2;
        add_param('fname');
    }

    // username search
    if (!empty($uname)) {
        $criteria[] = 'username LIKE ?s ' . $cs;
        $terms[] = $l1 . $uname . $l2;
        add_param('uname');
    }

    // mail verified
    if ($verified_mail === EMAIL_VERIFICATION_REQUIRED or
            $verified_mail === EMAIL_VERIFIED or
            $verified_mail === EMAIL_UNVERIFIED) {
        $criteria[] = 'verified_mail = ?d';
        $terms[] = $verified_mail;
        add_param('verified_mail');
    }

    //user am search
    if (!empty($am)) {
        $criteria[] = 'am LIKE ?s';
        $terms[] = $l1 . $am . $l2;
        add_param('am');
    }

    // user type search
    if (!empty($user_type)) {
        $criteria[] = 'status = ?d';
        $terms[] = $user_type;
        add_param('user_type');
    }

    // email search
    if (!empty($email)) {
        $criteria[] = 'email LIKE ?s';
        $terms[] = $l1 . $email . $l2;
        add_param('email');
    }

    // search for inactive users
    if ($search == 'inactive') {
        $criteria[] = 'expires_at < CURRENT_DATE()';
        add_param('search', 'inactive');
    }
    
    // search for users with their account being expired in one month
    if ($search == 'wexpire') {
        $criteria[] = 'expires_at between CURRENT_DATE() and date_add(CURRENT_DATE(), INTERVAL 1 MONTH)';
        add_param('search', 'wexpire');
    }

    // Department search
    $depqryadd = '';
    $dep = (isset($_GET['department'])) ? intval(getDirectReference($_GET['department'])) : 0;
    if ($dep || isDepartmentAdmin()) {
        $depqryadd = ', user_department';

        $subs = array();
        if ($dep) {
            $subs = $tree->buildSubtrees(array($dep));
            add_param('department', $dep);
        } else if (isDepartmentAdmin()) {
            $subs = $user->getDepartmentIds($uid);
        }

        $ids = '';
        foreach ($subs as $key => $id) {
            $ids .= $id . ',';
            validateNode($id, isDepartmentAdmin());
        }
        // remove last ',' from $ids
        $deps = substr($ids, 0, -1);

        $criteria[] = 'user.id = user_department.user';
        $criteria[] = 'department IN (' . $deps . ')';
    }

    // auth type search
    if (!empty($auth_type)) {
        if ($auth_type >= 2 && $auth_type < 8) {
            $criteria[] = "password = '{$auth_ids[$auth_type]}'";
        } elseif ($auth_type == 1) {
            $q1 = "'". implode("','", $auth_ids) . "'";
            $criteria[] = 'password NOT IN ('.$q1.')';
        } else {
            $depqryadd .= ', user_ext_uid';
            // ext auth uid's from user_ext_uid table
            $criteria[] = '(user_ext_uid.user_id = user.id AND user_ext_uid.auth_id = ?d)';
            $terms[] = $auth_type;
        }
        add_param('auth_type');
    }

    // user status
    if (!empty($_GET['sSearch_4'])) {
        if ($c) { // listing course users, get user's status in this course
            $criteria[] = '(course_user.status = ?d)';
        } else { // listing all users, get user's global status
            $criteria[] = '(user.status = ?d)';
        }
        $terms[] = $_GET['sSearch_4'];
    }

    // internal search
    if (!empty($_GET['sSearch'])) {
        $criteria[] = '(surname LIKE ?s OR givenname LIKE ?s OR username LIKE ?s OR email LIKE ?s)';
        $keywords = array_fill(0, 4, $l1 . $_GET['sSearch'] . $l2);
        $terms = array_merge($terms, $keywords);
    } else {
        $keywords = array_fill(0, 4, '%');
    }

    if (count($criteria)) {
        $qry_criteria = implode(' AND ', $criteria);
    } else {
        $qry_criteria = '';
    }

    // end filter/criteria
    if ($c) { // users per course
        $qry_base = "FROM user LEFT JOIN course_user ON user.id = course_user.user_id
                              $depqryadd WHERE course_user.course_id = ?d";
        if ($qry_criteria) {
            $qry_base .= ' AND ' . $qry_criteria;
        }
        $qry = "SELECT DISTINCT user.id, user.surname, user.givenname, user.username, user.email,
                           user.verified_mail, course_user.status " . $qry_base;
        add_param('c');
        array_unshift($terms, $c);
    } elseif ($search == 'no_login') { // users who have never logged in
        $qry_base = "FROM user LEFT JOIN loginout ON user.id = loginout.id_user $depqryadd
                              WHERE loginout.id_user IS NULL";
        if ($qry_criteria) {
            $qry_base .= ' AND ' . $qry_criteria;
        }
        $qry = "SELECT DISTINCT id, surname, givenname, username, email, verified_mail, status " .
                $qry_base;
        add_param('search', 'no_login');
    } else {
        $qry_base = ' FROM user' . $depqryadd;
        if ($qry_criteria) {
            $qry_base .= ' WHERE ' . $qry_criteria;
        }
        $qry = 'SELECT DISTINCT user.id, surname, givenname, username, email, status, verified_mail' .
                $qry_base;
    }
    $terms_base[] = $terms;

    // sorting
    if (!empty($_GET['iSortCol_0'])) {
        switch ($_GET['iSortCol_0']) {
            case '0': $qry .= ' ORDER BY surname ';
                break;
            case '1': $qry .= ' ORDER BY givenname ';
                break;
            case '2': $qry .= ' ORDER BY username ';
                break;
        }
        $qry .= ($_GET['sSortDir_0'] == 'desc' ? 'DESC' : '');
    } else {
        $qry .= ' ORDER BY status, surname ' .
                ($_GET['sSortDir_0'] == 'desc' ? 'DESC' : '');
    }
    //pagination
    if ($limit > 0) {
        $qry .= " LIMIT ?d, ?d";
        $terms[] = $offset;
        $terms[] = $limit;
    }
    $sql = Database::get()->queryArray($qry, $terms);
    $all_results = Database::get()->querySingle("SELECT COUNT(*) AS total $qry_base", $terms_base)->total;
    if ($qry_criteria or $c) {
        $filtered_results = Database::get()->querySingle("SELECT COUNT(*) AS total $qry_base
                                                         AND (surname LIKE ?s
                                                             OR givenname LIKE ?s
                                                             OR username LIKE ?s
                                                             OR email LIKE ?s)", $terms_base, $keywords)->total;
    } else {
        $filtered_results = Database::get()->querySingle("SELECT COUNT(*) AS total FROM user
                                                         WHERE (surname LIKE ?s
                                                                OR givenname LIKE ?s
                                                                OR username LIKE ?s
                                                                OR email LIKE ?s)", $keywords)->total;
    }
    $data['iTotalRecords'] = $all_results;
    $data['iTotalDisplayRecords'] = $filtered_results;
    $data['aaData'] = array();

    foreach ($sql as $logs) {
        $email_icon = $logs->email;
        if ($mail_ver_required) {
            switch ($logs->verified_mail) {
                case EMAIL_VERIFICATION_REQUIRED:
                    $icon = 'fa-clock-o';
                    $tip = $langMailVerificationPendingU;
                    break;
                case EMAIL_VERIFIED:
                    $icon = 'fa-check-square-o';
                    $tip = $langMailVerificationYesU;
                    break;
                default:
                    $icon = 'fa-circle';
                    $tip = $langMailVerificationNoU;
                    break;
            }
            $email_icon .= ' ' . icon($icon, $tip);
        }


        switch ($logs->status) {
            case USER_TEACHER:
                $icon = 'fa-university';
                $tip = $langTeacher;
                break;
            case USER_STUDENT:
                $icon = 'fa-graduation-cap';
                $tip = $langStudent;
                break;
            case USER_GUEST:
                $icon = 'fa-male';
                $tip = $langVisitor;
                break;
            default:
                $icon = false;
                $tip = $langOther;
                break;
        }

        if ($logs->id == 1) { // don't display actions for admin user
            $icon_content = "&mdash;&nbsp;";
        } else {
            $iuid = getIndirectReference($logs->id);
            $changetip = q("$langChangeUserAs $logs->username");
            $icon_content = action_button(array(
                array(
                    'title' => $langEditChange,
                    'icon' => 'fa-edit',
                    'url' => "edituser.php?u=$logs->id"
                ),
                array(
                    'title' => $langActions,
                    'icon' => 'fa-list-alt',
                    'url' => "userlogs.php?u=$logs->id"
                ),
                array(
                    'title' => $changetip,
                    'icon' => 'fa-key',
                    'url' => 'change_user.php?username=' . urlencode($logs->username),
                    'class' => 'change-user-link',
                    'hide' => isDepartmentAdmin()
                ),
                array(
                    'title' => $langUsage,
                    'icon' => 'fa-pie-chart',
                    'url' => "../usage/?t=u&u=$logs->id"
                ),
                array(
                    'title' => $langDelete,
                    'icon' => 'fa-times',
                    'url' => "deluser.php?u=$logs->id"
                )
            ));
        }
        $data['aaData'][] = array(
            '0' => sanitize_utf8($logs->surname),
            '1' => sanitize_utf8($logs->givenname),
            '2' => sanitize_utf8($logs->username),
            '3' => $email_icon,
            '4' => icon($icon, $tip),
            '5' => $icon_content
        );
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

load_js('tools.js');
load_js('datatables');
$head_content .= "<script>
    var csrf_token = '$_SESSION[csrf_token]';
    $(document).ready(function() {
        $(document).on('click', '.change-user-link', function (e) {
            e.preventDefault();
            $('<form>', {
                'action': $(this).attr('href'),
                'method': 'post'
            }).append($('<input>', {
                'type': 'hidden',
                'name': 'token',
                'value': csrf_token
            })).appendTo(document.body).submit();
        });
        var table = $('#search_results_table').DataTable ({
            initComplete: function () {
                var api = this.api();
                var column = api.column(4);
                var select = $('<select id=\'select_role\'>'+
                                 '<option value=\'0\'>-- " . js_escape($langAll) . " --</option>'+
                                 '<option value=\'".USER_TEACHER."\'>" . js_escape($langTeacher) . "</option>'+
                                 '<option value=\'".USER_STUDENT."\'>" . js_escape($langStudent) . "</option>'+
                                 '<option value=\'".USER_GUEST."\'>" . js_escape($langVisitor) . "</option>'+
                               '</select>')
                             .appendTo( $(column.footer()).empty() );
            },
            'fnDrawCallback': function( oSettings ) {
                popover_init();
            },
            'bProcessing': true,
            'bServerSide': true,
            'searchDelay': 1000,
            'sAjaxSource': '$_SERVER[REQUEST_URI]',
            'aLengthMenu': [
               [10, 15, 20 , -1],
               [10, 15, 20, '" . js_escape($langAllOfThem) . "'] // change per page values here
            ],
            'sPaginationType': 'full_numbers',
            'bAutoWidth': false,
            'aoColumns': [
                {'bSortable' : true, 'sWidth': '20%' },
                {'bSortable' : true, 'sWidth': '20%' },
                {'bSortable' : true, 'sWidth': '20%' },
                {'bSortable' : false, 'sWidth': '20%' },
                {'bSortable' : false, 'sClass': 'text-center' },
                {'bSortable' : false, 'sClass': 'text-center' },
            ],
            'oLanguage': {
               'sLengthMenu':   '" . js_escape("$langDisplay _MENU_ $langResults2") . "',
               'sZeroRecords':  '" . js_escape($langNoResult) . "',
               'sInfo':         '" . js_escape("$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults") . "',
               'sInfoEmpty':    '" . js_escape("$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2") . "',
               'sInfoFiltered': '',
               'sInfoPostFix':  '',
               'sSearch':       '" . js_escape($langSearch) . "',
               'sUrl':          '',
               'oPaginate': {
                   'sFirst':    '&laquo;',
                   'sPrevious': '&lsaquo;',
                   'sNext':     '&rsaquo;',
                   'sLast':     '&raquo;'
               }
            }
        });
        // Apply the filter
        $(document).on('change', '#search_results_table tfoot select#select_role', function (e) {
            table
                .column( $(this).parent().index()+':visible' )
                .search($('select#select_role').val())
                .draw();
        });
        $('.dataTables_filter input').attr('placeholder', '$langName, $langSurname, $langUsername');
    });
    </script>";

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'search_user.php', 'name' => $langSearchUser);
$toolName = $langListUsersActions;

// Display Actions Toolbar
$data['action_bar'] = action_bar(array(
            array('title' => $langAllUsers,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-search',
                'level' => 'primary-label'),
            array('title' => $langInactiveUsers,
                'url' => "$_SERVER[SCRIPT_NAME]?search=inactive",
                'icon' => 'fa-search',
                'level' => 'primary-label',
                'show' => !(isset($_GET['search']) and $_GET['search'] == 'inactive')),            
            array('title' => $langBack,
                'url' => "search_user.php",
                'icon' => 'fa-reply',
                'level' => 'primary')
                ));

$data['menuTypeID'] = 3;
// display search results
view('admin.users.list_users', $data);

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
