<?  session_start();
/***************************************************************************
                            reply.php  -  description
                             -------------------
    begin                : Sat June 17 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com
 
    $Id$
 
 ***************************************************************************/

/***************************************************************************
 *                                         				                                
 *   This program is free software; you can redistribute it and/or modify  	
 *   it under the terms of the GNU General Public License as published by  
 *   the Free Software Foundation; either version 2 of the License, or	    	
 *   (at your option) any later version.
 *
 ***************************************************************************/
include('extention.inc');
if(isset($cancel) && $cancel) {
	header("Location: viewtopic.$phpEx?topic=$topic&forum=$forum");
}

include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "Post Reply";
$pagetype = "reply";

if ($post_id)
{
	// We have a post id, so include that in the checks..
	$sql = "SELECT f.forum_type, f.forum_name, f.forum_access ";
	$sql .= "FROM forums f, topics t, posts p ";
	$sql .= "WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (p.post_id = $post_id) AND (t.forum_id = f.forum_id) AND (p.forum_id = f.forum_id) AND (p.topic_id = t.topic_id)";
}
else
{
	// No post id, just check forum and topic.
	$sql = "SELECT f.forum_type, f.forum_name, f.forum_access ";
	$sql .= "FROM forums f, topics t ";
	$sql .= "WHERE (f.forum_id = '$forum') AND (t.topic_id = $topic) AND (t.forum_id = f.forum_id)";	
}


if(!$result = mysql_query($sql, $db)) {
	error_die("Could not connect to the forums database.");
}
if (!$myrow = mysql_fetch_array($result))
{
	error_die("The forum/topic you selected does not exist.");	
}

$forum_name = $myrow[forum_name];
$forum_access = $myrow[forum_access];
$forum_type = $myrow[forum_type];
$forum_id = $forum;

if(is_locked($topic, $db)) {
	error_die ($l_nopostlock);
}
	
if(!does_exists($forum, $db, "forum") || !does_exists($topic, $db, "topic")) {
	error_die("The forum or topic you are attempting to post to does not exist. Please try again.");
}

