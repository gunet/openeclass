<?php 
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**
 * Announcements Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component offers several operations regarding a course's announcements.
 * The course administrator can:
 * 1. Re-arrange the order of the announcements
 * 2. Delete announcements (one by one or all at once)
 * 3. Modify existing announcements
 * 4. Add new announcements
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$langFiles = 'announcements';
$helpTopic = 'Announce';
$guest_allowed = true;

include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');
include('../../include/sendMail.inc.php');

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_ANNOUNCE');
/**************************************/

$nameTools = $langAn;
$tool_content = "";

if ($is_adminOfCourse && (@$addAnnouce==1 || isset($modify))) {
	if ($language == 'greek')
	$lang_editor='gr';
	else
	$lang_editor='en';

	$head_content = <<<hContent
<script type="text/javascript">
  _editor_url = '$urlAppend/include/htmlarea/';
  _css_url='$urlAppend/css/';
  _image_url='$urlAppend/include/htmlarea/images/';
  _editor_lang = '$lang_editor';
</script>
<script type="text/javascript" src='$urlAppend/include/htmlarea/htmlarea.js'></script>

<script type="text/javascript">
var editor = null;

function initEditor() {

  var config = new HTMLArea.Config();
  config.height = '220px';
  config.hideSomeButtons(" showhelp undo redo popupeditor ");

  editor = new HTMLArea("ta",config);

  // comment the following two lines to see how customization works
  editor.generate();
  return false;
}

</script>
hContent;

	$body_action = "onload=\"initEditor()\"";

}

