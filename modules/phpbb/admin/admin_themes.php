<?php
  /***************************************************************************
   *                           admin_themes.php  -  description
   *                              -------------------
   *     begin                : Tuesday Oct 31 2000 (Happy Halloween :) )
   *     copyright            : (C) 2001 The phpBB Group
	*	   email                : support@phpbb.com
   * 
   *     $Id$
   * 
   *  ***************************************************************************/
  
  /***************************************************************************
   *
   *  This program is free software; you can redistribute it and/or modify
   *  it under the terms of the GNU General Public License as published by
   *  the Free Software Foundation; either version 2 of the License, or
   *  (at your option) any later version.
   *
   ****************************************************************************/
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
     <UL>
     <FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
     <b>User Name: </b><INPUT TYPE="TEXT" NAME="username" SIZE="25" MAXLENGTH="40" VALUE="<?php echo $userdata[username]?>"><BR>
     <b>Password: </b><INPUT TYPE="PASSWORD" NAME="password" SIZE="25" MAXLENGTH="25"><br><br>
     <INPUT TYPE="SUBMIT" NAME="login" VALUE="Submit">&nbsp;&nbsp;&nbsp;<INPUT TYPE="RESET" VALUE="Clear"></ul>
     </FORM>
     </TD></TR></TABLE></TD></TR></TABLE>
     <?php
     include('../page_tail.'.$phpEx);
   exit();
}
else if($user_logged_in && $userdata[user_level] == 4) {
   
   
   $pagetitle = "Theme Administration";
   $pagetype = "admin";
   
   if($mode) {
      include('../page_header.'.$phpEx);
   switch($mode) {
    case 'add':
      if($submit) {
	 while(list($field, $value) = each($_POST)) {
	    if($value == '') {
	       $field_list[] = $field;
	       $die = 1;
	    }
	 }
	 if($die == 1) {
	    echo "You did not fill out all parts of the form, please go back and do so, all fields are required.";
	    include('../page_tail.'.$phpEx);
	    exit();
	 }
	   
	 $theme_name = addslashes($theme_name);
	 $image_header = "images/".$image_header;
	 $image_reply = "images/".$image_reply;
	 $image_newtopic = "images/".$image_newtopic;
	 $image_replylocked = "images/".$image_replylocked;
	 
	 $sql = "INSERT INTO themes (theme_name, bgcolor, textcolor, color1, color2, table_bgcolor, header_image, newtopic_image, reply_image, linkcolor, vlinkcolor, theme_default, fontface, fontsize1, fontsize2, fontsize3, fontsize4, tablewidth, replylocked_image) 
	         VALUES ('$theme_name', '$theme_bgcolor', '$theme_textcolor', '$theme_color1', '$theme_color2', '$theme_tablebg', '$image_header', '$image_newtopic', '$image_reply', '$theme_linkcolor', '$theme_vlinkcolor', '0', '$theme_fontface', '$theme_fontsize1', '$theme_fontsize2', '$theme_fontsize3', '$theme_fontsize4', '$theme_tablewidth', '$image_replylocked')";
	 if(!$r = mysql_query($sql, $db)) {
	    echo "Error inserting theme into the database.<BR>".mysql_error($db)."\n";
	    include('../page_tail.'.$phpEx);
	    exit();
	 }
?>
	   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	   <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	   <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Theme Added Successfully!</B></TD></TR>
	   <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	   <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Click <a href="<?php echo $PHP_SELF?>">here</a> to return to the Theme Administration panel. Or click <a href="<?php echo $url_admin_index?>">here</a> to return to the admin panel.</FONT></TD>
	   </TR>
	   </TABLE></TABLE>
<?php
	   
	   
	 
      }
      else {
?>	 
	   <FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	   <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	            <TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Add Theme</B></FONT></TD>
	   </TR>
	   <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	         <TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
	         <B>NOTE:</B> Image locations are relitive to your phpBB images dir. Therefor, if your header image is in phpBB/images/mytheme/header.jpg you would simply enter mytheme/header.jpg.<BR> Please be sure to remeber to upload your images after you have created this theme if you have not already done so.
	         </TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Theme Name:</TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="35" NAME="theme_name"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Background Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_bgcolor"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Text Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_textcolor"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Table Color 1:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_color1"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Table Color 2:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_color2"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Table Border Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_tablebg"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Link Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_linkcolor"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Visited Link Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_vlinkcolor"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Font:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: Verdana,Tahoma)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="100" NAME="theme_fontface"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Normal Font Size:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: 1)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="2" MAXLENTH="5" NAME="theme_fontsize1"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Header Font Size:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: 2)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="2" MAXLENTH="5" NAME="theme_fontsize2"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Small Font Size:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: -2)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="2" MAXLENTH="5" NAME="theme_fontsize3"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Large Font Size:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: +1)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="2" MAXLENTH="5" NAME="theme_fontsize4"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Table Width:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: 95%)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="4" MAXLENTH="5" NAME="theme_tablewidth"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Header Image:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: mytheme/header.jpg)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="255" NAME="image_header"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">New Topic Image:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: mytheme/newtopic.jpg)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="255" NAME="image_newtopic"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Reply Image:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: mytheme/reply.jpg)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="255" NAME="image_reply"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Reply Locked Image:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: mytheme/reply-locked.jpg)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="255" NAME="image_replylocked"></TD>
	   </TR>
	   <TR ALIGN="CENTER" BGCOLOR="<?php echo $color1?>">
	   <TD COLSPAN="2">
	   <INPUT TYPE="HIDDEN" NAME="mode" VALUE="add">
	   <INPUT TYPE="SUBMIT" NAME="submit" VALUE="Save Theme">&nbsp;&nbsp;<INPUT TYPE="RESET" VALUE="Clear">
	   </TD>
	   </TR>
	   </TABLE></TABLE>
	   </FORM>
<?php	   
      }
      break;
    case 'remove':
      $sql = "DELETE FROM themes WHERE theme_id = '$theme_id'";
      if(!$r = mysql_query($sql, $db))
	die("Error updateing the databse. Go back and try again");
