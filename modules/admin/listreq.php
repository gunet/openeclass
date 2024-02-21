<?php

/* ========================================================================
 * Open eClass 3.15
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2024  Greek Universities Network - GUnet
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

$tree = new Hierarchy();
$user = new User();

$close = isset($_GET['close']) ? $_GET['close'] : (isset($_POST['close']) ? $_POST['close'] : '');
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : '');
$show = isset($_GET['show']) ? $_GET['show'] : (isset($_POST['show']) ? $_POST['show'] : '');

// Deal with navigation
switch ($show) {
    case "closed":
        $toolName = $langReqHaveClosed;
        $pagination_link = '&amp;show=closed';
        $columns = 'null, null, null, null, null, null, { orderable: false }';
        break;
    case "rejected":
        $toolName = $langReqHaveBlocked;
        $columns = 'null, null, null, null, null, null, { orderable: false }';
        break;
    default:
        $toolName = $langUserOpenRequests;
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
            $('.dataTables_filter input').attr('placeholder', '$langName, $langSurname, $langUsername');
        });
        </script>";

$basetoolurl = $_SERVER['SCRIPT_NAME'];
if (isset($_GET['type']) and $_GET['type'] == 'user') {

    $reqtype = '&amp;type=user';
    $basetoolurl .= '?type=user';
    $linkreg = $langUserDetails;
    $linkget = '?type=user';
} else {
    $reqtype = '';
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
$tool_content .= "
      <div id='operations_container'>" .
        action_bar(array(
            array('title' => $langReqHaveClosed,
                'url' => "$_SERVER[SCRIPT_NAME]?show=closed$reqtype",
                'icon' => 'fa-close',
                'level' => 'primary-label'),
            array('title' => $langReqHaveBlocked,
                'url' => "$_SERVER[SCRIPT_NAME]?show=rejected$reqtype",
                'icon' => 'fa-ban',
                'level' => 'primary-label'),
            array('title' => $langBack,
                'url' => "$basetoolurl",
                'icon' => 'fa-reply',
                'level' => 'primary')
                )) .
        "</div>";

// -----------------------------------
// display closed requests
// ----------------------------------
if (!empty($show) and $show == 'closed') {
    if (!empty($id) and $id > 0) {
        // restore request
        Database::get()->query("UPDATE user_request set state = 1, date_closed = NULL WHERE id = ?d", $id);
        $tool_content .= "<div class='alert alert-success'>$langReintroductionApplication</div>";
    } else {
        $q = "SELECT id, givenname, surname, username, email, faculty_id,
                             phone, am, date_open, date_closed, comment, status
                          FROM user_request
                          WHERE state = 2 ORDER BY date_closed DESC";

        $sql = Database::get()->queryArray($q);
        $tool_content .= "<div class='table-responsive'><table id = 'requests_table' class='table-default'>";
        $tool_content .= table_header(1);
        foreach ($sql as $req) {
            $sort_date_open = date("Y-m-d H:i", strtotime($req->date_open));
            $sort_date_closed = date("Y-m-d H:i", strtotime($req->date_closed));
            $tool_content .= "<tr>";
            $tool_content .= '<td>' . q($req->givenname) . "&nbsp;" . q($req->surname) . "";
            $tool_content .= '<td>' . q($req->username) . '</td>';
            $tool_content .= '<td>' . $tree->getFullPath($req->faculty_id) . '</td>';
            if ($req->status == USER_TEACHER) {
                $legend = $langCourseCreate;
            } else {
                $legend = "&mdash;";
            }
            $tool_content .= "<td>" . $legend . "</td>";
            $tool_content .= "<td class='text-center' data-sort='$sort_date_open'>
				<small>" . format_locale_date(strtotime($req->date_open), 'short', false) . "</small></td>";
            $tool_content .= "<td class='text-center' data-sort='$sort_date_closed'>
				<small>" . format_locale_date(strtotime($req->date_closed), 'short', false) . "</small></td>";
            $tool_content .= "<td class='option-btn-cell'>";
            $tool_content .= action_button(array(
                                array('title' => $langRestore,
                                      'url' => "$_SERVER[SCRIPT_NAME]?id=$req->id&amp;show=closed$reqtype",
                                      'icon' => 'fa-retweet')));
            $tool_content .= "</td></tr>";
        }
        $tool_content .= "</table></div>";
    }

// -----------------------------------
// display rejected requests
// ----------------------------------
} elseif (!empty($show) && ($show == 'rejected')) {
    if (!empty($id) && ($id > 0)) {
        // restore request
        Database::get()->query("UPDATE user_request set state = 1, date_closed = NULL WHERE id = ?d", $id);
        $tool_content .= "<div class='alert alert-success'>$langReintroductionApplication</div>";
    } else {
        $tool_content .= "<div class='table-responsive'><table id = 'requests_table' class='table-default'>";
        $tool_content .= table_header(2);
        $sql = Database::get()->queryArray("SELECT id, givenname, surname, username, email,
                                        faculty_id, phone, am, date_open, date_closed, comment, status
                                        FROM user_request
                                        WHERE (state = 3 $depqryadd) ORDER BY date_closed DESC");
        $tool_content .= "<tbody>";
        foreach ($sql as $req) {
            $sort_date_open = date("Y-m-d H:i", strtotime($req->date_open));
            $sort_date_closed = date("Y-m-d H:i", strtotime($req->date_closed));
            $tool_content .= "<tr>";
            $tool_content .= "<td>" . q($req->givenname) . "&nbsp;" . q($req->surname) . "</td>";
            $tool_content .= "<td>" . q($req->username) . "&nbsp;</td>";
            $tool_content .= "<td>" . $tree->getFullPath($req->faculty_id) . "</td>";
            if ($req->status == USER_TEACHER) {
                $legend = $langCourseCreate;
            } else {
                $legend = "&mdash;";
            }
            $tool_content .= "<td>" . $legend . "</td>";
            $tool_content .= "<td class='text-center' data-sort='$sort_date_open'>
				<small>" . format_locale_date(strtotime($req->date_open), 'short', false) . "</small></td>";
            $tool_content .= "<td class='text-center' data-sort='$sort_date_closed'>
				<small>" . format_locale_date(strtotime($req->date_closed), 'short', false) . "</small></td>";
            $tool_content .= "<td class='option-btn-cell'>";
            $tool_content .= action_button(array(
                                array('title' => $langRestore,
                                      'url' => "$_SERVER[SCRIPT_NAME]?id=$req->id&amp;show=closed$reqtype",
                                      'icon' => 'fa-retweet')));
            $tool_content .= "</td></tr>";
        }
    }
    $tool_content .= "</tbody>";
    $tool_content .= "</table>";
    $tool_content .= "</div>";

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
            $tool_content .= "<div class='alert alert-info'>$langProfessorRequestClosed</div>";
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
                        $tool_content .= "<div class='alert alert-success'>" . (($list_status == 1) ? $langTeacherRequestHasRejected : $langRequestReject);
                        $tool_content .= " $langRequestMessageHasSent <b>" . q($_POST['prof_email']) . "</b></div>";
                        $tool_content .= "<br><p><b>$langComments:</b><br>" . q($_POST['comment']) . "</p>";
                    }
                }
            } else {
                // display the form
                $d = Database::get()->querySingle("SELECT comment, givenname, surname, email, status FROM user_request WHERE id = ?d", $id);
                $warning = ($d->status == 5) ? $langWarnReject : $langGoingRejectRequest;
                $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>
                <div class='alert alert-warning'>$warning</div>
                <table class='table-default'>
                    <tr><th class='left'>$langName</th>
                        <td>" . q($d->givenname) . "</td></tr>
                    <tr><th class='left'>$langSurname</th>
                        <td>" . q($d->surname) . "</td></tr>
                    <tr><th class='left'>$langEmail</th>
                        <td>" . q($d->email) . "</td></tr>
                    <tr><th class='left'>$langComments</th>
                        <td>
                        <input type='hidden' name='id' value='" . $id . "'>
                        <input type='hidden' name='close' value='2'>
                        <input type='hidden' name='prof_givenname' value='" . q($d->givenname) . "'>
                        <input type='hidden' name='prof_surname' value='" . q($d->surname) . "'>
                        <textarea class='auth_input' name='comment' rows='5' cols='60'>" . q($d->comment) . "</textarea>
                        </td>
                    </tr>
                    <tr><th class='left'>$langRequestSendMessage</th>
                        <td>&nbsp;<input type='text' class='auth_input' name='prof_email' value='" . q($d->email) . "'>
                            <input type='checkbox' name='sendmail' value='1' checked='yes'> <small>($langGroupValidate)</small>
                        </td
                    ></tr>
                    <tr>
                        <th class='left'>nbsp;</th>
                        <td><input class='btn btn-primary' type='submit' name='submit' value='" . q($langRejectRequest) . "'>&nbsp;&nbsp;<small>($langRequestDisplayMessage)</small></td>
                    </tr>
                </table>
                ". generate_csrf_token_form_field() ."
                </form>";
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
    $sql = Database::get()->queryArray("SELECT id, givenname, surname, username, faculty_id, date_open, comment, password, status FROM user_request
                                WHERE (state = 1 $depqryadd) ORDER BY date_open DESC");
    if (count($sql) > 0) {
        $tool_content .= "<div class='table-responsive'><table id='requests_table' class='table-default'>";
        $tool_content .= table_header();
        $tool_content .= "<tbody>";
        foreach ($sql as $req) {
            $sort_date = date("Y-m-d H:i", strtotime($req->date_open));
            $tool_content .= "<td>" . q($req->givenname) . "&nbsp;" . q($req->surname) . "</td>";
            $tool_content .= "<td>" . q($req->username) . "</td>";
            $tool_content .= "<td>" . $tree->getFullPath($req->faculty_id) . "</td>";
            if ($req->status == USER_TEACHER) {
                $legend = $langCourseCreate;
            } else {
                $legend = "&mdash;";
            }
            $tool_content .= "<td>" . $legend . "</td>";
            $tool_content .= "<td data-sort='$sort_date'><small>" . format_locale_date(strtotime($req->date_open), 'short', false) . "</small></td>";
            if ($req->status == USER_TEACHER) {
                $user_type = '&type=prof';
            } else {
                $user_type = '';
            }
            $tool_content .= "<td class='option_btn_cell'>";
            switch ($req->password) {
                case 'pop3':
                    $link = "../auth/ldapnewprofadmin.php?id=$req->id&amp;auth=2";
                    $authmethod = "($langViaPop)";
                    break;
                case 'imap':
                    $link = "../auth/ldapnewprofadmin.php?id=$req->id&amp;auth=3";
                    $authmethod = "($langViaImap)";
                    break;
                case 'ldap':
                    $link = "../auth/ldapnewprofadmin.php?id=$req->id&amp;auth=4";
                    $authmethod = "($langViaLdap)";
                    break;
                case 'db':
                    $link = "../auth/ldapnewprofadmin.php?id=$req->id&amp;auth=5";
                    $authmethod = "($langViaDB)";
                    break;
                case 'shibboleth':
                    $link = "../auth/ldapnewprofadmin.php?id=$req->id&amp;auth=6";
                    $authmethod = "($langViaShibboleth)";
                    break;
                case 'cas':
                    $link = "../auth/ldapnewprofadmin.php?id=$req->id&amp;auth=7";
                    $authmethod = "($langViaCAS)";
                    break;
                default:
                    $link = "newuseradmin.php?id=$req->id$user_type";
                    $authmethod = '';
                    break;
            }
            $tool_content .= action_button(array(
                array('title' => "$langEditChange $authmethod",
                      'icon' => 'fa-edit',
                      'url' => $link)
            ));
            $tool_content .= "</td></tr>";
        }
        $tool_content .= "</tbody>";
        $tool_content .= "</table>";
        $tool_content .= "</div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langUserNoRequests</div>";
    }
}

draw($tool_content, 3, null, $head_content);

/**
 * @brief function to display table header
 * @return string
 */
function table_header($addon = FALSE) {

    global $langSurnameName, $langFaculty, $langUsername, $langDateRequest,
           $langDateClosed, $langDateReject, $langUserPermissions;

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
                    <th scope='col'><div class='text-center'>$langUserPermissions</div></th>
                    <th class='text-center'>$langDateRequest</th>";
    $string .= $datestring;
    $string .= "<th scope='col'>" . icon('fa-gears') . "</th>";
    $string .= "</tr></thead>";

    return $string;
}
