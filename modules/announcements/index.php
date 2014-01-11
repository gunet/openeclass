<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * Announcements Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * @abstract This component offers several operations regarding a course's announcements.
 * The course administrator can:
 * 1. Re-arrange the order of the announcements
 * 2. Delete announcements (one by one or all at once)
 * 3. Modify existing announcements
 * 4. Add new announcements
 */

$require_current_course = true;
$require_help = true;
$helpTopic = 'Announce';
$guest_allowed = true;

include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.php';
require_once 'modules/search/announcementindexer.class.php';
// The following is added for statistics purposes
require_once 'include/action.php';

$action = new action();
$action->record(MODULE_ID_ANNOUNCE);

define('RSS', 'modules/announcements/rss.php?c=' . $course_code);
$public_code = course_id_to_public_code($course_id);
$nameTools = $langAnnouncements;

ModalBoxHelper::loadModalBox();
if ($is_editor) {
    load_js('tools.js');
    $head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
            $langEmptyAnTitle . '";</script>';
    $aidx = new AnnouncementIndexer();

    $announcementNumber = Database::get()->querySingle("SELECT COUNT(*) AS count FROM announcement WHERE course_id = ?", $course_id)->count;

    $displayForm = true;
    /* up and down commands */
    if (isset($_GET['down'])) {
        $thisAnnouncementId = intval($_GET['down']);
        $sortDirection = 'DESC';
    }
    if (isset($_GET['up'])) {
        $thisAnnouncementId = intval($_GET['up']);
        $sortDirection = 'ASC';
    }

    $thisAnnouncementOrderFound = false;
    if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
        //Debug::setLevel(Debug::INFO);
        $ids = Database::get()->queryArray("SELECT id, `order` FROM announcement
                                           WHERE course_id = ?
                                           ORDER BY `order` $sortDirection",$course_id );
        foreach ($ids as $announcement) {   
            if ($thisAnnouncementOrderFound) {
                $nextAnnouncementId = $announcement->id;
                $nextAnnouncementOrder = $announcement->order;
                Database::get()->query("UPDATE announcement SET `order` = ? WHERE id = ?", $nextAnnouncementOrder, $thisAnnouncementId);
                Database::get()->query("UPDATE announcement SET `order` = ? WHERE id = ?", $thisAnnouncementOrder, $nextAnnouncementId);
                break;
            }
            // find the order
            if ($announcement->id == $thisAnnouncementId) {
                $thisAnnouncementOrder = $announcement->order;
                $thisAnnouncementOrderFound = true;
            }
       }       
    }
   

    /* modify visibility */
    if (isset($_GET['mkvis'])) {
        $mkvis = intval($_GET['mkvis']);
        $vis = $_GET['vis'] ? 1 : 0;
        Database::get()->query("UPDATE announcement SET visible = ? WHERE id = ?", $vis, $mkvis);
        $aidx->store($mkvis);
    }
    /* delete */
    if (isset($_GET['delete'])) {
        $delete = intval($_GET['delete']);
        $announce = Database::get()->querySingle("SELECT title, content FROM announcement WHERE id = ? ", $delete);
        $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($announce->content)), 50, '+');
        Database::get()->query("DELETE FROM announcement WHERE id = ?", $delete);
        $aidx->remove($delete);
        Log::record($course_id, MODULE_ID_ANNOUNCE, LOG_DELETE, array('id' => $delete,
            'title' => $announce->title,
            'content' => $txt_content));
        $message = "<p class='success'>$langAnnDel</p>";
    }

    /* modify */
    if (isset($_GET['modify'])) {
        $modify = intval($_GET['modify']);
        $announce = Database::get()->querySingle("SELECT * FROM announcement WHERE id=?", $modify);
        if ($announce) {
            $AnnouncementToModify = $announce->id;
            $contentToModify = $announce->content;
            $titleToModify = q($announce->title);
        }
    }

    /* submit */
    if (isset($_POST['submitAnnouncement'])) {
        // modify announcement
        $antitle = $_POST['antitle'];       
        $newContent = purify($_POST['newContent']);
        $send_mail = !!(isset($_POST['emailOption']) and $_POST['emailOption']);
        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE announcement SET content = ?, title = ?, `date` = NOW() WHERE id = ?", $newContent, $antitle, $id);
            $log_type = LOG_MODIFY;
            $message = "<p class='success'>$langAnnModify</p>";
        } else { // add new announcement
            $orderMax = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM announcement
                                                   WHERE course_id = ?", $course_id)->maxorder;
            $order = $orderMax + 1;
            // insert
            $id = Database::get()->query("INSERT INTO announcement
                                         SET content = ?,
                                             title = ?, `date` = NOW(),
                                             course_id = ?, `order` = ?,
                                             visible = 1", $newContent, $antitle, $course_id, $order)->lastInsertID;
            $log_type = LOG_INSERT;
        }
        $aidx->store($id);
        $txt_content = ellipsize_html(canonicalize_whitespace(strip_tags($_POST['newContent'])), 50, '+');
        Log::record($course_id, MODULE_ID_ANNOUNCE, $log_type, array('id' => $id,
            'email' => $send_mail,
            'title' => $_POST['antitle'],
            'content' => $txt_content));

        // send email
        if ($send_mail) {
            $emailContent = "$professorMessage: $_SESSION[givenname] $_SESSION[surname]<br>\n<br>\n" .
                    autounquote($_POST['antitle']) .
                    "<br>\n<br>\n" .
                    autounquote($_POST['newContent']);
            $emailSubject = "$professorMessage ($public_code - $title)";
            // select students email list
            $countEmail = 0;
            $invalid = 0;
            $recipients = array();
            $emailBody = html2text($emailContent);
            $linkhere = "&nbsp;<a href='${urlServer}modules/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
            $unsubscribe = "<br /><br />" . sprintf($langLinkUnsubscribe, $title);
            $emailContent .= $unsubscribe . $linkhere;
            $general_to = 'Members of course ' . $course_code;
            Database::get()->queryFunc("SELECT course_user.user_id as id, user.email as email
                                                   FROM course_user, user
                                                   WHERE course_id = ? AND
                                                         course_user.user_id = user.user_id", function ($person)
                    use (&$countEmail, &$recipients, &$invalid, $course_id, $general_to, $emailSubject, $emailBody, $emailContent, $charset) {
                $countEmail++;
                $emailTo = $person->email;
                $user_id = $person->id;
                // check email syntax validity
                if (!email_seems_valid($emailTo)) {
                    $invalid++;
                } elseif (get_user_email_notification($user_id, $course_id)) {
                    // checks if user is notified by email
                    array_push($recipients, $emailTo);
                }
                // send mail message per 50 recipients
                if (count($recipients) >= 50) {
                    send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent, $charset);
                    $recipients = array();
                }
            }, $course_id);
            if (count($recipients) > 0) {
                send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $general_to, $recipients, $emailSubject, $emailBody, $emailContent, $charset);
            }
            $messageInvalid = " $langOn $countEmail $langRegUser, $invalid $langInvalidMail";
            $message = "<p class='success'>$langAnnAdd $langEmailSent<br />$messageInvalid</p>";
        } else {
            $message = "<p class='success'>$langAnnAdd</p>";
        }
    } // end of if $submit
    // teacher display
    if (isset($message) && $message) {
        $tool_content .= $message . "<br/>";
        $displayForm = false; //do not show form
    }

    /* display form */
    if ($displayForm and (isset($_GET['addAnnounce']) or isset($_GET['modify']))) {
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
        <fieldset>
        <legend>$langAnnouncement</legend>
        <table class='tbl' width='100%'>";
        if (isset($_GET['modify'])) {
            $langAdd = $nameTools = $langModifAnn;
        } else {
            $nameTools = $langAddAnn;
        }
        $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langAnnouncements);
        if (!isset($AnnouncementToModify))
            $AnnouncementToModify = "";
        if (!isset($contentToModify))
            $contentToModify = "";
        if (!isset($titleToModify))
            $titleToModify = "";

        $tool_content .= "
        <tr><th>$langAnnTitle:</th></tr>
        <tr>
          <td><input type='text' name='antitle' value='$titleToModify' size='50' /></td>
        </tr>
        <tr><th>$langAnnBody:</th></tr>
        <tr>
          <td>" . rich_text_editor('newContent', 4, 20, $contentToModify) . "</td>
        </tr>
        <tr>
          <td class='smaller right'>
          <img src='$themeimg/email.png' title='email' /> $langEmailOption: <input type='checkbox' value='1' name='emailOption' /></td>
        </tr>
        <tr>
          <td class='right'><input type='submit' name='submitAnnouncement' value='$langAdd' /></td>
        </tr>
        </table>
        <input type='hidden' name='id' value='$AnnouncementToModify' />
        </fieldset>
        </form>";
    } else {
        /* display actions toolbar */
        $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
            <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addAnnounce=1'>" . $langAddAnn . "</a></li>
          </ul>
        </div>";
    }
} // end: teacher only

