<?  session_start(); ?>
<?php
/***************************************************************************
                            bb_profile.php  -  description
                             -------------------
    begin                : Sat June 17 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpBB.com

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
$pagetitle = $l_profile;
$pagetype = "Edit Profile";


if($mode) {
	switch($mode) {
	 case 'view':
	   include('page_header.'.$phpEx);
	   $userdata = get_userdata_from_id($user, $db);
	   $total_posts = get_total_posts("0", $db, "all");
	   if($userdata[user_posts] != 0 && $total_posts != 0){
	     $user_percentage = $userdata[user_posts] / $total_posts * 100;
	   } else {
	     $user_percentage = 0;
	   }
	   
	   // Calculate the number of days this user has been a member ($memberdays)
	   $regdate = strtotime($userdata[user_regdate]);
	   $memberdays = (time()-$regdate)/(24*60*60);
	   $postday = $userdata[user_posts]/$memberdays;
	   
	   
	   if (!$userdata[user_id]) {
	      error_die($l_nouser);
	   }
	   if($userdata[user_level] == -1) {
			error_die($l_userremoved);
	   }
?>
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>"><TR><TD  BGCOLOR="<?php echo  $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_username?>:</FONT></b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php echo $userdata[username]?></FONT>
	        <font size=-2>(<a href="search.<?php echo $phpEx?>?term=&addterms=any&forum=all&search_username=<?php echo rawurlencode($userdata[username])?>&sortby=p.post_time&searchboth=both&submit=Search"><?php echo $l_viewpostuser?></a>)
			  &nbsp;&nbsp;(<a href="sendpmsg.<?php echo $phpEx?>?tousername=<?php echo rawurlencode($userdata[username])?>"><?php echo $l_sendpmsg?></a>)</font></TD>
	</TR>
	<TR ALIGN="LEFT">
                <TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_joined?>:</FONT></b></TD>
                <TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php printf("%s (%.2f %s)", $userdata[user_regdate], $postday, $l_perday)?></FONT></TD>
        </TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_posts?>:</FONT></b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php printf("%s (%.2f%% %s)", $userdata[user_posts], $user_percentage, $l_oftotal)?></FONT></TD>
	</TR>

<?php
			if($userdata[user_viewemail] == 1) {
?>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_emailaddress?>:<b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="mailto:<?php echo $userdata[user_email]?>"><?php echo $userdata[user_email]?></a></TD>
	</TR>
<?php
			}
?>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_icq . " " .$l_number?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php echo $userdata[user_icq]?>
		<?php if ($userdata[user_icq]!=""){ ?>
		</FONT>&nbsp;&nbsp;<font size=-2>(<a href="http://wwp.icq.com/scripts/search.dll?to=<?php echo $userdata[user_icq]?>"><?php echo $l_icqadd?></a>)</font>&nbsp;&nbsp;<font size=-2>(<a href="http://wwp.mirabilis.com/<?php echo $userdata[user_icq]?>" TARGET="_blank"><?php echo $l_icqpager?></a>)</font>
		<? } 
		else
		{
			echo "&nbsp";	
		}
		?>
		</TD>

	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_aim?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="aim:goim?screenname=<?php echo $userdata[user_aim]?>&message=Hi+<?php echo $userdata[user_aim]?>.+Are+you+there?"><?php echo $userdata[user_aim]?></a></FONT>&nbsp;</TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_yahoo?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="http://edit.yahoo.com/config/send_webmesg?.target=<?php echo $userdata[user_yim]?>&.src=pg"><?php echo $userdata[user_yim]?></a></FONT>&nbsp;</TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_messenger?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php echo $userdata[user_msnm]?>&nbsp;</TD>
	</TR>

	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_website?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><a href="<?php echo $userdata[user_website]?>" target="_blank"><?php echo $userdata[user_website]?></a></FONT>&nbsp;</TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_location?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php echo stripslashes($userdata[user_from])?>&nbsp;</TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_occupation?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php echo stripslashes($userdata[user_occ])?>&nbsp;</TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_interests?>: <b></TD>
<?php
	$userdata[user_intrest] = stripslashes($userdata[user_intrest]);
?>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><?php echo $userdata[user_intrest]?>&nbsp;</TD>
	</TR>
	</TABLE></TD></TR></TABLE>
<?php
		
	break;
	case 'edit':
	   if ($submit || $user_logged_in) {
	      // ok.. either the user's entered their username and password, or they have a valid session.
	      if ($save) {
		 // trying to save their profile information..
		 $userdata = get_userdata_from_id($user_id, $db);
		 if(is_banned($userdata[user_id], "username", $db))
		   error_die($l_banned);
		 if (!$userdata[user_id]) {
		    error_die($l_nouser);
		 }
		 if ($password == '') {
		    include('page_header.'.$phpEx);
		    error_die("$l_enterpassword $l_tryagain");
		 }
		 $md_pass = md5($password);
		 if ($md_pass != $userdata[user_password]) {
		    include('page_header.'.$phpEx);
		    error_die("$l_wrongpass $l_tryagain");
		 }
		 
		 if ($new_password != '') {
		    if ($new_password != $password2)  {
		       include('page_header.'.$phpEx);
		       error_die("$l_mismatch $l_tryagain");
		    }
		    $md_pass = md5($new_password);
		 }
		 // whatever the case, $md_pass contains the password for the DB.
		 // ready to save, they've authed just fine..
		 if($allow_namechange && $user_name != $userdata[username]) {
		    if (check_username($user_name, $db)) {
		       error_die("$l_usertaken $l_tryagain");
		    }
		    if(validate_username($user_name, $db) == 1) {
		       include('page_header.'.$phpEx);
		       error_die("$l_userdisallowed $l_tryagain");
		    }
		    $new_name = 1;
		 }
		 $sig = chop($sig); // Strip all trailing whitespace.
		 $sig = str_replace("\n", "<BR>", $sig);
		 $sig = addslashes($sig);
		 $occ = addslashes($occ);
		 $intrest = addslashes($intrest);
		 $from = addslashes($from);
		 $passwd = $md_pass;
		 $email = addslashes($email);
		 
		 // Ensure the website URL starts with "http://".
	    $website = addslashes(trim($website));
		 if(substr(strtolower($website), 0, 7) != "http://")
		   {
		      $website = "http://" . $website;
		   }

		 if($website == "http://")
		 {
		 	$website = "";
		 }
		 
		 // Check if the ICQ number only contains digits
		 $icq = (ereg("^[0-9]+$", $icq)) ? $icq : '';
		
       $aim = addslashes($aim);
       $yim = addslashes($yim);
       $msnm = addslashes($msnm);

		 
		 if($new_name) {
		    $sql = "UPDATE users SET username = '$user_name', user_password = '$md_pass', user_icq = '$icq', user_occ = '$occ', user_intrest = '$intrest', user_from = '$from', user_website = '$website', user_sig = '$sig', user_email = '$email', user_viewemail = '$viewemail', user_aim = '$aim', user_yim = '$yim', user_msnm = '$msnm' WHERE (user_id = '$user_id')";
		 }
		 else {
		    $sql = "UPDATE users SET user_password = '$md_pass', user_icq = '$icq', user_occ = '$occ', user_intrest = '$intrest', user_from = '$from', user_website = '$website', user_sig = '$sig', user_email = '$email', user_viewemail = '$viewemail', user_aim = '$aim', user_yim = '$yim', user_msnm = '$msnm' WHERE (user_id = '$user_id')";
		 }
		 if(!$result = mysql_query($sql, $db)) {
		    error_die("Could not update userinfo in database.<br>$sql");
		 }
		 // They have authed, log them in.
		 $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);
		 set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
		 include('page_header.'.$phpEx);
		 echo "$l_infoupdated.<br>$l_click <a href=\"index.$phpEx\">$l_here</a> $l_returnindex.";
	      } else { 
		 // not trying to save, so show the form.
		 if (!$user_logged_in) {
		    // no valid session, need to check user/pass.
		    if($user == '' || $passwd == '') {
		       error_die("$l_userpass $l_tryagain");
		    }
		    $md_pass = md5($passwd);
		    $userdata = get_userdata($user, $db);
		    if(is_banned($userdata[user_id], "username", $db))
		      error_die("$l_banned");
		    if($md_pass != $userdata["user_password"]) {
		       error_die("$l_wrongpass $l_tryagain");
		    }	
		    // They have authed succecfully, log them in.
		    $sessid = new_session($userdata[user_id], $REMOTE_ADDR, $sesscookietime, $db);
		    set_session_cookie($sessid, $sesscookietime, $sesscookiename, $cookiepath, $cookiedomain, $cookiesecure);
		 }
		 include('page_header.'.$phpEx);
?>
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="<?php echo $TableWidth?>">
	<TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_username?>: *</FONT></b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
<?php 
		if($allow_namechange) {
		   echo "<input type=\"text\" name=\"user_name\" size=\"35\" maxlength=\"40\" value=\"$userdata[username]\">";
		}
		else {
		   echo $userdata[username];
		}
?>
	       </FONT></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_password?>: *</FONT></b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="PASSWORD" NAME="password" SIZE="25" MAXLENGTH="25"></TD>
	</TR>
		<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_new ." " .$l_password?>: </FONT></b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="PASSWORD" NAME="new_password" SIZE="25" MAXLENGTH="25"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_confirm . " " . $l_password?>:</b></FONT><br><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>">(<?php echo $l_onlyreq?>)</FONT></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="PASSWORD" NAME="password2" SIZE="25" MAXLENGTH="25"></TD>
	</TR>

	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_emailaddress?>: *<b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="email" SIZE="25" MAXLENGTH="80" VALUE="<?php echo $userdata[user_email]?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_icq . " ". $l_number?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="icq" SIZE="10" MAXLENGTH="20" VALUE="<?php echo $userdata[user_icq]?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_aim?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="aim" SIZE="25" MAXLENGTH="80" VALUE="<?php echo $userdata[user_aim]?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_yahoo?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="yim" SIZE="25" MAXLENGTH="80" VALUE="<?php echo $userdata[user_yim]?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo  $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_messenger?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="msnm" SIZE="25" MAXLENGTH="80" VALUE="<?php echo $userdata[user_msnm]?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_website?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="website" SIZE="25" MAXLENGTH="120" VALUE="<?php echo $userdata[user_website]?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_location?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="from" SIZE="25" MAXLENGTH="40" VALUE="<?php echo $userdata[user_from]?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_occupation?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="occ" SIZE="25" MAXLENGTH="255" VALUE="<?php echo $userdata[user_occ]?>"></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_interests?>: <b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="TEXT" NAME="intrest" SIZE="25" MAXLENGTH="255" VALUE="<?php echo $userdata[user_intrest]?>"></TD>
	</TR>
<?php
	$sig = str_replace("<BR>", "\n", $userdata[user_sig]);
	$sig = stripslashes($sig);
?>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_signature?>:</b><br><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize1?>" COLOR="<?php echo $textcolor?>"><?php echo $l_sigexplain?></font></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><TEXTAREA NAME="sig" ROWS=6 COLS=45><?php echo $sig?></TEXTAREA></TD>
	</TR>
	<TR ALIGN="LEFT">
<?php
		if($userdata[user_viewemail] == 1)
			$s = " CHECKED";
?>
		<TD  BGCOLOR="<?php echo $color1?>" width="25%"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><b><?php echo $l_options?>:</FONT></b></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>"><INPUT TYPE="CHECKBOX" NAME="viewemail" VALUE="1" <?php echo $s?>> <?php echo $l_publicmail?></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" colspan = 2><FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize3?>" COLOR="<?php echo $textcolor?>"><?php echo $l_itemsreq?></font></TD>
	</TR>
	<TR>
		<TD BGCOLOR="<?php echo $color1?>" colspan=2 ALIGN="CENTER">
		<INPUT TYPE="HIDDEN" NAME="mode" VALUE="edit">
		<INPUT TYPE="HIDDEN" NAME="save" VALUE="1">
		<INPUT TYPE="HIDDEN" NAME="user_id" VALUE="<?php echo $userdata[user_id]?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo $l_submit?>">
		</TD>
	</TR>
	</TABLE></TD></TR></TABLE></FORM>
<?PHP

			}
		} else {
			// no valid session, and they haven't submitted.
			// so, we need to get a user/pass.
	      include('page_header.'.$phpEx);
			login_form();
		}
	break;

	} // switch

} // if ($mode)
?>
<?php
include('page_tail.'.$phpEx);
?>
