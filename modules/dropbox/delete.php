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

include '../../include/baseTheme.php';

require_once("class.msg.php");

if (isset($_POST['mid'])) {
    $mid = intval($_POST['mid']);
    $msg = new Msg($mid, $uid);
    if (!$msg->error) {
        $msg->delete();
    }
} elseif (isset($_POST['all_inbox'])) {
    require_once("class.mailbox.php");
    
    if (isset($_POST['course_id'])) {
        $course_id = intval($_POST['course_id']);
    } else {
        $course_id = 0;
    }
    
    $inbox = new Mailbox($uid, $course_id);
    $msgs = $inbox->getInboxMsgs();
    foreach ($msgs as $msg) {
        $msg->delete();
    }
} elseif (isset($_POST['all_outbox'])) {
    require_once("class.mailbox.php");
    
    if (isset($_POST['course_id'])) {
        $course_id = intval($_POST['course_id']);
    } else {
        $course_id = 0;
    }
    
    $outbox = new Mailbox($uid, $course_id);
    $msgs = $outbox->getOutboxMsgs();
    foreach ($msgs as $msg) {
        $msg->delete();
    }
}