/*****************************************
TEACHER ONLY
*****************************************/
if($is_adminOfCourse) // check teacher status
{

	/*----------------------------------------
	DEFAULT DISPLAY SETTINGS
	--------------------------------------*/
	$displayAnnouncementList = true;
	$displayForm             = true;

	/*----------------------------------------
	MOVE UP AND MOVE DOWN COMMANDS
	--------------------------------------*/
	if (isset($down) && $down)
	{
		$thisAnnouncementId = $down;
		$sortDirection = "DESC";
	}

	if (isset($up) && $up)
	{
		$thisAnnouncementId = $up;
		$sortDirection = "ASC";
	}

	if (isset($thisAnnouncementId ) && $thisAnnouncementId && isset($sortDirection) && $sortDirection)
	{
		$result = db_query("SELECT id, ordre FROM annonces WHERE code_cours='$currentCourseID'
		ORDER BY ordre $sortDirection",$mysqlMainDb);

		while (list ($announcementId, $announcementOrder) = mysql_fetch_row($result))
		{
			if (isset ($thisAnnouncementOrderFound)&&$thisAnnouncementOrderFound == true)
			{
				$nextAnnouncementId = $announcementId;
				$nextAnnouncementOrder = $announcementOrder;
				db_query("UPDATE annonces SET ordre = '$nextAnnouncementOrder' WHERE id = '$thisAnnouncementId'",$mysqlMainDb);
				db_query("UPDATE annonces SET ordre = '$thisAnnouncementOrder' WHERE id = '$nextAnnouncementId'",$mysqlMainDb);
				break;
			}
			// STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT
			if ($announcementId == $thisAnnouncementId)
			{
				$thisAnnouncementOrder = $announcementOrder;
				$thisAnnouncementOrderFound = true;
			}
		}
	}

	/*----------------------------------------
	DELETE ANNOUNCEMENT COMMAND
	--------------------------------------*/

	if (isset($delete) && $delete)
	{
		$result =  db_query("DELETE FROM annonces WHERE id='$delete'", $mysqlMainDb);
		$message = $langAnnDel;
	}

	/*----------------------------------------
	DELETE ALL ANNOUNCEMENTS COMMAND
	--------------------------------------*/

	if (isset($deleteAllAnnouncement) && $deleteAllAnnouncement) {
		db_query("DELETE FROM annonces WHERE code_cours='$currentCourseID'",$mysqlMainDb);
		$message = $langAnnEmpty;
	}


	/*----------------------------------------
	MODIFY COMMAND
	--------------------------------------*/

	if (isset($modify) && $modify) {
		// RETRIEVE THE CONTENT OF THE ANNOUNCEMENT TO MODIFY
		$result =  db_query("SELECT * FROM annonces WHERE id='$modify'",$mysqlMainDb);
		$myrow = mysql_fetch_array($result);

		if ($myrow) {
			$AnnouncementToModify = $myrow['id'];
			$contentToModify = $myrow['contenu'];
			$displayAnnouncementList = true;
		}

	}
	/*----------------------------------------
	SUBMIT ANNOUNCEMENT COMMAND
	--------------------------------------*/

	if (isset($submitAnnouncement) && $submitAnnouncement)
	{
		/*** MODIFY ANNOUNCEMENT ***/

		if($id) {
			db_query("UPDATE annonces SET contenu='$newContent', temps=NOW()
				WHERE id=$id",$mysqlMainDb);
			$message = $langAnnModify;
		}

		/*** ADD NEW ANNOUNCEMENT ***/
		else
		{
			// DETERMINE THE ORDER OF THE NEW ANNOUNCEMENT

			$result = db_query("SELECT MAX(ordre) FROM annonces
				WHERE code_cours = '$currentCourseID'",$mysqlMainDb);

			list($orderMax) = mysql_fetch_row($result);
			$order = $orderMax + 1;

			// INSERT ANNOUNCEMENT

			db_query("INSERT INTO annonces SET contenu = '$newContent', temps = NOW(),
				code_cours = '$currentCourseID', ordre = '$order'");

			// SEND EMAIL (OPTIONAL)
			// THIS FUNCTION ADDED BY THOMAS MAY 2002
			if(isset($emailOption) && $emailOption==1)
			{
				$emailContent=stripslashes($newContent);
				$emailSubject = "$professorMessage ($currentCourseID - $intitule)";

				// Select students email list
				$sqlUserOfCourse = "SELECT user.email
			FROM cours_user, user WHERE code_cours='$currentCourseID'
				AND cours_user.user_id = user.user_id";
				$result = db_query($sqlUserOfCourse,$mysqlMainDb);

				$countEmail = mysql_num_rows($result);

				// Email syntax test
				$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,5})$";

				$unvalid=0;
				// send email one by one to avoid antispam
				while ( $myrow = mysql_fetch_array($result) )
				{
					$emailTo=$myrow["email"];
					// echo "emailTo : $emailTo<br>";	// testing
					// check email syntax validity
					if(!eregi( $regexp, $emailTo )) {
						$unvalid++;
					} else {
						//avoid antispam by varying string
						$emailBody=html2text("$emailContent\n\n$emailTo");
						send_mail_multipart("$prenom $nom", $email, '',
						$myrow["email"], $emailSubject,
						$emailBody, $emailContent, $charset);
					}
				}
				$messageUnvalid=" $langOn $countEmail $langRegUser, $unvalid $langUnvalid";
				$message = " $langAnnAdd $langEmailSent.
				<br>
				<b>
					$messageUnvalid
				</b>";
				/*
				for ($i = 1; $i <= $countUser; $i++) {
				// $myrow = mysql_fetch_array($result);
				// $emailArray[$i]="$myrow[email]";
				}
				$emailList = implode(",", $emailArray);
				*/
			}       // if $emailOption==1
			else
			{
				$message = "$langAnnAdd";
			}	// else
		}	// else
	}	// if $submit Announcement

	/*****************************************
	TEACHER DISPLAY
	*****************************************/

	/*----------------------------------------
	DISPLAY ACTION MESSAGE
	--------------------------------------*/

	if (isset($message) && $message)
	{
		$tool_content .=  "
					<table>
				<tbody>
					<tr>
						<td class=\"success\">
						$message
					</td>
					</tr>
				</tbody>
			</table><br/>";
		
		$displayAnnouncementList = true;//do not show announcements
		$displayForm             = false;//do not show form
	}



	/*----------------------------------------
	DISPLAY FORM TO FILL AN ANNOUNCEMENT
	(USED FOR ADD AND MODIFY)
	--------------------------------------*/

	if ($displayForm ==  true && (@$addAnnouce==1 || isset($modify))) {

		// DISPLAY ADD ANNOUNCEMENT COMMAND

		$tool_content .=  "
	<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n";
		// should not send email if updating old message
		if (isset ($modify) && $modify) {
			$tool_content .=   "$langModifAnn";
		} else {
			$tool_content .=  "<p><b>
			".$langAddAnn."</b></p>
			<br>
			
				<p>$langEmailOption : 
				<input type=checkbox value=\"1\" name=\"emailOption\"></p>";
		}

		if (!isset($AnnouncementToModify) )
		$AnnouncementToModify ="";
		if (!isset($contentToModify) )
		$contentToModify ="";


		$tool_content .=  "<textarea id='ta' name='newContent' value='$contentToModify' rows='20' cols='96'>$contentToModify</textarea>";
		$tool_content .=  "<br><input type=\"hidden\" name=\"id\" value=\"".$AnnouncementToModify."\">";
		$tool_content .=  "<input type=\"Submit\" name=\"submitAnnouncement\" value=\"$langOk\"></form>";

		$tool_content .= "<br><br><br>";
	}

	/*----------------------------------------
	DISPLAY ANNOUNCEMENT LIST
	--------------------------------------*/

	if ($displayAnnouncementList == true)
	{
		$result = db_query("
			SELECT * FROM annonces WHERE code_cours='$currentCourse' ORDER BY ordre DESC",$mysqlMainDb);
		$iterator = 1;
		$bottomAnnouncement = $announcementNumber = mysql_num_rows($result);
		
		if (@$addAnnouce !=1) {
			$tool_content .= "

			<a href=\"".$_SERVER['PHP_SELF']."?addAnnouce=1\">".$langAddAnn."</a>";
		}
		if (@$addAnnouce!=1 && $announcementNumber >1) {
			$tool_content .= " | ";
		}
		
		if ($announcementNumber > 1)
		{
			$tool_content .=  "
				<a href=\"$_SERVER[PHP_SELF]?deleteAllAnnouncement=1\">$langEmptyAnn</a>
				";
		}	// if announcementNumber > 1
		if (@$addAnnouce !=1 || $announcementNumber > 1) {
			$tool_content .= "<br/><br/>";
		}
		$tool_content .=  "<table width=\"99%\">";
		if ($announcementNumber>0){
			$tool_content .= "<thead>
				<tr><th width=\"99%\">$langAnnouncement</th>";

			if ($announcementNumber>1){
				$tool_content .= "<th width=\"21\">$langMove</th>";
			}
			$tool_content .= "</tr></thead>";
		}
		while ( $myrow = mysql_fetch_array($result) )
		{
			// FORMAT CONTENT
			$content = make_clickable($myrow['contenu']);
			$content = nl2br($content);
			$myrow['temps'] = greek_format($myrow['temps']);
			$tool_content .=  "<tbody>
				<tr class=\"odd\"><span></span>
					<td class=\"arrow\">".$langPubl." : ".$myrow['temps']."</td>";
			if ($announcementNumber>1){
				$tool_content .= "<td width=21>";
			}
			/*** DISPLAY MOVE UP AND MOVE DOWN COMMANDS ***/
			// DISPLAY MOVE UP COMMAND
			//condition: only if it is not the top announcement
			if($iterator != 1)
			{
				$tool_content .=  "
					<a href=\"$_SERVER[PHP_SELF]?up=".$myrow["id"]."\">
						<img class=\"displayed\" src=../../template/classic/img/up.gif border=0 title=\"".$langUp."\">
					</a>";
			}

			// DISPLAY MOVE DOWN COMMAND
			// condition: only if it is not the bottom announcement
			if($iterator < $bottomAnnouncement)
			{
				$tool_content .=  "
					<a href=\"$_SERVER[PHP_SELF]?down=".$myrow["id"]."\">
						<img class=\"displayed\" src=../../template/classic/img/down.gif border=0 title=\"".$langDown."\">
					</a>";
			}

			// DISPLAY ANNOUNCEMENT CONTENT
			$tool_content .= "</td></tr>
				<tr><td colspan=2>".$content."
				<br>
				<a href=\"$_SERVER[PHP_SELF]?modify=".$myrow['id']."\">
				<img src=\"../../images/edit.gif\" border=\"0\" title=\"".$langModify."\"></a>
				<a href=\"$_SERVER[PHP_SELF]?delete=".$myrow['id']."\">
				<img src=\"../../images/delete.gif\" border=\"0\" title=\"".$langDelete."\"></a><br>
				</td></tr>";
			$iterator ++;
		}	// end while ($myrow = mysql_fetch_array($result))
		$tool_content .=  "</tbody>
			</table>";
		// DISPLAY DELETE ALL ANNOUNCEMENTS COMMAND		
	}	// end: if ($displayAnnoucementList == true)
} // end: teacher only

// student view

else {
	$result = db_query("SELECT * FROM annonces WHERE code_cours='$currentCourseID'
				ORDER BY ordre DESC",$mysqlMainDb) OR die("DB problem");

	$tool_content .=  "<table width=\"99%\"";
	while ($myrow = mysql_fetch_array($result))
	{
		$content = $myrow[1];
		$content = make_clickable($content);
		$content = nl2br($content);

		$tool_content .=  "
		<tr class=\"odd\">
			<td>$langPubl : ".greek_format($myrow["temps"])."</td></tr>
			<tr><td>$content</td></tr>";

	}	// while loop
	$tool_content .=  "</table>";
}

if($is_adminOfCourse && (@$addAnnouce == 1 || isset($modify))) {
	draw($tool_content, 2, 'announcements', $head_content, $body_action);
} else {
	draw($tool_content, 2, 'announcements');
}
?>
			
