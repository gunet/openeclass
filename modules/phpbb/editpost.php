<?php
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

/*===========================================================================
        phpbb/editpost.php
        @last update: 2006-07-23 by Artemios G. Voyiatzis
        @authors list: Artemios G. Voyiatzis <bogart@upnet.gr>

        based on Claroline version 1.7 licensed under GPL
              copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

        Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>

	based on phpBB version 1.4.1 licensed under GPL
		copyright (c) 2001, The phpBB Group
==============================================================================
    @Description: This module implements a per course forum for supporting
	discussions between teachers and students or group of students.
	It is a heavily modified adaptation of phpBB for (initially) Claroline
	and (later) eclass. In the future, a new forum should be developed.
	Currently we use only a fraction of phpBB tables and functionality
	(viewforum, viewtopic, post_reply, newtopic); the time cost is
	enormous for both core phpBB code upgrades and migration from an
	existing (phpBB-based) to a new eclass forum :-(

    @Comments:

    @todo:
==============================================================================
*/

/*
 * GUNET eclass 2.0 standard stuff
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = FALSE;
include '../../include/baseTheme.php';
$tool_content = "";
$lang_editor = langname_to_code($language);
$head_content = <<<hContent
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>
hContent;
/*
 * Tool-specific includes
 */
include_once("./config.php");
include("functions.php"); // application logic for phpBB

/******************************************************************************
 * Actual code starts here
 *****************************************************************************/
