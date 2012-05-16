<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
include '../../include/jscalendar/calendar.php';

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

function toggle(id, checkbox, name)
{
        var f = document.getElementById('f-calendar-field-' + id);
        f.disabled = !checkbox.checked;
}
</script>
hContent;

// display settings
$displayAnnouncementList = true;
$displayForm = true;

foreach (array('title', 'newContent', 'lang_admin_ann') as $var) {
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
	db_query("UPDATE admin_announcement SET visible = '$vis' WHERE id = $id");
}

if (isset($_GET['delete'])) {
        // delete announcement command
        $id = intval($_GET['delete']);
        $result =  db_query("DELETE FROM admin_announcement WHERE id = $id");
        $message = $langAdminAnnDel;
} elseif (isset($_GET['modify'])) {
        // modify announcement command
        $id = intval($_GET['modify']);
        $result = db_query("SELECT * FROM admin_announcement WHERE id = $id");
        $myrow = mysql_fetch_array($result);
        if ($myrow) {
                $titleToModify = q($myrow['title']);
                $contentToModify = standard_text_escape($myrow['body']);
                $displayAnnouncementList = true;
                $begindate = $myrow['begin'];
                $enddate = $myrow['end'];
        }
} elseif (isset($_POST['submitAnnouncement'])) {
        // submit announcement command
        $start_sql = 'begin = ' . (isset($_POST['start_date_active'])? autoquote($_POST['start_date']): 'NULL');
        $end_sql = 'end = ' . (isset($_POST['end_date_active'])? autoquote($_POST['end_date']): 'NULL');
        $newContent = purify($newContent);
        if (isset($_POST['id'])) {
                // modify announcement
                $id = intval($_POST['id']);
                db_query("UPDATE admin_announcement
                        SET title = $title, body = $newContent,
			lang = $lang_admin_ann, 
			`date` = NOW(), $start_sql, $end_sql
                        WHERE id = $id");
                $message = $langAdminAnnModify;
        } else {
                // add new announcement
                // order
                $result = db_query("SELECT MAX(`order`) FROM admin_announcement");
                list($orderMax) = mysql_fetch_row($result);
                $order = $orderMax + 1;
                db_query("INSERT INTO admin_announcement
                        SET title = $title, body = $newContent,
                        visible = 1, lang = $lang_admin_ann,
                        `date` = NOW(), `order` = $order, $start_sql, $end_sql");
                $message = $langAdminAnnAdd;
        }
}

// action message
if (isset($message) && !empty($message)) {
        $tool_content .= "<p class='success'>$message</p><br/>";
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
	if (isset($_GET['modify'])) {
		$tool_content .= "<input type='hidden' name='id' value='$id' />";
	}
	$tool_content .= "<fieldset><legend>$titleform</legend>";
        $tool_content .= "<table width='100%' class='tbl'>";
        $tool_content .= "<tr><td><b>$langTitle:</b>
		<input type='text' name='title' value='$titleToModify' size='50' /></td></tr>
		<tr><td><b>$langAnnouncement:</b><br />".
		rich_text_editor('newContent', 5, 40, q($contentToModify))
		."</td></tr>";
	$tool_content .= "<tr><td><b>$langLanguage:</b><br />";
	if (isset($_GET['modify'])) {
                if (isset($begindate)) {
                    $start_checkbox = 'checked';
                    $start_date = $begindate;
                } else {
                    $start_checkbox = '';
                    $start_date = date("Y-n-j",time());
                }
                if (isset($enddate)) {
                    $end_checkbox = 'checked';
                    $end_date = $enddate;
                } else {
                    $end_checkbox = '';
                    $end_date = date("Y-n-j",time());
                }
		$tool_content .= lang_select_options('lang_admin_ann', '', $myrow['lang']);
	} else {
                $start_checkbox = $end_checkbox = $end_date = $start_date = '';
		$tool_content .= lang_select_options('lang_admin_ann');
	}
        $tool_content .= "<span class='smaller'> $langTipLangAdminAnn</span></td></tr>";

        $lang_jscalendar = langname_to_code($language);
        $jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang_jscalendar, 'calendar-blue2', false);
        $head_content .= $jscalendar->get_load_files_code();
        
        $datetoday = date("Y-n-j",time());
        function make_calendar($id, $label, $name, $checkbox, $datetoday) {
                global $jscalendar, $langActivate;
                
                return "<tr><td><b>" . $label . ":</b><br />" .
                        $jscalendar->make_input_field(
                        array('showOthers' => true,
                              'showsTime' => true,
                              'align' => 'Tl',
                              'ifFormat' => '%Y-%m-%d %H:%m'),
                        array('name' => $name,
                              'value' => $datetoday,
                              'style' => '')) .
                        "&nbsp;<span class='smaller'><input type='checkbox' name='{$name}_active' $checkbox onClick=\"toggle($id,this,'$name')\"/>&nbsp;".
                        $langActivate . "</span></td></tr>";
        }
        $tool_content .= make_calendar(1, $langStartDate, 'start_date', $start_checkbox, $start_date) .
                         make_calendar(2, $langEndDate, 'end_date', $end_checkbox, $end_date) .
                         "<tr><td class='right'><input type='submit' name='submitAnnouncement' value='$langSubmit' />" .
                         "</td></tr></table></fieldset></form>";
}

// modify order taken from announcements.php
if (isset($_GET['down'])) {
	$thisAnnouncementId = $_GET['down'];
	$sortDirection = "DESC";
}
if (isset($_GET['up'])) {
	$thisAnnouncementId = $_GET['up'];
	$sortDirection = "ASC";
}

// if there are announcements without ordering -> order by id, latest is first
$result = db_query("SELECT id, `order` FROM admin_announcement WHERE `order`=0");
$no_order = mysql_fetch_row($result);
if (!empty($no_order)) {
	db_query("UPDATE admin_announcement SET `order`=`id`+1");
}

if (isset($thisAnnouncementId) && $thisAnnouncementId && isset($sortDirection) && $sortDirection) {
	$result = db_query("SELECT id, `order` FROM admin_announcement ORDER BY `order` $sortDirection");

	while (list($announcementId, $announcementOrder) = mysql_fetch_row($result)) {
		if (isset($thisAnnouncementOrderFound) && $thisAnnouncementOrderFound == true) {
			$nextAnnouncementId = $announcementId;
			$nextAnnouncementOrder = $announcementOrder;
			db_query("UPDATE admin_announcement SET `order` = '$nextAnnouncementOrder' WHERE id = '$thisAnnouncementId'");
			db_query("UPDATE admin_announcement SET `order` = '$thisAnnouncementOrder' WHERE id = '$nextAnnouncementId'");
			break;
		}
		// find the order
		if ($announcementId == $thisAnnouncementId) {
			$thisAnnouncementOrder = $announcementOrder;
			$thisAnnouncementOrderFound = true;
		}
	}
}

// display admin announcements
if ($displayAnnouncementList == true) {
        $result = db_query("SELECT * FROM admin_announcement ORDER BY `order` DESC");
		  // announcement order taken from announcements.php
        $iterator = 1;
        $bottomAnnouncement = $announcementNumber = mysql_num_rows($result);
        if (!isset($_GET['addAnnounce'])) {
                $tool_content .= "<div id='operations_container'>
                <ul id='opslist'><li>";
                $tool_content .= "<a href='".$_SERVER['PHP_SELF']."?addAnnounce=1'>".$langAdminAddAnn."</a>";
                $tool_content .= "</li></ul></div>\n";
        }
        if ($announcementNumber > 0) {
                $tool_content .= "<table class='tbl_alt' width='100%'>
                        <tr><th colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$langTitle</th>
                            <th>$langAnnouncement</th>
                            <th colspan='2'><div align='center'>$langActions</div></th>\n";
                /*
		if ($announcementNumber > 1) {
			$tool_content .= "<th width='70'>$langMove</th>\n";
		}
                */
		while ($myrow = mysql_fetch_array($result)) {
			if ($myrow['visible'] == 1) {
				$visibility = 0;
				$classvis = 'visible';
                                if ($iterator%2 == 0) {
                                        $classvis = 'even';
                                } else {
                                        $classvis = 'odd';
                                }
				$icon = 'visible.png';
			} else {
				$visibility = 1;
				$classvis = 'invisible';
				$icon = 'invisible.png';
			}
			$myrow['date'] = claro_format_locale_date($dateFormatLong, strtotime($myrow['date']));
			$tool_content .= "<tr class='$classvis'>\n";
                        $tool_content .= "<td width='1'><img style='margin-top:4px;' src='$themeimg/arrow.png' title='bullet' /></td>";
			$tool_content .= "<td width='180'><b>".q($myrow['title'])."</b><br><span class='smaller'>$myrow[date]</span></td>\n";
			$tool_content .= "<td>" . standard_text_escape($myrow['body']) . "</td>\n";
			$tool_content .= "<td width='60'>
			<a href='$_SERVER[PHP_SELF]?modify=$myrow[id]'>
			<img src='$themeimg/edit.png' title='$langModify' style='vertical-align:middle;' />
			</a> 
			<a href='$_SERVER[PHP_SELF]?delete=$myrow[id]' onClick='return confirmation();'>
			<img src='$themeimg/delete.png' title='$langDelete' style='vertical-align:middle;' /></a>
			
			<a href='$_SERVER[PHP_SELF]?id=$myrow[id]&amp;vis=$visibility'>
			<img src='$themeimg/$icon' title='$langVisibility'/></a>";
			if ($announcementNumber > 1) {
				$tool_content .= "<td align='right' width='40'>";
			}
			if ($iterator != 1) {
				$tool_content .= "<a href='$_SERVER[PHP_SELF]?up=" . $myrow["id"] . "'>
				<img class='displayed' src='$themeimg/up.png' title='" . $langUp . "' /></a>";
			}
			if ($iterator < $bottomAnnouncement) {
				$tool_content .= "<a href='$_SERVER[PHP_SELF]?down=" . $myrow["id"] . "'>
				<img class='displayed' src='$themeimg/down.png' title='" . $langDown . "' /></a>";
			}
			$tool_content .= "</td>\n</tr>\n";
			$iterator ++;
	        } // end of while
		$tool_content .= "</table>\n";
	}
}

draw($tool_content, 3, null, $head_content);