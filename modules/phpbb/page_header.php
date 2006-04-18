<?php


session_register("forumId");
session_register("userGroupId");

// added by jexi
header("Content-type: text/html; charset=ISO-8859-7");

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


include('../../config/config.php');
include('../../include/settings.php');
include("../lang/english/phpbb.inc");
@include("../lang/$language/phpbb.inc");

//added by jexi
include('../lang/english/trad4all.inc.php');
include("../lang/$language/trad4all.inc.php");

$nameTools = $l_forums;
//</ul>$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<HTML>
<HEAD>
	<TITLE>
		<?php echo " - $pagetitle" ?>
		<?php echo "$nameTools - $intitule - $siteName"; ?>
	</TITLE>

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
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?php echo $mainInterfaceWidth?>">
		<tr>
			<td colspan="4">

				<?php 
				$noPHP_SELF = true; //because  phpBB need always param IN URL
				include('../../include/phpbb_header.php');
echo "
				<br>
				<a href=\"./search.php?addterms=any&forum=all&sortby=p.post_time%20desc&searchboth=both&submit=Rechercher\">$langLastMsgs</a>
			</td>
		</tr>
	</table>
</center>";

//showheader($db);

//  Table layout (col and rowspans are marked with '*' and '-')
//  *one*   | two
//  *three* | four
//  -five-  | -six-

// cell one and three in the first TD with rowspan (logo)

echo "<center><table width=600><TR><TD ALIGN=left WIDTH=50% ROWSPAN=2>
	<font size=3 face=\"arial, helvetica\"><b>$l_forums</b>&nbsp;";

if($status[$dbname]==1 OR $status[$dbname]==2) {

echo "<br><font size=2><a href=../forum_admin/forum_admin.php>$langAdm</a>";

}	// if prof or allowed to admin	



##################################################################
#######  RELATE TO GROUP DOCUMENT AND SPACE FOR CLAROLINE  #######
##################################################################


// Determine if Forums are private. O=public, 1=private
$forumPriv=mysql_query("SELECT private FROM group_properties");
while ($myForumPriv = mysql_fetch_array($forumPriv)) 
{
	$privProp=$myForumPriv['private'];
	// echo "<br>privProp: $privProp<br>";	// Debugging
}

// Determine if uid is tutor for this course
$sqlTutor=mysql_query("SELECT tutor FROM $mysqlMainDb.cours_user
				WHERE user_id='$uid'
					AND code_cours='$dbname'");
while ($myTutor = mysql_fetch_array($sqlTutor)) 
{
	$tutorCheck=$myTutor[tutor];
	 // echo "<br>tutorCheck: $tutorCheck<br>uid $uid";	// Debugging
}

// Determine if forum category is Groups
$forumCatId=mysql_query("SELECT cat_id FROM forums WHERE forum_id='$forum'");
while ($myForumCat = mysql_fetch_array($forumCatId)) 
{
	$catId=$myForumCat[cat_id];
	// echo "<br>catId: $catId<br>";	// Debugging
}

// Check which group and which forum user is a member of
$findTeamUser=mysql_query("SELECT team, forumId, tutor FROM student_group, user_group 
				WHERE user_group.user='$uid'
					AND student_group.id=user_group.team");

while ($myTeamUser = mysql_fetch_array($findTeamUser)) 
{
	$myTeam=$myTeamUser[team];
	$myGroupForum=$myTeamUser[forumId];
	$myTutor=$myTeamUser[tutor];
	// echo "<br><br>uid : $uid Team : $myTeam<br> ForumId: $myGroupForum<br>tuteur : $myTutor";	// Debugging
}

// Show Group Documents and Group Space only if in Category 2 = Group Forums Category
if (($catId==1) AND ($forum==$myGroupForum))
{

	// Added by Thomas group space links
	echo "<BR><BR><a href=\"../group/group_space.php\">$langGroupSpaceLink</a>&nbsp;&nbsp;
	<a href=\"../group/document.php?userGroupId=$userGroupId\">$langGroupDocumentsLink</a><br><br>";
}


##################################################################





echo "</TD>";

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
	<TD ALIGN="right"><font face="arial, helvetica" size=2>
		<a href="newtopic.php?forum=<?php echo $forum?>">
	
<?php

		echo "$langNewTopic</a></TD>";
	break;
	case 'viewtopic':
?>
	<TD ALIGN="right"><font size=2 face="arial, helvetica">
<?php
	if($lock_state != 1) {
echo "<a href=\"$url_phpbb/reply.php?topic=$topic&forum=$forum\"><font color=#0000FF>$langAnswer</font color></a></TD>";
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
	<TD ALIGN=\"left\">

	</TD>
<?php
	break;
}  // End for switch cell two
// Cell four (block with links)
?>
</TR>
<TR>
	<TD ALIGN="right" valign=top>
<?
if($status[$dbname]==1 OR $status[$dbname]==2) {
?>

<a href="../help/help.php?topic=For&language=<?= $languageInterface ?>" 
onClick="window.open('../help/help.php?topic=For&language=<?= $languageInterface ?>','Help','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); 
return false;">
<font size=2 face ="arial, helvetica">
<?= $langHelp ?>
</font>
</a>

<?
		}	// END IF PROF OR ASSISTANT

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
			// print $statsblock;
			// print_login_status($user_logged_in, $userdata[username], $url_phpbb);   // deactivated by CLAROLINE
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
			<?php 
		
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
	// break;
}
?>
</TABLE> </center>
