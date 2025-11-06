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

$require_usermanage_user = true;
$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'teachers_request';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$data['tree'] = $tree = new Hierarchy();
$user = new User();

$close = $_GET['close'] ?? (isset($_POST['close']) ? $_POST['close'] : '');
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : '');
$show = $_GET['show'] ?? (isset($_POST['show']) ? $_POST['show'] : '');

$toolName = $langAdmin;
// Deal with navigation
switch ($show) {
    case "closed":
        $pageName = $langReqHaveClosed;
        $pagination_link = '&amp;show=closed';
        $columns = 'null, null, null, null, null, null, { orderable: false }';
        $order = "[5, 'desc']";
        break;
    case "rejected":
        $pageName = $langReqHaveBlocked;
        $columns = 'null, null, null, null, null, null, { orderable: false }';
        $order = "[5, 'desc']";
        break;
    default:
        $pageName = $langUserOpenRequests;
        $columns = 'null, null, null, null, null, { orderable: false }';
        $order = "[4, 'desc']";
        break;
}

load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#requests_table').DataTable ({
                   'columns': [ $columns ],
                   'lengthMenu': [10, 15, 20, -1],
                   'sPaginationType': 'full_numbers',
                   'bAutoWidth': true,
                   'searchDelay': 1000,
                   'order' : [ $order ],
                   'oLanguage': {
                   'lengthLabels': {
                       '-1': '$langAllOfThem'
                    },                   
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '" . $langSearch . "',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dt-search input ms-0 mb-3').attr('placeholder', '$langName, $langSurname, $langUsername');
        });
        </script>";

$basetoolurl = $_SERVER['SCRIPT_NAME'];
if (isset($_GET['type']) and $_GET['type'] == 'user') {
    $data['reqtype'] = $reqtype = '&amp;type=user';
    $basetoolurl .= '?type=user';
    $linkget = '?type=user';
} else {
    $data['reqtype'] = $reqtype = '';
    $linkget = '';
}
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

// id validation
if ($id > 0) {
    $req = Database::get()->querySingle("SELECT faculty_id FROM user_request WHERE id = ?d", $id);
    if ($req->faculty_id > 0) {
        validateNode($req->faculty_id, isDepartmentAdmin());
    }
}

// department admin additional query where clause
$depqryadd = '';
if (isDepartmentAdmin()) {
    $subtrees = $tree->buildSubtrees($user->getAdminDepartmentIds($uid));
    $depqryadd = ' AND faculty_id IN (' . implode(', ', $subtrees) . ')';
}

// Display Actions Toolbar
$data['action_bar'] =
        action_bar(array(
            array('title' => $langUserOpenRequests,
                'url' => "$_SERVER[SCRIPT_NAME]",
                'icon' => 'fa-solid fa-hand',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langReqHaveClosed,
                'url' => "$_SERVER[SCRIPT_NAME]?show=closed$reqtype",
                'icon' => 'fa-close',
                'level' => 'primary-label'),
            array('title' => $langReqHaveBlocked,
                'url' => "$_SERVER[SCRIPT_NAME]?show=rejected$reqtype",
                'icon' => 'fa-ban',
                'level' => 'primary-label')
        ));