if($submit) {
   if(trim($message) == '') {
      error_die($l_emptymsg);
   }
   if (!$user_logged_in) {
      if($username == '' && $password == '' && $forum_access == 2) {
	 // Not logged in, and username and password are empty and forum_access is 2 (anon posting allowed)
	 $userdata = array("user_id" => -1);
      }
      else if($username == '' || $password == '') {
	 // no valid session, need to check user/pass.
	 include('page_header.'.$phpEx);
	 error_die($l_userpass);
      }

      if($userdata[user_level] == -1) {
	 include('page_header.'.$phpEx);
	 error_die($l_userremoved);
      }
      if($userdata[user_id] != -1) {
	 $md_pass = md5($password);
	 $userdata = get_userdata($username, $db);
	 if($md_pass != $userdata["user_password"]) {
	    include('page_header.'.$phpEx);
	    error_die($l_wrongpass);
	 }	
      }
      if($forum_access == 3 && $userdata[user_level] < 2) {
	 include('page_header.'.$phpEx);
	 error_die($l_nopost);
      }
      if(is_banned($userdata[user_id], "username", $db)) {
	 include('page_header.'.$phpEx);
	 error_die($l_banned);
      }
      if($userdata[user_id] != -1) {
	 // You've entered your username and password, so we log you in.
	 $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);
	 set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
      }
   }
   else {            
      if($forum_access == 3 && $userdata[user_level] < 2) {
	 include('page_header.'.$phpEx);
	 error_die($l_nopost);
      }
   }
   // Either valid user/pass, or valid session. continue with post.. but first:
   // Check that, if this is a private forum, the current user can post here.
      
   if ($forum_type == 1)
     {
	   if (!check_priv_forum_auth($userdata[user_id], $forum, TRUE, $db))
	   {
	      include('page_header.'.$phpEx);
	      error_die("$l_privateforum $l_nopost");
	   }
	}
	 
   $poster_ip = $REMOTE_ADDR;
   
   $is_html_disabled = false;
   if($allow_html == 0 || isset($html)) {
      $message = htmlspecialchars($message);
      $is_html_disabled = true;
      
      if (isset($quote) && $quote)
      {
      	$edit_by = get_syslang_string($sys_lang, "l_editedby");
   
		   // If it's been edited more than once, there might be old "edited by" strings with
		   // escaped HTML code in them. We want to fix this up right here:
 $message = preg_replace("#&lt;font\ size\=-1&gt;\[\ $edit_by(.*?)\ \]&lt;/font&gt;#si", '<font size=-1>[ ' . $edit_by . '\1 ]</font>', $message);	
      }
   }
   if($allow_bbcode == 1 && !isset($bbcode)) {
      $message = bbencode($message, $is_html_disabled);
   }

	// MUST do make_clickable() and smile() before changing \n into <br>.
   $message = make_clickable($message);
   if(!$smile) {
      $message = smile($message);
   }
	
	$message = str_replace("\n", "<BR>", $message);
	$message = str_replace("<w>", "<s><font color=red>", $message);
	$message = str_replace("</w>", "</font color></s>", $message);
	$message = str_replace("<r>", "<font color=#0000FF>", $message);
	$message = str_replace("</r>", "</font color>", $message);

	$message = censor_string($message, $db);
	$message = addslashes($message);
	$time = date("Y-m-d H:i");


	// ADDED BY Thomas 20.2.2002

   $nom = addslashes($nom);
   $prenom = addslashes($prenom);

   // END ADDED BY THOMAS



   //to prevent [addsig] from getting in the way, let's put the sig insert down here.
   if($sig && $userdata[user_id] != -1) {
      $message .= "\n[addsig]";
   }

   $sql = "INSERT INTO posts (topic_id, forum_id, poster_id, post_time, poster_ip, nom, prenom)
   	VALUES ('$topic', '$forum', '$userdata[user_id]','$time', '$poster_ip', '$nom', '$prenom')";
   if(!$result = mysql_query($sql, $db)) {
      error_die("Error - Could not enter data into the database. Please go back and try again");
   }
   $this_post = mysql_insert_id();
   if($this_post)
   {
   	$sql = "INSERT INTO posts_text (post_id, post_text) VALUES ($this_post, '$message')";
   	if(!$result = mysql_query($sql, $db)) 
   	{
   		error_die("Could not enter post text!<br>Reason:".mysql_error());
   	}
   }
   		
   $sql = "UPDATE topics SET topic_replies = topic_replies+1, topic_last_post_id = $this_post, topic_time = '$time' 
				WHERE topic_id = '$topic'";

   if(!$result = mysql_query($sql, $db)) {
      error_die("Error - Could not enter data into the database. Please go back and try again");
   }
   if($userdata["user_id"] != -1) {
      $sql = "UPDATE users SET user_posts=user_posts+1 WHERE (user_id = $userdata[user_id])";
      $result = mysql_query($sql, $db);
      if (!$result) {
	 error_die("Error updating user post count.");
      }
   }
   $sql = "UPDATE forums SET forum_posts = forum_posts+1, forum_last_post_id = '$this_post' WHERE forum_id = '$forum'";
   $result = mysql_query($sql, $db);                                                                                             
   if (!$result) {                                                                                                               
      error_die("Error updating forums post count.");
   }    
   $sql = "SELECT t.topic_notify, u.user_email, u.username, u.user_id FROM topics t, users u 
			WHERE t.topic_id = '$topic' AND t.topic_poster = u.user_id";

   if(!$result = mysql_query($sql, $db)) {
		error_die("Couldn't get topic and user information from database.");
   }
   $m = mysql_fetch_array($result);
   if($m[topic_notify] == 1 && $m[user_id] != $userdata[user_id]) {
      // We have to get the mail body and subject line in the board default language!
      $subject = get_syslang_string($sys_lang, "l_notifysubj");
      $message = get_syslang_string($sys_lang, "l_notifybody");
      eval("\$message =\"$message\";");
      mail($m[user_email], $subject, $message, "From: $email_from\r\nX-Mailer: phpBB $phpbbversion");
   }


   $total_forum = get_total_topics($forum, $db);
   $total_topic = get_total_posts($topic, $db, "topic")-1;  
   // Subtract 1 because we want the nr of replies, not the nr of posts.
   
   $forward = 1;
   include('page_header.'.$phpEx);



  
   
   echo "<br><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACEING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\">";
   echo "<TR><TD  BGCOLOR=\"$table_bgcolor\"><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"100%\">";
   echo "<TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD><font face='arial, helvetica' size=2><P>";
   echo "<P><BR><center>$l_stored<ul>$l_click <a href=\"viewtopic.$phpEx?topic=$topic&forum=$forum&$total_topic\">$l_here</a> $l_viewmsg<P>";
   echo "$l_click <a href=\"viewforum.$phpEx?forum=$forum&$total_forum\">$l_here</a> $l_returntopic</ul></center><P></font>";
   echo "</TD></TR></TABLE></TD></TR></TABLE><br>";
   
} else {
	// Private forum logic here.
	
	if(($forum_type == 1) && !$user_logged_in && !$logging_in) 
	{
		require('page_header.'.$phpEx);

	?>
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
		<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $tablewidth?>">
			<TR>
				<TD BGCOLOR="<?php echo $table_bgcolor?>">
					<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
						<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
							<TD ALIGN="CENTER"><?php echo $l_private?></TD>
						</TR>
						<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
							<TD ALIGN="CENTER">
								
							</TD>
						</TR>
						<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
							<TD ALIGN="CENTER">
								<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
								<INPUT TYPE="HIDDEN" NAME="topic" VALUE="<?php echo $topic?>">
								<INPUT TYPE="HIDDEN" NAME="post" VALUE="<?php echo $post?>">
								<INPUT TYPE="HIDDEN" NAME="quote" VALUE="<?php echo $quote?>">
								<INPUT TYPE="SUBMIT" NAME="logging_in" VALUE="<?php echo $l_enter?>">
							</TD>
						</TR>
					</TABLE>
				</TD>
			</TR>
		</TABLE>
	</FORM>
	<?php
		require('page_tail.'.$phpEx);
		exit();
	}
   else 
     {
	if ($logging_in)
	  {
	     if ($username == '' || $password == '') 
	       {
		  error_die($l_userpass);
	       }
	     if (!check_username($username, $db)) 
	       {
		  error_die($l_nouser);
	       }
	     if (!check_user_pw($username, $password, $db)) 
	       {
		  error_die($l_wrongpass);
	       }
	     
	     /* if we get here, user has entered a valid username and password combination. */
	     $userdata = get_userdata($username, $db);
	     $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);	
	     set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
	  }
	
	require('page_header.'.$phpEx);



	 // ADDED BY CLAROLINE: exclude non identified visitors
if (!$uid AND !$fakeUid){
	echo "<center><br><br><font face='arial, helvetica' size=2>$langLoginBeforePost1<br>
		$langLoginBeforePost2<a href=../../index.php>$langLoginBeforePost3</a></center>";
	exit();
}
	
	if ($forum_type == 1)
	  {
	     // To get here, we have a logged-in user. So, check whether that user is allowed to view
	     // this private forum.
	     if (!check_priv_forum_auth($userdata[user_id], $forum, TRUE, $db))
	       {
		  error_die("$l_privateforum $l_nopost");
	       }
	     
	     // Ok, looks like we're good.
	  }
	
     }	
	
   
