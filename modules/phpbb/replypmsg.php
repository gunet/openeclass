<?php
/***************************************************************************
                            replypmsg.php  -  description
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
$pagetitle = "Post PM Reply";
$pagetype = "pmreply";
include('page_header.'.$phpEx);

if($submit) {
	if($message == '') {
		error_die("$l_emptymsg $l_tryagain");
	}

	$sql = "SELECT u.* FROM users u, priv_msgs p WHERE (u.user_id = p.to_userid) AND (p.msg_id = $msgid)";
	$result = mysql_query($sql, $db);
	if (!$result) {
		die("Error getting userinfo from database");
	}
	$fromuserdata = mysql_fetch_array($result);
	

	if (!$user_logged_in) { // don't check this stuff if we have a valid session..
		if($password == '') {
			die("$l_userpass $l_tryagain");
		}
	
		$md_pass = md5($password);
		
		if($md_pass != $fromuserdata["user_password"]) {
			die("$l_wrongpass $l_tryagain");
		}
	} else {
		// we have a valid session..
		if ($fromuserdata[user_id] == $userdata[user_id]) {
			$fromuserdata = $userdata; // fromuser = current user.
		} else {
			error_die("Wrong user logged in.");
		}
	}
	
	/* correct password or logged-in user, continuing with message send. */

	$is_html_disabled = false;
	if($allow_pmsg_html == 0 && !isset($html)) {
		$message = htmlspecialchars($message);
		$is_html_disabled = true;
		if (isset($quote) && $quote)
      {
      	$edit_by = get_syslang_string($sys_lang, "l_editedby");
   
		   // If it's been edited more than once, there might be old "edited by" strings with
		   // escaped HTML code in them. We want to fix this up right here:
		   $message = preg_replace("#&lt;font\ size\=-1&gt;\[\ $edit_by(.*?)\ \]&lt;/font&gt;#si", '<font size=-1>[ ' . $edit_by . '\1 ]</font>', $message);	
      }
	}
	
	if($sig) {
		$message .= "<BR>_________________<BR>" . $fromuserdata[user_sig];
	}
	if($allow_pmsg_bbcode == 1 && !isset($bbcode)) {
		$message = bbencode($message, $is_html_disabled);
	}
	
	// MUST do make_clickable() and smile() before changing \n into <br>.
	$message = make_clickable($message);
	if(!$smile) {
		$message = smile($message);
	}
	
	$message = str_replace("\n", "<BR>", $message);
	$message = addslashes($message);
	$time = date("Y-m-d H:i");
	$sql = "SELECT from_userid FROM priv_msgs WHERE (msg_id = $msgid)";
	$result = mysql_query($sql);
	if (!$result) {
		echo $sql . mysql_error();
		error_die("Error getting userid from message");
	}
	$row = mysql_fetch_array($result);
	$touserid = $row[from_userid];

	$sql = "INSERT INTO priv_msgs (from_userid, to_userid, msg_time, msg_text, poster_ip) ";
	$sql .= "VALUES ($fromuserdata[user_id], $touserid, '$time', '$message', '$poster_ip')";
	
	if(!$result = mysql_query($sql, $db)) {
		error_die("Error - Could not enter data into the database. Please go back and try again");
	}
   echo "<br><TABLE BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"0\" ALIGN=\"CENTER\" VALIGN=\"TOP\" WIDTH=\"$tablewidth\">";
   echo "<TR><TD  BGCOLOR=\"$table_bgcolor\"><TABLE BORDER=\"0\" CALLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"100%\">";
   echo "<TR BGCOLOR=\"$color1\" ALIGN=\"LEFT\"><TD><font face=\"Verdana\" size=\"2\"><P>";
   echo "<P><BR><center>$l_pmposted<p></center></font>";
   echo "</TD></TR></TABLE></TD></TR></TABLE><br>";
		
} else {
	$sql = "SELECT from_userid, to_userid FROM priv_msgs WHERE (msg_id = $msgid)";
	$result = mysql_query($sql, $db);
	if (!$result) {
		error_die("Error doing DB query to get userid's from message.");
	}
	$row = mysql_fetch_array($result);
	if (!$row) {
		error_die("Message not found");
	}
	$fromuserdata = get_userdata_from_id($row[from_userid], $db);
	$touserdata = get_userdata_from_id($row[to_userid], $db);
	if ( $user_logged_in && ($userdata[user_id] != $touserdata[user_id]) ) {
		error_die("You can't reply to that message. It wasn't sent to you.");
	}

?>
	<FORM ACTION="<?php echo $PHP_SELF?>" METHOD="POST">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACEING="0" ALIGN="CENTER" VALIGN="TOP" WIDTH="95%"><TR><TD  BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CALLPADDING="1" CELLSPACEING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="LEFT">
		<TD width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_aboutpost?>:</b>
			</FONT>
		</TD>
		<TD>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<?php echo "$l_regusers $l_cansend"?>
			</FONT>
		</TD> 
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_yourname?>:<b>
		</TD>
		<TD  BGCOLOR="<?php echo $color2?>">
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
<?PHP
	if ($user_logged_in) {
		echo "$userdata[username] \n";
	} else {
		echo "$touserdata[username] \n";
	}
?>
			</FONT>
		</TD>
	</TR>
<?PHP
	if (!$user_logged_in) { 
		// no session, need a password.
		echo "    <TR ALIGN=\"LEFT\"> \n";
		echo "        <TD BGCOLOR=\"$color1\" width=25%><b>$l_password:</b></TD> \n";
		echo "        <TD BGCOLOR=\"$color2\"><INPUT TYPE=\"PASSWORD\" NAME=\"password\" SIZE=\"25\" MAXLENGTH=\"25\"></TD> \n";
		echo "    </TR> \n";
	}
?>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>"  width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_recptname?>:<b>
			</FONT>
		</TD>
		<TD  BGCOLOR="<?php echo $color2?>">
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<?php echo $fromuserdata[username]?>
			</FONT>
		</TD>
	</TR>

	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_body?>:</b><br><br>
			</FONT>
		<font size=-1>
		<?php
		echo "$l_htmlis: ";
		if($allow_html == 1)
			echo "$l_on<BR>\n";
		else
			echo "$l_off<BR>\n";
		echo "$l_bbcodeis: ";
		if($allow_bbcode == 1)
			echo "$l_on<br>\n";
		else
			echo "$l_off<BR>\n";

		if($quote) {
			$sql = "SELECT p.msg_text, p.msg_time, u.username FROM priv_msgs p, users u ";
			$sql .= "WHERE (p.msg_id = $msgid) AND (p.from_userid = u.user_id)";
			if($result = mysql_query($sql, $db)) {
				$m = mysql_fetch_array($result);
				$m[post_time] = $m[msg_time];
				$text = desmile($m[msg_text]);
				$text = str_replace("<BR>", "\n", $text);
				$text = stripslashes($text);
				$text = bbdecode($text);
				$text = undo_make_clickable($text);
				$text = str_replace("[addsig]", "", $text);
				$syslang_quotemsg = get_syslang_string($sys_lang, "l_quotemsg");
				eval("\$reply = \"$syslang_quotemsg\";");
			}
			else {
				error_die("Problem with getting the quoted message.");
			}
		}				
		?>		
		</font></TD>
		<TD  BGCOLOR="<?php echo $color2?>"><TEXTAREA NAME="message" ROWS=10 COLS=45 WRAP="VIRTUAL"><?php echo $reply?></TEXTAREA></TD>
	</TR>
	<TR ALIGN="LEFT">
		<TD  BGCOLOR="<?php echo $color1?>" width=25%>
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
			<b><?php echo $l_options?>:</b>
			</FONT>
		</TD>
		<TD  BGCOLOR="<?php echo $color2?>" >
			<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		<?php
			if($allow_html == 1) {
				echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"html\">$l_disable $l_html $l_onthispost<BR>";
			}
			if($allow_bbcode == 1) {
				echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"bbcode\">$l_disable <a href=\"$bbref_url\" target=\"_blank\"><i>$l_bbcode</i></a> $l_onthispost<BR>";
			}

		echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"smile\">$l_disable <a href=\"$smileref_url\" target=\"_blank\"><i>$l_smilies</i></a> $l_onthispost.<BR>";
			if($allow_sig == 1) {
				echo "<INPUT TYPE=\"CHECKBOX\" NAME=\"sig\">$l_attachsig<BR>";
			}
		?>
			</FONT>
		</TD>
	</TR>
	<TR>
		<TD  BGCOLOR="<?php echo $color1?>" colspan=2 ALIGN="CENTER">
		<INPUT TYPE="HIDDEN" NAME="msgid" VALUE="<?php echo $msgid?>">
		<INPUT TYPE="HIDDEN" NAME="quote" VALUE="<?php echo $quote?>">
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="<?php echo $l_submit?>">
	</TR>
	</TABLE></TD></TR></TABLE>
	</FORM>
	<BR>

<?PHP
}
require('page_tail.'.$phpEx);
?>