// -----------------------------------
// display closed requests
// ----------------------------------
if (!empty($show) and $show == 'closed') {
    if (!empty($id) and $id > 0) {
        // restore request
        Database::get()->query("UPDATE user_request set state = 1, date_closed = NULL WHERE id = ?d", $id);
        Session::flash('message', $langReintroductionApplication);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/admin/listreq.php');
    } else {
        $q = "SELECT id, givenname, surname, username, email, faculty_id,
                             phone, am, date_open, date_closed, comment, status
                          FROM user_request
                          WHERE state = 2 ORDER BY date_closed DESC";


        $data['user_requests'] = Database::get()->queryArray($q);
        $view = 'admin.users.listreq.closedRequests';
    }

// -----------------------------------
// display rejected requests
// ----------------------------------
} elseif (!empty($show) && ($show == 'rejected')) {
    if (!empty($id) && ($id > 0)) {
        // restore request
        Database::get()->query("UPDATE user_request set state = 1, date_closed = NULL WHERE id = ?d", $id);
        Session::flash('message', $langReintroductionApplication);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/admin/listreq.php');
    } else {
        $data['user_requests'] = Database::get()->queryArray("SELECT id, givenname, surname, username, email,
                                        faculty_id, phone, am, date_open, date_closed, comment, status
                                        FROM user_request
                                        WHERE (state = 3 $depqryadd) ORDER BY date_closed DESC");
        $view = 'admin.users.listreq.rejectedRequests';
    }

// ------------------------------
// close request
// ------------------------------
} elseif (!empty($close)) {
    switch ($close) {
        case '1':
            Database::get()->query("UPDATE user_request
                                       SET state = 2,
                                           date_closed = " . DBHelper::timeAfter() . "
                                       WHERE id = ?d", $id);

            if ($list_status == 1) {
                Session::flash('message', $langProfessorRequestClosed);
                Session::flash('alert-class', 'alert-info');
                redirect_to_home_page('modules/admin/listreq.php');
            } else {
                Session::flash('message', $langRequestStudent);
                Session::flash('alert-class', 'alert-info');
                redirect_to_home_page('modules/admin/listreq.php');
            }
            break;
        case '2':
            $submit = isset($_POST['submit']) ? $_POST['submit'] : '';
            if (!empty($submit)) {
                if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
                // post the comment and do the delete action
                if (!empty($_POST['comment'])) {
                    $sql = "UPDATE user_request
                               SET state = 3,
                                   date_closed = " . DBHelper::timeAfter() . ",
                                   comment = ?s
                               WHERE id = ?d";
                    if (Database::get()->query($sql, $_POST['comment'], $id)->affectedRows > 0) {
                        if (isset($_POST['sendmail']) and ( $_POST['sendmail'] == 1)) {
                            $telephone = get_config('phone');
                            $administratorName = get_config('admin_name');
                            $emailhelpdesk = get_config('email_helpdesk');
                            $emailsubject = $langemailsubjectBlocked;

                            $emailHeader = "
                                <!-- Header Section -->
                                <div id='mail-header'>
                                    <div>
                                        <br>
                                        <div id='header-title'>$langemailbodyBlocked</div>
                                    </div>
                                </div>";

                            $emailMain = "
                            <!-- Body Section -->
                            <div id='mail-body'>
                                <br>
                                <div id='mail-body-inner'>
                                    ".q($_POST['comment'])."<br><br>
                                    <ul id='forum-category'>
                                        <li><span><b>$langManager $siteName:</b></span> <span class='left-space'>$administratorName</span></li>
                                        <li><span><b>$langPhone:</b></span> <span class='left-space'>$telephone</span></li>
                                        <li><span><b>$langEmail:</b></span> <span class='left-space'>$emailhelpdesk</span></li>
                                    </ul>
                                </div>
                            </div>";

                            $emailbody = $emailHeader.$emailMain;

                            $emailPlainBody = html2text($emailbody);

                            send_mail_multipart('', '', "$_POST[prof_givenname] $_POST[prof_surname]", $_POST['prof_email'], $emailsubject, $emailPlainBody, $emailbody);

                        }
                        $message = $list_status == 1 ? $langTeacherRequestHasRejected : $langRequestReject;
                        $message .= " $langRequestMessageHasSent <b>" . q($_POST['prof_email']) . "</b>";
                        Session::flash('message',$message);
                        Session::flash('alert-class', 'alert-success');
                        redirect_to_home_page('modules/admin/listreq.php');

                    }
                }
            } else {
                // display the form
                $data['user_request'] = $d = Database::get()->querySingle("SELECT comment, givenname, surname, email, status FROM user_request WHERE id = ?d", $id);
                $data['id'] = intval($id);
                $data['warning'] = ($d->status == 5) ? $langWarnReject : $langGoingRejectRequest;
                $view = 'admin.users.listreq.rejectForm';
            }
            break;
        default:
            break;
    } // end of switch
}

// -----------------------------------
// display all requests
// -----------------------------------
else {
    $data['user_requests'] = Database::get()->queryArray("SELECT id, givenname, surname, username, faculty_id, date_open, comment, password, status FROM user_request
                                              WHERE (state = 1 $depqryadd) ORDER BY date_open DESC");
    $view = 'admin.users.listreq.index';
}

view($view, $data);

/**
 * @brief function to display table header
 * @return string
 */
function table_header($addon = FALSE) {

    global $langSurnameName, $langFaculty, $langUsername, $langDateRequest,
           $langDateClosed, $langDateReject, $langUserPermissions, $langSettingSelect;

    $string = "<thead>";
    $datestring = '';
    if ($addon == 1) {
        $datestring = "<th>$langDateClosed</th>";
    } else if ($addon == 2) {
        $datestring = "<th>$langDateReject</th>";
    }
    $string .= "<tr class='list-header'>
                <th scope='col'><div>$langSurnameName</div></th>
                <th scope='col'><div>$langUsername</div></th>
                <th scope='col'><div>$langFaculty</div></th>
                <th scope='col'><div>$langUserPermissions</div></th>
                <th>$langDateRequest</th>";

    $string .= $datestring;
    $string .= "<th scope='col' aria-label='$langSettingSelect'>" . icon('fa-gears') . "</th>";
    $string .= "</tr></thead>";

    return $string;
}
