<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

/**
 * @file dropbox_submit.php
 * @brief Handles actions submitted from the main dropbox page
 */
$require_login = TRUE;
$require_current_course = TRUE;
$guest_allowed = FALSE;
include '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/sendMail.inc.php';
$dropbox_dir = $webDir . "/courses/" . $course_code . "/dropbox";
$thisisJustMessage = FALSE;
// get dropbox quotas from database
$d = mysql_fetch_array(db_query("SELECT dropbox_quota FROM course WHERE code = '$course_code'"));
$diskQuotaDropbox = $d['dropbox_quota'];
$nameTools = $langDropBox;

require_once("dropbox_class.inc.php");

/*
  form submission
 */
if (isset($_POST["submitWork"])) {
    $error = FALSE;
    $errormsg = '';
    if (!isset($_POST['description'])) {
        $error = TRUE;
        $errormsg = $langBadFormData;
    } elseif (empty($_FILES['file']['name'])) {
        $thisisJustMessage = TRUE;
    }
    /*
     * --------------------------------------
     *     FORM SUBMIT : UPLOAD NEW FILE
     * --------------------------------------
     */
    if (!$error) {
        if ($thisisJustMessage) {
            $dropbox_filename = '';
            $dropbox_filesize = 0;
            $newWorkRecipients = $_POST["recipients"];
            if (isset($_POST['message_title']) and $_POST['message_title'] != '') {
                $dropbox_title = $_POST['message_title'];
            } else {
                $dropbox_title = $langMessage;
            }
            new Dropbox_SentWork($uid, $dropbox_title, $_POST['description'], $dropbox_filename, $dropbox_filesize, $newWorkRecipients);
        } else {
            $cwd = getcwd();
            if (is_dir($dropbox_dir)) {
                $dropbox_space = dir_total_space($dropbox_dir);
            }
            $dropbox_filename = php2phps($_FILES['file']['name']);
            $dropbox_filesize = $_FILES['file']['size'];
            $dropbox_filetype = $_FILES['file']['type'];
            $dropbox_filetmpname = $_FILES['file']['tmp_name'];

            validateUploadedFile($_FILES['file']['name'], 1);

            if ($dropbox_filesize + $dropbox_space > $diskQuotaDropbox) {
                $errormsg = $langNoSpace;
                $error = TRUE;
            } elseif (!is_uploaded_file($dropbox_filetmpname)) { // check user found : no clean error msg 
                die($langBadFormData);
            }
            // set title                       
            if (isset($_POST['message_title']) and $_POST['message_title'] != '') {
                $dropbox_title = $_POST['message_title'];
            } else {
                $dropbox_title = $dropbox_filename;
            }
            $format = get_file_extension($dropbox_filename);
            $dropbox_filename = safe_filename($format);
            $newWorkRecipients = $_POST["recipients"];
            //After uploading the file, create the db entries
            if (!$error) {
                $filename_final = $dropbox_dir . '/' . $dropbox_filename;
                move_uploaded_file($dropbox_filetmpname, $filename_final) or die($langUploadError);
                @chmod($filename_final, 0644);
                new Dropbox_SentWork($uid, $dropbox_title, $_POST['description'], $dropbox_filename, $dropbox_filesize, $newWorkRecipients);
            }
            chdir($cwd);
        }        
        if (isset($_POST['mailing']) and $_POST['mailing']) { // send mail to recipients of dropbox file
                $c = course_code_to_title($course_code);
                $subject_dropbox = "$c ($course_code) - $langNewDropboxFile";
                foreach ($newWorkRecipients as $userid) {
                        if (get_user_email_notification($userid, $course_id)) {
                            $linkhere = "&nbsp;<a href='${urlServer}modules/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
                            $unsubscribe = "<br /><br />" . sprintf($langLinkUnsubscribe, $title);
                            $body_dropbox_message = "$langSender: $_SESSION[givenname] $_SESSION[surname] <br /><br /> $dropbox_title <br /><br />" . ellipsize_html($_POST['description'], 50, "...&nbsp;<a href='${urlServer}modules/dropbox/index.php?course=$course_code'>[$langMore]</a>") . "<br /><br />";
                            if ($dropbox_filesize > 0) {
                                    $body_dropbox_message .= "<a href='${urlServer}modules/dropbox/index.php?course=$course_code'>[$langAttachedFile]</a><br />";
                            }
                            $body_dropbox_message .= "$unsubscribe $linkhere";
                            $plain_body_dropbox_message = html2text($body_dropbox_message);
                            $emailaddr = uid_to_email($userid);
                            send_mail_multipart('', '', '', $emailaddr, $subject_dropbox, $plain_body_dropbox_message, $body_dropbox_message, $charset);
                        }
                }
        }
        $tool_content .= "<p class='success'>$langdocAdd<br />";
    } else { //end if(!$error)
        $tool_content .= "<p class='caution'>$errormsg<br />";
    }
    $tool_content .= "<a href='index.php?course=$course_code'>$langBack</a></p><br />";
}

/*
 * delete received or sent files
 */
if (isset($_GET['deleteReceived']) or isset($_GET['deleteSent'])) {

    $dropbox_person = new Dropbox_Person($uid, $is_editor);
    if (isset($_GET['deleteReceived'])) {
        if ($_GET["deleteReceived"] == "all") {
            $dropbox_person->deleteAllReceivedWork();
        } elseif (is_numeric($_GET["deleteReceived"])) {
            $dropbox_person->deleteReceivedWork($_GET['deleteReceived']);
        }
    } else {
        if ($_GET["deleteSent"] == "all") {
            $dropbox_person->deleteAllSentWork();
        } elseif (is_numeric($_GET["deleteSent"])) {
            $dropbox_person->deleteSentWork($_GET['deleteSent']);
        }
    }
    $tool_content .= "<p class='success'>$langDeletedMessage<br /><a href='index.php?course=$course_code'>$langBack</a></p><br />";
} elseif (isset($_GET['AdminDeleteSent'])) {
    $dropbox_person = new Dropbox_Person($uid, $is_editor);
    $dropbox_person->deleteWork($_GET['AdminDeleteSent']);
    $tool_content .= "<p class='success'>$langDelF<br /><a href='index.php?course=$course_code'>$langBack</a></p><br />";
}

draw($tool_content, 2, null, $head_content);