/* display announcements */
$limit_sql = ($is_editor ? '' : ' AND visible = 1') .
        (isset($_GET['an_id']) ? ' AND id = ' . intval($_GET['an_id']) : '');
$result = Database::get()->queryArray("SELECT * FROM announcement WHERE course_id = ? " . $limit_sql . " ORDER BY `order` DESC", $course_id);

$iterator = 1;
$bottomAnnouncement = $announcementNumber = count($result);

$tool_content .= "
        <script type='text/javascript' src='../auth/sorttable.js'></script>
        <table width='100%' class='sortable' id='t1'>";
if ($announcementNumber > 0) {
    $tool_content .= "<tr><th colspan='2'>$langAnnouncements</th>";
    if ($announcementNumber > 1) {
        $colsNum = 2;
    } else {
        $colsNum = 2;
    }
    if ($is_editor) {
        $tool_content .= "<th width='60' colspan='$colsNum' class='center'>$langActions</th>";
    }
    $tool_content .= "</tr>";
}
$k = 0;
if ($result)
    foreach ($result as $announce) {
        $content = standard_text_escape($announce->content);
        $announce->date = claro_format_locale_date($dateFormatLong, strtotime($announce->date));
        if ($is_editor) {
            if ($announce->visible == 0) {
                $visibility = 1;
                $vis_icon = 'invisible.png';
                $tool_content .= "<tr class='invisible'>";
            } else {
                $visibility = 0;
                $vis_icon = 'visible.png';
                if ($k % 2 == 0) {
                    $tool_content .= "<tr class='even'>";
                } else {
                    $tool_content .= "<tr class='odd'>";
                }
            }
        }
        $tool_content .= "<td width='16' valign='top'>
			<img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
			<td><b>";
        if (empty($announce->title)) {
            $tool_content .= $langAnnouncementNoTille;
        } else {
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;an_id=$announce->id'>" . q($announce->title) . "</a>";
        }
        $tool_content .= "</b><div class='smaller'>" . nice_format($announce->date) . "</div>";
        if (isset($_GET['an_id'])) {
            $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAnnouncements);
            $nameTools = q($announce->title);
            $tool_content .= $content;
        } else {
            $tool_content .= standard_text_escape(ellipsize_html($content, 500, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;an_id=$announce->id'> <span class='smaller'>[$langMore]</span></a></strong>"));
        }
        $tool_content .= "</td>";

        if ($is_editor) {
            $tool_content .= "
                <td width='70' class='right'>
                      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$announce->id'>
                      <img src='$themeimg/edit.png' title='" . $langModify . "' /></a>&nbsp;
                      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$announce->id' onClick=\"return confirmation('$langSureToDelAnnounce');\">
                      <img src='$themeimg/delete.png' title='" . $langDelete . "' /></a>&nbsp;
                      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;mkvis=$announce->id&amp;vis=$visibility'>
                      <img src='$themeimg/$vis_icon' title='$langVisible' /></a>
                </td>";
            if ($announcementNumber > 1) {
                $tool_content .= "<td align='center' width='35' class='right'>";
            }
            if ($iterator != 1) {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;up=$announce->id'>
			    <img class='displayed' src='$themeimg/up.png' title='" . $langMove . " " . $langUp . "' />
			    </a>";
            }
            if ($iterator < $bottomAnnouncement) {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;down=" . $announce->id . "'>
			    <img class='displayed' src='$themeimg/down.png' title='" . $langMove . " " . $langDown . "' />
			    </a>";
            }
            if ($announcementNumber > 1) {
                $tool_content .= "</td>";
            }
        }
        $tool_content .= "</tr>";
        $iterator ++;
        $k++;
    } // end of while
$tool_content .= "</table>";

if ($announcementNumber < 1) {
    $no_content = true;
    if (isset($_GET['addAnnounce'])) {
        $no_content = false;
    }
    if (isset($_GET['modify'])) {
        $no_content = false;
    }
    if ($no_content) {
        $tool_content .= "<p class='alert1'>$langNoAnnounce</p>\n";
    }
}

add_units_navigation(TRUE);

draw($tool_content, 2, null, $head_content);
