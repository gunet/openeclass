<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */
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
    $search = $_GET['search'] ?? '';
    $c = isset($_GET['c']) ? intval($_GET['c']) : ''; // course id
    $lname = $_GET['lname'] ?? '';
    $fname = $_GET['fname'] ?? '';
    $uname = isset($_GET['uname']) ? canonicalize_whitespace($_GET['uname']) : '';
    $am = $_GET['am'] ?? '';
    $verified_mail = isset($_GET['verified_mail']) ? intval($_GET['verified_mail']) : 3;
    $user_type = isset($_GET['user_type']) ? intval($_GET['user_type']) : '';
    $auth_type = isset($_GET['auth_type']) ? intval($_GET['auth_type']) : '';
    $email = isset($_GET['email']) ? mb_strtolower(trim($_GET['email'])) : '';
    $reg_flag = isset($_GET['reg_flag']) ? intval($_GET['reg_flag']) : '';
    $user_registered_at = $_GET['user_registered_at'] ?? '';
    $user_expires_until = $_GET['user_expires_until'] ?? '';
    $user_last_login = $_GET['user_last_login'] ?? '';
    $mail_ver_required = get_config('email_verification_required');
    // pagination
    $limit = intval($_POST['length']);
    $offset = intval($_POST['start']);

    // 'LIKE' argument prefix/postfix - default is substring search
    $l1 = $l2 = '%';
    $cs = 'COLLATE utf8mb4_general_ci';
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
        $date_user_registered_at = DateTime::createFromFormat("d-m-Y", $user_registered_at);
        $terms[] = $date_user_registered_at->format("Y-m-d");
    }

    // Expiration date search
    if (!empty($user_expires_until)) {
        add_param('user_expires_until');
        // join the above with registered at search
        $criteria[] = 'expires_at > CURRENT_DATE() AND expires_at < ?s';
        $date_user_expires_until = DateTime::createFromFormat("d-m-Y", $user_expires_until);
        $terms[] = $date_user_expires_until->format("Y-m-d");
    }

    // users who haven't logged in
    if (!empty($user_last_login)) {
        add_param('user_last_login');
        $criteria[] = 'registered_at < ?s AND id NOT IN (SELECT id_user FROM loginout WHERE `when` > ?s GROUP BY id_user)';
        $date_last_login = DateTime::createFromFormat("d-m-Y", $user_last_login);
        $terms[] = $date_last_login->format("Y-m-d");
        $terms[] = $date_last_login->format("Y-m-d");
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

    // search for active users
    if ($search == 'active') {
        $criteria[] = 'expires_at > CURRENT_DATE()';
        add_param('search', 'active');
    }

    // search for users with their account being expired in one month
    if ($search == 'wexpire') {
        $criteria[] = 'expires_at BETWEEN CURRENT_DATE() AND date_add(CURRENT_DATE(), INTERVAL 1 MONTH)';
        add_param('search', 'wexpire');
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
        } else if (isDepartmentAdmin()) {
            $subs = $user->getAdminDepartmentIds($uid);
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
    if (!empty($_POST['columns'][0]['search']['value'])) {
        if ($c) { // listing course users, get user's status in this course
            $criteria[] = '(course_user.status = ?d)';
        } else { // listing all users, get user's global status
            $criteria[] = '(user.status = ?d)';
        }
        $terms[] = $_POST['columns'][0]['search']['value'];
    }

    // internal search
    if (!empty($_POST['search']['value'])) {
        $criteria[] = '(surname LIKE ?s OR givenname LIKE ?s OR username LIKE ?s OR email LIKE ?s)';
        $keywords = array_fill(0, 4, $l1 . $_POST['search']['value'] . $l2);
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
    if (!empty($_POST['order'][0]['column'])) {
        switch ($_POST['order'][0]['column']) {
            case '0': $qry .= ' ORDER BY surname ';
                break;
            case '1': $qry .= ' ORDER BY givenname ';
                break;
            case '2': $qry .= ' ORDER BY username ';
                break;
        }
        $qry .= (isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] == 'desc' ? 'DESC' : '');
    } else {
        $qry .= ' ORDER BY status, surname ' .
                (isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] == 'desc' ? 'DESC' : '');
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

    $data['recordsTotal'] = $all_results;
    $data['recordsFiltered'] = $filtered_results;
    $data['aaData'] = array();

    foreach ($sql as $myrow) {
        $inactive_user = is_inactive_user($myrow->id);
        $email_icon = q($myrow->email);
        if ($mail_ver_required) {
            switch ($myrow->verified_mail) {
                case EMAIL_VERIFICATION_REQUIRED:
                    $icon = 'fa-regular fa-clock';
                    $tip = $langMailVerificationPendingU;
                    break;
                case EMAIL_VERIFIED:
                    $icon = 'fa-solid fa-check';
                    $tip = $langMailVerificationYesU;
                    break;
                default:
                    $icon = 'fa-solid fa-exclamation';
                    $tip = $langMailVerificationNoU;
                    break;
            }
            $email_icon .= '&nbsp;&nbsp;' . icon($icon, $tip);
        }


        switch ($myrow->status) {
            case USER_TEACHER:
                $icon = 'fa-university';
                $tip = $langWithRights;
                break;
            case USER_STUDENT:
                $icon = 'fa-graduation-cap';
                $tip = '';
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

        if ($myrow->id == 1) { // don't display actions for admin user
            $icon_content = "&mdash;&nbsp;";
        } else {
            $iuid = getIndirectReference($myrow->id);
            $changetip = q("$langChangeUserAs $myrow->username");

            $user_password = Database::get()->querySingle("SELECT password FROM user WHERE id = ?d", $myrow->id)->password;

            $show = true;
            if (in_array($user_password, $auth_ids)) {
                if (get_config('block_duration_alt_account')) {
                    $show = false;
                }
            } else if (get_config('block_duration_account')) {
                $show = false;
            }
            $profileUrl = $urlAppend .
                "main/profile/display_profile.php?id=$myrow->id&amp;token=" .
                token_generate($myrow->id, true);
            $icon_content = action_button(array(
                array(
                    'title' => $langEditChange,
                    'icon' => 'fa-edit',
                    'url' => "edituser.php?u=$myrow->id"
                ),
                array(
                    'title' => $langDisplayProfile,
                    'icon' => 'fa-user',
                    'url' => $profileUrl
                ),
                array(
                    'title' => $changetip,
                    'icon' => 'fa-key',
                    'url' => 'change_user.php?username=' . urlencode($myrow->username),
                    'class' => 'change-user-link',
                    'hide' => isDepartmentAdmin()
                ),
                array(
                    'title' => $langActions,
                    'icon' => 'fa-list-alt',
                    'url' => "userlogs.php?u=$myrow->id"
                ),
                array(
                    'title' => $langUsage,
                    'icon' => 'fa-pie-chart',
                    'url' => "../usage/index.php?t=u&u=$myrow->id"
                ),
                array(
                    'title' => ($inactive_user)? "$langActivate" : "$langDeactivate",
                    'icon' => ($inactive_user)? "fa-plus-circle" : "fa-minus-circle",
                    'url' => ($inactive_user)? "$_SERVER[SCRIPT_NAME]?&u=$myrow->id&amp;action=activate&amp;" . generate_csrf_token_link_parameter() : "$_SERVER[SCRIPT_NAME]?&u=$myrow->id&amp;action=deactivate&amp;" . generate_csrf_token_link_parameter(),
                    'show' => $show
                ),
                array(
                    'title' => $langDelete,
                    'icon' => 'fa-times',
                    'url' => "deluser.php?u=$myrow->id"
                )
            ));
        }
        $data['aaData'][] = array(
            '0' => '<div class="text-nowrap">' . q(sanitize_utf8($myrow->surname)) . '</div>',
            '1' => '<div class="text-nowrap">' . q(sanitize_utf8($myrow->givenname)) . '</div>',
            '2' => '<div class="text-nowrap">' . q(sanitize_utf8($myrow->username)) . '</div>',
            '3' => '<div class="text-nowrap">' . $email_icon . '</div>',
            '4' => icon($icon, $tip),
            '5' => $icon_content,
            '6' => $inactive_user
        );
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// activate - deactivate user
if (isset($_GET['action']) and isset($_GET['u'])) {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    switch ($_GET['action']) {
        case 'deactivate':
                Database::get()->query("UPDATE user SET expires_at = " .DBHelper::timeAfter() . " WHERE id = ?d" , $_GET['u']);
                Session::flash('message', sprintf($langUserDeactivated, uid_to_name($_GET['u'])));
                Session::flash('alert-class', 'alert-info');
            break;
        case 'activate':
                $expires_at = new DateTime(date('Y-m-d H:i', strtotime("now") + get_config('account_duration')));
                Database::get()->query("UPDATE user SET expires_at = ?t WHERE id = ?d" , $expires_at->format("Y-m-d H:i"), $_GET['u']);
                Session::flash('message', sprintf($langUserActivated, uid_to_name($_GET['u'])));
                Session::flash('alert-class', 'alert-info');
            break;
        default: break;
    }
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
                var column = api.column(0);
                var select = $('<select id=\'select_role\' aria-label=\'" . js_escape($langAll) . "\'>'+
                                 '<option value=\'0\'>-- " . js_escape($langAll) . " --</option>'+
                                 '<option value=\'".USER_TEACHER."\'>" . js_escape($langUsersWithRightsS) . "</option>'+
                                 '<option value=\'".USER_STUDENT."\'>" . js_escape($langUsersWithNoRightsS) . "</option>'+
                                 '<option value=\'".USER_GUEST."\'>" . js_escape($langGuests) . "</option>'+
                               '</select>')
                             .appendTo( $(column.footer()).empty() );
            },
            'fnDrawCallback': function( oSettings ) {
                popover_init();
            },
            'createdRow': function(row, data, dataIndex) {
                if (data[6] == 1) {
                    $(row). addClass('not_visible');
                }
            },
            'bProcessing': true,
            'bServerSide': true,
            'searchDelay': 1000,
            ajax: {
                url: '$_SERVER[REQUEST_URI]',
                type: 'POST'
            },            
            lengthMenu: [10, 15, 20 , -1],            
            'sPaginationType': 'full_numbers',
            'bAutoWidth': false,
            'aoColumns': [
                {'bSortable' : true },
                {'bSortable' : true },
                {'bSortable' : true },
                {'bSortable' : false },
                {'bSortable' : false, 'sClass': 'text-center' },
                {'bSortable' : false, 'sClass': 'text-end' },
            ],
            'oLanguage': {
                lengthLabels: {
                    '-1': '" . js_escape($langAllOfThem) . "'
                },
               'sLengthMenu':   '" . js_escape("$langDisplay _MENU_ $langResults2") . "',
               'sZeroRecords':  '" . js_escape($langNoResult) . "',
               'sEmptyTable':  '" . js_escape($langNoResult) . "',
               'sInfo':         '" . js_escape("$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults") . "',
               'sInfoEmpty':    '',
               'sInfoFiltered': '',
               'sInfoPostFix':  '',
               'sSearch':       '" . js_escape($langSearch) . "',               
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
        $('.dt-search input')
            .attr({
                    'style': 'width: 250px',
                    'placeholder': '$langName, $langSurname, Username, $langEmail'
                 });
    });
    </script>";

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'search_user.php', 'name' => $langSearchUser);

$toolName = $langAdmin;
$pageName = $langListUsersActions;

// Display Actions Toolbar
$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "search_user.php",
        'icon' => 'fa-reply',
        'level' => 'primary'),
    array('title' => $langAllUsers,
        'url' => "$_SERVER[SCRIPT_NAME]",
        'icon' => 'fa-solid fa-users',
        'level' => 'primary-label'),
    array('title' => $langActiveUsers,
        'url' => "$_SERVER[SCRIPT_NAME]?search=active",
        'icon' => 'fa-solid fa-user-check',
        'level' => 'primary-label',
        'show' => !(isset($_GET['search']) and $_GET['search'] == 'active')),
    array('title' => $langInactiveUsers,
        'url' => "$_SERVER[SCRIPT_NAME]?search=inactive",
        'icon' => 'fa-solid fa-user-xmark',
        'level' => 'primary-label',
        'show' => !(isset($_GET['search']) and $_GET['search'] == 'inactive'))
    ));

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
