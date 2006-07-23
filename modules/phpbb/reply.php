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
        phpbb/reply.php
        @last update: 2006-07-23 by Artemios G. Voyiatzis
        @authors list: Artemios G. Voyiatzis <bogart@upnet.gr>

        based on Claroline version 1.7 licensed under GPL
              copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

        Claroline authors: Piraux Sébastien <pir@cerdecam.be>
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
if ( isset($post_id) && $post_id) {
	// We have a post id, so include that in the checks..
	$sql  = "SELECT f.forum_type, f.forum_name, f.forum_access ";
	$sql .= "FROM forums f, topics t, posts p ";
	$sql .= "WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic)";
	$sql .= " AND (p.post_id = $post_id) AND (t.forum_id = f.forum_id)";
	$sql .= " AND (p.forum_id = f.forum_id) AND (p.topic_id = t.topic_id)";
} else {
	// No post id, just check forum and topic.
	$sql = "SELECT f.forum_type, f.forum_name, f.forum_access ";
	$sql .= "FROM forums f, topics t ";
	$sql .= "WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";	
}

if(!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= "Could not connect to the forums database.";
	draw($tool_content, 2);
	exit();
}
if (!$myrow = mysql_fetch_array($result)) {
	$tool_content .= "The forum/topic you selected does not exist.";
	draw($tool_content, 2);
	exit();
}

$forum_name = $myrow["forum_name"];
$forum_access = $myrow["forum_access"];
$forum_type = $myrow["forum_type"];
$forum_id = $forum;

if (!does_exists($forum, $currentCourseID, "forum") || !does_exists($topic, $currentCourseID, "topic")) {
	$tool_content .= "The forum or topic you are attempting to post to does not exist. Please try again.";
	draw($tool_content, 2);
	exit();
}

if (isset($submit) && $submit) {
	if (trim($message) == '') {
		$tool_content .= $l_emptymsg;
		draw($tool_content, 2);
		exit();
	}
	if ( $forum_access = 2 ) {
		$userdata = array("user_id" => -1);
	}
	if (isset($userdata["user_level"]) && $userdata["user_level"] == -1) {
		$tool_content .= $luserremoved;
		draw($tool_content, 2);
		exit();
	}
	if ($userdata["user_id"] != -1) {
		$md_pass = md5($password);
		$userdata = get_userdata($username, $db);
	}
	if ($forum_access == 3 && $userdata["user_level"] < 2) {
		$tool_content .= $l_nopost;
		draw($tool_content, 2);
		exit();
	}
	// XXX: Do we need this code ?
	if ( $userdata["user_id"] == -1 ) {
		if ($forum_access == 3 && $userdata["user_level"] < 2) {
			$tool_content .= $l_nopost;
			draw($tool_content, 2);
			exit();
		}
	}
	// Either valid user/pass, or valid session. continue with post.. but first:
	// Check that, if this is a private forum, the current user can post here.
	if ($forum_type == 1) {
		if (!check_priv_forum_auth($userdata["user_id"], $forum, TRUE, $currentCourseID)) {
			$tool_content .= "$l_privateforum $l_nopost";
			draw($tool_content, 2);
			exit();
		}
	}
	$poster_ip = $REMOTE_ADDR;
	$is_html_disabled = false;
	if ( (isset($allow_html) && $allow_html == 0) || isset($html)) {
		$message = htmlspecialchars($message);
		$is_html_disabled = true;
		if (isset($quote) && $quote) {
			$edit_by = get_syslang_string($sys_lang, "l_editedby");
			// If it's been edited more than once, there might be old "edited by" strings with
			// escaped HTML code in them. We want to fix this up right here:
			$message = preg_replace("#&lt;font\ size\=-1&gt;\[\ $edit_by(.*?)\ \]&lt;/font&gt;#si", '[ ' . $edit_by . '\1 ]', $message);
		}
	}
	if ( (isset($allow_bbcode) && $allow_bbcode == 1) && !isset($bbcode)) {
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
	$time = date("Y-m-d H:i");
	// ADDED BY Thomas 20.2.2002
	$nom = addslashes($nom);
	$prenom = addslashes($prenom);
	// END ADDED BY THOMAS

	//to prevent [addsig] from getting in the way, let's put the sig insert down here.
	if (isset($sig) && $sig && $userdata["user_id"] != -1) {
		$message .= "\n[addsig]";
	}
	$sql = "INSERT INTO posts (topic_id, forum_id, poster_id, post_time, poster_ip, nom, prenom)
			VALUES ('$topic', '$forum', '" . $userdata["user_id"] . "','$time', '$poster_ip', '$nom', '$prenom')";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= "Error - Could not enter data into the database. Please go back and try again";
		draw($tool_content, 2);
		exit();
	}
	$this_post = mysql_insert_id();
	if ($this_post) {
		$sql = "INSERT INTO posts_text (post_id, post_text) VALUES ($this_post, '$message')";
		if (!$result = db_query($sql, $currentCourseID)) {
			$tool_content .= "Could not enter post text!<br>Reason:" . mysql_error();
			draw($tool_content, 2);
			exit();
		}
	}
	$sql = "UPDATE topics
		SET topic_replies = topic_replies+1, topic_last_post_id = $this_post, topic_time = '$time' 
		WHERE topic_id = '$topic'";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= "Error - Could not enter data into the database. Please go back and try again";
		draw($tool_content, 2);
		exit();
	}
	$sql = "UPDATE forums 
		SET forum_posts = forum_posts+1, forum_last_post_id = '$this_post' 
		WHERE forum_id = '$forum'";
	$result = db_query($sql, $currentCourseID);
	if (!$result) {
		$tool_content .= "Error updating forums post count.";
		draw($tool_content, 2);
		exit();
	}    
	$sql = "SELECT t.topic_notify, u.user_email, u.username, u.user_id
		FROM topics t, users u 
		WHERE t.topic_id = '$topic' AND t.topic_poster = u.user_id";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= "Couldn't get topic and user information from database.";
		draw($tool_content, 2);
		exit();
	}
	$m = mysql_fetch_array($result);
	if ($m["topic_notify"] == 1 && $m["user_id"] != $userdata["user_id"]) {
		// We have to get the mail body and subject line in the board default language!
		$subject = get_syslang_string($sys_lang, "l_notifysubj");
		$message = get_syslang_string($sys_lang, "l_notifybody");
		eval("\$message =\"$message\";");
		mail($m["user_email"], $subject, $message, "From: $email_from\r\nX-Mailer: phpBB $phpbbversion");
	}
	$total_forum = get_total_topics($forum, $currentCourseID);
	$total_topic = get_total_posts($topic, $currentCourseID, "topic")-1;  
	// Subtract 1 because we want the nr of replies, not the nr of posts.
	$forward = 1;
	$tool_content .= <<<cData
		<br>
		<TABLE BORDER="0" CELLPADDING="1" CELLSPACEING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="99%">
		<TR><TD>
			<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
			<TR><TD><P><P><BR><center>
