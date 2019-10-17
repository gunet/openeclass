<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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


/* * ===========================================================================
  contactadmin.php
  @authors list: Karatzidis Stratos <kstratos@uom.gr>
  Vagelis Pitsioygas <vagpits@uom.gr>
  ==============================================================================
  @Description: Contact the admin with an e-mail message
  when an account has been deactivated

  This script allows a user the send an e-mail to the admin, requesting
  the re-activation of his/her account
  ==============================================================================
 */

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
$pageName = $langContactAdminAuth;

$userid = isset($_GET['userid']) ? intval($_GET['userid']) : 0;

if ($userid and isset($_GET['h']) and token_validate("userid=$userid", $_GET['h'])) {
    $info = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $userid);
    if ($info) {
        $firstname = $info->givenname;
        $lastname = $info->surname;
        $email = $info->email;
    } else {
        $firstname = $lastname = $email = '';
    }

    if (isset($_POST['submit'])) {
        $body = isset($_POST['body']) ? $_POST['body'] : '';
        $to = get_config('email_helpdesk');
        $emailsubject = $langAccountActivate;
        $emailbody = "$langAccountActivateMessage\n\n$firstname $lastname\ne-mail: $email\n" .
                "{$urlServer}modules/admin/edituser.php?u=$userid\n\n$m[comments]: $body\n";
        $header_html_topic_notify = "<!-- Header Section -->
        <div id='mail-header'>
            <br>
            <div>
                <div id='header-title'>$emailsubject</div>
            </div>
        </div>";

        $body_html_topic_notify = "<!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div id='mail-body-inner'>
            $langAccountActivateMessage
                <ul id='forum-category'>
                    <li><span><b>$langName:</b></span> <span>$firstname</span></li>
                    <li><span><b>$langSurname:</b></span> <span>$lastname</span></li>
                    <li><span><b>e-mail:</b></span> <span>$email</a></span></li>
                    <li><span><b>$m[comments]:</b></span> <span>$body</span></li>
                </ul>
                <div>
                    <p><a href='{$urlServer}modules/admin/edituser.php?u=$userid'>{$urlServer}modules/admin/edituser.php?u=$userid</a></p>
                </div>
            </div>
        </div>";

        $emailbody = $header_html_topic_notify.$body_html_topic_notify;

        $plainEmailBody = html2text($emailbody);
        if (!send_mail_multipart('', '', '', $to, $emailsubject, $plainEmailBody, $emailbody)) {
            $tool_content .= "<div class='alert alert-danger'>$langEmailNotSend " . q($to) . "!</div><br />";
        } else {
            $tool_content .= "<div class='alert alert-success'>$emailsuccess</div><br />";
        }
    } else {
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => '$urlAppend',
                'icon' => 'fa-reply',
                'level' => 'primary-label')
        ),false);
        $tool_content .= "
        <div class='form-wrapper'>
            <form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?userid=$userid&amp;h=$_GET[h]' method='post'>
            <fieldset>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langName:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='$langName' value='" . q($firstname) . "' disabled  />
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langSurname:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='$langSurname' value='" . q($lastname) . "' disabled  />
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>Email:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='email' value='" . q($email) . "' disabled  />
                    </div>
                </div>
                <div class='form-group'>
                    <label for='$langComments' class='col-sm-2 control-label'>$langComments:</label>
                    <div class='col-sm-10'>
                        <textarea class='form-control' rows='6' name='$langComments'>$langActivateAccount</textarea>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>".
                        form_buttons(array(
                            array(
                                'text'  => $langSend,
                                'name'  => 'submit',
                                'value' => $langSend,
                            ),
                            array(
                                'href' => $urlAppend
                            )
                        ))
                    ."</div>
                </div>
                </fieldset>
            </form>
        </div>";

    }
}

draw($tool_content, 0);
