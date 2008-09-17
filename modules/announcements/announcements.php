<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

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
// support for math symbols
include('../../include/phpmathpublisher/mathpublisher.php');

/*
 * *** The following is added for statistics purposes **
 */
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_ANNOUNCE');
/*
 */

$nameTools = $langAnnouncements;
$tool_content = $head_content = "";

if ($is_adminOfCourse && (@$addAnnouce == 1 || isset($modify))) {
    if ($language == 'greek')
        $lang_editor = 'el';
    else
        $lang_editor = 'en';

    $head_content = <<<hContent
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
        _editor_skin = "silva";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>
hContent;
}

/*
 * TEACHER ONLY
 */
if ($is_adminOfCourse) { // check teacher status
        $head_content .= '
<script>
function confirmation (name)
{
	if (name != "all") {
    	if (confirm("' . $langSureToDelAnnounce . ' "+ name + " ?"))
        	{return true;}
    	else
        	{return false;}
	} else {
		if (confirm("' . $langSureToDelAnnounceAll . ' "+" ?"))
        	{return true;}
    	else
        	{return false;}
	}
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

    $result = db_query("SELECT * FROM annonces WHERE code_cours='$currentCourse' ", $mysqlMainDb);
    $announcementNumber = mysql_num_rows($result);
    unset($result);

    /*----------------------------------------
	DEFAULT DISPLAY SETTINGS
	--------------------------------------*/
    $displayAnnouncementList = true;
    $displayForm = true;

    /*----------------------------------------
	MOVE UP AND MOVE DOWN COMMANDS
	--------------------------------------*/
    if (isset($down) && $down) {
        $thisAnnouncementId = $down;
        $sortDirection = "DESC";
    }

    if (isset($up) && $up) {
        $thisAnnouncementId = $up;
        $sortDirection = "ASC";
    }

    if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
        $result = db_query("SELECT id, ordre FROM annonces WHERE code_cours='$currentCourseID'
		ORDER BY ordre $sortDirection", $mysqlMainDb);

        while (list ($announcementId, $announcementOrder) = mysql_fetch_row($result)) {
            if (isset ($thisAnnouncementOrderFound) && $thisAnnouncementOrderFound == true) {
                $nextAnnouncementId = $announcementId;
                $nextAnnouncementOrder = $announcementOrder;
                db_query("UPDATE annonces SET ordre = '$nextAnnouncementOrder' WHERE id = '$thisAnnouncementId'", $mysqlMainDb);
                db_query("UPDATE annonces SET ordre = '$thisAnnouncementOrder' WHERE id = '$nextAnnouncementId'", $mysqlMainDb);
                break;
            }
            // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT
            if ($announcementId == $thisAnnouncementId) {
                $thisAnnouncementOrder = $announcementOrder;
                $thisAnnouncementOrderFound = true;
            }
        }
    }

    /*----------------------------------------
	DELETE ANNOUNCEMENT COMMAND
	--------------------------------------*/

    if (isset($delete) && $delete) {
        $result = db_query("DELETE FROM annonces WHERE id='$delete'", $mysqlMainDb);
        $message = "<p class=\"success_small\">$langAnnDel</p>";
    }

    /*----------------------------------------
	DELETE ALL ANNOUNCEMENTS COMMAND
	--------------------------------------*/

    if (isset($deleteAllAnnouncement) && $deleteAllAnnouncement) {
        db_query("DELETE FROM annonces WHERE code_cours='$currentCourseID'", $mysqlMainDb);
        $message = "<p class=\"success_small\">$langAnnEmpty</p>";
    }

    /*----------------------------------------
	MODIFY COMMAND
	--------------------------------------*/

    if (isset($modify) && $modify) {
        // RETRIEVE THE CONTENT OF THE ANNOUNCEMENT TO MODIFY
        $result = db_query("SELECT * FROM annonces WHERE id='$modify'", $mysqlMainDb);
        $myrow = mysql_fetch_array($result);

        if ($myrow) {
            $AnnouncementToModify = $myrow['id'];
            $contentToModify = q($myrow['contenu']);
            $titleToModify = q($myrow['title']);
            $displayAnnouncementList = true;
        }
    }
    /*----------------------------------------
	SUBMIT ANNOUNCEMENT COMMAND
	--------------------------------------*/

    if (isset($_POST['submitAnnouncement'])) {
        // modify announcement
        if ($id) {
            db_query("UPDATE annonces SET contenu='" . mysql_real_escape_string($newContent) . "',
			title='" . mysql_real_escape_string($antitle) . "', temps=NOW()
			WHERE id='" . mysql_real_escape_string($id) . "'", $mysqlMainDb);
            $message = "<p class=\"success_small\">$langAnnModify</p>";
        }

        // add new announcement
        else {
            // DETERMINE THE ORDER OF THE NEW ANNOUNCEMENT
            $result = db_query("SELECT MAX(ordre) FROM annonces
				WHERE code_cours = '$currentCourseID'", $mysqlMainDb);

            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;
            // INSERT ANNOUNCEMENT
            db_query("INSERT INTO annonces SET contenu = '" . mysql_real_escape_string($newContent) . "',
			title='" . mysql_real_escape_string($antitle) . "', temps = NOW(),
			code_cours = '$currentCourseID', ordre = '$order'");
        } // else
        // SEND EMAIL (OPTIONAL)
        if (isset($_POST['emailOption']) && is_numeric($_POST['emailOption']) && $_POST['emailOption'] == 1) {
            $emailContent = stripslashes($newContent);
            $emailSubject = "$professorMessage ($currentCourseID - $intitule)";
            // Select students email list
            $sqlUserOfCourse = "SELECT user.email FROM cours_user, user WHERE code_cours='$currentCourseID'
			AND cours_user.user_id = user.user_id";
            $result = db_query($sqlUserOfCourse, $mysqlMainDb);

            $countEmail = mysql_num_rows($result);

            $unvalid = 0;
            // send email one by one to avoid antispam
            while ($myrow = mysql_fetch_array($result)) {
                $emailTo = $myrow["email"];
                // check email syntax validity
                if (!email_seems_valid($emailTo)) {
                    $unvalid++;
                } else {
                    // avoid antispam by varying string
                    $emailBody = html2text("$emailContent\n\n$emailTo");
                    send_mail_multipart("$prenom $nom", $email, '',
                        $myrow["email"], $emailSubject,
                        $emailBody, $emailContent, $charset);
                }
            }
            $messageUnvalid = " $langOn $countEmail $langRegUser, $unvalid $langUnvalid";
            $message = "<p class=\"success_small\">$langAnnAdd $langEmailSent<br />$messageUnvalid</p>";
        } // if $emailOption==1
        else {
            $message = "<p class=\"success_small\">$langAnnAdd</p>";
        }
    } // if $submit Announcement


    // teacher display
    /*----------------------------------------
	DISPLAY ACTION MESSAGE
	--------------------------------------*/
    if (isset($message) && $message) {
        $tool_content .= "".$message."<br/>";
        $displayAnnouncementList = true; //do not show announcements
        $displayForm = false; //do not show form
    }


    /*----------------------------------------
	DISPLAY ACTIONS TOOL BAR
	--------------------------------------*/
    $tool_content .= "
      <div id=\"operations_container\">
        <ul id=\"opslist\">
          <li><a href=\"" . $_SERVER['PHP_SELF'] . "?addAnnouce=1\">" . $langAddAnn . "</a></li>";

    if ($announcementNumber > 1 || isset($_POST['submitAnnouncement'])) {
        $tool_content .= "
          <li><a href=\"$_SERVER[PHP_SELF]?deleteAllAnnouncement=1\" onClick=\"return confirmation('all');\">$langEmptyAnn</a></li>";
    }
    $tool_content .= "
        </ul>
      </div>";

    /*----------------------------------------
	DISPLAY FORM TO FILL AN ANNOUNCEMENT
	(USED FOR ADD AND MODIFY)
	--------------------------------------*/

    if ($displayForm == true && (@$addAnnouce == 1 || isset($modify))) {
        // DISPLAY ADD ANNOUNCEMENT COMMAND
        $tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]' onsubmit=\"return checkrequired(this, 'antitle');\">";
        // should not send email if updating old message
        if (isset ($modify) && $modify) {
            $tool_content .= "
            <table width='99%' class='FormData'>
            <tbody>
            <tr>
              <th>&nbsp;</th>
              <td><b>$langModifAnn</b></td>
            </tr>";
            $langAdd = $langModifAnn;
        } else {
		$tool_content .= "<table width='99%' class='FormData' align='center'>
      		<tbody><tr><th width='220'>&nbsp;</th><td><b>" . $langAddAnn . "</b></td></tr>";
        }

        if (!isset($AnnouncementToModify)) $AnnouncementToModify = "";
        if (!isset($contentToModify)) $contentToModify = "";
        if (!isset($titleToModify)) $titleToModify = "";

        $tool_content .= "
      <tr>
        <th width='150' class='left'>$langAnnTitle:</th>";
        $tool_content .= "
        <td><input type='text' name='antitle' value='$titleToModify' size='50' maxlength='50' class='FormData_InputText'></td>
      </tr>";
        $tool_content .= "
      <tr>
        <th class='left'>$langAnnBody:</th>
        <td>&nbsp;</td>
      </tr>";
        $tool_content .= "
      <tr>
        <td colspan='2'><textarea id='xinha' name='newContent' value='$contentToModify' rows='20' cols='90'>$contentToModify</textarea></td>
      </tr>";
        $tool_content .= "
            <input type=\"hidden\" name=\"id\" value=\"" . $AnnouncementToModify . "\">";
        $tool_content .= "
      <tr>
        <th>&nbsp;</th>
        <td><input type=checkbox value=\"1\" name=\"emailOption\"> $langEmailOption </td>
      </tr>";
        $tool_content .= "
      <tr>
        <th>&nbsp;</th>
        <td><input type=\"Submit\" name=\"submitAnnouncement\" value=\"$langAdd\"></td>
      </tr>
      </tbody>
      </table>
      </form>";
        $tool_content .= "
      <br />";
    }

    /*----------------------------------------
	DISPLAY ANNOUNCEMENT LIST
	--------------------------------------*/
    if ($displayAnnouncementList == true) {
        $result = db_query("SELECT * FROM annonces WHERE code_cours='$currentCourse' ORDER BY ordre DESC", $mysqlMainDb);
        $iterator = 1;
        $bottomAnnouncement = $announcementNumber = mysql_num_rows($result);
/*
	if ($announcementNumber > 0) {
		$tool_content .= <<<cData
\n
      <table class="FormData" width="99%">
      <thead>
      <tr>
        <th class="left" width="220">$langAnnouncement</th>
cData;
	$tool_content .= "
          <td class='right'>&nbsp;</td>
          <td width='70' class='right'>$langTools</td>";
		if ($announcementNumber > 1) {
			$tool_content .= "
          <td width='70' class='right'>$langMove</td>";
		}
		$tool_content .= "
        </tr></thead></table>";
	}
*/
	$tool_content .= "
      <table width=\"99%\" align='left' class=\"announcements\">";
	if ($announcementNumber > 0) {
		$tool_content .= <<<cData
\n
      <thead>
      <tr>
        <th class="left" colspan="2"><b>$langAnnouncement</b></th>
cData;
	$tool_content .= "
          <th width='70' class=\"right\"><b>$langTools</b></th>";
		if ($announcementNumber > 1) {
			$tool_content .= "
          <th width='70'><b>$langMove</b></th>";
		}
		$tool_content .= "
        </tr>
        </thead>";
	}
	$tool_content .= "
        <tbody>";
    $k = 0;
	while ($myrow = mysql_fetch_array($result))
		{
            // FORMAT CONTENT
            $content = make_clickable($myrow['contenu']);
            $content = nl2br($content);
            // display math symbols (if there are)
            $content = mathfilter($content, 12, "../../courses/mathimg/");
            $myrow['temps'] = nice_format($myrow['temps']);
            if ($k%2==0) {
	           $tool_content .= "\n      <tr>";
	        } else {
	           $tool_content .= "\n      <tr class=\"odd\">";
            }
            $tool_content .= "
        <td width=\"1\"><img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>
        <td><b>" . $myrow["title"] . "</b>&nbsp;<small>(" . $myrow['temps'] . ")</small>
            <br />$content        </td>
        <td width='70' class='right'>
        <a href=\"$_SERVER[PHP_SELF]?modify=" . $myrow['id'] . "\">
        <img src=\"../../template/classic/img/edit.gif\" border=\"0\" title=\"" . $langModify . "\"></a>
        <a href=\"$_SERVER[PHP_SELF]?delete=" . $myrow['id'] . "\" onClick=\"return confirmation('');\">
        <img src=\"../../template/classic/img/delete.gif\" border=\"0\" title=\"" . $langDelete . "\"></a>
        </td>";

	if ($announcementNumber > 1)  {
		$tool_content .= "<td align='center' width='70' class='right'>";
	}
           // DISPLAY MOVE UP COMMAND
            // condition: only if it is not the top announcement
	if ($iterator != 1)  {
		$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?up=" . $myrow["id"] . "\"><img class=\"displayed\" src=../../template/classic/img/up.gif border=0 title=\"" . $langUp . "\"></a>";
	}
        // DISPLAY MOVE DOWN COMMAND
	if ($iterator < $bottomAnnouncement) {
		$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?down=" . $myrow["id"] . "\"><img class=\"displayed\" src=../../template/classic/img/down.gif border=0 title=\"" . $langDown . "\"></a>";
	}
	if ($announcementNumber > 1) {
		$tool_content .= "</td>";
	}
// DISPLAY ANNOUNCEMENT CONTENT
	$tool_content .= "\n      </tr>";
            $iterator ++;
            $k++;
        } // end while ($myrow = mysql_fetch_array($result))
        $tool_content .= "
      </tbody>
      </table>";
    } // end: if ($displayAnnoucementList == true)
    if ($announcementNumber < 1) {
        $no_content = true;
        if (isset($_REQUEST['addAnnouce'])) {
            $no_content = false;
        }

        if (isset($_REQUEST['modify'])) {
            $no_content = false;
        }

        if ($no_content) $tool_content .= "<p class='alert1'>$langNoAnnounce</p>";
    }
} // end: teacher only
// student view
else {
    $result = db_query("SELECT * FROM annonces WHERE code_cours='$currentCourseID'
	ORDER BY ordre DESC", $mysqlMainDb) OR die("DB problem");
    if (mysql_num_rows($result) > 0) {
        $tool_content .= "
      <table width=\"99%\" align='left' class=\"announcements\">
      <thead>
      <tr>
        <th class=\"left\" colspan=\"2\"><b>$langAnnouncement</b></th>
      </tr>
      </thead>
      <tbody>";

        $k = 0;
        while ($myrow = mysql_fetch_array($result)) {
            $content = $myrow['contenu'];
            $content = make_clickable($content);
            $content = nl2br($content);
            if ($k%2==0) {
	           $tool_content .= "\n      <tr>";
	        } else {
	           $tool_content .= "\n      <tr class=\"odd\">";
            }
            $tool_content .= "
        <td width=\"1\"><img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>
        <td><b>$myrow[title]</b>&nbsp;<small>(" . nice_format($myrow["temps"]) . ")</small><br/>$content        </td>
      </tr>";
      $k++;
        } // while loop
        $tool_content .= "
      </tbody>
      </table>";
    } else {
        $tool_content .= "<p class='alert1'>$langNoAnnounce</p>";
    }
}

if ($is_adminOfCourse) {
    draw($tool_content, 2, 'announcements', $head_content, @$body_action);
} else {
    draw($tool_content, 2, 'announcements');
}
?>
