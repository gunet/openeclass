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

require_once 'include/sendMail.inc.php';

$default_email_subject = $GLOBALS['langCourseInvitationSubject'] . "  " . $GLOBALS['siteName'];
$default_email_body = "
        <!-- Header Section -->
        <div id='mail-header'>
            <div>
                <div id='header-title'>" .
                    $GLOBALS['langCourseInvitationSubject'] . "  " . $GLOBALS['siteName']
                . "</div>
            </div>
        </div>
        <!-- Body Section -->
        <div id='mail-body'>
            <div id='mail-body-inner'>
                <p>" . $GLOBALS['langCourseInvitationBody1'] . "</p>
                <p><strong>" . q($GLOBALS['currentCourseName']) . "</strong>
                <p>" . $GLOBALS['langCourseInvitationBody2'] . "</p>
                <p style='text-align: center'>
                    [link]
                </p>
            </div>
        </div>";

$rich_text_editor_style =
    'body { padding: 0px; margin: 0px; color: #555; background-color: #f7f7f7; font-family: Helvetica, sans-serif; font-size: 1em; }' .
    '#container { margin: 20px; padding: 10px; background-color: #fefefe; }' .
    '#mail-header, #mail-body, #mail-footer { padding: 0 15px 15px; }' .
    'hr { margin: 0px; }' .
    '#mail-header { padding-top: 10px; border-bottom: 1px solid #ddd; color: #666; }' .
    '#header-title { background-color: #f5f5f5; margin-left: -15px; margin-right: -15px; margin-bottom: 12px; padding: 12px 15px; font-weight: bold; }' .
    '#forum-category { list-style: none; padding-left: 0px; }' .
    '#forum-category li { padding-bottom: 1px; }' .
    '#forum-category li span:first-child { width: 150px; }' .
    '#forum-category li span:last-child { padding-left: 10px; }' .
    '#forum-category { margin-bottom: 0px; }' .
    '#mail-body-inner { padding-left: 30px; padding-right: 30px; }' .
    '#mail-footer { padding-bottom: 25px; border-top: 1px solid #ddd; color: #888; position: relative; }' .
    '#mail-footer-left { float: left; width: 8%; width: 80px; }' .
    '#mail-footer-right { float: left; width: 90%; }' .
    'b.notice { color: #555; }';

function send_invitation(string $email, string $token, string $email_subject = null, string $email_body = null) {

    global $default_email_subject, $default_email_body;

    $url = $GLOBALS['urlServer'] . 'main/invite.php?id=' . $token;

    if (!$email_subject) {
        $email_subject = $default_email_subject;
    }

    if (!$email_body) {
        $email_body = $default_email_body;
    }
    $email_body = str_replace(['[email]', '[link]'], [q($email), "<a href='$url'>$url</a>"], $email_body);

    $email_body_plain = html2text($email_body);
    send_mail_multipart('', '', '', $email, $email_subject, $email_body_plain, $email_body);
}