cData;
	$tool_content .= "$l_stored <ul>$l_click <a href=\"viewtopic.php?topic=$topic&forum=$forum&$total_topic\">$l_here</a> $l_viewmsg<P>";
	$tool_content .= "$l_click <a href=\"viewforum.php?forum=$forum&$total_forum\">$l_here</a> $l_returntopic</ul>";
	$tool_content .= <<<cData
			</center><P>
			</TD></TR>
			</TABLE>
		</TD></TR>
		</TABLE>
		<br>
cData;
} else {
	// Private forum logic here.
	if (($forum_type == 1) && !$user_logged_in && !$logging_in) {
		$tool_content .= "
			<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">
			<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"99%\">
			<TR><TD>
				<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"99%\">
				<TR><TD>$l_private</TD></TR>
				<TR><TD></TD></TR>
				<TR><TD>
					<INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
					<INPUT TYPE=\"HIDDEN\" NAME=\"topic\" VALUE=\"$topic\">
					<INPUT TYPE=\"HIDDEN\" NAME=\"post\" VALUE=\"$post\">
					<INPUT TYPE=\"HIDDEN\" NAME=\"quote\" VALUE=\"$quote\">
					<INPUT TYPE=\"SUBMIT\" NAME=\"logging_in\" VALUE=\"$l_enter\">
				</TD></TR>
				</TABLE>
			</TD></TR>
			</TABLE>
			</FORM>";
		draw($tool_content, 2);
		exit();
	} else {
		// ADDED BY CLAROLINE: exclude non identified visitors
		if (!$uid AND !$fakeUid) {
			$tool_content .= "<center><br><br>$langLoginBeforePost1<br>";
			$tool_content .= "$langLoginBeforePost2<a href=../../index.php>$langLoginBeforePost3</a></center>";
			draw($tool_content, 2);
			exit();
		}
		if ($forum_type == 1) {
			// To get here, we have a logged-in user. So, check whether that user is allowed to view
			// this private forum.
			if (!check_priv_forum_auth($userdata["user_id"], $forum, TRUE, $currentCourseID)) {
				$tool_content .= "$l_privateforum $l_nopost";
				draw($tool_content, 2);
				exit();
			}
			// Ok, looks like we're good.
		}
	}	
	$tool_content .= "
		<FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">
		<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"99%\">
		<TR><TD>
			<TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"99%\">
			<TR><TD width=\"25%\">
				<b>$l_body:</b><br><br>";
	if (isset($quote) && $quote) {
		$sql = "SELECT pt.post_text, p.post_time, u.username 
			FROM posts p, users u, posts_text pt 
			WHERE p.post_id = '$post' AND p.poster_id = u.user_id AND pt.post_id = p.post_id";
		if ($r = db_query($sql, $currentCourseID)) {
			$m = mysql_fetch_array($r);
			$text = $m["post_text"];
			$text = str_replace("<BR>", "\n", $text);
			$text = stripslashes($text);
			$text = bbdecode($text);
			$text = undo_make_clickable($text);
			$text = str_replace("[addsig]", "", $text);
			$syslang_quotemsg = get_syslang_string($sys_lang, "l_quotemsg");
			eval("\$reply = \"$syslang_quotemsg\";");
		} else {
			$tool_content .= "Error Contacting database. Please try again.\n";
			draw($tool_content, 2);
			exit();
		}
	}				
	if (!isset($reply)) {
		$reply = "";
	}
	if (!isset($quote)) {
		$quote = "";
	}
	$tool_content .= "
			</TD>
			<TD>
				<TEXTAREA NAME=\"message\" ROWS=15 COLS=50 WRAP=\"VIRTUAL\">$reply</TEXTAREA>
			</TD></TR>
			<TR><TD colspan=2>
				<INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
				<INPUT TYPE=\"HIDDEN\" NAME=\"topic\" VALUE=\"$topic\">
				<INPUT TYPE=\"HIDDEN\" NAME=\"quote\" VALUE=\"$quote\">
				<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">&nbsp;
				<INPUT TYPE=\"SUBMIT\" NAME=\"cancel\" VALUE=\"$l_cancelpost\">
			</TD></TR>
			</TABLE>
		</TD></TR>
		</TABLE>
		</FORM>";
	// Topic review
	$tool_content .= "<BR><CENTER>";
	$tool_content .= "<a href=\"viewtopic.php?topic=$topic&forum=$forum\" target=\"_blank\"><b>$l_topicreview</b></a>";
	$tool_content .= "</CENTER><BR>";
}
draw($tool_content,2);
?>
