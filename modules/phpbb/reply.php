<?php
/*=============================================================================
       	GUnet eClass 2.0 
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

/*===========================================================================
        phpbb/reply.php
* @version $Id$
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
$require_help = FALSE;
include '../../include/baseTheme.php';

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
	$sql  = "SELECT f.forum_type, f.forum_name, f.forum_access, t.topic_title ";
	$sql .= "FROM forums f, topics t, posts p ";
	$sql .= "WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic)";
	$sql .= " AND (p.post_id = $post_id) AND (t.forum_id = f.forum_id)";
	$sql .= " AND (p.forum_id = f.forum_id) AND (p.topic_id = t.topic_id)";
} else {
	// No post id, just check forum and topic.
	$sql = "SELECT f.forum_type, f.forum_name, f.forum_access, t.topic_title ";
	$sql .= "FROM forums f, topics t ";
	$sql .= "WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";	
}

if(!$result = db_query($sql, $currentCourseID)) {
	$tool_content .= $langErrorConnectForumDatabase;
	draw($tool_content, 2, 'phpbb');
	exit();
}
if (!$myrow = mysql_fetch_array($result)) {
	$tool_content .= $langErrorTopicSelect;
	draw($tool_content, 2, 'phpbb');
	exit();
}

$forum_name = $myrow["forum_name"];
$forum_access = $myrow["forum_access"];
$forum_type = $myrow["forum_type"];
$topic_title = $myrow["topic_title"];
$forum_id = $forum;

$nameTools = $l_reply;
$navigation[]= array ("url"=>"index.php", "name"=> $l_forums);
$navigation[]= array ("url"=>"viewforum.php?forum=$forum", "name"=> $forum_name);
$navigation[]= array ("url"=>"viewtopic.php?&topic=$topic&forum=$forum", "name"=> $topic_title);


if (!does_exists($forum, $currentCourseID, "forum") || !does_exists($topic, $currentCourseID, "topic")) {
	$tool_content .= $langErrorTopicSelect;
	draw($tool_content, 2, 'phpbb');
	exit();
}

if (isset($submit) && $submit) {
	if (trim($message) == '') {
		$tool_content .= $l_emptymsg;
		draw($tool_content, 2, 'phpbb');
		exit();
	}
	if ( $forum_access = 2 ) {
		$userdata = array("user_id" => -1);
	}
	if (isset($userdata["user_level"]) && $userdata["user_level"] == -1) {
		$tool_content .= $luserremoved;
		draw($tool_content, 2, 'phpbb');
		exit();
	}
	if ($userdata["user_id"] != -1) {
		$md_pass = md5($password);
		$userdata = get_userdata($username, $db);
	}
	if ($forum_access == 3 && $userdata["user_level"] < 2) {
		$tool_content .= $l_nopost;
		draw($tool_content, 2, 'phpbb');
		exit();
	}
	// XXX: Do we need this code ?
	if ( $userdata["user_id"] == -1 ) {
		if ($forum_access == 3 && $userdata["user_level"] < 2) {
			$tool_content .= $l_nopost;
			draw($tool_content, 2, 'phpbb');
			exit();
		}
	}
	// Either valid user/pass, or valid session. continue with post.. but first:
	// Check that, if this is a private forum, the current user can post here.
	if ($forum_type == 1) {
		if (!check_priv_forum_auth($userdata["user_id"], $forum, TRUE, $currentCourseID)) {
			$tool_content .= "$l_privateforum $l_nopost";
			draw($tool_content, 2, 'phpbb');
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
	$nom = addslashes($nom);
	$prenom = addslashes($prenom);

	//to prevent [addsig] from getting in the way, let's put the sig insert down here.
	if (isset($sig) && $sig && $userdata["user_id"] != -1) {
		$message .= "\n[addsig]";
	}
	$sql = "INSERT INTO posts (topic_id, forum_id, poster_id, post_time, poster_ip, nom, prenom)
			VALUES ('$topic', '$forum', '" . $userdata["user_id"] . "','$time', '$poster_ip', '$nom', '$prenom')";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= $langUnableEnterData;
		draw($tool_content, 2, 'phpbb');
		exit();
	}
	$this_post = mysql_insert_id();
	if ($this_post) {
		$sql = "INSERT INTO posts_text (post_id, post_text) VALUES ($this_post, '$message')";
		if (!$result = db_query($sql, $currentCourseID)) {
			$tool_content .= $langUnableEnterText;
			draw($tool_content, 2, 'phpbb');
			exit();
		}
	}
	$sql = "UPDATE topics
		SET topic_replies = topic_replies+1, topic_last_post_id = $this_post, topic_time = '$time' 
		WHERE topic_id = '$topic'";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= $langUnableEnterData;
		draw($tool_content, 2, 'phpbb');
		exit();
	}
	$sql = "UPDATE forums 
		SET forum_posts = forum_posts+1, forum_last_post_id = '$this_post' 
		WHERE forum_id = '$forum'";
	$result = db_query($sql, $currentCourseID);
	if (!$result) {
		$tool_content .= $langErrorUpadatePostCount;
		draw($tool_content, 2, 'phpbb');
		exit();
	}    
	$sql = "SELECT t.topic_notify, u.user_email, u.username, u.user_id
		FROM topics t, users u 
		WHERE t.topic_id = '$topic' AND t.topic_poster = u.user_id";
	if (!$result = db_query($sql, $currentCourseID)) {
		$tool_content .= $langUserTopicInformation;
		draw($tool_content, 2, 'phpbb');
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
	
	$tool_content .= "
    <div id=\"operations_container\">
      <ul id=\"opslist\">
        <li><a href=\"viewtopic.php?topic=$topic&forum=$forum&$total_topic\">$l_ViewMessage</a></li>
        <li><a href=\"viewforum.php?forum=$forum&$total_forum\">$l_returntopic</a></li>
      </ul>
    </div>
    <br />
	";
	
	$tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"success\">$l_stored</td>
    </tr>
    </tbody>
    </table>";
} else {
	// Private forum logic here.
	if (($forum_type == 1) && !$user_logged_in && !$logging_in) {
		$tool_content .= "
    <FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">

    <TABLE ALIGN=\"left\" WIDTH=\"99%\">
    <TR>
      <TD>

      <TABLE WIDTH=\"100%\">
      <TR>
        <TD>$l_private</TD>
      </TR>
      <TR>
        <TD>&nbsp;</TD>
      </TR>
      <TR>
        <TD>
          <INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
          <INPUT TYPE=\"HIDDEN\" NAME=\"topic\" VALUE=\"$topic\">
          <INPUT TYPE=\"HIDDEN\" NAME=\"post\" VALUE=\"$post\">
          <INPUT TYPE=\"HIDDEN\" NAME=\"quote\" VALUE=\"$quote\">
          <INPUT TYPE=\"SUBMIT\" NAME=\"logging_in\" VALUE=\"$l_enter\">
        </TD>
      </TR>
      </TABLE>

      </TD>
    </TR>
    </TABLE>

    </FORM>";
		draw($tool_content, 2, 'phpbb');
		exit();
	} else {
		// ADDED BY CLAROLINE: exclude non identified visitors
		if (!$uid AND !$fakeUid) {
			$tool_content .= "<center><br><br>$langLoginBeforePost1<br>";
			$tool_content .= "$langLoginBeforePost2<a href=../../index.php>$langLoginBeforePost3</a></center>";
			draw($tool_content, 2, 'phpbb');
			exit();
		}
		if ($forum_type == 1) {
			// To get here, we have a logged-in user. So, check whether that user is allowed to view
			// this private forum.
			if (!check_priv_forum_auth($userdata["user_id"], $forum, TRUE, $currentCourseID)) {
				$tool_content .= "$l_privateforum $l_nopost";
				draw($tool_content, 2, 'phpbb');
				exit();
			}
			// Ok, looks like we're good.
		}
	}	
	// Topic review
	$tool_content .= "
    <div id=\"operations_container\">
      <ul id=\"opslist\">
        <li><a href=\"viewtopic.php?topic=$topic&forum=$forum\" target=\"_blank\">$l_topicreview</a></li>
      </ul>
    </div>
    <br />
	";
	
	
	$tool_content .= "
    <FORM ACTION=\"$PHP_SELF\" METHOD=\"POST\">
    <table class=\"FormData\" width=\"99%\">
    <tbody>
    <tr>
      <th width=\"220\">&nbsp;</th>
      <TD><b>$langTopicAnswer</b>: $topic_title</TD>
    </tr>
    <tr>
      <th class=\"left\">$l_body:";
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
			$tool_content .= $langErrorConnectForumDatabase;
			draw($tool_content, 2, 'phpbb');
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
      </th>
      <td valign=\"top\"><TEXTAREA NAME=\"message\" ROWS=15 COLS=70 WRAP=\"VIRTUAL\" class=\"auth_input\">$reply</TEXTAREA></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td>
          <INPUT TYPE=\"HIDDEN\" NAME=\"forum\" VALUE=\"$forum\">
          <INPUT TYPE=\"HIDDEN\" NAME=\"topic\" VALUE=\"$topic\">
          <INPUT TYPE=\"HIDDEN\" NAME=\"quote\" VALUE=\"$quote\">
          <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"$l_submit\">&nbsp;
          <INPUT TYPE=\"SUBMIT\" NAME=\"cancel\" VALUE=\"$l_cancelpost\">
      </td>
    </tr>
    </tbody>
    </TABLE>
    </FORM>
    <br/>";

}
draw($tool_content, 2, 'phpbb');
?>
