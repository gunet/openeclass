<?  session_start(); ?>
<?php
/***************************************************************************
                            bb_register.php  -  description
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
$pagetitle = $l_register;
$pagetype = "Register";

if($submit) {
	// To avoid getting usernames that are unique to the database but look the same
	// in a browser, we remove HTML/PHP tags, strip whitespace from the start
	// and end of the username, and then normalize all internal whitespace in the
	// username.
	$username = strip_tags($username);
	$username = trim($username);
	$username = normalize_whitespace($username);
	$username = addslashes($username);

   if(trim($password) == '' || trim($username) == '' || trim($email) == '') {
      include('page_header_inscr.'.$phpEx);
      error_die("$l_notfilledin $l_tryagain");
   }
   
   if (check_username($username, $db)) {                            
      include('page_header_inscr.'.$phpEx);
      error_die("$l_invalidname $l_tryagain");
   }      
   if(validate_username($username, $db) == 1) {
     include('page_header_inscr.'.$phpEx);
     error_die("$l_disallowname $l_tryagain");
   }
   
   if($password != $password_rep) {
      include('page_header_inscr.'.$phpEx);
      error_die($l_mismatch);
   }
  
   $sig = chop($sig); // Strip all trailing whitespace.
   $sig = str_replace("\n", "<BR>", $sig);
   $sig = addslashes($sig);
   $occ = addslashes($occ);
   $intrest = addslashes($intrest);
   $from = addslashes($from);
   $passwd = md5($password);
	$email = addslashes($email);
   $regdate = date("M d, Y");
   
   // Ensure the website URL starts with "http://".
	$website = trim($website);
	if(substr(strtolower($website), 0, 7) != "http://")
	{
		$website = "http://" . $website;
	}
	
   // If no website entered, make it blank.
   if($website == "http://")
     $website = "";

	$website = addslashes($website);
   
   // Check if the ICQ number only contains digits
   $icq = (ereg("^[0-9]+$", $icq)) ? $icq : '';

	$aim = addslashes($aim);
	$yim = addslashes($yim);
	$msnm = addslashes($msnm);
  
   if($viewemail == 1) {
      $sqlviewemail = "1";
   }
   else {
      $sqlviewemail = "0";
   }
   $sql = "SELECT max(user_id) AS total FROM users";
   if(!$r = mysql_query($sql, $db))
     die("Error connecting to the database.");
   list($total) = mysql_fetch_array($r);
   $total += 1;
   $sql = "INSERT INTO users (user_id, username, user_regdate, user_email, user_icq, user_password, user_occ, user_intrest, user_from, user_website, user_sig, user_aim, user_viewemail, user_yim, user_msnm) 
				VALUES ('$total', '$username', '$regdate', '$email', '$icq', '$passwd', '$occ', '$intrest', '$from', '$website', '$sig', '$aim', '$sqlviewemail', '$yim', '$msnm')";

   if(!$result = mysql_query($sql, $db)) {
      include('page_header_inscr.'.$phpEx);
      die("An Error Occured while trying to add the information into the database. Please go back and try again. <BR>$sql<BR>$mysql_error()");
   }

   if($cookie_username) {
      $time = (time() + 3600 * 24 * 30 * 12);
      setcookie($cookiename, $total, $time, $cookiepath, $cookiedomain, $cookiesecure);
   }
   include('page_header_inscr.'.$phpEx);
   
   $message = "Welcome to $sitename forums!\nPlease keep this email for your records!\n\n";
   $message  .= "Your account information is as follows:\n";
   $message .= "----------------------------\n";
	$message .= "Username: $username\n";
   $message .= "Password: $password\n";
   $message .="\nPlease do not forget your password as it has been encrypted in our database and we cannot retrive it for you.";
   $message .= " However, should you forget your password we provide an easy to use script to generate and email a new, random, password.\nThank you for registering.";
   $message .= "\r\n$email_sig";
		 
   mail($email, $l_welcomesubj, $l_welcomemail, "From: $email_from");
   echo "<p>$l_beenadded<p>$l_click <a href=\"$url_phpbb/index.$phpEx\">$l_here</a> $l_returnindex<br>$l_thankregister<p><br>";
}
else {
   include('page_header_inscr.'.$phpEx);
   ?>
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	<TABLE BORDER="0" WIDTH="<?php echo $TableWidth?>" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_username?>: *</b></FONT><br><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>">(<?php echo $l_useruniq?>)</FONT></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="username" SIZE="25" MAXLENGTH="40"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_password?>: *</b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="PASSWORD" NAME="password" SIZE="25" MAXLENGTH="25"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo "$l_confirm $l_password"?>: *</b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="PASSWORD" NAME="password_rep" SIZE="25" MAXLENGTH="25"></TD>
	</TR>

	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_emailaddress?>: *<b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="email" SIZE="25" MAXLENGTH="80"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_icqnumber?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="icq" SIZE="10" MAXLENGTH="20"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_aim?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="aim" SIZE="15" MAXLENGTH="80"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_yahoo?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="yim" SIZE="25" MAXLENGTH="80"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_msn?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="msnm" SIZE="25" MAXLENGTH="80"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_website?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="website" SIZE="25" MAXLENGTH="120" VALUE="http://"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_location?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="from" SIZE="25" MAXLENGTH="40"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_occupation?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="occ" SIZE="25" MAXLENGTH="255"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_interests?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="intrest" SIZE="25" MAXLENGTH="255"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_signature?>: </b><br><font size=-2><?php echo $l_sigexplain?></font></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><TEXTAREA NAME="sig" ROWS=6 COLS=45></TEXTAREA></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_options?>: </b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><INPUT TYPE="CHECKBOX" NAME="viewemail" VALUE="1"><?php echo $l_publicmail?><BR>
		<INPUT TYPE="CHECKBOX" NAME="cookie_username" VALUE="1"><?php echo $l_storecookie?><BR>
		</TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" colspan="2"><font size=-1><?php echo $l_itemsreq?></font></TD>
	</TR>
	<TR>
		<TD  BGCOLOR="<?php echo $color1?>" colspan="2" ALIGN="CENTER">
		<INPUT TYPE="HIDDEN" NAME="forum" VALUE="<?php echo $forum?>">
		<INPUT TYPE="HIDDEN" NAME="topic_id" VALUE="<?php echo $topic?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo $l_submit?>">
	</TR>
	</TABLE></TD></TR></TABLE>
	</FORM>
<?php
}
require('page_tail.'.$phpEx);
?>
