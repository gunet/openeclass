<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

$require_usermanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$data['tree'] = $tree = new Hierarchy();
$user = new User();

$close = isset($_GET['close']) ? $_GET['close'] : (isset($_POST['close']) ? $_POST['close'] : '');
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : '');
$show = isset($_GET['show']) ? $_GET['show'] : (isset($_POST['show']) ? $_POST['show'] : '');

$columns = 'null, null, null, null, { orderable: false }';

// Deal with navigation
switch ($show) {
    case "closed":
        $toolName = $langReqHaveClosed;
        $pagination_link = '&amp;show=closed';
        $columns = 'null, null, null, null, null, { orderable: false }';
        break;
    case "rejected":
        $toolName = $langReqHaveBlocked;
        $columns = 'null, null, null, null, null, { orderable: false }';
        break;
}

load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#requests_table').DataTable ({
                'columns': [ $columns ],
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[4, 'desc']],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
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
            $('.dataTables_filter input ms-0 mb-3').attr('placeholder', '$langName, $langSurname, $langUsername');
        });
        </script>";

$basetoolurl = $_SERVER['SCRIPT_NAME'];
if (isset($_GET['type']) and $_GET['type'] == 'user') {
    $list_status = 5;
    $toolName = $langUserOpenRequests;
    $data['reqtype'] = $reqtype = '&amp;type=user';
    $basetoolurl .= '?type=user';
    $linkreg = $langUserDetails;
    $linkget = '?type=user';
} else {
    $list_status = 1;
    $toolName = $langOpenProfessorRequests;
    $data['reqtype'] = $reqtype = '';
    $linkreg = $langProfReg;
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
            array('title' => $linkreg,
                'url' => "newuseradmin.php$linkget",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langReqHaveClosed,
                'url' => "$_SERVER[SCRIPT_NAME]?show=closed$reqtype",
                'icon' => 'fa-close',
                'level' => 'primary'),
            array('title' => $langReqHaveBlocked,
                'url' => "$_SERVER[SCRIPT_NAME]?show=rejected$reqtype",
                'icon' => 'fa-ban',
                'level' => 'primary'),
            array('title' => $langBack,
                'url' => "$basetoolurl",
                'icon' => 'fa-reply',
                'level' => 'primary')
                ));

// -----------------------------------
// display closed requests
// ----------------------------------
if (!empty($show) and $show == 'closed') {
    if (!empty($id) and $id > 0) {
        // restore request
        Database::get()->query("UPDATE user_request set state = 1, date_closed = NULL WHERE id = ?d", $id);
        //Session::Messages($langReintroductionApplication, 'alert-success');
        Session::flash('message',$langReintroductionApplication);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/admin/listreq.php');
    } else {
        $count_req = count(Database::get()->queryArray("SELECT * FROM user_request WHERE (state = 2 AND status = ?d)", $list_status));

        $q = "SELECT id, givenname, surname, username, email, faculty_id,
                             phone, am, date_open, date_closed, comment
                          FROM user_request
                          WHERE (state = 2 AND status = $list_status) ORDER BY date_closed DESC";

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
        Session::flash('message',$langReintroductionApplication);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/admin/listreq.php');
    } else {
        $data['user_requests'] = Database::get()->queryArray("SELECT id, givenname, surname, username, email,
                                        faculty_id, phone, am, date_open, date_closed, comment
                                        FROM user_request
                                        WHERE (state = 3 AND status = $list_status $depqryadd) ORDER BY date_closed DESC");

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
                Session::flash('message',$langProfessorRequestClosed);
                Session::flash('alert-class', 'alert-info');
                redirect_to_home_page('modules/admin/listreq.php');
            } else {
                Session::flash('message',$langRequestStudent);
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
                        //$message .= "<p><b class='pe-3'>$langComments:</b>" . q($_POST['comment']) . "</p>";
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
    $data['user_requests'] = Database::get()->queryArray("SELECT id, givenname, surname, username, faculty_id, date_open, comment, password FROM user_request
                                WHERE (state = 1 AND status = $list_status $depqryadd) ORDER BY date_open DESC");
    $view = 'admin.users.listreq.index';

}

$data['menuTypeID'] = 3;
view($view, $data);

/**
 * @brief function to display table header
 * @return string
 */
function table_header($addon = FALSE) {

    global $langSurnameName, $langFaculty, $langUsername, $langDateRequest, $langDateClosed, $langDateReject;

    $string = "<thead>";
    $datestring = '';
    if ($addon == 1) {
        $datestring = "<th class='text-center'>$langDateClosed</th>";
    } else if ($addon == 2) {
        $datestring = "<th class='text-center'>$langDateReject</th>";
    }

    $string .= "<tr class='list-header'>
                    <th scope='col'><div class='text-center'>$langSurnameName</div></th>
                    <th scope='col'><div class='text-center'>$langUsername</div></th>
                    <th scope='col'><div class='text-center'>$langFaculty</div></th>
                    <th class='text-center'>$langDateRequest</th>";
    $string .= $datestring;
    $string .= "<th scope='col'>" . icon('fa-gears') . "</th>";
    $string .= "</tr></thead>";

    return $string;
}
