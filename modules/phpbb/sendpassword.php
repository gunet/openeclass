<?  session_start(); ?>
<?php
  /***************************************************************************
   *                        sendpassword.php  -  description
   *                              -------------------
   *    begin                : Thurs Nov. 16 2000
   *    copyright            : (C) 2001 The phpBB Group
   * 	  email                : support@phpbb.com
   * 
   *     $Id$
   * 
   *  ***************************************************************************/
  
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
$pagetype = "other";
$pagetitle = "Send Password";
include('page_header.'.$phpEx);

if($actkey) {
	$sql = "SELECT user_id FROM users WHERE user_actkey = '$actkey'";
	if(!$r = mysql_query($sql, $db))
		error_die("Error while attempting to query the database");
   if(mysql_num_rows($r) != 1) {
		error_die($l_wrongactiv);
   }
   else {
      list($update_id) = mysql_fetch_array($r);
   }
   $sql = "UPDATE users SET user_password = user_newpasswd WHERE user_id = '$update_id'";
   if(!$r = mysql_query($sql, $db))                        
     error_die("Error while attempting to query the database");  
?>
     <TABLE BORDER="0" WIDTH="<?php echo $TableWidth?>" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP">
     <TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
     <TD COLSPAN="2" ALIGN="CENTER"><b><?php echo $l_password?></b></TD>
     </TR>                              
     <TR ALIGN="LEFT">                  
     <TD BGCOLOR="<?php echo $color2?>"><?php echo $l_passchange?>
     </TD></TR></TABLE></TABLE>
<?php     
}
else if($submit) {
   $checkinfo = get_userdata($user, $db);
   if($checkinfo[user_email] != $email) {
      error_die("$l_wrongmail $l_tryagain");
   }

   $chars = array( 
		  "a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J",
		  "k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T",
		  "u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8",
		  "9","0"
		  );
   $max_elements = count($chars) - 1;
   srand((double)microtime()*1000000);
   $newpw = $chars[rand(0,$max_elements)];
   $newpw .= $chars[rand(0,$max_elements)];
   $newpw .= $chars[rand(0,$max_elements)];  
   $newpw .= $chars[rand(0,$max_elements)]; 
   $newpw .= $chars[rand(0,$max_elements)]; 
   $newpw .= $chars[rand(0,$max_elements)]; 
   $newpw .= $chars[rand(0,$max_elements)]; 
   $newpw .= $chars[rand(0,$max_elements)];
   $newpw_enc = md5($newpw);
   
   // Don't ask...
   $key = md5(md5(md5($newpw_enc)));
   
   $sql = "UPDATE users SET user_actkey = '$key', user_newpasswd = '$newpw_enc' WHERE user_id = '$checkinfo[user_id]'";
   if(!$r = mysql_query($sql, $db)) {
		error_die("An error occured while tring to update the database. Please go back and try again.");
   }
	
	eval("\$message =\"$l_pwdmessage\";");
   mail($email, $l_passsubj, $message, "From: $email_from\r\nX-Mailer: phpBB/$phpbbversion");
?>
     <TABLE BORDER="0" WIDTH="<?php echo $TableWidth?>" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP">
     <TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
     <TD COLSPAN="2" ALIGN="CENTER"><b><?php echo $l_password?></b></TD>
     </TR>
     <TR ALIGN="LEFT">
     <TD BGCOLOR="<?php echo $color2?>"><?php echo $l_passsent?>
     </TD>
     </TR>
     </TABLE></TABLE>
<?php     
}
else {
?>
     <FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
     <TABLE BORDER="0" WIDTH="<?php echo $TableWidth?>" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP"><TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
     <TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
       <TD COLSPAN="2" ALIGN="CENTER"><b><?php echo $l_emailpass?></b><br><?php echo $l_passexplain?>
     </TR>
     <TR ALIGN="LEFT">
       <TD BGCOLOR="<?php echo $color1?>"><?php echo $l_username?>:</TD>
       <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="user" VALUE="<?php echo $userdata[username]?>" SIZE="35" MAXLENGHT="50"></TD>
     </TR>
     <TR ALIGN="LEFT">
       <TD BGCOLOR="<?php echo $color1?>"><?php echo $l_emailaddress?>:</TD>
       <TD BGCOLOR="<?php echo $color2?>"><INPUT TYPE="TEXT" NAME="email" SIZE="35" MAXLENGTH="100"></TD>
     </TR>
     <TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
       <TD COLSPAN="2" ALIGN="CENTER"><INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo $l_sendpass?>"></TD>
     </TR>
     
     </TABLE></TABLE>
       
     
<?php
}
include('page_tail.'.$phpEx);
?>
