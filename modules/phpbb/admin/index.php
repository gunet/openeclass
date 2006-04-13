<?php
/***************************************************************************
                          admin.php  -  description
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
include('../extention.inc');
include('../functions.'.$phpEx);
include('../config.'.$phpEx);
require('../auth.'.$phpEx);

if($login) {
   if ($username == '') {
      die("You have to enter your username. Go back and do so.");
   }
   if ($password == '') {
      die("You have to enter your password. Go back and do so.");
   }
   if (!check_username($username, $db)) {
      die("Invalid username \"$username\". Go back and try again.");
   }
   if (!check_user_pw($username, $password, $db)) {
      die("Invalid password. Go back and try again.");
   }

   $userdata = get_userdata($username, $db);
   $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);
   set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);

   if (defined('USE_IIS_LOGIN_HACK') && USE_IIS_LOGIN_HACK)
	{
		echo "<META HTTP-EQUIV=\"refresh\" content=\"1;URL=$url_admin_index\">";
	}
	else
	{
		header("Location: $url_admin_index");
	}

}
else if(!$user_logged_in) {
   $pagetitle = "Forum Administration";
   $pagetype = "admin";
   include('../page_header.'.$phpEx);

?>
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>">
     <TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
     <TD><P><BR><FONT FACE="<?php echo $FontFace?>" SIZE="<? echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
     Please enter your username and password to login.<BR>
     <i>(NOTE: You MUST have cookies enabled in order to login to the administration section of this forum)</i><BR>
     <FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
     <table>
     <tr>
       <td><b>User Name: </b></td>
       <td><INPUT TYPE="TEXT" NAME="username" SIZE="25" MAXLENGTH="40" VALUE="<?php echo $userdata[username]?>"></td>
     </tr><tr>
       <td><b>Password: </b></td>
       <td><INPUT TYPE="PASSWORD" NAME="password" SIZE="25" MAXLENGTH="25"><br></td>
     </tr><tr>
       <td>&nbsp;</td>
       <td><INPUT TYPE="SUBMIT" NAME="login" VALUE="Submit"></td>
     </tr>
     </table>
     </FORM>
     </TD></TR></TABLE></TD></TR></TABLE>
<?php
     include('../page_tail.'.$phpEx);
     exit();
}
else if($user_logged_in && $userdata[user_level] == 4) {

$pagetitle = "Forum Administration";
$pagetype = "admin";
include('../page_header.'.$phpEx);

if($mode) {

}
else {
?>
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>phpBB Forum Administration</B></FONT></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_forums.<?php echo $phpEx?>?mode=addforum">Add a Forum</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This Link will take you to a page where you can add a forum to the database.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_forums.<?php echo $phpEx?>?mode=editforum">Edit a Forum</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to edit an existing forum.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_priv_forums.<?php echo $phpEx?>?mode=editforum">Set Private Forum Permissions</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to set the access to an existing private forum.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_board.<?php echo $phpEx?>?mode=sync">Sync forum/topic index</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to sync up the forum and topic indexes to fix any descrepancies that might arise</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_forums.<?php echo $phpEx?>?mode=addcat">Add a Category</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to add a new category to put forums into.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_forums.<?php echo $phpEx?>?mode=editcat">Edit a Category Title</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you edit the title of a category.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_forums.<?php echo $phpEx?>?mode=remcat">Remove a Category</a></FONT></TD>
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link allows you to remove any cagegories from the database</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_forums.<?php echo $phpEx?>?mode=catorder">Re-Order Categories</a></FONT></TD>
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to change the order in which your categories display on the index page</font></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_board.<?php echo $phpEx?>?mode=setoptions">Set Forum-wide Options</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to set various forum-wide options such as allowing HTML in posts.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_board.<?php echo $phpEx?>?mode=rankadmin">Create/Edit User Rankings</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to add different user rankings. Ranks can be assigned to specific users in the modify user section.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_board.<?php echo $phpEx?>?mode=headermetafooter">Set Header/Meta/Footer</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to set both your Meta Commands and Header/Footer text.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_users.<?php echo $phpEx?>?mode=moduser">Modify User</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to modify a user account, including username, level, and rank.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_users.<?php echo $phpEx?>?mode=remuser">Remove a User</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link will allow you to remove any registered user from the database.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_users.<?php echo $phpEx?>?mode=banuser">Ban a User</a></FONT></TD>
	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link allows you to ban a user account, or to ban by IP.</a></FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
       	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_users.<?php echo $phpEx?>?mode=badusernames">Disallow a Username</a></FONT></TD>
       	<TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link allows you to disallow specific usernames. No user will be allowed to register with a disallowed username.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_users.<?php echo $phpEx?>?mode=badwords">Censor Bad Words</a></FONT></TD>
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link allows you to define words that are censored and replace them with whatever you like</a></FONT></TD>
</TR>

<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/smiles.<?php echo $phpEx?>">Edit/Add/Delete Smiles</a></FONT></TD>
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link allows you to edit and delete smiles, and add new ones.</FONT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $url_admin?>/admin_themes.<?php echo $phpEx?>">Add/Edit/Delete Themes</a></FONT></TD>
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">This link allows you to add, edit, and delete forum themes.</FONT></TD>
</TR>
</TABLE></TD></TR></TABLE>
<?php
}
}
else {
   $pagetype = "admin";
   $pagetitle = "Access Denied!";

   include('../page_header.'.$phpEx);
?>
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>">
     <TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="center" VALIGN="TOP">
     <TD><FONT FACE="<?php echo $FontFace?>" SIZE="<? echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
     <B>You do not have acess to this area!</b><BR>
     Go <a href="<?php echo $url_phpbb_index?>">Back</a>
     </TD></TR></TABLE></TD></TR></TABLE>
<?php
}
include('../page_tail.'.$phpEx);
?>
