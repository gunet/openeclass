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

$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'For';
require_once '../../include/baseTheme.php';
$nameTools = $langForums;

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_FORUM);
/* * *********************************** */

require_once 'config.php';
require_once 'functions.php';
require_once 'modules/group/group_functions.php';

if ($is_editor) {
    $head_content .= '
	<script type="text/javascript">
	function confirmation ()
	{
	    if (confirm("' . $langConfirmDelete . '"))
		{return true;}
	    else
		{return false;}
	}
	</script>
	';
}

if ($is_editor) {
    $tool_content .= "
	<div id='operations_container'>
	<ul id='opslist'>
	<li><a href='forum_admin.php?course=$course_code'>$langAddCategory</a>
	</li>
	</ul>
	</div>";
}


if (isset($_GET['forumcatnotify'])) { // modify forum category notification
    if (isset($_GET['cat_id'])) {
        $cat_id = intval($_GET['cat_id']);
    }
    $rows = mysql_num_rows(db_query("SELECT * FROM forum_notify
		WHERE user_id = $uid AND cat_id = $cat_id AND course_id = $course_id", $mysqlMainDb));
    if ($rows > 0) {
        db_query("UPDATE forum_notify SET notify_sent = " . intval($_GET['forumcatnotify']) . "
			WHERE user_id = $uid AND cat_id = $cat_id AND course_id = $course_id", $mysqlMainDb);
    } else {
        db_query("INSERT INTO forum_notify SET user_id = $uid,
		cat_id = $cat_id, notify_sent = 1, course_id = $course_id", $mysqlMainDb);
    }
} elseif (isset($_GET['forumnotify'])) { // modify forum notification
    if (isset($_GET['forum_id'])) {
        $forum_id = intval($_GET['forum_id']);
    }
    $rows = mysql_num_rows(db_query("SELECT * FROM forum_notify
		WHERE user_id = $uid AND forum_id = $forum_id AND course_id = $course_id", $mysqlMainDb));
    if ($rows > 0) {
        db_query("UPDATE forum_notify SET notify_sent = " . intval($_GET['forumnotify']) . "
			WHERE user_id = $uid AND forum_id = $forum_id AND course_id = $course_id", $mysqlMainDb);
    } else {
        db_query("INSERT INTO forum_notify SET user_id = $uid,
		forum_id = $forum_id, notify_sent = 1, course_id = $course_id", $mysqlMainDb);
    }
}

/*
 * Populate data with forum categories
 */
$sql = "SELECT id, cat_title FROM forum_category WHERE course_id = $course_id ORDER BY id ";

$result = db_query($sql);
$total_categories = mysql_num_rows($result);

if ($total_categories) {
    while ($cat_row = mysql_fetch_array($result)) {
        $categories[] = $cat_row;
    }
    $sql = "SELECT f.id forum_id, f.*, p.post_time, p.topic_id, p.poster_id
		FROM forum f LEFT JOIN forum_post p ON p.id = f.last_post_id
                AND f.course_id = $course_id
		ORDER BY f.cat_id, f.id";

    $f_res = db_query($sql);
    while ($forum_data = mysql_fetch_assoc($f_res)) {
        $forum_row[] = $forum_data;
    }
    for ($i = 0; $i < $total_categories; $i++) {
        $title = stripslashes($categories[$i]['cat_title']);
        $catNum = $categories[$i]['id'];
        list($action_notify) = mysql_fetch_row(db_query("SELECT notify_sent FROM forum_notify
				WHERE user_id = $uid AND cat_id = $catNum AND course_id = $course_id", $mysqlMainDb));
        if (!isset($action_notify)) {
            $link_notify = FALSE;
            $icon = '_off';
        } else {
            $link_notify = toggle_link($action_notify);
            $icon = toggle_icon($action_notify);
        }
        $tool_content .= "<table width='100%' class='tbl_alt'  style='margin-bottom: 20px;'>";
        $tool_content .= "<tr class='odd'>
		<th colspan='5'><b>$title</b></th>
		<th width='80' class='right'>";
        if ($is_editor) {
            $tool_content .= "<a href='forum_admin.php?course=$course_code&amp;forumgo=yes&amp;cat_id=$catNum'>
			<img src='$themeimg/add.png' title='$langNewForum' alt='$langNewForum' /></a>
			<a href='forum_admin.php?course=$course_code&amp;forumcatedit=yes&amp;cat_id=$catNum'>
			<img src='$themeimg/edit.png' title='$langModify' alt='$langModify' /></a>
			<a href='forum_admin.php?course=$course_code&amp;forumcatdel=yes&amp;cat_id=$catNum' onClick='return confirmation();'>
			<img src='$themeimg/delete.png' title='$langDelete' /></a>";
        }
        $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumcatnotify=$link_notify&amp;cat_id=$catNum'>
		<img src='$themeimg/email$icon.png' title='$langNotify' alt='$langNotify' />
		</a></th>
		</tr>\n";
        $tool_content .= "<tr class='sub_title1'>
		<td colspan='2' width='150'>$langForums</td>
		<td class='center'>$langSubjects</td>
		<td class='center'>$langPosts</td>
		<td class='center'>$langLastPost</td>
		<td class='center'>$langActions</td>
		</tr>";

        @reset($forum_row);
        // display forum topics
        for ($x = 0; $x < count($forum_row); $x++) {
            unset($last_post);
            $cat_id = $categories[$i]['id'];
            $sql = db_query("SELECT * FROM forum WHERE cat_id = $cat_id AND course_id = $course_id");
            if (mysql_num_rows($sql) > 0) { // if category forum topics are found
                if ($forum_row[$x]['cat_id'] == $cat_id) {
                    if ($forum_row[$x]["post_time"]) {
                        $last_post = $forum_row[$x]["post_time"];
                        $last_post_datetime = $forum_row[$x]["post_time"];
                        list($last_post_date, $last_post_time) = explode(' ', $last_post_datetime);
                        list($year, $month, $day) = explode('-', $last_post_date);
                        list($hour, $min) = explode(':', $last_post_time);
                        $last_post_time = mktime($hour, $min, 0, $month, $day, $year);
                        $human_last_post_time = date('d/m/Y -  H:i', $last_post_time);
                    }
                    if (empty($last_post)) {
                        $last_post = $langNoPosts;
                    }
                    $tool_content .= "<tr class='even'>";
                    if (!isset($last_visit)) {
                        $last_visit = 0;
                    }
                    if (@$last_post_time > $last_visit && $last_post != $langNoPosts) {
                        $tool_content .= "<td width='1'><img src='$newposts_image' /></td>\n";
                    } else {
                        $tool_content .= "<td width='2'><img src='$folder_image' /></td>\n";
                    }
                    $forum_name = q($forum_row[$x]['name']);
                    $last_user_post = uid_to_name($forum_row[$x]['poster_id']);
                    $last_post_topic_id = $forum_row[$x]['topic_id'];
                    $total_posts = $forum_row[$x]['num_posts'];
                    $total_topics = $forum_row[$x]['num_topics'];
                    $desc = q($forum_row[$x]['desc']);
                    $tool_content .= "<td>";
                    $forum_id = $forum_row[$x]['id'];
                    $is_member = false;
                    $group_id = init_forum_group_info($forum_id);
                    $member = $is_member ? "&nbsp;&nbsp;($langMyGroup)" : '';
                    // Show link to forum if:
                    //  - user is admin of course
                    //  - forum doesn't belong to group
                    //  - forum belongs to group and group forums are enabled and
                    //     - user is member of group
                    if ($is_editor or !$group_id or ($has_forum and $is_member)) {
                        $tool_content .= "<a href='viewforum.php?course=$course_code&amp;forum=$forum_id'>
                                                                <b>$forum_name</b>
                                                                </a><div class='smaller'>" . $member;
                    } else {
                        $tool_content .= $forum_name;
                    }
                    $tool_content .= "</div><div class='smaller'>$desc</div>";
                    $tool_content .= "</td>";
                    $tool_content .= "<td width='65' class='center'>$total_topics</td>\n";
                    $tool_content .= "<td width='65' class='center'>$total_posts</td>\n";
                    $tool_content .= "<td width='200' class='center'>";
                    if ($total_topics > 0 && $total_posts > 0) {
                        $tool_content .= "<span class='smaller'>" . q($last_user_post) . "&nbsp;
                                                <a href='viewtopic.php?course=$course_code&amp;topic=$last_post_topic_id&amp;forum=$forum_id'>
						<img src='$icon_topic_latest' />
						</a>
						<br />$human_last_post_time</span></td>";
                    } else {
                        $tool_content .= "<div class='inactive'>$langNoPosts</div></td>";
                    }
                    list($forum_action_notify) = mysql_fetch_row(db_query("SELECT notify_sent FROM forum_notify
								WHERE user_id = $uid
								AND forum_id = $forum_id
								AND course_id = $course_id"));
                    if (!isset($forum_action_notify)) {
                        $forum_link_notify = false;
                        $forum_icon = '_off';
                    } else {
                        $forum_link_notify = toggle_link($forum_action_notify);
                        $forum_icon = toggle_icon($forum_action_notify);
                    }
                    $tool_content .= "<td class='right'>";
                    if ($is_editor) { // admin actions
                        $tool_content .= "<a href='forum_admin.php?course=$course_code&amp;forumgoedit=yes&amp;forum_id=$forum_id&amp;cat_id=$catNum'>
						<img src='$themeimg/edit.png' title='$langModify' />
						</a>
						<a href='forum_admin.php?course=$course_code&amp;forumgodel=yes&amp;forum_id=$forum_id&amp;cat_id=$catNum' onClick='return confirmation();'>
						 <img src='$themeimg/delete.png' title='$langDelete' /></a>";
                    }
                    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;forumnotify=$forum_link_notify&amp;forum_id=$forum_id'>
					<img src='$themeimg/email$forum_icon.png' title='$langNotify' alt='$langNotify' /></a>
					</td></tr>\n";
                }
            } else {
                $tool_content .= "<tr>";
                $tool_content .= "<td colspan='6' class='alert2'>$langNoForumsCat</td>";
                $tool_content .= "</tr>";
                break;
            }
        }
        $tool_content .= "</table>";
    }
} else {
    $tool_content .= "<p class='alert1'>$langNoForums</p>";
}
add_units_navigation(true);
if ($is_editor) {
    draw($tool_content, 2, null, $head_content);
} else {
    draw($tool_content, 2);
}