?>
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	          <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Theme Removed!</B></TD></TR>
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	          <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Click <a href="<?php echo $PHP_SELF?>">here</a> to return to the Theme Administration panel. Or click <a href="<?php echo $url_admin_index?>">here</a> to return to the admin panel.</FONT></TD>
	</TR>
	</TABLE></TABLE>
<?php
	
      break;
    case 'edit':
      if($submit) {
         while(list($field, $value) = each($_POST)) {	 
	    if($value == '') {
	       $field_list[] = $field;
	       $die = 1;
	    }
	 }
	 if($die == 1) {
	    echo "You did not fill out all parts of the form, please go back and do so, all fields are required.";
	    include('../page_tail.'.$phpEx);
	    exit();
	 }
	 
	 $theme_name = addslashes($theme_name);
	 $image_header = "images/".$image_header;
	 $image_reply = "images/".$image_reply;
	 $image_newtopic = "images/".$image_newtopic;
	 $image_replylocked = "images/".$image_replylocked;
	 
	 $sql = "UPDATE themes SET
		  theme_name        = '$theme_name',
		  bgcolor           = '$theme_bgcolor',
		  textcolor         = '$theme_textcolor',
		  color1            = '$theme_color1',
		  color2            = '$theme_color2',
		  table_bgcolor     = '$theme_tablebg',
		  header_image      = '$image_header',
		  newtopic_image    = '$image_newtopic',
		  reply_image       = '$image_reply',
		  linkcolor         = '$theme_linkcolor',
		  vlinkcolor        = '$theme_vlinkcolor',
		  theme_default     = '$theme_default',
		  fontface          = '$theme_fontface',
		  fontsize1         = '$theme_fontsize1',
		  fontsize2         = '$theme_fontsize2',
		  fontsize3         = '$theme_fontsize3',
		  fontsize4         = '$theme_fontsize4',
		  tablewidth        = '$theme_tablewidth',
		  replylocked_image = '$image_replylocked' 
		  WHERE theme_id = '$theme_id'";
	 if(!$r = mysql_query($sql, $db))
	   die("Error updateing the database!");
?>
	   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	   <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	   <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Theme Updated!</B></TD></TR>
	   <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	   <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Click <a href="<?php echo $PHP_SELF?>">here</a> to return to the Theme Administration panel. Or click <a href="<?php echo $url_admin_index?>">here</a> to return to the admin panel.</FONT></TD>
	   </TR>
	   </TABLE></TABLE>
<?php
      }
      else {
	 $sql = "SELECT * FROM themes WHERE theme_id = '$theme_id'";
	 if(!$r = mysql_query($sql, $db)) {
	    echo "Error selecting theme from the database. Please go back and try again.<BR>";
	    include('page_tail.'.$phpEx);
	    exit();
	 }
	 $m = mysql_fetch_array($r);
?>	 
           <FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	   <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	   <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	   <TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Add Theme</B></FONT></TD>
	   </TR>
	   <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	   <TD ALIGN="CENTER" COLSPAN="2"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
	   <B>NOTE:</B> Image locations are relitive to your phpBB images dir. Therefor, if your header image is in phpBB/images/mytheme/header.jpg you would simply enter mytheme/header.jpg.<BR> Please be sure to remeber to upload your images after you have created this theme if you have not already done so.
	   </TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Theme Name:</TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="35" NAME="theme_name" VALUE="<?php echo $m[theme_name]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Background Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_bgcolor" VALUE="<?php echo $m[bgcolor]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Text Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_textcolor" VALUE="<?php echo $m[textcolor]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Table Color 1:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_color1" VALUE="<?php echo $m[color1]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Table Color 2:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_color2" VALUE="<?php echo $m[color2]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Table Border Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_tablebg" VALUE="<?php echo $m[table_bgcolor]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Link Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_linkcolor" VALUE="<?php echo $m[linkcolor]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Visited Link Color:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(In HEX, eg.: #000000 = Black)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="10" MAXLENTH="10" NAME="theme_vlinkcolor" VALUE="<?php echo $m[vlinkcolor]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Font:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: Verdana,Tahoma)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="100" NAME="theme_fontface" VALUE="<?php echo $m[fontface]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Normal Font Size:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: 1)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="2" MAXLENTH="5" NAME="theme_fontsize1" VALUE="<?php echo $m[fontsize1]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Header Font Size:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: 2)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="2" MAXLENTH="5" NAME="theme_fontsize2" VALUE="<?php echo $m[fontsize2]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Small Font Size:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: -2)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="2" MAXLENTH="5" NAME="theme_fontsize3" VALUE="<?php echo $m[fontsize3]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Large Font Size:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: +1)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="2" MAXLENTH="5" NAME="theme_fontsize4" VALUE="<?php echo $m[fontsize4]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Table Width:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: 95%)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="4" MAXLENTH="5" NAME="theme_tablewidth" VALUE="<?php echo $m[tablewidth]?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Header Image:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: mytheme/header.jpg)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="255" NAME="image_header" VALUE="<?php echo str_replace("images/", "", $m[header_image])?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">New Topic Image:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: mytheme/newtopic.jpg)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="255" NAME="image_newtopic" VALUE="<?php echo str_replace("images/", "", $m[newtopic_image])?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Reply Image:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: mytheme/reply.jpg)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="255" NAME="image_reply" VALUE="<?php echo str_replace("images/", "", $m[reply_image])?>"></TD>
	   </TR>
	   <TR ALIGN="LEFT">
	   <TD BGCOLOR="<?php echo $color1?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Reply Locked Image:<BR>
	   <FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><I>(eg.: mytheme/reply-locked.jpg)</I></FONT></TD>
	   <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" SIZE="35" MAXLENTH="255" NAME="image_replylocked" VALUE="<?php echo str_replace("images/", "", $m[replylocked_image])?>"></TD>
	   </TR>
	   <TR ALIGN="CENTER" BGCOLOR="<?php echo $color1?>">
	   <TD COLSPAN="2">
	   <INPUT TYPE="HIDDEN" NAME="mode" VALUE="edit">
	   <INPUT TYPE="HIDDEN" NAME="theme_default" VALUE="<?php echo $m[theme_default]?>">
	   <INPUT TYPE="HIDDEN" NAME="theme_id" VALUE="<?php echo $theme_id?>">
	   <INPUT TYPE="SUBMIT" NAME="submit" VALUE="Save Theme">&nbsp;&nbsp;<INPUT TYPE="RESET" VALUE="Clear">
	   </TD>
	   </TR>
	   </TABLE></TABLE>
	   </FORM>
