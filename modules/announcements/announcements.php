<?php 
 /*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | $Id$        |
      +----------------------------------------------------------------------+
      |    This program is free software; you can redistribute it and/or     |
      |    modify it under the terms of the GNU General Public License       |
      |    as published by the Free Software Foundation; either version 2    |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GPL license is also available through the     |
      |   world-wide-web at http://www.gnu.org/copyleft/gpl.html             |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */
/*
 * Originally written by Thomas Depraetere <depraetere@ipm.ucl.ac.be> 15 January 2002.
 * Partially rewritten by Hugues Peeters <peeters@ipm.ucl.ac.be> 19 April 2002.
 *
 * The script works with the 'annonces' tables in the main claroline table
 *
 * DB Table structure:
 * ---
 *
 * id         : announcement id
 * contenu    : announcement content
 * temps      : date of the announcement introduction / modification
 * code_cours : course code of wich the announcement is linked
 * ordre      : order of the announcement display
 *              (the announcements are display in desc order)
 *
 * Script Structure:
 * ---
 *
 * - Teacher only section
 *
 *		commands
 *			move up and down announcement
 *			delete announcement
 *			delete all announcements
 *			modify announcement
 *			submit announcement (new or modified)
 *
 *		display
 *			casual message (often used after the execution of a command)
 *			announcement list
 *			form to fill new or modified announcement
 *
 * - Student section (only display)
 *
 */


$require_current_course = TRUE;
$require_help = TRUE;
$langFiles = 'announcements';
$helpTopic = 'Announce';
include('../../include/init.php');
include('../../include/lib/textLib.inc.php'); 
include('../../include/sendMail.inc.php');

begin_page($langAn);

if ($language == 'greek')
        $lang_editor='gr';
else
        $lang_editor='en';

?>
<script type="text/javascript">
  _editor_url = '<?= $urlAppend ?>/include/htmlarea/';
  _css_url='<?= $urlAppend ?>/css/';
  _image_url='<?= $urlAppend ?>/include/htmlarea/images/';
  _editor_lang = '<?= $lang_editor ?>';
</script>
<script type="text/javascript" src='<?= $urlAppend ?>/include/htmlarea/htmlarea.js'></script>

<script type="text/javascript">
var editor = null;

function initEditor() {

  var config = new HTMLArea.Config();
  config.height = '180px';
  config.hideSomeButtons(" showhelp undo redo popupeditor ");

  editor = new HTMLArea("ta",config);

  // comment the following two lines to see how customization works
  editor.generate();
  return false;
}

</script>

<body onload="initEditor()">

