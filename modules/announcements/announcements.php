<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

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
include('../../include/lib/textLib.inc.php');
include('../../include/sendMail.inc.php');

/*
 * *** The following is added for statistics purposes **
 */
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_ANNOUNCE');
/*
 */

define ('RSS', 'modules/announcements/rss.php?c='.$currentCourseID);
$fake_code = course_id_to_fake_code($cours_id);
$nameTools = $langAnnouncements;

if ($is_adminOfCourse) { // check teacher status
        $head_content .= '
<script type="text/javascript">
function confirmation ()
{
    	if (confirm("' . $langSureToDelAnnounce . ' ?"))
	    {return true;}
    	else
            {return false;}	
}

</script>
';

$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyAnTitle");
		return false;
	} else {
		return true;
	}
}

</script>
hContent;
    if ($is_adminOfCourse) {
	$result = db_query("SELECT count(*) FROM annonces WHERE cours_id = $cours_id", $mysqlMainDb);
    } else {
	$result = db_query("SELECT count(*) FROM annonces WHERE cours_id = $cours_id AND visibility = 'v'", $mysqlMainDb);
    }
    
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
        $newContent = autoquote($_POST['newContent']);
        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            db_query("UPDATE annonces SET contenu = $newContent,
			title = $antitle, temps = NOW()
			WHERE id = $id", $mysqlMainDb);
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
        }

        // send email 
        if (isset($_POST['emailOption']) and $_POST['emailOption']) {
            $emailContent = autounquote($_POST['antitle']) .
                            "<br><br>" .
                            autounquote($_POST['newContent']);
            $emailSubject = "$professorMessage ($fake_code - $intitule)";
            // select students email list
            $sqlUserOfCourse = "SELECT user.email FROM cours_user, user
                                WHERE cours_id = $cours_id AND cours_user.user_id = user.user_id";
            $result = db_query($sqlUserOfCourse, $mysqlMainDb);

            $countEmail = mysql_num_rows($result); // number of mail recipients

            $invalid = 0;
	    $recipients = array();
            $emailBody = html2text($emailContent);
            $general_to = 'Members of course ' . $currentCourseID;
            while ($myrow = mysql_fetch_array($result)) {
                    $emailTo = $myrow["email"]; 
                    // check email syntax validity
                    if (!email_seems_valid($emailTo)) {
                            $invalid++;
                    } else {
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
            $messageUnvalid = " $langOn $countEmail $langRegUser, $invalid $langUnvalid";
            $message = "<p class='success'>$langAnnAdd $langEmailSent<br />$messageUnvalid</p>";
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
        <form method='post' action='$_SERVER[PHP_SELF]' onsubmit=\"return checkrequired(this, 'antitle');\">
        <fieldset>
        <legend>$langAnnouncement</legend>
	<table class='tbl'>";
        if (isset($_GET['modify'])) {
            $langAdd = $nameTools = $langModifAnn;
        } else {
	    $nameTools = $langAddAnn;
        }
	$navigation[] = array("url" => "announcements.php", "name" => $langAnnouncements);
        if (!isset($AnnouncementToModify)) $AnnouncementToModify = "";
        if (!isset($contentToModify)) $contentToModify = "";
        if (!isset($titleToModify)) $titleToModify = "";

        $tool_content .= "
        <tr>
          <td>$langAnnTitle:<br />
	      <input type='text' name='antitle' value='$titleToModify' size='50' /></td>
	</tr>
	<tr>
          <td>$langAnnBody:<br />".rich_text_editor('newContent', 4, 20, $contentToModify)."</td>
        </tr>
	<tr>
          <td><input type='checkbox' value='1' name='emailOption' /> $langEmailOption</td>
        </tr>
	<tr>
          <td><input type='submit' name='submitAnnouncement' value='$langAdd' /></td>
	</tr>
	</table>
	<input type='hidden' name='id' value='$AnnouncementToModify' />
        </fieldset>
	</form>";
    }else{
    /* display actions toolbar */
    $tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='" . $_SERVER['PHP_SELF'] . "?addAnnounce=1'>" . $langAddAnn . "</a></li>
      </ul>
    </div>";
}
} // end: teacher only

    /* display announcements */
	if ($is_adminOfCourse) {
	    $result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id ORDER BY ordre DESC", $mysqlMainDb);
	} else {
	    $result = db_query("SELECT * FROM annonces WHERE cours_id = $cours_id AND visibility = 'v' ORDER BY ordre DESC", $mysqlMainDb);
	}
        $iterator = 1;
        $bottomAnnouncement = $announcementNumber = mysql_num_rows($result);

	$tool_content .= "
        <script type='text/javascript' src='../auth/sorttable.js'></script>
        <table width='100%' class='sortable' id='t1'>";
	if ($is_adminOfCourse) {
		$colspan = 1;
	} else {
		$colspan = 2;
	}
	if ($announcementNumber > 0) {
		$tool_content .= "
        <tr>
          <th>&nbsp;</th>
          <th colspan='$colspan'><div align='left'>$langAnnouncement</th>";
		if ($is_adminOfCourse) {
		    $tool_content .= "
          <th width='70'>$langActions</th>";
		    if ($announcementNumber > 1) {
			    $tool_content .= "
          <th width='70'>$langMove</th>";
		    }
		}
		$tool_content .= "
        </tr>\n";
	}
	$k = 0;
        while ($myrow = mysql_fetch_array($result)) {
            $content = standard_text_escape($myrow['contenu']);
	    $myrow['temps'] = claro_format_locale_date($dateFormatLong, strtotime($myrow['temps']));
	    if ($is_adminOfCourse) {
		if ($myrow['visibility'] == 'i') {
		    $visibility = 1;
		    $vis_icon = 'invisible.png';
		    $classvis = 'invisible';
		} else {
		    $visibility = 0;
		    $vis_icon = 'visible.png';
		    $classvis = 'visible';
		}
	    } else  {
		$classvis = 'visile';
	    }
            if ($k%2 == 0) {
                   $tool_content .= "        <tr class='even'>\n";
                } else {
                   $tool_content .= "        <tr class='odd'>\n";
            }

            $tool_content .= "<td width='1' valign='top' class=$classvis><img style='padding-top:3px;' src='${urlServer}/template/classic/img/arrow.png' title='bullet' /></td>
          <td><b>";
            if (empty($myrow['title'])) {
                $tool_content .= $langAnnouncementNoTille;
            } else {
                $tool_content .= q($myrow['title']);
            }
	    
            $tool_content .= "</b>&nbsp;<small>(" . nice_format($myrow["temps"]). ")</small><br />$content</td>\n";
	    if ($is_adminOfCourse) {
		$tool_content .= "          <td width='70' class='right'>
		<a href='$_SERVER[PHP_SELF]?modify=" . $myrow['id'] . "'>
		<img src='../../template/classic/img/edit.png' title='" . $langModify . "' /></a>&nbsp;
		<a href='$_SERVER[PHP_SELF]?delete=" . $myrow['id'] . "' onClick=\"return confirmation('');\">
		<img src='../../template/classic/img/delete.png' title='" . $langDelete . "' /></a>&nbsp;
		<a href='$_SERVER[PHP_SELF]?mkvis=$myrow[id]&amp;vis=$visibility'>
		<img src='../../template/classic/img/$vis_icon' title='$langVisible' /></a>
		</td>\n";
	    }
	if ($announcementNumber > 1)  {
		$tool_content .= "        <td align='center' width='70' class='right'>";
	}
	if ($is_adminOfCourse) {
	    if ($iterator != 1)  {
		$tool_content .= "<a href='$_SERVER[PHP_SELF]?up=" . $myrow["id"] . "'>
		<img class='displayed' src='../../template/classic/img/up.png' title='" . $langUp . "' /></a>";
	    }
	    if ($iterator < $bottomAnnouncement) {
		$tool_content .= "<a href='$_SERVER[PHP_SELF]?down=" . $myrow["id"] . "'>
		<img class='displayed' src='../../template/classic/img/down.png' title='" . $langDown . "' /></a>";
	    }
	    if ($announcementNumber > 1) {
		    $tool_content .= "</td>\n";
	    }
	}
	$tool_content .= "        </tr>\n";
        $iterator ++;
        $k++;
        } // end of while 
        $tool_content .= "        </table>\n";
    
    if ($announcementNumber < 1) {
        $no_content = true;
        if (isset($_GET['addAnnounce'])) {
            $no_content = false;
        }
        if (isset($_GET['modify'])) {
            $no_content = false;
        }
        if ($no_content) $tool_content .= "    <p class='alert1'>$langNoAnnounce</p>\n";
    }
add_units_navigation(TRUE);
if ($is_adminOfCourse) {
    draw($tool_content, 2, '', $head_content, @$body_action);
} else {
    draw($tool_content, 2);
}
