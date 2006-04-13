<?  session_start(); ?>
<?php
/***************************************************************************
                            prefs.php  -  description
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
$pagetitle = $l_preferences;
$pagetype = "index";

if($submit || $user_logged_in) {
   if($save) {
      if (!$user_logged_in) {
	 // no valid session, need to check user/pass.
	 if($user == '' || $passwd == '') {
	    error_die("$l_userpass $l_tryagain");
	 }
	 $md_pass = md5($passwd);
	 $userdata = get_userdata($user, $db);
	 if($md_pass != $userdata["user_password"]) {
	    error_die("$l_wrongpass $l_tryagain");
	 }	
	 if(is_banned($userdata[user_id], "username", $db))
	   error_die($l_banned);
	 // Log them in, they are authenticated!
	 $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);
	 set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
      }
      
      if($savecookie == 1) {
	 $time = (time() + 3600 * 24 * 30 * 12);
	 setcookie($cookiename, $userdata[user_id], $time, $cookiepath, $cookiedomain, $cookiesecure);
      }
      include('page_header.'.$phpEx);
      
      $sql = "UPDATE users SET user_viewemail='$viewemail', user_theme='$themes', user_attachsig = '$sig', user_desmile = '$smile', user_html = '$dishtml', user_bbcode = '$disbbcode', user_lang = '$lang' WHERE (user_id = '$userdata[user_id]')";
      if(!$result = mysql_query($sql, $db)) {
	 error_die("An Error Occured<hr>Could not update the database. Please go back and try again.");
      }
      echo "<br><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\">";
      echo "<TR><TD  BGCOLOR=\"$table_bgcolor\"><TABLE BORDER=\"0\" CALLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"100%\">";
      echo "<TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD><font face=\"Verdana\" size=\"2\"><P>";
      echo "<P><BR><center>$l_prefupdated<p></center></font>";
      echo "</TD></TR></TABLE></TD></TR></TABLE><br>";

   } else {
      
      if (!$user_logged_in) {
	 // no valid session, need to check user/pass.
	 if($user == '' || $passwd == '') {
	    error_die("$l_userpass $l_tryagain");
	 }
	 $md_pass = md5($passwd);
	 $userdata = get_userdata($user, $db);
	 if($md_pass != $userdata["user_password"]) {
	    include('page_header.'.$phpEx);
	    error_die("$l_wrongpass $l_tryagain");
	 }	
	 if(is_banned($userdata[user_id], "username", $db))
	   error_die($l_banned);
	 $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);
	 set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
      }
      include('page_header.'.$phpEx);
      if($userdata[user_viewemail] == 1) {
	 $y = "CHECKED";
      } else {
	 $n = "CHECKED";
      }
      
      if($userdata[user_attachsig] == 1) 
	$always_sig = "CHECKED";
      else
	$no_always_sig = "CHECKED";
      
      if($userdata[user_desmile] == 1)
	$never_smile = "CHECKED";
      else
	$no_never_smile = "CHECKED";
      
      if($userdata[user_html] == 1)
	$never_html = "CHECKED";
      else
	$no_never_html = "CHECKED";
      
      if($userdata[user_bbcode] == 1)
	$never_bbcode = "CHECKED";
      else
	$no_never_bbcode = "CHECKED";
      
      if(isset($HTTP_COOKIE_VARS[$cookiename])) {
	 $user_cookie = "CHECKED";
      } else {
	 $user_nocookie = "CHECKED";
      }
?>
<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
<TABLE BORDER="0" CELLPADDING="1" CELLSPACEING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $tablewidth?>"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
<TABLE BORDER="0" CALLPADDING="1" CELLSPACEING="1" WIDTH="100%">
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD COLSPAN="2" ALIGN="CENTER"><b><?php echo $l_editprefs?></b></TD>
</TR>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD COLSPAN="2" ALIGN="CENTER"><font size=-1><?php echo $l_themecookie?></font></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><?php echo $l_username?>:</TD>
	<TD><?php echo $userdata[username]?></TD>
</TR>
<?PHP
	if (!$user_logged_in) { 
		// no session, need a password.
		echo "    <TR BGCOLOR=\"$color2\" ALIGN=\"LEFT\"> \n";
		echo "        <TD>$l_password:</TD> \n";
		echo "        <TD><INPUT TYPE=\"PASSWORD\" NAME=\"passwd\" SIZE=\"25\" MAXLENGTH=\"25\"></TD> \n";
		echo "    </TR> \n";
	}
?>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><?php echo $l_publicmail?>:</TD>
	<TD><INPUT TYPE="RADIO" NAME="viewemail" VALUE="1" <?php echo $y?>><?php echo $l_yes?> 
	    <INPUT TYPE="RADIO" NAME="viewemail" VALUE="0" <?php echo $n?>><?php echo $l_no?></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><?php echo $l_storecookie?>:</TD>
	<TD><INPUT TYPE="RADIO" NAME="savecookie" VALUE="1" <?php echo $user_cookie?>><?php echo $l_yes?>
	    <INPUT TYPE="RADIO" NAME="savecookie" VALUE="0" <?php echo $user_nocookie?>><?php echo $l_no?></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
        <TD><?php echo $l_alwayssig?>:</TD>
        <TD><INPUT TYPE="RADIO" NAME="sig" VALUE="1" <?php echo $always_sig?>><?php echo $l_yes?> 
	    <INPUT TYPE="RADIO" NAME="sig" VALUE="0" <?php echo $no_always_sig?>><?php echo $l_no?></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><?php echo $l_alwayssmile?>:</TD>
	<TD><INPUT TYPE="RADIO" NAME="smile" VALUE="1" <?php echo $never_smile?>><?php echo $l_yes?> 
	    <INPUT TYPE="RADIO" NAME="smile" VALUE="0" <?php echo $no_never_smile?>><?php echo $l_no?></TD>
</TR>	     
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	     <TD><?php echo $l_alwayshtml?>:</TD>
	     <TD><INPUT TYPE="RADIO" NAME="dishtml" VALUE="1" <?php echo $never_html?>><?php echo $l_yes?>
	     <INPUT TYPE="RADIO" NAME="dishtml" VALUE="0" <?php echo $no_never_html?>><?php echo $l_no?></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	     <TD><?php echo $l_alwaysbbcode?>:</TD>
	     <TD><INPUT TYPE="RADIO" NAME="disbbcode" VALUE="1" <?php echo $never_bbcode?>><?php echo $l_yes?>
	     <INPUT TYPE="RADIO" NAME="disbbcode" VALUE="0" <?php echo $no_never_bbcode?>><?php echo $l_no?></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><?php echo $l_boardtheme?>:
	</TD>
<?php
	$sql = "SELECT theme_id, theme_name FROM themes ORDER BY theme_name";
	if(!$result = mysql_query($sql, $db))
		error_die("Error: Couldn't get themes data");
	if($myrow = mysql_fetch_array($result)) {
		echo "<TD><SELECT NAME=\"themes\">\n";
		do {
		   unset($s);
		   if($myrow[theme_id] == $userdata["user_theme"])
		     $s = "SELECTED";
		   echo "<OPTION VALUE=\"$myrow[theme_id]\" $s>$myrow[theme_name]</OPTION>\n";
		} while($myrow = mysql_fetch_array($result));
	}
	else {
		echo $l_nothemes;
	}
?>
	</SELECT></TD>
</TR>
<TR BGCOLOR="<?php echo $color2?>" ALIGN="LEFT">
	<TD><?php echo $l_boardlang?>:</TD>
	<td>
<?php
print language_select($default_lang, "lang");
?>
       </td>
<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
	<TD COLSPAN="2" ALIGN="CENTER"><INPUT TYPE="HIDDEN" NAME="save" VALUE="1"><INPUT TYPE="HIDDEN" NAME="user" VALUE="<?php echo $user?>">
	<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo $l_saveprefs?>">
	</TD>
</TR>
</TABLE></TD></TR></TABLE>
<?php
	}
}
else {
	include('page_header.'.$phpEx);
	login_form();
}
include('page_tail.'.$phpEx);
?>
