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
                    <li><span><b>$langComments:</b></span> <span>$body</span></li>
                </ul>
                <div>
                    <p><a href='{$urlServer}modules/admin/edituser.php?u=$userid'>{$urlServer}modules/admin/edituser.php?u=$userid</a></p>
                </div>
            </div>
        </div>";

        $emailbody = $header_html_topic_notify.$body_html_topic_notify;

        $plainEmailBody = html2text($emailbody);
        if (!send_mail_multipart("$lastname $firstname", $email, '', $to, $emailsubject, $plainEmailBody, $emailbody)) {
            $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langEmailNotSend " . q($to) . "!</span></div><br />";
        } else {
            $tool_content .= "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$emailsuccess</span></div><br />";
        }
    } else {
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => '$urlAppend',
                'icon' => 'fa-reply',
                'level' => 'primary')
        ),false);
        $tool_content .= "
        <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'>
        <div class='form-wrapper form-edit'>
            <form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?userid=$userid&amp;h=$_GET[h]' method='post'>
            <fieldset>
                <div class='form-group'>
                    <label for='name_id' class='col-12 control-label-notes'>$langName</label>
                    <div class='col-12'>
                        <input id='name_id' class='form-control' type='text' name='$langName' value='" . q($firstname) . "' disabled  />
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='surname_id' class='col-12 control-label-notes'>$langSurname</label>
                    <div class='col-12'>
                        <input id='surname_id' class='form-control' type='text' name='$langSurname' value='" . q($lastname) . "' disabled  />
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='email_id' class='col-12 control-label-notes'>Email</label>
                    <div class='col-12'>
                        <input id='email_id' class='form-control' type='text' name='email' value='" . q($email) . "' disabled  />
                    </div>
                </div>
                <div class='form-group mt-4'>
                    <label for='comments_id' class='col-12 control-label-notes'>$langComments</label>
                    <div class='col-12'>
                        <textarea id='comments_id' class='form-control' rows='6' name='body'>$langActivateAccount</textarea>
                    </div>
                </div>
                <div class='form-group mt-5'>
                    <div class='col-12 d-flex justify-content-end align-items-center'>".
                        form_buttons(array(
                            array(
                                'text'  => $langSend,
                                'name'  => 'submit',
                                'value' => $langSend,
                            ),
                            array(
                                'class' => 'cancelAdminBtn',
                                'href' => $urlAppend
                            )
                        ))
                    ."</div>
                </div>
                </fieldset>
            </form>
        </div>
    </div><div class='d-none d-lg-block'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div>
</div>";

    }
}

draw($tool_content, 0);
