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

$require_admin = TRUE;
include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php');
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$nameTools = $langAdminAn;

$head_content .= <<<hContent
<script type='text/javascript'>
function confirmation ()
{
        if (confirm('$langConfirmDelete'))
                {return true;}
        else
                {return false;}
}
</script>
hContent;

// display settings
$displayAnnouncementList = true;
$displayForm = true;

foreach (array('title', 'newContent') as $var) {
        if (isset($_POST[$var])) {
                $GLOBALS[$var] = autoquote($_POST[$var]);
        } else {
                $GLOBALS[$var] = '';
        }
}

// modify visibility
if (isset($_GET['vis'])) {
	$id = $_GET['id'];
	$vis = $_GET['vis'];
	if ($vis == 0) {
		$vis = 'I';
	} else {
		$vis = 'V';
	}
	db_query("UPDATE admin_announcements SET visible = '$vis' WHERE id = '$id'", $mysqlMainDb);
}


if (isset($_GET['delete'])) {
        // delete announcement command
        $id = intval($_GET['delete']);
        $result =  db_query("DELETE FROM admin_announcements WHERE id='$id'", $mysqlMainDb);
        $message = $langAdminAnnDel;
} elseif (isset($_GET['modify'])) {
        // modify announcement command
        $id = intval($_GET['modify']);
        $result = db_query("SELECT * FROM admin_announcements WHERE id='$id'", $mysqlMainDb);
        $myrow = mysql_fetch_array($result);
        if ($myrow) {
                $titleToModify = q($myrow['title']);
                $contentToModify = $myrow['body'];
                $displayAnnouncementList = true;
        }
} elseif (isset($_POST['submitAnnouncement'])) {
	// submit announcement command
        if (isset($_POST['id'])) {
                // modify announcement
                $id = intval($_POST['id']);
                db_query("UPDATE admin_announcements
                        SET title = $title, body = $newContent,
			lang = '".$_POST['lang_admin_ann']."', 
			date = NOW()
                        WHERE id = $id", $mysqlMainDb);
                $message = $langAdminAnnModify;
        } else {
                // add new announcement
                db_query("INSERT INTO admin_announcements
                        SET title = $title, body = $newContent,
			visible = 'V', lang = '".$_POST['lang_admin_ann']."', date = NOW()");
                $message = $langAdminAnnAdd;
        }
}

// action message
if (isset($message) && !empty($message)) {
        $tool_content .= "<p class='success_small'>$message</p><br/>";
        $displayAnnouncementList = true;
        $displayForm = false; //do not show form
}

// display form
if ($displayForm && isset($_GET['addAnnounce']) || isset($_GET['modify'])) {
        $displayAnnouncementList = false;
        // display add announcement command
	if (isset($_GET['modify'])) {
                $titleform = $langAdminModifAnn;
        } else {
                $titleform = $langAdminAddAnn;
        }
	$navigation[] = array("url" => "$_SERVER[PHP_SELF]", "name" => $langAdminAn);
	$nameTools = $titleform;
	
	if (!isset($contentToModify)) {
		$contentToModify = "";
	}
        if (!isset($titleToModify)) {
		$titleToModify = "";
	}

        $tool_content .= "<form method='post' action='$_SERVER[PHP_SELF]'>";
	$tool_content .= "<fieldset><legend>$titleform</legend>";
        $tool_content .= "<table width='99%' class='tbl'>";
        $tool_content .= "<tr><td>$langTitle<br />
		<input type='text' name='title' value='$titleToModify' size='50' /></td></tr>
		<tr><td>$langAnnouncement <br />".
		rich_text_editor('newContent', 4, 20, $contentToModify)
		."</td></tr></table></fieldset>";
	if (isset($_GET['modify'])) {
		$tool_content .= "<input type='hidden' name='id' value='$id'>";
	}
	$tool_content .= "<fieldset><legend>$langLanguage</legend>
	<table class='tbl'><tr><td>$langOptions&nbsp;:</td>
	<td width='1'>";
	if (isset($_GET['modify'])) {
		$tool_content .= lang_select_options('lang_admin_ann', '', $myrow['lang']);
	} else {
		$tool_content .= lang_select_options('lang_admin_ann');
	}
	$tool_content .= "</td><td>$langTipLangAdminAnn</td></tr></table></fieldset>";
        $tool_content .= "<tr><td><input type='submit' name='submitAnnouncement' value='$langSubmit' /></td></tr>              
	</table</fieldset></form>";
}

// display admin announcements
if ($displayAnnouncementList == true) {
        $result = db_query("SELECT * FROM admin_announcements ORDER BY id DESC", $mysqlMainDb);
        $announcementNumber = mysql_num_rows($result);
        if (!isset($_GET['addAnnounce'])) {
                $tool_content .= "<div id='operations_container'>
                <ul id='opslist'><li>";
                $tool_content .= "<a href='".$_SERVER['PHP_SELF']."?addAnnounce=1'>".$langAdminAddAnn."</a>";
                $tool_content .= "</li></ul></div>";
        }
        if ($announcementNumber > 0) {
                $tool_content .= "<table class='FormData' width='99%' align='left'><tbody>";
		$tool_content .= "<th>$langTitle</th><th>$langAnnouncement</th><th>$langActions</th>";
		while ($myrow = mysql_fetch_array($result)) {
			if ($myrow['visible'] == 'V') {
				$visibility = 0;
				$classvis = 'visible';
				$icon = 'visible.gif';
			} else {
				$visibility = 1;
				$classvis = 'invisible';
				$icon = 'invisible.gif';
			}
			$myrow['date'] = claro_format_locale_date($dateFormatLong, strtotime($myrow['date']));
			$tool_content .= "<tr class='$classvis'>";
			$tool_content .= "<td><b>".q($myrow['title'])."</b>&nbsp;&nbsp;<small>($myrow[date])</small></td>";
			$tool_content .= "<td>$myrow[body]</td>";
			$tool_content .=  "<td>
			<a href='$_SERVER[PHP_SELF]?modify=$myrow[id]'>
			<img src='../../template/classic/img/edit.gif' title='$langModify' style='vertical-align:middle;' />
			</a>&nbsp;
			<a href='$_SERVER[PHP_SELF]?delete=$myrow[id]' onClick='return confirmation();'>
			<img src='../../template/classic/img/delete.gif' title='$langDelete' style='vertical-align:middle;' /></a>
			&nbsp;
			<a href='$_SERVER[PHP_SELF]?id=$myrow[id]&amp;vis=$visibility'>
			<img src='../../template/classic/img/$icon' title='$langVisibility'/></a></td></tr>";
	        }
		$tool_content .= "</tbody></table>";
	}
}
draw($tool_content, 3, '', $head_content);
