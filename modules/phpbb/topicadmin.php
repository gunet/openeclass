<?php
/***************************************************************************
                            topicadmin.php  -  description
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
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "Topic Administration";
$pagetype = "bbcode_ref";
include('page_header.'.$phpEx);

if (isset($user))
{
	$mod_data = get_userdata($user, $db);
} else {
	$mod_data = get_userdata($userdata[username], $db);
}

if(!is_moderator($forum, $mod_data[user_id], $db) && $mod_data[user_level] <= 2)
	error_die("You are not the moderator of this forum therefore you cannot perform this function.");

if($submit || ($user_logged_in==1 && $mode=='viewip')) {
   if( $user_logged_in != 1 && ($mod_data[user_password] != md5($passwd)) )
     error_die("Error - You did not enter the correct password, please go back and try again.");

   switch($mode) {
    case 'del':
      // Update the users's post count, this might be slow on big topics but it makes other parts of the
      // forum faster so we win out in the long run.
      $sql = "SELECT poster_id, post_id FROM posts WHERE topic_id = '$topic'";
      if(!$r = mysql_query($sql, $db))
			die("Error - Could not query the posts database!");
      while($row = mysql_fetch_array($r)) {
	 		if($row[poster_id] != -1) {
	    		$sql = "UPDATE users SET user_posts = user_posts - 1 WHERE user_id = '$row[poster_id]'";
	    		@mysql_query($sql, $db);
	 		}
      }

		// Get the post ID's we have to remove.
		$sql = "SELECT post_id FROM posts WHERE topic_id = '$topic'";
		if(!$r = mysql_query($sql, $db))
			die("Error - Could not query the posts database!");
      while($row = mysql_fetch_array($r))
      {
			$posts_to_remove[] = $row["post_id"];
		}

      $sql = "DELETE FROM posts WHERE topic_id = '$topic'";
      if(!$result = mysql_query($sql, $db))
			die("Error - Could not remove posts from the database!");
      $sql = "DELETE FROM topics WHERE topic_id = '$topic'";
      if(!$result = mysql_query($sql, $db))
			die("Error - Could not remove posts from the database!");
		$sql = "DELETE FROM posts_text WHERE ";
		for($x = 0; $x < count($posts_to_remove); $x++)
		{
			if($set)
			{
				$sql .= " OR ";
			}
			$sql .= "post_id = ".$posts_to_remove[$x];
			$set = TRUE;
		}

		if(!mysql_query($sql, $db))
		{
			die("Error - Could not remove post texts!");
		}
		sync($db, $forum, 'forum');

      echo "The topic has been removed from the database. Click <a href=\"viewforum.$phpEx?forum=$forum\">here</a> to return to the forum, or <a href=\"index.$phpEx\">here</a> to return to the forum index.";
      break;
    case 'move':
      $sql = "UPDATE topics SET forum_id = '$newforum' WHERE topic_id = '$topic'";
      if(!$r = mysql_query($sql, $db))
			die("Error - Could not move selected topic to selected forum. Please go back and try again.");
      $sql = "UPDATE posts SET forum_id = '$newforum' WHERE topic_id = '$topic'";
      if(!$r = mysql_query($sql, $db))
			die("Error - Could not move selected topic to selected forum. Please go back and try again.");
		sync($db, $newforum, 'forum');
		sync($db, $forum, 'forum');

      echo "The topic has been moved. Click <a href=\"viewtopic.$phpEx?topic=$topic&forum=$newforum\">here</a> to view the updated topic. Or click <a href=\"index.$phpEx\">here</a> to return to the forum index";
      break;
    case 'lock':
      $sql = "UPDATE topics SET topic_status = 1 WHERE topic_id = '$topic'";
      if(!$r = mysql_query($sql, $db))
			die("Error - Could not lock the selected topic. Please go back and try again.");
      echo "The topic has been locked. Click <a href=\"viewtopic.$phpEx?topic=$topic&forum=$forum\">here</a> to view, or <a href=\"index.$phpEx\">here</a> to return to the forum index.";
      break;
    case 'unlock':
      $sql = "UPDATE topics SET topic_status = '0' WHERE topic_id = '$topic'";
      if(!$r = mysql_query($sql, $db))
	die("Error - Could not unlock the selected topic. Please go back and try again.");
      echo "The topic has been unlocked. Click <a href=\"viewtopic.$phpEx?topic=$topic&forum=$forum\">here</a> to view, or <a href=\"index.$phpEx\">here</a> to return to the forum index.";
      break;
    case 'viewip':
      $sql = "SELECT u.username, p.poster_ip FROM users u, posts p WHERE p.post_id = '$post' AND u.user_id = p.poster_id";
      if(!$r = mysql_query($sql, $db))
	die("Error - Could not query the database. <BR>Error: mysql_error()");
      if(!$m = mysql_fetch_array($r))
	die("Error - No such user or post in the database.");
      $poster_host = gethostbyaddr($m[poster_ip]);
?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACEING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACEING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD COLSPAN="2" ALIGN="CENTER">Users IP and Account information</TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD>User IP:</TD>
	<TD><?php echo $m[poster_ip] . " ( $poster_host )"?></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD COLSPAN="2" ALIGN="CENTER">Usernames of users that posted from this IP + post counts</TD>
</TR>
<?php
	$sql = "SELECT user_id, username, count(*) as postcount FROM posts p, users u WHERE poster_ip='".$m[poster_ip]."' && p.poster_id = u.user_id GROUP BY user_id";
	if(!$r = mysql_query($sql, $db))
	{
		echo "<TR><TD COLSPAN=\"2\">Error - Could not query the database. <BR>Error: mysql_error()</TD></TR></TABLE>";
		exit();
	}

	while ($row = mysql_fetch_array($r)){
		print "<TR BGCOLOR=\"$color2\" ALIGN=\"LEFT\">\n";
		print "	<TD><A HREF=\"bb_profile.php?mode=view&user=".$row[user_id]."\">".$row[username]."</A></TD>\n";
		print "	<TD>".$row[postcount]." posts</TD>\n";
		print "</TR>\n";
	}
?>

</TABLE></TD></TR></TABLE>
<?php
		break;

	}
}
else {  // No submit
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
<?php
	switch($mode) {
		case 'del':
?>
	<TD COLSPAN=2><B>Read This:</b>Once you press the delete button at the bottom of this form the topic you have selected, and all its related posts, will be <b>permanently</b> removed.</TD>
<?php
		break;
		case 'move':
?>
	<TD COLSPAN=2><B>Read This:</b>Once you press the move button at the bottom of this form the topic you have selected, and its related posts, will be moved to the forum you have selected.</TD>
<?php
		break;
		case 'lock':
?>
	<TD COLSPAN=2><B>Read This:</b>Once you press the lock button at the bottom of this form the topic you have selected will be locked. You may unlock it at a later time if you like.</TD>
<?php
		break;
		case 'unlock':
?>
	<TD COLSPAN=2><B>Read This:</b>Once you press the unlock button at the bottom of this form the topic you have selected will be unlocked. You may lock it again at a later time if you like.</TD>
<?php
		break;
		case 'viewip':
?>
	<TD COLSPAN=2><B>Read This:</b>View this users IP address.</TD>
<?php
		break;
	}
?>
</TR>
<?php
	if (!$user_logged_in)
	{
?>
<TR>
	<TD BGCOLOR="<?php echo $color1?>">Username:</TD>
	<TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="user" SIZE="25" MAXLENGTH="40" VALUE="<?php echo $userdata[username]?>"></TD>
</TR>
<TR>
	<TD BGCOLOR="<?php echo $color1?>">Password:</TD>
	<TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="PASSWORD" NAME="passwd" SIZE="25" MAXLENGTH="25"></TD>
</TR>
<?php
	}
	if($mode == 'move') {
?>
<TR>
	<TD BGCOLOR="<?php echo $color1?>">Move Topic To:</TD>
	<TD BGCOLOR="<?php echo $color2?>"><SELECT NAME="newforum" SIZE="0">
<?php
	$sql = "SELECT forum_id, forum_name FROM forums WHERE forum_id != '$forum' ORDER BY forum_id";
	if($result = mysql_query($sql, $db)) {
		if($myrow = mysql_fetch_array($result)) {
			do {
				echo "<OPTION VALUE=\"$myrow[forum_id]\">$myrow[forum_name]</OPTION>\n";
			} while($myrow = mysql_fetch_array($result));
		}
		else {
			echo "<OPTION VALUE=\"-1\">No Forums in DB</OPTION>\n";
		}
	}
	else {
		echo "<OPTION VALUE=\"-1\">Database Error</OPTION>\n";
	}
?>
	</SELECT></TD>
</TR>
<?php
	}
?>
<TR BGCOLOR="<?php echo $color1?>">
	<TD COLSPAN="2" ALIGN="CENTER">
<?php
	switch($mode) {
		case 'del':
?>
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="del">
		<INPUT TYPE="HIDDEN" NAME="topic" VALUE="<?php echo $topic?>">
		<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Delete Topic">
<?php
		break;
		case 'move':
?>
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="move">
		<INPUT TYPE="HIDDEN" NAME="topic" VALUE="<?php echo $topic?>">
		<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Move Topic">
<?php
		break;
		case 'lock':
?>
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="lock">
		<INPUT TYPE="HIDDEN" NAME="topic" VALUE="<?php echo $topic?>">
		<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Lock Topic">
<?php
		break;
		case 'unlock':
?>
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="unlock">
		<INPUT TYPE="HIDDEN" NAME="topic" VALUE="<?php echo $topic?>">
		<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="Unlock Topic">
<?php
		break;
		case 'viewip':
?>
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="viewip">
		<INPUT TYPE="HIDDEN" NAME="post" VALUE="<?php echo $post?>">
		<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="View IP">
<?php
		break;
	}
?>
</TD></TR>
</FORM>
</TABLE></TD></TR></TABLE></TD></TR></TABLE>
<?php
}
include('page_tail.'.$phpEx);
?>









