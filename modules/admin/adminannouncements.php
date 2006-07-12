<?

$langFiles = 'admin';

include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');

check_admin();

$nameTools = $langAdminAn;
$tool_content = "";

/*
if ($is_adminOfCourse && (@$addAnnouce==1 || isset($modify))) {
	if ($language == 'greek')
	$lang_editor='gr';
	else
	$lang_editor='en';

}

*/

// display settings 
		$displayAnnouncementList = true;
	$displayForm = true;

	// delete announcement command 
	if (isset($delete) && $delete) {
		$result =  db_query("DELETE FROM admin_announcements WHERE id='$delete'", $mysqlMainDb);
		$message = $langAdminAnnDel;
	}

	// moddify announcement command 
	if (isset($modify) && $modify) {
		$result =  db_query("SELECT * FROM admin_announcements WHERE id='$modify'",$mysqlMainDb);
		$myrow = mysql_fetch_array($result);

		if ($myrow) {
			$AnnouncementToModify = $myrow['id'];
			$titleToModify = $myrow['gr_title'];
			$contentToModify = $myrow['gr_body'];
			$commentToModify = $myrow['gr_comment'];
			$displayAnnouncementList = true;
		}
	}

	// submit announcement command 
	if (isset($submitAnnouncement) && $submitAnnouncement) {
		// modify announcement 
		if($id) {
			if (isset($visible)) {
				db_query("UPDATE admin_announcements 
					SET gr_title='$title', gr_body='$newContent', gr_comment='$comment', visible='V', date=NOW() WHERE id=$id",$mysqlMainDb);
			} else {
				db_query("UPDATE admin_announcements 
				SET gr_title='$title', gr_body='$newContent', gr_comment='$comment', visible='I', date=NOW() WHERE id=$id",$mysqlMainDb);
				}
			$message = $langAdminAnnModify;
		}
		// add new announcement 
		else {
			// insert announcement 
			if (isset($visible)) {
			db_query("INSERT INTO admin_announcements 
					SET gr_title = '$title', gr_body = '$newContent', gr_comment = '$comment', date = NOW()");
				} else {
			db_query("INSERT INTO admin_announcements 
					SET gr_title = '$title', gr_body = '$newContent', gr_comment = '$comment', visible='I', date = NOW()");
				}
					$message = "$langAdminAnnAdd";
		}	// else
	}	// if $submit announcement

	// 	action message
	
	if (isset($message) && $message) {
		$tool_content .=  "<table><tbody><tr><td class=\"success\">$message</td></tr></tbody></table><br/>";
		$displayAnnouncementList = true;//do not show announcements
		$displayForm  = false;//do not show form
	}

	//	display form
	if ($displayForm ==  true && (@$addAnnouce==1 || isset($modify))) {

		// display add announcement command
		$tool_content .=  "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">";
			if (isset($modify)) {
				$tool_content .= "$langAdminModifAnn";
			} else {
				$tool_content .=  "<p><b>".$langAdminAddAnn."</b></p><br>";
			}
		
		if (!isset($AnnouncementToModify)) $AnnouncementToModify ="";
		if (!isset($contentToModify))	$contentToModify ="";
		if (!isset($titleToModify))	$titleToModify ="";
		if (!isset($commentToModify))	$commentToModify ="";

		$tool_content .= "<table>";
		$tool_content .= "<tr><td>$langAdminAnnTitle</td></tr>";
		@$tool_content .= "<tr><td><input type=\"text\" name='title' value='$titleToModify' size='50'>
		$langAdminAnVis : <input type=checkbox value=\"1\" name=\"visible\" checked></td></tr>";
		$tool_content .= "<tr><td>$langAdminAnnBody</td></tr>";
		@$tool_content .=  "<tr><td><textarea name='newContent' value='$contentToModify' rows='20' cols='96'>$contentToModify</textarea></td></tr>";
		$tool_content .=  "<tr><td><input type=\"hidden\" name=\"id\" value=\"".$AnnouncementToModify."\"></td></tr>";
		$tool_content .= "<tr><td>$langAdminAnnComm</td></tr>";
		@$tool_content .= "<tr><td><textarea name='comment' value='$comment' rows='2' cols='80'>$commentToModify</textarea></td></tr>";	
		$tool_content .=  "<tr><td><input type=\"Submit\" name=\"submitAnnouncement\" value=\"$langOk\"></td></tr></table></form>";
		$tool_content .= "<br><br>";
	}

	// display admin announcements 
		if ($displayAnnouncementList == true) {
			$result = db_query("SELECT * FROM admin_announcements ORDER BY id DESC",$mysqlMainDb);
			$bottomAnnouncement = $announcementNumber = mysql_num_rows($result);
			if (@$addAnnouce !=1) {
					$tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?addAnnouce=1\">".$langAdminAddAnn."</a>";
			}
			$tool_content .=  "<table width=\"99%\">";
			if ($announcementNumber>0) {
				$tool_content .= "<thead><tr><th width=\"99%\">$langAdminAn</th>";
				$tool_content .= "</tr></thead>";
			}
		while ($myrow = mysql_fetch_array($result)) {
			$content = make_clickable($myrow['gr_body']);
			$content = nl2br($content);
			$tool_content .=  "<tbody>
				<tr class=\"odd\"><span></span>
					<td class=\"arrow\"> 
							".$myrow['gr_title']."
			</td>";

			// display announcements content
			$tool_content .=  "</td></tr>
				<tr><td colspan=2>".$content."<br>
				<a href=\"$_SERVER[PHP_SELF]?modify=".$myrow['id']."\">
			<img src=\"../../images/edit.gif\" border=\"0\" alt=\"".$langModify."\"></a>
			<a href=\"$_SERVER[PHP_SELF]?delete=".$myrow['id']."\">
			<img src=\"../../images/delete.gif\" border=\"0\" alt=\"".$langDelete."\"></a>
			<br></td></tr>";
			$tool_content .= "<tr><td>".$myrow['gr_comment']."</td></tr>";
		}	// end while ($myrow = mysql_fetch_array($result))
		$tool_content .=  "</tbody></table>";
	}	// end: if ($displayAnnoucementList == true)


if((@$addAnnouce == 1 || isset($modify))) {
	draw($tool_content, 3, 'announcements', $head_content, $body_action);
} else {
	draw($tool_content, 3, 'admin');
}
?>			