<?

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
			$displayAnnouncementList = false;
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
		echo "
			<font face='arial, helvetica' size=2>
				".$message.".
				<br>
				<br>
				<a href=\"$_SERVER[PHP_SELF]\">".$langBackList."</a>
				<br>
			</font>";
		$displayAnnouncementList = false;
		$displayForm             = false;
	}



		/*----------------------------------------
		   DISPLAY FORM TO FILL AN ANNOUNCEMENT
			   (USED FOR ADD AND MODIFY)
		 --------------------------------------*/

	if ($displayForm ==  true)
	{

		// DISPLAY ADD ANNOUNCEMENT COMMAND
		
	echo "<font face='arial, helvetica' size=2>
	<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n";
		// should not send email if updating old message
		if (isset ($modify) && $modify) {
			echo  "<font face='arial, helvetica' size=2>$langModifAnn";
		} else {
			echo "<b>
			".$langAddAnn."</b>
			<br>
			<font face='arial, helvetica' size=2>
				$langEmailOption : 
				<input type=checkbox value=\"1\" name=\"emailOption\">";
		}
		
		if (!isset($AnnouncementToModify) )
			$AnnouncementToModify ="";
		if (!isset($contentToModify) )
			$contentToModify ="";



	echo "<textarea id='ta' name='newContent' value='$contentToModify' rows='20' cols='70'>$contentToModify</textarea>";
	echo "<br><input type=\"hidden\" name=\"id\" value=\"".$AnnouncementToModify."\">";
	echo "<br><input type=\"Submit\" name=\"submitAnnouncement\" value=\"$langOk\"></form>";
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
		echo "<table width=600 cellpadding=1 cellspacing=2 border=0>";
		while ( $myrow = mysql_fetch_array($result) )
		{
			// FORMAT CONTENT
			$content = make_clickable($myrow['contenu']);
			$content = nl2br($content);
			echo "
				<tr bgcolor=$color2>
					<td rowspan=2 width=579>
						<font size=2 face='arial, helvetica'>
							".$langPubl." 
							: 
							".$myrow['temps']."
						</font>
					</td>
					<td width=21 bgcolor=white>";
			/*** DISPLAY MOVE UP AND MOVE DOWN COMMANDS ***/
			// DISPLAY MOVE UP COMMAND
			//condition: only if it is not the top announcement
			if($iterator != 1)
			{
				echo "
						<a href=\"$_SERVER[PHP_SELF]?up=".$myrow["id"]."\">
							<img src=../../images/up.gif border=0 alt=\"Up\">
						</a>";
			}
			echo "
					</td>
				</tr>
				<tr bgcolor=$color2>
					<td width=21 bgcolor=white>";
			// DISPLAY MOVE DOWN COMMAND
			// condition: only if it is not the bottom announcement
			if($iterator < $bottomAnnouncement)
			{
				echo "
						<a href=\"$_SERVER[PHP_SELF]?down=".$myrow["id"]."\">
							<img src=../../images/down.gif border=0 alt=\"Down\">
						</a>";
			}

			// DISPLAY ANNOUNCEMENT CONTENT
			echo "
					</td>
				</tr>
				<tr>
					<td colspan=2>
						<font face='arial, helvetica' size=2>
							".$content."
							<br>
							<a href=\"$_SERVER[PHP_SELF]?modify=".$myrow['id']."\">
								".$langModify."
							</a>
							&nbsp;
							|
							&nbsp;
							<a href=\"$_SERVER[PHP_SELF]?delete=".$myrow['id']."\">".
								$langDelete."
							</a>
							<br>
							<br>
						</font>
					</td>
				</tr>";
			$iterator ++;
		}	// end while ($myrow = mysql_fetch_array($result))
			echo "
			</table>";
		// DISPLAY DELETE ALL ANNOUNCEMENTS COMMAND
		if ($announcementNumber > 1)
		{
			echo "
			<table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
				<tr>
					<td align=\"right\">
						<p>
						<font face=\"arial, helvetica\" size=\"2\">
							<a href=\"$_SERVER[PHP_SELF]?deleteAllAnnouncement=1\">$langEmptyAnn</a>
						</font>
						</p>
					</td>
				</tr>
			</table>";
		}	// if announcementNumber > 1
	}	// end: if ($displayAnnoucementList == true)
} // end: teacher only


/*****************************************
             STUDENT VIEW
*****************************************/

else
{
	$result = db_query("SELECT * FROM annonces WHERE code_cours='$currentCourseID' 
				ORDER BY ordre DESC",$mysqlMainDb) OR die("DB problem");

	echo "<table width=\"600\" cellpadding=\"2\" cellspacing=\"4\" border=\"0\">";
	while ($myrow = mysql_fetch_array($result))
	{	
		$content = $myrow[1];
		$content = make_clickable($content);
		$content = nl2br($content);

		echo "
				<tr>
					<td bgcolor=\"$color2\">
						<font size=\"2\" face=\"arial, helvetica\">
							$langPubl : ".$myrow["temps"]."
						</font>
					</td>
				</tr>
				<tr>
					<td>
						<font size=\"2\" face=\"arial, helvetica\">
							$content
						</font>
					</td>
				</tr>";
		
	}	// while loop
	echo "</table>";
}

?>
			<br>
			<hr noshade size="1">
		</td>
	</tr>
</table>
</body>
</html>
