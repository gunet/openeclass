<?  session_start(); ?>
<?php
/***************************************************************************
                          whosonline.php  -  description
                             -------------------
    begin                : Thursday, July 20 2000
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
* Thursday, July 20, 2000 - Yokhannan: Fixed spelling on CELLSPACING &
* CELLPADDING. I also added the [$url_phpbb/] settings to all the <a href
* commands.
*/
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = $l_whosonline;
$pagetype = "other";
include('page_header.'.$phpEx);
?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>"><TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php echo $l_username?></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php echo $l_forum?></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
<?php
$sql = "SELECT * FROM whosonline";
if(!$result = mysql_query($sql, $db))
	die("Error - Could not connect to the database</table></table></table>");
if($myrow = mysql_fetch_array($result)) {
	do {
		echo "<TR BGCOLOR=$color2 ALIGN=LEFT>\n";
		if(!stristr($myrow[username], get_syslang_string($sys_lang, "l_guest"))) {
			$thisuser = get_userdata($myrow[username], $db);
			echo "<TD><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\"><a href=\"$url_phpbb/bb_profile.$phpEx?mode=view&user=$thisuser[user_id]\">$thisuser[username]</a></FONT></TD>\n";
		}
		else {
			echo "<TD><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\">Guest</FONT></TD>\n";
		}
		if($myrow[forum] == 0) {
			echo "<TD><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\"><a href=\"$url_phpbb/index.$phpEx\">Forum Index</a></FONT></TD>\n";
		}
		else {
			$forum = get_forum_name($myrow[forum], $db);
			echo "<TD><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\"><a href=\"$url_phpbb/viewforum.$phpEx?forum=$myrow[forum]\">$forum</a></FONT>";
		}
		echo "</TR>\n";
	} while($myrow = mysql_fetch_array($result));
}
else {
	echo "<TD COLSPAN=2><FONT FACE=\"$FontFace\" SIZE=\"$FontSize2\" COLOR=\"$textcolor\"><?php echo $l_nousers?></FONT></TD>";
}
?>
</TR></TABLE></TD></TR></TABLE>

<?php
include('page_tail.'.$phpEx);
?>
