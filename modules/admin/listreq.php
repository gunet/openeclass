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

$require_usermanage_user = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('datatables');
load_js('datatables_filtering_delay');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#requests_table').DataTable ({                                
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,                
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
            }).fnSetFilteringDelay(1000);
            $('.dataTables_filter input').attr('placeholder', '$langName, $langSurname, $langUsername');
        });
        </script>";

$head_content .= '
<script type="text/javascript">
function confirmation() {
   if (confirm("' . $langCloseConf . '")) {
                return true;
   } else {
          return false;
  }
}
</script>';

$basetoolurl = $_SERVER['SCRIPT_NAME'];
if (isset($_GET['type']) and $_GET['type'] == 'user') {
    $list_status = 5;
    $toolName = $langUserOpenRequests;
    $reqtype = '&amp;type=user';
    $basetoolurl .= '?type=user';
    $linkreg = $langUserDetails;
    $linkget = '?type=user';
} else {
    $list_status = 1;
    $toolName = $langOpenProfessorRequests;
    $reqtype = '';
    $linkreg = $langProfReg;
    $linkget = '';
}
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

// Main body
$close = isset($_GET['close']) ? $_GET['close'] : (isset($_POST['close']) ? $_POST['close'] : '');
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : '');
$show = isset($_GET['show']) ? $_GET['show'] : (isset($_POST['show']) ? $_POST['show'] : '');

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
    $deps = $user->getDepartmentIds($uid);
    $depqryadd = ' AND faculty_id IN (' . implode(', ', $deps) . ')';
}

// Deal with navigation
switch ($show) {
    case "closed":
        $toolName = $langReqHaveClosed;
        $pagination_link = '&amp;show=closed';
        break;
    case "rejected":        
        $toolName = $langReqHaveBlocked;
        break;
}

// Display Actions Toolbar
$tool_content .= "
      <div id='operations_container'>" .
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
        $count_req = count(Database::get()->queryArray("SELECT * FROM user_request WHERE (state = 2 AND status = ?d)", $list_status));

        $q = "SELECT id, givenname, surname, username, email, faculty_id,
                             phone, am, date_open, date_closed, comment
                          FROM user_request
                          WHERE (state = 2 AND status = $list_status)";

        $q .= "ORDER BY date_open DESC";

        $sql = Database::get()->queryArray($q);
        $tool_content .= "<div class='table-responsive'><table id = 'requests_table' class='table-default'>";
        $tool_content .= table_header(1, $langDateClosed_small);        
        foreach ($sql as $req) {
            $tool_content .= "<tr>";
            $tool_content .= '<td>' . q($req->givenname) . "&nbsp;" . q($req->surname) . "";
            $tool_content .= '<td>' . q($req->username) . '</td>';
            $tool_content .= '<td>' . $tree->getFullPath($req->faculty_id) . '</td>';
            $tool_content .= "<td align='center'>
				<small>" . nice_format(date('Y-m-d', strtotime($req->date_open))) . "</small></td>";
            $tool_content .= "<td align='center'>
				<small>" . nice_format(date('Y-m-d', strtotime($req->date_closed))) . "</small></td>";
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
        $tool_content .= table_header(1, $langDateReject_small);
        $sql = Database::get()->queryArray("SELECT id, givenname, surname, username, email,
                                        faculty_id, phone, am, date_open, date_closed, comment
                                        FROM user_request
                                        WHERE (state = 3 AND status = $list_status $depqryadd) ORDER BY date_open DESC");
        $tool_content .= "<tbody>";
        foreach ($sql as $req) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>" . q($req->givenname) . "&nbsp;" . q($req->surname) . "</td>";
            $tool_content .= "<td>" . q($req->username) . "&nbsp;</td>";
            $tool_content .= "<td>" . $tree->getFullPath($req->faculty_id) . "</td>";
            $tool_content .= "<td align='center'>
				<small>" . nice_format(date('Y-m-d', strtotime($req->date_open))) . "</small></td>";
            $tool_content .= "<td align='center'>
				<small>" . nice_format(date('Y-m-d', strtotime($req->date_closed))) . "</small></td>";
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
            if ($list_status == 1) {
                $tool_content .= "<div class='alert alert-info'>$langProfessorRequestClosed</div>";
            } else {
                $tool_content .= "<div class='alert alert-info'$langRequestStudent</div>";
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
                                    ".q($_POST[comment])."<br><br>
                                    <ul id='forum-category'>
                                        <li><span><b>$langManager $siteName:</b></span> <span class='left-space'>$administratorName</span></li>
                                        <li><span><b>$langPhone:</b></span> <span class='left-space'>$telephone</span></li>
                                        <li><span><b>$langEmail:</b></span> <span class='left-space'>$emailhelpdesk</span></li>
                                    </ul>
                                </div>
                            </div>";

                            $emailbody = $emailHeader.$emailMain;

                            $emailPlainBody = html2text($emailbody);

                            send_mail_multipart('', '', "$_POST[prof_givenname] $_POST[prof_surname]", $_POST['prof_email'], $emailsubject, $emailPlainBody, $emailbody, $charset);

                        }
                        $tool_content .= "<div class='alert alert-success'>" . (($list_status == 1) ? $langTeacherRequestHasRejected : $langRequestReject);
                        $tool_content .= " $langRequestMessageHasSent <b>" . q($_POST[prof_email]) . "</b></div>";
                        $tool_content .= "<br><p><b>$langComments:</b><br>" . q($_POST[comment]) . "</p>";
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
			</td></tr>
			<tr><th class='left'>$langRequestSendMessage</th>
			<td>&nbsp;<input type='text' class='auth_input' name='prof_email' value='" . q($d->email) . "'>
			<input type='checkbox' name='sendmail' value='1' checked='yes'> <small>($langGroupValidate)</small>
			</td></tr>
			<tr><th class='left'>&nbsp;</th>
			<td><input class='btn btn-primary' type='submit' name='submit' value='" . q($langRejectRequest) . "'>&nbsp;&nbsp;<small>($langRequestDisplayMessage)</small></td>
			</tr></table>
            ". generate_csrf_token_form_field() ."
			</form>";
            }
            break;
        default:
            break;
    } // end of switch
}

