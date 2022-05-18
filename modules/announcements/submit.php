<?php
/* ========================================================================
 * Open eClass 4.0
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

$require_current_course = true;
$require_editor = true;

include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/search/indexer.class.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'include/action.php';

if (isset($_POST['submitAnnouncement'])) {
    if (isset($_POST['id']) and $_POST['id']) {
        $announceEditUrl = "modules/announcements/edit.php?course=$course_code&id=" . intval($_POST['id']);
    } else {
        $announceEditUrl = "modules/announcements/new.php?course=$course_code";
    }
    $action = new action();
    $action->record(MODULE_ID_ANNOUNCE);

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('antitle'));
    $v->labels(array('antitle' => "$langTheField $langAnnTitle"));
    if (isset($_POST['startdate_active'])) {
        $v->rule('required', array('startdate'));
        $v->labels(array('startdate' => "$langTheField $langStartDate"));
    }
    if (isset($_POST['enddate_active'])) {
        $v->rule('required', array('enddate'));
        $v->labels(array('enddate' => "$langTheField $langEndDate"));
    }
    if ($v->validate()) {
        if ($language == 'el') {
            $datetime = claro_format_locale_date($dateTimeFormatShort);
        } else {
            $datetime = date('l jS \of F Y h:i A');
        }
        if (isset($_POST['show_public'])) {
            $is_visible = 1;
        } else {
            $is_visible = 0;
        }

        $antitle = $_POST['antitle'];
        $newContent = purify($_POST['newContent']);
        $send_mail = isset($_POST['recipients']) && (count($_POST['recipients'])>0);
        if (isset($_POST['startdate_active']) && isset($_POST['startdate']) && !empty($_POST['startdate'])) {
            $startDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['startdate']);
            $start_display = $startDate_obj->format('Y-m-d H:i:s');
        } else {
            $start_display = null;
        }
        if (isset($_POST['enddate_active']) && isset($_POST['enddate']) && !empty($_POST['enddate'])) {
            $endDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['enddate']);
            $stop_display = $endDate_obj->format('Y-m-d H:i:s');
        } else {
            $stop_display = null;
        }

        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE announcement
                    SET content = ?s,
                        title = ?s,
                        `date` = " . DBHelper::timeAfter() . ",
                        start_display = ?t,
                        stop_display = ?t,
                        visible = ?d
                    WHERE id = ?d",
                $newContent, $antitle, $start_display, $stop_display, $is_visible, $id);
            $log_type = LOG_MODIFY;
            $message = $langAnnModify;
        } else { // add new announcement
            $id = Database::get()->query("INSERT INTO announcement
                                             SET content = ?s,
                                                 title = ?s, `date` = " . DBHelper::timeAfter() . ",
                                                 course_id = ?d, `order` = 0,
                                                 start_display = ?t,
                                                 stop_display = ?t,
                                                 visible = ?d", $newContent, $antitle, $course_id, $start_display, $stop_display, $is_visible)->lastInsertID;
            $log_type = LOG_INSERT;
            $message = $langAnnAdd;
        }
        if (isset($_POST['tags'])) {
            $tagsArray = $_POST['tags'];
            $moduleTag = new ModuleElement($id);
            $moduleTag->syncTags($tagsArray);
        }
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_ANNOUNCEMENT, $id);
        $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($_POST['newContent'])), 50, '+');
        Log::record($course_id, MODULE_ID_ANNOUNCE, $log_type, array('id' => $id,
            'email' => $send_mail,
            'title' => $_POST['antitle'],
            'content' => $txt_content));

        // send email
        if ($send_mail) {
            $title = course_id_to_title($course_id);
            $recipients_emaillist = "";
            if ($_POST['recipients'][0] == -1) { // all users
                $cu = Database::get()->queryArray("SELECT cu.user_id FROM course_user cu
                                                            JOIN user u ON cu.user_id=u.id
                                                        WHERE cu.course_id = ?d
                                                        AND u.email <> ''
                                                        AND u.email IS NOT NULL", $course_id);
                foreach($cu as $re) {
                    $recipients_emaillist .= (empty($recipients_emaillist))? "'$re->user_id'":",'$re->user_id'";
                }
            } else { // selected users
                foreach($_POST['recipients'] as $re) {
                    $recipients_emaillist .= (empty($recipients_emaillist))? "'$re'":",'$re'";
                }
            }

            $emailHeaderContent = "
                    <!-- Header Section -->
                    <div id='mail-header'>
                        <br>
                        <div>
                            <div id='header-title'>$langAnnHasPublished <a href='{$urlServer}courses/$course_code/'>" . q($title) . "</a>.</div>
                            <ul id='forum-category'>
                                <li><span><b>$langSender:</b></span> <span class='left-space'>" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "</span></li>
                                <li><span><b>$langdate:</b></span> <span class='left-space'>$datetime</span></li>
                            </ul>
                        </div>
                    </div>";

            $emailBodyContent = "
                    <!-- Body Section -->
                    <div id='mail-body'>
                        <br>
                        <div><b>$langSubject:</b> <span class='left-space'>".q($_POST['antitle'])."</span></div><br>
                        <div><b>$langMailBody</b></div>
                        <div id='mail-body-inner'>
                            $newContent
                        </div>
                    </div>";

            $emailFooterContent = "
                    <!-- Footer Section -->
                    <div id='mail-footer'>
                        <br>
                        <div>
                            <small>" . sprintf($langLinkUnsubscribe, q($title)) ." <a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a></small>
                        </div>
                    </div>";

            $emailContent = $emailHeaderContent.$emailBodyContent.$emailFooterContent;

            $emailSubject = "$professorMessage ($public_code - " . q($title) . " - $langAnnouncement)";
            // select students email list
            $countEmail = 0;
            $invalid = 0;
            $recipients = array();
            $emailBody = html2text($emailContent);
            $general_to = 'Members of course ' . $course_code;
            Database::get()->queryFunc("SELECT course_user.user_id as id, user.email as email
                                                       FROM course_user, user
                                                       WHERE course_id = ?d AND user.id IN ($recipients_emaillist) AND
                                                             course_user.user_id = user.id", function ($person)
            use (&$countEmail, &$recipients, &$invalid, $course_id, $general_to, $emailSubject, $emailBody, $emailContent, $charset) {
                $countEmail++;
                $emailTo = $person->email;
                $user_id = $person->id;
                // check email syntax validity
                if (!Swift_Validate::email($emailTo)) {
                    $invalid++;
                } elseif (get_user_email_notification($user_id, $course_id)) {
                    // checks if user is notified by email
                    array_push($recipients, $emailTo);
                }
                // send mail message per 50 recipients
                if (count($recipients) >= 50) {
                    send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent);
                    $recipients = array();
                }
            }, $course_id);
            if (count($recipients) > 0) {
                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent);
            }
            Session::Messages("$langAnnAddWithEmail $countEmail $langRegUser", 'alert-success');
            if ($invalid > 0) { // info about invalid emails (if exist)
                Session::Messages("$langInvalidMail $invalid", 'alert-warning');
            }
        }
        Session::Messages($message, 'alert-success');
        redirect_to_home_page("modules/announcements/index.php?course=$course_code");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page($announceEditUrl);
    }
} else {
    redirect_to_home_page("modules/announcements/index.php?course=$course_code");
}
