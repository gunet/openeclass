<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
include '../../include/lib/textLib.inc.php';
include '../../include/sendMail.inc.php';
require_once '../video/video_functions.php';
include '../../include/log.php';

// The following is added for statistics purposes
include('../../include/action.php');
$action = new action();
$action->record(MODULE_ID_ANNOUNCE);

define ('RSS', 'modules/announcements/rss.php?c='.$course_code);
$public_code = course_id_to_public_code($course_id);
$nameTools = $langAnnouncements;

load_modal_box();
if ($is_editor) {
	load_js('tools.js');
	$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
			 $langEmptyAnTitle . '";</script>';
	
	$result = db_query("SELECT COUNT(*) FROM announcement WHERE course_id = $course_id");
	
	list($announcementNumber) = mysql_fetch_row($result);
	mysql_free_result($result);
	
	$displayForm = true;
	/* up and down commands */
	if (isset($_GET['down'])) {
		$thisAnnouncementId = $_GET['down'];
		$sortDirection = "DESC";
	}
	if (isset($_GET['up'])) {
		$thisAnnouncementId = $_GET['up'];
		$sortDirection = "ASC";
	}

	if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
	    $result = db_query("SELECT id, `order` FROM announcement WHERE course_id = $course_id
                        ORDER BY `order` $sortDirection");
		while (list ($announcementId, $announcementOrder) = mysql_fetch_row($result)) {
			if (isset($thisAnnouncementOrderFound) && $thisAnnouncementOrderFound == true) {
			    $nextAnnouncementId = $announcementId;
			    $nextAnnouncementOrder = $announcementOrder;
			    db_query("UPDATE announcement SET `order` = '$nextAnnouncementOrder' WHERE id = '$thisAnnouncementId'");
			    db_query("UPDATE announcement SET `order` = '$thisAnnouncementOrder' WHERE id = '$nextAnnouncementId'");
			    break;
			}
			// find the order
			if ($announcementId == $thisAnnouncementId) {
			    $thisAnnouncementOrder = $announcementOrder;
			    $thisAnnouncementOrderFound = true;
			}
		}	
	}

    /* modify visibility */
    if (isset($_GET['mkvis'])) {
	$mkvis = intval($_GET['mkvis']);
	if ($_GET['vis'] == 1) {
	    $result = db_query("UPDATE announcement SET visible = 1 WHERE id = '$mkvis'");
	}
	if ($_GET['vis'] == 0) {
	    $result = db_query("UPDATE announcement SET visible = 0 WHERE id = '$mkvis'");
	}
    }
    /* delete */
    if (isset($_GET['delete'])) {
	$delete = intval($_GET['delete']);
        $row = mysql_fetch_array(db_query("SELECT title, content FROM announcement WHERE id = $delete"));
        $txt_content = ellipsize(canonicalize_whitespace(strip_tags($row['content'])), 50, '+');
        $result = db_query("DELETE FROM announcement WHERE id = $delete");
        Log::record(MODULE_ID_ANNOUNCE, LOG_DELETE,
                    array('id' => $delete,
                         'title' => $row['title'],
                         'content' => $txt_content));
        $message = "<p class='success'>$langAnnDel</p>";
    }

    /* modify */
    if (isset($_GET['modify'])) {
        $modify = intval($_GET['modify']);
        $result = db_query("SELECT * FROM announcement WHERE id='$modify'", $mysqlMainDb);
        $myrow = mysql_fetch_array($result);
        if ($myrow) {
            $AnnouncementToModify = $myrow['id'];
	    $contentToModify = $myrow['content'];
            $titleToModify = q($myrow['title']);
        }
    }
    
    /* submit */
    if (isset($_POST['submitAnnouncement'])) {
        // modify announcement
        $antitle = autoquote($_POST['antitle']);
        $newContent = autoquote(purify($_POST['newContent']));
        $send_mail = !!(isset($_POST['emailOption']) and $_POST['emailOption']);
        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            db_query("UPDATE announcement SET content = $newContent,
			title = $antitle, `date` = NOW()
			WHERE id = $id");
            $log_type = LOG_MODIFY;
            $message = "<p class='success'>$langAnnModify</p>";
        } else { // add new announcement
            $result = db_query("SELECT MAX(`order`) FROM announcement
				WHERE course_id = $course_id");
            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;
            // insert
            db_query("INSERT INTO announcement SET content = $newContent,
                            title = $antitle, `date` = NOW(),
                            course_id = $course_id, `order` = $order,
                            visible = 1");
            $id = mysql_insert_id();
            $log_type = LOG_INSERT;
        }
        $txt_content = ellipsize(canonicalize_whitespace(strip_tags($_POST['newContent'])), 50, '+');
        Log::record(MODULE_ID_ANNOUNCE, $log_type,
                    array('id' => $id,
                          'email' => $send_mail,
                          'title' => $_POST['antitle'],
                          'content' => $txt_content));


        // send email 
        if ($send_mail) {
            $emailContent = "$professorMessage: $_SESSION[prenom] $_SESSION[nom]<br>\n<br>\n".
                            autounquote($_POST['antitle']) .
                            "<br>\n<br>\n" .
                            autounquote($_POST['newContent']);
            $emailSubject = "$professorMessage ($public_code - $title)";
            // select students email list
            $sqlUserOfCourse = "SELECT course_user.user_id, user.email FROM course_user, user
                                WHERE course_id = $course_id 
                                AND course_user.user_id = user.user_id";
            $result = db_query($sqlUserOfCourse);            

            $countEmail = mysql_num_rows($result); // number of mail recipients
            
            $invalid = 0;
	    $recipients = array();
            $emailBody = html2text($emailContent);            
            $linkhere = "&nbsp;<a href='${urlServer}modules/profile/emailunsubscribe.php?cid=$course_id'>$langHere</a>.";
            $unsubscribe = "<br /><br />".sprintf($langLinkUnsubscribe, $title);
            $emailContent .= $unsubscribe.$linkhere;            
            $general_to = 'Members of course ' . $course_code;
            while ($myrow = mysql_fetch_array($result)) {
                    $emailTo = $myrow["email"]; 
                    $user_id = $myrow["user_id"];
                    // check email syntax validity
                    if (!email_seems_valid($emailTo)) {
                            $invalid++;
                    } elseif (get_user_email_notification($user_id, $course_id)) {                                    
                            // checks if user is notified by email
                            array_push($recipients, $emailTo);
                    }                    
                    // send mail message per 50 recipients
                    if (count($recipients) >= 50) {                                                        
                            send_mail_multipart("$_SESSION[prenom] $_SESSION[nom]", $_SESSION['email'],
                                                $general_to,
                                            $recipients, $emailSubject,
                                            $emailBody, $emailContent, $charset);                            
                            $recipients = array();
                    }
            }
            if (count($recipients) > 0)  {                    
                send_mail_multipart("$_SESSION[prenom] $_SESSION[nom]", $_SESSION['email'], $general_to,
                            $recipients, $emailSubject,
                            $emailBody, $emailContent, $charset);                    
            }                        
            $messageInvalid = " $langOn $countEmail $langRegUser, $invalid $langInvalidMail";                
            $message = "<p class='success'>$langAnnAdd $langEmailSent<br />$messageInvalid</p>";
        } // if $emailOption==1
        else {
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
        <form method='post' action='$_SERVER[PHP_SELF]?course=".$course_code."' onsubmit=\"return checkrequired(this, 'antitle');\">
        <fieldset>
        <legend>$langAnnouncement</legend>
	<table class='tbl' width='100%'>";
        if (isset($_GET['modify'])) {
            $langAdd = $nameTools = $langModifAnn;
        } else {
	    $nameTools = $langAddAnn;
        }
	$navigation[] = array("url" => "announcements.php?course=$course_code", "name" => $langAnnouncements);
        if (!isset($AnnouncementToModify)) $AnnouncementToModify = "";
        if (!isset($contentToModify)) $contentToModify = "";
        if (!isset($titleToModify)) $titleToModify = "";

        $tool_content .= "
        <tr>
          <th>$langAnnTitle:</th>
        </tr>
        <tr>
          <td><input type='text' name='antitle' value='$titleToModify' size='50' /></td>
	</tr>
	<tr>
          <th>$langAnnBody:</th>
        </tr>
        <tr>
          <td>".rich_text_editor('newContent', 4, 20, $contentToModify)."</td>
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
	    <li><a href='" . $_SERVER['PHP_SELF'] . "?course=" .$course_code . "&amp;addAnnounce=1'>" . $langAddAnn . "</a></li>
	  </ul>
	</div>";
    }
} // end: teacher only

    /* display announcements */
	if ($is_editor) {
		if (isset($_GET['an_id'])) {
			$result = db_query("SELECT * FROM announcement 
                                                WHERE course_id = $course_id AND 
                                                id = $_GET[an_id]");
		} else {
			$result = db_query("SELECT * FROM announcement 
                                                WHERE course_id = $course_id 
                                                ORDER BY `order` DESC");
		}
	} else {
		if (isset($_GET['an_id'])) {
			$result = db_query("SELECT * FROM announcement 
                                                WHERE course_id = $course_id AND 
                                                id = $_GET[an_id] 
                                                AND visible = 1");
		} else {
			$result = db_query("SELECT * FROM announcement
                                                WHERE course_id = $course_id AND 
                                                visible = 1 
                                                ORDER BY `order` DESC");
		}
	}
        $iterator = 1;
        $bottomAnnouncement = $announcementNumber = mysql_num_rows($result);
 
	$tool_content .= "
        <script type='text/javascript' src='../auth/sorttable.js'></script>
        <table width='100%' class='sortable' id='t1'>";
	if ($announcementNumber > 0) {
		$tool_content .= "<tr><th colspan='2'>$langAnnouncements</th>";
                if ($announcementNumber > 1) {
                    $colsNum= 2;
                } else {
                    $colsNum= 2;
                }
		if ($is_editor) {
		    $tool_content .= "<th width='60' colspan='$colsNum' class='center'>$langActions</th>";
		}
		$tool_content .= "</tr>\n";
	}
	$k = 0;
        while ($myrow = mysql_fetch_array($result)) {
		$content = standard_text_escape($myrow['content']);
		$myrow['date'] = claro_format_locale_date($dateFormatLong, strtotime($myrow['date']));
		if ($is_editor) {
		    if ($myrow['visible'] == 0) {
			$visibility = 1;
			$vis_icon = 'invisible.png';
			$tool_content .= "<tr class='invisible'>";
		    } else {
			$visibility = 0;
			$vis_icon = 'visible.png';
			if ($k%2 == 0) {
			       $tool_content .= "<tr class='even'>";
			} else {
			       $tool_content .= "<tr class='odd'>";
			}
		    }
		}
		$tool_content .= "<td width='16' valign='top'>
			<img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
			<td><b>";
		if (empty($myrow['title'])) {
		    $tool_content .= $langAnnouncementNoTille;
		} else {
		    $tool_content .= "<a href='$_SERVER[PHP_SELF]?course=$course_code&amp;an_id=$myrow[id]'>".q($myrow['title'])."</a>";
		}
		$tool_content .= "</b><div class='smaller'>" . nice_format($myrow["date"]). "</div>";
		if (isset($_GET['an_id'])) {
			$navigation[] = array("url" => "announcements.php?course=$course_code", "name" => $langAnnouncements);
			$nameTools = q($myrow['title']);
			$tool_content .= $content;
		} else {
			$tool_content .= standard_text_escape(ellipsize($content, 250, "<strong>&nbsp;...<a href='$_SERVER[PHP_SELF]?course=$course_code&amp;an_id=$myrow[id]'> <span class='smaller'>[$langMore]</span></a></strong>"));		
		}
		$tool_content .= "</td>";
		
		if ($is_editor) {
			$tool_content .= "
			<td width='70' class='right'>
			      <a href='$_SERVER[PHP_SELF]?course=".$course_code ."&amp;modify=" . $myrow['id'] . "'>
			      <img src='$themeimg/edit.png' title='" . $langModify . "' /></a>&nbsp;
			      <a href='$_SERVER[PHP_SELF]?course=".$course_code ."&amp;delete=" . $myrow['id'] . "' onClick=\"return confirmation('$langSureToDelAnnounce');\">
			      <img src='$themeimg/delete.png' title='" . $langDelete . "' /></a>&nbsp;
			      <a href='$_SERVER[PHP_SELF]?course=".$course_code ."&amp;mkvis=$myrow[id]&amp;vis=$visibility'>
			      <img src='$themeimg/$vis_icon' title='$langVisible' /></a>
			</td>";
			if ($announcementNumber > 1)  {
				$tool_content .= "<td align='center' width='35' class='right'>";
			}
			if ($iterator != 1)  {
			    $tool_content .= "<a href='$_SERVER[PHP_SELF]?course=".$course_code ."&amp;up=" . $myrow["id"] . "'>
			    <img class='displayed' src='$themeimg/up.png' title='" . $langMove ." ". $langUp . "' />
			    </a>";
			}
			if ($iterator < $bottomAnnouncement) {
			    $tool_content .= "<a href='$_SERVER[PHP_SELF]?course=".$course_code ."&amp;down=" . $myrow["id"] . "'>
			    <img class='displayed' src='$themeimg/down.png' title='" . $langMove ." ". $langDown . "' />
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
        $tool_content .= "</table>\n";
    
    if ($announcementNumber < 1) {
        $no_content = true;
        if (isset($_GET['addAnnounce'])) {
            $no_content = false;
        }
        if (isset($_GET['modify'])) {
            $no_content = false;
        }
        if ($no_content) $tool_content .= "<p class='alert1'>$langNoAnnounce</p>\n";
    }
add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
