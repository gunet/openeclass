<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/*
 * Groups Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This module is responsible for the user groups of each lesson
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

$group_id = intval($_REQUEST['group_id']);

$nameTools = $langEmailGroup;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langGroupSpace,
    'url' => "group_space.php?group_id=$group_id", 'name' => $langGroupSpace);

$tutor_id = Database::get()->querySingle("SELECT is_tutor FROM group_members WHERE group_id = ?d", $group_id)->is_tutor;
$is_tutor = ($tutor_id == 1);

if (!$is_editor and ! $is_tutor) {
    header('Location: group_space.php?course=' . $course_code . '&group_id=' . $group_id);
    exit;
}

if ($is_editor or $is_tutor) {
    if (isset($_POST['submit'])) {
        $sender = Database::get()->querySingle("SELECT email, surname, givenname FROM user
                                                             WHERE id = ?d", $uid);
        $sender_name = $sender->givenname . ' ' . $sender->surname;
        $sender_email = $sender->email;
        $emailsubject = $title . " - " . $_POST['subject'];
        $emailbody = "$_POST[body_mail]\n\n$langSender: $sender->surname $sender->givenname <$sender->email>\n$langProfLesson\n";
        $req = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $group_id);
        foreach ($req as $userid) {
            $email = Database::get()->querySingle("SELECT email FROM user WHERE id = $userid->user_id")->email;
            if (get_user_email_notification($userid->user_id, $course_id)) {
                $linkhere = "&nbsp;<a href='${urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
                $unsubscribe = "<br /><br />$langNote: " . sprintf($langLinkUnsubscribe, $title);
                $emailbody .= $unsubscribe . $linkhere;
                if (email_seems_valid($email) and ! send_mail($sender_name, $sender_email, '', $email, $emailsubject, $emailbody, $charset)) {
                    $tool_content .= "<h4>$langMailError</h4>";
                }
            }
        }
        // aldo send email to professor
        send_mail($sender_name, $sender_email, '', $sender_email, $emailsubject, $emailbody, $charset);
        $tool_content .= "<div class='alert alert-success'>$langEmailSuccess<br>";
        $tool_content .= "<a href='index.php?course=$course_code'>$langBack</a></div>";
    } else {
        $tool_content .= "
                <form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
                <fieldset>
                <legend>$langTypeMessage</legend>
                <input type='hidden' name='group_id' value='$group_id'>
                <table width='99%' class='FormData'>
                <thead>
                <tr>
                  <td class='left'>$langMailSubject</td></tr>
                </tr>
                <tr>
                    <td><input type='text' name='subject' size='58' class='FormData_InputText'></input></td>
                </tr>
                <tr>
                  <td class='left'>$langMailBody</td>
                </tr>
                <tr>
                  <td><textarea name='body_mail' rows='10' cols='73' class='FormData_InputText'></textarea></td>
                </tr>
                <tr>
                  <td><input type='submit' name='submit' value='$langSend'></input></td>
                </tr>
                </thead>
                </table>
                </fieldset>
                 </form>";
    }
}
draw($tool_content, 2);
