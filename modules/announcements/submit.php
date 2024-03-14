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
        $datetime = format_locale_date(time(), 'short');
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

        if (isset($_POST['id']) and !isset($_POST['copy_ann'])) { // modify existing announcement
            if (is_null($start_display)) {
                $date_announcement = date("Y-m-d H:i:s");
            } else {
                $date_announcement = $start_display;
            }
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE announcement
                    SET content = ?s,
                        title = ?s,
                        `date` = '$date_announcement',
                        start_display = ?t,
                        stop_display = ?t,
                        visible = ?d
                    WHERE id = ?d",
                $newContent, $antitle, $start_display, $stop_display, $is_visible, $id);
            $log_type = LOG_MODIFY;
            $message = $langAnnModify;
            if (isset($_POST['tags'])) {
                $tagsArray = $_POST['tags'];
                $moduleTag = new ModuleElement($id);
                $moduleTag->syncTags($tagsArray);
            }
        } else { // add new announcement
            if (is_null($start_display)) {
                $date_announcement = date("Y-m-d H:i:s");
            } else {
                $date_announcement = $start_display;
            }
            $id = Database::get()->query("INSERT INTO announcement
                                             SET content = ?s,
                                                 title = ?s, `date` = '$date_announcement',
                                                 course_id = ?d, `order` = 0,
                                                 start_display = ?t,
                                                 stop_display = ?t,
                                                 visible = ?d", $newContent, $antitle, $course_id, $start_display, $stop_display, $is_visible)->lastInsertID;
            $log_type = LOG_INSERT;
            $message = $langAnnAdd;
            if (isset($_POST['tags'])) {
                $tagsArray = $_POST['tags'];
                $moduleTag = new ModuleElement($id);
                $moduleTag->syncTags($tagsArray);
            }
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
            $recipients_emaillist = '';
            if ($_POST['recipients']) { // Sending mail to users
                $emailContent = "
                    <!-- Header Section -->
                    <div id='mail-header'>
                        <br>
                        <div>
                            <div id='header-title'>$langAnnHasPublished <a href='{$urlServer}courses/$course_code/'>" . q($title) . "</a>.</div>
                            <ul id='forum-category'>
                                <li><span><b>$langSender:</b></span> <span class='left-space'>" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "</span></li>
                                <li><span><b>$langDate:</b></span> <span class='left-space'>$datetime</span></li>
                            </ul>
                        </div>
                    </div>
                    <!-- Body Section -->
                    <div id='mail-body'>
                        <br>
                        <div><b>$langSubject:</b> <span class='left-space'>" . q($_POST['antitle']) . "</span></div><br>
                        <div><b>$langMailBody</b></div>
                        <div id='mail-body-inner'>
                            $newContent
                        </div>
                    </div>
                    <!-- Footer Section -->
                    <div id='mail-footer'>
                        <br>
                        <div>
                            <small>" . sprintf($langLinkUnsubscribe, q($title)) . " <a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a></small>
                        </div>
                    </div>";

                $emailSubject = "$professorMessage ($public_code - " . q($title) . " - $langAnnouncement)";
                $emailBody = html2text($emailContent);
                $general_to = 'Members of course ' . $course_code;
                // select students email list
                $total = $invalid = $disabled = 0;
                $recipients = [];
                if (in_array('-1', $_POST['recipients'])) { // All users in course
                    $course_users = Database::get()->queryArray('SELECT course_user.user_id as id, user.email as email
                       FROM course_user, user WHERE course_id = ?d AND course_user.user_id = user.id', $course_id);
                } else {
                    $placeholders = implode(', ', array_fill(0, count($_POST['recipients']), '?d'));
                    $course_users = Database::get()->queryArray("SELECT course_user.user_id as id, user.email as email
                        FROM course_user, user
                        WHERE course_id = ?d AND course_user.user_id = user.id AND user.id IN ($placeholders)",
                        $course_id, $_POST['recipients']);
                }
                foreach ($course_users as $user) {
                    $total++;
                    if (!valid_email($user->email)) {
                        // email is unset or email syntax is invalid
                        $invalid++;
                    } elseif (get_user_email_notification($user->id, $course_id)) {
                        // email notifications are enabled so add to recipients
                        $recipients[] = $user->email;
                    } else {
                        // email notifications are disabled for this user
                        $disabled++;
                    }
                    // send mail message per 50 recipients
                    if (count($recipients) >= 50) {
                        send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent);
                        $recipients = [];
                    }
                };
                if (count($recipients) > 0) {
                    send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent);
                }
                Session::Messages("$langAnnAddWithEmail $total $langRegUser", 'alert-success');
                $notices = [];
                if ($invalid > 0) { // info about invalid emails (if exist)
                    $notices[] = "$langInvalidEmailRecipients: $invalid";
                }
                if ($disabled > 0) { // info about users with disabled emails
                    $notices[] = "$langDisabledEmailRecipients: $disabled";
                }
                Session::Messages($notices, 'alert-warning');
            }
        } else {
            Session::Messages($message, 'alert-success');
        }
        redirect_to_home_page("modules/announcements/index.php?course=$course_code");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page($announceEditUrl);
    }
} else {
    redirect_to_home_page("modules/announcements/index.php?course=$course_code");
}
