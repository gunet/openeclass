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

load_js('jstree3');

$auth = isset($_REQUEST['auth']) ? intval($_REQUEST['auth']) : '';

$msg = "$langProfReg (" . (get_auth_info($auth)) . ")";

$toolName = $msg;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../admin/listreq.php", "name" => $langOpenProfessorRequests);

$submit = isset($_POST['submit']) ? $_POST['submit'] : '';
// professor registration
if ($submit) {
    $rid = $_POST['rid'];
    $pn = $_POST['pn'];
    $ps = $_POST['ps'];
    $pu = $_POST['pu'];
    $pe = $_POST['pe'];
    $phone = $_POST['phone'];
    $department = getDirectReference($_POST['department']);
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    $lang = $session->validate_language_code(@$_POST['language']);
    
    // check if user name exists
    $username_check = Database::get()->querySingle("SELECT username FROM user WHERE username = ?s", $pu);    
    if ($username_check) {
        $tool_content .= "<div class='alert alert-danger'>$langUserFree</div><br><br><p align='pull-right'>
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
    $verified_mail = isset($_REQUEST['verified_mail_form']) ? intval($_REQUEST['verified_mail_form']) : EMAIL_UNVERIFIED;

    $sql = Database::get()->query("INSERT INTO user (surname, givenname, username, password, email, status, phone,
                                                    am, registered_at, expires_at, lang, verified_mail, description, whitelist)
                                VALUES (?s, ?s, ?s, ?s, ?s, 1, ?s, ?s, 
                                " . DBHelper::timeAfter() . ",
                                " . DBHelper::timeAfter(get_config('account_duration')) . ", ?s, ?d, '', '')", 
                    $ps, $pn, $pu, $password, $pe, $phone, $comment, $lang, $verified_mail);
    $last_id = $sql->lastInsertID;
    // update personal calendar info table
    // we don't check if trigger exists since it requires `super` privilege
    Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $last_id);    
    $userObj->refresh($last_id, array(intval($department)));
    user_hook($last_id);
    
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

    $header_html_topic_notify = "<!-- Header Section -->
        <div id='mail-header'>
            <br>
            <div>
                <div id='header-title'>$langYouAreReg $siteName</div>
            </div>
        </div>";

    $body_html_topic_notify = "<!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div id='mail-body-inner'>
                <p>$langSettings</p>
                <ul id='forum-category'>
                    <li><span><b>$lang_username:</b></span> <span>$pu</span></li>
                    <li><span><b>$langPassword:</b></span> <span>$langPassSameAuth</span></li>
                    <li><span><b>$langAddress $siteName:</b></span> <span>$urlServer</span></li>
                    </ul>
                    <p>$langProblem<br><br>$langFormula<br>$administratorName<br>$langManager $siteName<br>$langTel: $telephone<br>$langEmail: $emailhelpdesk</p>

            </div>
        </div>";

    $emailbody = $header_html_topic_notify.$body_html_topic_notify;
    $plainemailbody = html2text($emailbody);

    if (!send_mail_multipart('', '', '', $pe, $mailsubject, $plainemailbody, $emailbody)) {
        $tool_content .= "
	    <div class='alert alert-danger'>$langMailErrorMessage &nbsp; <a href=\"mailto:$emailhelpdesk\">$emailhelpdesk</a></div>
	    ";
        draw($tool_content, 3);
        exit();
    }

    // user message
    $tool_content .= "<div class='alert alert-success'>$profsuccess<br><br>
                     <a href='../admin/listreq.php'>$langBackRequests</a></div>";
} else {
    // if not submit then display the form
    if (isset($_GET['id'])) { // if we come from prof request
        $id = intval($_GET['id']);
        
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "../admin/index.php",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'),
            array('title' => $langBackRequests,
                  'url' => "../admin/listreq.php",
                  'icon' => 'fa-reply',
                  'level' => 'primary'),
            array('title' => $langRejectRequest,
                  'url' => "../admin/listreq.php?id=$_GET[id]&amp;close=2",
                  'icon' => 'fa-ban',
                  'level' => 'primary'),
            array('title' => $langClose,
                  'url' => "../admin/listreq.php?id=$_GET[id]&amp;close=1",
                  'icon' => 'fa-close',
                  'level' => 'primary')));
                        
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

    $tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' onsubmit='return validateNodePickerForm();'>
        <fieldset>
        <div class='form-group'>
        <label for='Sur' class='col-sm-2 control-label'>$langSurname:</label>
            <div class='col-sm-10'>" .q($ps) ."
                <input type='hidden' name='ps' value='" . q($ps) . "'>              
            </div>
        </div>
        <div class='form-group'>
        <label for='Name' class='col-sm-2 control-label'>$langName:</label>
            <div class='col-sm-10'>" .q($pn) ."
                <input type='hidden' name='pn' value='" . q($pn) . "'>              
            </div>            
        </div>
        <div class='form-group'>
        <label for='Username' class='col-sm-2 control-label'>$langUsername:</label>
            <div class='col-sm-10'>" . q($pu) . "
                <input type='hidden' name='pu' value='" . q($pu) . "'>                
            </div>
        </div>        
        <div class='form-group'>
        <label for='email' class='col-sm-2 control-label'>$langEmail:</label>
            <div class='col-sm-10'>" . q($pe) . "
                <input type='hidden' name='pe' value='" . q($pe) . "'>
            </div>
        </div>
	<div class='form-group'>
          <label for='emailverified' class='col-sm-2 control-label'>$langEmailVerified:</label>
            <div class='col-sm-10'>";
        $verified_mail_data = array();        
        $verified_mail_data[0] = $m['pending'];
        $verified_mail_data[1] = $langYes;
        $verified_mail_data[2] = $langNo;
        if (isset($pvm)) {
            $tool_content .= selection($verified_mail_data, "verified_mail_form", $pvm, "class='form-control'");
        } else {
            $tool_content .= selection($verified_mail_data, "verified_mail_form", '', "class='form-control'");
        }
        $tool_content .= "</div></div>";          	
        $tool_content .= "<div class='form-group'>
        <label for='faculty' class='col-sm-2 control-label'>$langFaculty:</label>
            <div class='col-sm-10'>";           
        list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="department"', 'defaults' => $pt, 'tree' => null, 'where' => "AND node.allow_user = true", 'multiple' => false));
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</div></div>";
        $tool_content .= "<div class='form-group'>
            <label for='lang' class='col-sm-2 control-label'>$langLanguage:</label>
            <div class='col-sm-10'>";
        $tool_content .= lang_select_options('language', "class='form-control'", $lang);
        $tool_content .= "</div></div>";            
        $tool_content .= "<div class='form-group'>
            <label for='phone' class='col-sm-2 control-label'>$langPhone:</label>
                <div class='col-sm-10'>            
                    <input class='form-control' id='phone' type='text' name='phone' value='" . q($pphone) . "' placeholder='$langPhone'>
                </div>
            </div>
        <div class='form-group'>
            <label for='comments' class='col-sm-2 control-label'>$langComments</label>
                <div class='col-sm-10'>" . q($pcom) . "</div>
            </div>
	<div class='form-group'><label for='date' class='col-sm-2 control-label'>$langDate</label>
                                <div class='col-sm-10'>" . q($pdate) . "</div></div>        
	<div class='col-sm-offset-2 col-sm-10'>
            <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
        </div>		
	<input type='hidden' name='auth' value='$auth' >	
	<input type='hidden' name='rid' value='" . @$id . "'>
      </fieldset>
    </form>
    </div>";    
}
draw($tool_content, 3, null, $head_content);
