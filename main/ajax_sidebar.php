<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * @file ajax_sidebar.php
 * @brief Sidebar AJAX handler
 */

$require_login = true;

require_once '../include/baseTheme.php';
require_once 'perso_functions.php';

header('Content-Type: application/json');

function getSidebarMessages() {
           
    global $uid, $urlServer, $langFrom, $dateFormatLong;
    
    $message_content = '';    
               
    $mbox = new Mailbox($uid, 0);
    $msgs = $mbox->getInboxMsgs('', 5);
    foreach ($msgs as $message) {
        if ($message->course_id > 0) {
            $course_title = q(ellipsize(course_id_to_title($message->course_id), 30));
        } else {
            $course_title = '';
        }
        $is_read = $message->is_read ? 'read-msg' : 'unread-msg' ;
        $message_date = claro_format_locale_date($dateFormatLong, $message->timestamp);
        $message_content .= "<li class='list-item'>
                            <span class='item-wholeline'>                                    
                                <div class='text-title class='$is_read''>$langFrom ".display_user($message->author_id, false, false).":<br>
                                    <a href='{$urlServer}modules/dropbox/index.php?mid=$message->id'>" .q($message->subject)."</a>
                                </div>                                    
                                <div class='text-grey'>$course_title</div>
                                <div>$message_date</div>
                                </span>
                            </li>";
    }    
    return $message_content;
    
}

$json_obj = array('messages' => getSidebarMessages());
echo json_encode($json_obj);