if ($is_adminOfCourse) { // course admin 
	if (isset($submit) && $submit) {
		$sql = "SELECT * FROM posts WHERE post_id = '$post_id'";
		if (!$result = db_query($sql, $currentCourseID)) {
			$tool_content .= $langErrorDataOne;
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
		if (mysql_num_rows($result) <= 0) {
			$tool_content .= $langErrorDataTwo;
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
		$myrow = mysql_fetch_array($result);
		$forum_id = $myrow["forum_id"];
		$topic_id = $myrow["topic_id"];
		$this_post_time = $myrow["post_time"];
		list($day, $time) = split(" ", $myrow["post_time"]);
		$date = date("Y-m-d H:i");
	
		$row1 = mysql_fetch_row(db_query("SELECT forum_name FROM forums WHERE forum_id='$forum_id'"));
		$forum_name = $row1[0];
		$row2 = mysql_fetch_row(db_query("SELECT topic_title FROM topics WHERE topic_id='$topic_id'"));
		$topic_title = $row2[0];
	
		$nameTools = $l_reply;
		$navigation[]= array ("url"=>"index.php", "name"=> $l_forums);
		$navigation[]= array ("url"=>"viewforum.php?forum=$forum_id", "name"=> $forum_name);
		$navigation[]= array ("url"=>"viewtopic.php?&topic=$topic_id&forum=$forum_id", "name"=> $topic_title);
	
		// IF we made it this far we are allowed to edit this message, yay!
		$is_html_disabled = false;
		if ( (isset($allow_html) && $allow_html == 0) || isset($html) ) {
			$message = htmlspecialchars($message);
			$is_html_disabled = true;
		}
		if ( isset($allow_bbcode) && $allow_bbcode == 1 && !isset($bbcode)) {
			$message = bbencode($message, $is_html_disabled);
		}
		// MUST do make_clickable() before changing \n into <br>.
		if (isset($message)) {
			$message = make_clickable($message);
			$message = str_replace("\n", "<BR>", $message);
			$message = str_replace("<w>", "<s><font color=red>", $message);
			$message = str_replace("</w>", "</font color></s>", $message);
			$message = str_replace("<r>", "<font color=#0000FF>", $message);
			$message = str_replace("</r>", "</font color>", $message);
			$message = addslashes($message);
		}
		if (!isset($delete) || !$delete) {
			$forward = 1;
			$topic = $topic_id;
			$forum = $forum_id;
			$sql = "UPDATE posts_text SET post_text = '$message' WHERE (post_id = '$post_id')";
			if (!$result = db_query($sql, $currentCourseID)) {
				$tool_content .= $langUnableUpadatePost;
				draw($tool_content, 2, 'phpbb', $head_content);
				exit();
			}
			if (isset($subject)) {
				$subject = strip_tags($subject);
			}
			if (isset($subject) && (trim($subject) != '')) {
				if(!isset($notify)) {
					$notify = 0;
				} else {
					$notify = 1;
				}
				$subject = addslashes($subject);
				$sql = "UPDATE topics
					SET topic_title = '$subject', topic_notify = '$notify'
					WHERE topic_id = '$topic_id'";
				if (!$result = db_query($sql, $currentCourseID)) {
					$tool_content .= $langUnableUpadateTopic;
				}
			}
			
			$tool_content .= "<div id=\"operations_container\">
			<ul id=\"opslist\">
			<li><a href=\"viewtopic.php?topic=$topic_id&forum=$forum_id\">$l_viewmsg1</a></li>
			<li><a href=\"viewforum.php?forum=$forum_id\">$l_returntopic</a></li>
			</ul>
			</div>
			<br />";
			$tool_content .= "<table width=\"99%\">
			<tbody><tr><td class=\"success\">$l_stored</td>
			</tr></tbody></table>";
		} else {
			$now_hour = date("H");
			$now_min = date("i");
			list($hour, $min) = split(":", $time);
			$last_post_in_thread = get_last_post($topic_id, $currentCourseID, "time_fix");
			$sql = "DELETE FROM posts
				WHERE post_id = '$post_id'";
			if (!$r = db_query($sql, $currentCourseID)){
				$tool_content .= $langUnableDeletePost;
				draw($tool_content, 2, 'phpbb', $head_content);
				exit();
			}
			$sql = "DELETE FROM posts_text
				WHERE post_id = '$post_id'";
			if (!$r = db_query($sql, $currentCourseID)) {
				$tool_content .= $langUnableDeletePost;
				draw($tool_content, 2, 'phpbb', $head_content);
				exit();
			} else if ($last_post_in_thread == $this_post_time) {
				$topic_time_fixed = get_last_post($topic_id, $currentCourseID, "time_fix");
				$sql = "UPDATE topics
					SET topic_time = '$topic_time_fixed'
					WHERE topic_id = '$topic_id'";
				if (!$r = db_query($sql, $currentCourseID)) {
					$tool_content .= $langPostRemoved;
					draw($tool_content, 2, 'phpbb', $head_content);
					exit();
				}
			}
			if (get_total_posts($topic_id, $currentCourseID, "topic") == 0) {
				$sql = "DELETE FROM topics
					WHERE topic_id = '$topic_id'";
				if (!$r = db_query($sql, $currentCourseID)) {
					$tool_content .= $langUnableDeleteTopic;
					draw($tool_content, 2, 'phpbb', $head_content);
					exit();
				}
				$topic_removed = TRUE;
			}
			sync($currentCourseID, $forum, 'forum');
			if (@!$topic_removed) {
				sync($currentCourseID, $topic_id, 'topic');
			}
			
			$tool_content .= "<div id=\"operations_container\">
			<ul id=\"opslist\">
			<li><a href=\"viewforum.php?forum=$forum_id\">$l_returntopic</a></li>
			<li><a href=\"index.php\">$l_returnindex</a></li>
			</ul></div><br />";
			$tool_content .= "<table width=\"99%\"><tbody>
			<tr>
			<td class=\"success\">$l_deleted</td>
			</tr>
			</tbody></table>";
		}
	} else {
		// Gotta handle private forums right here. They're naturally covered on submit, but not in this part.
		$sql = "SELECT f.forum_type, f.forum_name, t.topic_title
			FROM forums f, topics t
			WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";
		
		if (!$result = db_query($sql, $currentCourseID)) {
			$tool_content .= "$langTopicInformation";
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
		
		if (!$myrow = mysql_fetch_array($result)) {
			$tool_content .= "$langErrorTopicSelect";
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
		
		$nameTools = $l_reply;
		$navigation[]= array ("url"=>"index.php", "name"=> $l_forums);
		$navigation[]= array ("url"=>"viewforum.php?forum=$forum", "name"=> $myrow['forum_name']);
		$navigation[]= array ("url"=>"viewtopic.php?&topic=$topic&forum=$forum", "name"=> $myrow['topic_title']);
	
		if (($myrow["forum_type"] == 1) && !$user_logged_in && !$logging_in) {
			// Private forum, no valid session, and login form not submitted...
			$tool_content .= "<FORM ACTION=\"$_SERVER[PHP_SELF]\" METHOD=\"POST\">
			<TABLE WIDTH=\"99%\">
			<TR>
			<TD>$l_private</TD>
			</TR>
			<TR>
			<TD>
			<TABLE WIDTH=\"99%\">
			<TR>
			<TD>&nbsp;</TD>
			</TR>
			</TABLE>
			</TD>
			</TR>
			<TR>
			<TD>
			<INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
			<INPUT TYPE=\"HIDDEN\" NAME=\"topic\" VALUE=\"$topic\">
			<INPUT TYPE=\"HIDDEN\" NAME=\"post_id\" VALUE=\"$post_id\">
			<INPUT TYPE=\"SUBMIT\" NAME=\"logging_in\" VALUE=\"$l_enter\">
			</TD>
			</TR>
			</TABLE></FORM>";
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		} else {
			if ($myrow["forum_type"] == 1) {
				// To get here, we have a logged-in user. So, check whether that user is allowed to post in
				// this private forum.
				if (!check_priv_forum_auth($uid, $forum, TRUE, $currentCourseID)) {
					$tool_content .= "$l_privateforum $l_nopost";
					draw($tool_content, 2, 'phpbb', $head_content);
					exit();
				}
				// Ok, looks like we're good.
			}
		}	
		
		$sql = "SELECT p.*, pt.post_text, t.topic_title, t.topic_notify, 
			       t.topic_title, t.topic_notify 
			FROM posts p, topics t, posts_text pt 
			WHERE (p.post_id = '$post_id') AND (pt.post_id = p.post_id) AND (p.topic_id = t.topic_id)";

		if (!$result = db_query($sql, $currentCourseID)) {
			$tool_content .= "<p>Couldn't get user and topic information from the database.</p>";
			draw($tool_content, 2, 'phpbb', $head_content);
			exit();
		}
		$myrow = mysql_fetch_array($result);
		if (isset($user_logged_in) && $user_logged_in) {
			if($user_level <= 2) {
				if($user_level == 2 && !is_moderator($forum, $uid, $currentCourseID)) {
					if($user_level < 2 && ($uid != $myrow["p.poster_id"])) {
						$tool_content .= $l_notedit;
						draw($tool_content, 2, 'phpbb', $head_content);
						exit();
					}
				}
			}
		}
		$message = $myrow["post_text"];
		$message = str_replace('{','&#123;',$message);
		if (eregi("\[addsig]$", $message)) {
			$addsig = 1;
		} else {
			$addsig = 0;
		}
		//$message = eregi_replace("\[addsig]$", "\n_________________\n" . $myrow["user_sig"], $message);   
		$message = str_replace("<BR>", "\n", $message);
		$message = stripslashes($message);
		$message = bbdecode($message);
		$message = undo_make_clickable($message);
		$message = undo_htmlspecialchars($message);
		// Special handling for </textarea> tags in the message, which can break the editing form..
		$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);
		list($day, $time) = split(" ", $myrow["post_time"]);
		
		
		$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\">
		<li><a href=\"viewtopic.php?topic=$topic&forum=$forum\" target=\"_blank\">$l_topicreview</a></li>
		</ul>
		</div>
		<br />";
		$tool_content .= "<FORM action=\"$_SERVER[PHP_SELF]\" METHOD=\"POST\">
		<table class=\"FormData\" width=\"99%\">
		<tbody>
		<TR>
		<th width=\"220\">&nbsp;</th>
		<TD><b>$l_replyEdit</b></TD>
		</TR>";
		$first_post = is_first_post($topic, $post_id, $currentCourseID);
		if($first_post) {
			$tool_content .= "<tr>
			<th class=\"left\">$l_subject:</th>
			<TD><INPUT TYPE=\"TEXT\" NAME=\"subject\" SIZE=\"53\" MAXLENGTH=\"100\" VALUE=\"" . stripslashes($myrow["topic_title"]) . "\"  class=\"FormData_InputText\"></TD>
			</TR>";
		}
		$tool_content .= "<TR><th class=\"left\">$l_body:</th>
		<TD>
		<table class='xinha_editor'>
		<tr>
		<td>
		<TEXTAREA id='xinha' NAME='message' ROWS=10 COLS=50 WRAP='VIRTUAL'  class='FormData_InputText'>$message</TEXTAREA>
		</td></tr></table>
		</TD>
		</TR>
		<TR>
		<th class=\"left\">$l_delete:</th>
		<TD><INPUT TYPE=\"CHECKBOX\" NAME=\"delete\"></TD>
		</TR>
		<TR><th>&nbsp;</th><TD>";
		
		$tool_content .= "
		<INPUT TYPE=\"HIDDEN\" NAME=\"post_id\" VALUE=\"$post_id\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
		<!--
		<INPUT TYPE=\"HIDDEN\" NAME=\"topic_id\" VALUE=\"$topic\">
		-->
		<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">
		</TD></TR>";
		$tool_content .= "</tbody></table>";
	}
} else {
	$tool_content .= $langForbidden;
}
draw($tool_content, 2, 'phpbb', $head_content);
