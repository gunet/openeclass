<?

$langFiles = 'admin';
$require_admin = TRUE;
include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$nameTools = $langAdminAn;
$tool_content = "";

// default language
if (!isset($localize)) $localize='el';

// choose the database tables
if (isset($localize) and $localize == 'en') {


$table_title = 'en_title';
	$_lang_title_ = $langAdminAnnTitleGr;
	$_lang_body_ = $langAdminAnnBodyGr;
	$_lang_comment_ = $langAdminAnnCommGr;
	$_lang_titleen_ = $langAdminAnnTitleEn;
	$_lang_bodyen_ = $langAdminAnnBodyEn;
	$_lang_commenten_ = $langAdminAnnCommEn;
} else {
	$_lang_title_ = $langTitle;
	$_lang_body_ = $langAdminAnnBody;
	$_lang_comment_ = $langComments;
	$_lang_titleen_ = $langAdminAnnTitleEn;
	$_lang_bodyen_ = $langAdminAnnBodyEn;
	$_lang_commenten_ = $langAdminAnnCommEn;
}

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
		$result = db_query("SELECT * FROM admin_announcements WHERE id='$modify'",$mysqlMainDb);
		$myrow = mysql_fetch_array($result);

		if ($myrow) {
			$AnnouncementToModify = $myrow['id'];
			$titleToModify = $myrow['gr_title'];
			$contentToModify = $myrow['gr_body'];
			$commentToModify = $myrow['gr_comment'];
			$titleToModifyEn = $myrow['en_title'];
			$contentToModifyEn = $myrow['en_body'];
			$commentToModifyEn = $myrow['en_comment'];
			$visibleToModify = $myrow['visible'];
			$displayAnnouncementList = true;
		}
	}

	// submit announcement command 
	if (isset($submitAnnouncement) && $submitAnnouncement) {
		// modify announcement 
		if($id) {
			if (isset($visible)) {
				db_query("UPDATE admin_announcements 
					SET gr_title='$title', gr_body='$newContent', gr_comment='$comment', 
					en_title='$title_en', en_body='$newContent_en', en_comment='$comment_en',
					visible='V', date=NOW() WHERE id=$id",$mysqlMainDb);
					
			} else {
				db_query("UPDATE admin_announcements 
					SET gr_title='$title', gr_body='$newContent', gr_comment='$comment', 
					en_title='$title_en', en_body='$newContent_en', en_comment='$comment_en',
					visible='I', date=NOW() WHERE id=$id",$mysqlMainDb);
				}
			$message = $langAdminAnnModify;
		}
		// add new announcement 
		else {
			// insert announcement 
			if (isset($visible)) {
			db_query("INSERT INTO admin_announcements 
					SET gr_title = '$title', gr_body = '$newContent', gr_comment = '$comment', 
					en_title='$title_en', en_body='$newContent_en', en_comment='$comment_en',
					date = NOW()");
				} else {
			db_query("INSERT INTO admin_announcements 
					SET gr_title = '$title', gr_body = '$newContent', gr_comment = '$comment', 
					en_title='$title_en', en_body='$newContent_en', en_comment='$comment_en',
					visible='I', date = NOW()");
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
		$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]?localize=$localize'>";
			if (isset($modify)) {
				$tool_content .= "$langAdminModifAnn";
			} else {
				$tool_content .=  "<p><b>".$langAdminAddAnn."</b></p><br>";
			}
		
		if (!isset($AnnouncementToModify)) $AnnouncementToModify ="";
		if (!isset($contentToModify))	$contentToModify ="";
		if (!isset($titleToModify))	$titleToModify ="";
		if (!isset($commentToModify))	$commentToModify ="";
		// english
		if (!isset($contentToModifyEn))	$contentToModifyEn ="";
		if (!isset($titleToModifyEn))	$titleToModifyEn ="";
		if (!isset($commentToModifyEn))	$commentToModifyEn ="";

		$tool_content .= "<table>";
		$tool_content .= "<tr><td>$_lang_title_</td></tr>";
		@$tool_content .= "<tr><td><input type=\"text\" name='title' value='$titleToModify' size='50'>";
		if (isset($visibleToModify) and $visibleToModify == 'V') 
				$tool_content .= "$langAdminAnVis : <input type=checkbox value=\"1\" name=\"visible\" checked></td></tr>";
		else		
				$tool_content .= "$langAdminAnVis : <input type=checkbox value=\"1\" name=\"visible\"></td></tr>";
		$tool_content .= "<tr><td>$_lang_body_</td></tr>";
		@$tool_content .= "<tr><td><textarea name='newContent' value='$contentToModify' rows='15' cols='96'>$contentToModify</textarea></td></tr>";
		$tool_content .= "<tr><td><input type=\"hidden\" name=\"id\" value=\"".$AnnouncementToModify."\"></td></tr>";
		$tool_content .= "<tr><td>$_lang_comment_</td></tr>";
		@$tool_content .= "<tr><td><textarea name='comment' value='$comment' rows='2' cols='80'>$commentToModify</textarea></td></tr>";	
		// english
		$tool_content .= "<tr><td>$_lang_titleen_</td></tr>";
		@$tool_content .= "<tr><td><input type=\"text\" name='title_en' value='$titleToModifyEn' size='50'></td</tr>";
		$tool_content .= "<tr><td>$_lang_bodyen_</td></tr>";
		@$tool_content .= "<tr><td><textarea name='newContent_en' value='$contentToModifyEn' rows='15' cols='96'>$contentToModifyEn</textarea></td></tr>";
		$tool_content .= "<tr><td>$_lang_commenten_</td></tr>";
		@$tool_content .= "<tr><td><textarea name='comment_en' value='$commentToModifyEn' rows='2' cols='80'>$commentToModifyEn</textarea></td></tr>";	
		$tool_content .= "<tr><td><input type=\"Submit\" name=\"submitAnnouncement\" value=\"$langOk\"></td></tr></table></form>";
		$tool_content .= "<br><br>";
	}

	// display admin announcements 
		if ($displayAnnouncementList == true) {
			$result = db_query("SELECT * FROM admin_announcements ORDER BY id DESC", $mysqlMainDb);
			$announcementNumber = mysql_num_rows($result);
			if (@$addAnnouce != 1) {
					$tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?addAnnouce=1&localize=$localize\">".$langAdminAddAnn."</a>";
			}
			$tool_content .=  "<table width=\"99%\">";
			if ($announcementNumber>0) {
				$tool_content .= "<thead><tr><th width=\"99%\" colspan=\"2\">$langAdminAn</th>";
				$tool_content .= "</tr></thead>";
			}
		while ($myrow = mysql_fetch_array($result)) {
			$content = make_clickable($myrow['gr_body']);
			$content = nl2br($content);
			$content_en = make_clickable($myrow['en_body']);
			$content_en = nl2br($content_en);
			$tool_content .=  "<tbody><tr class='odd'>";
			$tool_content .= 	"<td colspan='2'> (".$langAdminAnnMes." ".$myrow['date'].")
				<a href='$_SERVER[PHP_SELF]?modify=$myrow[id]&localize=$localize'>
			  <img src='../../images/edit.gif' border='0' title='$langModify' style='vertical-align:middle;'></a>
			  <a href='$_SERVER[PHP_SELF]?delete=$myrow[id]&localize=$localize'>
			  <img src='../../images/delete.gif' border='0' title='$langDelete' style='vertical-align:middle;'></a></td></tr>";
			$tool_content .= "<tr class='odd'>";
			// title
			$tool_content .= "<td><span class='headers'>".$_lang_title_.":</span> ".$myrow['gr_title']."</td>";
			// english title
			$tool_content .= "<td>".$myrow['en_title']."</td>";
			// announcements content
			$tool_content .= "</tr>";
			$tool_content .=	"<tr><td><span class='headers'>".$_lang_body_.":</span> ".$content."</td>";
				//english content
			$tool_content .= "<td>".$content_en."</td></tr>";
			// comments
			$tool_content .= "<tr><td><span class='headers'>".$_lang_comment_.":</span> ".$myrow['gr_comment']."</td>";
			// english comments
			$tool_content .= "<td>".$myrow['en_comment']."</td></tr>";
			
			// blank line
			$tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";
		}	// end while ($myrow = mysql_fetch_array($result))
		$tool_content .= "</tbody></table>";
	}	// end: if ($displayAnnoucementList == true)


// display everything 
draw($tool_content, 3);
?>			
