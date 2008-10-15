<?
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

$require_admin = TRUE;
include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$nameTools = $langAdminAn;
$tool_content = $head_content = "";

$head_content .= '
<script>
function confirmation ()
{
        if (confirm("'.$langConfirmDelete.'"))
                {return true;}
        else
                {return false;}
}
</script>
';


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
	$_lang_body_ = $langAnnouncement;
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
		$tool_content .=  "<p class=\"success_small\">$message</p><br/>";
 		$displayAnnouncementList = true;
		$displayForm  = false;//do not show form
	}

	//	display form
	if ($displayForm == true && (@$addAnnouce==1 || isset($modify))) {
		$displayAnnouncementList = false;
		// display add announcement command
		$tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]?localize=$localize'>";
		$tool_content .= "<table width='99%' class='FormData' align='left'><tbody>
    			<tr><th width='220'>&nbsp;</th><td><b>";
			if (isset($modify)) {
				$tool_content .= "$langAdminModifAnn";
			} else {
				$tool_content .=  "$langAdminAddAnn";
			}
		$tool_content .= "</b></td></tr>";

		if (!isset($AnnouncementToModify)) $AnnouncementToModify ="";
		if (!isset($contentToModify))	$contentToModify ="";
		if (!isset($titleToModify))	$titleToModify ="";
		if (!isset($commentToModify))	$commentToModify ="";
		// english
		if (!isset($contentToModifyEn))	$contentToModifyEn ="";
		if (!isset($titleToModifyEn))	$titleToModifyEn ="";
		if (!isset($commentToModifyEn))	$commentToModifyEn ="";

	$tool_content .= "<tr><th class='left'>$langAdminAnVis</th><td>";
        if (isset($visibleToModify) and $visibleToModify == 'V') {
			$tool_content .= "<input type=checkbox value=\"1\" name=\"visible\" checked>";
		} else {
			$tool_content .= "<input type=checkbox value=\"1\" name=\"visible\">";
		}
      $tool_content .= "</td></tr><tr><td colspan=\"2\">&nbsp;</td></tr>
    	<tr>
        <th class='left'>$_lang_title_</th>
        <td><input type=\"text\" name='title' value='$titleToModify' size='50' class='FormData_InputText'></td>
        </tr>
        <tr><th class='left'>$_lang_body_</th>
       <td><textarea name='newContent' value='$contentToModify' rows='10' cols='50' class='FormData_InputText'>$contentToModify</textarea>
	<input type=\"hidden\" name=\"id\" value=\"".$AnnouncementToModify."\"></td>
       </tr><tr>
      <th class='left'>$_lang_comment_</th>
      <td><textarea name='comment' value='$commentToModify' rows='2' cols='50' class='FormData_InputText'>$commentToModify</textarea></td>
    </tr>";
	// ----------------------- english ---------------------------
	$tool_content .= "<tr><td colspan=\"2\">&nbsp;</td></tr><tr>
      <th class='left'>$_lang_titleen_</th>
      <td><input type=\"text\" name='title_en' value='$titleToModifyEn' size='50' class='FormData_InputText'></td>
      </tr><tr>
      <th class='left'>$_lang_bodyen_</th>
      <td><textarea name='newContent_en' value='$contentToModifyEn' rows='10' cols='50' class='FormData_InputText'>$contentToModifyEn</textarea></td>
    </tr>
    <tr>
      <th class='left'>$_lang_commenten_</th>
      <td><textarea name='comment_en' value='$commentToModifyEn' rows='2' cols='50' class='FormData_InputText'>$commentToModifyEn</textarea></td>
    </tr>
    <tr>
      <th class='left'>&nbsp;</th>
      <td><input type=\"Submit\" name=\"submitAnnouncement\" value=\"$l_register\"></td>
    </tr>
    <tr><td colspan=\"2\">&nbsp;</td></tr>
    </tbody>
    </table>
    </form>";

    $tool_content .= "<br><br>";
}

	// display admin announcements
	if ($displayAnnouncementList == true) {
		$result = db_query("SELECT * FROM admin_announcements ORDER BY id DESC", $mysqlMainDb);
		$announcementNumber = mysql_num_rows($result);
		if (@$addAnnouce != 1) {
			$tool_content .= "<div id=\"operations_container\">
    			<ul id=\"opslist\"><li>";
			$tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?addAnnouce=1&localize=$localize\">".$langAdminAddAnn."</a>";
			$tool_content .= "</li></ul></div>";
		}
		if ($announcementNumber > 0) {
			$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\"><tbody>
  				<tr><th width=\"220\" class=\"left\">$langAdminAn</th>
    				<td width=\"300\"><b>".$langNameOfLang['greek']."</b></td>
    				<td width=\"300\"><b>".$langNameOfLang['english']."</b></td></tr>";
		}
		while ($myrow = mysql_fetch_array($result)) {
			$content = make_clickable($myrow['gr_body']);
			$content = nl2br($content);
			$content_en = make_clickable($myrow['en_body']);
			$content_en = nl2br($content_en);
			$visibleAnn = $myrow['visible'];
			if ($visibleAnn == 'I') {
			   	$stylerow = "style='color: silver;'";
			} else {
				$stylerow = "";
			}
			$tool_content .=  "<tr class=\"odd\" $stylerow>
    			<td colspan=\"3\" class=\"right\">(".$langAdminAnnMes." <b>".nice_format($myrow['date'])."</b>)
			&nbsp;&nbsp;
			<a href='$_SERVER[PHP_SELF]?modify=$myrow[id]&localize=$localize'>
			<img src='../../template/classic/img/edit.gif' border='0' title='$langModify' style='vertical-align:middle;'>
			</a>&nbsp;
			<a href='$_SERVER[PHP_SELF]?delete=$myrow[id]&localize=$localize' onClick='return confirmation();'>
			<img src='../../images/delete.gif' border='0' title='$langDelete' style='vertical-align:middle;'></a>
    			</td></tr>";
			$tool_content .= "<tr $stylerow>";
			// title
			$tool_content .= "<th class=\"left\">".$_lang_title_.":</th>";
			$tool_content .= "<td>".$myrow['gr_title']."</td>";
			// english title
			$tool_content .= "<td>".$myrow['en_title']."</td>";
			// announcements content
			$tool_content .= "</tr>";
			$tool_content .= "<tr $stylerow><th class=\"left\">".$_lang_body_.":</th><td>".$content."</td>";
			//english content
			$tool_content .= "<td>".$content_en."</td></tr>";
			// comments
			$tool_content .= "<tr $stylerow><th class=\"left\">".$_lang_comment_.":</th>
    			<td>".$myrow['gr_comment']."</td>";
			// english comments
			$tool_content .= "<td>".$myrow['en_comment']."</td></tr>";
		}	// end while
		$tool_content .= "</tbody></table>";
	}	// end: if ($displayAnnoucementList == true)

// display everything
draw($tool_content, 3, '', $head_content);
?>
