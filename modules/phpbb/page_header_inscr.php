<?php
/***************************************************************************
                          page_header.php  -  description
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

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;



/* Who's Online Hack */
$IP=$REMOTE_ADDR;

if($pagetype == "index") {
	$users_online = get_whosonline($IP, $userdata[username], 0, $db);
}
if($pagetype == "viewforum" || $pagetype == "viewtopic") {
	$users_online = get_whosonline($IP, $userdata[username], $forum, $db);
}
if($pagetype == "admin") {
	$header_image = "../$header_image";
}


$login_logout_link = make_login_logout_link($user_logged_in, $url_phpbb);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<TITLE><?php echo "$sitename $l_forums - $pagetitle" ?></TITLE>
<?php
if($l_special_meta) {
	echo $l_special_meta . "\n";
}
if($forward) {
	echo "<META HTTP-EQUIV=\"refresh\" content=\"3;URL=$url_phpbb/viewtopic.$phpEx?topic=$topic&forum=$forum&$total_topic\">";
} 
$meta = showmeta($db);
?>
<?php echo $meta?>
</HEAD>
<BODY BGCOLOR="<?php echo $bgcolor?>" TEXT="<?php echo $textcolor?>" LINK="<?php echo $linkcolor?>" VLINK="<?php echo $vlinkcolor?>">
<font face="<?php echo $FontFace?>">
<?php


echo "<center><table border=0 width=\"600\" cellpadding=\"5\" align=\"center\"><tr><td colspan=4>";
include('../cours/settings.php');
$cheminpage="<a href=../$dbname/index.php>$intitule</a>&nbsp;&gt;&nbsp;<b>Forum</b>";
include('../cours/header.php');
echo "</center>";

//showheader($db);

//  Table layout (col and rowspans are marked with '*' and '-')
//  *one*   | two
//  *three* | four
//  -five-  | -six-

// cell one and three in the first TD with rowspan (logo)
?>
<TR>                    
        <TD ALIGN="left" WIDTH="50%" ROWSPAN="2"><h2>S'enregistrer</2>
			</TD>
<?php
// Switch for cell two  (posts buttons)
switch($pagetype) {
	// 'index' is covered by default
	case 'newtopic':
?>
	<TD ALIGN="CENTER">
		<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b>Υποβολή 
νέου θέματος στο:<BR>
		<a href="<?php echo $url_phpbb?>/viewforum.<?php echo $phpEx ?>?forum=<?php echo $forum?>"><?php echo $forum_name?></a></b>
		</font>
	</TD>
<?php
	break;
	case 'viewforum':
?>
	<TD ALIGN="CENTER">
		<a href="<?php echo $url_phpbb?>/newtopic.<?php echo $phpEx ?>?forum=<?php echo $forum?>"><IMG SRC="<?php echo $newtopic_image?>" BORDER="0"></a>
	</TD>
<?php
	break;
	case 'viewtopic':
?>
	<TD ALIGN="CENTER">
		<a href="<?php echo $url_phpbb?>/newtopic.<?php echo $phpEx ?>?forum=<?php echo $forum?>">
			<IMG SRC="<?php echo $newtopic_image?>" BORDER="0"></a>&nbsp;&nbsp;
<?php
	if($lock_state != 1) {
?>
		<a href="<?php echo $url_phpbb?>/reply.<?php echo $phpEx ?>?topic=<?php echo $topic?>&forum=<?php echo $forum?>">
			<IMG SRC="<?php echo $reply_image?>" BORDER="0"></a></TD>
<?php
	}
	else
			echo "<img src=\"$reply_locked_image\" BORDER=0>\n";
?>
	</TD>
<?php
	break;
	// 'Register' is covered by default
	case '':
?>
<?php
        default:
?>
	<TD ALIGN="CENTER">

	</TD>
<?php
	break;
}  // End for switch cell two
// Cell four (block with links)
?>
</TR>
<TR>
	<TD ALIGN="right">
		
<?php
		if ($user_logged_in)
		{
			// do PM notification.
			$last_visit_date = date("Y-m-d h:i", $last_visit);

			$username = addslashes($userdata[username]);

			$sql = "SELECT count(*) AS count
			      FROM priv_msgs p, users u
			      WHERE p.to_userid = u.user_id and p.msg_status = '0' and u.username = '$username'";

			if(!$result = mysql_query($sql, $db))
			{
				error_die("phpBB was unable to check private messages because " .mysql_error($db));
			}

			$row = @mysql_fetch_array($result);
			$new_message = $row[count];
			$word = ($new_message > 1) ? "messages" : "message";
			$privmsg_url = "$url_phpbb/viewpmsg.$phpEx";

			if ($new_message != 0)
			{
				eval($l_privnotify);
				print $privnotify;
			}
		}
?>		
		
		</font>
	</TD>
</TR>
<?php
//Third row with cell five and six (misc. information)
switch($pagetype) {
	case 'index':
	$total_posts = get_total_posts("0", $db, "all");
	$total_users = get_total_posts("0", $db, "users");
	$sql = "SELECT username, user_id FROM users WHERE user_level != -1 ORDER BY user_id DESC LIMIT 1";
	$res = mysql_query($sql, $db);
	$row = mysql_fetch_array($res);
	$newest_user = $row["username"];
	$newest_user_id = $row["user_id"];
	$profile_url = "$url_phpbb/bb_profile.$phpEx?mode=view&user=$newest_user_id";
	$online_url = "$url_phpbb/whosonline.$phpEx";

?>
<TR>
	<TD COLSPAN="2" ALIGN="RIGHT">
		<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>">
		<?php 
			eval($l_statsblock);
			print $statsblock;
			print_login_status($user_logged_in, $userdata[username], $url_phpbb);
		?>
		</font>
	</TD>
</TR>
<?php
	break;
	case 'newforum':
	// No third row
	break;
	case 'viewforum':
?>
<TR>
	<TD COLSPAN="2" ALIGN="LEFT">
	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		<b><?php echo $forum_name?></b>
		<BR>
		<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>">
			<?php echo $l_moderatedby?>:
<?php
$count = 0;     
$forum_moderators = get_moderators($forum, $db);
   while(list($null, $mods) = each($forum_moderators)) {
      while(list($mod_id, $mod_name) = each($mods)) {
	 if($count > 0)
	   echo ", ";
	 echo "<a href=\"bb_profile.$phpEx?mode=view&user=$mod_id\">".trim($mod_name)."</a>";
	 $count++;
      }
   }
?></font></TD>
</TR>

<?php
	case 'viewtopic':
	$total_forum = get_total_posts($forum, $db, 'forum');
?>
<TR>
	<TD COLSPAN="2" ALIGN="LEFT">
	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>">
		<a href="<?php echo $url_phpbb?>/index.<?php echo $phpEx ?>"><?php echo $sitename?> Forum Index</a>
		<b><?php echo $l_separator?></b>
		<a href="<?php echo "$url_phpbb/viewforum.$phpEx?forum=$forum&$total_forum"?>"><?php echo stripslashes($forum_name)?></a> 
<?php
        if($pagetype != "viewforum")
		echo "<b>$l_separator</b>";
?>
		<?php echo $topic_subject?>
	</TD>
</TR>
<?php
	break;
	case 'privmsgs':
?>
<TR>
        <TD COLSPAN="2" ALIGN="CENTER">
	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		[<a href="<?php echo $url_phpbb?>/sendpmsg.<?php echo $phpEx ?>"><?php echo $l_sendpmsg?></a>]
	<br>
        </TD>
</TR>
<?php
	break;
}
?>
</TABLE>