<?php	 
      }
      
      
      break;
    case 'setdefault':
      $sql = "UPDATE themes SET theme_default = 0";
      if(!$r = mysql_query($sql, $db))
	die("Error updateing the databse. Go back and try again");
      $sql = "UPDATE themes SET theme_default = 1 WHERE theme_id = '$theme_id'";
      if(!$r = mysql_query($sql, $db))
	        die("Error updateing the databse. Go back and try again");      
      ?>
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	     <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Theme Updated!</B></TD></TR>
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	             <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">Click <a href="<?php echo $PHP_SELF?>">here</a> to return to the Theme Administration panel. Or click <a href="<?php echo $url_admin_index?>">here</a> to return to the admin panel.</FONT></TD>
	</TR>
	</TABLE></TABLE>
<?php
      break;
    case 'view':
      break;
   }
}
else {
   include('../page_header.'.$phpEx); 
?>
     <FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
           <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><B>Theme Administration</B></FONT></TD>
     </TR>
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
           <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
           Select a theme from the following list to edit/delete. Or click the Add Theme link to add a new theme to the database.<BR>
           <B>NOTE:</B> User selected themes will overide the default theme unless you have selected 'Overide user theme selection' in the Board setup.</FONT></TD>
     </TR>
<?php
     $sql = "SELECT theme_name, theme_id, theme_default FROM themes ORDER BY theme_name";
   if(!$r = mysql_query($sql, $db)) {
      echo "<TR BGCOLOR=$color2 ALIGN=CENTER><TD COLSPAN=3>Error: Could not query the database!<BR>".mysql_error($db)."</TD></TR></TABLE></TABLE>";
      include('../page_tail.'.$phpEx);
      exit();
   }
   echo "<TR BGCOLOR=\"$color1\" ALIGN=\"CENTER\"><TD>Name</TD><TD>Default Theme?</TD><TD>Action</TD>";
   if($row = mysql_fetch_array($r)) {
      do {
	 echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">\n";
	 echo "<TD>".stripslashes($row[theme_name])."</TD>\n";
	 if($row[theme_default] == 1)
	   echo "<TD>Yes</TD>";
	 else
	   echo "<TD>No (<a href=\"$PHP_SELF?mode=setdefault&theme_id=$row[theme_id]\">Make Default</a>)</TD>";
	 echo "<TD><a href=\"$PHP_SELF?mode=edit&theme_id=$row[theme_id]\">Edit</a>&nbsp;&nbsp;<a href=\"$PHP_SELF?mode=remove&theme_id=$row[theme_id]\">Delete</a></TD>";
	 echo "</TR>";
      } while($row = mysql_fetch_array($r));
   }
   else
     echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\"><TD COLSPAN=\"3\">No Themes in the database. Click <a href=\"$PHP_SELF?mode=add\">here</a> to add one.</TD></TR>";
?>
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
           <TD ALIGN="CENTER" COLSPAN="3"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
           <a href="<?php echo $PHP_SELF?>?mode=add">Add a New Theme</a>
           </TD>
     </TR>
     </TABLE></TABLE>
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