// -----------------------------------
// display all the requests
// -----------------------------------
else {
    // show username as well (useful)
    $sql = Database::get()->queryArray("SELECT id, givenname, surname, username, faculty_id, date_open, comment, password FROM user_request
                                WHERE (state = 1 AND status = $list_status $depqryadd)");
    if (count($sql) > 0) {
        $tool_content .= "<div class='table-responsive'><table id='requests_table' class='table-default'>";
        $tool_content .= table_header();        
        $tool_content .= "<tbody>";
        foreach ($sql as $req) {                        
            $tool_content .= "<td>" . q($req->givenname) . "&nbsp;" . q($req->surname) . "</td>";
            $tool_content .= "<td>" . q($req->username) . "</td>";
            $tool_content .= "<td>" . $tree->getFullPath($req->faculty_id) . "</td>";
            $tool_content .= "<td class='text-center'>
                                <small>" . nice_format(date('Y-m-d', strtotime($req->date_open))) . "</small></td>";
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
                    $link = "newuseradmin.php?id=$req->id";
                    $authmethod = '';
                    break;
            }
            $tool_content .= action_button(array(
                array('title' => "$langElaboration $authmethod",
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
 * @global type $langName
 * @global type $langSurname
 * @global type $langFaculty
 * @global type $langDate
 * @global type $langActions
 * @global type $langUsername
 * @global type $langDateRequest_small
 * @param type $addon
 * @param type $message
 * @return string
 */
function table_header($addon = FALSE, $message = FALSE) {

    global $langName, $langSurname, $langFaculty, $langDate, $langActions, $langUsername;
    global $langDateRequest_small;

    $string = '<thead>';
    if ($addon) {
        $rowspan = 2;
        $datestring = "<th colspan='2'>$langDate</th>
		<th scope='col' rowspan='$rowspan'><div align='center'>$langActions</div></th>
		</tr><tr class='list-header'>
		<th>$langDateRequest_small</th>
		<th>$message</th>";
    } else {
        $rowspan = 1;
        $datestring = "<th scope='col'><div align='center'>$langDate<br />$langDateRequest_small</div></th>
		<th scope='col'><div align='center'>$langActions</div></th>";
    }

    $string .= "<tr class='list-header'>
	<th scope='col' rowspan='$rowspan'><div align='left'>&nbsp;&nbsp;$langName $langSurname</div></th>
	<th scope='col' rowspan='$rowspan'><div align='left'>$langUsername</div></th>
	<th scope='col' rowspan='$rowspan'><div align='center'>$langFaculty</div></th>";
    $string .= $datestring;
    $string .= "</tr></thead>";

    return $string;
}
