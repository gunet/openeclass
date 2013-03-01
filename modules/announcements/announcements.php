<?php
/* ========================================================================
 * Open eClass 2.6
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
require_once 'preview.php';

// The following is added for statistics purposes
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_ANNOUNCE');

define('RSS', 'modules/announcements/rss.php?c='.$currentCourseID);

$fake_code = course_id_to_fake_code($cours_id);
$nameTools = $langAnnouncements;

load_modal_box();
if ($is_editor) {
	load_js('tools.js');
	$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
			 $langEmptyAnTitle . '";</script>';

	$result = db_query("SELECT count(*) FROM annonces WHERE cours_id = $cours_id", $mysqlMainDb);

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
	    $result = db_query("SELECT id, ordre FROM annonces WHERE cours_id = $cours_id
		ORDER BY ordre $sortDirection", $mysqlMainDb);
		while (list ($announcementId, $announcementOrder) = mysql_fetch_row($result)) {
			if (isset($thisAnnouncementOrderFound) && $thisAnnouncementOrderFound == true) {
			    $nextAnnouncementId = $announcementId;
			    $nextAnnouncementOrder = $announcementOrder;
			    db_query("UPDATE annonces SET ordre = '$nextAnnouncementOrder' WHERE id = '$thisAnnouncementId'", $mysqlMainDb);
			    db_query("UPDATE annonces SET ordre = '$thisAnnouncementOrder' WHERE id = '$nextAnnouncementId'", $mysqlMainDb);
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
	    $result = db_query("UPDATE annonces SET visibility = 'v' WHERE id = '$mkvis'", $mysqlMainDb);
	}
	if ($_GET['vis'] == 0) {
	    $result = db_query("UPDATE annonces SET visibility = 'i' WHERE id = '$mkvis'", $mysqlMainDb);
	}
    }
    /* delete */
    if (isset($_GET['delete'])) {
	$delete = intval($_GET['delete']);
        $result = db_query("DELETE FROM annonces WHERE id='$delete'", $mysqlMainDb);
        $message = "<p class='success'>$langAnnDel</p>";
    }

    /* modify */
    if (isset($_GET['modify'])) {
        $modify = intval($_GET['modify']);
        $result = db_query("SELECT * FROM annonces WHERE id='$modify'", $mysqlMainDb);
        $myrow = mysql_fetch_array($result);
        if ($myrow) {
            $AnnouncementToModify = $myrow['id'];
	    $contentToModify = $myrow['contenu'];
            $titleToModify = q($myrow['title']);
        }
    }

    /* submit */
    if (isset($_POST['submitAnnouncement'])) {
        // modify announcement
        $antitle = autoquote($_POST['antitle']);
        $newContent = autoquote(purify($_POST['newContent']));
        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            db_query("UPDATE annonces SET contenu = $newContent,
                             title = $antitle, temps = NOW()
			WHERE id = $id AND cours_id = $cours_id");
            $message = "<p class='success'>$langAnnModify</p>";
        } else { // add new announcement
            $result = db_query("SELECT MAX(ordre) FROM annonces
				WHERE cours_id = $cours_id", $mysqlMainDb);
            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;
            // insert
            db_query("INSERT INTO annonces SET contenu = $newContent,
                                  title = $antitle, temps = NOW(),
                                  cours_id = $cours_id, ordre = $order,
                                  visibility = 'v'");
            $id = mysql_insert_id();
        }
        create_preview($_POST['newContent'], false, $id, $cours_id, $code_cours);

        // send email
        if (isset($_POST['emailOption']) and $_POST['emailOption']) {
            $emailContent = "$professorMessage: $_SESSION[prenom] $_SESSION[nom]<br>\n<br>\n".
			     autounquote($_POST['antitle']) .
                            "<br>\n<br>\n" .
                            autounquote($_POST['newContent']);
            $emailSubject = "$intitule ($fake_code)";
            // select students email list
            $sqlUserOfCourse = "SELECT cours_user.user_id, user.email FROM cours_user, user
                                WHERE cours_id = $cours_id
                                AND cours_user.user_id = user.user_id";
            $result = db_query($sqlUserOfCourse, $mysqlMainDb);

            $countEmail = mysql_num_rows($result); // number of mail recipients

            $invalid = 0;
	    $recipients = array();
            $emailBody = html2text($emailContent);
            $linkhere = "&nbsp;<a href='${urlServer}modules/profile/emailunsubscribe.php?cid=$cours_id'>$langHere</a>.";
            $unsubscribe = "<br /><br />".sprintf($langLinkUnsubscribe, q($intitule));
            $emailContent .= $unsubscribe.$linkhere;
            $general_to = 'Members of course ' . $currentCourseID;
            while ($myrow = mysql_fetch_array($result)) {
                    $emailTo = $myrow["email"];
                    $user_id = $myrow["user_id"];
                    // check email syntax validity
                    if (!email_seems_valid($emailTo)) {
                            $invalid++;
                    } elseif (get_user_email_notification($user_id, $cours_id)) {
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
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=".$code_cours."' onsubmit=\"return checkrequired(this, 'antitle');\">
        <fieldset>
        <legend>$langAnnouncement</legend>
	<table class='tbl' width='100%'>";
        if (isset($_GET['modify'])) {
            $langAdd = $nameTools = $langModifAnn;
        } else {
	    $nameTools = $langAddAnn;
        }
	$navigation[] = array("url" => "announcements.php?course=$code_cours", "name" => $langAnnouncements);
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
          <td class='right'><input type='submit' name='submitAnnouncement' value='".q($langAdd)."' /></td>
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
	    <li><a href='" . $_SERVER['SCRIPT_NAME'] . "?course=" .$code_cours . "&amp;addAnnounce=1'>" . $langAddAnn . "</a></li>
	  </ul>
	</div>";
    }
} // end: teacher only

    /* display announcements */
	if ($is_editor) {
		if (isset($_GET['an_id'])) {
			$result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id AND id = ". intval($_GET['an_id']), $mysqlMainDb);
		} else {
			$result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id ORDER BY ordre DESC", $mysqlMainDb);
		}
	} else {
		if (isset($_GET['an_id'])) {
			$result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id AND id = ". intval($_GET['an_id']) ." AND visibility = 'v'", $mysqlMainDb);
		} else {
			$result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id AND visibility = 'v' ORDER BY ordre DESC", $mysqlMainDb);
		}
	}
        $iterator = 1;
        $bottomAnnouncement = $announcementNumber = mysql_num_rows($result);

	$tool_content .= "
        <script type='text/javascript' src='../auth/sorttable.js'></script>
        <table width='100%' class='sortable' id='t1'>";
	if ($announcementNumber > 0) {
		$tool_content .= "<tr><th colspan='2'>$langAnnouncement</th>";
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
                $myrow['temps'] = claro_format_locale_date($dateFormatLong, strtotime($myrow['temps']));
		if ($is_editor) {
		    if ($myrow['visibility'] == 'i') {
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
			<img style='padding-top:3px;' src='$themeimg/arrow.png' alt=''></td>
			<td><b>";
		if (empty($myrow['title'])) {
		    $tool_content .= $langAnnouncementNoTille;
		} else {
		    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;an_id=$myrow[id]'>".q($myrow['title'])."</a>";
		}
		$tool_content .= "</b><div class='smaller'>" . nice_format($myrow["temps"]). "</div>";
		if (isset($_GET['an_id'])) {
			$navigation[] = array('url' => "announcements.php?course=$code_cours", 'name' => $langAnnouncements);
			$nameTools = q($myrow['title']);
			$tool_content .= standard_text_escape($myrow['contenu']);
		} else {
                        if (isset($myrow['preview']) and !empty($myrow['preview'])) {
                                $tool_content .= $myrow['preview'];
                        } else {
                                $preview = standard_text_escape(ellipsize($myrow['contenu'], PREVIEW_SIZE, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;an_id=$myrow[id]'> <span class='smaller'>[$langMore]</span></a></strong>"));
                                db_query('UPDATE annonces SET preview = ' . autoquote($preview) . '
                                                 WHERE id = ' . $myrow['id']);
                                $tool_content .= $preview;
                        }
		}
		$tool_content .= "</td>";

		if ($is_editor) {
			$tool_content .= "
			<td width='70' class='right'>
			      <a href='$_SERVER[SCRIPT_NAME]?course=".$code_cours ."&amp;modify=" . $myrow['id'] . "'>
			      <img src='$themeimg/edit.png' title='" . q($langModify) . "' /></a>&nbsp;
			      <a href='$_SERVER[SCRIPT_NAME]?course=".$code_cours ."&amp;delete=" . $myrow['id'] . "' onClick=\"return confirmation('$langSureToDelAnnounce');\">
			      <img src='$themeimg/delete.png' title='" . q($langDelete) . "' /></a>&nbsp;
			      <a href='$_SERVER[SCRIPT_NAME]?course=".$code_cours ."&amp;mkvis=$myrow[id]&amp;vis=$visibility'>
			      <img src='$themeimg/$vis_icon' title='".q($langVisible)."' /></a>
			</td>";
			if ($announcementNumber > 1)  {
				$tool_content .= "<td align='center' width='35' class='right'>";
			}
			if ($iterator != 1)  {
			    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=".$code_cours ."&amp;up=" . $myrow["id"] . "'>
			    <img class='displayed' src='$themeimg/up.png' title='" . q($langMove) ." ". q($langUp) . "' />
			    </a>";
			}
			if ($iterator < $bottomAnnouncement) {
			    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=".$code_cours ."&amp;down=" . $myrow["id"] . "'>
			    <img class='displayed' src='$themeimg/down.png' title='" . q($langMove) ." ". q($langDown) . "' />
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
