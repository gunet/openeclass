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


/* ===========================================================================
  @authors list: Karatzidis Stratos <kstratos@uom.gr>
  Vagelis Pitsioygas <vagpits@uom.gr>
  ==============================================================================
  @Description: This script/file tries to authenticate the user, using
  his user/pass pair and the authentication method defined by the admin

  ==============================================================================
 */

$require_usermanage_user = TRUE;

include '../../include/baseTheme.php';
include 'include/sendMail.inc.php';
require_once 'auth.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';

$tree = new Hierarchy();
$userObj = new User();

load_js('jquery');
load_js('jquery-ui');
load_js('jstree');


$auth = isset($_REQUEST['auth']) ? intval($_REQUEST['auth']) : '';

$msg = "$langProfReg (" . (get_auth_info($auth)) . ")";

$nameTools = $msg;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../admin/listreq.php", "name" => $langOpenProfessorRequests);
$tool_content = "";

$submit = isset($_POST['submit']) ? $_POST['submit'] : '';
// professor registration
if ($submit) {
    $rid = $_POST['rid'];
    $pn = $_POST['pn'];
    $ps = $_POST['ps'];
    $pu = $_POST['pu'];
    $pe = $_POST['pe'];    
    $department = $_POST['department'];
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    $lang = $session->validate_language_code(@$_POST['language']);
    
    // check if user name exists
    $username_check = Database::get()->querySingle("SELECT username FROM user WHERE username = ?s", $pu);    
    if ($username_check) {
        $tool_content .= "<p class='caution'>$langUserFree</p><br><br><p align='right'>
        <a href='../admin/listreq.php'>$langBackRequests</a></p>";
        draw($tool_content, 3);
        exit();
    }

    switch ($auth) {
        case '2': $password = "pop3";
            break;
        case '3': $password = "imap";
            break;
        case '4': $password = "ldap";
            break;
        case '5': $password = "db";
            break;
        case '6': $password = "shibboleth";
            break;
        case '7': $password = "cas";
            break;
        default: $password = "";
            break;
    }

    $registered_at = time();
    $expires_at = time() + get_config('account_duration');
    $verified_mail = isset($_REQUEST['verified_mail']) ? intval($_REQUEST['verified_mail']) : 2;

    $sql = Database::get()->query("INSERT INTO user (surname, givenname, username, password, email, status,
                                                    am, registered_at, expires_at, lang, verified_mail)
                                VALUES (?s, ?s, ?s, ?s, ?s, 1, ?s, 
                                " . DBHelper::timeAfter() . ",
                                " . DBHelper::timeAfter(get_config('account_duration')) . ", ?s, ?d)", 
                    $ps, $pn, $pu, $password, $pe, $comment, $lang, $verified_mail);

    $last_id = $sql->lastInsertID;
    $userObj->refresh($last_id, array(intval($department)));
    
    $telephone = get_config('phone');
    $administratorName = get_config('admin_name');
    $emailhelpdesk = get_config('email_helpdesk');
    // Close user request
    Database::get()->query("UPDATE user_request SET state = 2,
                            date_closed = " . DBHelper::timeAfter() . ",
                            verified_mail = ?d WHERE id = ?d", $verified_mail, $rid);
    $emailbody = "$langDestination $pn $ps\n" .
            "$langYouAreReg $siteName $langSettings $pu\n" .
            "$langPass: $langPassSameAuth\n$langAddress $siteName: " .
            "$urlServer\n$langProblem\n$langFormula" .
            "$administratorName\n" .
            "$langManager $siteName \n$langTel $telephone \n" .
            "$langEmail: $emailhelpdesk";

    if (!send_mail('', '', '', $pe, $mailsubject, $emailbody, $charset)) {
        $tool_content .= "<table width='99%'><tbody><tr>
	    <td class='caution' height='60'>
	    <p>$langMailErrorMessage &nbsp; <a href=\"mailto:$emailhelpdesk\">$emailhelpdesk</a></p>
	    </td></tr></tbody></table>";
        draw($tool_content, 3);
        exit();
    }

    // user message
    $tool_content .= "<p class='success'>$profsuccess<br><br>
                     <a href='../admin/listreq.php'>$langBackRequests</a></p>";
} else {
    // if not submit then display the form
    if (isset($_GET['id'])) { // if we come from prof request
        $id = intval($_GET['id']);
        // display actions toolbar
        $tool_content .= "<div id='operations_container'>
		<ul id='opslist'>
		<li><a href='../admin/listreq.php?id=$id&amp;close=1' onclick='return confirmation();'>$langClose</a></li>
		<li><a href='../admin/listreq.php?id=$id&amp;close=2'>$langRejectRequest</a></li>";
        if (isset($_GET['id'])) {
                $tool_content .= "<li><a href='../admin/listreq.php'>$langBackRequests</a>";
        }
        $tool_content .= "</ul></div>";
        $res = Database::get()->querySingle("SELECT givenname, surname, username, email,
                                                    faculty_id, comment, lang, date_open, phone, am, verified_mail 
                                                    FROM user_request WHERE id = ?d", $id);
        $ps = $res->surname;
        $pn = $res->givenname;
        $pu = $res->username;
        $pe = $res->email;
        $pt = $res->faculty_id;
        $pcom = $res->comment;
        $pam = $res->am;
        $pphone = $res->phone;
        $lang = $res->lang;
        $pvm = $res->verified_mail;
        $pdate = nice_format(date('Y-m-d', strtotime($res->date_open)));
    }

    @$tool_content .= "
      <form action='$_SERVER[SCRIPT_NAME]' method='post'>
      <fieldset>
      <legend>$langNewProf</legend>
	<table width='100%' class='tbl'>
	<tr>
	<th class='left' width='180'><b>" . $langSurname . "</b></th>
	<td>" . q($ps) . "<input type='hidden' name='ps' value='" . q($ps) . "'></td>
	</tr>
	<tr>
	<th class='left'><b>$langName</b></th>
	<td>" . q($pn) . "<input type='hidden' name='pn' value='" . q($pn) . "'></td>
	</tr>
	<tr>
	<th class='left'><b>$langUsername</b></th>
	<td>" . q($pu) . "<input type='hidden' name='pu' value='" . q($pu) . "'></td>
	</tr>
	<tr>
	<th class='left'><b>$langEmail</b></th>
	<td>" . q($pe) . "<input type='hidden' name='pe' value='" . q($pe) . "' ></td>
	</tr>
	<tr>
	<th class='left'><b>$langEmailVerified</b></th>
	<td>";

    $verified_mail_data = array();
    $verified_mail_data[0] = $m['pending'];
    $verified_mail_data[1] = $langYes;
    $verified_mail_data[2] = $langNo;

    $tool_content .= selection($verified_mail_data, "verified_mail", $pvm);

    $tool_content .= "</td>
	</tr>
	<tr>
	<th class='left'>$langFaculty</th>
	<td>";
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => $pt, 'tree' => null, 'useKey' => "id", 'where' => "AND node.allow_user = true", 'multiple' => false));
    $head_content .= $js;
    $tool_content .= $html;
    $tool_content .= "</td></tr>";
    $tool_content .= "<tr><th class='left'>$langLanguage</th><td>";
    $tool_content .= lang_select_options('language', '', $lang);
    $tool_content .= "</td></tr>";
    $tool_content .= "<tr><th class='left'><b>$langPhone</b></th>
	<td>" . @q($pphone) . "&nbsp;</td></tr>
	<tr>
	<th class='left'><b>$langComments</b></th>
	<td>" . @q($pcom) . "&nbsp;</td>
	</tr>
	<tr>
	<th class='left'><b>$langDate</b></th>
	<td>" . @q($pdate) . "&nbsp;</td>
	</tr>
	<tr><th>&nbsp;</th>
	<td><input type='submit' name='submit' value='" . $langSubmit . "' >
	<input type='hidden' name='auth' value='$auth' >
	</td></tr>
	</table>
	<input type='hidden' name='rid' value='" . @$id . "'>
      </fieldset>
      </form>";
    $tool_content .= "<p align='right'><a href='../admin/index.php'>$langBack</a></p>";
}
draw($tool_content, 3, null, $head_content);
