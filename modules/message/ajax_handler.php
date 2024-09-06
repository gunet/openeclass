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

$require_login = TRUE;
$guest_allowed = FALSE;

if (isset($_GET['course'])) {
    $require_current_course = true;
    $require_user_registration = true;
}

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
}
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'class.msg.php';
require_once 'class.mailbox.php';

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    $message_path = '';
    if (isset($_GET['mbox_type'])) {
        $mbox_type = $_GET['mbox_type'];
    }

    if (isset($_POST['mid'])) {
        $mid = intval($_POST['mid']);
        if (!isset($course_id)) { // messages from user portfolio
            $msg = new Msg($mid, $uid, 'any');
            $course_id = $msg->course_id;
        }
        $inbox = new Mailbox($uid, $course_id);
        $message_path = $inbox->get_mailbox_path();
        $msg = new Msg($mid, $uid, 'any');
        if (!$msg->error) {
            if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
            $msg->delete($message_path);
        }
        exit();
    } elseif (isset($_POST['all_inbox'])) {
        if (!isset($course_id)) {  // messages from user portfolio
            $course_id = 0;
            $inbox = new Mailbox($uid, $course_id);
            $msgs = $inbox->getInboxMsgs();
            foreach ($msgs as $msg) {
                $message_path = $webDir . "/courses/" . course_id_to_code($msg->course_id) . "/dropbox";
                if (!$msg->error) {
                    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
                    $msg->delete($message_path);
                }
            }
        } else {
            $inbox = new Mailbox($uid, $course_id);
            $message_path = $inbox->get_mailbox_path();
            $msgs = $inbox->getInboxMsgs();
            foreach ($msgs as $msg) {
                if (!$msg->error) {
                    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
                    $msg->delete($message_path);
                }
            }
        }
        exit();
    } elseif (isset($_POST['all_outbox'])) {
        if (!isset($course_id)) {  // messages from user portfolio
            $course_id = 0;
            $outbox = new Mailbox($uid, $course_id);
            $message_path = $outbox->get_mailbox_path();
            $msgs = $outbox->getInboxMsgs();
            foreach ($msgs as $msg) {
                $message_path = $webDir . "/courses/" . course_id_to_code($msg->course_id) . "/dropbox";
                if (!$msg->error) {
                    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
                    $msg->delete($message_path);
                }
            }
        } else {
            $outbox = new Mailbox($uid, $course_id);
            $message_path = $outbox->get_mailbox_path();
            $msgs = $outbox->getOutboxMsgs();
            foreach ($msgs as $msg) {
                if (!$msg->error) {
                    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
                    $msg->delete($message_path);
                }
            }
        }
        exit();
    }

    $mbox = new Mailbox($uid, $course_id);

    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);

    //Total records
    $data['iTotalRecords'] = $mbox->MsgsNumber($mbox_type);

    $keyword = $_GET['sSearch'];

    if ($mbox_type == 'inbox') {
        //Total records after applying search filter
        $data['iTotalDisplayRecords'] = count($mbox->getInboxMsgs($keyword));

        $msgs = $mbox->getInboxMsgs($keyword, $limit, $offset);
    } else {
        //Total records after applying search filter
        $data['iTotalDisplayRecords'] = count($mbox->getOutboxMsgs($keyword));

        $msgs = $mbox->getOutboxMsgs($keyword, $limit, $offset);
    }

    $data['aaData'] = array();

    foreach ($msgs as $msg) {
        if ($msg->is_read == 1) {
            $typeText = "TextRegular";
            $bold_start = "";
            $bold_end = "";
            $envelove_icon = "fa-envelope-open";
        } else {
            $typeText = "TextBold";
            $bold_start = "<strong>";
            $bold_end = "</strong>";
            $envelove_icon = "fa-envelope";
        }

        $urlstr = '';
        if ($course_id != 0) {
            $urlstr = "&amp;course=".course_id_to_code($course_id);
        }

        if (($msg->filename != '') and ($msg->filesize != 0)) {
            $ahref = "message_download.php?course=".course_id_to_code($msg->course_id)."&amp;id=".$msg->id;
            $filename = "&nbsp;&nbsp;&#124;&nbsp;&nbsp;" .
                "<a class='outtabs' href='$ahref' target='_blank' aria-label='(opens in a new tab)' title='$langDownload' data-bs-toggle='tooltip' data-bs-placement='top'>" .
                "<span class='fa fa-paperclip'></span></a>" .
                "<span class='smaller'>&nbsp;&nbsp;(".format_file_size($msg->filesize).")</span><br />";
        } else {
            $filename = '';
        }

        $i = 0;

        if ($mbox_type == 'inbox') {
            $td[$i++] = "<i class='fa $envelove_icon' title='".q($msg->subject)."' /></i>&nbsp;&nbsp;$bold_start<a class='$typeText' href='inbox.php?mid=$msg->id".$urlstr."'>".q($msg->subject)."</a>".$bold_end.$filename;
        } else {
            $td[$i++] = "<i class='fa fa-envelope-open' title='".q($msg->subject)."' /></i>&nbsp;&nbsp;<a class='TextRegular' href='outbox.php?mid=$msg->id".$urlstr."'>".q($msg->subject)."</a>".$filename;
        }

        if ($course_id == 0) {
            if ($msg->course_id != 0) {
                $td[$i++] = "$bold_start<a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($msg->course_id)."\">".q(course_id_to_title($msg->course_id))."</a>$bold_end";
            } else {
                $td[$i++] = "";
            }
        }

        if ($mbox_type == 'inbox') {
            $td[$i++] = display_user($msg->author_id, false, false, "outtabs");
        } else {
            $recipients = '';
            foreach ($msg->recipients as $r) {
                if ($r != $msg->author_id) {
                    $recipients .= display_user($r, false, false, "outtabs").' ,&nbsp;';
                }
            }
            $recipients = rtrim($recipients, ',&nbsp;'); // remove the last comma
            $td[$i++] = "<div><p class='recipients'>$recipients</p></div>";
        }
        $td[$i++] = format_locale_date($msg->timestamp, 'short');
        $btn_class = ($mbox_type == 'inbox')? 'delete_in' : 'delete_out';
        $td[$i++] = "<a href='javascript:void(0)' aria-label='$langDelete' class='$btn_class link-delete' data-id='$msg->id'><span class='fa-solid fa-xmark' style='padding-top:8px; font-size:1.2em;'></span></a>";

        if ($course_id == 0) {
            $data['aaData'][] = array(
                    'DT_RowId' => $msg->id,
                    '0' => $td[0],
                    '1' => $td[1],
                    '2' => $td[2],
                    '3' => $td[3],
                    '4' => $td[4]
            );
        } else {
            $data['aaData'][] = array(
                    'DT_RowId' => $msg->id,
                    '0' => $td[0],
                    '1' => $td[1],
                    '2' => $td[2],
                    '3' => $td[3]
            );
        }
    }

    echo json_encode($data);
    exit();
}
