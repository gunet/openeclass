<?php  session_start(); 
/***************************************************************************
                          viewpmsg.php  -  description
                             -------------------
    begin                : Wed June 19 2000
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

/**
 * viewpmsg.$phpEx - Nathan Codding
 * - Used for receiving private messages between users of the BB.
 */
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "Private Messages";
$pagetype = "privmsgs";
include('page_header.'.$phpEx);

if (!$submit && !$user_logged_in) {
	login_form();
} else {
	
	if (!$user_logged_in) {
	   if ($user == '' || $passwd == '') {
	      error_die("$l_userpass $l_tryagain");
	   }
	   if (!check_username($user, $db)) {
	      error_die("$l_nouser $l_tryagain");
	   }
	   if (!check_user_pw($user, $passwd, $db)) {
	      error_die("$l_wrongpass $l_tryagain");
	   }
	   
	   /* throw away user data from the cookie, use username from the form to get new data */
	   $userdata = get_userdata($user, $db);
	   if(is_banned($userdata[user_id], "username", $db))
	     error_die($l_banned);
	}

	$sql = "SELECT * FROM priv_msgs WHERE (to_userid = $userdata[user_id]) ORDER BY msg_time DESC";
	$resultID = mysql_query($sql, $db);
	if (!$resultID) {
		error_die("Error getting messages from DB.");
	}

?>

<TABLE BORDER="0" CELLPADDING="1" CELLPADDING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="3" CELLPADDING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD WIDTH=20% COLSPAN=2><?php echo $l_from?></TD>
</TR>

<?php
	if (!mysql_num_rows($resultID)) {
		echo "<TD BGCOLOR=\"$color1\" colspan = 2 ALIGN=CENTER>$l_nopmsgs</TD></TR>\n";
	}
	
	while ($myrow = mysql_fetch_array($resultID)) {

		echo "<TR BGCOLOR=\"$color2\" ALIGN=\"LEFT\">\n";
		$posterdata = get_userdata_from_id($myrow[from_userid], $db);
		echo "<TD valign=top><b>$posterdata[username]</b><br>\n";
		$posts = $posterdata[user_posts];
		if($posts < 15)
			echo "<font size=-2>$rank1<BR>\n";
		else
			echo "<font size=-2>$rank2<br>\n";
		echo "<br><font size=-2>$l_posts: $posts<br>\n";
		echo "$l_location: $posterdata[user_from]<br></FONT></TD>\n";
		echo "<TD><img src=\"$posticon\"><font size=-1>$l_posted: $myrow[msg_time]&nbsp;&nbsp;&nbsp";
		echo "<HR></font>\n";
		$message = stripslashes($myrow[msg_text]);
		echo $message . "<BR><BR>";
		echo "<HR>\n";
		echo "&nbsp;&nbsp<a href=\"bb_profile.$phpEx?mode=view&user=$posterdata[user_id]\"><img src=\"$profile_image\" border=0 alt=\"$l_profileof $myrow[poster_name]\"></a>\n";
		if($posterdata["user_viewemail"] != 0) 
			echo "&nbsp;&nbsp;<a href=\"mailto:$posterdata[user_email]\"><IMG SRC=\"$email_image\" BORDER=0 ALT=\"$l_emial $posterdata[username]\"></a>\n";
		if($posterdata["user_web"] != '') {
			if(strstr("http://", $posterdata["user_web"]))
				$posterdata["user_web"] = "http://" . $posterdata["user_web"];
				echo "&nbsp;&nbsp;<a href=\"$posterdata[user_web]\" TARGET=\"_blank\"><IMG SRC=\"$www_image\" BORDER=0 ALT=\"$l_viewsite $posterdata[username]\"></a>\n";
		}
		if($posterdata["user_icq"] != '')
			echo "&nbsp;&nbsp;<a href=\"http://wwp.mirabilis.com/$posterdata[user_icq]\" TARGET=\"_blank\"><IMG SRC=\"http://wwp.icq.com/scripts/online.dll?icq=$posterdata[user_icq]&img=5\" BORDER=0\"></a>";
	
		if($posterdata["user_aim"] != '')
     		echo "&nbsp;&nbsp;<a href=\"aim:goim?screenname=$posterdata[user_aim]&message=Hi+$posterdata[user_aim].+Are+you+there?\"><img src=\"$images_aim\" border=\"0\"></a>";
	
		echo "&nbsp;&nbsp;<IMG SRC=\"images/div.gif\">\n";
		echo "&nbsp;&nbsp;<a href=\"replypmsg.$phpEx?msgid=$myrow[msg_id]&quote=1\"><IMG SRC=\"$reply_wquote_image\" BORDER=\"0\" alt=\"$l_replyquote\"></a>\n";
		echo "&nbsp;&nbsp;<IMG SRC=\"images/div.gif\">\n";
		echo "&nbsp;&nbsp;<a href=\"replypmsg.$phpEx?msgid=$myrow[msg_id]\">$l_reply</a>\n";
		echo "&nbsp;&nbsp;<IMG SRC=\"images/div.gif\">\n";
		echo "&nbsp;&nbsp;<a href=\"$url_phpbb/delpmsg.$phpEx?msgid=$myrow[msg_id]\">$l_delete</a>\n";
	
		echo "</TD></TR>";
	} //while ($myrow = mysql_fetch_array($resultID));
	
	$sql = "UPDATE priv_msgs SET msg_status='1' WHERE (to_userid = $userdata[user_id])";
	if (!mysql_query($sql, $db)) {
		error_die("Error marking the messages as read in the DB.");
	}
	
?>

</TABLE></TD></TR></TABLE>
<TABLE ALIGN="CENTER" BORDER="0" WIDTH="95%">

<TR>
	<TD>
		&nbsp;
	</TD>
	<TD ALIGN="RIGHT">
		<?php make_jumpbox()?>
	</TD>
</TR></TABLE>

<?php

} // if/else

require('page_tail.'.$phpEx);
?>