?>
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $tablewidth?>"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	

<?PHP
	if (!$user_logged_in) { 
		// no session, need a password.
		
	}
?>

	<TR ALIGN="LEFT">
		<TD  VALIGN=TOP BGCOLOR="<?php echo $color1?>" width=25%><font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>"><b><?php echo $l_body?>:</b><br><br>
		<?php
		
		if($quote) {
			$sql = "SELECT pt.post_text, p.post_time, u.username FROM posts p, users u, posts_text pt WHERE p.post_id = '$post' AND p.poster_id = u.user_id AND pt.post_id = p.post_id";
			if($r = mysql_query($sql, $db)) {
				$m = mysql_fetch_array($r);
				$text = desmile($m[post_text]);
				$text = str_replace("<BR>", "\n", $text);
				$text = stripslashes($text);
				$text = bbdecode($text);
				$text = undo_make_clickable($text);
				$text = str_replace("[addsig]", "", $text);
				$syslang_quotemsg = get_syslang_string($sys_lang, "l_quotemsg");
				eval("\$reply = \"$syslang_quotemsg\";");
			}
			else {
				error_die("Error Contacting database. Please try again.\n<br>$sql");
			}
		}				
		?>		
		</font></TD>
		<TD  BGCOLOR="<?php echo $color2?>">
			<TEXTAREA NAME="message" ROWS=15 COLS=50 WRAP="VIRTUAL"><?php echo $reply?></TEXTAREA>
		</TD>
	</TR>
	
	<TR>
		<TD  BGCOLOR="<?php echo $color1?>" colspan=2 ALIGN="CENTER">
                <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
		<INPUT TYPE="HIDDEN" NAME="topic" VALUE="<?php echo $topic?>">
		<INPUT TYPE="HIDDEN" NAME="quote" VALUE="<?php echo $quote?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo $l_submit?>">
		&nbsp;<INPUT TYPE="SUBMIT" NAME="cancel" VALUE="<?php echo $l_cancelpost?>">
		</TD>
	</TR>
	</TABLE></TD></TR></TABLE>
	</FORM>
<?php     
	// Topic review
	echo "<font size=\"$FontSize2\" face=\"$FontFace\">";
	echo "<BR><CENTER>";
	echo "<a href=\"viewtopic.$phpEx?topic=$topic&forum=$forum\" target=\"_blank\"><b>$l_topicreview</b></a>";
	echo "</CENTER><BR>";

}
require('page_tail.'.$phpEx);
?>
