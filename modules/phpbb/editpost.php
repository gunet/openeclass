<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
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

error_reporting(E_ALL);
/*
 * GUNET eclass 2.0 standard stuff
 */
$require_current_course = TRUE;
$require_login = TRUE;
$langFiles = 'phpbb';
$require_help = FALSE;
include '../../include/baseTheme.php';
$nameTools = $l_forums;
$tool_content = "";

/*
 * Tool-specific includes
 */
include_once("./config.php");
include("functions.php"); // application logic for phpBB

/******************************************************************************
 * Actual code starts here
 *****************************************************************************/
if (isset($submit) && $submit) {
	$sql = "SELECT *
		FROM posts
		WHERE post_id = '$post_id'";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= "Error retrieving data from database (1)";
		draw($tool_content, 2);
		exit();
	}
	if (mysql_num_rows($result) <= 0) {
		$tool_content .= "Error retrieving data from database (2)";
		draw($tool_content, 2);
		exit();
	}
	$myrow = mysql_fetch_array($result);
	$poster_id = $myrow["poster_id"];
	$forum_id = $myrow["forum_id"];
	$topic_id = $myrow["topic_id"];
	$this_post_time = $myrow["post_time"];
	list($day, $time) = split(" ", $myrow["post_time"]);
	$date = date("Y-m-d H:i");

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
	$message = make_clickable($message);
	$message = str_replace("\n", "<BR>", $message);
	$message = str_replace("<w>", "<s><font color=red>", $message);
	$message = str_replace("</w>", "</font color></s>", $message);
	$message = str_replace("<r>", "<font color=#0000FF>", $message);
	$message = str_replace("</r>", "</font color>", $message);

	$message = addslashes($message);
	if (!isset($delete) || !$delete) {
		$forward = 1;
		$topic = $topic_id;
		$forum = $forum_id;
		$sql = "UPDATE posts_text
			SET post_text = '$message'
			WHERE (post_id = '$post_id')";
		if (!$result = db_query($sql, $currentCourseID)) {
			$tool_content .= "Unable to update the posting in the database";
			draw($tool_content, 2);
			exit();
		}
		$subject = strip_tags($subject);
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
				$tool_content .= "Unable to update the topic subject in the database";
			}
		}
		$tool_content .= "
		<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$l_stored</b></p>
							
							<p>$l_click <a href=\"viewtopic.php?topic=$topic_id&forum=$forum_id\">$l_here</a> $l_viewmsg</p>
				<p>	$l_click <a href=\"viewforum.php?forum=$forum_id\">$l_here</a> $l_returntopic</p>
						</td>
					</tr>
				</tbody>
			</table>
		";
	 } else {
		$now_hour = date("H");
		$now_min = date("i");
		list($hour, $min) = split(":", $time);
		$last_post_in_thread = get_last_post($topic_id, $currentCourseID, "time_fix");
		$sql = "DELETE FROM posts
			WHERE post_id = '$post_id'";
		if (!$r = db_query($sql, $currentCourseID)){
			$tool_content .= "Couldn't delete post from database";
			draw($tool_content, 2);
			exit();
		}
		$sql = "DELETE FROM posts_text
			WHERE post_id = '$post_id'";
		if (!$r = db_query($sql, $currentCourseID)) {
			$tool_content .= "Couldn't delete post from database";
			draw($tool_content, 2);
			exit();
		} else if ($last_post_in_thread == $this_post_time) {
			$topic_time_fixed = get_last_post($topic_id, $currentCourseID, "time_fix");
			$sql = "UPDATE topics
				SET topic_time = '$topic_time_fixed'
				WHERE topic_id = '$topic_id'";
			if (!$r = db_query($sql, $currentCourseID)) {
				$tool_content .= "Couldn't update to previous post time - last post has been removed";
				draw($tool_content, 2);
				exit();
			}
		}
		if (get_total_posts($topic_id, $currentCourseID, "topic") == 0) {
			$sql = "DELETE FROM topics
				WHERE topic_id = '$topic_id'";
			if (!$r = db_query($sql, $currentCourseID)) {
				$tool_content .= "Couldn't delete topic from database";
				draw($tool_content, 2);
				exit();
			}
	 		$topic_removed = TRUE;
		}
		sync($currentCourseID, $forum, 'forum');
		if (@!$topic_removed) {
			sync($currentCourseID, $topic_id, 'topic');
		}
		$tool_content .= "
		
		<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$l_deleted</b></p>
							
							<p>$l_click <a href=\"viewforum.php?forum=$forum_id\">$l_here</a> $l_returntopic</p>
				<p>	$l_click <a href=\"index.php\">$l_here</a>$l_returnindex</p>
						</td>
					</tr>
				</tbody>
			</table>
		
		";
	}	
} else {
	// Gotta handle private forums right here. They're naturally covered on submit, but not in this part.
	$sql = "SELECT f.forum_type, f.forum_name, t.topic_title
		FROM forums f, topics t
		WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= "Couldn't get forum and topic information from the database.";
		draw($tool_content, 2);
		exit();
	}
	
	if (!$myrow = mysql_fetch_array($result)) {
		$tool_content .= "Error - The forum/topic you selected does not exist. Please go back and try again.";
		draw($tool_content, 2);
		exit();
	}
	
	if (($myrow["forum_type"] == 1) && !$user_logged_in && !$logging_in) {
		// Private forum, no valid session, and login form not submitted...
		$tool_content .= "
			<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">
			<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"99%\">
			<TR><TD>
			
				<TR><TD>$l_private</TD></TR>
				<TR><TD>
					<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\">
					<TR><TD></TD></TR>
					</TABLE>
				</TD></TR>
				<TR><TD>
					<INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
					<INPUT TYPE=\"HIDDEN\" NAME=\"topic\" VALUE=\"$topic\">
					<INPUT TYPE=\"HIDDEN\" NAME=\"post_id\" VALUE=\"$post_id\">
					<INPUT TYPE=\"SUBMIT\" NAME=\"logging_in\" VALUE=\"$l_enter\">
				</TD></TR>
				
			</TABLE>
			</FORM>";
		draw($tool_content, 2);
		exit();
	} else {
		if ($myrow["forum_type"] == 1) {
			// To get here, we have a logged-in user. So, check whether that user is allowed to post in
			// this private forum.
			if (!check_priv_forum_auth($userdata["user_id"], $forum, TRUE, $currentCourseID)) {
				$tool_content .= "$l_privateforum $l_nopost";
				draw($tool_content, 2);
				exit();
			}
			// Ok, looks like we're good.
		}
	}	
	
	$sql = "SELECT p.*, pt.post_text, u.username, u.user_id, u.user_sig, t.topic_title, t.topic_notify 
		FROM posts p, users u, topics t, posts_text pt 
		WHERE (p.post_id = '$post_id') AND (pt.post_id = p.post_id) AND (p.topic_id = t.topic_id) AND (p.poster_id = u.user_id)";

	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= "<p>Couldn't get user and topic information from the database.</p>";
		draw($tool_content, 2);
		exit();
	}
	$myrow = mysql_fetch_array($result);
	// Freekin' ugly but I couldn't get it to work right as 1 big if 
	//          - James
	if (isset($user_logged_in) && $user_logged_in) {
		if($userdata["user_level"] <= 2) {
			if($userdata["user_level"] == 2 && !is_moderator($forum, $userdata["user_id"], $currentCourseID)) {
				if($userdata[user_level] < 2 && ($userdata["user_id"] != $myrow["user_id"])) {
					$tool_content .= $l_notedit;
					draw($tool_content, 2);
					exit();
				}
			}
		}
	}
	$message = $myrow["post_text"];
	
	if (eregi("\[addsig]$", $message)) {
		$addsig = 1;
	} else {
		$addsig = 0;
	}
	$message = eregi_replace("\[addsig]$", "\n_________________\n" . $myrow["user_sig"], $message);   
	$message = str_replace("<BR>", "\n", $message);
	$message = stripslashes($message);
	$message = bbdecode($message);
	$message = undo_make_clickable($message);
	$message = undo_htmlspecialchars($message);
	// Special handling for </textarea> tags in the message, which can break the editing form..
	$message = preg_replace('#</textarea>#si', '&lt;/TEXTAREA&gt;', $message);
	list($day, $time) = split(" ", $myrow["post_time"]);
	
	$tool_content .= "
	<a href=\"viewtopic.php?topic=$topic&forum=$forum\" target=\"_blank\">$l_topicreview</a><br/><br/>
		<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">
		<TABLE WIDTH=\"99%\">
		<thead>
		";
	 	
	$first_post = is_first_post($topic, $post_id, $currentCourseID);
	if($first_post) {
		$tool_content .= "
			<TR>
			<th>$l_subject:</th>
			<TD>
				<INPUT TYPE=\"TEXT\" NAME=\"subject\" SIZE=\"50\" MAXLENGTH=\"100\" VALUE=\"" . stripslashes($myrow["topic_title"]) . "\"></TD>
			</TR>";
	}
	$tool_content .= "
			<TR>
				<th>
					$l_body:
					
				</th>
				<TD>
					<TEXTAREA NAME=\"message\" ROWS=10 COLS=45 WRAP=\"VIRTUAL\">$message</TEXTAREA>
				</TD>
			</TR>
			</thead>
			</table>
			
			<p>		<INPUT TYPE=\"CHECKBOX\" NAME=\"delete\">$l_delete</p>
			
					
			
					";

	if (isset($user_logged_in) && $user_logged_in) {
		$tool_content .= "<INPUT TYPE=\"HIDDEN\" NAME=\"username\" VALUE=\"" . $userdata["username"] . "\">";
	}
	$tool_content .= "
					<INPUT TYPE=\"HIDDEN\" NAME=\"post_id\" VALUE=\"$post_id\">
					<INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
					<!--
					<INPUT TYPE=\"HIDDEN\" NAME=\"topic_id\" VALUE=\"$topic\">
					<INPUT TYPE=\"HIDDEN\" NAME=\"poster_id\" VALUE=\"" . $myrow["poster_id"] ."\">
					-->
					<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">
";
	       
}
draw($tool_content,2);
?>
